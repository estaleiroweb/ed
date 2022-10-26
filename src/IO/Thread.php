<?php

namespace EstaleiroWeb\ED\IO;

use Exception;

//use EstaleiroWeb\ED\IO\_;
//use ReflectionClass;

/**
 * Implements threading in PHP
 * 
 * @package ed-IO
 * @version 1.0.0 - stable
 * @author Tudor Barbu <miau@motane.lu>
 * @copyright MIT
 */
class Thread {
	const FUNCTION_NOT_CALLABLE     = 10;
	const COULD_NOT_FORK            = 15;
	/**
	 * possible errors
	 *
	 * @var array
	 */
	private $errors = array(
		Thread::FUNCTION_NOT_CALLABLE   => 'You must specify a valid function name that can be called from the current scope.',
		Thread::COULD_NOT_FORK          => 'pcntl_fork() returned a status of -1. No new process was created',
	);
	/**
	 * callback for the function that should
	 * run as a separate thread
	 *
	 * @var mixed callback
	 */
	protected $runnable;
	/**
	 * holds the current process id
	 *
	 * @var integer
	 */
	private $pid;
	/**
	 * hodls exit code after child die
	 */
	private $exitCode = -1;

	/**
	 * class constructor - you can pass
	 * the callback function as an argument
	 *
	 * ~~~php
	 * <?php
	 * // test to see if threading is available
	 * if( ! Thread::available() ) die( 'Threads not supported' );
	 * 
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
	 * @param mixed callback $_runnable
	 */
	public function __construct($_runnable = null) {
		if ($_runnable !== null) {
			$this->setRunnable($_runnable);
		}
	}
	/**
	 * checks if threading is supported by the current
	 * PHP configuration
	 *
	 * @return boolean
	 */
	public static function available() {
		return function_exists('pcntl_fork');
	}
	/**
	 * sets the callback
	 *
	 * @param mixed callback $_runnable
	 * @return mixed callback
	 */
	public function setRunnable($_runnable) {
		if (self::runnableOk($_runnable)) {
			$this->runnable = $_runnable;
		} else {
			throw new Exception($this->getError(Thread::FUNCTION_NOT_CALLABLE), Thread::FUNCTION_NOT_CALLABLE);
		}
	}
	/**
	 * gets the callback
	 *
	 * @return mixed callback
	 */
	public function getRunnable() {
		return $this->runnable;
	}
	/**
	 * checks if the callback is ok (the function/method
	 * actually exists and is runnable from the current
	 * context)
	 * 
	 * can be called statically
	 *
	 * @param mixed callback $_runnable
	 * @return boolean
	 */
	public static function runnableOk($_runnable) {
		return (function_exists($_runnable) && is_callable($_runnable));
	}
	/**
	 * returns the process id (pid) of the simulated thread
	 * 
	 * @return int
	 */
	public function getPid() {
		return $this->pid;
	}
	/**
	 * checks if the child thread is alive
	 *
	 * @return boolean
	 */
	public function isAlive() {
		$pid = pcntl_waitpid($this->pid, $status, WNOHANG);

		if ($pid === 0) { // child is still alive
			return true;
		} else {
			if (pcntl_wifexited($status) && $this->exitCode == -1) { // normal exit
				$this->exitCode = pcntl_wexitstatus($status);
			}
			return false;
		}
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
	/**
	 * starts the thread, all the parameters are 
	 * passed to the callback function
	 * 
	 * @return void
	 */
	public function start() {
		$pid = @pcntl_fork();
		if ($pid == -1) { //not suport
			throw new Exception($this->getError(Thread::COULD_NOT_FORK), Thread::COULD_NOT_FORK);
		}
		if ($pid) { // parent 
			$this->pid = $pid;
		} else { // child
			pcntl_signal(SIGTERM, array($this, 'signalHandler'));
			$arguments = func_get_args();
			if (empty($arguments)) call_user_func($this->runnable);
			else call_user_func_array($this->runnable, $arguments);
			exit(0);
		}
	}
	/**
	 * attempts to stop the thread
	 * returns true on success and false otherwise
	 *
	 * @param integer $_signal - SIGKILL/SIGTERM
	 * @param boolean $_wait
	 */
	public function stop($_signal = SIGKILL, $_wait = false) {
		if ($this->isAlive()) {
			posix_kill($this->pid, $_signal);
			if ($_wait) {
				pcntl_waitpid($this->pid, $status = 0);
			}
		}
	}
	/**
	 * alias of stop();
	 *
	 * @return boolean
	 */
	public function kill($_signal = SIGKILL, $_wait = false) {
		return $this->stop($_signal, $_wait);
	}
	/**
	 * gets the error's message based on
	 * its id
	 *
	 * @param integer $_code
	 * @return string
	 */
	public function getError($_code) {
		if (isset($this->errors[$_code])) {
			return $this->errors[$_code];
		} else {
			return 'No such error code ' . $_code . '! Quit inventing errors!!!';
		}
	}
	/**
	 * signal handler
	 *
	 * @param integer $_signal
	 */
	protected function signalHandler($_signal) {
		switch ($_signal) {
			case SIGTERM:
				exit(0);
				break;
		}
	}
}
