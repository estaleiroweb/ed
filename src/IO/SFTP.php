<?php

namespace EstaleiroWeb\ED\IO;

use EstaleiroWeb\ED\IO\_;
use ReflectionClass;

/**
 * @see https://phpseclib.com/docs/connect
 * @see https://api.phpseclib.com/master/phpseclib3/Net/SFTP.html
 */
class SFTP {	
	/**
	 * Connect SFTP
	 *
	 * @param  mixed $host
	 * @param  int $port
	 * @param  int $timeout
	 * @return \phpseclib3\Net\SFTP 
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
