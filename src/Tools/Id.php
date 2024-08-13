<?php

namespace EstaleiroWeb\ED\Tools;

class Id {
	static private $instance;
	public $ids = array();
	private $idCount = 0;
	private $protect = array('id' => '');
	private $hashs = array();

	private function __construct() {
	}
	function __put($nm, $v) {
	}
	function __get($nm) {
		if (isset($this->protect[$nm])) return $this->protect[$nm];
	}
	function __tostring() {
		return $this->id;
	}
	function newId($id = false) {
		if ($id !== false) {
			if (isset($this->ids[$id])) die("Id '$id' já está sendo usado por outro objeto");
			$this->ids[$id] = $id;
			$this->protect['id'] = $id;
			return;
		}
		while (isset($this->ids[$i = "id{$this->idCount}"])) $this->idCount++;
		$this->ids[$i] = $this->idCount;
		$this->protect['id'] = $i;
	}
	static public function getBackTrace($hop = false) {
		$bt = debug_backtrace();
		if ($hop === false) {
			foreach ($bt as $k => $v) unset($bt[$k]['object']);
			return $bt;
		}
		$out = array();
		foreach ($bt as $k => $v) if ($v['file'] != __FILE__ && --$hop < 0) {
			unset($v['object']);
			return $v;
		}
		return array('file' => '', 'line' => '', 'function' => '', 'class' => '', 'type' => '', 'args' => '',);
		/*
			Array
			(
				[0] => Array
					(
						[file] => /var/www/html/shared/easyData/php/Element.php
						[line] => 239
						[function] => getIdHash
						[class] => Id
						[type] => ->
						[args] => Array
							(
							)

					)

				[1] => Array
					(
						[file] => /var/www/html/shared/bdc2/cpe/dados/edit.php
						[line] => 98
						[function] => validadeSearch
						[class] => Element
						[type] => ->
						[args] => Array
							(
							)

					)

			)
		*/
	}
	static public function getRootIdHash() {
		$bt = array_pop(debug_backtrace());
		return md5($bt['file']) . '_' . $bt['line'];
	}
	static public function getRootId() {
		$bt = array_pop(debug_backtrace());
		return $bt['file'] . '[' . $bt['line'] . ']';
	}
	static public function getIdHash($hop = 0) {
		$bt = self::getBackTrace($hop + 0);
		return md5($bt['file']) . '_' . $bt['line'];
	}
	static public function getIdClass($hop = 0) {
		$bt = self::getBackTrace($hop + 0);
		return $bt['file'] . '[' . $bt['line'] . ']';
	}
	static public function singleton($id = false) {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		self::$instance->newId($id);
		return self::$instance;
	}
}
