<?php

namespace EstaleiroWeb\ED\Db\Res;

use EstaleiroWeb\ED\Db\GetterAndSetter;
use Iterator;
use Countable;
use PDO;

abstract class Res implements Iterator, Countable {
	use GetterAndSetter;

	protected $conn;

	public function __construct($conn, $res, $query, $mode = null) {
		$this->conn = $conn;
		$this->extends = $res;
		$this->readonly = [
			'mode' => $mode,
			'query' => $query,
			'recNum' => 0,
		];
		$this->resetFields(); // readonly: fields, fieldsNames

		//$stmt->setFetchMode(PDO::FETCH_ASSOC);
		//_::show($this->extends);
		//_::show($this->readonly);exit;
	}
	public function __destruct() {
		$this->close();
	}
	public function __toString() {
		return "{$this->query}";
	}
	public function __invoke() {
		return $this->extends;
	}

	public function getFetchs() {
		return [
			PDO::FETCH_LAZY => 'LAZY',
			PDO::FETCH_BOTH => 'BOTH',
			PDO::FETCH_OBJ => 'OBJ',
			PDO::FETCH_BOUND => 'BOUND',
			PDO::FETCH_COLUMN => 'COLUMN',
			PDO::FETCH_CLASS => 'CLASS',
			PDO::FETCH_INTO => 'INTO',
			PDO::FETCH_FUNC => 'FUNC',
			PDO::FETCH_GROUP => 'GROUP',
			PDO::FETCH_KEY_PAIR => 'KEY_PAIR',
			PDO::FETCH_CLASSTYPE => 'CLASSTYPE',
			PDO::FETCH_SERIALIZE => 'SERIALIZE',
		];
	}
	public function getFields() {
		return $this->fetch_fields();
	}
	public function getFieldsNames() {
		return $this->fields();
	}
	public function defaultMode($mode = null, $default = PDO::FETCH_ASSOC) {
		if (is_null($mode)) $mode = $this->mode;
		if (is_null($mode)) $mode = $default;
		return $mode;
	}

	public function current() {
		return $this->readonly['current'];
	}
	public function key() {
		return $this->readonly['recNum'];
	}	
	/**
	 * next
	 *
	 * @return void
	 */
	public function next() {
		$this->readonly['recNum']++;
	}	
	/**
	 * rewind
	 *
	 * @return void
	 */
	public function rewind() {
		$this->readonly['recNum'] = 0;
	}	
	/**
	 * valid
	 *
	 * @return void
	 */
	public function valid() {
		return $this->readonly['current'] = $this->fetch(null, PDO::FETCH_ORI_ABS, $this->readonly['recNum']);
	}	
	/**
	 * count
	 *
	 * @return int
	 */
	public function count() {
		return $this->rowCount();
	}

	public function rowCount() {
		if ($this->extends) return $this->extends->rowCount();
		if (preg_match('/^\s*select\b/i', $this->query)) {
			//_::show("SELECT COUNT(1) FROM ({$this->query})t");
			return $this->conn->fastValue("SELECT COUNT(1) FROM ({$this->query})t");
		}
	}
	public function num_rows() {
		return $this->rowCount();
	}
	public function reccount() {
		return $this->rowCount();
	}
	public function length() {
		return $this->rowCount();
	}

	public function close() {
		$this->conn->release($this);
		return $this->extends?@$this->extends->closeCursor():false;
	}
	public function free() {
		return $this->extends->closeCursor();
	}
	public function free_result() {
		return $this->extends->closeCursor();
	}

	public function fetch($mode = null, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0) {
		return $this->extends->fetch($this->defaultMode($mode, PDO::FETCH_BOTH), $cursorOrientation, $cursorOffset);
	}
	public function fetch_all($mode = null) {
		return $this->extends->fetchAll($this->defaultMode($mode, PDO::FETCH_NUM));
	}
	public function fetch_all_col($mode = null) {
		$i = 0;
		$out = [];
		while ($line = $this->fetch($mode)) {
			foreach ($line as $k => $v) $out[$k][$i] = $v;
			$i++;
		}
		return $out;
	}
	public function fetch_array($mode = PDO::FETCH_BOTH) {
		return $this->extends->fetch($mode);
	}
	public function fetch_assoc() {
		return $this->extends->fetch(PDO::FETCH_ASSOC);
	}
	public function fetch_assoc_all() {
		return $this->extends->fetchAll(PDO::FETCH_ASSOC);
	}
	public function fetch_assoc_col() {
		return $this->fetch_all_col(PDO::FETCH_ASSOC);
	}
	public function fetch_row() {
		return $this->extends->fetch(PDO::FETCH_NUM);
	}
	public function fetch_row_all() {
		return $this->extends->fetchAll(PDO::FETCH_NUM);
	}
	public function fetch_row_col() {
		return $this->fetch_all_col(PDO::FETCH_NUM);
	}
	public function fetch_both() {
		return $this->extends->fetch(PDO::FETCH_BOTH);
	}
	public function fetch_both_all() {
		return $this->extends->fetchAll(PDO::FETCH_BOTH);
	}
	public function fetch_both_col() {
		return $this->fetch_all_col(PDO::FETCH_BOTH);
	}
	public function fetch_bound() {
		return $this->extends->fetch(PDO::FETCH_BOUND);
	}
	public function fetch_bound_all() {
		return $this->extends->fetchAll(PDO::FETCH_BOUND);
	}
	public function fetch_bound_col() {
		return $this->fetch_all_col(PDO::FETCH_BOUND);
	}
	public function fetch_object() {
		return $this->extends->fetch(PDO::FETCH_OBJ);
	}
	public function fetch_object_all() {
		return $this->extends->fetchAll(PDO::FETCH_OBJ);
	}
	public function fetch_object_col() {
		return $this->fetch_all_col(PDO::FETCH_OBJ);
	}

	public function error() {
		return $this->errorInfo();
	}
	public function errno() {
		return $this->errorCode();
	}

	public function field_count() {
		return $this->columnCount();
	}
	public function num_fields() {
		return $this->columnCount();
	}

	//public fetch_field_direct(int $index)
	public function resetFields() {
		$this->readonly['fields'] = $this->readonly['fieldsNames'] = [];
		return $this;
	}
	public function fields() {
		if (!$this->readonly['fieldsNames']) {
			$o = @$this->fetch_fields();
			foreach ($o as $obj) $this->readonly['fields'][] = $obj->orgname ? $obj->orgname : $obj->name;
		}
		return $this->readonly['fields'];
	}
	public function fetch_field($index = 0) {
		$class = $this->conn->fldClass;
		$readonly = $this->getColumnMeta($index);
		$readonly['index'] = $index;
		$f = new $class($this->conn, $this, $readonly);
		$f->trType();
		return $f;
	}
	public function fetch_fields() {
		if (!$this->readonly['fields']) {
			$tam = $this->columnCount();
			for ($i = 0; $i < $tam; $i++) $this->readonly['fields'][$i] = $this->fetch_field($i);
		}
		return $this->readonly['fields'];
	}
	public function fetch_fieldsTable() {
		$fields = $this->fetch_fields();
		foreach ($fields as &$v) $v = $v->readonly;
		return $fields;
	}
	public function fetch_fields_coll() {
		$in = $this->fetch_fields();
		$out = [];
		if ($in) foreach ($in as $index => $obj) {
			$arr = $obj->getReadonly();
			foreach ($arr as $k => $v) $out[$k][$index] = $v;
		}
		return $out;
	}

	//TODO Implement
	public function data_seek($offset) {
	}
	public function fetch_field_direct($fieldnr) {
	}
	public function field_seek($fieldnr) {
	}
}
