<?php
// ssh protocols
class SSH {
	private $protect=array(
		'conn'=>null,
		'shell'=>null,
		'error'=>'',
		'prompt'=>''
	);
	public $host='';
	public $user='';
	public $port=22;
	public $password='';
	public $shell_type='bash'; //xterm
	function __construct($host=false, $port=false) {
		$this->connect($host, $port);
	}
	function __destruct(){
		$this->closeShell();
	}
	function __get($nm){
		if(isset($this->protect[$nm])) return $this->protect[$nm];
	}
	function __set($nm,$val){}
	function __tostring(){
		return print_r($this,true);
		//return $this->protect['error'];
	}
	function connect($host=false, $port=false) {
		if($host!==false) $this->host=$host;
		if($port!==false) $this->port=$port;
		if ($this->host && $this->port) {
			$this->protect['conn']=@ssh2_connect($this->host, $this->port);
			$this->protect['error']="";
			if ($this->protect['conn']) return $this->protect['conn'];
			else $this->protect['error']="Connection failed. Host={$this->host} Port={$this->port}";
		}
		return false;
	}
	function auth($user=false, $password=false) {
		if($user!==false) $this->user=$user;
		if($password!==false) $this->password=$password;
		if ($this->protect['conn']) {
			$this->protect['error']="";
			if ($this->password) {
				$auth_methods=@ssh2_auth_password($this->protect['conn'], $this->user, $this->password );
				if($auth_methods) return $this->openShell();
				else $this->protect['error']="Authorization failed. User={$this->user} Passwd={$this->password}";
			} else {
				$auth_methods=ssh2_auth_none($this->protect['conn'], $this->user);
				if (in_array('password', $auth_methods)) return $this->openShell();
				else $this->protect['error']="Authorization failed. User={$this->user}";
			}
		}
		return false;
	}
	function openShell($shell_type=false) {
		if ($shell_type!==false) $this->shell_type=$shell_type;
		if ($this->protect['conn'] && $this->shell_type) {
			$this->protect['error']="";
			$this->protect['shell']=ssh2_shell($this->protect['conn'], $this->shell_type );
			if(!$this->protect['shell']) $this->protect['error']="Shell connection failed !";
			else {
				fwrite($this->protect['shell'],"pwd\n");
				$output='';
				while(!preg_match("/\n(.+?)pwd\s/",$output,$ret)) $output.=fgets($this->protect['shell']);
				$this->protect['prompt']=$ret[1];
				$this->expectPrompt();
//				print "\n\nOutput: $output\n";
				return $this->protect['shell'];
			}
		}
		return false;
	}
	function expectPrompt(){
		$output='';
		if($this->protect['prompt'] && $this->protect['shell']){
			$time=time();
			$max_time=2;
			while(!$pos=strpos($output,$this->protect['prompt'])) {
				$output.=$line=fgets($this->protect['shell']);
				if (time()-$time>$max_time) break;
			}
			if ($pos) $output=substr($output,0,$pos);
		}
		return $output;
	}
	function closeShell(){
		if ($this->protect['shell']) {
			fclose($this->protect['shell']);
			$this->protect['prompt']='';
		}
	}
	function writeShell($command='') {
		if ($command && $this->protect['shell']) fwrite($this->protect['shell'], $command."\n");
	}
	function exec(){
		$aCmd=func_get_args();
		$cmd=implode(" && ",$aCmd);
		if ($cmd && $this->protect['conn']){
			$stream = ssh2_exec($this->protect['conn'], $cmd);
			stream_set_blocking($stream, true);
			return fread($stream, 4096);
		}
	}
	function execShell($cmd='') {
		if (!$cmd || !$this->protect['shell']) return '';
		$cmd="$cmd\n";
		fwrite($this->protect['shell'],$cmd);
		return substr($this->expectPrompt(),strlen($cmd)+1);
	}

	function scp_get($remote_file,$local_file=''){
		if ($this->protect['conn']){
			if (!$local_file) $local_file=realpath('.');
			$this->protect['error']="";
			if(ssh2_scp_recv($this->protect['conn'],$remote_file,$local_file)) return true;
			$this->protect['error']="Get File Error";
		}
		return false;
	}
	function scp_put($local_file,$remote_file,$permition=0644){
		if ($this->protect['conn']){
			$this->protect['error']="";
			if(ssh2_scp_send($this->protect['conn'],$local_file,$remote_file,$permition)) return true;
			$this->protect['error']="Put File Error";
		}
		return false;
	}
}