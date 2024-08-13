<?php
define ('MESSAGE',0);
define ('NORMAL_MESSAGE',1);
define ('WARNING',2);
define ('CRITICAL_ERROR',3);
define ('FATAL_ERROR',4);

class OutError{
	use Traits\OO\Singleton, JSON, Show;
	
	static $show_debug_all_erros=true;
	static $__TRAP_ERROR=0;
	static $__TRAP_ERROR_CALLBACK=NULL; // call_user_func_array => 'function' | array('obj|class','method')
	static $__TRAP_ERROR_CALLBACK_PARAMETERS=array();
	static $errorLevels=array(0=>'Message',1=>'Normal',2=>'Warning',3=>'Critical',4=>'Fatal');
	
	private function __construct(){
		$this->set();
		//$this->reporting ([ int $nível ] )
	}

	public function reporting($error_num){
		error_reporting($error_num);
		/*
		Valor	Constante
		1       E_ERROR
		2       E_WARNING
		4       E_PARSE
		8       E_NOTICE
		16      E_CORE_ERROR
		32      E_CORE_WARNING
		64      E_COMPILE_ERROR
		128     E_COMPILE_WARNING
		256     E_USER_ERROR
		512     E_USER_WARNING
		1024    E_USER_NOTICE
		6143    E_ALL
		2048    E_STRICT
		4096    E_RECOVERABLE_ERROR
		*/
	}
	public function set(){
		set_error_handler(array($this,'error_handler'), E_ALL & ~E_NOTICE);
	}
	public function disable(){
		set_error_handler(array($this,'no_error_handler'), E_ALL & ~E_NOTICE & ~E_WARNING);
	}
	public function restore(){
		restore_error_handler();
	}
	public function compile_debug_backtrace($bt) {
		if(!$bt) return '';
		array_shift($bt);
		$bt=array_reverse($bt);
		$message=array();
		if($bt) {
			foreach($bt as $k=>$t) {
				$cmd=@$t['class'];
				$cmd.=@$t['type'];
				$cmd.=@$t['function'];
				$args=@$t['args']?preg_replace('/^\[(.*)\]$/','(\1)',$this->json_encode_full($t['args'])):'';
				$message[$k+1]="{$t['file']}[{$t['line']}] {$cmd}".$args;
			}
		}
		return preg_replace(
			array('/^Array\s*\([\r\n]+/','/[\r\n]+\)$/','/ +\[(\d+)\]\s*=>/'),
			array('','','\1:'),
			print_r($message,true)
		);
	}
	public function no_error_handler($errno, $errstr, $errfile, $errline, $errcontext,$bt=array(),$trigger=true){ }
	public function error_handler($errno, $errstr, $errfile, $errline, $errcontext,$bt=array(),$trigger=true) {
		//E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_STRICT
		if(self::$__TRAP_ERROR & $errno && self::$__TRAP_ERROR_CALLBACK) call_user_func_array(self::$__TRAP_ERROR_CALLBACK,self::$__TRAP_ERROR_CALLBACK_PARAMETERS);

		if(!self::$show_debug_all_erros || $errno==8192) return;
		$message=array();
		if(!$bt) $bt=debug_backtrace(); //Generates a backtrace {line,file,class,type,function,object,args)
		$this->error($this->compile_debug_backtrace($bt),0,"<label>ERROR [$errno] </label><i>$errstr </i><label>File: </label><i>$errfile [$errline]</i>");
		if($trigger) @trigger_error($errstr, $errno);
	}
	public function error($message='',$level=0,$title=false,$showBT=false){
		$message=print_r($message,true);
		if($showBT) $message.=($message?"\n\n":'').$this->compile_debug_backtrace(debug_backtrace());
		$level=min(floor(abs($level)),FATAL_ERROR);
		$title=self::$errorLevels[$level].($title?': ':'').$title;
		if(@$_SERVER['SHELL']) {
			print '###'.strip_tags($title)."\n";
			if($message) print "$message\n";
		}
		else {
			$class=strtolower(self::$errorLevels[$level]).'_error';
			$message=$this->htmlScpChar($message);
			print $this->makeBox($message,$title,'makeBox errorBox '.$class);
		}
		if($level==FATAL_ERROR) {
			throw new Exception('FATAL ERROR');
			exit;
		}
		return !$level;
	}
}