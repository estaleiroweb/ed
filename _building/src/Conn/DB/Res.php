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
abstract class Res extends Error {
	use \Show;

	private $oConn;
	protected $conn, $sql, $verifyError;
	public $res, $dsn;

	function __construct($conn, $sql, $verifyError = true, $dsn = '') {
		$this->verbose($sql);
		if (!$conn) return false;
		$this->oConn = $conn;
		$this->conn = $conn->conn;
		$this->dsn = $dsn;
		$this->sql = $sql;
		$this->verifyError = $verifyError;
	}
	function __get($nm) {
		if ($nm == 'sql') return $this->sql;
		if (method_exists($this, $nm)) return $this->$nm();
		return $this->res->$nm;
	}
	function __set($nm, $val) {
	}
	function __call($nm, $par) {
		if (!@$this->res) return error($par ? print_r($par, true) : '', 0, "Invalid called function $nm " . $this->conn->error);
		return call_user_func_array(array($this->res, $nm), $par);
	}
	function verifyError() {
		if ($this->verifyError && $this->error()) $this->fatalError();
	}
	function fatalError() {
		$this->verbose($this->sql);
		$this->oConn->fatalError($this->error());
	}
	function fetch_assoc_all() {
		$out = array();
		while ($line = $this->fetch_assoc()) $out[] = $line;
		return $out;
	}
	function fetch_assoc_coll() {
		$i = 0;
		$out = array();
		while ($line = $this->fetch_assoc()) {
			foreach ($line as $k => $v) $out[$k][$i] = $v;
			$i++;
		}
		return $out;
	}
	function fetch_row_all() {
		$out = array();
		while ($line = $this->fetch_row()) $out[] = $line;
		return $out;
	}
	function fetch_row_coll() {
		$i = 0;
		$out = array();
		while ($line = $this->fetch_row()) {
			foreach ($line as $k => $v) $out[$k][$i] = $v;
			$i++;
		}
		return $out;
	}
	function fetch_fields_coll() {
		$in = $this->fetch_fields();
		$out = array();
		if ($in) foreach ($in as $index => $obj) {
			$out['name'][$index] = $obj->name;
			$out['orgname'][$index] = $obj->orgname;
			$out['table'][$index] = $obj->table;
			$out['orgtable'][$index] = $obj->orgtable;
			$out['def'][$index] = $obj->def;
			$out['max_length'][$index] = $obj->max_length;
			$out['length'][$index] = $obj->length;
			$out['charsetnr'][$index] = $obj->charsetnr;
			$out['flags'][$index] = $obj->flags;
			$out['type'][$index] = $obj->type;
			$out['decimals'][$index] = $obj->decimals;
			//$out['vartype'][$index]=$this->trNumType($obj);
		}
		return $out;
	}
	function close() {
		$this->res = null;
	}
	function count() {
		return $this->num_rows();
	}
	function free() {
		$this->close();
	}
	function reccount() {
		return $this->num_rows();
	}
	function field_count() {
		return $this->num_fields();
	}
	function num_fields() {
		return 0;
	}
	function current_field() {
		return 0;
	}
	function num_rows() {
		return 0;
	}
	function lengths() {
		return null;
	}
	function error() {
		return '';
	}
	function errno() {
		return 0;
	}
	function type() {
		return 0;
	}
}
