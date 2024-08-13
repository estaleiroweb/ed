<?php

namespace EstaleiroWeb\ED\Db;

class Raw {
	private $val;
	public function __construct($val = null) {
		$this->val = $val;
	}
	public function __toString() {
		return $this->val;
	}
	public function __invoke() {
		return $this->val;
	}
}
