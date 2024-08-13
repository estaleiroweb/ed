<?php
class Conn_mysqli_details_query extends Conn_mysqli_details_main {
	protected $deltaUpdate=1800;  /*segundos 1800seg=30min*/
	private $_query;
	private $_bouderauxAspas='•';     //149
	private $_bouderauxParenteses='¤';//164
	private $_contraBarra='——';       //151
	private $_aspasSimples='””';      //148
	private $_aspasDupla='““';        //147
	private $_pontoVirgula='¡';       //161
	private $erFullTableAlias='(?:(?:`(?:[^`]+)`|(?:\w+))\.)?(?:`(?:[^`]+)`|(?:\w+))(?:\s*(?:\bas\s+)?(?:`(?:[^`]+)`|(?:\w+)))?';
	private $_vars=array();
	function __construct($fullName,$force=false){ 
		$this->_query=$fullName;
		parent::__construct(md5($fullName).'_'.strlen($fullName),$force); 
	}
	protected function getFullName(){ return "`{$this->readonly['db']}`.[{$this->readonly['name']}]"; }
	protected function getStatus(){ return true; }
	//protected function renameDDL($ddl,$newFullName) { return preg_replace('/(CREATE\b.*?\bVIEW\b)(\s*.*?\s*)(\bAS\b\s*)/',"\\1 $newFullName \\3\n","$ddl;\n"); }
	protected function rebuildObj(){
		$this->readonly['raw']=$this->_query;
		if(!$this->_query) return;
		$query=preg_split('/\s*;\s*/',
			preg_replace(
				array('/\r\n/', '/\r/', '/\\\\/',            "/['\\\]'/",          '/["\\\]"/',        '/(?:(\n+)[\t ]*)?\/\*(?:.|\s)*?\*\//', '/(?:(\n+)[\t ]*)?(?:--|#).*?\n+/', '/\n\s*(?:--|#)[^\n]*$/', '/\n{2,}/', '/(["\'])([^"\']*?)\1/e',                            ),
				array('\n',     '\n',   $this->_contraBarra, $this->_aspasSimples, $this->_aspasDupla, '\1',                                   '\1',                               '',                       "\n",       "'\\1'.str_replace(';',\$this->_pontoVirgula,'\\2').'\\1'", ),
				$this->_query
			)
		);
		//print_r($query);exit;
		//foreach($query as $k=>$v) $query[$k]=$this->resumeQuery($v);
		$sp=array();
		foreach($query as $k=>$v) {
			if(!($v=trim($v))) continue;
			$sp[$k]=$this->resumeQuery($v);
			if(preg_match('/^(select)/i',$v)) {
				if (preg_match('/^select\s+(.|\s)+\bunion\b/i',$v)) $v="SELECT * FROM ($v) as t";
			}
			elseif (preg_match($this->erFullTableAlias,$v)) $v="SELECT * FROM $v";
			//$sp[$k]['full']=$v;
		}
		/*
		$this->protect['sqlFull']=$sp;
		$this->parseQuery($sp,$this->conn->get_database());
		*/
		$this->readonly['query']=$sp;
		return true;
	}
	protected function resumeQuery($query){
		$out=array();
		while(preg_match('/[bh]?([\'"])(.|\s)*?\1/',$query,$ret)) {
			$query=str_replace($ret[0],$this->_bouderauxAspas.count($out).$this->_bouderauxAspas,$query);
			$out[]=$ret[0];
		}
		while(preg_match('/\([^\(\))]*?\)/',$query,$ret)) {
			$query=str_replace($ret[0],'['.count($out).']',$query);
			$out[]=$ret[0];
		}
		$out[]=$query;
		while($pp=preg_grep('/\(\[\d+\]\)/',$out)) {
			$k=key($pp);$v=current($pp);unset($out[$k]);
			$out=str_replace("[$k]",str_replace(array('(',')'),'',$v),$out);
		}
		//$out=preg_replace('/^\(((?:$er|\[\d+\])\s*)((?:(?:inner | cross |(natural )?(?:left |right ))(outer )?|straight_)?join\s*$er\s*(?:on(?:.|\s)+?))\)$/i',' \1\n\2 ',$out);
		//'/($er)/i'
		//print_r($out);
		$this->_vars=$out;
		$out=$this->returnSpecialChar($this->identQuery($out));
		return $out;
	}
	protected function identQuery($query){
		if(is_array($query)){
			foreach($query as $k=>$v) $query[$k]=$this->identQuery($v);
			return $query;
		}
		$er1='((?:'.$this->erFullTableAlias.'|\[\d+\])\s*)';
		$erJoin='(?:(?:inner | cross |(natural )?(?:left |right ))(outer )?|straight_)?join\s*';
		$erOnUsing='(?:\s*(?:on|using)(?:.|\s)+?)?';
		$er2='('.$erJoin.$this->erFullTableAlias.$erOnUsing.')';
		$query=preg_replace('/^\('.$er1.$er2.'\)$/i'," \\1\n\\2 ",$query);
		$er=array('/^select\b\s*');
		$er[]=
	'(?<options>'.
				'(?:(?:ALL|DISTINCT|DISTINCTROW)\b\s*)?'.
				'(?:HIGH_PRIORITY\b\s*)?'.
				'(?:STRAIGHT_JOIN\b\s*)?'.
				'(?:SQL_(?:SMALL|BIG|BUFFER)_RESULT\b\s*)?'.
				'(?:SQL_(?:(?:NO_)?CACHE|CALC_FOUND_ROWS)\b\s*)?'.
				')?';
		$er[]='(?<fields>(?:.|\s)+?)';
		$er[]='(?<into>\bINTO\b(?:.|\s)+?)?';
		$er[]='(?:';
		$er[]='(?<from>\bFROM\b(?:.|\s)+?)';
		$er[]='(?<where>\bWHERE\b(?:.|\s)+?)?';
		$er[]='(?<group>\bGROUP\s+BY\b(?:.|\s)+?(?<rollup>\bWITH\s+ROLLUP\b\s*)?(?<having>\bHAVING\b(?:.|\s)+?)?)?';
		$er[]='(?<limit>\bLIMIT\b\s*\d+\s*(?:,\s*\d+\s*)?(?<offset>\bOFFSET\b\s*\d+\s*)?)?';
		$er[]='(?<procedure>\bPROCEDURE\b(?:.|\s)+?)?';
		$er[]='(?<into2>\bINTO\b(?:.|\s)+?)?';
		$er[]='(?<for_update>\bFOR UPDATE\b\s*)?';
		$er[]='(?<lock_in_share_mode>\bLOCK IN SHARE MODE\b\s*)?';
		$er[]=')?';
		//$er[]='(?<rest>(?:.|\s)*?)?';
		$er[]='$/i';
		if(preg_match(implode('',$er),$query,$ret)) {
			$ret['fields']=preg_split('/\s*,\s*/',$ret['fields']);
			foreach($ret['fields'] as $k=>$v) {
				//$v=preg_replace(array('/^\s+/','/\s+$/'),'',$v);
				if($fld=$this->splitFullName($v,true)) {
					$fld[]=$v;
					$ret['fields'][$k]=$fld;
				}
				else{
					if(preg_match('/^(.*?)(?:\s*(?:\bas\s+)?(?:(?<!\.)`([^`]+)`|\b([a-z_][a-z0-9_]*)))?$/',$v,$fld)) {
						($alias=@$fld[2]) || ($alias=@$fld[3]) || ($alias=@$fld[1]);
						$ret['fields'][$k]=array(
							'content'=>$fld[1],
							'type'=>$this->getContentType($fld[1]),
							'alias'=>$alias,
							0=>$v
						);
					}
					else $ret['fields'][$k]=$v;
				}
			}
			$query=$ret;
		}
				return $query;
	}
	protected function getErTbl($name=false){ return $name?"(?:`(?<$name>[^`]+)`|(?<s$name>\w+))":'(?:`([^`]+)`|(\w+))'; }
	protected function getErFullTbl(){ return "(?:{$this->getErTbl('db')}\.)?{$this->getErTbl('table')}"; }
	protected function getErTblAlias(){ return "(?:\s*(?:\bas\s+)?{$this->getErTbl('alias')})?"; }
	protected function getErFullTblOnly(){ return "/^{$this->getErFullTbl()}{$this->getErTblAlias()}$/i"; }
	protected function returnSpecialChar($query){
		return str_replace(
			array($this->_contraBarra,$this->_aspasSimples,$this->_aspasDupla,$this->_pontoVirgula,),
			array('\\\\',      "''",         '""',       ';',          ),
			$query
		);
	}
	protected function parserQuery($query){
		//FIXME
	}
	protected function checkIs_function($value){
	}
	protected function getContentType($value){
		$value=preg_replace('/@[a-z_][a-z0-9_]*:=/i','',$value);
		if(strtoupper($value)=='NULL') return 'NULL';
		if(preg_match('/\//',$value)) return 'DOUBLE';
		if(preg_match('/[\+\*-]/',$value)) return preg_match('/\.\d/',$value)?'DOUBLE':'BIGINT';
		if(preg_match('/(\b(DIV|MOD)\b|%)/',$value)) return 'BIGINT';
		if(preg_match('/(\b(AND|X?OR|NOT|IS|IN|R?LIKE|REGEXP|BETWEEN)\b|!|>?=|<=|<>|<=>|&&|\|\|)/i',$value)) return 'BOOLEAN';
		if(preg_match('/(\^|~|>>|<<|\||\&)/',$value)) return 'BIGINT UNSIGNED';
		if(preg_match('/[<>]/i',$value)) return 'BOOLEAN';
		if(substr($value,0,1)==$this->_bouderauxAspas && preg_match('/^[bh]/i',@$this->_vars[str_replace($this->_bouderauxAspas,'',$value)])) return 'BIGINT UNSIGNED';
		return 'LONGTEXT';
	}
}