<?php

namespace EstaleiroWeb\ED\IO;

use ArrayAccess;
use Closure;
use Countable;
use Exception;
use Iterator;
use EstaleiroWeb\Traits\GetSet;

/**
 * Threads
 * 
 * Orquestra threads
 * 
 * 
 */
abstract class Threads implements Iterator, Countable, ArrayAccess {
	const STATUS_NO_STARTED = 0;
	const STATUS_RUNNING = 1;
	const STATUS_FINISHED = 2;
	const STATUS_PAUSED = 3;
	const STATUS_WAITING = 4;
	const STATUS_STARTING = 5;

	static public $debug = false;
	static private $startedId = null;

	private $started = false;
	private $paused = false;
	private $waitting = false;
	private $finished = false;
	private $count = 0;
	private $lastId = null;
	private $shared = null;

	protected $statusList = ['not started', 'running', 'finished', 'paused', 'waiting', 'starting'];
	protected $id = null;
	protected $pid = null;
	protected $index = null;
	protected $sharedKey = null;
	protected $parent = null;
	protected $data = [];
	protected $parameters = [];
	protected $readonly = [];
	protected $pids = [];

	public $poolStartThreads = 3;
	public $poolQueue = [];
	public $poolPaused = [];
	public $poolRunning = [];

	/**
	 * ~~~php
	 * <?php
	 * 	class Processos extends Threads {
	 * 		protected $readonly=['time'=>1,'laps'=>3];
	 * 		function run() {
	 * 			$this->debug();
	 * 			for($i=0;$i<$this->laps;$i++){
	 * 				$n=$i+1;
	 * 				print "[{$this->pid}] LAP: $n/{$this->laps} - WAIT: {$this->time} - {$this}\n";
	 * 				usleep($this->time*1000000);
	 * 			}
	 * 		}
	 * 	}
	 * 
	 * Processos::$debug=true;
	 * $i=new Processos(2);
	 * $i[]=null;
	 * $i[]=array(.05,20);
	 * $i[]=.5;
	 * //$i->pool(2); //num threads in the same time
	 * //$i->pool();  //checking load
	 * $i->start(true);
	 * $i->wait();
	 * print "=>$i\n";
	 * ?>
	 * ~~~
	 *
	 * @return void
	 */
	final public function __construct() {
		$this->debug();
		$this->initMain();
		$args = func_get_args();
		foreach ($this->readonly as $k => $v) {
			if ($args) $this->__set($k, array_shift($args));
			else break;
		}
		$this->parameters = $args;
		$this->setStart(false);
	}
	final public function __get($name) {
		return @$this->readonly[$name];
	}
	final public function __set($name, $value) {
		return $this->readonly[$name] = $value;
	}
	public function __toString() {
		$cont = [];
		foreach ($this->pids as $obj) @$cont[$obj->status()]++;
		foreach ($cont as $status => $sum) if ($sum) $out[] = "{$this->statusList[$status]}($sum)";
		return implode(', ', $out);
	}
	public function __invoke($obj) {
		static $wait = 0;
		static $load = null;
		static $threds = null;
		static $history = [];

		$maxHistory = 60;
		sleep($wait);
		$wait = 1;
		$countHistory = count($history);
		$l = sys_getloadavg();
		$l = $l[0];

		if ($threds === null) $threds = $l <= 1 ? $this->poolStartThreads : 0;
		else {
			$sum = array_sum($history);
			$tps = round($sum / $countHistory); //threads per second
			$indHist = $maxHistory - $countHistory + 1;
			//$avg=array($sum[0]/$countHistory,$sum[1]/$countHistory,);
			if ($l <= 1) { //checa histórico para aumentar
				if ($tps < 0) $threds = $l < .6 ? 1 : 0;
				else {
					$multiplicator = (1 - $l) / $indHist + 1;
					$tps = max(1, $tps);
					$threds = round($tps * $multiplicator);
				}
			} else { //checa histórico para diminuir
				$threds = $l <= 1.5 ? 0 : -1;
			}
		}

		//Monta historico
		$load = $l;
		if ($countHistory >= $maxHistory) array_pop($history);
		array_unshift($history, $threds);
		return $threds;
	}

	//*begin main methods
	final private function initMain() {                                //Initialization Main-Object
		if (self::$startedId) return $this->init(self::$startedId);
		$this->id = '';
		$this->shared = new IPC_SharedMemory(null, $this->id);
		$this->sharedKey = $this->shared->key();
		$this->pids[$this->id] = $this;
	}
	final private function init(array $pass) {                         //Initialization Sub-Object
		self::$startedId = null;
		$this->parent = $pass[0];
		$this->index = $pass[1];
		$this->id = "{$this->parent->id}['{$this->index}']";
		$this->sharedKey = $this->parent->sharedKey;
		$this->pids = &$this->parent->pids;
		$this->shared = new IPC_SharedMemory($this->sharedKey, $this->id);
		$this->pids[] = $this;
	}
	final private function getShared($name) {                          //Get a shared variable
		return @$this->shared->{"{$this->id}$name"};
	}
	final private function setShared($name, $value) {                   //Set a shared variable
		$this->shared->{"{$this->id}$name"} = $value;
		return $value;
	}
	final private function newClass($value = []) {                 //Create a new class
		if (!is_array($value)) $value = (array)$value;
		$parm = [];
		foreach ($value as $k => $v) $parm[] = '$value["' . $k . '"]';
		return eval('return new ' . get_called_class() . '(' . implode(', ', $parm) . ');');
	}
	final private function checkAutoIncrement($index) {               //Get a next index
		if (is_null($index)) $index = ($this->lastId += is_null($this->lastId) ? 0 : 1);
		elseif (is_numeric($index)) $this->lastId = max($this->lastId, (int)$index);
		$this->debug("INDEX=$index/MAX={$this->lastId}");
		return $index;
	}
	final private function recursive($fn, $args, $out = []) {         //Excute the method $fn in every $this->data elements
		$this->debug();
		foreach ($this->data as $obj) $out[] = call_user_func_array([$obj, $fn], $args);
		return $out;
	}
	protected function debug($text = null) {                             //Print
		if (!self::$debug) return;
		if (is_null($text)) {
			$bt = debug_backtrace();
			$args = preg_replace('/^\[((.|\s)*)\]$/', '\1', json_encode($bt[1]['args']));
			//$text=$bt[0]['file'].'['.$bt[0]['line'].']#'; //file[line]:
			$text = '[' . $bt[0]['line'] . ']#';
			$text .= isset($bt[1]['class']) ? $bt[1]['class'] . $bt[1]['type'] : ''; //class-> or class:: (if exists)
			$text .= $bt[1]['function'] . '(' . $args . ');'; //function(
		}
		print date('[c]: ') . "$text\n";
		// print strftime('[%F %T]: ') . "$text\n";
	}
	/**/ //end main methods

	//*begin required implements methods
	final public function current() {                                  // implements by Iterator
		$this->debug();
		return current($this->data);
	}
	final public function key() {                                      // implements by Iterator
		$this->debug();
		return key($this->data);
	}
	final public function next(): void {                               // implements by Iterator
		$this->debug();
		if ($this->valid()) throw new Exception('at end of ' . get_called_class());
		next($this->data);
	}
	final public function rewind(): void {                             // implements by Iterator
		$this->debug();
		reset($this->data);
	}
	final public function valid(): bool {                              // implements by Iterator
		$this->debug();
		return $this->offsetExists(key($this->data));
	}
	final public function previous() {                                 // implements by Iterator
		$this->debug();
		return @prev($this->data);
	}
	final public function count(): int {                               // implements by Countable
		$this->debug();
		if (is_null($this->count)) $this->count = count($this->data);
		return $this->count;
	}
	final public function offsetExists($index): bool {                 // implements by ArrayAccess
		$this->debug();
		return array_key_exists($index, $this->data);
	}
	final public function offsetGet($index) {                          // implements by ArrayAccess
		$this->debug();
		$index = $this->checkAutoIncrement($index);
		if (array_key_exists($index, $this->data)) return $this->data[$index];
		return $this->offsetSet($index, null);
	}
	final public function offsetSet($offset, $value): void {           // implements by ArrayAccess
		$this->debug();
		$offset = $this->checkAutoIncrement($offset);
		if (!is_null($this->count)) $this->count++;
		if (is_object($value) && $value instanceof Threads) {
			$this->data[$offset] = $value;
		} else {
			self::$startedId = array($this, $offset);
			$this->data[$offset] = $this->newClass($value);
		}
	}
	final public function offsetUnset($index): void {                  // implements by ArrayAccess
		$this->debug();
		if (array_key_exists($index, $this->data)) {
			unset($this->data[$index]);
			if (!is_null($this->count)) $this->count--;
		}
	}
	/**/ //end required implements methods

	//*begin optional implements methods
	final public function keys() {                                     // implements array_keys()
		return array_keys($this->data);
	}
	final public function values() {                                   // implements array_values()
		return array_values($this->data);
	}
	final public function chunk($size, $preserve_keys = false) {       // implements array_values()
		$this->debug();
		return array_chunk($this->data, $size, $preserve_keys);
	}
	final public function merge($from, $overwrite = true) {            // implements by Threaded
		$this->debug();
		if (is_resource($from) || is_null($from)) return false;
		if (is_array($from) || is_object($from)) foreach ($from as $k => $v) {
			if (!$overwrite && $this->offsetExists($k)) $k == null;
			$this->offsetSet($k, $v);
		}
		else $this->offsetSet(null, $from);
		return true;
	}
	final public function pop() {                                      // implements array_pop()
		$this->debug();
		if (!$this->data) return;
		$this->count--;
		return array_pop($this->data);
	}
	final public function shift() {                                    // implements array_shift()
		$this->debug();
		if (!$this->data) return;
		$this->count--;
		return array_shift($this->data);
	}
	final public function synchronized(Closure $block) {               // implements by Threaded
		$this->debug();
		$args = func_get_args();
		array_shift($args);
		return call_user_func_array($block, $args);
	}
	final public function getTerminationInfo() {                       // implements by Threaded
		if (pcntl_waitpid($this->getPid(), $status, WNOHANG) == 0) return null;
		return $status;
	}
	final public function pool($num_threads = null) {                  // implements by Pool
		$this->debug();
		$this->poolQueue = $this->pids;
		$this->poolPaused = [];
		$this->poolRunning = [];

		if (!$num_threads) $num_threads = $this;
		if (is_numeric($num_threads)) {
			while ($this->poolQueue) {
				while ($this->poolQueue && count($this->poolRunning) < $num_threads) {
					$item = array_pop($this->poolQueue);
					$this->poolRunning[] = $item;
					$item->start();
				}
				foreach ($this->poolRunning as $k => $o) if ($o->isFinished()) unset($this->poolRunning[$k]);
			}
		} else {
			while ($this->poolQueue || $this->poolPaused || $this->poolRunning) {
				$threads = call_user_func($num_threads, $this); //__invoke

				while ($threads > 0 && $this->poolPaused) {
					$threads--;
					reset($this->poolPaused);
					$k = key($this->poolPaused);
					$this->poolRunning[$k] = $this->poolPaused[$k];
					unset($this->poolPaused[$k]);
					$this->poolRunning[$k]->unpause();
				}
				while ($threads > 0 && $this->poolQueue) {
					$threads--;
					end($this->poolQueue);
					$k = key($this->poolQueue);
					$this->poolRunning[$k] = $this->poolQueue[$k];
					unset($this->poolQueue[$k]);
					$this->poolRunning[$k]->start();
				}
				while ($threads < 0 && $this->poolRunning) {
					$threads++;
					reset($this->poolRunning);
					$k = key($this->poolRunning);
					$this->poolPaused[$k] = $this->poolRunning[$k];
					unset($this->poolRunning[$k]);
					$this->poolPaused[$k]->pause();
				}
				foreach ($this->poolRunning as $k => $o) if ($o->isFinished()) unset($this->poolRunning[$k]);
			}
		}
	}

	final public function start($recursive = false) {                  // implements by Thread
		$this->debug();
		if ($recursive) $this->recursive(__FUNCTION__, func_get_args());
		$this->setStart();
		$pid = pcntl_fork();
		if ($pid == -1) die("Could not fork\n");
		elseif ($pid) {
			$this->setPid($pid);
		} else {
			$this->setPid(posix_getpid());
			$this->run();
			$this->setFinished(true);
			$this->shared->release();
			exit(0);
		}
	}
	abstract public function run();                                    // implements by Threaded
	/**/ //end optional implements methods

	final public function pause($recursive = false) {
		$this->debug();
		if (!($pid = $this->getPid()) || $this->isPaused() || !$this->isRunning()) return false;
		$this->setPause(true);
		$out = posix_kill($pid, SIGSTOP);
		if ($recursive) $out = $this->recursive(__FUNCTION__, func_get_args(), $out);
		return $out;
	}
	final public function unpause($recursive = false) {
		$this->debug();
		if (!($pid = $this->getPid()) || !$this->isPaused() || !$this->isRunning()) return false;
		$out = posix_kill($pid, SIGCONT);
		$this->setPause(false);
		if ($recursive) $out = $this->recursive(__FUNCTION__, func_get_args(), $out);
		return $out;
	}
	final public function resume($recursive = false) {
		return $this->unpause($recursive);
	}
	final public function kill($sig = SIGKILL, $recursive = false) { //SIGQUIT SIGKILL
		$this->debug();
		if (!($pid = $this->getPid()) || !$this->isRunning()) return false;
		if ($recursive) $out = $this->recursive(__FUNCTION__, func_get_args());
		$out = posix_kill($pid, $sig);
		$this->setFinished(true);
		return $out;
	}
	final public function stop($sig = SIGKILL, $recursive = false) {
		return $this->kill($sig, $recursive);
	}
	final public function wait($recursive = true) {
		$this->debug();
		if (!($pid = $this->getPid()) || !$this->isStarted() || $this->isFinished() || $this->isPaused()) return;

		$this->setWait(true);
		if ($recursive === null) $recursive = 0;
		if ($recursive === true) $this->recursive(__FUNCTION__, func_get_args());
		if (is_bool($recursive)) {
			pcntl_waitpid($pid, $out, WUNTRACED);
			$this->setFinished(true);
		} else pcntl_waitpid((int)$recursive, $out, WUNTRACED);
		$this->setWait(false);
		return $out;
	}
	final public function priority($priority = 0, $recursive = false) {
		$this->debug();
		if (!($pid = $this->getPid())) return;
		$out = pcntl_setpriority($priority, $pid);
		if ($recursive) $out = array('this' => $out, 'child' => $this->recursive(__FUNCTION__, func_get_args()));
		return $out;
	}

	final public  function status() {
		if ($this->isStarted()) {
			if (!$this->getPid()) $s = self::STATUS_STARTING;
			elseif ($this->isPaused()) $s = self::STATUS_PAUSED;
			//elseif($this->isWaiting()) $s=self::STATUS_WAITING;
			elseif ($this->isFinished()) $s = self::STATUS_FINISHED;
			elseif ($this->isRunning()) $s = self::STATUS_RUNNING;
			else $s = self::STATUS_STARTING;
		} else $s = self::STATUS_NO_STARTED;
		return $s;
	}
	final public  function isRunning() {
		if (!($pid = $this->getPid()) || $this->isFinished()) return false;
		if (pcntl_waitpid($pid, $status, WNOHANG) == 0) {
			$this->setFinished(true);
			return false;
		}
		return true;
	}
	final public  function isPaused() {
		return (bool)$this->getShared('paused');
	}
	final public  function isWaiting() {
		return (bool)$this->getShared('waitting');
	}
	final public  function isFinished() {
		return (bool)$this->getShared('finished');
	}
	final public  function isTerminated() {
		return $this->isFinished();
	}
	final public  function isStarted() {
		return (bool)$this->getShared('started');
	}
	final public  function getPid() {
		return $this->pid ? $this->pid : $this->pid = $this->getShared('pid');
	}
	final public  function checkPid() {
		return ($pid = $this->getPid()) ? trim(`ps -p {$pid} --no-heading -o pid`) : false;
	}
	final private function setPid($pid = 0) {
		return $this->setShared('pid', $this->pid = $pid);
	}
	final private function setPause($value = false) {
		return $this->setShared('paused', $value);
	}
	final private function setWait($value = false) {
		return $this->setShared('waitting', $value);
	}
	final private function setFinished($value = false) {
		return $this->setShared('finished', $value);
	}
	final private function setStart($value = true) {
		$this->setShared('started', $value);
		$this->setPause(false);
		$this->setFinished(false);
		$this->setPid();
	}
}
