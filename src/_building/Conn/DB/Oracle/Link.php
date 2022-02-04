<?php
namespace DB\Oracle;

use Sys\Config;

//putenv("ORACLE_SID=orcl");
class Link extends \DB\Link {
	public $autocommit=false;
	public $startTableDelimiter='"';
	public $endTableDelimiter='"';
	public $startFieldDelimiter='"';
	public $endFieldDelimiter='"';
	public $strDelimiter='\'';
	private $passwd;
	function __construct($splitConn){
		parent::__construct($splitConn);
		//$this->export_variables();
		$this->readOnly['conn']=$this->connect($this->readOnly['host'], @$this->readOnly['user'], $this->passwd=@$splitConn['pass']);
		//print_r($this->readOnly); 
		//$this->checkConnetctionSelectDb();
	}
	function connect($host='localhost',$user='root',$passwd=''){
		//$error_reporting=ini_get('error_reporting');
		//error_reporting(0);
		\_::disable_error_handler();
		try {
		//print __LINE__." :{$this->readOnly['user']}:{$this->passwd}@{$this->readOnly['host']}\n"; exit;
			return oci_connect($this->readOnly['user'], $this->passwd, $this->readOnly['host']); //,null,OCI_SYSDBA
		} catch (\PDOException $e) {
			$this->fatalError($e->getMessage());
		}
		restore_error_handler();
		//error_reporting($error_reporting);
	}
	function export_variables(){
		$this->readOnly['version']=isset($this->readOnly['dsn']['version'])?($this->readOnly['dsn']['version']==10?10:11):11;
		$c=Config::singleton();
		exec(". {$c->ini}/export_ora{$this->readOnly['version']}.sh");
	}
	function close(){
		if(!$this->readOnly['conn']) return false;
		@oci_close($this->readOnly['conn']);
		parent::close();
	}
	function select_db($db){}
	function error() { 
		if(is_null($this->readOnly['conn']) || $this->readOnly['conn']===false) {
			$e=oci_error();
			return @$e['message']?$e['message']:'Without Connection';
		}
		$e=oci_error($this->readOnly['conn']);
		return @$e['message'];
	}
	function errno() { 
		$e=oci_error($this->readOnly['conn']);
		return $e['code'];
	}
	function commit(){ return oci_commit($this->readOnly['conn']); }
	function autocommit($bool){ return $this->autocommit=$bool; }
	function change_user($user='root'){ 
		$this->connect($this->readOnly['host'],$user,$this->passwd);
		return @$this->readOnly['conn']?true:false; 
	}
	function affected_rows(){ return oci_num_rows($this->readOnly['conn']); }
	function insert_id($sequence){ 
		$line=$this->fastline("SELECT $sequence.CURRVAL AS INSERT_ID FROM DUAL");
		return @$line['INSERT_ID']+0;
	}
	function get_client_info(){ 
		$line=$this->fastline("SELECT UTL_INADDR.GET_HOST_NAME || ' - ' || UTL_INADDR.GET_HOST_ADDRESS as HOSTNAME FROM DUAL");
		return @$line['HOSTNAME'];
	}
	function ping(){ 
		//verifica se conn est� ativa //FIXME
		$line=$this->fastline("SELECT 1 AS TEST FROM DUAL");
		if(!@$line['TEST']) $this->connect($this->readOnly['host'], $this->readOnly['user'], $this->passwd);
		return @$this->readOnly['conn']?true:false; 
	}
	function get_server_info(){ 
		//select * from v$version
		$line=$this->fastline('SELECT BANNER FROM SYS.V_$VERSION WHERE ROWNUM=1');
		return @$line['BANNER'];
	}
	function merge($tblTo,$line=null,$keysC=null,$caracater='.'){
		static $keyComp=array();
		static $keys=array();
		static $sum=array();
		
		if($line) {
			if(!@$keys[$tblTo]) {
				$keyComp[$tblTo]=array_flip(preg_split('/\s*[;,]\s*/',$keysC));
				$keys[$tblTo]=$this->mountFieldsKeys($line); 
				$sum[$tblTo]=0;
			}
			$where=$this->mountFieldsConpareValues(array_intersect_key($line, $keyComp[$tblTo]));
			$set=$this->mountFieldsSetValues(array_diff_key($line, $keyComp[$tblTo]));
			$sql="MERGE INTO $tblTo USING dual ON ($where) ";
			$sql.="WHEN MATCHED THEN UPDATE SET $set ";
			$sql.="WHEN NOT MATCHED THEN INSERT ({$keys[$tblTo]}) VALUES {$this->mountValueInsertLine($line)}";

			//show($sql);
			$this->query($sql);
			$sum[$tblTo]++;
			if($sum[$tblTo] % 100==0) print $caracater;
		}
		if(!@$keys[$tblTo]) return 0;
		$out=$sum[$tblTo];
		if(!$line) {
			unset($keys[$tblTo]);
			$sum[$tblTo]=0;
		}
		return $out;
	}
	function addQuote($value){
		if(is_numeric($value)) return $this->strDelimiter.$this->escape_string($value).$this->strDelimiter;
		return parent::addQuote($value);
	}
	function fieldCompareValue($field,$value){
		if(is_numeric($value)) return $this->startFieldDelimiter.$field.$this->endFieldDelimiter.'='.$this->strDelimiter.$value.$this->strDelimiter;
		return parent::fieldCompareValue($field,$value);
	}
	/*
	function multi_query($sql){ return $this->query($sql); }
	function more_results(){ return $this->readOnly['conn']->more_results(); }
	function store_result(){ return $this->readOnly['conn']->store_result(); }
	function next_result(){ return $this->readOnly['conn']->next_result(); }
	function use_result(){ return $this->readOnly['conn']->use_result(); }


	function get_charset(){return @$this->readOnly['conn']->get_charset(); }
	function set_charset($charset){return @$this->readOnly['conn']->set_charset($charset); }
	*/
}
