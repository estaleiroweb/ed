<?php

namespace EstaleiroWeb\ED\Ext;

use EstaleiroWeb\Cache\Config;
use EstaleiroWeb\ED\IO\_;
use EstaleiroWeb\ED\Screen\OutHtml;

class Once {
	protected $version = null;
	protected $versions = [];
	protected $passed = false;

	final public function __construct($version = null) {
		if ($this->passed) return;
		$this->passed = false;
		if (is_null($version)) $version = $this->version;
		if (!key_exists($version, $this->versions)) {
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
		if (key_exists($version, $this->versions)) $versions = $this->versions[$version];
		else {
			reset($this->versions);
			$version = key($this->versions);
			$versions = current($this->versions);
		}
		$this->dependences($version);
		$o = OutHtml::singleton();
		if(is_array($versions)) {
			foreach ($versions as $v) $o->head($this->tr($v));
		} else $o->head($this->tr($versions));
	}
	final public function tr($val) {
		$c = Config::singleton();
		return preg_replace_callback('/\{([^\}]+?)\}/', function ($m) use ($c) {
			return eval('return ' . $m[1] . ';');
		}, $val);
		return $val;
	}
	public function dependences($version) {
	}
}
