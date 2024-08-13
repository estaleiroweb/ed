<?php

/**
 * Classe root 
 *
 * @author Helbert Fernandes
 * @link http://www.estaleiroweb.com.br
 * @access helbert@estaleiroweb.com.br
 * @version 1.0
 * @package Conn
 */
abstract class Conn_Main extends Conn_Main_Pattern {
	//static protected $instances=array();
	public $startTableDelimiter = '`';
	public $endTableDelimiter = '`';
	public $startFieldDelimiter = '`';
	public $endFieldDelimiter = '`';
	public $strDelimiter = '\'';
	public $showQueryOnError = false;
	public $maxInsert = 1;
	public $on_save = null;
	protected $readOnly;

	public function __construct($splitConn = null) {
		$this->readonly = [
			'dsn' => null,
			'conn' => null,
			'host' => null,
			'user' => null,
			'pass' => null,
			'db' => null,
			'socket' => null,
			'res' => null,
			'parameters' => [],
			'tunnel' => null,
			'sql' => null,
		];
		if (is_null($splitConn)) return;
		$splitConn = $this->tunnel($splitConn);
		$this->rebildConnVars($splitConn);
	}
	public function __destruct() {
		@$this->close();
	}
	public function getHostPort() {
		$port = @$this->readOnly['port'] ? ':' . $this->readOnly['port'] : '';
		return @$this->readOnly['host'] ? $this->readOnly['host'] . $port : ($port ? '127.0.0.1' . $port : null);
	}
	public function checkConnetctionSelectDb() {
		$er = 'Without Connection';
		if (!$this->readOnly['conn'] || ($er = $this->error())) $this->fatalError($er);
		if (@$this->readOnly['db']) $this->select_db($this->readOnly['db']);
	}
	public function rebildConnVars($splitConn) {
		if (!$splitConn['host']) $this->fatalError('Without host');
		$this->readOnly['host'] = is_string($splitConn['host']) ? gethostbyname($splitConn['host']) : $splitConn['host'];
		$this->readOnly['dsn'] = @$splitConn['fragment'];
		$this->readOnly['user'] = @$splitConn['user'];
		$this->readOnly['pass'] = @$splitConn['pass'];
		$this->readOnly['db'] = @$splitConn['db'];
		$this->readOnly['port'] = @$splitConn['port'];
		$this->readOnly['parameters'] = @$splitConn['query'];
		$this->readOnly['socket'] = @$splitConn['query']['socket'];
		$this->readOnly['tunnetPs'] = @$splitConn['tunnetPs'];
		//print_r($this->readOnly);
	}
	public function tunnel($splitConn) {
		if (!@$splitConn['query']['tunnel']) return $splitConn;
		//ssh -fNg -L 3307:127.0.0.1:3306 myuser@remotehost.com
		//ssh -fNg -L <ssh_Port>:127.0.0.1:<db_Port> myuser@remotehost.com

		$out = parse_url($splitConn['query']['tunnel']);
		if (!@$out['user']) $out['user'] = 'root';
		$toPort = (@$splitConn['port']) ? $splitConn['port'] : 3306;
		$splitConn['query']['tunnetRemotePort'] = $toPort;
		$splitConn['query']['tunnetRemoteHost'] = $out['host'];
		$splitConn['host'] = '127.0.0.1';
		$splitConn['port'] = @$splitConn['query']['tunnelLocalPort'];

		//show($this->getUsedTunnelPort($out['host'],$toPort));exit;
		if ($splitConn['port']) {
			$tunnelTime = 0;
			$splitConn['tunnetPs'] = $this->getUsedTunnelPort($out['host'], $toPort, $splitConn['port']);
			if ($splitConn['tunnetPs']) return $splitConn;
		} else {
			$tunnelTime = (int)@$splitConn['query']['tunnelTime'];
			if (!$tunnelTime && ($splitConn['tunnetPs'] = $this->getUsedTunnelPort($out['host'], $toPort))) {
				$splitConn['port'] = $splitConn['tunnetPs'][0]['tunnel']['local_port'];
				return $splitConn;
			}
			($splitConn['port'] = getFreeRandomPort()) || $this->fatalError('Tunnel port auto select error');
		}

		$sshPort = @$out['port'] ? ' -p ' . $out['port'] : '';
		$tunnel = "{$splitConn['port']}:{$splitConn['host']}:{$toPort}";
		$host = " {$out['user']}@{$out['host']}";
		$pass = addcslashes(@$out['pass'], '\'\\');
		$sleep = $tunnelTime ? " sleep $tunnelTime" : ' -N';

		$cmd = "sshpass -p '$pass' ssh -f -L $tunnel$sshPort$host$sleep > /dev/null";
		//print "$cmd\n";
		shell_exec($cmd);
		$splitConn['tunnetPs'] = $this->getUsedTunnelPort($out['host'], $toPort, $splitConn['port']);
		return $splitConn;
		/*//ssh2_connect
		#resource ssh2_connect ( string $host [, int $port = 22 [, array $methods [, array $callbacks ]]] )
		if(!($sshConn=ssh2_connect($out['host'], @$out['port']))) $this->fatalError('SSH tunnel connection error');
		#bool ssh2_auth_password ( resource $session , string $username , string $password )
		if(@$out['pass']) if(!ssh2_auth_password($sshConn, $out['user'], $out['pass'])) $this->fatalError('SSH tunnel authentication error');
		#resource ssh2_tunnel ( resource $session , string $host , int $port )
		$this->readOnly['tunnel']=array('sshRes'=>$sshConn);
		$tunnel=ssh2_tunnel($sshConn, $splitConn['host'], 12345);
		shell_exec(“ssh -f -L 3307:127.0.0.1:3306 user@remote.rjmetrics.com sleep 60 >> logfile”); $db = mysqli_connect(‘127.0.0.1′, ‘sqluser’, ‘sqlpassword’, ‘rjmadmin’, 3307);
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
	public function getUsedTunnelPort($remote_host, $remote_port, $local_port = null) { //$action=(pid|pids|kill_pids|local_port|locals_port)
		$l = '(127\.0\.0\.1|localhost):';
		$rhost = escapeshellarg('@' . $remote_host);
		$lhostRaw = "(?:$l)?" . ($local_port ? "($local_port)" : '([0-9]+)') . ":$l($remote_port)";
		$lhost = escapeshellarg($lhostRaw);
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
		$out = "ps -fC ssh | grep $rhost | egrep -e $lhost";
		/*
		UID        PID  PPID  C STIME TTY          TIME CMD
		root      4911     1  0 08:18 ?        00:00:00 ssh -fN -L 50000:127.0.0.1:3306 cvna@10.221.24.45
		root      4959     1  0 08:19 ?        00:00:00 ssh -fN -L 46169:127.0.0.1:3306 cvna@10.221.24.45
		root      4999     1  0 08:19 ?        00:00:00 ssh -fN -L 16761:127.0.0.1:3306 cvna@10.221.24.45
		*/
		$ps = trim(`$out`);
		//print_r($ps);
		if (!$ps) return false;
		$ps = explode("\n", $ps);
		foreach ($ps as $k => $line) {
			$ps[$k] = array('ps' => $line);
			if (preg_match('/^\s*(\S+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\S+)\s+(\S+)\s+(\S+).*\b(ssh\s.*)/', $line, $ret)) {
				$ps[$k]['UID'] = $ret[1];
				$ps[$k]['PID'] = $ret[2];
				$ps[$k]['PPID'] = $ret[3];
				$ps[$k]['C'] = $ret[4];
				$ps[$k]['STIME'] = $ret[5];
				$ps[$k]['TTY'] = $ret[6];
				$ps[$k]['TIME'] = $ret[7];
				$ps[$k]['CMD'] = $ret[8];
				$ps[$k]['tunnel'] = array();
				if (preg_match("/$lhostRaw/", $ps[$k]['CMD'], $ret)) $ps[$k]['tunnel'] = array('local_port' => $ret[2], 'remote_port' => $ret[4]);
				$cmd = preg_replace(
					array(
						'/^ssh\s*/',
						'/-[1246AaCfgKkMNnqsTtVvXxYy]+/',
						'/-\w\s+\S+\s*/',
						'/-\s+/',
					),
					array(
						'',
						'-',
						'',
						'',
					),
					$ps[$k]['CMD']
				);
				if (preg_match($er = '/\s*(?:([^ @]+)@)?(\S+)\s*/', $cmd, $ret)) {
					$ps[$k]['tunnel']['user'] = $ret[1];
					$ps[$k]['tunnel']['host'] = $ret[2];
					$cmd = preg_replace($er, '', $cmd);
				}

				$ps[$k]['tunnel']['time'] = preg_match("/sleep\s+(\d+)/", $cmd, $ret) ? $ret[1] : log(0);
				$ps[$k]['tunnel']['cmd'] = $cmd;
			}
		}
		return $ps;
	}
	public function __get($nm) {
		if (array_key_exists($nm, $this->readOnly)) return $this->readOnly[$nm];
		if (method_exists($this, $nm)) return $this->$nm();
		return @$this->readOnly['conn']->$nm;
	}
	public function __set($nm, $val) {
	}
	public function __call($nm, $par) {
		return call_user_func_array(array($this->readOnly['conn'], $nm), $par);
	}
	public function __toString() {
		return $this->dsn;
	}
	//public function __sleep(){ return $this->dsn; }
	public function __debugInfo() {
		return array('dsn' => $this->dsn);
	}
	public function set($nm, $val) {
		if (isset($this->readOnly[$nm])) $this->readOnly[$nm] = $val;
	}
	public function close() {
		//print "\ncloseMySQLi $this->strDsn\n";
		$this->readOnly['conn'] = null;
		Conn::close($this->strDsn);
	}
	public function fatalError($error = '') {
		die(error(
				$error,
				4,
				"MySQL ERROR => Host: {$this->host} - User: {$this->user} - DB: {$this->db}\n\n{$this->sql}"
			));
	}
	public function select_db($db) {
		$this->readOnly['db'] = $db;
	}
	public function multi_query($sql) {
		return $this->query($sql);
	}
	public function query($sql, $verifyError = true) {
		$this->sql = $sql;
		//print "$sql\n";
		if (!$this->readOnly['conn']) {
			//print 'r';
			//@$this->readOnly['conn']->close();
			//$this->__construct();
			//print 'R';
			return false;
		}
		$this->select_db($this->readOnly['db']);
		$class = get_class($this) . '_result';
		$this->readOnly['res'] = new $class($this, $sql, $verifyError, $this->readOnly['dsn']);
		if (!$this->readOnly['res']->res) $this->readOnly['res'] = $this->next_result();

		return $this->readOnly['res'];
	}
	public function query_all($sql, $verifyError = true, $cmd = 'fetch_assoc_all') { //fetch_assoc_all | fetch_row_all
		$res = $this->query($sql, $verifyError);
		if ($er = $this->error()) return;
		$out = $res->$cmd();
		$res->close();
		return $out;
	}
	public function fastLine($sql, $verifyError = true, $cmd = 'fetch_assoc') {
		$res = $this->query($sql, $verifyError);
		if (!$res) return false;
		($line = @$res->$cmd()) || ($line = array());
		$res->close();
		return $line;
	}
	public function fastValue($sql, $field = null, $verifyError = true, $cmd = 'fetch_assoc') {
		$line = $this->fastLine($sql, $verifyError, $cmd);
		if (!$line) return null;
		if ($field != '' && array_key_exists($field, $line)) return $line[$field];
		return reset($line);
	}
	public function procedure($procedure) { //($procedure,$parameters=array(), $verifyError=true, $showTables=false) || ($procedure,$parameter1, $parameterN, ...)
		$sql = call_user_func_array([$this, 'buildProcedure'], func_get_args());
		return $this->getAllQuerys($sql, true, true);
	}
	public function call($procedure) {
		$sql = call_user_func_array([$this, 'buildProcedure'], func_get_args());
		return $this->getAllQuerys($sql, true, false);
	}
	public function buildProcedure($procedure) {
		$args = func_get_args();
		array_shift($args);
		$parameters = ($args && is_array($args[0])) ? $args[0] : $args;
		//print_r(array($procedure=>$parameters));
		//print 'CALL '.$procedure.$this->mountValueInsertLine($parameters)."\n";
		return 'CALL ' . $procedure . $this->mountValueInsertLine($parameters);
	}
	public function getAllQuerys($sql, $verifyError = true, $getTables = false) {
		$out = array();
		$cont = 0;
		$res = $this->query($sql, $verifyError);
		while ($res) {
			$cont++;
			verbose("Query $cont runned");
			if ($getTables) $out[] = $res->fetch_assoc_all();
			$res = $this->next_result();
		}
		return $out;
	}
	public function release() {
		while (@$this->next_result());
	}
	public function shiftArrayRes(&$aRes) {
		if (!$aRes) return false;
		if (is_array($aRes)) $res = array_shift($aRes);
		else {
			$res = $aRes;
			$aRes = false;
		}
		return $res instanceof Conn_Main_result ? $res : $this->shiftArrayRes($aRes);
	}
	public function simple_insert($tbl, $lines, $cmd = null, $onUpdate = '') {
		if (!$lines) return 0;
		if ($onUpdate) $onUpdate = ' ON DUPLICATE KEY UPDATE ' . $onUpdate;
		if (!$cmd) $cmd = 'INSERT LOW_PRIORITY IGNORE';

		$this->query(
			$cmd . ' ' . $tbl . ' (' . $this->mountFieldsKeys(reset($lines)) . ") VALUES \n" .
				implode(",\n", array_map(array($this, 'mountValueInsertLine'), $lines)) .
				$onUpdate
		);
		return (int)($x = $this->affected_rows());
	}
	public function insert($tbl, $lines, $cmd = null, $onUpdate = '') {
		if (!$lines) return 0;
		if (is_array(reset($lines))) {
			$cont = 0;
			$blk = array_chunk($lines, $this->maxInsert, true);
			foreach ($blk as $lines) $cont += $this->simple_insert($tbl, $lines, $cmd, $onUpdate);
			return $cont;
		} else return $this->simple_insert($tbl, array($lines), $cmd, $onUpdate);
	}
	public function import($res, $tbl, $cmd = null, $onUpdate = '', $charPrint = '.') {
		while ($line = $res->fetch_assoc()) $this->save($tbl, $line, $cmd, $onUpdate, $charPrint);
		return $this->save($tbl, null, $cmd, $onUpdate, $charPrint);
	}
	/**
	 *  string      $tblTo       Nome da tabela. Se null, salva todas as tabelas
	 *  array       $line        Linhas associativa ou linhas contendo Linhaa associativas que deseja salvar/cache. Se null, salva cache da tabela
	 *  string      $cmd         Comando INSERT [IGNORE] ou RECLACE a ser executado
	 *  string|bool $onUpdate    Se true, monta linha de UPDATE caso Key duplicada ou recebe string do ON DUPLICATE KEY UPDATE
	 *  string      $charPrint   Caracter a ser impresso na tela a cada save
	 *  string      $keysDefault Nome dos campos que recebem os valores. Se Null, monta as Keys automaticamente
	 */
	public function save($tblTo = null, $line = null, $cmd = null, $onUpdate = null, $charPrint = '.', $keysDefault = null) {
		static $sqls = array();
		static $tbls = array();
		static $keys = array();
		static $updt = array();
		static $cmdT = array();
		static $c = array();
		static $cont = array();
		static $sum = array();

		if (!$tblTo) foreach ($keys as $k => $v) if ($v) $this->save($k);
		if ($line && is_array($line)) {
			if (is_array(reset($line))) {
				$sumLines = 0;
				foreach ($line as $l) $sumLines += $this->save($tblTo, $l, $cmd, $onUpdate, $charPrint, $keysDefault);
				return $sumLines;
			}
			$k = @$keys[$tblTo];
			if (!array_key_exists($tblTo, $keys)) {
				$tbls[$tblTo] = preg_replace('/#.*?$/', '', $tblTo);
				$keys[$tblTo] = $keysDefault ? $keysDefault : $this->mountFieldsKeys($line);
				$cmdT[$tblTo] = ($cmd ? $cmd : 'INSERT LOW_PRIORITY') . ($onUpdate ? '' : ' IGNORE');
				$updt[$tblTo] = '';
				if ($onUpdate) {
					if ($onUpdate === true) $onUpdate = $this->mountFieldsUpdateValues($line);
					$updt[$tblTo] = " \nON DUPLICATE KEY UPDATE $onUpdate";
				}
				$c[$tblTo] = $charPrint;
				$sqls[$tblTo] = array();
				$cont[$tblTo] = $sum[$tblTo] = 0;
			}
			$sqls[$tblTo][] = $this->mountValueInsertLine($line);
			$cont[$tblTo]++;
			$sum[$tblTo]++;
		}
		if (!array_key_exists($tblTo, $keys)) return 0;
		if ($cont[$tblTo] >= $this->maxInsert || !$line) {
			if (@$sqls[$tblTo]) {
				if ($c[$tblTo]) print $c[$tblTo];
				$sql = "{$cmdT[$tblTo]} {$tbls[$tblTo]} ({$keys[$tblTo]}) VALUES \n" . implode(",\n", $sqls[$tblTo]) . $updt[$tblTo];
				$this->query($sql);
				if ($this->on_save) call_user_func($this->on_save);
				//print "\n$sql\n";
			}
			$sqls[$tblTo] = array();
			$cont[$tblTo] = 0;
		}
		$out = $sum[$tblTo];
		if (!$line) {
			unset($keys[$tblTo]);
			$sum[$tblTo] = 0;
		}
		return $out;
	}
	public function mountFieldsKeys($line) {
		$l = array();
		foreach ($line as $k => $v) $l[] = is_object($v) ? $this->getName_byObjField($v, $k) : $k;
		return $this->fieldDelimiter(implode($this->endFieldDelimiter . ',' . $this->startFieldDelimiter, $l));
	}
	public function mountFieldsSetValues($line) {
		$out = array();
		foreach ($line as $k => $v) {
			if (is_object($v)) {
				$k = $this->getName_byObjField($v, $k);
				$v = $v();
			} else $v = $this->addQuote($v);
			$out[] = $this->fieldDelimiter($k) . '=' . $v;
		}
		return implode(', ', $out);
	}
	public function mountFieldsUpdateValues($line) {
		$out = array();
		foreach ($line as $k => $v) {
			if (is_object($v)) $k = $this->getName_byObjField($v, $k);
			$fld = $this->fieldDelimiter($k);
			$out[] = $fld . '=VALUES(' . $fld . ')';
		}
		return implode(', ', $out);
	}
	public function getName_byObjField($obj, $default = null) {
		($n = @$obj->name) || ($n = @$obj->orgname) || ($n = @$obj->key) || ($n = $default);
		return $n;
	}
	public function mountFieldsConpareValues($line, $op = ' AND ') {
		foreach ($line as $field => $value) $line[$field] = $this->fieldCompareValue($field, $value);
		return implode($op, $line);
	}
	public function mountValueInsertLine($line) {
		return '(' . implode(',', array_map(array($this, 'addQuote'), $line)) . ')';
	}
	public function addQuote($value) {
		if (is_object($value)) {
			if ($value instanceof Conn_Main_result_field) return $value();
			return $this->stringDelimiter(json_encode($value));
		}
		if (is_string($value) || is_numeric($value)) return $this->stringDelimiter($value);
		if (is_null($value))  return 'NULL';
		if (is_bool($value))  return $value ? 'True' : 'False';
		if (is_array($value)) return $this->stringDelimiter(json_encode($value));
		return 'NULL';
	}
	public function fieldCompareValue($field, $value) {
		if (is_object($value)) {
			$field = $this->getName_byObjField($value, $field);
			if ($value instanceof Conn_Main_result_field) return "{$this->fieldDelimiter($field)}{$value->compare()}";
			else $value = json_encode($value);
		}
		$f = $this->fieldDelimiter($field);
		if (is_null($value)) return $f . ' IS NULL';
		if (is_numeric($value)) return $f . '=' . ($value + 0);
		if (is_bool($value)) return $f;
		elseif (is_array($value)) $value = json_encode($value);
		//if(is_array($value) || is_object($value)) $value=serialize($value);
		return $f . '=' . $this->stringDelimiter($value);
	}
	public function fieldDelimiter($field) {
		return $this->startFieldDelimiter . $field . $this->endFieldDelimiter;
	}
	public function stringDelimiter($value) {
		return $this->strDelimiter . $this->escape_string($value) . $this->strDelimiter;
	}
	public function more_results() {
		return $this->readOnly['conn']->more_results();
	}
	public function store_result() {
		if ($this->readOnly['res']) $this->readOnly['res']->resetFields();
		return $this->readOnly['conn']->store_result();
	}
	public function use_result() {
		return $this->readOnly['conn']->use_result();
	}
	public function next_result() {
		if (!$this->readOnly['conn']->next_result()) return false;
		$this->readOnly['res']->res = $this->store_result();
		return $this->readOnly['res']->res ? $this->readOnly['res'] : $this->next_result();
	}
	public function next() { //TODO deprecieted
		if (!$this->readOnly['conn']->more_results()) return false;
		$out = $this->readOnly['conn']->next_result();
		$this->readOnly['res']->res = $this->store_result();
		return true;
	}
	public function mountWhere($keys, $line = array()) {
		if (!is_array($keys)) return $this->mountWhere(preg_split('/\s*[,;]\s*/', trim($keys)), $line);
		if (is_numeric(key($keys))) {
			$out = array();
			foreach ($keys as $k) if (array_key_exists($k, $line)) $out[$k] = $line[$k];
			$keys = $out;
		}
		return $keys ? $this->mountFieldsConpareValues($keys) : '';
	}
	public function host_info() {
		return $this->get_client_info();
	}
	public function createLike($tblSource, $tblNew) {
		$this->query("DROP TABLE IF EXISTS $tblNew");
		$this->query("CREATE TABLE $tblNew LIKE $tblSource");
	}
	public function rename($tblFrom, $tblTo) {
		$this->query("DROP TABLE IF EXISTS $tblTo");
		$this->query("ALTER TABLE $tblFrom RENAME TO $tblTo");
		//$this->query("REPAIR TABLE $tblTo");
		//$this->query("OPTIMIZE TABLE $tblTo");
	}
	public function details($sql) {
		$class = get_class($this) . '_details';
		return new $class($this, $sql);
	}

	public function showResult($res) {
		if (!$res) {
			print "Query witout result\n";
			return;
		}

		$in = $res->fetch_fields();
		$head = array();
		if ($in) foreach ($in as $k => $obj) {
			($v = $obj->name) || ($v = $obj->orgname);
			$head['length'][$k] = $len = Conn::fit_Lenght($v, $obj->length);
			$head['orgname'][$k] = Conn::fit_Field($v, $len);
			$head['line'][$k] = str_repeat('-', $len);
		}

		$lineSep = Conn::fit_Line(@$head['line']);
		Conn::fit_showHeadLineTop(@$head['orgname']);
		print Conn::fit_Line(@$head['orgname']);
		Conn::fit_showHeadLineBottom(@$head['orgname']);
		//if(Conn::$headLineBottom) print str_replace('-',Conn::$headLineBottom,$lineSep);
		while ($line = $res->fetch_row()) {
			foreach ($line as $k => $v) $line[$k] = Conn::fit_Field($v, @$head['length'][$k]);
			print Conn::fit_Line($line);
		}
		Conn::fit_showFootLine(@$head['orgname']);
		print "\n";
	}
	public function showDatabases() {
	}
	public function showTables($db = null) {
	}
	public function showViews($db = null) {
	}
	public function showFunctions($db = null) {
	}
	public function showProcedures($db = null) {
	}
	public function showEvents($db = null) {
	}
	public function showAllObjects($db = null) {
	}
	public function buildTableName() {
		$argv = func_get_args();
		if (!$argv) return;
		if (is_array($argv[0])) return call_user_func_array(array($this, __FUNCTION__), $argv[0]);
		foreach ($argv as $k => $v) $argv[$k] = $this->startTableDelimiter . $v . $this->endTableDelimiter;
		return implode('.', $argv);
	}
	public function buildFieldName() {
		$argv = func_get_args();
		if (!$argv) return;
		if (is_array($argv[0])) return call_user_func_array(array($this, __FUNCTION__), $argv[0]);
		$field = $this->fieldDelimiter(array_pop($argv));
		$table = $this->buildTableName($argv);
		return ($table ? $table . '.' : '') . $field;
	}
	public function field($index = null) {
		$class = get_class($this) . '_result_field';
		return new $class($index, null, $this);
	}
	public function fn($k, $v, $fn) {
		$f = $this->field();
		$f->raw = true;
		$f->name = $k;
		if (is_array($v)) {
			//foreach($v as $k=>$value) $v[$k]=$this->conn->addQuote($value);
			$v = array_map(array($this, 'addQuote'), $v);
			$v = implode(',', $v);
		} else {
			$f->value = $v;
			$v = $f->quote();
		}
		$f->value = "{$fn}({$v})";
		return $f;
	}
}
class Conn_Main_result extends Conn_Main_Pattern {
	public $res, $dsn;
	protected $conn, $sql, $verifyError;
	protected $fields = array();
	private $oConn;

	public function __construct($conn, $sql, $verifyError = true, $dsn = '') {
		verbose($sql);
		if (!$conn) return false;
		$this->oConn = $conn;
		$this->conn = $conn->conn;
		$this->dsn = $dsn;
		$this->sql = $sql;
		$this->verifyError = $verifyError;
	}
	public function __get($nm) {
		if ($nm == 'sql') return $this->sql;
		if (method_exists($this, $nm)) return $this->$nm();
		//show($this->res); exit;
		return $this->res->$nm;
	}
	public function __set($nm, $val) {
	}
	public function __call($nm, $par) {
		if (!@$this->res) return error($par ? print_r($par, true) : '', 0, "Invalid called function $nm " . $this->conn->error);
		return call_user_func_array(array($this->res, $nm), $par);
	}
	public function verifyError() {
		if ($this->verifyError && $err = $this->error()) $this->fatalError($err);
	}
	public function fatalError($err = '') {
		verbose($this->sql);
		//$err=$this->error();
		if ($this->oConn->showQueryOnError) $err .= "\n" . $this->sql;
		$this->oConn->fatalError($err);
	}
	public function fetch() {
		$fields = $this->fetch_fields();
		$row = $this->fetch_row();
		foreach ($row as $k => $v) $fields[$k]->value = $v;
		return $fields;
	}
	public function fetch_field($index) {
		$class = get_class($this) . '_field';
		return new $class($index, $this->res, $this->conn);
	}
	public function fetch_fields() {
		if (!$this->fields) {
			$tam = $this->num_fields();
			for ($i = 0; $i < $tam; $i++) $this->fields[$i] = $this->fetch_field($i);
		}
		return $this->fields;
	}
	public function fetch_assoc_all() {
		$out = array();
		while ($line = $this->fetch_assoc()) $out[] = $line;
		return $out;
	}
	public function fetch_assoc_coll() {
		$i = 0;
		$out = array();
		while ($line = $this->fetch_assoc()) {
			foreach ($line as $k => $v) $out[$k][$i] = $v;
			$i++;
		}
		return $out;
	}
	public function fetch_row_all() {
		$out = array();
		while ($line = $this->fetch_row()) $out[] = $line;
		return $out;
	}
	public function fetch_row_coll() {
		$i = 0;
		$out = array();
		while ($line = $this->fetch_row()) {
			foreach ($line as $k => $v) $out[$k][$i] = $v;
			$i++;
		}
		return $out;
	}
	public function fetch_fields_coll() {
		$in = $this->fetch_fields();
		$out = array();
		if ($in) foreach ($in as $index => $obj) {
			$arr = $obj->getReadonly();
			foreach ($arr as $k => $v) $out[$k][$index] = $v;
		}
		return $out;
	}
	public function close() {
		$this->res = null;
	}
	public function count() {
		return $this->num_rows();
	}
	public function free() {
		$this->close();
	}
	public function reccount() {
		return $this->num_rows();
	}
	public function field_count() {
		return $this->num_fields();
	}
	public function num_fields() {
		return 0;
	}
	public function current_field() {
		return 0;
	}
	public function num_rows() {
		return 0;
	}
	public function lengths() {
		return null;
	}
	public function error() {
		return '';
	}
	public function errno() {
		return 0;
	}
	public function type() {
		return 0;
	}
	public function resetFields() {
		$this->fields = array();
		return $this;
	}
}
class Conn_Main_result_field {
	public $dataTypes = array();
	public $startFieldDelimiter = '`';
	public $endFieldDelimiter = '`';
	public $strDelimiter = '\'';
	public $raw = false;

	public $index, $name, $orgname, $table, $orgtable, $def, $vartype, $realType;
	public $type, $max_length, $length, $decimals, $charsetnr, $flags;
	public $not_null, $zerofill, $unsigned;
	public $value, $fn;

	public function __construct($index = null, $res = null, $oConn = null) {
		$this->index = $index;
		$this->conn($oConn);
		$this->res($res);
	}
	public function __toString() {
		return $this->value;
	}
	public function __invoke() {
		$v = $this->raw ? $this->value : $this->quote();
		return $this->fn ? call_user_func($this->fn, $v, $this) : $v;
	}
	public function quote() {
		if (is_string($this->value) || is_numeric($this->value)) return $this->stringDelimiter($this->value);
		if (is_null($this->value)) return 'NULL';
		if (is_bool($this->value)) return $this->value ? 'True' : 'False';
		if (is_array($this->value) || is_object($this->value)) return $this->stringDelimiter(json_encode($this->value));
		return 'NULL';
	}
	public function compare() {
		$v = $this->value;
		if (is_array($v) || is_object($v)) $v = json_encode($this->value);
		if (is_string($v)) return '=' . $this->stringDelimiter($v);
		if (is_numeric($v)) return '=' . $v;
		if (is_null($v)) return ' IS NULL';
		if (is_bool($v)) return $v ? '=True' : '=False';
		return 'NULL';
	}
	public function stringDelimiter($value) {
		return $this->strDelimiter . $this->escape($value) . $this->strDelimiter;
	}
	public function escape($texto) {
		return addcslashes($texto, "\x00..\x1F\x7E..\xFF'\"\\");
	}
	public function conn($value = null) {
		static $out = null;
		if ($value) $out = $value;
		return $out;
	}
	public function res($value = null) {
		static $out = null;
		if ($value) $out = $value;
		return $out;
	}

	public function get_dataTypeGroup() {
		static $tr = array();
		if (!$tr) foreach ($this->dataTypes as $grp => $tps) foreach ($tps as $tp => $ln) $tr[$tp] = $grp;
		return $tr[$this->vartype];
	}
	public function convert($obj) {
		$grp = $this->get_dataTypeGroup();
		$fromDataType = @$this->dataTypes[$grp][$this->vartype];
		($length = @$this->length) || ($length = @$fromDataType['length']);
		if ($grp == 'others' || !$fromDataType) {
			$types = $obj->dataTypes['text'];
			end($types);
			$type = key($types);
		} else {
			$types = @$obj->dataTypes[$grp];
			if (count($types) == 1) $type = key($types);
			elseif (array_key_exists($this->vartype, $types) && !@$types[$this->vartype]['length']) {
				$type = $this->vartype;
			} else while ($types) {
				$type = key($types);
				$ln = array_shift($types);
				if (@$ln['length'] && $ln['length'] >= $length) break;
			}
		}
		$precision = @$this->decimals + 0;
		if ($grp == 'datetime') $tam = '';
		elseif ($grp == 'dec') $tam = "({$length},{$precision})";
		elseif ($grp == 'float') $tam = "({$length},{$precision})";
		else $tam = "({$length})";
		$attrs = '';
		if ($this->unsigned) $attrs .= ' UNSIGNED';
		if ($this->not_null) $attrs .= ' NOT NULL';
		if ($this->zerofill) $attrs .= ' ZEROFILL';
		//AUTO_INCREMENT
		return $obj->startFieldDelimiter . $this->name . $obj->endFieldDelimiter . ' ' . $type . $tam . $attrs;
	}
	public function convert_toMySQL() {
		static $obj;
		if (!$obj) $obj = new Conn_mysqli_result_field;
		return $this->convert($obj);
	}
	public function convert_toOracle() {
		static $obj;
		if (!$obj) $obj = new Conn_oracle_result_field;
		return $this->convert($obj);
	}
	public function convert_toSQLServer() {
		static $obj;
		if (!$obj) $obj = new Conn_mssql_result_field;
		return $this->convert($obj);
	}
}
