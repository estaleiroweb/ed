<?php
class BootstrapDateTimePicker extends ExternalPlugins{
	protected $context='bootstrapDateTimePicker';
	function __construct(){
		new Moment;
		$b=new Bootstrap;
		$b->lib('transition');
		$b->lib('collapse');
		parent::__construct();
	}
}