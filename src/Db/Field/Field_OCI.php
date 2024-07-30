<?php

namespace EstaleiroWeb\ED\Db\Field;

class Field_OCI extends Field {
	public $dataTypes = [
		'bit' => [
			'number'     => ['length' => 38,               'descr' => 'Variable-length numeric data. Maximum precision p and/or scale s is 38.'],
		],
		'int' => [
			'number'     => ['length' => 38,               'descr' => 'Variable-length numeric data. Maximum precision p and/or scale s is 38.'],
		],
		'dec' => [
			'number'     => ['length' => 38,               'descr' => 'Variable-length numeric data. Maximum precision p and/or scale s is 38.'],
		],
		'float' => [
			'number'     => ['length' => 38,               'descr' => 'Variable-length numeric data. Maximum precision p and/or scale s is 38.'],
		],
		'datetime' => [
			'date'       => ['length' => null,             'descr' => 'Fixed-length date and time data, ranging from Jan. 1, 4712 B.C.E. to Dec. 31, 4712 C.E.'],
		],
		'char' => [
			'nchar'      => ['length' => 2000,             'descr' => 'Fixed-length character data of length size characters or bytes, depending on the national character set.'],
			'char'       => ['length' => 2000,             'descr' => 'Fixed-length character data of length size bytes.'],
		],
		'string' => [
			'nvarchar2'  => ['length' => 4000,             'descr' => 'Variable-length character data of length size characters or bytes, depending on national character set. A maximum size must be specified.'],
			'varchar2'   => ['length' => 4000,             'descr' => 'Variable-length character data.'],
		],
		'text' => [
			'long'       => ['length' => 2199023255552,    'descr' => 'Variable-length character data.'],
		],
		'binary' => [
			'raw'        => ['length' => 2000,             'descr' => 'Variable-length raw binary data.'],
			'long raw'   => ['length' => 2199023255552,    'descr' => 'Variable-length raw binary data.'],
		],
		'lob' => [
			'nclob'      => ['length' => 4398046511104,    'descr' => 'Single-byte or fixed-length multibyte national character set (NCHAR] data.'],
			'clob'       => ['length' => 4398046511104,    'descr' => 'Single-byte character data.'],
			'blob'       => ['length' => 4398046511104,    'descr' => 'Unstructured binary data.'],
			'bfile'      => ['length' => 4398046511104,    'descr' => 'Binary data stored in an external file.'],
		],
		'others' => [
			'rowid'      => ['length' => null,             'descr' => 'Binary data representing row addresses.'],
			'mlslabel'   => ['length' => null,             'descr' => 'Trusted Oracle datatype.'],
		],
	];

	public function __construct2($index = null, $res = null, $oConn = null) {
		parent::__construct($index, $res, $oConn);
		if (!$res) return;

		$this->name = $this->orgname = @oci_field_name($res, $index + 1);
		$this->table = $this->orgtable = null;
		$this->max_length = @oci_field_size($res, $index + 1); //o limite de tamanho da coluna 
		$this->length = @oci_field_precision($res, $index + 1);
		$this->vartype = @oci_field_type($res, $index + 1); //o tipo da coluna 
		$this->type = $this->charsetnr = @oci_field_type_raw($res, $index + 1); //o tipo da coluna 
		$this->decimals = $this->scale = @oci_field_scale($res, $index + 1);

		//$o->not_null=!@oci_field_is_null($res,$index+1);//1 se a coluna não puder ser NULL 
		//$o->primary_key=null;//1 se a coluna é a chave primária 
		//$o->unique_key=null;//1 se a coluna é a chave única 
		//$o->multiple_key=null;//1 se a coluna é uma chave não-única 
		//$o->numeric=null;//1 se a coluna é numérica 
		//$o->unsigned=null;//1 se a coluna é sem sinal 
		//$o->blob=null;//1 se a coluna é um BLOB 
		//$o->zerofill=null;//1 se a coluna é prenchida com zero 
		//$this->flags=null;
		//$this->mysqlExtra=$fld;
	}
}
