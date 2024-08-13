<?php
class Conn_mysqli_details extends Conn_details {
	//private $all['fieldsKey'];
	private $tr=[];
	protected $reservedWords='ACCESSIBLE|ACTION|ADD|AFTER|AGAINST|AGGREGATE|ALGORITHM|ALL|ALTER|ANALYZE|AND|ANY|AS|ASC|ASCII|ASENSITIVE|AT|AUTHORS|AUTOEXTEND_SIZE|AUTO_INCREMENT|AVG|AVG_ROW_LENGTH|BACKUP|BEFORE|BEGIN|BETWEEN|BIGINT|BINARY|BINLOG|BIT|BLOB|BLOCK|BOOL|BOOLEAN|BOTH|BTREE|BY|BYTE|CACHE|CALL|CASCADE|CASCADED|CASE|CATALOG_NAME|CHAIN|CHANGE|CHANGED|CHAR|CHARACTER|CHARSET|CHECK|CHECKSUM|CIPHER|CLASS_ORIGIN|CLIENT|CLOSE|COALESCE|CODE|COLLATE|COLLATION|COLUMN(?:S|_NAME)?|COMMENT|COMMIT|COMMITTED|COMPACT|COMPLETION|COMPRESSED|CONCURRENT|CONDITION|CONNECTION|CONSISTENT|CONSTRAINT|CONSTRAINT_CATALOG|CONSTRAINT_NAME|CONSTRAINT_SCHEMA|CONTAINS|CONTEXT|CONTINUE|CONTRIBUTORS|CONVERT|CPU|CREATE|CROSS|CUBE|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|CURSOR_NAME|DATA|DATABASE|DATABASES|DATAFILE|DATE|DATETIME|DAY|DAY_HOUR|DAY_MICROSECOND|DAY_MINUTE|DAY_SECOND|DEALLOCATE|DEC|DECIMAL|DECLARE|DEFAULT|DEFINER|DELAYED|DELAY_KEY_WRITE|DELETE|DESC|DESCRIBE|DES_KEY_FILE|DETERMINISTIC|DIRECTORY|DISABLE|DISCARD|DISK|DISTINCT|DISTINCTROW|DIV|DO|DOUBLE|DROP|DUAL|DUMPFILE|DUPLICATE|DYNAMIC|EACH|ELSE|ELSEIF|ENABLE|ENCLOSED|ENDS?|ENGINES?|ENUM|ERRORS?|ESCAPE|ESCAPED|EVENT|EVENTS|EVERY|EXECUTE|EXISTS|EXIT|EXPANSION|EXPLAIN|EXTENDED|EXTENT_SIZE|FALSE|FAST|FAULTS|FETCH|FIELDS|FILE|FIRST|FIXED|FLOAT[48]?|FLUSH|FOR|FORCE|FOREIGN|FOUND|FRAC_SECOND|FROM|FULL|FULLTEXT|FUNCTION|GENERAL|GEOMETRY|GEOMETRYCOLLECTION|GET_FORMAT|GLOBAL|GRANTS?|GROUP|HANDLER|HASH|HAVING|HELP|HIGH_PRIORITY|HOSTS?|HOUR|HOUR_MICROSECOND|HOUR_MINUTE|HOUR_SECOND|IDENTIFIED|IF|IGNORE|IGNORE_SERVER_IDS|IMPORT|IN|INDEX|INDEXES|INFILE|INITIAL_SIZE|INNER|INNOBASE|INNODB|INOUT|INSENSITIVE|INSERT|INSERT_METHOD|INSTALL|INT[12348]?|INTEGER|INTERVAL|INTO|INVOKER|IO|IO_THREAD|IPC|IS|ISOLATION|ISSUER|ITERATE|JOIN|KEY|KEYS|KEY_BLOCK_SIZE|KILL|LANGUAGE|LAST|LEADING|LEAVES?|LEFT|LESS|LEVEL|LIKE|LIMIT|LINEAR|LINES|LINESTRING|LIST|LOAD|LOCAL|LOCALTIME|LOCALTIMESTAMP|LOCKS?|LOGFILE|LOGS|LONG|LONGBLOB|LONGTEXT|LOOP|LOW_PRIORITY|MASTER|MASTER_CONNECT_RETRY|MASTER_HEARTBEAT_PERIOD|MASTER_HOST|MASTER_LOG_FILE|MASTER_LOG_POS|MASTER_PASSWORD|MASTER_PORT|MASTER_SERVER_ID|MASTER_SSL|MASTER_SSL_CA|MASTER_SSL_CAPATH|MASTER_SSL_CERT|MASTER_SSL_CIPHER|MASTER_SSL_KEY|MASTER_SSL_VERIFY_SERVER_CERT|MASTER_USER|MATCH|MAXVALUE|MAX_CONNECTIONS_PER_HOUR|MAX_QUERIES_PER_HOUR|MAX_ROWS|MAX_SIZE|MAX_UPDATES_PER_HOUR|MAX_USER_CONNECTIONS|MEDIUM|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MEMORY|MERGE|MESSAGE_TEXT|MICROSECOND|MIDDLEINT|MIGRATE|MINUTE(?:_(?:MICRO)?SECOND)?|MIN_ROWS|MOD|MODE|MODIF(?:Y|IES)|MONTH|MULTI(?:LINESTRING|POINT|POLYGON)|MUTEX|MYSQL_ERRNO|NAME|NAMES|NATIONAL|NATURAL|NCHAR|NDB|NDBCLUSTER|NEW|NEXT|NO|NODEGROUP|NONE|NOT|NO_WAIT|NO_WRITE_TO_BINLOG|NULL|NUMERIC|NVARCHAR|OFFSET|OLD_PASSWORD|ON|ONE|ONE_SHOT|OPEN|OPTIMIZE|OPTION|OPTIONALLY|OPTIONS|OR|ORDER|OUT|OUTER|OUTFILE|OWNER|PACK_KEYS|PAGE|PARSER|PARTIAL|PARTITION|PARTITIONING|PARTITIONS|PASSWORD|PHASE|PLUGIN|PLUGINS|POINT|POLYGON|PORT|PRECISION|PREPARE|PRESERVE|PREV|PRIMARY|PRIVILEGES|PROCEDURE|PROCESSLIST|PROFILE|PROFILES|PROXY|PURGE|QUARTER|QUERY|QUICK|RANGE|READ|READS|READ_ONLY|READ_WRITE|REAL|REBUILD|RECOVER|REDOFILE|REDO_BUFFER_SIZE|REDUNDANT|REFERENCES|REGEXP|RELAY|RELAYLOG|RELAY_LOG_FILE|RELAY_LOG_POS|RELAY_THREAD|RELEASE|RELOAD|REMOVE|RENAME|REORGANIZE|REPAIR|REPEAT|REPEATABLE|REPLACE|REPLICATION|REQUIRE|RESET|RESIGNAL|RESTORE|RESTRICT|RESUME|RETURN|RETURNS|REVOKE|RIGHT|RLIKE|ROLLBACK|ROLLUP|ROUTINE|ROW|ROWS|ROW_FORMAT|RTREE|SAVEPOINT|SCHEDULE|SCHEMA|SCHEMAS|SCHEMA_NAME|SECOND|SECOND_MICROSECOND|SECURITY|SELECT|SENSITIVE|SEPARATOR|SERIAL|SERIALIZABLE|SERVER|SESSION|SET|SHARE|SHOW|SHUTDOWN|SIGNAL|SIGNED|SIMPLE|SLAVE|SLOW|SMALLINT|SNAPSHOT|SOCKET|SOME|SONAME|SOUNDS|SOURCE|SPATIAL|SPECIFIC|SQL|SQLEXCEPTION|SQLSTATE|SQLWARNING|SQL_BIG_RESULT|SQL_BUFFER_RESULT|SQL_CACHE|SQL_CALC_FOUND_ROWS|SQL_NO_CACHE|SQL_SMALL_RESULT|SQL_THREAD|SQL_TSI_DAY|SQL_TSI_FRAC_SECOND|SQL_TSI_HOUR|SQL_TSI_MINUTE|SQL_TSI_MONTH|SQL_TSI_QUARTER|SQL_TSI_SECOND|SQL_TSI_WEEK|SQL_TSI_YEAR|SSL|START|STARTING|STARTS|STATUS|STOP|STORAGE|STRAIGHT_JOIN|STRING|SUBCLASS_ORIGIN|SUBJECT|SUBPARTITION|SUBPARTITIONS|SUPER|SUSPEND|SWAPS|SWITCHES|TABLE|TABLES|TABLESPACE|TABLE_CHECKSUM|TABLE_NAME|TEMPORARY|TEMPTABLE|TERMINATED|TEXT|THAN|THEN|TIME|TIMESTAMP|TIMESTAMPADD|TIMESTAMPDIFF|TINYBLOB|TINYINT|TINYTEXT|TO|TRAILING|TRANSACTION|TRIGGER|TRIGGERS|TRUE|TRUNCATE|TYPE|TYPES|UNCOMMITTED|UNDEFINED|UNDO|UNDOFILE|UNDO_BUFFER_SIZE|UNICODE|UNINSTALL|UNION|UNIQUE|UNKNOWN|UNLOCK|UNSIGNED|UNTIL|UPDATE|UPGRADE|USAGE|USE|USER|USER_RESOURCES|USE_FRM|USING|UTC_DATE|UTC_TIME|UTC_TIMESTAMP|VALUE|VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARIABLES|VARYING|VIEW|WAIT|WARNINGS|WEEK|WHEN|WHERE|WHILE|WITH|WORK|WRAPPER|WRITE|X509|XA|XML|XOR|YEAR|YEAR_MONTH|ZEROFILL';
	
	protected function getVersion(){
		$conn=$this->conn();
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
		if(is_null($value)) $value=$this->get_backtrace('sql');
		if(!$value) return;
		$this->protect['sql']=$value;
		$this->all['fieldsKey']=[];
		$value=$this->strip($value);
		$value=preg_split('/\s*;\s*/',$value);
		$er=$this->getErFullTblOnly();
		$sp=[];
		foreach ($value as $k=>$q) {
			$q=trim($q);
			if(!$q) continue;
			if(!preg_match('/^(?!select)/i',$q)) {
				if (preg_match($er,$q)) $q="SELECT * FROM $q";
				elseif (preg_match('/^select\s+(.|\s)+\bunion\b/i',$q)) $q="SELECT * FROM ($q) as t";
			}
			$sp[]=$q;
		}
		$sp=$this->unstrip($sp);
		$this->protect['sqlFull']=$sp;
		$this->parseQuery($sp,($conn=$this->conn())?$conn->get_database():null);
		//exit;
		//$this->parseQuery($value);
	}
	public function resume($sql){
		if(!$sql) $sql=$this->sql;
		$sql=$this->strip($sql);

		//strip `
		if(preg_match_all('/(`)([^`]*?)\1/',$sql,$ret,PREG_SET_ORDER|PREG_OFFSET_CAPTURE)) while($ret) {
			$line=array_pop($ret);
			if(preg_match('/^\w+$/',$line[2][0]) && !preg_match('/^('.$this->reservedWords.')$/',$line[2][0])) $sql=substr_replace($sql,$line[2][0],$line[0][1],strlen($line[0][0]));
		}
		//replace '"
		if(preg_match_all('/([\'"])((?:.|\s)*?)\1/',$sql,$ret,PREG_SET_ORDER|PREG_OFFSET_CAPTURE)) while($ret) {
			$line=array_pop($ret);
			$this->replaceObj($sql,$line[2][1],$line[2][0]);
		}
		//replace ()
		while(preg_match('/\(([^\(\)]*?)\)/',$sql,$line,PREG_OFFSET_CAPTURE)) $this->replaceObj($sql,$line[0][1],$line[0][0],'[',']');
		$sql=$this->stripStatements($sql);
		$sql=$this->stripCreate($sql);
		
		$sql=preg_split('/\s*;\s*/',$sql);
		
		foreach($sql as $k=>$v) {
			$o=$this->buildObject($v);
			if($o===false) continue;
			$this->obj[]=$o;
			$sql[$k]=$o->resume();
		}
		
		$sql=implode(";\n",$sql);
		//$sql=$this->unstrip($sql);
		
		return $sql;
	}
	protected function buildObject($sql){
		$sql=trim($sql);
		if(!$sql) return false;
		if(preg_match('/^\[#(\d+)\]/',$sql,$ret)) return $this->buildObject($this->tr[$ret[1]]);
		if(preg_match('/^select\b/',$sql,$ret)) return new Detail_Query($sql,$this->tr);
		return false;
	}
	protected function stripCreate($sql){
		while(preg_match('/\b(CREATE)\s+((?:.|\s)+?)\b(END(?!`)\b\s*)/i',$sql,$line,PREG_OFFSET_CAPTURE)) {
			if(preg_match('/\bCREATE(?!`)\b/i',$line[2][0],$ret,PREG_OFFSET_CAPTURE)){
				$content=$line[2][0].$line[3][0];
				$sub=$this->stripCreate($content);
				if($content==$sub) $this->replaceObj($sql,$line[3][1],$line[3][0],'[',']');
				else $sql=substr_replace($sql,$sub,$line[2][1],strlen($content));
			}
			else $this->replaceObj($sql,$line[0][1],$line[0][0],'[',']');
		}
		return $sql;
	}
	protected function stripStatements($sql){
		while(preg_match('/\s((\w+)\s*:\s*)?\b(CASE|IF|LOOP|REPEAT|WHILE)\b\s*((?:.|\s)+?)\b(END\s+\3(?:\s+\2)?\s*)/i',$sql,$line,PREG_OFFSET_CAPTURE)) {
			if(preg_match('/\bEND\s+/i',$line[4][0],$ret,PREG_OFFSET_CAPTURE)){
				$content=$line[4][0].$line[5][0];
				$sub=$this->stripStatements($content);
				if($content==$sub) $this->replaceObj($sql,$line[5][1],$line[5][0],'[',']');
				else $sql=substr_replace($sql,$sub,$line[4][1],strlen($content));
			}
			else $this->replaceObj($sql,$line[0][1],$line[0][0],'[',']');
		}
		return $sql;
	}
	protected function strip($sql){
		/*
			$sql=preg_replace(
			array('/\\\\/',     "/['\\\]'/",   '/["\\\]"/', '/\s*\/\*(?:.|\s)*?\*\//', ),
			array($contraBarra, $aspasSimples, $aspasDupla, '',                        ),
			$sql
			);
		*/
		$p=$this->pontoVirgula;
		$sql=preg_replace(
			array(
				'/(\r\n|\n\r)/',                           //1
				'/\\\\\\\/',                               //2
				"/['\\\]'/",                               //3
				'/["\\\]"/',                               //4
				'/(?:(\n+)[\t ]*)?\/\*(?:.|\s)*?\*\//',    //5
				'/(?:(\n+)[\t ]*)?(?:--|#).*?\n+/',        //6
				'/\n\s*(?:--|#)[^\n]*$/',                  //7
				'/\n{2,}/',                                //8
				//'/(["\'])([^"\']*?)\1/e',                  //9
			),
			array(
				"\n",                                      //1
				$this->contraBarra,                        //2
				$this->aspasSimples,                       //3
				$this->aspasDupla,                         //4
				'\1',                                      //5
				'\1',                                      //6
				'',                                        //7
				"\n",                                      //8
				//"'\\1'.str_replace(';',\$pontoVirgula,'\\2').'\\1'",  //9
			),
			$sql
		);
		return $sql;
	}
	protected function unstrip($sql){
		return str_replace(
			array(
				$this->contraBarra,
				$this->aspasSimples,
				$this->aspasDupla,
				$this->pontoVirgula,
			),
			array(
				'\\\\',
				"''",
				'""',
				';',
			),
			$sql
		);
	}
	protected function replaceObj(&$text,$pos,$from,$start='',$end=''){
		$c=count($this->tr);
		$r=$start.'#'.$c.$end;
		$text=substr_replace($text,$r,$pos,strlen($from));
		$this->tr[$c]=$from;
	}
	private function parseQuery($view,$dbNow){
		if(is_array($view)) {
			foreach($view as $v) $this->parseQuery($v,$dbNow);
			return;
		}
		$ret=$this->getDependencesOfQuery($view,$dbNow);
		//print __LINE__.": $view\n"; print_r($ret);
		if(@$ret['Table|View']) foreach($ret['Table|View'] as $nm=>$l) {
			$this->protect['tables'][$nm]=$this->getShowTable($nm,$l['DataBase'],$l['Name'],$l['Alias']);
			$this->all['databases'][$l['DataBase']][$l['Name']]=$nm;
			$this->all['tables'][$l['Name']][$l['DataBase']]=$nm;
			$this->all['alias'][$l['Alias']]=$nm;
		}
	}
	protected function getDependencesOfQuery($view,$dbNow){
		//$v=$this->version;
		$c=$this->getErTbl();
		$erFullTable=$this->getErFullTbl();
		$erAlias=$this->getErTblAlias();
		$conn=$this->conn();
		//$view=$this->sqlFull;
		$out=[];
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
		static $results=[];
		$conn=$this->conn();
		$dsn="$conn";
		$key=$dsn.':'.$fullName;
		if(!isset($results[$key])) {
			$fields=$keys=$constraints=$properties=[];
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
						$this->all['fields'][$ret['field']][$fullName]=$alias;
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
					$this->all['fields'][$line['COLUMN_NAME']][$fullName]=$alias;
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
		return strtr($str,array('\r'=>"\r",'\n'=>"\n",'\t'=>"\t","''"=>"'",'""'=>'"',));
	}
	private function getSourceSetEnum($type,&$length){
		$list=preg_replace(array('/^(set|enum)\(/i','/\)$/'),'',$type);
		$source=[];
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
		$where=[];
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
		if(!array_key_exists($fff,@$this->all['fieldsKey'])) {
			$field=$this->getFieldLastArg($fullField);
			if(!$field) return $this->all['fieldsKey'][$fff]=false;
			$table=$this->getFieldLastArg($fullField);
			if(!$table) {
				$tmp=@$this->all['fields'][$field];
				if(!$tmp) return $this->all['fieldsKey'][$fff]=false;
				$table=key($tmp);
				return $this->all['fieldsKey'][$fff]=@$this->protect['tables'][$table]['fields'][$field];
			}
			$db=$this->getFieldLastArg($fullField);
			if($db && ($f=@$this->protect['tables']["`$db`.`$table`"]['fields'][$field])) return $this->all['fieldsKey'][$fff]=$f;
			$tmp=@$this->all['alias'][$table];
			if($tmp && ($f=@$this->protect['tables'][$tmp]['fields'][$field])) return $this->all['fieldsKey'][$fff]=$f;
			$tmp=@$this->all['tables'][$table];
			if(!$tmp) return $this->all['fieldsKey'][$fff]=false;
			$this->all['fieldsKey'][$fff]=@$this->protect['tables'][reset($tmp)]['fields'][$field];
		}
		return $this->all['fieldsKey'][$fff];
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
					$not=$cond[0]=='!'?' NOT':'';
					$out.="IFNULL($key,''){$not} IN ('','0000-00-00','0000-00-00 00:00:00','00:00:00')";
				}
				else {
					$not=$cond[0]=='!'?'!':'';
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
					$not=$cond[0]=='!'?'!':'';
					$sign='=';
				}
				if($isDateTime) {
					$dateTime=[];
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
								if($retDT['year'][0]!='%') $retDT['year']=substr(date('Y'),0,4-strlen($retDT['year'])).$retDT['year'];
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
		$w=[];
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
		$conn=$this->conn();
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
			$out[$index]->details=$this->getField(($obj->orgtable?$obj->orgtable.'.':'').$obj->orgname);
		}
		return $out;
	}
	function getDependences($query,$item='query',$dbNow=false){
		static $results=[];
		$conn=$this->conn();
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
		static $results=[];
		$conn=$this->conn();
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
					$results[$key]['Fields']=[];
					$results[$key]['Dependences']=array('tables'=>[],'views'=>[],'functions'=>[],'procedores'=>[],'events'=>[],);
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
	function shiftItem($item,$fullName){//Implementar
	} 
	function getOrder_items($case,$tb){//Implementar
	} 
	function replaceDb($aSql){//Implementar
		return [];
	} 
	function useDB($db){//Implementar
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
		if($item=='tables' && $this->paramTables && $this->dropTable && !$this->paramData) array_unshift($aSql,"DROP TABLE IF EXISTS $fullName;\n");
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

class Detail_Main{
	protected $sql,$tr;
	protected $itens=[];
	protected $objs=[];
	
	public function __construct($sql,$tr){
		$this->sql=$sql;
		$this->tr=$tr;
		$this->fragment();
	}
	protected function fragment(){}
	public function resume(){}
}
class Detail_Query extends Detail_Main {
	protected $itens=array(
		'flags'=>[],
		'fields'=>[],
		'tables'=>[],
		'partition'=>[],
		'where'=>[],
		'group'=>[],
		'having'=>[],
		'window'=>[],
		'order'=>[],
		'limit'=>[],
		'file'=>[],
		'for'=>[],
	);
	protected function fragment(){
		//todo verificar union all
		$sql=preg_replace('/^select\s*/','',$this->sql);
		//FLAGS
		while(preg_match('/^\b(ALL|DISTINCT|DISTINCTROW|HIGH_PRIORITY|STRAIGHT_JOIN|SQL_SMALL_RESULT|SQL_BIG_RESULT|SQL_BUFFER_RESULT|SQL_CACHE|SQL_NO_CACHE|SQL_CALC_FOUND_ROWS)\b\s*/i',$sql,$ret)) {
			$sql=substr($sql,strlen($ret[0]));
			$this->itens['flags'][]=strtoupper($ret[1]);
		}
		//FIELDS
		if(preg_match('/^((?:.|\s)*?)\bFROM(?!`)\b/i',$sql,$ret)){
			$sql=substr($sql,strlen($ret[1]));
			$fields=preg_split('/\s*,\s*/',$ret[1]);
			foreach($fields as $v){
				$v=$this->depureField($v);
				if($v) $this->itens['fields'][$v['key']]=$v['content'];
			}
		}
		$this->itens['rest']=$sql;
	}
	public function resume($tab=''){
		return print_r($this->itens,true);
	}
	protected function depureField($value){
		if(preg_match('/^(.*)\s+(?:as\s+)(`)([^`]+)\2$/i',$value,$ret)){
			$v=$ret[1];
			$k=$ret[3];
		}
		elseif(preg_match('/^(.*)\s+(?:as\s+)(\w+)$/i',$value,$ret)){
			$v=$ret[1];
			$k=$ret[2];
		}
		elseif(preg_match('/^(.*\.)?(`)([^`]+)\2$/i',$value,$ret)){
			$v=$ret[0];
			$k=$ret[3];
		}
		elseif(preg_match('/^(.*\.)?(\w+)$/i',$value,$ret)){
			$v=$ret[0];
			$k=$ret[2];
		}
		elseif(preg_match('/^\[#(\d+)\]$/i',$value,$ret)) return $this->depureField($this->tr[$ret[1]]);
		elseif(preg_match('/^\((.*)\)$/i',$value,$ret)) return $this->depureField($ret[1]);
		else return false;
		$v=$this->untrField($v);
		return array('key'=>$k,'content'=>$v);
	}
	protected function untrField($value){
		while(preg_match('/\[#(\d+)\]/i',$value,$ret)) {
			$value=str_replace($ret[0],$this->tr[$ret[1]],$value);
		}
		return $value;
	}
}
/*
ACCESSIBLE
ACTION
ADD
AFTER
AGAINST
AGGREGATE
ALGORITHM
ALL
ALTER
ANALYZE
AND
ANY
AS
ASC
ASCII
ASENSITIVE
AT
AUTHORS
AUTOEXTEND_SIZE
AUTO_INCREMENT
AVG
AVG_ROW_LENGTH
BACKUP
BEFORE
BEGIN
BETWEEN
BIGINT
BINARY
BINLOG
BIT
BLOB
BLOCK
BOOL
BOOLEAN
BOTH
BTREE
BY
BYTE
CACHE
CALL
CASCADE
CASCADED
CASE
CATALOG_NAME
CHAIN
CHANGE
CHANGED
CHAR
CHARACTER
CHARSET
CHECK
CHECKSUM
CIPHER
CLASS_ORIGIN
CLIENT
CLOSE
COALESCE
CODE
COLLATE
COLLATION
COLUMN(?:S|_NAME)?
COMMENT
COMMIT
COMMITTED
COMPACT
COMPLETION
COMPRESSED
CONCURRENT
CONDITION
CONNECTION
CONSISTENT
CONSTRAINT
CONSTRAINT_CATALOG
CONSTRAINT_NAME
CONSTRAINT_SCHEMA
CONTAINS
CONTEXT
CONTINUE
CONTRIBUTORS
CONVERT
CPU
CREATE
CROSS
CUBE
CURRENT_DATE
CURRENT_TIME
CURRENT_TIMESTAMP
CURRENT_USER
CURSOR
CURSOR_NAME
DATA
DATABASE
DATABASES
DATAFILE
DATE
DATETIME
DAY
DAY_HOUR
DAY_MICROSECOND
DAY_MINUTE
DAY_SECOND
DEALLOCATE
DEC
DECIMAL
DECLARE
DEFAULT
DEFINER
DELAYED
DELAY_KEY_WRITE
DELETE
DESC
DESCRIBE
DES_KEY_FILE
DETERMINISTIC
DIRECTORY
DISABLE
DISCARD
DISK
DISTINCT
DISTINCTROW
DIV
DO
DOUBLE
DROP
DUAL
DUMPFILE
DUPLICATE
DYNAMIC
EACH
ELSE
ELSEIF
ENABLE
ENCLOSED
ENDS?
ENGINES?
ENUM
ERRORS?
ESCAPE
ESCAPED
EVENT
EVENTS
EVERY
EXECUTE
EXISTS
EXIT
EXPANSION
EXPLAIN
EXTENDED
EXTENT_SIZE
FALSE
FAST
FAULTS
FETCH
FIELDS
FILE
FIRST
FIXED
FLOAT[48]?
FLUSH
FOR
FORCE
FOREIGN
FOUND
FRAC_SECOND
FROM
FULL
FULLTEXT
FUNCTION
GENERAL
GEOMETRY
GEOMETRYCOLLECTION
GET_FORMAT
GLOBAL
GRANTS?
GROUP
HANDLER
HASH
HAVING
HELP
HIGH_PRIORITY
HOSTS?
HOUR
HOUR_MICROSECOND
HOUR_MINUTE
HOUR_SECOND
IDENTIFIED
IF
IGNORE
IGNORE_SERVER_IDS
IMPORT
IN
INDEX
INDEXES
INFILE
INITIAL_SIZE
INNER
INNOBASE
INNODB
INOUT
INSENSITIVE
INSERT
INSERT_METHOD
INSTALL
INT[12348]?
INTEGER
INTERVAL
INTO
INVOKER
IO
IO_THREAD
IPC
IS
ISOLATION
ISSUER
ITERATE
JOIN
KEY
KEYS
KEY_BLOCK_SIZE
KILL
LANGUAGE
LAST
LEADING
LEAVES?
LEFT
LESS
LEVEL
LIKE
LIMIT
LINEAR
LINES
LINESTRING
LIST
LOAD
LOCAL
LOCALTIME
LOCALTIMESTAMP
LOCKS?
LOGFILE
LOGS
LONG
LONGBLOB
LONGTEXT
LOOP
LOW_PRIORITY
MASTER
MASTER_CONNECT_RETRY
MASTER_HEARTBEAT_PERIOD
MASTER_HOST
MASTER_LOG_FILE
MASTER_LOG_POS
MASTER_PASSWORD
MASTER_PORT
MASTER_SERVER_ID
MASTER_SSL
MASTER_SSL_CA
MASTER_SSL_CAPATH
MASTER_SSL_CERT
MASTER_SSL_CIPHER
MASTER_SSL_KEY
MASTER_SSL_VERIFY_SERVER_CERT
MASTER_USER
MATCH
MAXVALUE
MAX_CONNECTIONS_PER_HOUR
MAX_QUERIES_PER_HOUR
MAX_ROWS
MAX_SIZE
MAX_UPDATES_PER_HOUR
MAX_USER_CONNECTIONS
MEDIUM
MEDIUMBLOB
MEDIUMINT
MEDIUMTEXT
MEMORY
MERGE
MESSAGE_TEXT
MICROSECOND
MIDDLEINT
MIGRATE
MINUTE(?:_(?:MICRO)?SECOND)?
MIN_ROWS
MOD
MODE
MODIF(?:Y|IES)
MONTH
MULTI(?:LINESTRING|POINT|POLYGON)
MUTEX
MYSQL_ERRNO
NAME
NAMES
NATIONAL
NATURAL
NCHAR
NDB
NDBCLUSTER
NEW
NEXT
NO
NODEGROUP
NONE
NOT
NO_WAIT
NO_WRITE_TO_BINLOG
NULL
NUMERIC
NVARCHAR
OFFSET
OLD_PASSWORD
ON
ONE
ONE_SHOT
OPEN
OPTIMIZE
OPTION
OPTIONALLY
OPTIONS
OR
ORDER
OUT
OUTER
OUTFILE
OWNER
PACK_KEYS
PAGE
PARSER
PARTIAL
PARTITION
PARTITIONING
PARTITIONS
PASSWORD
PHASE
PLUGIN
PLUGINS
POINT
POLYGON
PORT
PRECISION
PREPARE
PRESERVE
PREV
PRIMARY
PRIVILEGES
PROCEDURE
PROCESSLIST
PROFILE
PROFILES
PROXY
PURGE
QUARTER
QUERY
QUICK
RANGE
READ
READS
READ_ONLY
READ_WRITE
REAL
REBUILD
RECOVER
REDOFILE
REDO_BUFFER_SIZE
REDUNDANT
REFERENCES
REGEXP
RELAY
RELAYLOG
RELAY_LOG_FILE
RELAY_LOG_POS
RELAY_THREAD
RELEASE
RELOAD
REMOVE
RENAME
REORGANIZE
REPAIR
REPEAT
REPEATABLE
REPLACE
REPLICATION
REQUIRE
RESET
RESIGNAL
RESTORE
RESTRICT
RESUME
RETURN
RETURNS
REVOKE
RIGHT
RLIKE
ROLLBACK
ROLLUP
ROUTINE
ROW
ROWS
ROW_FORMAT
RTREE
SAVEPOINT
SCHEDULE
SCHEMA
SCHEMAS
SCHEMA_NAME
SECOND
SECOND_MICROSECOND
SECURITY
SELECT
SENSITIVE
SEPARATOR
SERIAL
SERIALIZABLE
SERVER
SESSION
SET
SHARE
SHOW
SHUTDOWN
SIGNAL
SIGNED
SIMPLE
SLAVE
SLOW
SMALLINT
SNAPSHOT
SOCKET
SOME
SONAME
SOUNDS
SOURCE
SPATIAL
SPECIFIC
SQL
SQLEXCEPTION
SQLSTATE
SQLWARNING
SQL_BIG_RESULT
SQL_BUFFER_RESULT
SQL_CACHE
SQL_CALC_FOUND_ROWS
SQL_NO_CACHE
SQL_SMALL_RESULT
SQL_THREAD
SQL_TSI_DAY
SQL_TSI_FRAC_SECOND
SQL_TSI_HOUR
SQL_TSI_MINUTE
SQL_TSI_MONTH
SQL_TSI_QUARTER
SQL_TSI_SECOND
SQL_TSI_WEEK
SQL_TSI_YEAR
SSL
START
STARTING
STARTS
STATUS
STOP
STORAGE
STRAIGHT_JOIN
STRING
SUBCLASS_ORIGIN
SUBJECT
SUBPARTITION
SUBPARTITIONS
SUPER
SUSPEND
SWAPS
SWITCHES
TABLE
TABLES
TABLESPACE
TABLE_CHECKSUM
TABLE_NAME
TEMPORARY
TEMPTABLE
TERMINATED
TEXT
THAN
THEN
TIME
TIMESTAMP
TIMESTAMPADD
TIMESTAMPDIFF
TINYBLOB
TINYINT
TINYTEXT
TO
TRAILING
TRANSACTION
TRIGGER
TRIGGERS
TRUE
TRUNCATE
TYPE
TYPES
UNCOMMITTED
UNDEFINED
UNDO
UNDOFILE
UNDO_BUFFER_SIZE
UNICODE
UNINSTALL
UNION
UNIQUE
UNKNOWN
UNLOCK
UNSIGNED
UNTIL
UPDATE
UPGRADE
USAGE
USE
USER
USER_RESOURCES
USE_FRM
USING
UTC_DATE
UTC_TIME
UTC_TIMESTAMP
VALUE
VALUES
VARBINARY
VARCHAR
VARCHARACTER
VARIABLES
VARYING
VIEW
WAIT
WARNINGS
WEEK
WHEN
WHERE
WHILE
WITH
WORK
WRAPPER
WRITE
X509
XA
XML
XOR
YEAR
YEAR_MONTH
ZEROFILL
*/