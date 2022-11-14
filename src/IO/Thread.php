<?php

namespace EstaleiroWeb\ED\IO;

use Exception;
use EstaleiroWeb\Traits\GetSet;

/**
 * Implements threading in PHP
 * 
 * @package ed-IO
 * @version 1.0.0 - stable
 * @author Tudor Barbu <miau@motane.lu>
 * @copyright MIT
 */
class Thread {
	use GetSet;

	const OK = 0;
	const ERROR_PCNTL_FORK = 1;
	const ERROR_CALLBACK_FUNCTION_NOT_EXISTS = 2;
	const ERROR_CALLBACK_FUNCTION_NOT_CALLABLE = 3;
	const ERROR_CALLBACK_FUNCTION = 4;
	const ERROR_FORK = 10;
	const ERROR_SHARE_MEMORY = 15;
	const ERROR_PID_STOPED = 101;
	const ERROR_PID_KILLED = 102;
	const ERROR_PID_TERM = 103;
	const ERRORS = [
		self::OK => null,
		self::ERROR_PCNTL_FORK => 'Function pcntl_fork doesn\'t exist',
		self::ERROR_CALLBACK_FUNCTION_NOT_EXISTS => 'Callback function doesn\'t exist',
		self::ERROR_CALLBACK_FUNCTION_NOT_CALLABLE => 'Callback function must be a valid function that can be called from call_user_func_array',
		self::ERROR_CALLBACK_FUNCTION => 'Callback function ERROR',
		self::ERROR_FORK => 'pcntl_fork() returned a status of -1. No new process was created',
		self::ERROR_SHARE_MEMORY => 'shm_attach() returned error to create shared memory',
		self::ERROR_PID_STOPED => 'Process stoped',
		self::ERROR_PID_KILLED => 'Process killed',
		self::ERROR_PID_TERM => 'Process terminated',
	];
	static public $breakOnError = true;
	protected $readonly = [];
	protected $protect = [];
	/**
	 * callback for the function that should run as a separate thread
	 *
	 * @var mixed callback function
	 */
	protected $callbackFunction;
	/**
	 * Arguments of the callback function
	 *
	 * @var array|null
	 */
	protected $args;
	/**
	 * holds the current process id
	 *
	 * @var integer
	 */
	protected $pid;
	/**
	 * hodls exit code after child die
	 */
	protected $exitCode;
	protected $idSharedMemory;
	protected $benchmark;
	protected $benchmarkQuant;
	protected $benchmarkArray = true;

	/**
	 * class constructor - you can pass
	 * the callback function as an argument
	 *
	 * ~~~php
	 * <?php
	 * // function to be ran on separate threads
	 * function paralel($_limit,$_name) {
	 * 	for ( $index = 0; $index < $_limit; $index++ ) {
	 * 		echo 'Now running thread ' . $_name . PHP_EOL;
	 * 		sleep(1);
	 * 	}
	 * }
	 * 
	 * // create 2 thread objects
	 * $t1=new Thread('paralel');
	 * $t2=new Thread('paralel');
	 * 
	 * // start them
	 * $t1->start(10,'t1');
	 * $t2->start(10,'t2');
	 * 
	 * // keep the program running until the threads finish
	 * while( $t1->isAlive() && $t2->isAlive() );
	 * ?>
	 * ~~~
	 * 
	 * @param mixed callback $callbackFunction
	 */
	public function __construct($callbackFunction, $args = []) {
		if (!function_exists('pcntl_fork')) return $this->error(self::ERROR_PCNTL_FORK);
		if (!function_exists($callbackFunction)) return $this->error(self::ERROR_CALLBACK_FUNCTION_NOT_EXISTS);
		if (!is_callable($callbackFunction)) return $this->error(self::ERROR_CALLBACK_FUNCTION_NOT_CALLABLE);

		// Shared memory
		$file = tempnam(sys_get_temp_dir(), 'ipcThread_');
		$this->idSharedMemory = shm_attach(ftok($file, chr(0)));
		unlink($file);
		if (!$this->idSharedMemory) return $this->error(self::ERROR_SHARE_MEMORY);

		$this->callbackFunction = $callbackFunction;
		$this->args = (array)$args;
		$this->benchmarkOff();
	}
	public function __destruct() {
		if ($this->idSharedMemory) shm_remove($this->idSharedMemory);
	}
	public function __invoke() {
		return [
			'callbackFn' => $this->callbackFunction,
			'pid' => $this->pid,
			'return' => $this->pidReturn,
			'time' => $this->pidTime,
			'error' => $this->pidError,
			'benchmark' => $this->benchmark,
			'quant' => $this->benchmarkQuant,
			'count' => $this->pidCount,
		];
	}

	/**
	 * return exit code of child (-1 if child is still alive)
	 *
	 * @return int
	 */
	public function getExitCode() {
		$this->isAlive();
		return $this->exitCode;
	}
	public function getPidTime() {
		return $this->share('pidTime');
	}
	public function getPidReturn() {
		return $this->share('pidReturn');
	}
	public function getPidError() {
		return $this->share('pidError');
	}
	public function getPidCount() {
		return $this->share('pidCount');
	}
	public function setPidTime($val) {
		return $this->share('pidTime', $val);
	}
	public function setPidReturn($val) {
		return $this->share('pidReturn', $val);
	}
	public function setPidError($val) {
		return $this->share('pidError', $val);
	}
	public function setPidCount($val) {
		return $this->share('pidCount', $val);
	}

	public function share($nm, $val = null) {
		static $keys = [], $i = 0;
		if (key_exists($nm, $keys)) $key = $keys[$nm];
		else $keys[$nm] = $key = $i++;

		if (is_null($val)) {
			if (!shm_has_var($this->idSharedMemory, $key)) return;
			return shm_get_var($this->idSharedMemory, $key);
		}
		return shm_put_var($this->idSharedMemory, $key, $val);
	}
	/**
	 * gets the error's message based on its id
	 *
	 * @param integer $codeError
	 * @return string
	 */
	public function error($codeError) {
		$this->exitCode = $codeError;
		$message = key_exists($codeError, self::ERRORS) ?
			self::ERRORS[$codeError] :
			'No such error code ' . $codeError . '! Quit inventing errors!!!';
		if (self::$breakOnError) {
			throw new Exception($message, $codeError);
		}
		return $message;
	}
	/**
	 * checks if the child thread is alive
	 *
	 * @return boolean
	 */
	public function isAlive() {
		if (is_null($this->pid)) return;
		if (!is_null($this->exitCode)) return false;

		$pid = pcntl_waitpid($this->pid, $status, WNOHANG);
		if ($pid === 0) return true; // child is still alive
		if (pcntl_wifexited($status)) { // normal exit
			$this->exitCode = pcntl_wexitstatus($status);
		}
		return false;
	}
	/**
	 * attempts to stop the thread returns true on success and false otherwise
	 *
	 * @param integer $_signal - SIGKILL/SIGTERM
	 * @param boolean $_wait
	 */
	public function term($_signal = SIGTERM, $_wait = false) {
		if ($this->isAlive()) {
			posix_kill($this->pid, $_signal);
			if ($_wait) {
				pcntl_waitpid($this->pid, $status = 0);
			}
		}
		$this->exitCode = self::ERROR_PID_TERM;
		return $this;
	}
	/**
	 * alias of term(SIGSTOP, $_wait);
	 *
	 * @return boolean
	 */
	public function stop($_wait = false) {
		$this->term(SIGSTOP, $_wait);
		$this->exitCode = self::ERROR_PID_STOPED;
		return $this;
	}
	/**
	 * alias of term(SIGKILL, $_wait);
	 *
	 * @return boolean
	 */
	public function kill($_wait = false) {
		$this->term(SIGKILL, $_wait);
		$this->exitCode = self::ERROR_PID_KILLED;
		return $this;
	}
	public function pause($_wait = false) {
	}
	public function unpause($_wait = false) {
	}
	public function resume($_wait = false) {
	}

	public function benchmarkOff() {
		$this->benchmark = false;
		$this->benchmarkQuant = 0;
		$this->benchmarkArray = false;
		return $this;
	}
	public function benchmarkTime($val = 10, $array = false) {
		$this->benchmark = 'ByTime';
		$this->benchmarkQuant = $val;
		$this->benchmarkArray = $array;
		return $this;
	}
	public function benchmarkLoop($val = 1000, $array = false) {
		$this->benchmark = 'ByLoop';
		$this->benchmarkQuant = $val;
		$this->benchmarkArray = $array;
		return $this;
	}
	public function run($args) {
		$this->pidReturn = call_user_func_array($this->callbackFunction, $args);
		$this->pidCount = 1;
	}
	public function runByTime($args) {
		$start = microtime(true);
		$i = 0;
		if ($this->benchmarkArray) {
			$return = [];
			do {
				$return[] = call_user_func_array($this->callbackFunction, $args);
				$i++;
			} while ((microtime(true) - $start) < $this->benchmarkQuant);
		} else {
			$return = null;
			do {
				$return = call_user_func_array($this->callbackFunction, $args);
				$i++;
			} while ((microtime(true) - $start) < $this->benchmarkQuant);
		}
		$this->pidReturn = $return;
		$this->pidCount = $i;
	}
	public function runByLoop($args) {
		$start = microtime(true);
		if ($this->benchmarkArray) {
			$return = [];
			for ($i = 0; $i < $this->benchmarkQuant; $i++) {
				$return[] = call_user_func_array($this->callbackFunction, $args);
			}
		} else {
			$return = null;
			for ($i = 0; $i < $this->benchmarkQuant; $i++) {
				$return = call_user_func_array($this->callbackFunction, $args);
			}
		}
		$this->pidReturn = $return;
		$this->pidCount = microtime(true) - $start;
	}
	/**
	 * start signal handler
	 *
	 * @param integer $_signal
	 */
	protected function signalHandler($_signal) {
		switch ($_signal) {
			case SIGSTOP:
			case SIGKILL:
			case SIGTERM:
				exit(0);
				break;
		}
	}
	/**
	 * starts the thread, all the parameters are 
	 * passed to the callback function
	 * 
	 * @return void
	 */
	public function start() {
		if (is_null($this->callbackFunction)) return;
		$this->exitCode = null;
		$this->pidReturn = null;
		$this->pidError = null;
		$this->pidTime = null;
		$pid = @pcntl_fork();
		if ($pid == -1) { //not suport
			$this->error(self::ERROR_FORK);
		} elseif ($pid) { // parent 
			$this->pid = $pid;
		} else { // child
			pcntl_signal(SIGTERM, [$this, 'signalHandler']);
			try {
				($args = func_get_args()) || ($args = $this->args);
				$fn = 'run' . $this->benchmark;
				$start = microtime(true);
				call_user_func([$this, $fn], $args);
				$exitCode = self::OK;
			} catch (Exception $e) {
				$this->pidError = $e;
				$exitCode = self::ERROR_CALLBACK_FUNCTION;
			} finally {
				$this->pidTime = microtime(true) - $start;
				exit($exitCode);
			}
		}
		return $this;
	}
	public function wait() {
		while ($this->isAlive());
		return $this;
	}
}
