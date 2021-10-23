<?php

namespace EstaleiroWeb\ED\Ext;

class Once {
	protected $version = null;
	protected $versions = [];
	protected $passed = false;

	final public function __construct($version = null) {
		if ($this->passed) return;
		$this->passed = false;
		if (is_null($version)) $version = $this->version;
		if (!array_key_exists($version, $this->versions)) {
			$keys = array_keys($this->versions);
			$arr = preg_grep("/^$version\b/", $keys);
			if ($arr) $version = reset($arr);
			else {
				$arr = preg_grep("/$version/", $keys);
				if ($arr) $version = reset($arr);
				else $version = key($this->versions);
			}
			//print_r([get_class($this), $version, $arr]);
		}
		$versions = $this->versions[$version];
		$this->dependences($version);
		foreach ($versions as $v) print $v;
	}
	public function dependences($version) {
	}
}
