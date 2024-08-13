<?php
namespace DB\MSSQL_ORA;

class Field extends \DB\Field {
	public function __construct($res,$i,$oConn){
		$fld=mssql_fetch_field($res,$i);
		$this->name=$this->orgname=$fld->name;
		$this->table=$this->orgtable=$fld->column_source;
		$this->max_length=$fld->max_length;
		$this->length=mssql_field_length($res,$i);
		//$this->flags=$oConn->trFlag($f=mysql_field_flags($res,$i));
		//$this->type=$oConn->trType($fld->type,$this->length,$f,$fld->max_length);
		$this->type=$this->realType=$fld->type;
		$this->vartype=$this->type;
		//$this->mysqlExtra=$fld;
	}
}
