<?php
namespace DB\MSSQL_ORA;

class Link extends \DB\Link {
	public $startTableDelimiter='\'';
	public $endTableDelimiter='\'';
	public $startFieldDelimiter='\'';
	public $endFieldDelimiter='\'';
	function connect($host='localhost',$user='root',$passwd='',$db=''){
		parent::connect($host,$user,$passwd,$db);
		$this->readOnly['conn']=mssql_connect($host,$user,$passwd);
		if (!$this->readOnly['conn'] && $this->error()) $this->fatalError();
		if ($db) $this->select_db($db);
		return $this;
	}
	function close(){
		@mssql_close($this->readOnly['conn']);
		parent::close();
	}
	function select_db($db){
		parent::select_db($db);
		//mssql_select_db($db,$this->conn);
	}
	function escape_string($texto){ ///////////////
		return $texto;
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
	function query($sql,$verifyError=true){
		return $this->readOnly['res']=new \DB\MSSQL_ORA\Res($this->conn,$sql,$verifyError,$this->db,$this->dsn);
	}
}
