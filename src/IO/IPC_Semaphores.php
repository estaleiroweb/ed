<?php

namespace EstaleiroWeb\ED\IO;

final class IPC_Semaphores extends IPC {
	protected $param = '-s';

	protected function newKey() {
		return $this->resource = sem_get($this->key, self::$max_acquire, self::$perms, self::$auto_release);
	}
	protected function delKey() {
		if ($this->resource) return sem_remove($this->resource);
	}

	public function acquire($nowait = false) {
		if ($this->resource) return sem_acquire($this->resource, $nowait);
	}
	public function release() {
		if ($this->resource) return sem_release($this->resource);
	}
}
