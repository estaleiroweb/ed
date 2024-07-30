<?php
namespace DB\MySQL;

class Link extends \DB\Link {
	public $maxInsert=200;
	function __construct($splitConn){
		parent::__construct($splitConn);
		#resource mysql_connect ([ string $server [, string $username [, string $password [, bool $new_link [, int $client_flags ]]]]] ) //client_flags=MYSQL_CLIENT_SSL, MYSQL_CLIENT_COMPRESS, MYSQL_CLIENT_IGNORE_SPACE ou MYSQL_CLIENT_INTERACTIVE
		$this->readOnly['conn']=mysql_connect($this->getHostPort(), @$this->readOnly['user'], @$splitConn['pass'], true);
		//print_r($this->readOnly);
		$this->checkConnetctionSelectDb();
	}
	function close(){
		if(!$this->readOnly['conn']) return false;
		@mysql_close($this->readOnly['conn']);
		parent::close();
	}
	function select_db($db){
		if(!$this->readOnly['conn']) return false;
		parent::select_db($db);
		mysql_select_db($db);
	}
	function escape_string($texto){ return mysql_escape_string($texto); }
	function error() { return mysql_error($this->readOnly['conn']); }
	function errno() { return mysql_errno($this->readOnly['conn']); }
	function commit(){
		mysql_query("COMMIT",$this->readOnly['conn']);
		return true;
	}
	function autocommit($bool){
		mysql_query("SET @@autocommit=".(int)$bool,$this->readOnly['conn']);
		return true;
	}
	function change_user($user='root',$passwd='',$db=''){ return mysql_change_user($user,$passwd,$db,$this->readOnly['conn']); }
	function affected_rows(){ return mysql_affected_rows($this->readOnly['conn']); }
	function insert_id(){ return mysql_insert_id($this->readOnly['conn']); }
	function get_client_info(){ return mysql_get_host_info ($this->readOnly['conn']); }
	function multi_query($sql){ return mysqli_multi_query($this->readOnly['conn'],$sql); }
	function store_result(){ return mysqli_store_result($this->readOnly['conn']); }
	function next_result(){ return mysqli_next_result($this->readOnly['conn']); }
	function get_server_info(){ return mysql_get_server_info($this->readOnly['conn']); }
	function ping() { return mysql_ping($this->readOnly['conn']); }
	function get_charset(){return mysql_get_charset($this->readOnly['conn']); }
	function set_charset($charset){return @mysql_set_charset($charset,$this->readOnly['conn']); }
}
