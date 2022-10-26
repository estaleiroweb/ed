<?php

namespace EstaleiroWeb\ED\Db\Conn;

class Conn_DBLIB extends ConnMain {
	public $delimiters = [
		'tableStart' => '[',
		'tableEnd' => ']',
		'fieldStart' => '[',
		'fieldEnd' => ']',
		'string' => '"',
	];
}
