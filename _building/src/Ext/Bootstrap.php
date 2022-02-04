<?php
class Bootstrap extends ExternalPlugins {
	protected $context = 'bootstrap';
	function __construct() {
		new JQuery;
		parent::__construct();
		new IE_lt_9;
	}
	function font($item) {
		//$this->config[$this->context]['fonts'];
	}
	function lib($item) {
		$this->OutHtml->script($item, $this->config[$this->context]['lib']);
	}
}
