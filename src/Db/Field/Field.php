<?php

namespace EstaleiroWeb\ED\Db\Field;

use EstaleiroWeb\Traits\GetterAndSetter;
use PDO;

class Field {
	use GetterAndSetter;

	public $dataTypes = [];
	public $raw = false;
	protected $conn, $res;
	/*
		public $index,$name,$orgname,$table,$orgtable,$def,$vartype,$realType;
		public $type,$max_length,$length,$decimals,$charsetnr,$flags;
		public $not_null,$zerofill,$unsigned;
		public $value,$fn;	*/
	
	public function __construct($conn, $res, $readonly = [], $protect = ['value' => null, 'raw' => null, 'fn' => null,]) {
		$this->conn = $conn;
		$this->res = $res;
		$this->readonly = $readonly;
		$this->protect = $protect;
	}
	public function __invoke() {
		return $this->raw ? $this->value : $this->conn->addQuote($this->__toString());
	}
	public function __toString() {
		if ($this->fn) return call_user_func($this->fn, $this->value, $this);
		elseif (is_array($this->value)) return json_encode($this->value, JSON_PRETTY_PRINT);
		//elseif(is_object($this->value)) return "{$this->value}";
		return "{$this->value}";
	}

	public function getType($type = null) {
		static $aTypes = [
			PDO::PARAM_BOOL => 'BOOL',
			PDO::PARAM_NULL => 'NULL',
			PDO::PARAM_INT => 'INT',
			PDO::PARAM_STR => 'STR',
			PDO::PARAM_STR_NATL => 'STR_NATL',
			PDO::PARAM_STR_CHAR => 'STR_CHAR',
			PDO::PARAM_LOB => 'LOB',
			PDO::PARAM_STMT => 'STMT',
			PDO::PARAM_INPUT_OUTPUT => 'INPUT_OUTPUT',
		];
		return is_null($type) ? $aTypes : @$aTypes[$type];
	}
	public function trType() {
		return $this->readonly['type'] = $this->native_type;
	}

	public function get_dataTypeGroup() {
		static $tr = array();
		if (!$tr) foreach ($this->dataTypes as $grp => $tps) foreach ($tps as $tp => $ln) $tr[$tp] = $grp;
		return $tr[$this->vartype];
	}
	/**
	 * @param string $class MYSQL,SQLSRV,OCI,ODBC... see class Field_*
	 */
	public function convert($class) {
		$readonly = $this->readonly;
		$readonly['index'] = null;
		$class = preg_replace('/[^\\]+$/', 'Field_' . strtoupper($class), __CLASS__);
		$obj = new $class($this->conn, $this->res, $readonly, $this->protect);

		$grp = $this->get_dataTypeGroup();
		$fromDataType = @$this->dataTypes[$grp][$this->vartype];
		($length = @$this->length) || ($length = @$fromDataType['length']);
		if ($grp == 'others' || !$fromDataType) {
			$types = $obj->dataTypes['text'];
			end($types);
			$type = key($types);
		} else {
			$types = @$obj->dataTypes[$grp];
			if (count($types) == 1) $type = key($types);
			elseif (array_key_exists($this->vartype, $types) && !@$types[$this->vartype]['length']) {
				$type = $this->vartype;
			} else while ($types) {
				$type = key($types);
				$ln = array_shift($types);
				if (@$ln['length'] && $ln['length'] >= $length) break;
			}
		}
		$precision = @$this->decimals + 0;
		if ($grp == 'datetime') $tam = '';
		elseif ($grp == 'dec') $tam = "({$length},{$precision})";
		elseif ($grp == 'float') $tam = "({$length},{$precision})";
		else $tam = "({$length})";
		$attrs = '';
		if ($this->unsigned) $attrs .= ' UNSIGNED';
		if ($this->not_null) $attrs .= ' NOT NULL';
		if ($this->zerofill) $attrs .= ' ZEROFILL';
		//AUTO_INCREMENT
		return $this->conn->fieldDelimiter($this->name) . ' ' . $type . $tam . $attrs;
	}

	/*
	public function quote() {
		return $this->conn->{__FUNCTION__}(func_get_args());
	}
	public function addQuote() {
		return $this->conn->{__FUNCTION__}(func_get_args());
	}
	public function compare() {
		return $this->conn->{__FUNCTION__}(func_get_args());
	}
	public function stringDelimiter() {
		return $this->conn->{__FUNCTION__}(func_get_args());
	}
	public function escape() {
		return $this->conn->{__FUNCTION__}(func_get_args());
	}
	*/
}
