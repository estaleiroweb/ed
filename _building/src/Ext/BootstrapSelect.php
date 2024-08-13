<?php
class BootstrapSelect extends ExternalPlugins{
	protected $context='bootstrap-select';
	function __construct(){
		new Bootstrap;
		parent::__construct();
	}
}