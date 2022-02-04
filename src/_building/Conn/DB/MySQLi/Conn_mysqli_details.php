<?php
class Conn_mysqli_details extends Conn_details {
	private $__fieldsKey;
	protected function getVersion(){
		$conn=$this->conn;
		if(!$this->protect['version']) $this->protect['version']=$conn->get_server_info()+0;
		return $this->protect['version'];
	}
	protected function getErTbl($name=false){
		return $name?"(?:`(?<$name>[^`]+)`|(?<s$name>\w+))":'(?:`([^`]+)`|(\w+))';
	}
	protected function getErFullTbl(){
		return "(?:{$this->getErTbl('DataBase')}\.)?{$this->getErTbl('Table')}";
	}
	protected function getErTblAlias(){
		return "(?:\s*(?:\bas\s+)?{$this->getErTbl('Alias')})?";
	}
	protected function getErFullTblOnly(){
		return "/^{$this->getErFullTbl()}{$this->getErTblAlias()}$/i";
	}
	protected function setSql($value){
		if(is_null($value)) $value=$this->__get_backtrace('sql');
		if(!$value) return;
		$this->__fieldsKey=array();
		$contraBarra=chr(251).chr(251);  // \x5C
		$aspasSimples=chr(252).chr(252); // \x27
		$aspasDupla=chr(253).chr(253);   // \x22
		$pontoVirgula=chr(254);          // \x3B
		/*
		$value=preg_replace(
			array('/\\\\/',     "/['\\\]'/",   '/["\\\]"/', '/\s*\/\*(?:.|\s)*?\*\//', ),
			array($contraBarra, $aspasSimples, $aspasDupla, '',                        ),
			$value
		);
		*/
		$value=preg_replace(
			array('/\r/', '/\\\\/',     "/['\\\]'/",   '/["\\\]"/', '/(?:(\n+)[\t ]*)?\/\*(?:.|\s)*?\*\//', '/(?:(\n+)[\t ]*)?(?:--|#).*?\n+/', '/\n\s*(?:--|#)[^\n]*$/', '/\n{2,}/', ),
			array('\n',   $contraBarra, $aspasSimples, $aspasDupla, '\1',                                   '\1',                               '',                       "\n",       ),
			$value
		);
		$value=preg_replace(
			'/(["\'])([^"\']*?)\1/e',
			"'\\1'.str_replace(';',\$pontoVirgula,'\\2').'\\1'",
			$value
		);
		$value=preg_split('/\s*;\s*/',$value);
		$er=$this->getErFullTblOnly();
		$sp=array();
		foreach ($value as $k=>$q) {
			if(!($q=trim($q))) continue;
			if(!preg_match('/^(?!select)/i',$q)) {
				if (preg_match($er,$q)) $q="SELECT * FROM $q";
				elseif (preg_match('/^select\s+(.|\s)+\bunion\b/i',$q)) $q="SELECT * FROM ($q) as t";
			}
			$sp[]=$q;
		}
		$sp=str_replace(
			array($contraBarra,$aspasSimples,$aspasDupla,$pontoVirgula,),
			array('\\\\',      "''",         '""',       ';',          ),
			$sp //implode(";\n",$sp)
		);
		$this->protect['sqlFull']=$sp;
		$this->parseQuery($sp,$this->conn->get_database());
		exit;
		//$this->parseQuery($value);
	}
	private function parseQuery($view,$dbNow){
		if(is_array($view)) {
			foreach($view as $v) $this->parseQuery($v,$dbNow);
			return;
		}
		$ret=$this->getDependencesOfQuery($view,$dbNow);
		print __LINE__.": $view\n"; print_r($ret);
		if(@$ret['Table|View']) foreach($ret['Table|View'] as $nm=>$l) {
			$this->protect['tables'][$nm]=$this->getShowTable($nm,$l['DataBase'],$l['Name'],$l['Alias']);
			$this->__allDatabases[$l['DataBase']][$l['Name']]=$nm;
			$this->__allTables[$l['Name']][$l['DataBase']]=$nm;
			$this->__allAlias[$l['Alias']]=$nm;
		}
	}
	protected function getDependencesOfQuery($view,$dbNow){
		//$v=$this->version;
		$c=$this->getErTbl();
		$erFullTable=$this->getErFullTbl();
		$erAlias=$this->getErTblAlias();
		$conn=$this->conn;
		//$view=$this->sqlFull;
		$out=array();
		if (
			preg_match_all("/^$erFullTable$erAlias$/i",$view,$ret,PREG_SET_ORDER) ||
			preg_match_all("/(?<=\sfrom|\sjoin|\sstraight_join|call)[\s\(]+(?!select)(?:$erFullTable(?:\s+(?!union|where|on|select|group|having|order|limit|procedure|(?:inner\s+|cross\s+|straight_|left\s+)?join)$erAlias)?(?:\s*,\s*)?)+/i",$view,$ret,PREG_SET_ORDER)
		) {
			foreach($ret as $line) {
				($db=@$line['Database']) || ($db=@$line['sDataBase']) || ($db=$dbNow);
				($tbl=@$line['Table']) || ($tbl=@$line['sTable']);
				($alias=@$line['Alias']) || ($alias=@$line['sAlias']) || ($alias=$tbl);
				$nm="`$db`.`$tbl`";
				$out['Table|View'][$nm]=array('FullName'=>$nm,'DataBase'=>$db,'Name'=>$tbl,'Type'=>'Table|View','Alias'=>$alias);
			}
		}
		return $out;
	}
	private function getShowTable($fullName,$database,$table,$alias){
		static $results=array();
		$conn=$this->conn;
		$dsn=$conn->dsn['dsnName'];
		$key=$dsn.':'.$fullName;
		if(!isset($results[$key])) {
			$fields=$keys=$constraints=$properties=array();
			$line=$conn->fastLine('show create table '.$fullName);
			if(($ddl=@$line['Create Table'])) {
				$detSplit=explode(",\n",preg_replace(array('/^.+\n/','/\n.+$/'),'',$ddl));
				foreach($detSplit as $v) {
					if(preg_match('/^\s*`(?<field>[^`]+)`\s*(?<type>\w+(?:\(.*?\))?)(?: CHARACTER SET (?<charset>\w+))?(?: COLLATE (?<collate>\w+))?(?: (?<unsigned>unsigned))?(?: (?<zerofill>zerofill))?(?: (?<not_null>(?:NOT )?NULL))?(?: (?<auto_increment>AUTO_INCREMENT))?(?: DEFAULT (?<default>\'.*?\'|\w+))?(?: ON UPDATE (?<on_update>\w+))?(?: COMMENT \'(?<comment>.*)\')?$/i',$v,$ret)) {
						$length=$precision=$source=null;
						if(preg_match('/^(set|enum)/i',$ret['type'],$rTy)) {
							$source=$this->getSourceSetEnum($ret['type'],$length);
						} 
						else if(preg_match('/^(\w+)\((\d+)(?:,(\d+))?\)/i',$ret['type'],$rTy)) {
							$length=$rTy[2];
							$precision=@$rTy[3];
						}
						else $rTy=array(1=>$ret['type']);
						$type=$rTy[1];
						$fields[$ret['field']]=array(
							'line'=>$v,
							'fullName'=>"{$fullName}.`{$ret['field']}`",
							'orgName'=>"`{$alias}`.`{$ret['field']}`",
							'type'=>$type,
							'length'=>$length,
							'precision'=>$precision,
							'source'=>$source,
							'unsigned'=>(bool)@$ret['unsigned'],
							'zerofill'=>(bool)@$ret['zerofill'],
							'not_null'=>strtolower(@$ret['not_null'])=='not null',
							'auto_increment'=>(bool)@$ret['auto_increment'],
							'default'=>$this->decodeComment(preg_replace('/\'(.*)\'/','\1',@$ret['default'])),
							'defaultRaw'=>@$ret['default'],
							'on_update'=>@$ret['on_update'],
							'comment'=>$this->decodeComment(@$ret['comment']),
							'charset'=>@$ret['charset'],
							'collate'=>@$ret['collate'],
							'key'=>'',
						);
						$this->__allFields[$ret['field']][$fullName]=$alias;
					}
					elseif(preg_match('/^\s*(?<keyType>(?:\w+ )?KEY) (?:`(?<keyName>[^ ]+)` )?\(`(?<fields>.+?)`\)\s*$/i',$v,$ret)) {
						$list=explode('`,`',$ret['fields']);
						foreach($list as $f) if(@$fields[$f] && !$fields[$f]['key']) $fields[$f]['key']=$ret['keyType'];
						if(@$ret['keyName']) $keys[$ret['keyType']][$ret['keyName']]=$list;
						else $keys[$ret['keyType']]=$list;
					}
					elseif(preg_match('/^\s*CONSTRAINT `(?<constraintName>[^`]+)` FOREIGN KEY (?:`(?<keyName>[^ ]+)` )?\(`(?<fields>.+?)`\) REFERENCES (?<tableRefer>(`[^`]+`.)?`[^`]+`) \(`(?<fieldsRefer>.+?)`\)(?: ON DELETE (?<on_delete>RESTRICT|CASCADE|SET NULL|NO ACTION))?(?: ON UPDATE (?<on_update>RESTRICT|CASCADE|SET NULL|NO ACTION))?\s*$/i',$v,$ret)) {
						$constraints[$ret['constraintName']]=array(
							'keyName'=>@$ret['keyName'],
							'fields'=>explode('`,`',$ret['fields']),
							'tableRefer'=>$this->rebuildTableName(@$ret['tableRefer'],$database),
							'fieldsRefer'=>explode('`,`',$ret['fieldsRefer']),
							'on_delete'=>@$ret['on_delete'],
							'on_update'=>@$ret['on_update'],
						);
					}
				}
				if(preg_match('/\n\) *(.+)$/',$ddl,$tblDet)){
					if(preg_match_all('/(\w+)=(\w+)/',$tblDet[1],$tmp,PREG_SET_ORDER)) {
						foreach($tmp as $v) $properties[$v[1]]=$v[2];
					}
					if(preg_match('/ (COMMENT)=\'(.*)\'$/i',$tblDet[1],$v)) $properties[$v[1]]=$this->decodeComment($v[2]);
				}
				$properties['TABLE_TYPE']='TABLE';
			}
			elseif(($ddl=@$line['Create View'])) {
				$res=$conn->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$table'");
				while($line=$res->fetch_assoc()) {
					$length=$precision=$source=null;
					preg_match('/^(?<type>.+?)(?<unsigned> unsigned)?(?<zerofill> zerofill)?$/',$line['COLUMN_TYPE'],$ret);
					if(preg_match('/^(set|enum)/i',$ret['type'],$rTy)) {
						$source=$this->getSourceSetEnum($ret['type'],$length);
					} 
					else if(preg_match('/^(\w+)\((\d+)(?:,(\d+))?\)/i',$ret['type'],$rTy)) {
						$length=$rTy[2];
						$precision=@$rTy[3];
					}
					else $rTy=array(1=>$ret['type']);
					$type=$rTy[1];
					$fields[$line['COLUMN_NAME']]=array(
						'line'=>"`{$line['COLUMN_NAME']}` {$line['COLUMN_TYPE']}",
						'fullName'=>"{$fullName}.`{$line['COLUMN_NAME']}`",
						'orgName'=>"`{$alias}`.`{$line['COLUMN_NAME']}`",
						'type'=>$type,
						'length'=>$length,
						'precision'=>$precision,
						'source'=>$source,
						'unsigned'=>(bool)@$ret['unsigned'],
						'zerofill'=>(bool)@$ret['zerofill'],
						'not_null'=>$line['IS_NULLABLE']=='NO',
						'auto_increment'=>false,
						'default'=>$line['COLUMN_DEFAULT'],
						'defaultRaw'=>$line['COLUMN_DEFAULT'],
						'on_update'=>null,
						'comment'=>$line['COLUMN_COMMENT'],
						'charset'=>$line['CHARACTER_SET_NAME'],
						'collate'=>$line['COLLATION_NAME'],
						'key'=>$line['COLUMN_KEY'],
					);
					$this->__allFields[$line['COLUMN_NAME']][$fullName]=$alias;
				}
				$res->close();
				$properties=$conn->fastLine("
					SELECT CHECK_OPTION, IS_UPDATABLE, DEFINER, SECURITY_TYPE, CHARACTER_SET_CLIENT, COLLATION_CONNECTION
					FROM information_schema.VIEWS 
					WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$table'
				");
				if($properties['IS_UPDATABLE']=='NO') $this->readonly['is_updatable']=false;
				$properties['TABLE_TYPE']='VIEW';
			}
			$results[$key]=compact('database','table','ddl','fields','keys','constraints','properties');
		}
		return $results[$key];
	}
	private function rebuildTableName($table,$db) {
		if(preg_match('/^(?:(`?)(?<db>[^`]+?)\1.)?(`?)(?<tbl>[^`]+?)\3$/',$table,$ret)) {
			if(!@$ret['db']) $ret['db']=$db;
			return "`{$ret['db']}`.`{$ret['tbl']}`";
		}else return $table;
	}
	private function decodeComment($str) {
		return strtr($str,array('\r'=>"\r",'\n'=>"\n",'\t'=>"\t","''"=>"'",));
	}
	private function getSourceSetEnum($type,&$length){
		$list=preg_replace(array('/^(set|enum)\(/i','/\)$/'),'',$type);
		$source=array();
		do {
			preg_match('/^(\'?)(.*?)(?<!\\\)\1(?:,(.*))?$/',$list,$ret);
			$list=stripslashes($ret[2]);
			$length=max($length,strlen($list));
			$source[]=$list;
			$list=@$ret[3];
		} while($list);
		return $source;
	}
	//Monta o WHERE
	function mountWhere($conditions){ //$conditions=array('`BANCO`.`TABELA`.`FIELD`=><VALOR>);
		$where=array();
		foreach ($conditions as $key=>$value) {
			if (preg_match('/^\*+$/',$value)) continue;
			if (preg_match("/^\((!?)ereg\)(.*)/i",$value,$ret)) {// (ereg)partern or (!ereg)partern
				$not=$ret[1]?'NOT ':'';
				return "$key {$not}REGEXP '".str_replace("'","\\'",$ret[2])."'";
			}
			if($cond=$this->mountWhereField($key,$value)) $where[]=$cond;
		}
		//print "<pre>MANUTENÇÃO: \n".print_r($where,true)."</pre>";
		return $where?"\nWHERE ".implode(' AND ',$where):'';
	}
	/*
	 * Captura os tipos de condições:
	 * vazio(''), igual('='), diferente('!=', '<>'), maior('>'), maiorIgual('>='), menor('<'), menorIgual('>=') 
	 * e separadores ou('', '|'), e('&')
	 * o caracter escape '\' pode ser utilizado para os sinais acima para texto literal
	 *
	 * @parameter $value string Condição Ex: aaa!*\&|<>234
	 * @return array array(array('separator'=><'','|','&'>,'sign'=><'','=','!=','>','>=','<','>='>,'content'=><conteudo literal>), ...)
	 */
	private function getField($fullField){
		$fff=$fullField;
		if(!array_key_exists($fff,@$this->__fieldsKey)) {
			$field=$this->getFieldLastArg($fullField);
			if(!$field) return $this->__fieldsKey[$fff]=false;
			$table=$this->getFieldLastArg($fullField);
			if(!$table) {
				$tmp=@$this->__allFields[$field];
				if(!$tmp) return $this->__fieldsKey[$fff]=false;
				$table=key($tmp);
				return $this->__fieldsKey[$fff]=@$this->protect['tables'][$table]['fields'][$field];
			}
			$db=$this->getFieldLastArg($fullField);
			if($db && ($f=@$this->protect['tables']["`$db`.`$table`"]['fields'][$field])) return $this->__fieldsKey[$fff]=$f;
			$tmp=@$this->__allAlias[$table];
			if($tmp && ($f=@$this->protect['tables'][$tmp]['fields'][$field])) return $this->__fieldsKey[$fff]=$f;
			$tmp=@$this->__allTables[$table];
			if(!$tmp) return $this->__fieldsKey[$fff]=false;
			$this->__fieldsKey[$fff]=@$this->protect['tables'][reset($tmp)]['fields'][$field];
		}
		return $this->__fieldsKey[$fff];
	}
	private function mountWhereField($key,$value){
		if(!($value=$this->parserCondition($value))) return false;
		$field=$this->getField($key);/*
	'line'=>$v,
	'fullName'=>"{$fullName}.`{$ret['field']}`",
	'orgName'=>"`{$alias}`.`{$ret['field']}`",
	'type'=>$type,
	'length'=>$length,
	'precision'=>$precision,
	'source'=>$source,
	'unsigned'=>(bool)@$ret['unsigned'],
	'zerofill'=>(bool)@$ret['zerofill'],
	'not_null'=>strtolower(@$ret['not_null'])=='not null',
	'auto_increment'=>(bool)@$ret['auto_increment'],
	'default'=>$this->decodeComment(preg_replace('/\'(.*)\'/','\1',@$ret['default'])),
	'defaultRaw'=>@$ret['default'],
	'on_update'=>@$ret['on_update'],
	'comment'=>$this->decodeComment(@$ret['comment']),
	'charset'=>@$ret['charset'],
	'collate'=>@$ret['collate'],
	'key'=>'',*/
		$isDateTime=preg_match('/(date|time|stamp)/',@$field['type']);
		$out='';
		foreach($value as $k=>$c){
			if($k!=0) $out.=($c['separator']=='&')?' AND ':' OR ';
			$c['content']=addslashes($c['content']);
			$cond=$c['sign'].$c['content'];
			if(preg_match('/^!?(=|\*+)?$/',$cond)) {//empty or not
				if($isDateTime) {
					$not=$cond{0}=='!'?' NOT':'';
					$out.="IFNULL($key,''){$not} IN ('','0000-00-00','0000-00-00 00:00:00','00:00:00')";
				}
				else {
					$not=$cond{0}=='!'?'!':'';
					$out.="IFNULL($key,''){$not}=''";
				}
			}
			else{
				$val=$c['content'];
				if($c['sign']=='>=' || $c['sign']=='<=') { //like or not
					$not='';
					$sign=$c['sign'];
				}
				elseif(($c['sign']=='' || $c['sign']=='!') && preg_match('/[\*\?]/',$val)) { //like or not
					$not=$c['sign']=='!'?' NOT':'';
					$sign=' LIKE ';
					$val=preg_replace(
						array('([_%])','/(?<!\\\)\*/','/(?<!\\\)\?/','/\\\*/','/\\\?/'),
						array('\\\1','%','_','*','?'),
						$val
					);
					//fixme testar
				}
				else { //equal or not
					$not=$cond{0}=='!'?'!':'';
					$sign='=';
				}
				if($isDateTime) {
					$dateTime=array();
					if(preg_match('/(date|stamp)/',@$field['type'])) {
						if(@$field['type']=='date') $t=$val;
						elseif(preg_match('/(.*) /',$val,$retDT) || preg_match('/(?[\d_%-]*-?[\d_%-]*|?[\d_%\/]*\/?[\d_%\/]*)/',$val,$retDT)) $t=$retDT[1];
						else $t='';
						if($sign==' LIKE '){
							if(
								preg_match("/(?<day>[\d_%]{1,2})(?:\/(?<month>[\d_%]{1,2})(?:\/(?<year>[\d_%]{2,4})))/",$t,$retDT) ||
								preg_match("/(?<year>[\d_%]{2,4})(?:-(?<month>[\d_%]{1,2})(?:-(?<day>[\d_%]{1,2})))/",$t,$retDT)
							) {
								$retDT=preg_replace(array('/^(\d)$/','/^$/','/^_$/'),array('0\1','%','%'),$retDT);
								if($retDT['year']{0}!='%') $retDT['year']=substr(date('Y'),0,4-strlen($retDT['year'])).$retDT['year'];
								$dateTime[]=$retDT['year'].'-'.substr($retDT['month'],0,2).'-'.substr($retDT['day'],0,2);
							} else $dateTime[]='%';
						}
						else {
							if(
								preg_match("/(?<day>\d{1,2})\/(?<month>\d{1,2})\/(?<year>\d{2,4})/",$t,$retDT) ||
								preg_match("/(?<year>\d{2,4})-(?<month>\d{1,2})-(?<day>\d{1,2})/",$t,$retDT)
							) {
								$dateTime[]=substr(date('Y'),0,4-strlen($retDT['year'])).$retDT['year'].'-'.str_pad($retDT['month'],2,'0').'-'.str_pad($retDT['day'],2,'0');
							} else $dateTime[]='0000-00-00';
						}
					}
					if(preg_match('/(time|stamp)/',@$field['type'])) {
						if(@$field['type']=='time') $t=$val;
						elseif(preg_match('/ (.*)/',$val,$retDT) || preg_match('/(?[\d_%:]*:?[\d_%:]*)/',$val,$retDT)) $t=$retDT[1];
						else $t='';
						if($sign==' LIKE '){
							if(preg_match('/(?[\d_%]*):?([\d_%]*):?([\d_%]+)/',$t,$retDT)) {
								$dateTime[]=implode(':',preg_replace(array('/_/','/^(\d)$/','/^$/'),array('%','0\1','%'),$retDT));
							} else $dateTime[]='%';
						}
						else {
							if(preg_match('/(\d*):?(\d*):?(\d+)/',$t,$retDT)) {
								$dateTime[]=implode(':',preg_replace(array('/^(\d)$/','/^$/'),array('0\1','00'),$retDT));
							} else $dateTime[]='00:00:00';
						}
					}
				}
				$out.="$key{$not}{$sign}'$val'";
			}
		}
		return $out;
	}
	function parserCondition($value){
		$w=array();
		$sign=$separator='';
		if($value) {
			if(preg_match_all('/(?:(?<!\\\)([|&])(!=?|<[>=]?|>=?|==?)?)|(?<!\\\)(!=?|<[>=]?|>=?|=)/', $value, $ret, PREG_OFFSET_CAPTURE+PREG_SET_ORDER)) {
				$ret=array_reverse($ret);
				foreach($ret as $pos){
					$separator=@$pos[1][0];
					($sign=@$pos[2][0]) || ($sign=@$pos[3][0]);
					$sign=strtr($sign,array('<>'=>'!='));
					$content=strtr(substr($value,$pos[0][1]+strlen($pos[0][0])),array('\&'=>'&','\|'=>'|','\!'=>'!','\>'=>'>','\<'=>'<','\='=>'=',));
					$value=substr_replace($value,'',$pos[0][1]);
					if(preg_match('/^\*+$/',$content) && $sign=='') {
						if($separator=='|' || $separator=='') return false;
					} else $w[]=compact('separator','sign','content');
				}
			} 
			if($separator=='&' || $separator=='|') $w[]=array('separator'=>'','sign'=>'','content'=>$value);
			$w=array_reverse($w);
		} else $w[]=array('separator'=>'','sign'=>'','content'=>'');
		return $w;
	}
	private function getFieldLastArg(&$fullField){
		if(!(preg_match('/\.?`([^`]+)`$/',$fullField,$ret) || preg_match('/\.([^ ]+)$/',$fullField,$ret))) return;
		$fullField=substr($fullField,0,-1*strlen($ret[0]));
		return $ret[1];
	}
	function fields(){
		$sql=preg_replace('/(\bLIMIT)(\s+\d+(?:\s*,\d+)?\s*)$/i','\1 0',$this->protect['sqlFull']);
		if($sql==$this->protect['sqlFull']) $sql.="\nLIMIT 0";
		$conn=$this->conn;
		$res=$conn->query($sql,false);
		$error=$this->res->error();
		if($error) {
			$sql="SELECT * FROM (\n{$this->protect['sqlFull']}\n) t";
			$res=$conn->query($sql.' limit 0',false);
			if($this->res->error()) return $error;
			$this->protect['sqlFull']=$sql;
		}
		$r=$res->res;
		$out=@$r->fetch_fields();
		if($out) foreach($out as $index=>$obj) {
			//$obj->vartype=$res->trNumType($obj);
			$obj->details=$this->getField(($obj->orgtable?$obj->orgtable.'.':'').$obj->orgname);
		}
		return $out;
	}
	function getDependences($query,$item='query',$dbNow=false){
		static $results=array();
		$conn=$this->conn;
		$dsn=$conn->dsn['dsnName'];
		if(!$dbNow) $dbNow=$conn->get_database();
		if($item=='query') {
			$key=$dsn.':'.md5($query).'#'.strlen($query);
			if(!array_key_exists($key,$results)){
			}
		}elseif($item=='query') {
		}
	}
	function getStatusTable($db,$tbl,$dbNow=false,$item='query'){
		static $results=array();
		$conn=$this->conn;
		$dsn=$conn->dsn['dsnName'];
		$fullName="`$db`.`$tbl`";
		$key="{$dsn}/databases/{$db}/tables/{$tbl}.cfg";
		if(!array_key_exists($key,$results)) {
			$line=$conn->fastLine("SHOW TABLE STATUS FROM `$db` LIKE '$tbl'");
			if($line) {
				$cfg=$this->getFileConf($key);
				$results[$key]=array(
					'DataBase'=>$db,
					'Name'=>$tbl,
					'FullName'=>$fullName,
					'Engine'=>$line['Engine'],
					'Version'=>$line['Version'],
					'RecordCount'=>$line['Rows'],
					'DtGer'=>$line['Create_time'],
					'DtUpdate'=>$line['Update_time'],
					'DtMaintenance'=>$line['Check_time'],
					'Charset'=>$line['Collation'],
					'Comment'=>$line['Comment'],
					'Auto_increment'=>$line['Auto_increment'],
				);
				if($cfg['DtUpdate']<$results[$key]['DtUpdate']) {//FIXME gerar informações deste bloco 
					$results[$key]['DDL']='';
					$results[$key]['Options']='';
					$results[$key]['Fields']=array();
					$results[$key]['Dependences']=array('tables'=>array(),'views'=>array(),'functions'=>array(),'procedores'=>array(),'events'=>array(),);
				}
			}
			elseif($this->version>=5) {
				$line=$conn->fastLine("SHOW CREATE VIEW $fullName");
				if($line) {
					$view=$line['Create View'];
					$cfg=$this->getFileConf($key);
					$hash=md5($view);
					if(!$dbNow) $dbNow=$conn->get_database();
					$tables=$this->getDependencesOfQuery($view,$dbNow);
					$dtUpdate='';
					foreach($tables as $v) {
						$c=$this->getStatusTable($v['db'],$v['tbl'],$dbNow);
						if($c['DtUpdate']>$dtUpdate) $dtUpdate=$c['DtUpdate'];
					}
					$results[$key]=array(
						'hash'=>$hash,
						'tables'=>$tables,
					);
					//if(!$cfg || $cfg['hash']!=$hash || $cfg['DtUpdate']<$results[$key]['DtUpdate']) getView
				}
				else $results[$key]=false;
			}
			else $results[$key]=false;
		}
		return $results[$key];
	}
	function getFileConf($file){
		$fullName=$this->tmpDir.'/'.$file;
		if(!file_exists($fullName)) return false;
		return unserialize(file_get_contents($fullName));
	}
	function setFileConf($file,$aValues){
		$fullName=$this->tmpDir.'/'.$file;
		$path=dirname($fullName);
		`mkdir -p "$path"`;
		file_put_contents($fullName,serialize($aValues));
	}
	function splitFullName($fullName){
		preg_match('/`([^`]+)`(?:\.`([^`]+)`)?/',$fullName,$ret);
		return array('db'=>@$ret[1],'name'=>@$ret[2]);
	}
	private function getDependenceitem($fullName=false,$item='view'){
		if(!($aSql=$this->shiftItem($item,$fullName))) return false;
		//print "$item: $fullName\n";
		$aSql=(array)$aSql;
		$tbl=$this->splitFullName($fullName);
		$aCases=array(
			array('table',"/CONSTRAINT\b.*\bFOREIGN KEY\b.*\bREFERENCES\b\s*{$this->erFullTable}/i"),
			array('function',"/{$this->erFullTable}\s*\(/i"),
			array('procedure',"/call\b\s*{$this->erFullTable}/i"),
			array('view',"/(?:FROM|JOIN)(?:\(|\s)+{$this->erFullTable}/i"),
		);
		foreach($aSql as $sql) foreach($aCases as $cases) if (preg_match_all($cases[1],$sql,$ret,PREG_SET_ORDER)) foreach($ret as $v){
			$dbProc=$tbl['db'];
			$tbProc=$v[3];
			if($v[1]) $dbProc=$v[1];
			elseif(@$v[2]) $dbProc=$v[2];
			elseif(@$v[4] && preg_match($this->erWR,$v[4])) continue;
			if(!$tbProc) $tbProc=$v[4];
			if($cases[0]==$item && $dbProc==$tbl['db'] && $tbProc==$tbl['name']) continue;
			//print "$item: $fullName => {$cases[0]}: `$dbProc`.`$tbProc`\n";
			$this->getOrder_items($cases[0],"`$dbProc`.`$tbProc`");
		}
		//print "$item: $fullName\n";
		if($item=='tables' && $this->paramTables && $this->dropTable && !$this->paramData) array_unshift($aSql,"DROP TABLE IF EXISTS $fullTable;\n");
		$aSql=$this->replaceDb($aSql);
		if($db=$this->useDB($tbl['db'])) array_unshift($aSql,$db);
		$this->elementOrder[preg_replace('/s$/','',$item).($fullName?": $fullName":'')]=array(
			'item'=>$item,
			'sql'=>$aSql,
			'fullName'=>$fullName,
		);
		return true;
	}
}