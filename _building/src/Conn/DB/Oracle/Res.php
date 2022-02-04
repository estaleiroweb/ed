<?php
namespace DB;
/**
 * Class Main 
 *
 * @author Helbert Fernandes
 * @link http://www.estaleiroweb.com.br
 * @access helbert@estaleiroweb.com.br
 * @version 1.0
 * @package Conn
 */
class Res extends \DB\Res {
	function __construct($dadObj,$sql,$verifyError=true,$dsn=''){
		parent::__construct($dadObj,$sql,$verifyError,$dsn);
		$error_reporting=ini_get('error_reporting');
		error_reporting(0);
		oci_execute($this->res=oci_parse($this->conn, $sql), ($this->conn->autocommit?OCI_COMMIT_ON_SUCCESS:OCI_DEFAULT));
		error_reporting($error_reporting);
		$this->verifyError($sql);
	}
	function close(){
		if(!$this->res) return;
		$this->free_result();
		$this->res=null;
	}
	function fetch(){ return @oci_fetch($this->res); }
	function fetch_all(&$output,$skip=0,$maxrows=-1,$flags=0){ return @oci_fetch_all($this->res,$output,$skip,$maxrows,$flags); }
	function fetch_assoc(){ return @oci_fetch_assoc($this->res); }
	function fetch_array($resulttype){ return @oci_fetch_array($this->res,$resulttype); }
	function fetch_object(){ return @oci_fetch_object($this->res); }
	function fetch_row(){ return @oci_fetch_row($this->res); }

	function field_is_null($field){ return @oci_field_is_null($this->res,$field); }
	function field_name($field){ return @oci_field_name($this->res,$field); }
	function field_precision($field){ return @oci_field_precision($this->res,$field); }
	function field_scale($field){ return @oci_field_scale($this->res,$field); }
	function field_size($field){ return @oci_field_size($this->res,$field); }
	function field_type($field){ return @oci_field_type($this->res,$field); }
	function field_type_raw($field){ return @oci_field_type_raw($this->res,$field); }

	function internal_debug($onoff){ return @oci_internal_debug($this->res,$field); }
	function free_result(){ return @oci_free_statement($this->res); }
	function num_fields(){ return @oci_num_fields($this->res); }
	function num_rows(){ 
		static $num=null;
		if($num!==null) return $num;
		if(!preg_match('/^(\s|\()*SELECT\b/i',$this->sql)) return null;
		$sql="SELECT COUNT(1) AS QUANT FROM ( \n".preg_replace('/(\s|;)+$/','',$this->sql)." \n) T";
		$error_reporting=ini_get('error_reporting');
		error_reporting(0);
		oci_execute($res=oci_parse($this->conn, $sql), OCI_DEFAULT);
		error_reporting($error_reporting);
		$line=@oci_fetch_assoc($res);
		return $num=@$line['QUANT']+0;
	}
	function fetch_field2($field){
		$o=new \StdClass;
		$o->name=$this->field_name($field);//nome da coluna
		$o->table=null;//a tabela a qual o objeto pertence 
		$o->def=null;//o valor padr�o da coluna
		$o->max_length=$this->field_size($field);//o limite de tamanho da coluna 
		$o->not_null=!$this->field_is_null($field);//1 se a coluna n�o puder ser NULL 
		$o->primary_key=null;//1 se a coluna � a chave prim�ria 
		$o->unique_key=null;//1 se a coluna � a chave �nica 
		$o->multiple_key=null;//1 se a coluna � uma chave n�o-�nica 
		$o->numeric=null;//1 se a coluna � num�rica 
		$o->blob=null;//1 se a coluna � um BLOB 
		$o->type=$this->field_type($field);//o tipo da coluna 
		$o->unsigned=null;//1 se a coluna � sem sinal 
		$o->zerofill=null;//1 se a coluna � prenchida com zero 
		$o->type_raw=$this->field_type_raw($field);
		$o->precision=$this->field_precision($field);
		$o->scale=$this->field_scale($field);
		return o;
	}
	function fetch_field($field){
		$field++;
		$o=new \StdClass;
		$o->name=$this->field_name($field);//nome da coluna
		$o->orgname=$o->name;
		$o->table=null;//a tabela a qual o objeto pertence 
		$o->orgtable=$o->table;
		$o->def=null;//o valor padr�o da coluna
		$o->max_length=$this->field_size($field);//o limite de tamanho da coluna 
		$o->length=$this->field_precision($field);
		$o->charsetnr=$this->field_type_raw($field);
		$o->flags=null;
		$o->type=$this->field_type_raw($field);//o tipo da coluna 
		$o->decimals=$this->field_scale($field);
		$o->vartype=$this->field_type($field);//o tipo da coluna 
		return $o;
	}
	function fetch_fields(){ 
		$out=array();
		$max=$this->num_fields();
		for($i=0;$i<$max;$i++ ) $out[$i]=$this->fetch_field($i);
		return $out; 
	}
	/*

	function data_seek($offset){ return @$this->res->data_seek($offset); }
	
	//retorna numero de campos
	function fetch_field_direct($fieldnr){ return @$this->res->fetch_field_direct($fieldnr); }
	function field_seek($fieldnr){ return @$this->res->field_seek($fieldnr); }
	function current_field(){ return @$this->res->current_field; }
	function lengths(){ return @$this->res->lengths; }
	*/
	function error() { 
		$e=@oci_error($this->res);
		return $e['message'];
	}
	function errno() { 
		$e=@oci_error($this->res);
		return $e['code'];
	}
}