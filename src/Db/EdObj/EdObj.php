<?php

namespace EstaleiroWeb\ED\Db\EdObj;

class EdObj {
	public $query;
	public $parameters=[];
	public $fields=[];
	public $errors=[];
	public $tables=[];
	/**
	 * Return type of query:
	 * NULL
	 * SELECT
	 * INT
	 * REAL
	 * TEXT
	 * BLOB
	 * DATE
	 * DATETIME
	 * TIME
	 */
	public $return=[];

	public function __construct() {
	}
	public function __toString() {
		return "{$this->query}";
	}
}
