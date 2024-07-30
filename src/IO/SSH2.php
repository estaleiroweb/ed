<?php

namespace EstaleiroWeb\ED\IO;

use EstaleiroWeb\ED\IO\_;
use ReflectionClass;

/**
 * @see https://phpseclib.com/docs/connect
 * @see https://api.phpseclib.com/master/phpseclib3/Net/SSH2.html
 */
class SSH2 {
	/**
	 * Connect SSH2
	 *
	 * @param  mixed $host
	 * @param  int $port
	 * @param  int $timeout
	 * @return \phpseclib3\Net\SSH2 
	 */
	static public function init($host, $port = 22, $timeout = 10) {
		$k = __CLASS__;
		if (
			!class_exists($class = 'phpseclib3\Net\\' . $k) &&
			!class_exists($class = 'phpseclib\Net\\' . $k)
		) {
			_::error("phpseclib $k class not exists\nSee https://phpseclib.com/docs/connect", FATAL_ERROR);
		}
		$refl = new ReflectionClass($class);
		return $refl->newInstanceArgs(func_get_args());
	}
}
