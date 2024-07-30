<?php

namespace EstaleiroWeb\ED\IO;

use Exception;

final class IPC_SharedMemory extends IPC {
	protected $param = '-m';
	protected $data = ['names' => [], 'pids' => [],];

	protected function newKey() {
		$this->newKey = true;
		$this->resource = @shm_attach($this->key, self::$size, self::$perms);
		$this->get();
		if ($this->pid !== null) $this->data['pids'][$this->pid] = $this->pid;
		$this->set();
		return $this->resource;
	}
	protected function delKey() {
		if ($this->release()) return;
		$this->del();
	}
	public function release() {
		if (!$this->resource) return false;
		$this->get();
		if (array_key_exists($this->pid, $this->data['pids'])) unset($this->data['pids'][$this->pid]);
		$this->set();
		return $this->data['pids'];
	}

	public function __get($name) {
		if (!$this->resource) return;
		$id = $this->tr($name);
		if (shm_has_var($this->resource, $id)) return shm_get_var($this->resource, $id);
	}
	public function __set($name, $value) {
		if ($this->resource) return shm_put_var($this->resource, $this->tr($name), $value);
	}
	public function __isset($name) {
		if ($this->resource) return shm_has_var($this->resource, $this->tr($name));
	}
	public function __unset($name) {
		if (!$this->resource) return;
		$id = $this->tr($name);
		if (shm_has_var($this->resource, $id)) return shm_remove_var($this->resource, $id);
	}

	private function tr($name) {
		$this->get();
		$key = @$this->data['names'][$name];
		if (!$key) {
			$this->data['names'][$name] = $key = count($this->data['names']) + 1;
			$this->set();
		}
		return max(1, $key);
	}
	private function set() {
		if ($this->resource) return shm_put_var($this->resource, 0, $this->data);
	}
	private function get() {
		if (!$this->resource || !shm_has_var($this->resource, 0)) return false;
		error_reporting(0);
		try {
			$data = @shm_get_var($this->resource, 0);
			if ($data) $this->data = $data;
		} catch (Exception $e) {
		}
		restore_error_handler();
	}
}
