<?php
namespace DB;
/**
 * Class Main 
 *
 * @author Helbert Fernandes
 * @link http://www.estaleiroweb.com.br
 * @access helbert@estaleiroweb.com.br
 * @version 1.0
 * @package Conn
 */
abstract class Link extends Pattern{
	use \Show;
	
	//protected static $instances=array();
	protected $readOnly=['dsn'=>null,'conn'=>null,'host'=>null,'user'=>null,'db'=>null,'socket'=>null,'res'=>null,'parameters'=>array(),'tunnel'=>null,];
	public $startTableDelimiter='`';
	public $endTableDelimiter='`';
	public $startFieldDelimiter='`';
	public $endFieldDelimiter='`';
	public $strDelimiter='\'';
	public $maxInsert=1;

	public function __construct($splitConn){
		$splitConn=$this->tunnel($splitConn);
		$this->rebildConnVars($splitConn);
	}
	public function __destruct(){ @$this->close(); }
	public function __get($nm){
		if(isset($this->readOnly[$nm])) return $this->readOnly[$nm];
		if(method_exists($this,$nm)) return $this->$nm();
		return @$this->readOnly['conn']->$nm;
	}
	public function __call($nm,$par){ return call_user_func_array(array($this->readOnly['conn'],$nm),$par); }
	public function __tostring(){ return $this->readOnly['dsn']; }
	public function close(){
		$this->readOnly['conn']=null;
		Conn::close($this->strDsn);
	}
	public function getHostPort(){
		$port=@$this->readOnly['port']?':'.$this->readOnly['port']:'';
		return @$this->readOnly['host']?$this->readOnly['host'].$port:($port?'127.0.0.1'.$port:null);
	}
	public function checkConnetctionSelectDb(){
		$er='Without Connection';
		if (!$this->readOnly['conn'] || ($er=$this->error())) $this->fatalError($er);
		if (@$this->readOnly['db']) $this->select_db($this->readOnly['db']);
	}
	public function rebildConnVars($splitConn){
		if(!$splitConn['host']) $this->fatalError('Without host');
		$this->readOnly['host']=is_string($splitConn['host'])?gethostbyname($splitConn['host']):$splitConn['host'];
		$this->readOnly['dsn']=@$splitConn['fragment'];
		$this->readOnly['user']=@$splitConn['user'];
		$this->readOnly['db']=@$splitConn['db'];
		$this->readOnly['port']=@$splitConn['port'];
		$this->readOnly['parameters']=@$splitConn['query'];
		$this->readOnly['socket']=@$splitConn['query']['socket'];
		$this->readOnly['tunnetPs']=@$splitConn['tunnetPs'];
		//print_r($this->readOnly);
	}
	public function tunnel($splitConn){
		if(!@$splitConn['query']['tunnel']) return $splitConn;
		//ssh -fNg -L 3307:127.0.0.1:3306 myuser@remotehost.com
		//ssh -fNg -L <ssh_Port>:127.0.0.1:<db_Port> myuser@remotehost.com
		
		$out=parse_url($splitConn['query']['tunnel']);
		if(!@$out['user']) $out['user']='root';
		$toPort=(@$splitConn['port'])?$splitConn['port']:3306;
		$splitConn['query']['tunnetRemotePort']=$toPort;
		$splitConn['query']['tunnetRemoteHost']=$out['host'];
		$splitConn['host']='127.0.0.1';
		$splitConn['port']=@$splitConn['query']['tunnelLocalPort'];
		
		//show($this->getUsedTunnelPort($out['host'],$toPort));exit;
		if($splitConn['port']) {
			$tunnelTime=0;
			$splitConn['tunnetPs']=$this->getUsedTunnelPort($out['host'],$toPort,$splitConn['port']);
			if($splitConn['tunnetPs']) return $splitConn;
		}
		else {
			$tunnelTime=(int)@$splitConn['query']['tunnelTime'];
			if(!$tunnelTime && ($splitConn['tunnetPs']=$this->getUsedTunnelPort($out['host'],$toPort))) {
				$splitConn['port']=$splitConn['tunnetPs'][0]['tunnel']['local_port'];
				return $splitConn;
			}
			($splitConn['port']=getFreeRandomPort()) || $this->fatalError('Tunnel port auto select error');
		}

		$sshPort=@$out['port']?' -p '.$out['port']:'';
		$tunnel="{$splitConn['port']}:{$splitConn['host']}:{$toPort}";
		$host=" {$out['user']}@{$out['host']}";
		$pass=addcslashes(@$out['pass'],'\'\\');
		$sleep=$tunnelTime?" sleep $tunnelTime":' -N';
		
		$cmd="sshpass -p '$pass' ssh -f -L $tunnel$sshPort$host$sleep > /dev/null";
		//print "$cmd\n";
		shell_exec($cmd);
		$splitConn['tunnetPs']=$this->getUsedTunnelPort($out['host'],$toPort,$splitConn['port']);
		return $splitConn;
		/*//ssh2_connect
		#resource ssh2_connect ( string $host [, int $port = 22 [, array $methods [, array $callbacks ]]] )
		if(!($sshConn=ssh2_connect($out['host'], @$out['port']))) $this->fatalError('SSH tunnel connection error');
		#bool ssh2_auth_password ( resource $session , string $username , string $password )
		if(@$out['pass']) if(!ssh2_auth_password($sshConn, $out['user'], $out['pass'])) $this->fatalError('SSH tunnel authentication error');
		#resource ssh2_tunnel ( resource $session , string $host , int $port )
		$this->readOnly['tunnel']=array('sshRes'=>$sshConn);
		$tunnel=ssh2_tunnel($sshConn, $splitConn['host'], 12345);
		shell_exec(“ssh -f -L 3307:127.0.0.1:3306 user@remote.rjmetrics.com sleep 60 >> logfile”); $db = mysqli_connect(‘127.0.0.1', ‘sqluser’, ‘sqlpassword’, ‘rjmadmin’, 3307);
		*/
		/*//tunnelLocalPort
		$ssh=init('VpSSH',array(
			'host' => $out['host'],
			'user' => @$out['user'],
			'pass' => @$out['pass'],
			//'local_port'  => @$splitConn['query']['tunnelLocalPort'], //Tunnel
			//'remote_host' => $splitConn['host'], //Tunnel
			//'remote_port' => $toPort, //Tunnel
		));
		$ssh->tunnel($toPort,"127.0.0.1:$fromPort");
		$splitConn['port']=$fromPort;
		$splitConn['host']='127.0.0.1';
				
		print $ssh->exec('ls -l');
		print $ssh->which('install.log')."\n";
		#ssh2_auth_pubkey_file($connection, 'username', 'id_dsa.pub', 'id_dsa');
		exit;

		*/
	}
	public function getUsedTunnelPort($remote_host,$remote_port,$local_port=null){ //$action=(pid|pids|kill_pids|local_port|locals_port)
		$l='(127\.0\.0\.1|localhost):';
		$rhost=escapeshellarg('@'.$remote_host);
		$lhostRaw="(?:$l)?".($local_port?"($local_port)":'([0-9]+)').":$l($remote_port)";
		$lhost=escapeshellarg($lhostRaw);
		/*
		$lst_pids=' | awk \'{print $2}\'';
		$lst_rport=" | sed -r 's/\\s+/\\n/g' | sed -rn '/$l/p' | sed -r 's/:$l.+//;s/$l//'";
		
		if($action=='pid') $cmd=' | head -1'.$lst_pids;
		elseif($action=='pids') $cmd=$lst_pids;
		elseif($action=='kill_pids') $cmd=$lst_pids.' | xargs kill -9';
		elseif(!$action || $action=='local_port') $cmd=' | head -1'.$lst_rport;
		elseif($action=='locals_port') $cmd=$lst_rport;
		elseif($action=='cmd') $cmd=$lst_rport;
		else $cmd='';
		*/
		
		#$out="ps -fC ssh | grep $rhost | egrep $lhost$cmd";
		$out="ps -fC ssh | grep $rhost | egrep -e $lhost";
		/*
		UID        PID  PPID  C STIME TTY          TIME CMD
		root      4911     1  0 08:18 ?        00:00:00 ssh -fN -L 50000:127.0.0.1:3306 cvna@10.221.24.45
		root      4959     1  0 08:19 ?        00:00:00 ssh -fN -L 46169:127.0.0.1:3306 cvna@10.221.24.45
		root      4999     1  0 08:19 ?        00:00:00 ssh -fN -L 16761:127.0.0.1:3306 cvna@10.221.24.45
		*/
		$ps=trim(`$out`);
		//print_r($ps);
		if(!$ps) return false;
		$ps=explode("\n",$ps);
		foreach($ps as $k=>$line) {
			$ps[$k]=array('ps'=>$line);
			if(preg_match('/^\s*(\S+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(\S+)\s+(\S+).*\b(ssh\s.*)/',$line,$ret)) {
				$ps[$k]['UID']=$ret[1];
				$ps[$k]['PID']=$ret[2];
				$ps[$k]['PPID']=$ret[3];
				$ps[$k]['C']=$ret[4];
				$ps[$k]['STIME']=$ret[5];
				$ps[$k]['TTY']=$ret[6];
				$ps[$k]['TIME']=$ret[7];
				$ps[$k]['CMD']=$ret[8];
				$ps[$k]['tunnel']=array();
				if(preg_match("/$lhostRaw/",$ps[$k]['CMD'],$ret)) $ps[$k]['tunnel']=array('local_port'=>$ret[2],'remote_port'=>$ret[4]);
				$cmd=preg_replace(
					array(
						'/^ssh\s*/',
						'/-[1246AaCfgKkMNnqsTtVvXxYy]+/',
						'/-\w\s+\S+\s*/',
						'/-\s+/',
					),array(
						'',
						'-',
						'',
						'',
					),
					$ps[$k]['CMD']
				);
				if(preg_match($er='/\s*(?:([^ @]+)@)?(\S+)\s*/',$cmd,$ret)) {
					$ps[$k]['tunnel']['user']=$ret[1];
					$ps[$k]['tunnel']['host']=$ret[2];
					$cmd=preg_replace($er,'',$cmd);
				}
				
				$ps[$k]['tunnel']['time']=preg_match("/sleep\s+(\d+)/",$cmd,$ret)?$ret[1]:log(0);
				$ps[$k]['tunnel']['cmd']=$cmd;
			}
		}
		return $ps;
	}
	public function set($nm,$val){ if(isset($this->readOnly[$nm])) $this->readOnly[$nm]=$val; }
	public function fatalError($error=''){ die(error($error,4,"MySQL ERROR => Host: {$this->host} - User: {$this->user} - DB: {$this->db}")); }
	public function select_db($db){ $this->readOnly['db']=$db; }
	public function multi_query($sql){ return $this->query($sql); }
	public function query($sql,$verifyError=true){
		//print "$sql\n";
		if(!$this->readOnly['conn']) return false;
		$this->select_db($this->readOnly['db']);
		$class='\\'.preg_replace('/\w+$/','Res',get_class($this));
		return $this->readOnly['res']=new $class($this,$sql,$verifyError,$this->readOnly['dsn']);
	}
	public function query_all($sql,$verifyError=true,$cmd='fetch_assoc_all') { //fetch_assoc_all | fetch_row_all
		$res=$this->query($sql,$verifyError);
		$out=$res->$cmd();
		$res->close();
		return $out;
	}
	public function fastLine($sql,$verifyError=true,$cmd='fetch_assoc'){
		$res=$this->query($sql,$verifyError);
		($line=$res->$cmd()) || ($line=array());
		$res->close();
		return $line;
	}
	public function simple_insert($tbl,$lines,$cmd=null,$onUpdate=''){
		if(!$lines) return 0;
		if($onUpdate) $onUpdate=' ON DUPLICATE KEY UPDATE '.$onUpdate;
		if(!$cmd) $cmd='INSERT IGNORE';

		$this->query(
			$cmd.' '.$tbl.' ('.$this->mountFieldsKeys(reset($lines)).") VALUES \n".
			implode(",\n",array_map(array($this,'mountValueInsertLine'),$lines)).
			$onUpdate
		);
		return (int)($x=$this->affected_rows());
	}
	public function insert($tbl,$lines,$cmd=null,$onUpdate=''){
		if(!$lines) return 0;
		$cont=0;
		if(is_array(reset($lines))) {
			$fn=__FUNCTION__;
			$blk=array_chunk($lines,$this->maxInsert,true);
			foreach($blk as $lines) $cont+=$this->$fn($tbl,$lines,$cmd,$onUpdate);
			return $cont;
		}
		else return $this->simple_insert($tbl,$lines,$cmd,$onUpdate);
	}
	public function import($res,$tbl,$cmd=null,$onUpdate='',$charPrint='.'){
		while($line=$res->fetch_assoc()) $this->save($tbl,$line,$cmd,$onUpdate,$charPrint);
		return $this->save($tbl,null,$cmd,$onUpdate,$charPrint);
	}
	
	public function save($tblTo,$line=null,$cmd=null,$onUpdate=null,$charPrint='.',$keysDefault=null){
		static $sqls=array();
		static $keys=array();
		static $updt=array();
		static $cmdT=array();
		static $c=array();
		static $cont=array();
		static $sum=array();
		
		if($line) {
			if(!@$keys[$tblTo]) {
				$keys[$tblTo]=$keysDefault?$keysDefault:$this->mountFieldsKeys($line);
				$cmdT[$tblTo]=$cmd?$cmd:'INSERT'.($onUpdate?'':' IGNORE');
				$updt[$tblTo]=$onUpdate?" \nON DUPLICATE KEY UPDATE $onUpdate":'';
				$c[$tblTo]=$charPrint;
				$sqls[$tblTo]=array();
				$cont[$tblTo]=$sum[$tblTo]=0;
			}
			$sqls[$tblTo][]=$this->mountValueInsertLine($line);
			$cont[$tblTo]++;
			$sum[$tblTo]++;
		}
		if(!@$keys[$tblTo]) return 0;
		if($cont[$tblTo]>=$this->maxInsert || (!$line && @$sqls[$tblTo])){
			if($c[$tblTo]) print $c[$tblTo];
			$sql="{$cmdT[$tblTo]} $tblTo ({$keys[$tblTo]}) VALUES \n".implode(",\n",$sqls[$tblTo]).$updt[$tblTo];
			$this->verbose($sql);
			$this->query($sql);
			//print_r("\n$sql\n");
			$sqls[$tblTo]=array();
			$cont[$tblTo]=0;
		}
		$out=$sum[$tblTo];
		if(!$line) {
			unset($keys[$tblTo]);
			$sum[$tblTo]=0;
		}
		return $out;
	}
	public function mountFieldsKeys($line){
		return $this->startFieldDelimiter.implode($this->endFieldDelimiter.','.$this->startFieldDelimiter,array_keys($line)).$this->endFieldDelimiter;
	}
	public function mountFieldsSetValues($line){
		foreach($line as $field=>$value) $line[$field]=$this->startFieldDelimiter.$field.$this->endFieldDelimiter.'='.$this->addQuote($value);
		return implode(', ',$line);
	}
	public function mountFieldsConpareValues($line,$op=' AND '){
		foreach($line as $field=>$value) $line[$field]=$this->fieldCompareValue($field,$value);
		return implode($op,$line);
	}
	public function mountValueInsertLine($line){ return '('.implode(',',array_map(array($this,'addQuote'),$line)).')'; }
	public function addQuote($value){
		if(is_null($value)) return 'NULL';
		if(is_bool($value)) return $value?'True':'False';
		if(is_numeric($value)) return $value;
		if(is_string($value)) return $this->strDelimiter.$this->escape_string($value).$this->strDelimiter;
		if(is_object($value)) $value=(array)$value;
		if(is_array($value)) {
			foreach($value as &$v) $v=$this->addQuote($v);
			return '('.implode(', ',$value).')';
		}
		return 'NULL';
	}
	public function fieldCompareValue($field,$value){
		if(is_null($value)) return $this->startFieldDelimiter.$field.$this->endFieldDelimiter.' IS NULL';
		if(is_bool($value)) return $this->startFieldDelimiter.$field.$this->endFieldDelimiter;
		if(is_numeric($value)) return $this->startFieldDelimiter.$field.$this->endFieldDelimiter.'='.$value;
		if(is_array($value) || is_object($value)) $value=serialize($value);
		return $this->startFieldDelimiter.$field.$this->endFieldDelimiter.'='.$this->strDelimiter.$this->escape_string($value).$this->strDelimiter;
	}
	public function next(){
		if(!$this->readOnly['conn']->more_results()) return false;
		$out=$this->readOnly['conn']->next_result();
		$this->readOnly['res']->res=$this->readOnly['conn']->store_result();
		return true;
	}
	public function escape_string($texto){
		return addcslashes($texto, "\x00..\x1F\x7E..\xFF'\"\\");
	}
	public function mountWhere($keys,$line=array()){
		if(!is_array($keys)) return $this->mountWhere(preg_split('/\s*[,;]\s*/',trim($keys)),$line);
		if(is_numeric(key($keys))) {
			$out=array();
			foreach($keys as $k) if(array_key_exists($k,$line)) $out[$k]=$line[$k];
			$keys=$out;
		}
		return $keys?$this->mountFieldsConpareValues($keys):'';
	}
	public function host_info(){ return $this->get_client_info(); }
}
