<?php

namespace EstaleiroWeb\ED\Tools;

use EstaleiroWeb\ED\IO\Vault;

class Redirect {
	protected $protected = array('scheme' => '', 'host' => '', 'port' => 80, 'user' => '', 'pass' => '', 'path' => '',);
	private $portList = array(
		'file' => 0,
		'ftp' => 21,
		'ssh' => 22,
		'telnet' => 23,
		'smtp' => 25,
		'tacacs' => 49,
		'gopher' => 70,
		'http' => 80,
		'pop2' => 109,
		'pop3' => 110,
		'sftp' => 115,
		'nntp' => 119,
		'ntp' => 123,
		'imap' => 143,
		'snmp' => 161,
		'srmp' => 193,
		'mftp' => 349,
		'ldap' => 389,
		'https' => 443,
	);
	private $locked = false;
	private $redirect = false;
	private $dsn;
	function __construct() {
		$this->protected['scheme'] = $this->getScheme();
		$this->protected['host'] = $_SERVER['SERVER_NAME'];
		$this->protected['port'] = $_SERVER['SERVER_PORT'];
		$this->protected['path'] = $_SERVER['REQUEST_URI'];
		$dsn = new Vault();
		$this->dsn = $dsn->contents();
		$arg = func_get_args();
		$num = count($arg);
		if ($this->locked = ($num > 1)) {
			$arg = array_reverse($arg);
			foreach ($arg as $host) {
				$this->host = $host;
				if (!$this->redirect) return $this->locked = false;
			}
			$this->locked = false;
			$this->move();
		} elseif ($num) $this->host = $arg[0];
	}
	function __get($nm) {
		return @$this->protected[$nm];
	}
	function __set($nm, $val) {
		if ($nm == 'locked') return $this->locked = (bool)$val;
		elseif (isset($this->protected[$nm])) {
			if (!$this->$nm($val)) return;
		} else return;
		$this->isDiferenteHost();
	}
	private function getScheme() {
		return strtolower(preg_replace('/[^a-z]/i', '', $_SERVER['SERVER_PROTOCOL'])) . (@$_SERVER['HTTPS'] ? 's' : '');;
	}
	private function scheme($scheme = '') {
		$scheme = strtolower($scheme);
		if (isset($this->portList[$scheme])) $this->protected['port'] = $this->portList[$scheme];
		else $scheme = $this->getScheme();
		$this->protected['scheme'] = $scheme;
		return true;
	}
	function host($host = '') {
		if (!preg_match("/^(?:(?<scheme>\w+)\:\/\/)?(?:(?<user>.*?)?(?:\:(?<pass>.*?))?@)?(?<host>.*?)?(?:\:(?<port>\d+))?(?<path>[?#\/].*)?$/", $host, $ret)) return false;
		if (@$ret['path']) {
			if (preg_match('/^[?#]/', $ret['path'])) $ret['path'] = preg_replace('/[?#].*/', '', $this->protected['path']) . $ret['path'];
			elseif (preg_match('/^\w/', $ret['path'])) $ret['path'] = preg_replace('/(\/?).*?$/', '\1', $this->protected['path']) . $ret['path'];
		}
		//print '<pre>'.print_r($ret,true).'</pre>';exit;
		//print '<pre>'.print_r($this->protected,true).'</pre>';exit;
		$host = (@$ret['host'] ? $ret['host'] : $_SERVER['SERVER_NAME']);
		if (isset($this->dsn[$host])) $host = $this->dsn[$host]['host'];
		$this->protected['host'] = $host;
		foreach ($this->protected as $k => $v) if ($k != 'host') $this->$k(@$ret[$k]);
		//$this->protected['all']=$ret;
		return true;
	}
	private function port($port = '') {
		$port = (int)$port;
		$this->protected['port'] = $port ? $port : (isset($this->portList[$this->protected['scheme']]) ? $this->portList[$this->protected['scheme']] : $_SERVER['SERVER_PORT']);
		return true;
	}
	private function user($user = '') {
		$this->protected['user'] = $user;
	}
	private function pass($pass = '') {
		$this->protected['pass'] = $pass;
	}
	private function path($path = '') {
		$this->protected['path'] = ($path ? $path : $_SERVER['REQUEST_URI']);
	}
	function isDiferenteHost() {
		$this->redirect = ($this->protected['scheme'] != $this->getScheme() ||
			$this->protected['port']  != $_SERVER['SERVER_PORT'] ||
			($this->protected['host'] != $_SERVER['SERVER_NAME'] && $this->protected['host'] != $_SERVER['SERVER_ADDR']) ||
			$this->protected['path']  != $_SERVER['REQUEST_URI']);
		if ($this->redirect && !$this->locked) $this->move();
	}
	function move() {
		if ($this->redirect) {
			//print "Move to {$this->getUrlToMove()}";exit;
			header("HTTP/1.0 200 OK");
			header("HTTP/1.0 202 Accepted", false);
			/*
			header("HTTP/1.0 204 No Content",false);
			header("HTTP/1.0 203 Non-Authoritative Information",false);
			header("HTTP/1.0 205 Reset Content",false);
			header("HTTP/1.0 301 Moved");
			*/
			header("Location: {$this->getUrlToMove()}");
			//print "<p>Redirecionado: {$this->getUrlToMove()}</p>";
			exit;
		}
	}
	function getUrlToMove() {
		$port = isset($this->portList[$this->protected['scheme']]) ? $this->portList[$this->protected['scheme']] : $_SERVER['SERVER_PORT'];
		$port = ($this->protected['port'] == $port ? '' : ':' . $this->protected['port']);
		$pass = ($this->protected['pass'] ? ':' . $this->protected['pass'] : '');
		$user = ($this->protected['user'] ? $this->protected['user'] . $pass . '@' : '');
		return "{$this->protected['scheme']}://$user{$this->protected['host']}$port{$this->protected['path']}";
	}
}
