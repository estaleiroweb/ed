<?php
class FTP {
	public $conn;
	public $mode=FTP_ASCII;
	public $path;

	public function __construct($url){
		$this->conn = ftp_connect($url);
	}
	public function __destruct(){
		ftp_close($this->conn);
	}
	public function __call($func, $a){
		if (function_exists($fn='ftp_'.$func) || function_exists($fn=$func)) {
			array_unshift($a, $this->conn);
			return call_user_func_array($fn, $a);
		} else die("$func is not a valid FTP function\n");
	}
	public function binary(){ $this->mode=FTP_BINARY; return $this; }
	public function asc(){ $this->mode=FTP_ASCII; return $this; }
	public function ascII(){ return $this->asc(); }
	public function cd($dir){ return ftp_chdir($this->conn,$dir); }
	public function rm($file){ return ftp_delete($this->conn,$file); }
	public function dir($dir=null){ return ftp_nlist($this->conn,$dir); }
	public function rawdir($dir=null,$recursive=false){ return ftp_rawlist($this->conn,$dir,$recursive); }
	public function get($remote_file,$local_file=null, $mode=null){
		if(!$local_file) ($local_file=$this->path) || ($local_file=trim(`pwd`));
		if(is_dir($local_file)) $local_file.='/'.basename($remote_file);
		elseif(!is_file($local_file) && !is_dir(dirname($local_file))) die("Fire/Dir não existe $local_file\n");
		if(!$mode) $mode=$this->mode;
		return ftp_get($this->conn,$local_file, $remote_file, $mode);
	}
}