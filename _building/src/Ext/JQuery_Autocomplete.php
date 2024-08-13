<?php
class JQuery_Autocomplete extends ExternalPlugins{
	protected $context='jquery-autocomplete';
	function __construct(){
		new JQuery_Mockjax;
		parent::__construct();
	}
}
