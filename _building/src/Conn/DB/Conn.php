<?php

namespace DB;

class Conn {
	private static $instance = [];
	public static $dsnFile = null;

	public static function singleton($strConn = false) {
		$splitConn = self::rebuildDsn($strConn);
		if (!@$splitConn['scheme'] || !@$splitConn['fragment']) die('Erro Scheme or Fragment on DSN');
		$dsn = $splitConn['fragment'];
		if (!isset(self::$instance[$dsn])) {
			$c = self::buildCaller($splitConn['scheme']);
			//print_r([$c,$splitConn]);exit;
			self::$instance[$dsn] = new $c($splitConn);
		}
		return self::$instance[$dsn];
	}
	public static function dsn($dsn = false) {
		return self::singleton($dsn);
	}
	public static function open($dsn = false) {
		return self::singleton($dsn);
	}
	public static function close($strDsn = false) {
		if ($strDsn === false) self::$instance = [];
		else unset(self::$instance[$strDsn]);
	}
	private static function rebuildDsn($strConn = false) {
		if (!$strConn) $strConn = 'localhost';
		if (is_string($strConn)) {
			$d = Dsn::singleton(self::$dsnFile);
			if (preg_match('/^\w+$/', $strConn) && ($str = $d->$strConn)) return $d->splitURL($str);
			if (!preg_match('/^\w+:\/\//', $strConn)) $strConn = $d->getDefault()['scheme'] . '://' . $strConn;
			$p = parse_url($strConn);
			return $d->splitURL($strConn . (@$p['fragment'] ? '' : '#' . self::mountDSN($p)));
		} elseif (is_array($strConn)) {
			if (!@$strConn['fragment']) $strConn['fragment'] = self::mountDSN($strConn);
			return $strConn;
		}
		die('Erro na string DSN');
	}
	private static function buildCaller($scheme) {
		$nm = '';
		switch (strtolower($scheme)) {
			case 'mysqli':
				$nm = 'MySQLi';
				break;
			case 'mysql':
				$nm = 'MySQL';
				break;
			case 'mssqlora':
				$nm = 'MSSQL_ORA';
				break;
			case 'ms-sql-s':
			case 'mssql':
				$nm = 'MSSQL';
				break;
			case 'ora':
			case 'oracle':
				$nm = 'Oracle';
				break;
			default:
				trigger_error('Scheme Database unknown: ' . $scheme, E_USER_ERROR);
		}
		return '\\' . __NAMESPACE__ . '\\' . $nm . '\\Link';
	}
	/**
	 * regra de formação: scheme://user:pass@host:port/path?query#fragment
	 * scheme   [a-z0-9+.-]
	 * user     [a-z0-9 !@#%&*()_=+[]{}?,.;\|~-]
	 * pass     [a-z0-9 !@#%&*()_=+[]{}?,.;\|~-]
	 * host     [a-z0-9 !#%&*()_=+[]{}?,.;\|~-]
	 * port     [0-9]
	 * path     [a-z0-9 !@%&*()_=+[]{},.;\|~-]
	 * query    [a-z0-9 !@%&*()_=+[]{}?,.;\|~-]
	 * fragment [a-z0-9 !@#%&*()_=+[]{}?,.;\|~-]
	 * 
	 * $p=parse_url('mysqli://user:user@host:3306/banco_ereg/table:name_ereg/field:name_ereg?propriedade1=valor1&propriedade2=valor2#id');
	 * parse_str($p['query'],$p['query']);
	 * print_r($p);
	 */
	private static function mountDSN($p) {
		$scheme = @$p['scheme'] ? $p['scheme'] . '://' : 'mysql://';
		$user  = @$p['user']  ? $p['user'] . '@'    : '';
		$host  = @$p['host']  ? $p['host']        : 'localhost';
		$port  = @$p['port']  ? ':' . @$p['port']   : '';
		$path  = @$p['path']  ? $p['path']        : (@$p['db'] ? '/' . $p['db'] : '');
		return rawurlencode($scheme . $user . $host . $port . $path);
	}
}
