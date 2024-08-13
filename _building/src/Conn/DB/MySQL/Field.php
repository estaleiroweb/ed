<?php
namespace DB\MySQL;

class Field extends \DB\Field {
	public function __construct($res,$i,$oConn){
		$fld=mysql_fetch_field($res,$i);
		$this->name=$this->orgname=$fld->name;
		$this->table=$this->orgtable=$fld->table;
		$this->max_length=$fld->max_length;
		$this->length=mysql_field_len($res,$i);
		$this->flags=$oConn->trFlag($f=mysql_field_flags($res,$i));
		$this->type=$oConn->trType($fld->type,$this->length,$f,$fld->max_length);
		//$this->mysqlExtra=$fld;
	}
}