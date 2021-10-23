<?php

namespace EstaleiroWeb\ED\IO;

use Composer\Autoload\ClassLoader;
use EstaleiroWeb\Traits\Singleton;

class ConfigExt {
	use Singleton;
	/**
	 * Composer object
	 * @see Composer https://getcomposer.org/doc/
	 */
	public $composer;
	/**
	 * Autoload Functions
	 * @see spl_autoload_functions https://www.php.net/manual/en/function.spl-autoload-functions.php
	 */
	public $al;
	/**
	 * Namespaces and paths
	 * @see getPrefixesPsr4 Method of Composer
	 */
	public $nm_paths;
	public $host;
	public $ed;
	public $path_imgs;

	private function __construct(){
		$al=$this->al = spl_autoload_functions();
		if (!$al) _::error('Autoload isn\'t registred', FATAL_ERROR);
		//$al = reset($al);
		//$al=ComposerAutoloaderInit88b796964b1d95f3ba4f63403e82804e::getLoader();

		while ($al && is_array($al)) $al = reset($al);
		if (!($al instanceof ClassLoader)) _::error('Autoload isn\'t Composer', FATAL_ERROR);
		$this->composer=$al;
		$this->nm_paths=$al->getPrefixesPsr4();
	}
}
