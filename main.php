<?php

use EstaleiroWeb\ED\IO\_;

mb_detect_order('ASCII,UTF-8,ISO-8859-1,eucjp-win,sjis-win');
_::$encode = mb_detect_encoding('aeiouáéíóú');
mb_internal_encoding(_::$encode);
mb_http_output(_::$encode);
mb_regex_encoding(_::$encode);
mb_language('uni');
//mb_http_input('G'); // G:GET, P:POST, C:COOKIE, S:string, L:list, I:whole list (will return array)

@date_default_timezone_set('America/Sao_Paulo');
setlocale(LC_ALL,  'pt_BR.utf-8', 'pt_BR', 'portuguese');
setlocale(LC_NUMERIC,  'en_US.utf-8', 'en_US');

if(!function_exists('array_is_list')){
	function array_is_list(array $arr){
		$cont=0;
		foreach($arr as $k=>$v) if($cont++!=$k) return false;
		return true;
	}
}

/*print_r([
	date_default_timezone_get(),
	strftime('%F %T'),
	strftime('%c'),
	strftime('%a %x %r'),
	strftime('%D'),
	date('l jS')
]);*/
/*
	$al = spl_autoload_functions();
	while ($al && is_array($al)) $al = reset($al);
	$prefix = (array)$al->getPrefixesPsr4();
	print_r($prefix);
*/

//if (!session_id()) session_start();
_::init();
