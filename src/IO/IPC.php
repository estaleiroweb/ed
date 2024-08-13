<?php

namespace EstaleiroWeb\ED\IO;

abstract class IPC {
	static $perms = 0666;
	static $size = 102400;
	static $max_acquire = 1;
	static $auto_release = 1;

	protected $newKey = false;
	protected $param = '';
	protected $pid;
	protected $key = null;
	protected $keyHex = null;
	protected $id = null;
	protected $resource = null;
	protected $types = array(
		'NULL' => 1,
		'boolean' => 10,
		'integer' => 20,
		'double' => 30,
		'float' => 30,
		'string' => 40,
		'array' => 50,
		'object' => 60,
		'resource' => 70,
		'' => 99,
	);

	final public function __construct($key = null, $pid = null) {
		$this->pid = $pid;
		$this->key = $key;
		if ($key === null || $key === 0) {
			$this->newKey = true;
			$this->key = $this->createKey_auto();
			//$this->key=$this->createKey_seq();
		} else $this->keyHex = $this->dechex($key);
		$this->newKey();
	}
	final public function __destruct() {
		if ($this->newKey) $this->delKey();
	}
	abstract protected function newKey();
	abstract protected function delKey();
	final public function getNumType($var) {
		$t = @$this->types[gettype($var)];
		return $t ? $t : $this->types[''];
	}
	final public function getType($numType) {
		return array_search($numType, $this->types);
	}

	final public function reset($getAsOwner = false) {
		if ($getAsOwner) $this->newKey = true;
		$this->delKey();
		return $this->newKey();
	}
	final public function key() {
		return $this->key;
	}
	final public function resource() {
		return $this->resource;
	}
	final public function allKeys() { //List, key, id => { values }
		return array(
			'sem' => $this->keys('-s'),
			'shm' => $this->keys('-m'),
			'msq' => $this->keys('-q'),
		);
	}
	final public function keys($param = null) { //key, id => { values }
		$out = $ret = array();
		if (!$param) $param = $this->param;
		exec($cmd = "ipcs {$param} | grep -e '^[0-9k]'", $ret);
		$cols = preg_split('/\s+/', array_shift($ret));
		array_shift($cols);
		array_shift($cols);
		$cols = array_flip($cols);
		foreach ($ret as $line) {
			$line = preg_split('/\s+/', $line);
			$key = (int)hexdec(array_shift($line));
			$id = array_shift($line);
			$out[$key][$id] = array();
			foreach ($cols as $name => $k) $out[$key][$id][$name] = @$line[$k];
		}
		return $out;
	}
	final public function exists($key, $id = null) {
		$l = $this->keys();
		if (!array_key_exists($key, $l)) return false;
		return $id === null || array_key_exists($id, $l[$key]);
	}
	final public function clearAll() {
		$a = array('-s', '-m', '-q');
		foreach ($a as $param) $this->clear($param);
	}
	final public function clear($param = null) {
		if (!$param) $param = $this->param;
		$l = $this->keys($param);
		foreach ($l as $key => $ids) foreach ($ids as $id => $line) `ipcrm $param $id`;
	}
	final public function createKey_auto() {
		$param = strtoupper($this->param);
		$perm = decoct(self::$perms);
		switch ($param) {
			case '-S':
				$p = $param . ' ' . self::$max_acquire . ' -p' . $perm;
				break;
			case '-M':
				$p = $param . ' ' . self::$size . ' -p' . $perm;
				break;
			case '-Q':
				$p = $param;
				break;
			default:
				exit;
		}
		$this->id = trim(`ipcmk {$p} | sed -r 's/^[^0-9]+//'`);
		$this->keyHex = trim(`ipcs -m | grep " {$this->id} " | cut -d ' ' -f 1`);
		return hexdec($this->keyHex);
	}
	final public function createKey_seq() {
		$l = $this->keys();
		if (!$l) return 1;
		$l[] = '';
		end($l);
		$key = key($l);
		$this->keyHex = $this->dechex($key);
		return $key;
	}
	final public function del($key = null) {
		if ($key === null) $key = $this->key;
		if ($key) $this->delHex($this->dechex($key));
	}
	final public function delHex($key) { //0x4598cd4c
		$param = strtoupper($this->param);
		`ipcrm $param $key 2> /dev/null`;
	}
	final public function dechex($key) {
		return '0x' . str_pad(dechex($key), 8, 0, STR_PAD_LEFT);
	}
}
