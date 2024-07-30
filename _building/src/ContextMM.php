<?php
class ContextMM {
	function __construct(){
		$outHtml=OutHtml::singleton();
		$outHtml->script(__CLASS__,'easyData');
		$outHtml->style(__CLASS__,'easyData');
	}
}
