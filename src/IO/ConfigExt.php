<?php

namespace EstaleiroWeb\ED\IO;

use Composer\Autoload\ClassLoader;
use EstaleiroWeb\Traits\GetterAndSetterRO;
#use ReflectionClass;

class ConfigExt {
	use GetterAndSetterRO;

	public $root = '';
	/**
	 * Root URL Project
	 */
	public $baseURL = '';
	/**
	 * Root Base path Default=$_SERVER['DOCUMENT_ROOT']
	 */
	public $baseDIR = null;
	/**
	 * dirname($_SERVER['PHP_SELF'] | $_SERVER['SCRIPT_NAME'])
	 */
	public $path = null;
	/**
	 * dirname(@$_SERVER['SCRIPT_FILENAME'])
	 */
	public $fullpath = null;
	/**
	 * Easy Data Paths
	 */
	public $ed = [
		'base' => '/easyData',
		'js' => '/easyData/js',
		'fn' => '/easyData/fn',
		'skin' => '/easyData/skin/default',
		'css' => '/easyData/skin/default/css',
		'imgs' => '/easyData/skin/default/img',
		'icons' => '/easyData/skin/default/icons',
		'url' => '/easyData/url',
	];
	/**
	 * Secure Class settings
	 */
	public $secure = [
		'dsn' => '',
		'db' => 'db_Secure',
		'db_log' => 'db_Secure_Log',
		'autoLogon' => true,               // (DEFAULT OFF)
		'multiSession' => true,            // (DEFAULT OFF)
		'expiresSession' => 15,            // MINUTES, 0=INFINITE (DEFAULT 15)
		'tryWait' => 10,                   // SECONDS (DEFAULT 10)
		'tryTimes' => 3,                   // (DEFAULT 3)
		'processBarStyle' => 0,            // 0=Normal | 1=Splited (DEFAULT 0)
		'expiresPassword' => 120,          // (DEFAULT 120) 0=Never
		'siging' => ['CLASS', 'method'],      // Class sigin access
		'newUser' => ['CLASS', 'method'],     // Class New User
		'denied' => ['CLASS', 'method'],      // Class denied access
		'passwdRulesCheck' => true,
		'passwdRules' => [
			'Numbers' => '/\d/',
			'Uppercase' => '/[A-Z]/',
			'Lowercase' => '/[a-z]/',
			'Symbols' => '/[!@#$%&\*\+=\(\)\/\?<>\[\]\\-]/',
			'Min Llength 8' => '/.{8,}/',
			//'Space' => '/ /',
		],
		'log_path' => './log',
		'log_ext' => 'log',
		'log_fileAuth' => 'authError',
		'log_filePre' => 'acs_',
		'translate' => [
			'intbhe101' => '/^(0*189\.0*21\.0*3\.0*61|0*10\.0*72\.0*5\.0*2|intbhe101\.localdomain)(:0*(80|443))?$/i',
			'evoice' => '/^(0*10\.0*9\.0*5\.0*14|0*10\.0*192\.0*72\.0*10|0*200\.0*184\.0*192\.0*201|0*127\.0+\.0+\.0*1|0*10\.174\.0*220\.117)|(((evoice|fsc(srv)?)|((db|portal)(fsc|v?cp|vcd))|localhost)(\.(localdomain|internal\.timbrasil\.com\.br))?)(:0*(80|443))?$/i',
		],
	];
	/**
	 * Ldap Class Config
	 */
	public $ldap = [
		'server' => 'localhost', //snepdc03v.internal.timbrasil.com.br internal.timbrasil.com.br
		'domain' => 'domain',
		'fn' => 'ldap_search',
		'dn' => 'OU=Organization,DC=domain,DC=host,DC=com,DC=br',
		'port' => 389,
	];
	/**
	 * Regional settings
	 */
	public $regional = [
		'full_datetime_format' => '%d/%m/%Y %T',
		'datetime_format' => '%d/%m/%Y %T',
		'date_format' => '%d/%m/%Y',
		'time_format' => '%T',
	];
	/**
	 * Named hosts
	 * [ 'example' => ['description' => 'Example Server', 'ip' => '10.0.0.1', 'name' => "hostname",],],
	 */
	public $hosts = [];
	/**
	 * DataGraph Class Config
	 */
	public $dataGraph = [
		'exportEnabled' => true,
		'exportFileName' => 'Graph', //.$this->head['Device'], 
		'zoomEnabled' => true,
		'zoomType' => 'xy', //'x','x,y,xy'
		'creditText' => 'EstaleiroWeb',
		'creditHref' => 'http://estaleiroweb.com.br',
		'culture' => 'pt-br',
	];
	/**
	 * Mail Class config
	 */
	public $mail = [
		'server' => '10.0.0.1',
		'engine' => 2,                 //0:mail (default), 1:smtp, 2:sendmail, 3:qmail
		'priority' => '',              //"" (default), 1:High, 2:Low, 3:Normal
		'charset' => 'ISO-8859-1',     //The character set of the message.
		'contentType' => 'text/plain', //text/plain, text/html
		'encoding' => '8bit',          //'8bit', '7bit', 'binary', 'base64', and 'quoted-printable'.
		'port' => 25,
		'version' => 1.0,
		'from' => 'anonimo <noreply@domain.com>',
		'replyTo' => '',
		'smtp_auth' => true,
		'smtp_address' => '',
		'smtp_port' => '',
		'smtp_user' => '',
		'smtp_password' => '',
		'smtp_cript' => '', //SSL, TLS, auto
		'smtp_time' => 60, //segundos
	];

	protected function __construct() {
		$al = spl_autoload_functions();
		if (!$al) _::error('Autoload isn\'t registred', FATAL_ERROR);
		//$al = reset($al);
		//$al=ComposerAutoloaderInit88b796964b1d95f3ba4f63403e82804e::getLoader();
		$composer = $al;
		while ($composer && is_array($composer)) $composer = reset($composer);
		if (($composer instanceof ClassLoader)) $nm_paths = $composer->getPrefixesPsr4();
		else _::error('Autoload isn\'t Composer', FATAL_ERROR);
		$prefix = (array)$nm_paths;
		//print '========== '.preg_replace('/[^\\\]+?$/','',get_class($this));
		//$nm = Config::$nmCache . '\\';
		$nm = preg_replace('/[^\\\]+?$/', '', get_class($this));
		if (!array_key_exists($nm, $prefix)) _::error('PSR4 ' . $nm . ' isn\'t registred', FATAL_ERROR);
		$dirs = $prefix[$nm];
		$cacheDir = realpath(array_shift($dirs));
		//$r = new ReflectionClass($composer);
		if ($this->baseDIR == '') $this->baseDIR =  @$_SERVER['DOCUMENT_ROOT'];
		if(!isset($_SERVER['SHELL'])) $this->root = is_link($this->baseDIR) ? readlink($this->baseDIR) : $this->baseDIR;

		$script = @$_SERVER['SCRIPT_NAME'] == '' ? @$_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$this->path = dirname($script);
		$this->fullpath = dirname(@$_SERVER['SCRIPT_FILENAME']);

		$this->readonly = [
			/**
			 * Autoload Functions
			 * @see spl_autoload_functions https://www.php.net/manual/en/function.spl-autoload-functions.php
			 */
			'al' => $al,
			/**
			 * Composer object
			 * @see Composer https://getcomposer.org/doc/
			 */
			'composer' => $composer,
			/**
			 * Namespaces and paths
			 * @see getPrefixesPsr4 Method of Composer
			 */
			'nm_paths' => $nm_paths,
			/**
			 * Cache Dir
			 */
			'cacheDir' => $cacheDir,
			/**
			 * Root Path Project
			 */
			'baseDir' => dirname(dirname($cacheDir)),
			/**
			 * Last Link form moved
			 */
			'referer' => @$_SERVER["HTTP_REFERER"],
			$_SERVER
		];
		//$composer=new ClassLoader;
	}
}
