<?php

namespace EstaleiroWeb\ED\IO;

use Composer\Autoload\ClassLoader;
use EstaleiroWeb\Traits\GetterAndSetterRO;
use EstaleiroWeb\Traits\Singleton;
use ReflectionClass;

class ConfigExt {
	use Singleton;
	use GetterAndSetterRO;

	static public $nmCache = 'EstaleiroWeb\\Cache';

	/**
	 * Root URL Project
	 */
	public $baseURL = 'http://URL_BASE';
	/**
	 * Easy Data Paths
	 */
	public $ed = [
		'base' => '/',
		'js' => '/js',
		'fn' => '/fn',
		'skin' => '/skin/default',
		'css' => '/skin/default/css',
		'imgs' => '/skin/default/img',
		'icons' => '/skin/default/icons',
		'url' => '/url',
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
		$nm = self::$nmCache . '\\';
		if (!array_key_exists($nm, $prefix)) _::error('PSR4 ' . $nm . ' isn\'t registred', FATAL_ERROR);
		$dirs = $prefix[$nm];
		$cacheDir = realpath(array_shift($dirs));
		//$r = new ReflectionClass($composer);

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
