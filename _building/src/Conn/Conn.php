<?php
class Conn {
	static public $instance = array();
	static public $dsnFile = null;

	//showResult
	static public $fit_maxFieldLength = 30;
	static public $fit_maxLength = 180;
	static public $fit_separator = ' ';
	static public $fit_separatorStart = '';
	static public $fit_separatorEnd = '';
	static public $fit_headLineTop = '-';
	static public $fit_headLineBottom = '-';
	static public $fit_footLine = '=';

	static public function singleton($strConn = false) {
		$splitConn = self::rebuildDsn($strConn);
		if (!@$splitConn['scheme'] || !@$splitConn['fragment']) die('Erro Scheme or Fragment on DSN');

		$dsn = $splitConn['fragment'];
		if (!isset(self::$instance[$dsn])) {
			$c = self::buildClassCaller($splitConn['scheme']);
			//print_r(array($c,$splitConn));print "\n";exit;
			self::$instance[$dsn] = new $c($splitConn);
		}
		return self::$instance[$dsn];
	}
	static public function buildClassCaller($scheme) {
		$scheme = strtolower($scheme);
		if ($scheme == 'ms-sql-s') $scheme = 'mssql';
		elseif ($scheme == 'ora') $scheme = 'oracle';
		return __CLASS__ . "_" . $scheme;
	}
	static public function dsn($dsn = false) {
		return self::singleton($dsn);
	}
	static private function rebuildDsn(&$strConn = false) {
		if (!$strConn) $strConn = 'localhost';
		if (is_string($strConn)) {
			$d = Dsn::singleton(self::$dsnFile);
			if (preg_match('/^\w+$/', $strConn) && ($str = $d->$strConn)) return $d->splitURL($str);
			if (!preg_match('/^\w+:\/\//', $strConn)) $strConn = $d->getDefault()['scheme'] . '://' . $strConn;
			$p = parse_url($strConn);
			return $d->splitURL($strConn . (@$p['fragment'] ? '' : '#' . self::mountDSN($p)));
		} elseif (is_array($strConn)) {
			if (array_key_exists('dsn', $strConn)) return self::rebuildDsn($strConn['dsn']);
			if (!@$strConn['fragment']) $strConn['fragment'] = self::mountDSN($strConn);
			return $strConn;
		}
		die('Erro na string DSN');
	}
	static function mountDSN($p) {
		$scheme = @$p['scheme'] ? $p['scheme'] . '://' : 'mysql://';
		$user  = @$p['user']  ? $p['user'] . '@'    : '';
		$host  = @$p['host']  ? $p['host']        : 'localhost';
		$port  = @$p['port']  ? ':' . @$p['port']   : '';
		$path  = @$p['path']  ? $p['path']        : (@$p['db'] ? '/' . $p['db'] : '');
		return rawurlencode($scheme . $user . $host . $port . $path);
	}
	/*
		regra de formação: scheme://user:pass@host:port/path?query#fragment
		scheme   [a-z0-9+.-]
		user     [a-z0-9 !@#%&*()_=+[]{}?,.;\|~-]
		pass     [a-z0-9 !@#%&*()_=+[]{}?,.;\|~-]
		host     [a-z0-9 !#%&*()_=+[]{}?,.;\|~-]
		port     [0-9]
		path     [a-z0-9 !@%&*()_=+[]{},.;\|~-]
		query    [a-z0-9 !@%&*()_=+[]{}?,.;\|~-]
		fragment [a-z0-9 !@#%&*()_=+[]{}?,.;\|~-]

		$p=parse_url('mysqli://user:user@host:3306/banco_ereg/table:name_ereg/field:name_ereg?propriedade1=valor1&propriedade2=valor2#id');
		parse_str($p['query'],$p['query']);
		print_r($p);
	*/
	static public function close($strDsn = false) {
		//print "\nclose $strDsn\n";
		//print_r(array_keys(self::$instance));
		if ($strDsn === false) return self::$instance = array();
		if (!$strDsn) $strDsn = 'localhost';
		if (array_key_exists($strDsn, self::$instance)) unset(self::$instance[$strDsn]);
	}
	static public function test($strConn = false) {
		$split = self::rebuildDsn($strConn);
		$host = $split['host'];
		$port = @$split['port'];

		if (preg_match('/^mysqli?$/i', $split['dbType'])) return nmap($host, $port ? $port : 3306);
		elseif (preg_match('/^mssql(ora)?$/i', $split['dbType'])) return nmap($host, $port ? $port : 1433);
		elseif (preg_match('/^oracle$/i', $split['dbType']) && ($dirname = $GLOBALS['__autoload']::findFileDefault('tnsnames.ora'))) {
			$arg = '\s*\(\s*(?:[^\(\)]+?)\s*=\s*';
			$val = '\s*(?:[^\(\)]*?)\s*\)';
			$end = '(?:\s*\))';
			if (preg_match('/' . preg_quote($host) . "\s*=\s*(?:$arg(?:$arg(?:$arg(?:$arg(?:$arg$val)*$end|$arg$val)*$end|$arg$val)*$end|$arg$val)*$end|$arg$val)/i", file_get_contents("{$dirname}/tnsnames.ora"), $ret)) {
				if (preg_match_all('/host\s*=\s*([^ \(\)]+)\s*\)(?:\s*\(\s*port\s*=\s*(\d+))?/i', $ret[0], $aHosts, PREG_SET_ORDER)) {
					foreach ($aHosts as $k => $v) if (nmap($v[1], @$v[2] ? $v[2] : 1521)) return true;
					return false;
				}
			}
		}
		return true;
	}

	static public function fit_Lenght($value, $len = 1) {
		if (!self::$fit_maxFieldLength) return $value;
		return min(max($len, strlen($value)), self::$fit_maxFieldLength);
	}
	static public function fit_Field($value, $len = null, $pad = STR_PAD_RIGHT) { //STR_PAD_LEFT STR_PAD_RIGHT STR_PAD_BOTH 
		if ($len === null) $len = self::$fit_maxFieldLength;
		if (!$len) return $value;
		return str_pad(substr($value, 0, $len), $len, ' ', $pad);
	}
	static public function fit_Line($line, $return = false) {
		$line = implode(self::$fit_separator, $line);
		if (self::$fit_maxLength) $line = substr($line, 0, self::$fit_maxLength);
		$out = self::$fit_separatorStart . $line . self::$fit_separatorEnd . "\n";
		if ($return) return $out;
		print $out;
	}
	static public function fit_showHeadLineTop($line, $return = false) {
		if (!self::$fit_headLineTop) return;
		$line = preg_replace('/./', self::$fit_headLineTop, $line);
		$out = self::fit_Line($line, $return);
		if ($return) return $out;
		print $out;
	}
	static public function fit_showHeadLineBottom($line, $return = false) {
		if (!self::$fit_headLineBottom) return;
		$line = preg_replace('/./', self::$fit_headLineBottom, $line);
		$out = self::fit_Line($line, $return);
		if ($return) return $out;
		print $out;
	}
	static public function fit_showFootLine($line, $return = false) {
		if (!self::$fit_footLine) return;
		$line = preg_replace('/./', self::$fit_footLine, $line);
		$out = self::fit_Line($line, $return);
		if ($return) return $out;
		print $out;
	}
}
