<?php
class MainMenu {
	static private $instance;
	public $show=true;
	private $done=false;
	
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
		$c=__CLASS__;
		$OutHtml=OutHtml::singleton();
		$OutHtml->script('xmlextras','easyData');
		$OutHtml->script(__CLASS__,'easyData');
		$OutHtml->style(__CLASS__,'easyData');
		$easyData=$OutHtml->config->easyData;
		$systemUser=@$_SESSION['secury']['user'];
		return "
			<div id='{$c}'>
				<div id='{$c}_left'></div>
				<div id='{$c}_middle'><script language='JavaScript'>$c('{$OutHtml->config->xml}','{$OutHtml->config->imgs}','php','$systemUser')</script></div>
				<div id='{$c}_right'></div>
			</div>
		";
	}
}