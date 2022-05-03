<?php

namespace EstaleiroWeb\ED\Secure;

use EstaleiroWeb\ED\IO\_;

class File extends Common {
	static private $instance = array('loadById' => array(), 'loadByName' => array(),);
	static public $levels = array('Free', 'Segured', 'Paranoic');

	static public function singleton($fullName, $flags = 0) { //SEE SECURE_FILE_FLAG_* Ex: SECURE_FILE_FLAG_FIND_BY_NAME + SECURE_FILE_FLAG_CREATE_IF_NOT_EXISTS
		if (is_numeric($fullName) && $flags & 1) $fn = 'loadById'; //SECURE_FILE_FLAG_FIND_BY_ID
		else {
			$fullName = self::translate($fullName);
			$fn = 'loadByName';      //SECURE_FILE_FLAG_FIND_BY_NAME
		}
		if (!array_key_exists($fullName, self::$instance[$fn])) {
			$c = __CLASS__;
			self::$instance[$fn][$fullName] = $obj = new $c();
			$obj->startVars();
			$createIfNotExists = (bool)($flags & 2); //SECURE_FILE_FLAG_CREATE_IF_NOT_EXISTS
			if ($fullName) $obj->$fn($fullName, $createIfNotExists);
		} else $obj = self::$instance[$fn][$fullName];
		return $obj;
	}
	function startVars() {
		$this->readonly = array('idFile' => 0, 'File' => '', 'L' => 0, 'CRUDS' => 0, 'C' => 0, 'R' => 0, 'U' => 0, 'D' => 0, 'S' => 0, 'Error' => 'Unloaded');
	}
	static public function translate($url) {
		global $__autoload;
		static $urls = array();
		if (!array_key_exists($url, $urls)) {
			$urlSplit = parse_url($url);
			if (@$urlSplit['query']) unset($urlSplit['query']);
			if (@$urlSplit['fragment']) unset($urlSplit['fragment']);
			if (!$urlSplit) $urlSplit = parse_url($__autoload->url);
			$path = @$urlSplit['path'];
			if (@$urlSplit['host'] == 'this' || @$urlSplit['host'] == 'localhost') $urlSplit['host'] = $__autoload->host;
			$scheme = strtolower(@$urlSplit['scheme'] ? "{$urlSplit['scheme']}://" : '');
			if (@$_SERVER['SHELL']) {
				if (!@$urlSplit['host']) $urlSplit['host'] = $__autoload->host;
			} else {
				if (!$scheme || $scheme == 'https://') $scheme = 'http://';
				if (!@$urlSplit['host']) {
					$urlSplit['host'] = $__autoload->host; //path
					if ($path[0] != '/') {
						$p = parse_url($__autoload->url);
						$path = dirname(@$p['path']) . '/' . $path;
					}
				}
			}
			$path = preg_replace(array('/\/\.\//', '/\/+\.?$/'), array('/', ''), $path);
			while ($path != ($newPath = preg_replace('/\/[^\/]+\/\.\./', '', $path, 1))) $path = $newPath;

			$host = @$urlSplit['host'] . (@$urlSplit['port'] ? ":{$urlSplit['port']}" : '');
			if ($host && @Secure::$ini['translate']) foreach (Secure::$ini['translate'] as $newHost => $er) if (preg_match($er, $host)) {
				$host = $newHost;
				break;
			}
			$urls[$url] = $scheme . $host . $path;
		}
		return $urls[$url];
	}
	private function load($where) {
		_::verbose($where);
		$line = Secure::$conn->fastLine('SELECT * FROM ' . Secure::$ini['db'] . '.tb_Files WHERE ' . $where . ' LIMIT 1');
		if (!$line) return false;
		$this->readonly = array_merge($this->readonly, $line);
		$this->error();
		return $line;
	}
	private function loadById($idFile) {
		$line = $this->load("idFile={$idFile}");
		if ($line) return self::$instance['loadByName'][$line['File']] = &self::$instance['loadById'][$line['idFile']];
		else return $this->error('Not Find by IdFile');
	}
	private function loadByName($fullFileName, $createIfNotExists = false) {
		$file = $fullFileName;
		//$fullFileName=self::translate($fullFileName);
		$this->readonly['File'] = $fullFileName;
		$lnks = array_flip(array_flip(array(
			Secure::$conn->addQuote($fullFileName),
			Secure::$conn->addQuote(preg_replace(array('/(?<!:)\/\//', '/(.+\.[a-z]+)\/index.php/i'), array('/', '\1'), $fullFileName . '/index.php')),
		)));
		$line = $this->load('`File` IN (' . implode(', ', $lnks) . ')');
		//show([$fullFileName,$lnks,$line]);
		if ($line) {
			self::$instance['loadById'][$line['idFile']] = &self::$instance['loadByName'][$file];
			return true;
		} elseif ($createIfNotExists) return $this->create();
		return false; //$this->error('Not Find by File name: '.$fullFileName);
	}
	function create($fullFileName = false) {
		if ($fullFileName) $fullFileName = $this->translate($fullFileName);
		elseif ($this->readonly['File']) $fullFileName = $this->readonly['File'];
		else return;
		_::verbose(__FUNCTION__ . ': ' . $fullFileName);
		$idFile = $this->dbFunction('fn_File_Create', $fullFileName);
		return $idFile ? $this->loadById($idFile) : false;
	}
	function save($data) {
		if (!$this->readonly['idFile']) return;
		$fields = array('File', 'L', 'C', 'R', 'U', 'D', 'S', 'Obs',);
		$set = array();
		foreach ($fields as $k) if (array_key_exists($k, $data)) $set[] = "`$k`=" . Secure::$conn->addQuote($data[$k]);
		if ($set) {
			$set = implode(',', $set);
			Secure::$conn->query('UPDATE {' . Secure::$ini['db'] . '.tb_Files SET $set WHERE idFile=' . $this->readonly['idFile']);
			$this->loadById($this->readonly['idFile']);
		}
	}
}
