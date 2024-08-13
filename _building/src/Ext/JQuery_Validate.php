<?php
class JQuery_Validate extends ExternalPlugins{
	protected $context='jquery-validate';
	protected $filesType=array(
		//'css'=>'style', 
		'js'=>'script',
		'jsLocation'=>'script',
	);
	function __construct(){
		new JQuery;
		parent::__construct();
	}
}
