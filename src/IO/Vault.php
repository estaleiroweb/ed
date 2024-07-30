<?php

namespace EstaleiroWeb\ED\IO;

use Composer\Autoload\ClassLoader;
use EstaleiroWeb\Cache\VaultData;

/**
 * Vault of Connections
 * This file must be stay with web access
 */
class Vault {
	static public $erPass = '/((ssh_|proxy_)?passwd|pwd|passwd_enable)/i';
	static public $class = 'VaultData';
	static public $namespace = 'EstaleiroWeb\\Cache';

	public function __construct() {
		if (!self::checkClass()) $this->build();
	}
	/**
	 * Get a account
	 * @param string $key Context of account
	 * @return array
	 */
	public function __invoke($idx = '') {
		$content = @VaultData::$content[$idx];

		if (is_array($content)) {
			foreach ($content as $k => $v) {
				if (preg_match(self::$erPass, $k)) $content[$k] = $this->decrypt($v);
			}
		}
		return $content;
	}
	public function contents() {
		return @VaultData::$content;
	}

	static public function checkClass() {
		return class_exists(self::$namespace . '\\' . self::$class);
	}
	static public function fileClass() {
		return __FILE__;
	}
	static public function fileClassData() {
		$al = spl_autoload_functions();
		if (!$al) _::error('Autoload isn\'t registred', FATAL_ERROR);
		//$al = reset($al);
		//$al=ComposerAutoloaderInit88b796964b1d95f3ba4f63403e82804e::getLoader();

		while ($al && is_array($al)) $al = reset($al);
		if ($al instanceof ClassLoader) $prefix = (array)$al->getPrefixesPsr4();
		else _::error('Autoload isn\'t Composer', FATAL_ERROR);
		$nm = self::$namespace . '\\';
		if (!array_key_exists($nm, $prefix)) _::error('PSR4 ' . $nm . ' isn\'t registred', FATAL_ERROR);
		$dirs = $prefix[$nm];
		return array_shift($dirs) . '/' . self::$class . '.php';
	}

	/**
	 * Add a content form Vault data
	 * @param array $arr itens of the account [
	 * 		'dsn' => '...',
	 * 
	 * 		'protocol' => '...',
	 * 		'host' => '...',
	 * 		'port' => '...',
	 * 		'user' => '...',
	 * 		'passwd' => '...',
	 * 		'options' => '...',
	 * 
	 * 		'dbname' => '...',
	 * 		'charset' => '...',
	 * 		'uid' => '...',
	 * 		'pwd' => '...',
	 * 	]
	 * @param string $key Context of account
	 * @return self $this
	 */
	public function add(array $arr, $idx = '') {
		$arr = array_change_key_case($arr, CASE_LOWER);
		if (!array_key_exists('protocol', $arr) && !array_key_exists('dsn', $arr)) {
			print "protocol or dsn key is required\n";
			return $this;
		}
		foreach ($arr as $k => $val) {
			if (preg_match(self::$erPass, $k)) $arr[$k] = $this->crypt($val);
		}
		VaultData::$content[$idx] = $arr;
		return $this->build(VaultData::$content, VaultData::$key, VaultData::$cipher);
	}
	/**
	 * Remove a content form Vault data
	 * @param string $key Context of account
	 * @return self $this
	 */
	public function del($key = '') {
		if (array_key_exists($key, VaultData::$content)) unset(VaultData::$content[$key]);
		return $this->build(VaultData::$content, VaultData::$key, VaultData::$cipher);
	}

	private function build($arr = [], $key = '', $cipher = '') {
		static $lf = "\n", $t = "\t";

		if ($key == '') {
			for ($i = 0; $i < 64; $i++) $key .= dechex(rand(0, 15));
		}
		if ($cipher == '') $cipher = 'AES-128-CBC';

		$content = '<?php' . $lf . $lf;
		$content .= 'namespace ' . self::$namespace . ';' . $lf . $lf;
		$content .= 'class ' . self::$class . ' {' . $lf;
		//VaultData::$cipher
		$content .= $t . 'static public $cipher = \'' . $cipher . '\';' . $lf;
		$content .= $t . 'static public $key = \'' . $key . '\';' . $lf;
		$content .= $t . 'static public $content = ';
		$content .= $this->trVal($arr, $t, ';') . $lf;
		$content .= '}' . $lf;

		$filename = self::fileClassData();
		if (!file_put_contents($filename, $content)) die("Dont have write permition on $filename");
		return $this;
	}
	private function trVal($content, $tab = '', $el = '') {
		if (is_null($content)) return "NULL$el";
		if (is_array($content)) {
			if (!$content) return "[]$el";
			$nTab = "\t$tab";
			$out = ['['];
			foreach ($content as $key => $val) {
				$out[] = $nTab . $this->trVal($key) . ' => ' . $this->trVal($val, "\t$tab", ',');
			}
			$out[] = $tab . "]$el";
			return implode("\n", $out);
		}
		return "'$content'$el";
	}
	private function key() {
		return pack('H*', VaultData::$key);
	}
	public function crypt($content) {
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(VaultData::$cipher));
		$ivdb = base64_encode($iv);
		return $ivdb . openssl_encrypt($content, VaultData::$cipher, $this->key(), $options = 0, $iv);
	}
	public function decrypt($content) {
		$iv = base64_decode(substr($content, 0, 24));
		$pss = substr($content, 24);
		return openssl_decrypt($pss, VaultData::$cipher, $this->key(), $options = 0, $iv);
	}
}
