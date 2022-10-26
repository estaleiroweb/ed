<?php

namespace EstaleiroWeb\ED\Db\Conn;

use EstaleiroWeb\ED\IO\_;
use EstaleiroWeb\ED\Db\Detail\Detail;
use EstaleiroWeb\ED\Db\Field\Field;
use EstaleiroWeb\ED\Db\GetterAndSetter;
use EstaleiroWeb\ED\Db\Raw;
use PDO;
use Exception;
use Iterator;

abstract class ConnMain {
	use GetterAndSetter;

	protected $res;
	public $delimiters = [
		'tableStart' => '',
		'tableEnd' => '',
		'fieldStart' => '',
		'fieldEnd' => '',
		'string' => '\'',
	];

	public $charPrint = null;
	public $cmdSave = null; // INSERT LOW_PRIORITY IGNORE, REPLACE
	public $stopOnError = true;
	public $showErrorOnError = true;
	public $showQueryOnError = false;
	public $showQuery = false;
	public $maxInsert = 1;
	public $on_save = null;

	public function __construct($dsn = '', $username = null, $password = null, $options = null, $name = null) {
		if (!preg_match('/^(\w+)\s*:\s*(.+?)\s*$/', $dsn, $ret)) {
			_::error("DSN format $dsn wrong", FATAL_ERROR);
		}
		$protocol = @Conn::$drivers[strtolower($ret[1])];
		if (!$protocol) {
			_::error("Protocol $protocol not exists", FATAL_ERROR);
		}

		$dsn = $protocol . ':' . $ret[2];
		if (is_null($name)) $name = $dsn . ($username ? ";user=$username" : '');

		$class = get_class($this);
		if (!preg_match(Conn::$erClass, $class, $rCl)) $rCl = ['', '', '', '',];
		$this->readonly = [
			'connClass' => $class,
			'resClass' => $rCl[1] . 'Res\\Res_' . $rCl[4],
			'fldClass' => $rCl[1] . 'Field\\Field_' . $rCl[4],
			'detClass' => $rCl[1] . 'Detail\\Detail_' . $rCl[4],
			//'resClass' => preg_replace(Conn::$erClass, '\1Res\\\Res_\\4', $class),
			//'fldClass' => preg_replace(Conn::$erClass, '\1Field\\\Field_\\4', $class),
			'name' => $name,
			'dsn' => $dsn,
			'protocol' => $protocol,
			'user' => $username,
			'passwd' => $password,
			'options' => $options,
			'parts' => [],
		];
		$parts = preg_split('/\s*;\s*/', $ret[2]);
		foreach ($parts as $item) {
			if (preg_match('/^\s*([^_][^ =]+)\s*=\s*(.*?)\s*$/', $item, $par)) {
				$this->readonly['parts'][strtolower($par[1])] = @$par[2];
			}
		}
		//_::show($this->readonly);exit;
		$this->open();
	}
	public function __destruct() {
		$this->close();
	}
	public function __toString() {
		return $this->readonly[$k = 'name'] == '' ? '[default]' : $this->readonly[$k];
	}
	public function __invoke() {
		return $this->extends;
	}

	public function getAttrs() {
		return [
			PDO::ATTR_AUTOCOMMIT => 'AUTOCOMMIT',
			PDO::ATTR_PREFETCH => 'PREFETCH',
			PDO::ATTR_TIMEOUT => 'TIMEOUT',
			PDO::ATTR_ERRMODE => 'ERRMODE',
			PDO::ATTR_SERVER_VERSION => 'SERVER_VERSION',
			PDO::ATTR_CLIENT_VERSION => 'CLIENT_VERSION',
			PDO::ATTR_SERVER_INFO => 'SERVER_INFO',
			PDO::ATTR_CONNECTION_STATUS => 'CONNECTION_STATUS',
			PDO::ATTR_CURSOR => 'CURSOR',
		];
	}

	public function open() {
		try {
			$this->extends = new PDO(
				$this->readonly['dsn'],
				$this->readonly['user'],
				$this->readonly['passwd'],
				$this->readonly['options']
			);
		} catch (Exception $e) {
			print "Exception DSN {$this->readonly['dsn']}: " .  $e->getMessage() . "\n";
		}
		return $this;
	}
	public function close() {
		$this->extends = null;
		return $this;
	}
	public function ping() {
		return $this->close()->open();
	}
	public function select_db($db) {
		$this->readonly['parts']['dbname'] = $db;
		$this->query('USE ' . $db);
		return $this;
	}
	public function change_user($user = 'root', $passwd = null, $db = null, $options = null) {
		$this->readonly['user'] = $user;
		$this->readonly['passwd'] = $passwd;
		$this->readonly['options'] = $options;
		$out = $this->ping();
		if ($db != '') $this->select_db($db);
		return $out;
	}
	public function info() {
		$arr = [
			'AUTOCOMMIT' => null, 'ERRMODE' => null, 'CASE' => null, 'CLIENT_VERSION' => null, 'CONNECTION_STATUS' => null,
			'ORACLE_NULLS' => null, 'PERSISTENT' => null, 'PREFETCH' => null, 'SERVER_INFO' => null, 'SERVER_VERSION' => null,
			'TIMEOUT' => null,
		];
		foreach ($arr as $k => $v) $arr[$k] = $this->extends->getAttribute(constant('PDO::ATTR_' . $k));
		return $arr;
	}
	public function get_server_info() {
		return $this->info();
	}

	private function detail($query) {
		if ($this->showQuery) _::show($query);
		else _::verbose($query);

		$class = $this->detClass;
		return new $class($this, $query);
	}
	public function exec($query) {
		if ($this->showQuery) _::show($query);
		else _::verbose($query);
		return call_user_func_array([$this->extends, __FUNCTION__], func_get_args());
	}
	private function queryMethod($fn, $args, $mode = null) {
		$query = $args[0];
		if ($this->showQuery) _::show($query);
		else _::verbose($query);
		$res = call_user_func_array([$this->extends, $fn], $args);
		if (!$res) {
			if (!$this->showQuery && $this->showQueryOnError) _::show($query);
			if ($this->showErrorOnError) _::error($this->extends->errorInfo());
			if ($this->stopOnError) exit;
		}
		$class = $this->resClass;
		return $this->res = new $class($this, $res, $query, $mode);
	}
	public function prepare($query, $options = []) {
		return $this->queryMethod(__FUNCTION__, func_get_args(), PDO::FETCH_ASSOC);
	}
	public function query($query, $mode = PDO::FETCH_ASSOC) {
		if (is_array($query) || (is_object($query) && $query instanceof Detail)) {
			$out = [];
			$args = func_get_args();
			foreach ($query as $q) {
				$args[0] = $q;
				$out[] = call_user_func_array([$this, __FUNCTION__], $args);
			}
			return $out;
		}
		$res = $this->queryMethod(__FUNCTION__, func_get_args(), $mode);
		$this->use_result($res);
		return $res;
	}
	public function query_all($query, $mode = PDO::FETCH_ASSOC) {
		$res = $this->query($query);
		$lines = $res->fetch_all($mode);
		$res->close();
		return $lines;
	}
	public function getAllQuerys($query, $getTables = false) {
		$out = [];
		$cont = 0;
		$res = $this->query($query);
		do {
			_::verbose("Query $cont runned");
			if ($getTables && $res->columnCount()) {
				$out[$cont] = $res->fetch_all(PDO::FETCH_ASSOC);
			}
			$cont++;
		} while ($res->nextRowset());
		$res->close();
		/*
			do {
				//if ($res->columnCount()) continue;
				while ($line = $res->fetch(PDO::FETCH_ASSOC)) {
					if (array_key_exists('Dt', $line) && array_key_exists('Descr', $line)) {
						print "{$line['Dt']}: {$line['Descr']}\n";
					} else print_r($line);
				}
			} while (@$res->nextRowset());
		*/
		return $out;
	}
	public function fastLine($query, $mode = PDO::FETCH_ASSOC) {
		$res = $this->query($query);
		($line = @$res->fetch($mode)) || ($line = []);
		$res->close();
		return $line;
	}
	public function fastValue($query, $field = null) {
		$res = $this->query($query);
		$val = @$res->fetchColumn();
		$res->close();
		return $val;
		//if (!($line = @$res->fetch(PDO::FETCH_NUM))) return;
		//if (array_key_exists("$field", $line)) return $line[$field];
		//return reset($line);
	}
	public function call($procedure) {
		$args = func_get_args();
		array_shift($args);
		return $this->call_array($procedure, $args);
	}
	public function call_array($procedure, $args = []) {
		$query = 'CALL ' . $procedure;
		if ($args) {
			$args = is_array($args[0]) ? $args[0] : $args;
			$query .= $this->mountValueInsertLine($args);
		}
		return $this->getAllQuerys($query);
	}
	public function save($tblTo = null, $line = null, $cmd = null, $onUpdate = null, $charPrint = '.', $keysDefault = null) {
		$this->cmdSave = $cmd;
		$this->charPrint = $charPrint;
		return $this->rec($tblTo, $line, $onUpdate, $keysDefault);
	}
	/**
	 *  @param string $tblTo Nome da tabela. Se null, salva todas as tabelas
	 *  @param array $line Linhas associativa ou linhas contendo Linhaa associativas que deseja salvar/cache. Se null, salva cache da tabela
	 *  @param string|bool $onUpdate Se true, monta linha de UPDATE caso Key duplicada ou recebe string do ON DUPLICATE KEY UPDATE
	 *  @param string $keysDefault Nome dos campos que recebem os valores. Se Null, monta as Keys automaticamente
	 *  @return int Quantidade de registros
	 */
	public function rec($tblTo = null, $line = null, $onUpdate = null, $keysDefault = null) {
		static $sqls = [];
		static $tbls = [];
		static $keys = [];
		static $updt = [];
		static $cmdT = [];
		static $c = [];
		static $cont = [];
		static $sum = [];

		if (!$tblTo) foreach ($keys as $k => $v) if ($v) $this->save($k);
		if ($line && is_array($line)) {
			if (is_array(reset($line))) {
				$sumLines = 0;
				foreach ($line as $l) $sumLines += $this->rec($tblTo, $l, $onUpdate, $keysDefault);
				return $sumLines;
			}
			$k = @$keys[$tblTo];
			if (!array_key_exists($tblTo, $keys)) {
				$tbls[$tblTo] = preg_replace('/#.*?$/', '', $tblTo);
				$keys[$tblTo] = $keysDefault ? $keysDefault : $this->mountFieldsKeys($line);
				$cmdT[$tblTo] = ($this->cmdSave ? $this->cmdSave : 'INSERT LOW_PRIORITY') . ($onUpdate ? '' : ' IGNORE');
				$updt[$tblTo] = '';
				if ($onUpdate) {
					if ($onUpdate === true) $onUpdate = $this->mountFieldsUpdateValues($line);
					$updt[$tblTo] = " \nON DUPLICATE KEY UPDATE $onUpdate";
				}
				$c[$tblTo] = is_null($this->charPrint) ? Conn::$chrPrint : $this->charPrint;
				$sqls[$tblTo] = [];
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
				$this->exec($sql);
				$er = $this->errorInfo();
				if (@$er[1]) {
					$er['sql'] = $sql;
					print_r($er);
				}
				if ($this->on_save) call_user_func($this->on_save, $this);
				//print "\n$sql\n";
			}
			$sqls[$tblTo] = [];
			$cont[$tblTo] = 0;
		}
		$out = $sum[$tblTo];
		if (!$line) {
			unset($keys[$tblTo]);
			$sum[$tblTo] = 0;
		}
		return $out;
	}

	public function release($res = null) {
		if (is_null($res)) $res = $this->extends;
		while (@$res->nextRowset());
	}
	public function clearResults($res = null) {
		return $this->release($res);
	}
	public function next($res = null) {
		if (is_null($res)) $res = $this->extends;
		$hasCol = $next = false;
		if (@$res->nextRowset()) return $this->use_result($res);;
		return $next && $hasCol;
	}
	public function next_result($res = null) {
		return next($res);
	}
	public function use_result($res = null) {
		if (is_null($res)) $res = $this->extends;
		$hasCol = false;
		while (!($hasCol = $res->columnCount()) && @$res->nextRowset());
		return $hasCol;
	}

	public function mountValueInsertLine($line) {
		return '(' . implode(',', array_map([$this, 'addQuote'], $line)) . ')';
	}
	public function mountFieldsKeys($line) {
		$l = [];
		foreach ($line as $k => $v) $l[] = is_object($v) ? $this->nameByObjField($v, $k) : $k;
		return $this->fieldDelimiter(implode($this->delimiters['fieldEnd'] . ',' . $this->delimiters['fieldStart'], $l));
	}
	public function mountFieldsSetValues($line) {
		$out = [];
		foreach ($line as $k => $v) {
			if (is_object($v)) {
				$k = $this->nameByObjField($v, $k);
				$v = $v();
			} else $v = $this->addQuote($v);
			$out[] = $this->fieldDelimiter($k) . '=' . $v;
		}
		return implode(', ', $out);
	}
	public function mountFieldsUpdateValues($line) {
		$out = [];
		foreach ($line as $k => $v) {
			if (is_object($v)) $k = $this->nameByObjField($v, $k);
			$fld = $this->fieldDelimiter($k);
			$out[] = $fld . '=VALUES(' . $fld . ')';
		}
		return implode(', ', $out);
	}
	public function mountFieldsConpareValues($line, $op = ' AND ') {
		foreach ($line as $field => $value) $line[$field] = $this->mountFieldsConpareValuesItem($field, $value);
		return implode($op, $line);
	}
	public function mountFieldsConpareValuesItem($field, $value) {
		if (is_object($value)) {
			$field = $this->nameByObjField($value, $field);
			if ($value instanceof Field) {
				$v = call_user_func([$value, 'compare']);
				return "{$this->fieldDelimiter($field)}{$v}";
			} else $value = json_encode($value);
		}
		$f = $this->fieldDelimiter($field);
		if (is_null($value)) return $f . ' IS NULL';
		if (is_numeric($value)) return $f . '=' . ($value + 0);
		if (is_bool($value)) return $f;
		elseif (is_array($value)) $value = json_encode($value);
		//if(is_array($value) || is_object($value)) $value=serialize($value);
		return $f . '=' . $this->stringDelimiter($value);
	}
	public function mountWhere($keys, $line = []) {
		if (!is_array($keys)) return $this->mountWhere(preg_split('/\s*[,;]\s*/', trim($keys)), $line);
		if (is_numeric(key($keys))) {
			$out = [];
			foreach ($keys as $k) if (array_key_exists($k, $line)) $out[$k] = $line[$k];
			$keys = $out;
		}
		return $keys ? $this->mountFieldsConpareValues($keys) : '';
	}
	public function fieldDelimiter($field) {
		return $this->delimiters['fieldStart'] . $field . $this->delimiters['fieldEnd'];
	}
	public function nameByObjField($obj, $default = null) {
		($n = @$obj->name) || ($n = @$obj->orgname) || ($n = @$obj->key) || ($n = $default);
		return $n;
	}
	public function stringDelimiter($value) {
		return $this->delimiters['string'] . _::escapeString($value) . $this->delimiters['string'];
	}

	public function addQuote($value) {
		if (is_object($value)) {
			if ($value instanceof Field || $value instanceof Raw) return $value();
			return $this->stringDelimiter("$value");
			return $this->stringDelimiter(json_encode($value));
		}
		if (is_string($value) || is_numeric($value)) return $this->stringDelimiter($value);
		if (is_null($value))  return 'NULL';
		if (is_bool($value))  return $value ? 'True' : 'False';
		if (is_array($value)) return $this->stringDelimiter(json_encode($value));
		return 'NULL';
	}
	public function escape($texto) {
		return addcslashes($texto, "\x00..\x1F\x7E..\xFF'\"\\");
	}
	public function escapeString($str) {
		return addcslashes($str, "\"'\\\0..\37\177");
	}
	public function escapeStringForce($str) {
		return addcslashes($str, "\"'%_\\\0..\37!@\177..\377");
	}

	public function compare($value) {
		if (is_array($value) || is_object($value)) $value = json_encode($value);
		if (is_string($value)) return '=' . $this->quote($value);
		if (is_numeric($value)) return '=' . $value;
		if (is_null($value)) return ' IS NULL';
		if (is_bool($value)) return $value ? '=True' : '=False';
		return 'NULL';
	}
	public function get_dataTypeGroup() {
		static $tr = [];
		if (!$tr) foreach ($this->dataTypes as $grp => $tps) foreach ($tps as $tp => $ln) $tr[$tp] = $grp;
		return $tr[$this->vartype];
	}
	public function buildTableName() {
		$argv = func_get_args();
		if (!$argv) return;
		if (is_array($argv[0])) return call_user_func_array([$this, __FUNCTION__], $argv[0]);
		foreach ($argv as $k => $v) $argv[$k] = $this->delimiters['tableStart'] . $v . $this->delimiters['tableEnd'];
		return implode('.', $argv);
	}

	//TODO fix
	public function shiftArrayRes(&$aRes) {
		if (!$aRes) return false;
		if (is_array($aRes)) $res = array_shift($aRes);
		else {
			$res = $aRes;
			$aRes = false;
		}
		return $res instanceof Field ? $res : $this->shiftArrayRes($aRes);
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
			($splitConn['port'] = _::getFreeRandomPort()) || $this->fatalError('Tunnel port auto select error');
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

	//TODO Implement
	public function host_info() { //TODO implement
		//return $this->get_client_info();
	}
	public function createLike($tblSource, $tblNew) {
		$this->exec("DROP TABLE IF EXISTS $tblNew");
		$this->exec("CREATE TABLE $tblNew LIKE $tblSource");
		return $this;
	}
	public function rename($tblFrom, $tblTo) {
		$this->exec("DROP TABLE IF EXISTS $tblTo");
		$this->exec("ALTER TABLE $tblFrom RENAME TO $tblTo");
		//$this->query("REPAIR TABLE $tblTo");
		//$this->query("OPTIMIZE TABLE $tblTo");
		return $this;
	}
	public function details($sql) { //TODO implement
		$class = get_class($this) . '_details';
		return new $class($this, $sql);
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
	public function buildFieldName() {
		$argv = func_get_args();
		if (!$argv) return;
		if (is_array($argv[0])) return call_user_func_array(array($this, __FUNCTION__), $argv[0]);
		$field = $this->fieldDelimiter(array_pop($argv));
		$table = $this->buildTableName($argv);
		return ($table ? $table . '.' : '') . $field;
	}
	public function field($index = null) {
		if (is_null($index)) $index = $this->fieldIdx++;
		$class = $this->fldClass;
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

	public function affected_rows() {
	}
	public function get_client_info() {
	}
}
