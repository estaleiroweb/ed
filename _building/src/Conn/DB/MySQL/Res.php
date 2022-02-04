<?php
namespace DB\MySQL;

class Res extends \DB\Res {
	function __construct($dadObj,$sql,$verifyError=true,$dsn=''){
		parent::__construct($dadObj,$sql,$verifyError,$dsn);
		$this->res=mysql_query($sql,$this->conn);
		$this->verifyError();
	}
	function close(){ mysql_free_result($this->res); }
	function data_seek($offset){ return mysql_data_seek($this->res,$offset); }
	function num_rows(){ return mysql_num_rows($this->res); }
	//retorna informação sobre uma coluna do conjunto de resulatdos
	function fetch_field($fieldnr){ return mysql_fetch_field($this->res,$fieldnr); }
	function fetch_fields(){//retorna informação sobre todas as colunas do conjunto de resultados
		$out=array();
		for ($i=0;$i<mysql_num_fields($this->res);$i++) $out[$i]=new Field($this->res,$i,$this->objConn);
		return $out;
	}
	function num_fields(){//retorna numero de campos
		return mysql_num_fields($this->res);
	}
	function fetch_field_direct($fieldnr){//retorna informação da coluna especificada
	}
	function field_seek($fieldnr){//define o ponteiro do resultado para o índice de campo especificado
	}
	function fetch_array($resulttype){ return mysql_fetch_array($this->res,$resulttype); }
	function fetch_assoc(){ return mysql_fetch_assoc($this->res); }
	function fetch_object(){ return mysql_fetch_object($this->res); }
	function fetch_row(){ return mysql_fetch_row($this->res); }
	function field_count(){ return mysql_num_fields($this->res); }
	function current_field(){//retorna o índice do campo atual
	}
	function lengths(){//retorna uma matriz com os tamanhos das colunas
	}
	function error() { return mysql_error($this->conn); }
	function errno() { return mysql_errno($this->conn); }
}
class Field {
	/*
		name - nome da coluna 
		table - nome da tabela onde esta o campo 
		max_length - o limite de tamanho para a coluna 
		not_null - 1 se a coluna não pode ser NULL 
		primary_key - 1 se a coluna é a chave primária 
		unique_key - 1 se a coluna é a chave única 
		multiple_key - 1 se a coluna é uma chave não única 
		numeric - 1 se a coluna é numérica 
		blob - 1 se a coluna é BLOB 
		type - o tipo da coluna 
		unsigned - 1 se a coluna é unsigned(sem sinal) 
		zerofill - 1 se a coluna é preenchida com zero 
	*/
	public $name='';
	public $orgname='';
	public $table='';
	public $orgtable='';
	public $def='';
	public $max_length=0;
	public $length=0;
	public $charsetnr=0;
	public $flags=0;
	public $type=0;
	public $decimals=0;
	
	function __construct($res,$i,$oConn){
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