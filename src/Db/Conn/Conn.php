<?php

namespace EstaleiroWeb\ED\Db\Conn;

use EstaleiroWeb\ED\IO\_;
use EstaleiroWeb\ED\IO\Vault;
use PDO;

class Conn extends ConnMain {
	static public $chrPrint = '.';
	static public $drivers = [
		'mysql' => 'mysql',
		'mariadb' => 'mysql',
		'oci' => 'oci',
		'oracle' => 'oci',
		'mssql' => 'sqlsrv',
		'sqlsrv' => 'sqlsrv',
		'dblib' => 'dblib',
		'pgsql' => 'pgsql',
		'postgresql' => 'pgsql',
		'odbc' => 'odbc',
		'sqlite' => 'sqlite',
		'firebird' => 'firebird',
		'cubrid' => 'cubrid',
		'ibm' => 'ibm',
		'informix' => 'informix',
	];
	static public $erClass = '/^(.*?)([^\\\]+[\\\])(\w+)_([^_\\\]+)$/i';

	/**
	 * Get a string connection by Vault class
	 */
	public function __construct($dsn = '', $username = null, $password = null, $options = null) {
		$this->extends = self::dsn($dsn, $username, $password, $options);
	}
	public function __invoke() {
		return call_user_func_array([$this->extends, 'query'], func_get_args());
	}
	public function __toString() {
		return "{$this->extends->__toString()}";
	}

	public function getReadonly() {
		return $this->extends ? $this->extends->getReadonly() : null;
	}

	/**
	 * dsn
	 *
	 * @param  string|array|object $dsn Connection contents
	 * @param  string|null $username
	 * @param  string|null $password
	 * @param  array|null $options
	 * @return ConnMain
	 */
	static public function dsn($dsn = '', $username = null, $password = null, $options = null) {
		if (is_object($dsn)) $dsn = (array) $dsn;
		if (is_array($dsn)) return self::connByArray($dsn);
		elseif (($conn = self::connByVault($dsn))) return $conn;
		elseif ($conn = self::connByDSN($dsn, $username, $password, $options)) return $conn;
	}

	/**
	 * connByArray
	 *
	 * @param  array $arr Connection contents
	 * @param  string|null $name Name of connection
	 * @return ConnMain
	 */
	static private function connByArray(array $arr, $name = null) {
		static $aFlds = ['dsn', 'host', 'port', 'dbname', 'charset', 'uid', 'pwd',];
		//$dsn = 'mysql:dbname=testdb;host=127.0.0.1';
		//dbc:DSN=SAMPLE;UID=john;PWD=mypass
		//$dsn = 'uri:file:///usr/local/dbconnect';

		if (array_key_exists($k = 'protocol', $arr)) {
			$dsn = [];
			foreach ($aFlds as $f) if (array_key_exists($f, $arr)) $dsn[] = "$f={$arr[$f]}";
			$dsn = @$arr['protocol'] . ':' . implode(';', $dsn);
		} else $dsn = @$arr['dsn'];
		if ($dsn == '') _::error('Connection erro', FATAL_ERROR);
		return  self::connByDSN($dsn, @$arr['user'], @$arr['passwd'], @$arr['options'], $name);
	}
	/**
	 * connByVault
	 *
	 * @param  string $key Key of the valut connection
	 * @param  string|null $name Name of connection
	 * @return ConnMain
	 */
	static private function connByVault($key, $name = null) {
		if (!preg_match('/^[0-9A-Z_]*$/i', $key)) return false;
		$v = new Vault;
		$arr = $v($key);
		if (!$arr) return false;
		$name = $key;
		return self::connByArray($arr, $name);
	}
	/**
	 * connByDSN
	 *
	 * @param  string|array|object $dsn Connection contents
	 * @param  string|null $user User of connection
	 * @param  string|null $passwd Password of connection
	 * @param  array|null $options Options of connection
	 * @param  string|null $name Name of connection
	 * @return ConnMain
	 */
	static private function connByDSN($dsn, $user = null, $passwd = null, $options = null, $name = null) {
		$class = preg_replace_callback('/^(\w+)\s*:\s*(.+?)\s*$/', [__CLASS__, 'getClass'], $dsn);
		return new $class($dsn, $user, $passwd, $options, $name);
	}
	static private function getClass($matches) {
		return __NAMESPACE__ . '\\Conn_' . strtoupper($matches[1]);
	}

	static public function getAvailableDrivers() {
		return PDO::getAvailableDrivers();
	}
	static public function getAllDrivers() {
		return self::$drivers;
	}
}
