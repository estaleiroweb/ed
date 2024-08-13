<?php

namespace EstaleiroWeb\ED\Tools;

use EstaleiroWeb\ED\Screen\OutHtml;

class History {
	static private $instance;
	private $OutHtml;
	private $pg;
	private $last;

	static public function singleton() {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	function __construct() {
		$this->OutHtml = OutHtml::singleton();
		$this->addList();
		$this->link($this->pg - 1, 'historyBack', 'fBck');
		$this->link($this->pg + 1, 'historyForward', 'fFwd');
	}
	function link($pg, $item, $cmd) {
		if ($d = @$_SESSION['history']['list'][$pg]) {
			$this->OutHtml->headScript[$item] = "function $cmd(){location='{$d['protocol']}://{$d['link']}?{$d['query']}'}";
		}
	}
	function linkNav($lnk) {
		$this->OutHtml->headScript['historyNav'] = "function fNav(){location='$lnk'}";
	}
	function clearBck() {
		$_SESSION['history']['list'] = array();
		$_SESSION['history']['pg'] = false;
	}

	function addList() {
		@session_start();
		if (!isset($_SESSION['history']['list'])) $this->clearBck();
		$this->last = count($_SESSION['history']['list']);
		$this->pg = isset($_GET['history']) ? $_GET['history'] : $this->last;
		if ($this->pg === $this->last) { //incrementa
			if (($pg = $_SESSION['history']['pg']) !== false) {
				@array_splice($_SESSION['history']['list'], $pg + 1);
				$this->pg = count($_SESSION['history']['list']);
			}
			$_SESSION['history']['pg'] = false;
			$link = $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];
			if ($_SESSION['history']['list']) {
				$d = end($_SESSION['history']['list']);
				if ($d['link'] == $link) {
					array_pop($_SESSION['history']['list']);
					$this->pg = count($_SESSION['history']['list']);
				}
			}
			$protocol = strtolower(preg_replace("/[^a-z]/i", "", $_SERVER['SERVER_PROTOCOL']) . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] = 'on') ? 's' : ''));
			$query = "history={$this->pg}" . ($_SERVER['QUERY_STRING'] ? "&" : "") . $_SERVER['QUERY_STRING'];
			$_SESSION['history']['list'][$this->pg] = $x = compact('link', 'protocol', 'query');
		} else $_SESSION['history']['pg'] = $this->pg;
	}
}
