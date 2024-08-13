<?php
class Wrapper_expect {
	private $command;
	private $resource;
	private $pid;
	private $pidThread;

	function stream_open($path, $mode, $options, &$opened_path){
		$scheme=substr($path,0,9);
		if($scheme!=='expect://') {
			trigger_error('Scheme not is expect', E_USER_WARNING);
			return false;
		}
		$this->command=substr($path,9);
		$this->resource=popen($this->command,$mode);
		
		if(is_resource($this->resource)) return true;
		trigger_error('Error to run command '.$this->command, E_USER_WARNING);
		return false;
		
//$res=fopen('expect://'.$cmd,'r');
#$pid=posix_getpid();
#$id=uniqid('');
#$res=popen("( $cmd ); T=$?; [ '$id' ]; exit \$T",'r');
#exec("ps -o pid,cmd --ppid $pid --no-heading",$ppids);
#$ppids=preg_replace('/ .*/','',preg_grep("/ '$id' /",$ppids));


	}
	function stream_close(){
		return pclose($this->resource);
	}
	function stream_read($count)                    { return fread($this->resource,$count); }
	function stream_eof()                           { return feof($this->resource); }
	function stream_tell()                          { return ftell($this->resource); }
	function stream_seek($offset, $whence=SEEK_SET) { return fseek($this->resource,$offset, $whence); }
	function stream_stat()                          { return fstat($this->resource); }
}
