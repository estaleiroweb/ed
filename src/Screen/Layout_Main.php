<?php
class Layout_Main {
	static protected $instance;
	public $OutHtml,$Secure,$NavBar,$Mediator,$history;
	public $showMenu=true;
	protected $protect=array(
		'Layout_header'=>true,
	);

	static public function singleton($parameters='')   {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c($parameters);
		}
		return self::$instance;
	}
	protected function __construct($parameters){ //$parameters='NoCache,NoContentType,NoIE,NoMobile,NoBootstrap,NoJQuery,NoNavBar,NoSecure,NoMediator'
		$this->OutHtml=OutHtml::singleton();
		//header('Content-Type: text/html; charset=utf-8');
		if(preg_match('/\bNoSecure\b/i',$parameters))       $this->OutHtml->nocache();
		//if(!preg_match('/\bNoContentType\b/i',$parameters)) 
			$this->OutHtml->contentType();
		//exit;
		if(!preg_match('/\bNoIE\b/i',$parameters))          $this->OutHtml->ie();
		if(!preg_match('/\bNoMobile\b/i',$parameters))      $this->OutHtml->mobile();
		if(!preg_match('/\bNoBootstrap\b/i',$parameters))   new Bootstrap;
		if(!preg_match('/\bNoJQuery\b/i',$parameters))      new JQuery_UI;
		if(!preg_match('/\bNoSecure\b/i',$parameters))      $this->Secure=Secure::singleton();
		//if(!preg_match('/\bNoNavBar\b/i',$parameters))      $this->NavBar=NavBar::singleton();
		if(!preg_match('/\bNoMediator\b/i',$parameters)) {
			$this->Mediator=MediatorPHPJS::singleton();
			foreach ($this->protect as $k=>$v) if (isset($_COOKIE[$k])) $this->protect[$k]=$this->Mediator->$k;
		}
		//$this->history=History::singleton();
	}
	function __destruct(){
		print $this->__toString();
	}
	function __get($nm){
		if (isset($this->protect[$nm])) return $this->protect[$nm];
	}
	function __set($nm,$val){
		if (isset($_COOKIE[$nm])) return;
		$this->Mediator->$nm=$this->protect[$nm]=$val;
	}
	function __tostring() {/*
		static $done;
		
		if ($done || !$this->OutHtml) return '';
		$done=true;
		//restante
	*/}
	function title($title){
		$this->OutHtml->title($title);
	}
	function when_loged(){
	}
	function when_unloged(){
	}
	function access_denided(){
	}
}
