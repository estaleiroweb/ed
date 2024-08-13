<?php
namespace DB\MySQLi;

class Res extends \DB\Res {
	function __construct($dadObj,$sql,$verifyError=true,$dsn=''){
		parent::__construct($dadObj,$sql,$verifyError,$dsn);
	//print_r([2222222]); return [];
		if(@$this->conn->multi_query($sql)) $this->res=@$this->conn->store_result();
		$this->verifyError($sql);
		/*mysqli_result Object(
            [current_field] => 0
            [field_count] => 5
            [lengths] => Array(
					[0] => 3
					[1] => 3
					[2] => 1
					[3] => 19
					[4] => 23
				)
            [num_rows] => 181
            [type] => 0
        )*/
	}
	function fetch_fields(){ 
		$out=false;
		if($this->res) {
			$out=@$this->res->fetch_fields();
			if($out) foreach($out as $index=>&$obj) {
				//$obj->vartype=$this->trNumType($obj);
				if(!@$obj->orgname) $obj->orgname=$obj->name;
				if(!@$obj->orgtable) $obj->orgtable=$obj->table;
			}
		}
		return $out; 
	}
	function fields(){
		$out=array();
		if($this->res) {
			$o=@$this->res->fetch_fields();
			if($o) foreach($o as $obj) $out[]=$obj->orgname?$obj->orgname:$obj->name;
		}
		return $out;
	}
	function fetch_field($fieldnr){ 
		$out=@$this->res?@$this->res->fetch_fields():array(); 
		return @$out[$fieldnr];
	}
	function reccount(){ 
		$out=$this->oConn->fastLine('SELECT FOUND_ROWS() c');
		return @$out['c'];
	}
	function free_result(){ return @$this->res->free(); }
	function close(){
		if($this->res) {
			while(@$this->conn->more_results()) $this->conn->next_result();
			@$this->res->close();
			$this->res=null;
		}
	}
	
	function num_fields(){ return @$this->res->field_count; }
	function current_field(){ return @$this->res->current_field; }
	function num_rows(){ return @$this->res->num_rows; }
	function lengths(){ return @$this->res->lengths; }
	function error() { return @$this->conn->error; }
	function errno() { return @$this->conn->errno; }
	function type(){ return @$this->res->type; }
}