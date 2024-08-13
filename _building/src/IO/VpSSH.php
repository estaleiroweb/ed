<?php
/** User/Pass Based Auth
	$ssh=VpSSH::init(array(
		'host' => 'server.com',
		'user' => 'username',//[opitional default root]
		'pass' => 'password', //[opitional]
		'port' => 22, //[opitional default 22]
	));
	$ls_binary=$ssh->which('ls');                         // Returns the string path of the command, if found, or false
	echo $ssh->exec("$ls_binary -lah");                   // Returns the string results of the command
	$result_file=$ssh->exec("$ls_binary -lah", 'file'); // Returns a filename which contains the results of the command
	$result_fp=$ssh->exec("$ls_binary -lah", 'fp');     // Returns a rewound filepointer which contains the result of the command
**/
/** Key Based Auth
	$ssh=VpSSH::init(array(
		'host' => 'server.com',
		'user' => 'username',//[opitional default root]
		'port' => 22,//[opitional default 22]
		'pass' => 'password', //[opitional]
		'kpub' => "ssh-dss ...",
		'kpri' => "-----BEGIN DSA PRIVATE KEY-----\n...\n-----END DSA PRIVATE KEY-----",
	));
**/
/** Instance by construct
	$ssh=new VpSSH(<host>[, user_default_root[, pass_default_null[, port_default_22[, kpub[, kpri]]]]]]);
**/

class VpSSH extends OverLoadElements {
	protected $protect=array(
		'host'  => null, // SSH Host
		'user'  => 'root', // Username
		'pass'  => null, // User Password | Key Password
		'port'  => 22,   // SSH Port

		'kpub'  => null, // Public Key
		'kpri'  => null, // Private Key
	);
	protected $readonly=array(
		'conn'  => null, // Resource: ssh2 connection
	); 
	protected $allParameters=array('host','user','pass','port','kpub','kpri');
	private $tmp='/var/tmp';
	
	function init(){
		$this->connect();
	}
	function connect() {
		if($this->readonly['conn']) return $this->readonly['conn'];
		if(!$this->protect['host'] || !$this->protect['port'] || !$this->protect['user']) return false;
		
		$this->readonly['conn']=ssh2_connect($this->protect['host'], $this->protect['port']);
		if(!$this->readonly['conn']) return $this->pr("Could not connect to {$this->protect['host']}:{$this->protect['port']}\n") && false;
		$this->pr("Connected to {$this->protect['host']}:{$this->protect['port']}");
		$auth=false;
		if($this->protect['kpri'] || $this->protect['kpub']) {
			$wayAuth='key';
			$pufile=tempnam($this->tmp, 'ssh-id-');
			$prfile=tempnam($this->tmp, 'ssh-id-');
			file_put_contents($pufile, $this->protect['kpub']);
			file_put_contents($prfile, $this->protect['kpri']);
			$auth=ssh2_auth_pubkey_file($this->readonly['conn'], $this->protect['user'], $pufile, $prfile, $this->protect['pass']);
			//if ($this->protect['pass']) 
			//else 
			//	$auth=ssh2_auth_pubkey_file($this->readonly['conn'], $this->protect['user'], $pufile, $prfile);
			unlink($pufile);
			unlink($prfile);
		}
		elseif($this->protect['pass']){
			$wayAuth='password';
			$auth=ssh2_auth_password($this->readonly['conn'], $this->protect['user'], $this->protect['pass']);
		}
		else {
			$wayAuth='without';
			$prfile=trim(`ls ~/.ssh/id_rsa 2> /dev/null`);
			if($prfile && file_exists($pufile=$prfile.'.pub')) {
				$auth=ssh2_auth_pubkey_file($this->readonly['conn'], $this->protect['user'], $pufile, $prfile);
			}
		}
		if($auth) {
			$this->pr("- Logged in, via $wayAuth auth, as {$this->protect['user']}");
		} else $this->pr("- Could not authenticate via $wayAuth auth for user {$this->protect['user']}");
		return $auth;
	}
	function rawexec($command) {
		if (!$this->connect()) return false;
		$res=ssh2_exec($this->readonly['conn'], $command);
		if ($res && is_resource($res)) return $res;
		$this->pr("\tFailed Executing:\t$command");
		return false;
	}
	function exec($command, $result='string') {
		if(!$this->connect()) return false;
		$res=$this->rawexec($command);
		if(!$res || !is_resource($res)) return false;
		stream_set_blocking($res, true);
		switch($result) {
			case 'file':
				$file=tempnam($this->tmp, 'ssh-exec-');
				$fp =fopen($file, 'w');
				while(!feof($res)) fwrite($fp, fread($res, 4096));
				fclose($fp);
				fclose($res);
				return $file;
				break;
			case 'fp':
				$file=tempnam($this->tmp, 'ssh-exec-');
				$fp=fopen($file, 'w');
				unlink($file);
				while(!feof($res)) fwrite($fp, fread($res, 4096));
				fclose($res);
				rewind($fp);
				return $fp;
				break;
			default:
				$rval=stream_get_contents($res);
				fclose($res);
				return $rval;
				break;
		}
	}
	function which($command) {
		if (!$this->connect()) return false;
		if (($res=trim($this->exec("which $command")))) return $res;
		$paths=array(
			'/bin',
			'/usr/bin',
			'/usr/local/bin',
			'/sbin',
			'/susr/bin',
			'/susr/local/bin',
		);
		foreach($paths as $path) if (file_exists($res="$path/$command")) return $res;
		//locate -l1 -ber ^install\.log$
	}
	function tunnel($remote_port,$ssh_server=null) {
		if(!$this->connect()) return false;
		if(!$ssh_server) $ssh_server=$this->protect['host'];
		return ssh2_tunnel($this->readonly['conn'], $ssh_server, $remote_port);
	}
}
