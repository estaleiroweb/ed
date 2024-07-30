<?php
 /**
 * Page-Level DocBlock OutHtml
 * @package Easy
 * @subpackage Config
 * @category Tools
 * @author Helbert Fernandes <helbertfernandes@yahoo.com.br>
 * @version 1.0 06/07/2005 22:40 - Helbert Fernandes
 * @deprecated OutHtml
 */
 
 /**
 * Gera um cabe�alho para n�0 gravar nada no cache
 */
class Cache {
	static private $instance;

	/**
	 * Use $var=Cache::singleton();
	 *
	 */
	private function __construct(){
		//session_cache_limiter('private'); // Define o limitador de cache para 'private'
		//session_cache_expire (180); // Define o limite de tempo do cache em 180 minutos
		//if (!session_id()) session_start();// Inicia a sess�o

		@header ("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
		@header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		@header ("Cache-Control: private");
		@header ("Pragma: no-cache");
		//header ("Cache-Control: public");
		//header ("Cache-Control: no-cache, must-revalidate", false);
		//header("Cache-Control: post-check=0, pre-check=0", false);
	}
	/**
	 * Gera uma �nica instancia deste objeto
	 *
	 * @return object
	 */
	static public function singleton()   {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
}
