<?php
class Passwd {
	public $passwd;
	function __construct() {
		$config=Config::singleton();
		$this->passwd=parse_ini_file("{$config->ini}/passwd.ini",true);
	}
	function __get($nm) { return $this->user($nm); }
	function user($user){ return @$this->passwd['default'][$user]; }
	function ena($user){ return @$this->passwd['ena'][$user]; }
}