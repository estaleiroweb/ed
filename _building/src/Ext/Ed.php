<?php
class Ed extends ExternalPlugins{
	public $outHtml;
	function __construct(){
		new JQuery_Cookie;
		$this->outHtml=OutHtml::singleton();
		$this->outHtml->script('Ed','easyData');
	}
}
