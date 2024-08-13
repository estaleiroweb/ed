<?php

namespace EstaleiroWeb\ED\Db;

use EstaleiroWeb\ED\IO\_;
use Exception;

trait GetterAndSetter {
	protected $readonly = [];
	protected $protect = [];
	public $extends;

	public function __get($name) {
		if ($this->extends && property_exists($this->extends,$name)) return $this->extends->{$name};
		if (method_exists($this, $fn = 'get' . $name)) return $this->$fn();
		if (array_key_exists($name, $this->readonly)) return $this->readonly[$name];
		return @$this->protect[$name];
	}
	public function __set($name, $value) {
		if ($this->extends && property_exists($this->extends,$name)) $this->extends->{$name} = $value;
		elseif (method_exists($this, $fn = 'set' . $name)) $this->$fn($value);
		else $this->protect[$name]=$value;
		return $this;
	}
	public function __call($fn, $args) {
		if ($this->extends) {
			try {
				if(method_exists($this->extends, $fn)) return call_user_func_array([$this->extends, $fn], $args);
			} catch (Exception $e) {
				$mess = 'Command error (' .  $e->getMessage() . ')';
			}
		} else $mess = 'Extends not init';
		$a = json_encode($args);
		_::error("$mess: $fn($a)");
	}

	public function getReadonly() {
		return $this->readonly;
	}
}
