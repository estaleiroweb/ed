<?php
class Compatibility {
	static private function iniSet($defaults,$ini_set){
		foreach($defaults as $k=>$v) {
			if(array_key_exists($k,$ini_set)) ini_set($k, $ini_set[$k]);
			elseif(ini_get($k)===false)   ini_set($k, $v);
		}
	}
	static private function checkWrapper($wrapper){
		$exists=!in_array($wrapper, stream_get_wrappers());
		if($exists) stream_wrapper_register($wrapper, 'Wrapper_'.$wrapper);
		return;
	}
	static public function expect($ini_set=array()){
		$open=function_exists('expect_popen');
		$expectl=function_exists('expect_expectl');
		$wrapper=self::checkWrapper('expect');
		$out=false;
		if($open || $expectl || $wrapper) {
			$out=true;
			self::iniSet(array(
				'expect.timeout', 10,     //The timeout period for waiting for the data, when using the expect_expectl() function. A value of "-1" disables a timeout from occurring. A value of "0" causes the expect_expectl() function to return immediately.
				'expect.loguser', 1,      //Whether expect should send any output from the spawned process to stdout. Since interactive programs typically echo their input, this usually suffices to show both sides of the conversation.
				'expect.logfile', '',     //Name of the file, where the output from the spawned process will be written. If this file doesn't exist, it will be created.
				'expect.match_max', 2000, //Changes default size (2000 bytes) of the buffer used to match asterisks in patterns.
			),$ini_set);

			define('EXP_GLOB',0);         //Indicates that the pattern is a glob-style string pattern.
			define('EXP_EXACT',1);        //Indicates that the pattern is an exact string.
			define('EXP_REGEXP',3);       //Indicates that the pattern is a regexp-style string pattern.
			define('EXP_EOF',0);          //Value, returned by expect_expectl(), when EOF is reached.
			define('EXP_TIMEOUT',1);      //Value, returned by expect_expectl() upon timeout of seconds, specified in value of expect.timeout
			define('EXP_FULLBUFFER',2);   //Value, returned by expect_expectl() if no pattern have been matched.
		}
		if($open) {
			function expect_popen($command) { 
				return popen($command,'r');
			}
		}
		if($expectl) {
			function expect_expectl($expect ,array $cases, &$match=array()){
				$timeout=ini_get('expect.timeout');
				$loguser=ini_get('expect.loguser');
				$logfile=ini_get('expect.logfile');
				$match_max=ini_get('expect.match_max');
				$buffer='';
				$match=array();
				$time=time();
				do {
					$part=fread($expect, 1024);
					//$part=stream_get_contents($expect);
					if($part===false) return EXP_EOF;
					if($match_max && strlen($part)>$match_max) return EXP_FULLBUFFER;
				} while ($time!=0 && time()-$time<$timeout);
				return EXP_TIMEOUT;
			}
		}
		return $out;
	}
	static public function varWrapper(){
		return self::checkWrapper('var');
	}
}
