<?php
class IE_lt_9 {
	function __construct(){
		$outHtml=OutHtml::singleton();
		$outHtml->addHead('<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->','bootstrap_compatibility');
//	<script src="https://cdn.jsdelivr.net/respond/1.4.2/respond.min.js"></script>
	}
}