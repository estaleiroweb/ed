<?php
class Wrapper_var {
	private $path;
	private $varname;
	private $position;

	function stream_open($path, $mode, $options, &$opened_path){
		$this->path=parse_url($path);
		$this->varname=$this->path['host'];
		if(!isset($GLOBALS[$this->varname])){
			trigger_error('Global variable '.$this->varname.' does not exist', E_USER_WARNING);
			return false;
		}
		$this->position=0;
		return true;
	}
	function stream_close(){
		return true;
	}
	function stream_read($count){
		//$count always 8192
		$ret=substr($GLOBALS[$this->varname], $this->position, $count);
		$this->position += strlen($ret);
		return $ret;
	}
	function stream_eof(){
		return $this->position>=strlen($GLOBALS[$this->varname]);
	}
	function stream_tell() {
		return $this->position;
	}
	function stream_seek($offset, $whence) {
		if($whence==SEEK_SET) {
			$this->position=$offset;
			return true;
		}
		return false;
	}
	function stream_stat() {
		return array();
	}
}
