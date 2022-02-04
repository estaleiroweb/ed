<?php

namespace EstaleiroWeb\ED\Db\Conn;

class Conn_OCI extends ConnMain {
	public $delimiters = [
		'tableStart' => '"',
		'tableEnd' => '"',
		'fieldStart' => '"',
		'fieldEnd' => '"',
		'string' => '\'',
	];
	public $autocommit = false;


	public function addQuote($value) {
		if (is_numeric($value)) return $this->stringDelimiter($this->escape_string($value));
		return parent::addQuote($value);
	}
	public function fieldCompareValue($field, $value) {
		if (is_numeric($value)) return $this->fieldDelimiter($field) . '=' . $this->stringDelimiter($value);
		return parent::fieldCompareValue($field, $value);
	}
	public function autocommit($bool) {
		return $this->autocommit = $bool;
	}
	public function affected_rows() {
		return oci_num_rows($this->readOnly['conn']);
	}
	public function insert_id($sequence) {
		$line = $this->fastline("SELECT $sequence.CURRVAL AS INSERT_ID FROM DUAL");
		return @$line['INSERT_ID'] + 0;
	}
	public function get_client_info() {
		$line = $this->fastline("SELECT UTL_INADDR.GET_HOST_NAME || ' - ' || UTL_INADDR.GET_HOST_ADDRESS as HOSTNAME FROM DUAL");
		return @$line['HOSTNAME'];
	}
	public function ping() {
		//verifica se conn está ativa //FIXME
		$line = $this->fastline("SELECT 1 AS TEST FROM DUAL");
		if (!@$line['TEST']) $this->connect($this->readOnly['host'], $this->readOnly['user'], $this->readOnly['pass']);
		return @$this->readOnly['conn'] ? true : false;
	}
	public function get_server_info() {
		//select * from v$version
		$line = $this->fastline('SELECT BANNER FROM SYS.V_$VERSION WHERE ROWNUM=1');
		return @$line['BANNER'];
	}
	public function merge($tblTo, $line = null, $keysC = null, $caracater = '.') {
		static $keyComp = array();
		static $keys = array();
		static $sum = array();

		if ($line) {
			if (!@$keys[$tblTo]) {
				$keyComp[$tblTo] = array_flip(preg_split('/\s*[;,]\s*/', $keysC));
				$keys[$tblTo] = $this->mountFieldsKeys($line);
				$sum[$tblTo] = 0;
			}
			$where = $this->mountFieldsConpareValues(array_intersect_key($line, $keyComp[$tblTo]));
			$set = $this->mountFieldsSetValues(array_diff_key($line, $keyComp[$tblTo]));
			$sql = "MERGE INTO $tblTo USING dual ON ($where) ";
			$sql .= "WHEN MATCHED THEN UPDATE SET $set ";
			$sql .= "WHEN NOT MATCHED THEN INSERT ({$keys[$tblTo]}) VALUES {$this->mountValueInsertLine($line)}";

			//show($sql);
			$this->query($sql);
			$sum[$tblTo]++;
			if ($sum[$tblTo] % 100 == 0) print $caracater;
		}
		if (!@$keys[$tblTo]) return 0;
		$out = $sum[$tblTo];
		if (!$line) {
			unset($keys[$tblTo]);
			$sum[$tblTo] = 0;
		}
		return $out;
	}
	public function showDatabases() {
		return $this->query_all('
			SELECT 
				u.USERNAME "SCHEMA", 
				u.USER_ID "DOMAIN",
				u.USERNAME "OWNER",
				NULL DEFAULT_CHARACTER_SET_NAME,
				NULL DEFAULT_COLLATION_NAME,
				NULL DEFAULT_CHARACTER_SET_CATALOG, 
				NULL DEFAULT_CHARACTER_SET_SCHEMA,
				NULL SQL_PATH
				-- u.CREATED, u.COMMON, u.ORACLE_MAINTAINED
			FROM ALL_USERS u 
			ORDER BY USERNAME
		');
	}
	public function showTables($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showViews($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showFunctions($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showProcedures($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showEvents($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
	public function showAllObjects($db = null) {
		if (!($db = $db ? $db : $this->db)) return;
	}
}
