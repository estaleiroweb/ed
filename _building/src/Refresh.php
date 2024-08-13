<?php
class Refresh {
	static private $instance;
	static public function singleton()   {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	private function __construct(){
		$OutHtml=OutHtml::singleton();
		$OutHtml->script(__CLASS__,'easyData');
	}
	function __set($nm,$val){
		if ($nm=='time') {
			$val=(int)$val;
			$OutHtml=OutHtml::singleton();
			$this->OutHtml->script['refresh']="fRfr($val)";
		}
	}
}