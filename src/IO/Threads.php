<?php

namespace EstaleiroWeb\ED\IO;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * Threads
 * 
 * Orquestra threads
 */
class Threads implements Iterator, Countable, ArrayAccess {
	protected $data = []; // Thread collection

	/**
	 *
	 * @return void
	 */
	public function __construct() {
		$args = func_get_args();
		foreach ($args as $obj) {
			if (!$this->add($obj)) {
				if (is_array($obj)) {
					foreach ($obj as $index => $v) {
						$this->add($v, [], $index);
					}
				}
			}
		}
	}
	public function __get($nm) {
		return $this->__call(__FUNCTION__, func_get_args());
	}
	public function __set($nm, $val) {
		$this->__call(__FUNCTION__, func_get_args());
	}
	public function __call($nm, $args) { // implements by Thread
		$out = [];
		foreach ($this->data as $k => $v) {
			$out[$k] = call_user_func_array([$v, $nm], $args);
		}
		return is_object(reset($out)) ? $this : $out;
	}
	public function __toString() {
		return implode("\n", $this->__call(__FUNCTION__, []));
	}
	public function __invoke() {
		return $this->__call(__FUNCTION__, func_get_args());
	}

	//*begin required implements methods
	public function current() { // implements by Iterator
		return current($this->data);
	}
	public function key() { // implements by Iterator
		return key($this->data);
	}
	public function next(): void { // implements by Iterator
		@next($this->data);
	}
	public function rewind(): void { // implements by Iterator
		reset($this->data);
	}
	public function valid(): bool { // implements by Iterator
		return $this->offsetExists(key($this->data));
	}
	public function previous() { // implements by Iterator
		return @prev($this->data);
	}
	public function count(): int { // implements by Countable
		return count($this->data);
	}
	public function offsetExists($index): bool { // implements by ArrayAccess
		return key_exists($index, $this->data);
	}
	public function offsetGet($index) { // implements by ArrayAccess
		return @$this->data[$index];
	}
	public function offsetSet($index, $val): void { // implements by ArrayAccess
		$this->add($val, [], $index);
	}
	public function offsetUnset($index): void { // implements by ArrayAccess
		if (key_exists($index, $this->data)) unset($this->data[$index]);
	}
	/**/ //end required implements methods

	//*begin optional implements methods
	public function sizeof() { // implements sizeof()
		return $this->count();
	}
	public function exists($index) { // implements key_exists()
		return key_exists($index, $this->data);
	}
	public function keys() { // implements array_keys()
		return array_keys($this->data);
	}
	public function values() { // implements array_values()
		return new self(array_values($this->data));
	}
	public function chunk($size, $preserve_keys = false) { // implements array_values()
		return new self(array_chunk($this->data, $size, $preserve_keys));
	}
	public function merge($from, $overwrite = true) { // implements by Threaded
		if (is_resource($from) || is_null($from)) return false;
		if (is_array($from) || is_object($from)) foreach ($from as $k => $v) {
			if (!$overwrite && $this->offsetExists($k)) $k == null;
			$this->offsetSet($k, $v);
		}
		else $this->offsetSet(null, $from);
		return true;
	}
	public function pop() { // implements array_pop()
		return @array_pop($this->data);
	}
	public function shift() { // implements array_shift()
		return @array_shift($this->data);
	}
	public function pos() { // implements pos()
		return $this->current();
	}
	public function reset() { // implements reset()
		return reset($this->data);
	}
	public function end() { // implements end()
		return end($this->data);
	}
	public function shuffle() { // implements shuffle()
		return shuffle($this->data);
	}
	/**/ //end optional implements methods

	public  function add($val, array $args = [], $index = null) {
		if (!($val instanceof Thread)) {
			$val = new Thread($val, $args);
			if (is_null($val->callbackFunction)) return false;
		}
		if (is_null($index)) $this->data[] = $val;
		else $this->data[$index] = $val;
		return true;
	}
	public  function isRunning() {
		foreach ($this->data as $o) {
			if ($o->isAlive()) return true;
		}
		return false;
	}
}
