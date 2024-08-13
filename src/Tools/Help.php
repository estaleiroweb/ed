<?php

namespace EstaleiroWeb\ED\Tools;

use EstaleiroWeb\ED\Screen\OutHtml;

class Help {
	static private $instance;
	static public function singleton() {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	private function __construct() {
		$OutHtml = OutHtml::singleton();
		$OutHtml->script(__CLASS__, 'easyData');
	}
	function __set($nm, $val) {
		if ($nm == 'id') {
			$OutHtml = OutHtml::singleton();
			$OutHtml->script[] = "window.helpIdReferer='$val'";
		}
	}
}
