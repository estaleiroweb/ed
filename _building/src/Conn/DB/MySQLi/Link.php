<?php
namespace DB\MySQLi;

class Link extends \DB\Link {
	public $maxInsert=200;
	
	function __construct($splitConn){
		parent::__construct($splitConn);
		$pass=@$splitConn['pass']?$splitConn['pass']:null;
		//print_r($this->readOnly); 
		
		$this->readOnly['conn']=new \mysqli(
			$this->readOnly['host'],
			$this->readOnly['user'],
			$pass,
			$this->readOnly['db'],
			$this->readOnly['port'],
			$this->readOnly['socket']
		);
		$er='Withou Connection';
		if (!$this->readOnly['conn'] || ($er=$this->error())) $this->fatalError($er);
	}
	function close(){
		if($this->readOnly['conn']) {
			@$this->readOnly['conn']->close();
			$this->readOnly['conn']=null;
			parent::close();
		}
	}
	function select_db($db){
		parent::select_db($db);
		$this->readOnly['conn']->select_db($db);
	}
	function escape_string($texto){ return @mysql_escape_string($texto); }
	function error() { return @mysqli_connect_error().@$this->readOnly['conn']->error; }
	function errno() { 
		($out=@mysqli_connect_errno()) || ($out=@$this->readOnly['conn']->errno);
		return $out;
	}
	function get_database(){ 
		$out=$this->fastLine('SELECT DATABASE() db');
		return @$out['db']; 
	}
	function affected_rows(){ return $this->readOnly['conn']->affected_rows; }
	function insert_id(){ return $this->readOnly['conn']->insert_id; }
	function get_client_info(){ return $this->readOnly['conn']->host_info; }
}