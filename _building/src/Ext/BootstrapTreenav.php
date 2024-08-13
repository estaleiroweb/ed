<?php
class BootstrapTreenav extends ExternalPlugins{
	protected $context='bootstrapTreenav';
	function __construct(){
		new Bootstrap;
		parent::__construct();
	}
}