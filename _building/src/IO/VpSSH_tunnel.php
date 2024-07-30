<?php
/** Key Based Auth
	$tunnel=VpSSH_tunnel::init( array(
		'user' => 'username',
		'host' => 'server.com',
		'port' => 22,
		'kpub' => "ssh-dss ...",
		'kpri' => "-----BEGIN DSA PRIVATE KEY-----\n...\n-----END DSA PRIVATE KEY-----",
	));
	$local_proxy_port = 4444;
	$remote_proxy_host = '127.0.0.1';
	$remote_proxy_port = 3306;
	$tunnel->proxy( $local_proxy_port, $remote_proxy_host, $remote_proxy_port );
	// We now have a tcp server on 127.0.0.1 port 4444 which is proxying, over ssh, to 127.0.0.1 port 3306 on myserver.com
**/
/** User/Pass Based Auth
	$tunnel=VpSSH_tunnel::init(array(
		'user' => 'username',
		'pass' => 'password',
		'host' => 'myserver.com',
		'port' => 22
	));
**/
class VpSSH_tunnel extends VpSSH {
	protected $protect=array(
		'host'  => null, // SSH Host
		'user'  => 'root', // Username
		'pass'  => null, // User Password | Key Password
		'port'  => 22,   // SSH Port

		'kpub'  => null, // Public Key
		'kpri'  => null, // Private Key
		
		'local_port'  => null, //Tunnel
		'remote_host' => '127.0.0.1', //Tunnel
		'remote_port' => null, //Tunnel
	);
	protected $readonly=array(
		'conn'    => null, // Resource: ssh2 connection
		'tunnel'  => null, // Resource: tunnel connection
		'socket'  => null, // Resource: socket connection
	); 
	protected $allParameters=array('host','user','pass','port','kpub','kpri','remote_port','remote_host','local_port');
	
	function init(){
		if(!$this->protect['remote_port'] || !$this->protect['remote_host']) return false;
		$local_port=$this->protect['local_port']?$this->protect['local_port']:getFreeRandomPort();
		if(!$local_port) $this->fatalError('Tunnel local port error');
		$this->proxy($local_port, $this->protect['remote_host'], $this->protect['remote_port']);
	}
	function connect_to($remote_host, $remote_port) {
		if(!$this->connect()) return false;
		if($this->readonly['tunnel']) return true;
		$this->readonly['tunnel']=fopen("ssh2.tunnel://{$this->readonly['conn']}/$remote_host:$remote_port", 'r+');
		if(!$this->readonly['tunnel'] || !is_resource($this->readonly['tunnel'])) return $this->pr("- Failed Initializing Tunnel To $remote_host:$remote_port") && false;
		$this->pr("- Tunnel To $remote_host:$remote_port Initialized");
		stream_set_blocking($this->readonly['tunnel'], false);
		return true;
	}
	function proxy($local_port, $remote_host, $remote_port) {
		if(!$this->connect_to($remote_host, $remote_port)) return false;
		if(!$this->create_local_socket($local_port)) return false;
		$conn=stream_socket_accept($this->readonly['socket'], 5);
		stream_set_blocking($conn, false);
		while(!feof($conn) && !feof($this->readonly['socket'])) {
			// read from server to client
			if(strlen($data=@fread($this->readonly['tunnel'], 4096))) {
				if(fwrite($conn, $data)===false) break;
			} else if(strlen($data=@fread($conn, 4096))) {
				if(fwrite($this->readonly['tunnel'], $data)===false) break;
			} else usleep(5000);
		}
		@fclose( $conn );
		@fclose( $this->readonly['tunnel'] );
		return true;
	}
	function create_local_socket($port) {
		$this->readonly['socket']=@stream_socket_server( "tcp://127.0.0.1:$port", $errno, $errstr );
		if (!$this->readonly['socket']) return $this->pr("- Failed creating socket server:\t$errstr ($errno)") && false;
		return true;
	}
}
