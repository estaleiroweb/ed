<?php

namespace EstaleiroWeb\ED\Db\Field;
function mssql_fetch_field(){return new Field_SQLSRV(null,null);}
function mssql_field_length(){ return 0;}

class Field_SQLSRV extends Field {
	public $dataTypes = [
		'bit' => [
			'bit'       => ['min' => 1, 'max' => 64, 'descr' => 'A bit field'],
		],
		'int' => [
			'tinyint'  => ['length' => 4,   'ulength' => 3,   'min' => 0,                   'max' => 255,],
			'smallint' => ['length' => 6,   'ulength' => 5,   'min' => -32768,              'max' => 32767,],
			'int'      => ['length' => 11,  'ulength' => 10,  'min' => -2147483648,         'max' => 2147483647,],
			'bigint'   => ['length' => 20,  'ulength' => 20,  'min' => -9223372036854775808, 'max' => 9223372036854775807,],
		],
		'dec' => [
			'decimal'  => ['min' => -1E38,    'max' => 1E38,],
			'numeric'  => ['min' => -1E38,    'max' => 1E38,],
		],
		'float' => [
			'float'    => ['length' => 23,        'min' => -1.79E308, 'max' => 1.79E308,],
			'real'     => ['length' => 53,        'min' => -3.40E38, 'max' => 3.40E38,],
		],
		'datetime' => [
			'date'     => ['length' => null,           'descr' => 'Stores date in the format YYYY-MM-DD'],
			'time'     => ['length' => null,           'descr' => 'Stores time in the format HH:MI:SS'],
			'datetime' => ['length' => null,           'descr' => 'Stores date and time information in the format YYYY-MM-DD HH:MI:SS'],
			'timestamp' => ['length' => null,           'descr' => 'Stores number of seconds passed since the Unix epoch (‘1970-01-01 00:00:00’ UTC]'],
			'year'     => ['length' => 4,              'descr' => 'Stores year in 2 digit or 4 digit format. Range 1901 to 2155 in 4-digit format. Range 70 to 69, representing 1970 to 2069.'],
		],
		'char' => [
			'nchar'    => ['length' => 4000,           'descr' => 'Fixed length with maximum length of 4000 characters'],
			'char'     => ['length' => 8000,           'descr' => 'Fixed length with maximum length of 8000 characters'],
		],
		'string' => [
			'nvarchar' => ['length' => 4000,           'descr' => 'Variable length storage with maximum length of 4000 characters'],
			'varchar'  => ['length' => 8000,           'descr' => 'Variable length storage with maximum length of 8000 characters'],
		],
		'text' => [
			'ntext'    => ['length' => 1099511627776,  'descr' => 'Variable length storage with maximum size of 1GB data'],
			'text'     => ['length' => 2199023255552,  'descr' => 'Variable length storage with maximum size of 2GB data'],
		],
		'binary' => [
			'binary'   => ['length' => 8000,           'descr' => 'Fixed length with maximum length of 8000 bytes'],
			'varbinary' => ['length' => 8000,           'descr' => 'Variable length storage with maximum length of 8000 bytes'],
			'image'    => ['length' => 2199023255552,  'descr' => 'Variable length storage with maximum size of 2GB binary data'],
		],
		'lob' => [
			'clob'     => ['length' => 2199023255552,  'descr' => 'Character large objets that can hold up to 2GB'],
			'blob'     => ['length' => 2199023255552,  'descr' => 'For binary large objects'],
		],
		'others' => [
			'xml'      => ['length' => 2199023255552,  'descr' => 'for storing xml data'],
			'json'     => ['length' => 2199023255552,  'descr' => 'for storing JSON data'],
		],
	];

	public function __construct2($index = null, $res = null, $oConn = null) {
		parent::__construct($index, $res, $oConn);
		if (!$res || !($fld = mssql_fetch_field($res, $index))) return;

		$this->name = $this->orgname = $fld->name;
		$this->table = $this->orgtable = $fld->column_source;
		$this->max_length = $fld->max_length;
		$this->length = mssql_field_length($res, $index);
		//$this->flags=$oConn->trFlag($f=mysql_field_flags($res,$index));
		//$this->type=$oConn->trType($fld->type,$this->length,$f,$fld->max_length);
		$this->type = $this->realType = $fld->type;
		$this->vartype = $this->type;
		//$this->mysqlExtra=$fld;
	}
}
