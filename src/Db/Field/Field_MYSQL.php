<?php

namespace EstaleiroWeb\ED\Db\Field;

class Field_MYSQL extends Field {
	public $dataTypes = [
		'bit' => [
			'bit'       => ['min' => 1, 'max' => 64, 'descr' => 'A bit field'],
		],
		'int' => [
			'tinyint'   => ['length' => 4,   'ulength' => 3, 'min' => 0,                   'max' => 255,                'descr' => 'A very small integer'],
			'smallint'  => ['length' => 6,   'ulength' => 5, 'min' => -32768,              'max' => 32767,              'descr' => 'A small integer'],
			'mediumint' => ['length' => 9,   'ulength' => 8,          'descr' => 'A medium-sized integer'],
			'int'       => ['length' => 11,  'ulength' => 10, 'min' => -2147483648,         'max' => 2147483647,         'descr' => 'A standard integer'],
			'bigint'    => ['length' => 20,  'ulength' => 20, 'min' => -9223372036854775808, 'max' => 9223372036854775807, 'descr' => 'A large integer'],
		],
		'dec' => [
			'decimal'   => ['descr' => 'A fixed-point number'],
			'numeric'   => ['descr' => 'A fixed-point number'],
		],
		'float' => [
			'float'     => ['length' => 23,                'descr' => 'A single-precision floating point number'],
			'real'      => ['length' => 23,                'descr' => 'A single-precision floating point number'],
			'double'    => ['length' => 53,                'descr' => 'A double-precision floating point number'],
		],
		'datetime' => [
			'date'      => ['length' => null,              'descr' => 'A date value in CCYY-MM-DD format'],
			'time'      => ['length' => null,              'descr' => 'A time value in hh:mm:ss format'],
			'datetime'  => ['length' => null,              'descr' => 'A date and time value inCCYY-MM-DD hh:mm:ssformat'],
			'timestamp' => ['length' => null,              'descr' => 'A timestamp value in CCYY-MM-DD hh:mm:ss format'],
			'year'      => ['length' => 4,                 'descr' => 'A year value in CCYY or YY format'],
		],
		'char' => [
			'char'      => ['length' => 4294967295,        'descr' => 'A fixed-length nonbinary (character] string'],
		],
		'string' => [
			'varchar'   => ['length' => 4294967295,        'descr' => 'A variable-length non-binary string'],
		],
		'text' => [
			'tinytext'  => ['length' => 8000,              'descr' => 'A very small non-binary string (255]'],
			'text'      => ['length' => 4294967295,        'descr' => 'A small non-binary string (65535]'],
			'mediumtext' => ['length' => 1099511627776,     'descr' => 'A medium-sized non-binary string (16777215]'],
			'longtext'  => ['length' => 2199023255552,     'descr' => 'A large non-binary string (4294967295]'],
		],
		'binary' => [
			'binary'    => ['length' => 4294967295,        'descr' => 'A fixed-length binary string'],
			'varbinary' => ['length' => 4294967295,        'descr' => 'A variable-length binary string'],
		],
		'lob' => [
			'tinyblob'  => ['length' => 8000,              'descr' => 'A very small BLOB (binary large object]'],
			'blob'      => ['length' => 4294967295,        'descr' => 'A small BLOB'],
			'mediumblob' => ['length' => 1099511627776,     'descr' => 'A medium-sized BLOB'],
			'longblob'  => ['length' => 2199023255552,     'descr' => 'A large BLOB'],
		],
		'others' => [
			'enum'              => ['length' => null,      'descr' => 'An enumeration; each column value may be assigned one enumeration member'],
			'set'               => ['length' => null,      'descr' => 'A set; each column value may be assigned zero or more SET members'],
			'geometry'          => ['length' => null,      'descr' => 'A spatial value of any type'],
			'point'             => ['length' => null,      'descr' => 'A point (a pair of X-Y coordinates]'],
			'linestring'        => ['length' => null,      'descr' => 'A curve (one or more POINT values]'],
			'polygon'           => ['length' => null,      'descr' => 'A polygon'],
			'geometrycollection' => ['length' => null,      'descr' => 'A collection of GEOMETRYvalues'],
			'multilinestring'   => ['length' => null,      'descr' => 'A collection of LINESTRINGvalues'],
			'multipoint'        => ['length' => null,      'descr' => 'A collection of POINTvalues'],
			'multipolygon'      => ['length' => null,      'descr' => 'A collection of POLYGONvalues'],
		],
	];

	/**
	 * name         O nome da coluna
	 * orgname      Nome original da coluna se foi especificado um alias
	 * table        O nome da tabela a qual este campo pertence (se não for calculada)
	 * orgtable     Nome da tabela original se foi especificado um alias
	 * def          O valor padrão para este campo, representando como uma string
	 * max_length   O tamanho máximo do campo no conjunto de resultados.
	 * flags        Um inteiro representando bit-flags para o campo.
	 * type         O tipo de dados usado para este campo
	 * decimals     O número de decimais usados (par campos integer)
	 */
	public function __construct2($index = null, $res = null, $oConn = null) {
		parent::__construct($index, $res, $oConn);
		if (!$res) return;
		$aFld = @$res->fetch_fields();
		if (!$aFld || !array_key_exists($index, $aFld)) return;
		$fld = $aFld[$index];

		$this->name = $fld->name;
		$this->orgname = @$fld->orgname == '' ? $fld->name : $fld->orgname;
		$this->table = $fld->table;
		$this->orgtable = @$fld->orgtable == '' ? $fld->table : $fld->orgtable;

		$this->def = $fld->def;
		$this->max_length = $fld->max_length;
		$this->flags = $fld->flags;
		$this->type = $fld->type;
		$this->decimals = $fld->decimals;

		$this->length = $this->max_length;
		$this->vartype = $this->trNumType();
		//$this->mysqlExtra=$fld;
	}
	public function trType() {
		static $aTypes = [
			'TINY' => 'TINYINT',
			'SHORT' => 'SMALLINT',
			'INT24' => 'MEDIUMINT',
			'LONG' => 'INT',
			'LONGLONG' => 'BIGINT',

			'NEWDECIMAL' => 'DECIMAL',
			'FLOAT' => 'FLOAT',
			'DOUBLE' => 'DOUBLE',

			'BIT' => 'BIT',
			'STRING' => 'BINARY',
			'STRING' => 'ENUM',
			'STRING' => 'SET',
			'STRING' => 'CHAR',

			'BLOB' => 'JSON',
			'BLOB' => 'BLOB',
			'BLOB' => 'MEDIUMBLOB',
			'BLOB' => 'MEDIUMTEXT',
			'BLOB' => 'LONGBLOB',
			'BLOB' => 'LONGTEXT',
			'BLOB' => 'TEXT',
			'BLOB' => 'TINYBLOB',
			'BLOB' => 'TINYTEXT',

			'VAR_STRING' => 'VARBINARY',
			'VAR_STRING' => 'VARCHAR',

			'DATE' => 'DATE',
			'TIME' => 'TIME',
			'DATETIME' => 'DATETIME',
			'TIMESTAMP' => 'TIMESTAMP',
			'YEAR' => 'YEAR',

			'NEWDATE' => 'NEWDATE',

			'GEOMETRY' => 'POINT',
			'GEOMETRY' => 'LINESTRING',
			'GEOMETRY' => 'POLYGON',
			'GEOMETRY' => 'MULTIPOINT',
			'GEOMETRY' => 'MULTILINESTRING',
			'GEOMETRY' => 'MULTIPOLYGON',
			'GEOMETRY' => 'GEOMETRYCOLLECTION',
			'GEOMETRY' => 'GEOMETRY',
		];
		return $this->readonly['type'] = array_key_exists($this->native_type, $aTypes) ? $aTypes[$this->native_type] : $this->native_type;
	}
	public function trNumType() {
		$type = $this->type;
		if ($type == 1) {
			if (!(($this->flags >> 12) & 1)) return ($this->length == 1) ? 'BOOLEAN' : 'CHAR';
		} elseif ($type == 252) {
			$b = ($this->flags >> 4) & 1 ? "BLOB" : "TEXT";
			if ($this->length == 255) return "TINY$b";
			elseif ($this->length == 65535) return $b;
			elseif ($this->length == 16777215) return "MEDIUM$b";
			elseif ($this->length == -1) return "LONG$b";
		} elseif ($type == 253) {
			if (($this->flags >> 7) & 1) return "VARBINARY";
		} elseif ($type == 254) {
			if (($this->flags >> 7) & 1) return "BINARY";
			elseif (($this->flags >> 8) & 1) return "ENUM";
			elseif (($this->flags >> 11) & 1) return "SET";
		}
		return $this->data_type($type);
	}
	public function data_type($type = null) {
		static $data_type = array(
			1 => 'tinyint',
			2 => 'smallint',
			3 => 'int',
			4 => 'float',
			5 => 'double',
			7 => 'timestamp',
			8 => 'bigint',
			9 => 'mediumint',
			10 => 'date',
			11 => 'time',
			12 => 'datetime',
			13 => 'year',
			16 => 'bit',
			252 => 'blob', //is currently mapped to all text and blob types (MySQL 5.0.51a)
			253 => 'varchar',
			254 => 'char',
			246 => 'decimal'
			/*
			DECIMAL           0
			TINY              1
			SHORT             2
			LONG              3
			FLOAT             4
			DOUBLE            5
			NULL              6
			TIMESTAMP         7
			LONGLONG          8
			INT24             9
			DATE             10
			TIME             11
			DATETIME         12
			YEAR             13
			NEWDATE          14
			ENUM            247
			SET             248
			TINY_BLOB       249
			MEDIUM_BLOB     250
			LONG_BLOB       251
			BLOB            252
			VAR_STRING      253
			STRING          254
			GEOMETRY        255
		*/
		);
		if (is_null($type)) return $data_type;
		return array_key_exists($type, $data_type) ? $data_type[$type] : "UNKNOWN";
	}
}
