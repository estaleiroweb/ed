<?php
class BootstrapTreeview extends ExternalPlugins{
	protected $context='bootstrapTreeview';
	function __construct(){
		new Bootstrap;
		parent::__construct();
	}
	function init($selector='#tree',$data='getTree()') {
		if(!is_string($data)) $data=json_encode($data);
		$this->OutHtml->jQueryScript[$this->context]='$("'.$selector.'").treeview('.$data.');';
	}
}