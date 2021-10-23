<?php
class NavBar {
	static private $instance;
	public $Mediator;
	public $show=true;
	private $done=false;
	private $protect=array('Layout_header'=>true);
	
	static public function singleton($secury=0,$menu=true)   {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c($secury);
		}
		return self::$instance;
	}
	function __tostring(){
		if ($this->done || !$this->show) return '';
		$this->done=true;
		$this->Mediator=MediatorPHPJS::singleton();
		$OutHtml=OutHtml::singleton();
		$OutHtml->script(__CLASS__,'easyData');
		$OutHtml->style(__CLASS__,'easyData');
		return "<script language='JavaScript'>".__CLASS__."()</script>";
	}
}
