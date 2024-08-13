<?php
class Db {
	/**
	 * Array associativo com todas as conexões abertas pela classe
	 *
	 * @var array
	 */
	static public $aConn=array();
	static public $connSel;

	/**
	 * Abre uma conexão MySQL utilizando funções mysql_
	 *
	 * @param Array (associativo da classe Dsn -> $dsn['host'],$dsn['user'],$dsn['passwd'],$dsn['db'])
	 * @return Resource Link
	 */
	static function conn($dsn){
		if (!function_exists('mysql_connect')) die ("Não existe função mysql_connect do PHP para fazer conexão com o banco\r\n");
		$id=self::getIdConn($dsn);
		if (isset(self::$aConn[$id])) return self::$aConn[$id];
		$conn=@mysql_connect($dsn['host'],$dsn['user'],$dsn['passwd']) or exit;
		if ($conn && isset($dsn['db'])) {
			mysql_select_db($dsn['db']);
		}
		if (!$conn) self::error_Host($dsn);
		self::$aConn[$id]=$conn;
		self::$connSel=$conn;
		return $conn;
	}
	/**
	 * Abre uma conexão MySQL utilizando classe mysqli
	 *
	 * @param Array (associativo da classe Dsn -> $dsn['host'],$dsn['user'],$dsn['passwd'],$dsn['db'])
	 * @return Object Resource Link
	 */
	static function conni($dsn){
		$id="i::".self::getIdConn($dsn);
		if (isset(self::$aConn[$id])) return self::$aConn[$id];
		if (isset($dsn['db'])) $conn=new mysqli($dsn['host'],$dsn['user'],$dsn['passwd'],$dsn['db']);
		else $conn=new mysqli($dsn['host'],$dsn['user'],$dsn['passwd']);
		if (!$conn->host_info) self::error_Host($dsn);
		self::$aConn[$id]=$conn;
		return $conn;
	}
	function getIdConn($dsn){
		return implode("/",$dsn);
	}
	function query($conn,$sql){
		$res=$conn->query($sql);
		if($conn->error) die("<pre>Error: $sql\n\n{$conn->error}</pre>");
		return $res;
	}
	function q($sql){
		if (!self::$connSel) die ("Não há conexão ativa");
		$conn=self::$connSel;
		$res=$conn->query($sql);
		if($conn->error) die("<pre>Error: $sql\n\n{$conn->error}</pre>");
		return $res;
	}
	function selConn($conn){
		if ($conn) self::$connSel=$conn;
	}
	/**
	 * Transforma um Result Link em um Array bidimencional [registro][campos]
	 *
	 * @param resource $result ResultLink
	 * @return Array
	 */
	static function parserResult($result){
		$ret=array();
		if ($result && mysql_numrows($result)) while (($line=mysql_fetch_assoc($result))) $ret[]=$line;
		return $ret;
	}
	/**
	 * Enter description here...
	 *
	 * @param object $iResult ClassResult
	 * @return Array
	 */
	static function parserResulti($iResult){
		$ret=array();
		if ($iResult && $iResult->num_rows) while (($line=$iResult->fetch_assoc())) $ret[]=$line;
		return $ret;
	}
	/**
	 * Gera o erro de conexão
	 *
	 * @param Array (associativo da classe Dsn -> $dsn['host'],$dsn['user'],$dsn['passwd'],$dsn['db'])
	 */
	function error_Host($dsn) {
		die("Erro de conxão com o Host {$dsn['host']}/{$dsn['db']}, contate um administrador de sistemas.");
	}
}
