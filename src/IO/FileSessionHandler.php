<?php

namespace EstaleiroWeb\ED\IO;

use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface {
	private $savePath, $saveFile, $id, $destroyOnClose;

	function __construct($destroyOnClose = false) {
		$this->destroyOnClose = $destroyOnClose;
	}
	function open($savePath, $sessionName) {
		$this->savePath = $savePath;
		if (!$this->savePath) return false;
		$this->saveFile = $this->savePath . '/sess_';
		//print_r([$this->saveFile]);
		if (!is_dir($this->savePath)) mkdir($this->savePath, 0777);
		return true;
	}
	function close($id = null) {
		if ($this->destroyOnClose) return $this->destroy($this->id);
		return true;
	}
	function read($id) {
		$this->id = $id;
		if (!$this->savePath) return false;
		return file_exists($file = $this->saveFile . $id) ? (string)file_get_contents($file) : '';
	}
	function write($id, $data) {
		$this->id = $id;
		if (!$this->savePath) return false;
		return file_put_contents($this->saveFile . $id, $data) === false ? false : true;
	}
	function destroy($id) {
		$this->id = $id;
		if (!$this->savePath) return false;
		if (file_exists($file = $this->saveFile . $id)) unlink($file);
		return true;
	}
	function gc($maxlifetime) {
		if (!$this->savePath) return false;
		foreach (glob($this->saveFile . '*') as $file) {
			if (filemtime($file) + $maxlifetime < time() && file_exists($file)) unlink($file);
		}
		return true;
	}
}
