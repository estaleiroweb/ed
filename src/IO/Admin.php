<?php
# php -r "require 'vendor/autoload.php'; new EstaleiroWeb\ED\Admin;"
namespace EstaleiroWeb\ED\IO;

class Admin {
	private $vlt, $std;
	private $spc = "\t";

	public function __construct($install = false) {
		$this->check();
		$this->vlt = new Vault;
		$this->std = new Console;

		//print_r($this->std->confirm('xxxxxxx'));exit;
		//print_r($this->vlt->contents());
		if ($install || preg_grep('/--install/', $GLOBALS['argv'])) $this->install();
		else $this->menu_Main();
	}

	private function install() {
		$this->std->cls = false;
		$this->directories();
		$this->main_Key();
		$this->conn_Add();
		$this->config_Paths();
		$this->build_db();
		$this->std->cls = true;
	}
	public function check() {
		if (!Vault::checkClass()) {
			$filename = $this->directories();
			if (!is_file($filename)) {
				file_put_contents($filename, '');
				if (!is_file($filename)) die("Erro to create file $filename\n");
			}
			unlink($filename);
		}
	}
	public function directories() {
		$filename = Vault::fileClassData();
		$dir = dirname($filename);
		print "Creating directory $dir \n";
		@mkdir($dir, 0777, true);
		chmod($dir, 0777);

		//$dir = dirname($dir);
		//@mkdir($dir.'/Html', 0775);

		return $filename;
	}

	public function menu_Main() {
		while ($this->std->menu([
			['opt' => 'Alter the main Key', 'fn' => [$this, 'main_Key']],
			['opt' => 'Manager DSN Connections', 'fn' => [$this, 'menu_Conn']],
			['opt' => 'Config Directories', 'fn' => [$this, 'config_Paths']],
			['opt' => 'Repopulate Database', 'fn' => [$this, 'build_db']],
		], 'Easy Data Main Menu'));
	}
	public function menu_Conn() {
		while ($this->std->menu([
			['opt' => 'List DSN Connection', 'fn' => [$this, 'connList']],
			['opt' => 'Add DSN Connection', 'fn' => [$this, 'conn_Add']],
			['opt' => 'Remove DSN Connection', 'fn' => [$this, 'menu_ConnRm']],
			//['opt' => 'xxxx', 'fn' => 'yyy'],
		], 'Easy Data DSN Menu'));
	}
	public function menu_ConnRm() {
		print __FUNCTION__ . PHP_EOL;
		$conn = $this->vlt->contents();
		$opts = [];
		foreach ($conn as $name => $line) {
			$exclude = preg_grep(Vault::$erPass, array_keys($line));
			foreach ($exclude as $k) unset($line[$k]);

			$opts[] = ['opt' => '[' . $name . ']: ' . json_encode($line), 'fn' => [$this, 'conn_Rm']];
		}
		while ($this->std->menu($opts, 'Easy Data DSN Remove List Menu'));
	}

	public function main_Key() {
		$this->std->cls();
		$this->std->title('Alter Main key');
		print 'WARNNIG:' . PHP_EOL;
		print '     After change that key, you must change every DSN Connections' . PHP_EOL;
		print '     Your database will keep if exists' . PHP_EOL . PHP_EOL;

		$filename = Vault::fileClass();
		$content = file_get_contents($filename);
		//if (!preg_match($er = '(/static\s+private\s+\$key\s*=\s+)([\'"])([a-f0-9]*)\1/i', $content, $ret)) {

		if (!preg_match($er = '/(static\s+private\s+\$key\s*=\s+)([\'"])([^\'"]*)\2/i', $content, $ret)) {
			print 'Erro to find the KEY';
			return $this->std->pressKey();
		}
		$key = $ret[3];

		print 'The current hex key is: ' . $key . PHP_EOL;
		$newKey = $this->std->readLine('New Hex Key (tam 16-64): ', 64, 120, '/^([a-f0-9]{16,64}|)$/i');
		$newKey = strtolower($newKey);

		if ($newKey == '') {
			print 'The key is keep' . PHP_EOL;
		} elseif ($this->std->confirm('Confirm a new key [' . $newKey . '] ') != 'Y') return;
		else {
			$content = preg_replace($er, '\1\'' . $newKey . '\'', $content);
			file_put_contents($filename, $content);
			print 'The key is changed' . PHP_EOL;
		}
		$this->std->pressKey();
	}
	public function connList($showPass = false) {
		$this->std->cls();
		$this->std->title('List of Connections');
		$arr = $this->vlt->contents();
		foreach ($arr as $k => $c) {
			$c['passwd'] = $showPass ? $this->vlt->decrypt($c['passwd']) : '******';
			$arr[$k] = array_merge(['name' => $k], $c);
		}
		_::showTable($arr);
		//print json_encode($this->vlt->contents(), JSON_PRETTY_PRINT) . PHP_EOL . PHP_EOL;
		$show = $this->std->pressKey();
		if ($show[0]['raw'] == 'p') $this->connList(true);
	}
	public function conn_Add() {
		$this->std->cls();
		$this->std->title('ADD DSN Connection');

		print $this->spc . 'Insert PDO options:' . PHP_EOL;
		$arr = [
			'dsn' => [
				'label' => $this->spc . 'DSN String',
				'len' => null,
				'timeout' => 120,
				'er' => [null, null, '/^([a-z]+:.+|)$/i'],
				'default' => 'mysql:host=127.0.0.1;dbname=test',
				'callbackFn' => null,
			],
			'user' => [
				'label' => $this->spc . 'Username',
				'len' => 32,
				'timeout' => 120,
				'er' => '/\w+/',
				'default' => null,
				'callbackFn' => null,
				'show' => true,
			],
			'passwd' => [
				'label' => $this->spc . 'Password',
				'len' => 32,
				'timeout' => 120,
				'er' => null,
				'default' => null,
				'callbackFn' => null,
				'show' => '*',
			],
			'options' => [
				'label' => $this->spc . 'Options <json encoded>',
				'len' => null,
				'timeout' => 120,
				'er' => null,
				'default' => null,
				'callbackFn' => function ($content) {
					if ($content == '') return null;
					$content = (array)@json_decode($content);
					if (!$content) return false;
					return $content;
				},
			],
			'name' => [
				'label' => $this->spc . 'Connection Name',
				'len' => 25,
				'timeout' => 120,
				'er' => '/[a-z0-9]/i',
				'default' => null,
				'callbackFn' => null,
			],
		];
		$arr = $this->std->multRead($arr);
		if (is_null($arr)) return;
		$key = $arr['name'];
		unset($arr['name']);

		print 'Confirm to create this connection:' . PHP_EOL;
		print "[$key]" . json_encode($arr, JSON_PRETTY_PRINT) . PHP_EOL;

		if ($this->std->confirm('Confirm a DSN adding ') == 'Y') {
			$this->vlt->add($arr, $key);
			$this->std->pressKey();
		}
	}
	public function conn_Rm($item) {
		$this->std->title('Remove DSN Connection');
		$conn = $this->vlt->contents();
		$akeys = array_keys($conn);
		if (!array_key_exists($item, $akeys)) {
			print 'Not Fount Connection' . PHP_EOL;
			return $this->std->pressKey();
		}
		$key = $akeys[$item];
		$this->std->cls();
		print "DSN Connection: [$key]\n";
		print json_encode($conn[$key], JSON_PRETTY_PRINT) . PHP_EOL;
		if ($this->std->confirm('Do you want to delete this DSN? ') == 'Y') {
			$this->vlt->del($key);
			print 'DSN deleted' . PHP_EOL;
			$this->std->pressKey();
		}
	}

	//TODO implements
	public function config_Paths() {
		$this->std->cls();
		$this->std->title(__FUNCTION__);
		$this->std->pressKey();
	}
	public function build_db() {
		$this->std->cls();
		$this->std->title(__FUNCTION__);
		$this->std->pressKey();
	}
}
