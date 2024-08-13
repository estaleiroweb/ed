<?php

namespace EstaleiroWeb\ED\Secure;

use EstaleiroWeb\Traits\GetterAndSetter;

class Common {
	use GetterAndSetter;

	function error($error = null, $show = false) {
		$this->readonly['Error'] = $error;
		if ($show && $error) print "<div class='container text-danger bg-danger'>$error</div>";
		return !$error;
	}
	function runCaller_array($fn, $args = array()) {
		$bt = debug_backtrace();
		foreach ($bt as $line) {
			if (@$line['object'] && $line['object'] !== $this) {
				return call_user_func_array(array($line['object'], $fn), $args);
			}
		}
	}
	function dbCheckParameters($param) {
		if (is_array($param)) {
			foreach ($param as &$v) {
				if (preg_match('/^\$\(\((.*)\)\)$/', $v, $ret)) $v = trim($ret[1]);
				else $v = Secure::$conn->addQuote($v);
			}
			return implode(', ', $param);
		} elseif (is_object($param)) return $this->dbCheckParameters((array)$param);
		return Secure::$conn->addQuote($param);
	}
	###Callers of Database###
	function dbFunction($fn, $param = []) {
		$line = Secure::$conn->fastLine('SELECT ' . Secure::$db . '.' . $fn . '(' . $this->dbCheckParameters($param) . ') r');
		return @$line['r'];
	}
	function dbProcedure($fn, $param = []) {
		return Secure::$conn->fastLine('CALL ' . Secure::$db . '.' . $fn . '(' . $this->dbCheckParameters($param) . ')');
	}
	function dbProcedureAll($pc, $param = []) {
		return Secure::$conn->query_all('CALL ' . Secure::$db . '.' . $pc . '(' . $this->dbCheckParameters($param) . ')');
	}
	static function dbViewAll($vw) {
		return Secure::$conn->query_all('SELECT * FROM ' . Secure::$db . '.' . $vw);
	}
	public function logOnTrErrors($error = 0) {
		return $this->dbProcedure('pc_User_LogOnTrErrors', (int)$error);
	}
	public function erroByToken(&$token) {
		if (!$token) return 9;
		if (strlen($token) > 1) return 0;
		$err = (int)$token;
		$token = '';
		return $err;
	}
}
