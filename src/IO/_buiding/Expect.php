<?php
class Expect {
	public $debug=false;
	public $fp=false;
	public $port=23;
	public $timeout=10;
	public $error=0; 
	public $verifyNoExpect=2; //0-verifica comoum todo, 1-verifica last line, 2-verifica last line com o resto
	public $out=array();
	public $lineFeed="\r\n";
	public $waitChar="";
	public $prErrors=array(
		0=>"Sem errors",1=>"Connection TimeOut",2=>"EOF",
		3=>"Connection Error",4=>"Text doesn't expect",
		5=>"'ifExist' event called",6=>"Connection doesn't stabled'"
	);
	public $flag, $errno, $errstr;
	private $ifExistErrorFind=false;
	private $ifExistErrorCmd=false;
	
	function __construct($fp=false) {
		$this->fp=$fp;
	}
	function open($ip,$port=false,$timeOut=false){
		$this->close();
		if (!$timeOut) $timeOut=$this->timeout;
		if (!$port) $port=$this->port;
		$this->fp=@fsockopen($ip, $port, $this->errno, $this->errstr, $timeOut);
		$this->error=$this->fp?0:3;
		if ($this->error) $this->prErr();
		return $this->fp;
	}
	function close(){
		if (!$this->fp) return;
		fclose($this->fp);
		$this->fp=false;
	}
	function expti($findStr){ return $this->_expt($findStr,"existi"); }
	function expt($findStr){ return $this->_expt($findStr,"exist"); }
	function preg_expt($pattern){ return $this->_expt($pattern,"preg_exist"); }
	function put($value){ $this->hardPut("$value$this->lineFeed"); }
	function hardPut($value){
		if (!$this->fp) return $this->showErro(6);
		if(!($ret=@fputs ($this->fp,$value))) $this->showErro(2);
		return $ret;
	}
	function get(){
		if (!$this->fp) return $this->showErro(6);
		$this->pr($str=fread($this->fp, 65535));
		return $str;
	}
	function hasContent(){
		$pos=ftell($this->fp);
		fseek($this->fp,0,SEEK_END);
		$ret=ftell($this->fp)==$pos;
		fseek($this->fp,$pos);
		return $ret;
	}
	function ifExisti($findStr=false){
		$this->ifExistErrorFind=$findStr;
		$this->ifExistErrorCmd="existi";
	}
	function ifExist($findStr=false){
		$this->ifExistErrorFind=$findStr;
		$this->ifExistErrorCmd="exist";
	}
	function ifPreg_Exist($pattern=false){
		$this->ifExistErrorFind=$pattern;
		$this->ifExistErrorCmd="preg_exist";
	}
	private function existi($findStr,$text){ 
		$r=true;
		if ($this->verifyNoExpect) {
			$lastLine=stripos($this->getLastLine($text),$findStr);
			if ($lastLine!==false) return true;
			if ($this->verifyNoExpect==1) return false;
			$r=0;
		}
		return stripos($text,$findStr)===false?false:$r;
	}
	private function exist($findStr,$text){ 
		$r=true;
		if ($this->verifyNoExpect) {
			$lastLine=strpos($this->getLastLine($text),$findStr);
			if ($lastLine!==false) return true;
			if ($this->verifyNoExpect==1) return false;
			$r=0;
		}
		return strpos($text,$findStr)===false?false:$r;
	}
	private function preg_exist($pattern,$text){
		$r=true;
		if ($this->verifyNoExpect) {
			$lastLine=preg_match($pattern,$this->getLastLine($text));
			if ($lastLine) return true;
			if ($this->verifyNoExpect==1) return false;
			$r=0;
		}
		return preg_match($pattern,$text)?$r:false;
	}
	private function getLastLine($string) {
		if (preg_match("/(.*)$/",trim($string),$ret)) return $ret[1];
	}
	private function _expt($findStr,$function){
		if(!$this->fp) return $this->showErro(6);
		$aEr=array('timed_out'=>1,'eof'=>2);
		$idx=count($this->out);
		$this->out[$idx]='';
		$ret=false;
		do {
			$this->flag=stream_get_meta_data($this->fp);
//print "stream_type={$this->flag['stream_type']}, mode={$this->flag['mode']}, unread_bytes={$this->flag['unread_bytes']}, seekable={$this->flag['seekable']}, timed_out={$this->flag['timed_out']}, blocked={$this->flag['blocked']}, eof={$this->flag['eof']}\n";
			foreach ($aEr as $k=>$v) if ($this->flag[$k]) return $this->showErro($v);
			$this->out[$idx].=$this->get();
			if ($this->ifExistErrorFind) {
				$cmd=$this->ifExistErrorCmd;
				if ($this->$cmd($this->ifExistErrorFind,$this->out[$idx])!==false) {
					$this->error=5;
					$findStr.="->".$this->ifExistErrorFind;
					break;
				}
			}
			if (($ret=$this->$function($findStr,$this->out[$idx]))===0) $this->error=4;
			elseif(!$ret && $this->waitChar) $this->hardPut($this->waitChar);
			if ($this->error) break;
		} while (!$ret);
		if ($this->error) $this->prErr($findStr);
		return @$this->out[$idx];
	}
	function pr($text){ if ($this->debug) print $text; }
	function prErr($err='') { $this->pr("\nERROR({$this->error}): {$this->prErrors[$this->error]}".($err?" ($err)":"")."\n"); }
	function showErro($n,$err=''){
		$this->error=$n;
		$this->prErr($err);
	}
}