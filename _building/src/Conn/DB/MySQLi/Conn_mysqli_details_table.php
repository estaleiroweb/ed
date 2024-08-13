<?php
class Conn_mysqli_details_table extends Conn_mysqli_details_main {//
	static public function singleton($db,$name,$force=false) { return parent::singleton($db,$name,$force,__CLASS__); }
	protected function checkUpdate($force){
		$this->log();
		if(!file_exists($this->fullFileName)) {
			$oView=Conn_mysqli_details_main::singleton("`{$this->readonly['db']}`.`{$this->readonly['name']}`",'view',$force);
			if(!$oView->error) return $this->readonly=$oView->obj;
		}
		parent::checkUpdate($force);
	}
	protected function getStatus(){
		static $obj=false;
				$this->log();
		if($obj===false) {
			$obj=array();
			$line=$this->conn->fastLine("SHOW TABLE STATUS FROM `{$this->readonly['db']}` LIKE '{$this->conn->escape_string($this->readonly['name'])}'");
			if($line) $obj=array(
				'engine'=>$line['Engine'],
				'version'=>$line['Version'],
				'recordCount'=>$line['Rows'],
				'dtGer'=>$line['Create_time'],
				'dtUpdate'=>$line['Update_time'],
				'dtMaintenance'=>$line['Check_time'],
				'charset'=>$line['Collation'],
				'comment'=>$line['Comment'],
				'auto_increment'=>$line['Auto_increment'],
			);
			else $obj=$this->setError("Table doesn't exist");
		}
		return $obj;
	}
	protected function renameDDL($ddl,$newFullName) { return preg_replace('/^(CREATE\b.+\bTABLE\b\s*).+?\(/i','\1'.$newFullName.'(',$ddl); }
	protected function rebuildObj(){
		$this->log();
		$s=$this->getStatus();
		$c=$this->conn->fastLine("SHOW CREATE TABLE {$this->readonly['fullName']}",false);
		if(!$c) return $this->setError("Table doesn't have DDL");
		$ddl=$this->renameDDL($c['Create Table'],$this->readonly['fullName']);
		if(@$this->readonly['DDL']==$ddl) return true;
				$this->readonly['dtGer']=$s['dtGer'];
		$this->readonly['isUpdateble']=true;
		$this->readonly['DDL']=$ddl;
		$this->readonly['comment']=$s['comment'];
		$this->readonly['charset']=$s['charset'];
		$this->readonly['dtMaintenance']=$s['dtMaintenance'];
		$this->readonly['recordCount']=$s['recordCount'];
		$this->readonly['engine']=$s['engine'];
		$this->readonly['version']=$s['version'];
		$this->readonly['auto_increment']=$s['auto_increment'];
				$this->buildDetails($ddl);
		//save arrays
		//$path=$this->renewDir("{$this->protect['fullPath']}/dependencies");
		//foreach($this->readonly['dependencies'] as $k=>$value) `ln -s "$value" "$path/$k"`;
		return true;
	}
	protected function buildDetails($ddl){
		if(!@$this->readonly['fields']) $this->readonly['fields']=array();
		$this->readonly['indexes']=array();
		$this->readonly['checks']=array();
		$this->readonly['constraints']=array();
		$this->readonly['dependencies']=array();
		$this->readonly['properties']=array();
				$erField=$this->getErFieldLine();
		$erIndex=$this->getErIndexLine();
		$erCheck=$this->getErCheckLine();
				$detSplit=explode(",\n",preg_replace(array('/^.+\n/','/\n.+$/'),'',$ddl));
		foreach($detSplit as $line) {
			if(preg_match($erField,$line,$ret)) {
				$o=Conn_mysqli_details_main::singleton("{$this->readonly['fullName']}.`{$ret['field']}`",'field',$ret);
				$this->readonly['fields'][$ret['field']]=$o;
				if(@$ret['key']) $this->buildIndex($ret);
			}
			elseif(preg_match($erIndex,$line,$ret)) $this->buildIndex($ret);
			elseif(preg_match($erCheck,$line,$ret)) $this->buildCheck($ret);/*
			elseif(preg_match('/^\s*(?<keyType>(?:\w+ )?KEY) (?:`(?<keyName>[^ ]+)` )?\(`(?<fields>.+?)`\)\s*$/i',$v,$ret)) {
				$fields=explode('`,`',$ret['fields']);
				$this->buildIndex($ret['keyType'],$fields,$ret['keyName']);
							}
			elseif(preg_match('/^\s*CONSTRAINT `(?<constraintName>[^`]+)` FOREIGN KEY (?:`(?<keyName>[^ ]+)` )?\(`(?<fields>.+?)`\) REFERENCES (?<tableRefer>(`[^`]+`.)?`[^`]+`) \(`(?<fieldsRefer>.+?)`\)(?: ON DELETE (?<on_delete>RESTRICT|CASCADE|SET NULL|NO ACTION))?(?: ON UPDATE (?<on_update>RESTRICT|CASCADE|SET NULL|NO ACTION))?\s*$/i',$v,$ret)) {
				$fields=explode('`,`',$ret['fields']);
				$this->buildConstraint($ret,$fields);
				foreach($fields as $field) $this->readonly['fields'][$field]['key']|=8;
			}
			*/
		}
		if(preg_match('/\n\) *(.+)$/',$ddl,$tblDet)){
			if(preg_match_all('/(\w+)=(\w+)/',$tblDet[1],$tmp,PREG_SET_ORDER)) {
				foreach($tmp as $v) $this->readonly['properties'][$v[1]]=$v[2];
			}
			if(preg_match('/ (COMMENT)=\'(.*)\'$/i',$tblDet[1],$v)) $this->readonly['properties'][$v[1]]=$this->decodeComment($v[2]);
		}
		foreach($this->readonly['fields'] as $k=>$value) $this->readonly['fields'][$k]=$value->getObj();
	}
		/*create_definition:
			data_type:BIT[(length)]| TINYINT[(length)] [UNSIGNED] [ZEROFILL]| SMALLINT[(length)] [UNSIGNED] [ZEROFILL]| MEDIUMINT[(length)] [UNSIGNED] [ZEROFILL]| INT[(length)] [UNSIGNED] [ZEROFILL]| INTEGER[(length)] [UNSIGNED] [ZEROFILL]| BIGINT[(length)] [UNSIGNED] [ZEROFILL]| REAL[(length,decimals)] [UNSIGNED] [ZEROFILL]| DOUBLE[(length,decimals)] [UNSIGNED] [ZEROFILL]| FLOAT[(length,decimals)] [UNSIGNED] [ZEROFILL]| DECIMAL[(length[,decimals])] [UNSIGNED] [ZEROFILL]| dec[(length[,decimals])] [UNSIGNED] [ZEROFILL]| NUMERIC[(length[,decimals])] [UNSIGNED] [ZEROFILL]| fixed[(length[,decimals])] [UNSIGNED] [ZEROFILL]| NUMERIC[(length[,decimals])] [UNSIGNED] [ZEROFILL]| DATE| TIME| TIMESTAMP| DATETIME| YEAR[(length)]| CHAR[(length)] [CHARACTER SET charset_name] [COLLATE collation_name]| nchar[(length)] [CHARACTER SET charset_name] [COLLATE collation_name]| VARCHAR(length)  [CHARACTER SET charset_name] [COLLATE collation_name]| NVARCHAR(length)  [CHARACTER SET charset_name] [COLLATE collation_name]| BINARY[(length)]| VARBINARY(length)| TINYBLOB| BLOB| MEDIUMBLOB| LONGBLOB| TINYTEXT [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]| TEXT [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]| MEDIUMTEXT [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]| LONGTEXT [BINARY] [CHARACTER SET charset_name] [COLLATE collation_name]| ENUM(value1,value2,value3,...) [CHARACTER SET charset_name] [COLLATE collation_name]| SET(value1,value2,value3,...) [CHARACTER SET charset_name] [COLLATE collation_name]| GEOMETRY| GEOMETRYCOLLECTION| LINESTRING| MULTILINESTRING| MULTIPOINT| MULTIPOLYGON| POINT| POLYGON| BOOLEAN| BOOLtable_option:    ENGINE [=] engine_name  | AUTO_INCREMENT [=] value  | AVG_ROW_LENGTH [=] value  | [DEFAULT] CHARACTER SET [=] charset_name  | CHECKSUM [=] {0 | 1}  | [DEFAULT] COLLATE [=] collation_name  | COMMENT [=] 'string'  | CONNECTION [=] 'connect_string'  | DATA DIRECTORY [=] 'absolute path to directory'  | DELAY_KEY_WRITE [=] {0 | 1}  | INDEX DIRECTORY [=] 'absolute path to directory'  | INSERT_METHOD [=] { NO | FIRST | LAST }  | KEY_BLOCK_SIZE [=] value  | MAX_ROWS [=] value  | MIN_ROWS [=] value  | PACK_KEYS [=] {0 | 1 | DEFAULT}  | PASSWORD [=] 'string'  | ROW_FORMAT [=] {DEFAULT|DYNAMIC|FIXED|COMPRESSED|REDUNDANT|COMPACT}  | TABLESPACE tablespace_name [STORAGE {DISK|MEMORY|DEFAULT}]  | UNION [=] (tbl_name[,tbl_name]...)partition_options:    PARTITION BY        { [LINEAR] HASH(expr)        | [LINEAR] KEY(column_list)        | RANGE(expr)        | LIST(expr) }    [PARTITIONS num]    [SUBPARTITION BY        { [LINEAR] HASH(expr)        | [LINEAR] KEY(column_list) }      [SUBPARTITIONS num]    ]    [(partition_definition [, partition_definition] ...)]partition_definition:    PARTITION partition_name        [VALUES             {LESS THAN {(expr) | MAXVALUE}             |             IN (value_list)}]        [[STORAGE] ENGINE [=] engine_name]        [COMMENT [=] 'comment_text' ]        [DATA DIRECTORY [=] 'data_dir']        [INDEX DIRECTORY [=] 'index_dir']        [MAX_ROWS [=] max_number_of_rows]        [MIN_ROWS [=] min_number_of_rows]        [TABLESPACE [=] tablespace_name]        [NODEGROUP [=] node_group_id]        [(subpartition_definition [, subpartition_definition] ...)]subpartition_definition:    SUBPARTITION logical_name        [[STORAGE] ENGINE [=] engine_name]        [COMMENT [=] 'comment_text' ]        [DATA DIRECTORY [=] 'data_dir']        [INDEX DIRECTORY [=] 'index_dir']        [MAX_ROWS [=] max_number_of_rows]        [MIN_ROWS [=] min_number_of_rows]        [TABLESPACE [=] tablespace_name]        [NODEGROUP [=] node_group_id]
		*/
	private function buildIndex($ret){
		$keyBit=0;
		$fields=array();
		if(@$ret['fields']) $fields=explode('`,`',$ret['fields']);
		elseif(@$ret['field']) {
			$keyName=$ret['field'];
			$fields=array($ret['field']);
		}
		if(!$fields || !preg_match('/^\s*(\w+)/',@$ret['key'],$index_type)) return $keyBit;
		$index_type=strtoupper($index_type[1]);
		if(@$ret['constraint']) {
			$constraint=$this->splitFullName($ret['constraint']);
			$name=$constraint['name'];
		} else {
			$constraint=false;
			$name=@$ret['index_name'];
		}
				$keyBit=$this->indexTypes[$index_type];
		if($keyBit==1) $index_type='INDEX';
		else {
			$index_type.=' KEY';
			if($keyBit==4 && !$name) $name=$index_type;
		}
		if(!$name) $name=reset($fields);
				if(@$ret['index_options']) {
			$index_options=array();
			$erIndex_option='/(\bKEY_BLOCK_SIZE(?:\s*=\s*|\s+)(?<key_block_size>\S+)\s+|\bUSING\s+(?<index_option>BTREE|HASH)\s*|\bWITH\s+PARSER\s*`(?<parser_name>[^`]+)`\s*)/i';
			if(preg_match_all($erIndex_option,$ret['index_options'],$o,PREG_SET_ORDER)){
				foreach($o as $v) {
					if(@$v['key_block_size']) $index_options['key_block_size']=$v['key_block_size'];
					if(@$v['index_option']) $index_options['index_option']=$v['index_option'];
					if(@$v['parser_name']) $index_options['parser_name']=$v['parser_name'];
				}
			}
		} else $index_options=false;
		$reference=@$ret['tableRefer']?array(
			'table'=>$this->splitFullName($ret['tableRefer']),
			'fields'=>explode('`,`',$ret['fieldsRefer']),
			'match'=>@$ret['match'],
			'on_delete'=>@$ret['on_delete'],
			'on_update'=>@$ret['on_update'],
		):false;
		if(!@$this->readonly['indexes'][$name]) $this->readonly['indexes'][$name]=array();
		if(!@$this->readonly['indexes'][$name]['constraint']) $this->readonly['indexes'][$name]['constraint']=$constraint;
		if(!@$this->readonly['indexes'][$name]['index_name']) $this->readonly['indexes'][$name]['index_name']=@$ret['index_name'];
		if(!@$this->readonly['indexes'][$name]['index_type']) $this->readonly['indexes'][$name]['index_type']=$index_type;
		if(!@$this->readonly['indexes'][$name]['fields']) $this->readonly['indexes'][$name]['fields']=$fields;
		if(!@$this->readonly['indexes'][$name]['bit']) $this->readonly['indexes'][$name]['bit']=0;
		$this->readonly['indexes'][$name]['bit']|=$keyBit;
		if(!@$this->readonly['indexes'][$name]['index_options']) $this->readonly['indexes'][$name]['index_options']=$index_options;
		if(!@$this->readonly['indexes'][$name]['reference']) $this->readonly['indexes'][$name]['reference']=$reference;
			//foreach($fields as $field) if(@$this->readonly['fields'][$field]) $this->readonly['fields'][$field]['key']|=$keyBit;
		foreach($fields as $field) if(@$this->readonly['fields'][$field]) $this->readonly['fields'][$field]->setKey($keyBit);
		if($n=$this->readonly['indexes'][$name]['constraint']){
			$l=$this->conn->fastLine("SELECT * FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='{$this->conn->escape_string($n['db'])}' AND  CONSTRAINT_NAME='{$this->conn->escape_string($n['name'])}'");
			if($l){
				$this->readonly['indexes'][$name]['constraint']['UNIQUE_CONSTRAINT_CATALOG']=$l['UNIQUE_CONSTRAINT_CATALOG'];
				$this->readonly['indexes'][$name]['constraint']['UNIQUE_CONSTRAINT_CATALOG']=$l['UNIQUE_CONSTRAINT_CATALOG'];
				if($l['MATCH_OPTION']!='NONE') {
					$this->readonly['indexes'][$name]['reference']['match']=$l['MATCH_OPTION'];
					$match=' MATCH '.$l['MATCH_OPTION'];
				} else $match='';
				$this->readonly['indexes'][$name]['reference']['on_delete']=$l['DELETE_RULE'];
				$this->readonly['indexes'][$name]['reference']['on_update']=$l['UPDATE_RULE'];
				$old=$ret[0];
				$ret[0].="$match ON DELETE {$l['DELETE_RULE']} ON UPDATE {$l['UPDATE_RULE']}";
				$this->readonly['DDL']=str_replace($old,$ret[0],$this->readonly['DDL']);
			}
			$this->readonly['constraints'][$n['db'].'.'.$n['name']]=$this->readonly['indexes'][$name];
			$this->readonly['constraints'][$n['db'].'.'.$n['name']]['table']=array('db'=>$this->readonly['db'],'name'=>$this->readonly['name'],);
		}
		if($keyBit==8 && $table=@$this->readonly['indexes'][$name]['reference']['table']) {
			$o=Conn_mysqli_details_main::singleton("`{$table['db']}`.`{$table['name']}`",'table');
			if(!$o->error) $this->readonly['dependencies'][$table['db'].'.'.$table['name']]=$o->fullFileName; //$o->obj['fullPath'];
		}
		if(@$this->readonly['indexes'][$name]['line']) $this->readonly['indexes'][$name]['line'].=",\n".$ret[0];
		else $this->readonly['indexes'][$name]['line']=$ret[0];
		return $keyBit;
	}
	private function buildCheck($ret){
		$name=@$ret['constraintName'];
		if($name) $this->readonly['checks'][$name]=$ret['expr'];
		else $this->readonly['checks'][]=$ret['expr'];
	}
}