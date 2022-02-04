<?php
namespace DB;

abstract class Pattern {
	public $flags_bit=array(
		0	=>'not_null',
		1	=>'primary_key',
		2	=>'unique_key',
		3	=>'multiple_key',
		4	=>'blob',
		5	=>'unsigned',
		6	=>'zerofill',
		7	=>'binary',
		8	=>'enum',
		9	=>'auto_increment',
		10	=>'timestamp',
		11	=>'set',
		12	=>'numeric',
		13	=>'multiple_key',
		14	=>'part_key',
		15	=>'group'
	);
	public $flags=array(
		1		=>'not_null',
		2		=>'primary_key',
		4		=>'unique_key',
		8		=>'multiple_key',
		16		=>'blob',
		32		=>'unsigned',
		64		=>'zerofill',
		128		=>'binary',
		256		=>'enum',
		512		=>'auto_increment',
		1024	=>'timestamp',
		2048	=>'set',
		4096	=>'numeric',
		8192	=>'multiple_key',
		16384	=>'part_key',
		32768	=>'group'
	);
	public $type=array(
		0	=>'DECIMAL',
		1	=>'TINYINT',
		2	=>'SMALLINT',
		3	=>'INTEGER',
		4	=>'FLOAT',
		5	=>'DOUBLE',
		6	=>'NULL',
		7	=>'TIMESTAMP',
		8	=>'BIGINT',
		9	=>'MEDIUMINT',
		10	=>'DATE',
		11	=>'TIME',
		12	=>'DATETIME',
		13	=>'YEAR',
		14	=>'NEWDATE',
		16	=>'BIT',
		246	=>'DECIMAL',
		247	=>'EMUM',
		248	=>'SET',
		249	=>'TINYBLOB',
		250	=>'MEDIUMBLOB',
		251	=>'LONGBLOB',
		252	=>'BLOB',
		253	=>'VARCHAR',
		254	=>'CHAR',//STRING
		255	=>'GEOMETRY'
	);
	function trans($value) {
		if (is_null($value)) return 'null';
		elseif (is_numeric($value)) return $value;
		return "'{$this->escape_string($value)}'";
	}
	function trNumFlag($flag){
		$ret=array();
		foreach ($this->flags_bit as $bit=>$text) if (($flag>>$bit)&1) $ret[$bit]=$text;
		return $ret;
	}
	function trFlag($flag){
		//not_null", "primary_key", "unique_key", "multiple_key", "blob", "unsigned", "zerofill", "binary", "enum", "auto_increment", "timestamp". 
		$ret=array_sum(array_flip(preg_grep("/^".str_replace(" ","|",$flag)."$/i",$this->flags)));
		return $ret;
	}
	
	function trType($type,$len,$flags='',$maxlen=0){
		$f=strtoupper($type);
		if ($f=='INT'){
			if ($len==1) $f='TINYINT';
			elseif ($len==4) $f='TINYINT';
			elseif ($len==6) $f='SMALLINT';
			elseif ($len==9) $f='MEDIUMINT';
			elseif ($len==20) $f='BIGINT';
			else $f='INTEGER';
		} elseif ($f=='REAL'){
			$f=$len==9?"FLOAT":"DOUBLE";
		} elseif ($f=='DATE'){
			if ($len==19) $f.="TIME";
		} elseif ($f=='UNKNOWN'){
			if ($len==-1) $f="GEOMETRY";
			elseif ($len==12) $f="DECIMAL";
			else $f="BIT";
		} elseif ($f=='STRING'){
			if (preg_match("/enum/i",$flags)) $f='ENUM';
			elseif (preg_match("/set/i",$flags)) $f='SET';
			else $f=preg_match("/binary/i",$flags)?'CHAR':'VARCHAR';
		}
		return (int)array_search($f,$this->type);
	}
}