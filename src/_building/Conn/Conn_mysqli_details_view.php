<?php
class Conn_mysqli_details_view extends Conn_mysqli_details_main {
	protected $deltaUpdate=1800;  /*segundos 1800seg=30min*/
	protected function getStatus(){
		static $obj=false;
				if($this->version<5) {
			$obj=array();
			$this->setError("Database version doesn't support Views");
		}
		if($obj===false) {
			$obj=array();
			$line=$this->conn->fastLine("SELECT * FROM information_schema.VIEWS WHERE TABLE_SCHEMA='{$this->conn->escape_string($this->readonly['db'])}' AND  TABLE_NAME='{$this->conn->escape_string($this->readonly['name'])}'");
			if($line) {
				$obj=$line;
				$obj['dtUpdate']=time();
			}
			else $obj=$this->setError("View doesn't exist");
		}
		return $obj;
		/*CREATE TABLE information_schema.VIEWS (
			TABLE_CATALOG varchar(512) DEFAULT NULL,
			TABLE_SCHEMA varchar(64) NOT NULL DEFAULT '' COMMENT 'db',
			TABLE_NAME varchar(64) NOT NULL DEFAULT '' COMMENT 'view',
			VIEW_DEFINITION longtext NOT NULL COMMENT 'sql',
			CHECK_OPTION varchar(8) NOT NULL DEFAULT '' COMMENT 'NONE | CASCADED | LOCAL',
			IS_UPDATABLE varchar(3) NOT NULL DEFAULT '' COMMENT '',
			DEFINER varchar(77) NOT NULL DEFAULT '' COMMENT 'geralmente admin@%',
			SECURITY_TYPE varchar(7) NOT NULL DEFAULT '' COMMENT 'geralmente DEFINER',
			CHARACTER_SET_CLIENT varchar(32) NOT NULL DEFAULT '' COMMENT 'geralmente latin1',
			COLLATION_CONNECTION varchar(32) NOT NULL DEFAULT '' COMMENT 'geralmente latin1_swedish_ci'
		)*/
	}
	protected function renameDDL($ddl,$newFullName) { return preg_replace('/(CREATE\b.*?\bVIEW\b)(\s*.*?\s*)(\bAS\b\s*)/',"\\1 $newFullName \\3\n","$ddl;\n"); }
	protected function rebuildObj(){
		$s=$this->getStatus();
				$c=$this->conn->fastLine("SHOW CREATE VIEW {$this->readonly['fullName']}",false);
		if(!$c) return $this->setError("View doesn't have DDL");
				$ddl=preg_replace(
			array(
					'/(\bAS\b\s+select\b\s*)/i',
					'/\s+((?:cross|inner|(?:natural\s+)?(?:right|left)(?:\s+outer)?)?join|straight_join|from)/i',
					'/(\bAS\b.*?,)/',
			),
			array("\\1\n\t"," \n\\1","\\1\n\t"),
			"{$c['Create View']};\n"
		);
		$ddl=$this->renameDDL($ddl,$this->readonly['fullName']);
		/*CREATE [OR REPLACE]
		[ALGORITHM = {UNDEFINED | MERGE | TEMPTABLE}]
		[DEFINER = { user | CURRENT_USER }]
		[SQL SECURITY { DEFINER | INVOKER }]
		VIEW view_name [(column_list)]
		AS select_statement
		[WITH [CASCADED | LOCAL] CHECK OPTION]*/
		if(@$this->readonly['DDL']==$ddl) return true;
				$this->readonly['isUpdateble']=$s['IS_UPDATABLE'];
		$this->readonly['DDL']=$ddl;
		$this->readonly['sql']=$s['VIEW_DEFINITION'];
		$this->readonly['definer']=$s['DEFINER'];
		$this->readonly['securityType']=$s['SECURITY_TYPE'];
		$this->readonly['checkOption']=$s['CHECK_OPTION'];
		$this->readonly['charsetClient']=$s['CHARACTER_SET_CLIENT'];
		$this->readonly['collationConnection']=$s['COLLATION_CONNECTION'];
				$o=Conn_mysqli_details_main::singleton($s['VIEW_DEFINITION'],'query',0);
		$this->readonly['sql']=$o->query;
		//Fields
		//Dependences
		//Constraints
		return true;
	}
}