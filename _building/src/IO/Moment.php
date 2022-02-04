<?php
class Moment extends ExternalPlugins{
	protected $context='moment';
	protected $filesType=array(
		'js'=>'script',
		'jsLocation'=>'script',
	);
	function __construct(){
		new JQuery;
		parent::__construct();
	}
}