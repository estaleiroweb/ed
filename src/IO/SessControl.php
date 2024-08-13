<?php

namespace EstaleiroWeb\ED\IO;

class SessControl {
	static private $instance = [];
	private $protect = ['id' => '', 'idFile' => ''];

	static public function singleton($id = null, $idFile = null, $get_QUERY_STRING = false) {
		global $__autoload;

		if (is_null($idFile)) $idFile = $__autoload->url . ($get_QUERY_STRING ? $_SERVER['QUERY_STRING'] : '');
		//if(is_null($idFile)) $idFile=Secure::$idFile?Secure::$idFile:$__autoload->url;
		//if(is_null($idFile)) $idFile=Secure::$idFile?Secure::$idFile:$__autoload->host.':'.$__autoload->port;

		//show($idFile);
		if (is_null($id)) {
			$bt = debug_backtrace();
			$id = @$bt[1]['class'];
		}
		if (!isset(self::$instance[$idFile][$id])) {
			$c = __CLASS__;
			self::$instance[$idFile][$id] = new $c($id, $idFile);
		}
		return self::$instance[$idFile][$id];
	}
	private function __construct($id = null, $idFile = null) {
		//if (!session_id()) session_start();// Inicia a sessão
		@session_start(); // Inicia a sessão
		if ($id) $this->protect['id'] = $id;
		if ($idFile) $this->protect['idFile'] = $idFile;
		if (!array_key_exists($this->protect['idFile'], $_SESSION)) $_SESSION[$this->protect['idFile']] = [];
		if (!array_key_exists($this->protect['id'], $_SESSION[$this->protect['idFile']])) $_SESSION[$this->protect['idFile']][$this->protect['id']] = [];
		//show(unserialize($_SESSION[$this->protect['idFile']][$this->protect['id']]));
	}
	function __get($nm) {
		if (array_key_exists($nm, $_SESSION[$this->protect['idFile']][$this->protect['id']])) return unserialize($_SESSION[$this->protect['idFile']][$this->protect['id']][$nm]);
	}
	function __set($nm, $val) {
		$_SESSION[$this->protect['idFile']][$this->protect['id']][$nm] = serialize($val);;
	}
	function __isset($nm) {
		return array_key_exists($nm, $_SESSION[$this->protect['idFile']][$this->protect['id']]);
	}
	function __unset($nm) {
		unset($_SESSION[$this->protect['idFile']][$this->protect['id']][$nm]);
	}
	function id() {
		return $this->protect['id'];
	}
	function idFile() {
		return $this->protect['idFile'];
	}
	function prOut($mess) {
		file_put_contents('/var/www/html/logs/test.txt', "$mess\n", FILE_APPEND);
	}
	function getSess() {
		return $this->get();
	}
	function set(array $data) {
		if ($data) {
			foreach ($data as $k => $v) $_SESSION[$this->protect['idFile']][$this->protect['id']][$k] = serialize($v);
		} else $_SESSION[$this->protect['idFile']][$this->protect['id']] = [];
		return $this;
	}
	function get() {
		$out = [];
		foreach ($_SESSION[$this->protect['idFile']][$this->protect['id']] as $k => $v) $out[$k] = unserialize($v);
		return $out;
	}
	function getRaw() {
		return $_SESSION[$this->protect['idFile']][$this->protect['id']];
	}
	function destroy() {
		return $this->destroy_id();
	}
	function destroy_all() {
		session_destroy();
		$this->__construct($this->protect['id'], $this->protect['idFile']);
		return $this;
	}
	function destroy_file() {
		unset($_SESSION[$this->protect['idFile']]);
		$this->__construct($this->protect['id'], $this->protect['idFile']);
		return $this;
	}
	function destroy_id() {
		$id = $this->protect['id'];
		$idFile = $this->protect['idFile'];
		unset($_SESSION[$this->protect['id']][$this->protect['idFile']]);
		$this->__construct($this->protect['id'], $this->protect['idFile']);
		return $this;
	}
}
