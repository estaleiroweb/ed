<?php
namespace DB\MSSQL;

class Link extends \DB\Link {
	public $startTableDelimiter='[';
	public $endTableDelimiter=']';
	public $startFieldDelimiter='[';
	public $endFieldDelimiter=']';
	public $strDelimiter='"';
	function __construct($splitConn){
		parent::__construct($splitConn);
		#int mssql_connect ([ string $nomedoservidor [, string $username [, string $password ]]] )
		$this->readOnly['conn']=mssql_connect($this->getHostPort(), @$this->readOnly['user'], @$splitConn['pass']);
		//print_r($this->readOnly); 
		$this->checkConnetctionSelectDb();
	}
	function close(){
		if(!$this->readOnly['conn']) return false;
		@mssql_close($this->readOnly['conn']);
		parent::close();
	}
	function select_db($db){
		if(!$this->readOnly['conn']) return false;
		parent::select_db($db);
		mssql_select_db($db,$this->conn);
	}
	function error() {
		if (isset($this->readOnly['res'])) return $this->readOnly['res']?'':mssql_get_last_message();
		return $this->readOnly['conn']?'':mssql_get_last_message();
	}
	function commit(){
		return mssql_query("COMMIT",$this->readOnly['conn']);
	}
	function autocommit($bool){
		//return mssql_query("COMMIT",$this->readOnly['conn']);
	}
	function change_user($user='root',$passwd='',$db=''){
	}
	function affected_rows(){
		return mssql_rows_affected($this->readOnly['conn']);
	}
	function insert_id(){
		$res=mssql_query("SELECT @@IDENTITY as last_insert_id", $this->readOnly['conn']);
		return $line=@mssql_fetch_row($res)?$line[0]:0;
	}
	function get_client_info(){
	}
	function listSchema(){
		return $this->query("
			SELECT 
				tab.name AS table_name, 
				col.name AS column_name, 
				col.colid AS column_id, 
				typ.name AS data_type,
				col.length AS length, 
				col.prec AS prec,
				col.scale AS scale, 
				com.text AS default_value, 
				obj.name AS default_cons_name
			FROM systypes typ 
			INNER JOIN syscolumns col ON typ.xusertype = col.xusertype 
			INNER JOIN sysobjects tab ON col.id = tab.id 
			LEFT OUTER JOIN syscomments com ON col.cdefault = com.id AND com.colid = 1
			INNER JOIN sysobjects obj ON com.id = obj.id 
			WHERE (tab.xtype = 'U')
			ORDER BY tab.name, col.colid
		");
	}
}
