<?php
class MediatorPHPJS {
	static private $instance;
	private $id='';
	
	private function __construct($validate=false){
		$OutHtml=OutHtml::singleton();
		new JQuery;
		$OutHtml->script('xmlextras','easyData');
		$OutHtml->script(__CLASS__,'easyData');
		if($validate) $OutHtml->script('validateform','easyData');
		$this->__set('URLEASY',$GLOBALS['__autoload']->dir);
	}
	static public function singleton($validate=false)   {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c($validate);
		}
		return self::$instance;
	}
	function __get($nm){
		//if(isset($_COOKIE[$nm])) return unserialize(urldecode($_COOKIE[$nm]));
		return isset($_COOKIE[$nm])?unserialize(rawurldecode($_COOKIE[$nm])):null;
	}
	function __set($nm,$val){
		@setcookie($nm,rawurlencode(serialize($val)));
		//@setcookie($nm,urlencode(serialize($val)));
	}
	function setIdTrace($hop=1){
		$this->id=Id::getIdHash($hop);
	}
	function getIdTrace(){
		return $this->id;
	}
	function setSession($nm,$val,$id=false){
		if($id===false) $id=$this->id;
		if (!session_id()) session_start();
		$_SESSION[$id][$nm]=$val;
	}
	function getSession($nm,$id=false){
		if($id===false) $id=$this->id;
		if (!session_id()) session_start();
		return @$_SESSION[$id][$nm]?$_SESSION[$id][$nm]:null;
	}
}