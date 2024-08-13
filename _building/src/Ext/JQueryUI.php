<?php
class JQueryUI extends ExternalPlugins{
	function __construct(){
		new JQuery;
		parent::__construct('jquery-ui');
	}
}
