<?php

namespace EstaleiroWeb\ED\IO;

use EstaleiroWeb\Cache\Config;
use EstaleiroWeb\Traits\GetterAndSetter;

if (!function_exists('disable_error_handler')) {
	function disable_error_handler() {
	}
}
if (!function_exists('ldap_control_paged_result')) {
	function ldap_control_paged_result($conn = null, $p2 = null, $p3 = null, $p4 = null) {
	}
}
if (!function_exists('ldap_control_paged_result_response')) {
	function ldap_control_paged_result_response($conn = null, $res = null, $cookie = null) {
	}
}

class Ldap {
	use GetterAndSetter;

	function __construct($user = null, $pass = null) {
		$this->readonly = [
			'fulluser' => null,
			'con' => null,
			'bind' => null,
		];
		$config = Config::singleton();
		$arr = [
			'server' => 'localhost',
			'domain' => 'mydomain',
			'fn' => 'ldap_search',
			'dn' => 'OU=Organization,DC=domain,DC=host,DC=com,DC=br',
			'port' => 389,
			'user' => null,
			'pass' => $pass,
		];
		$this->protect = array_merge($arr, $config->ldap);
		$this->user = $user;
		@define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 0x0032);
		@define('LDAP_SCOPE_ONELEVEL', 2);
		@define('LDAP_SCOPE_SUBTREE', 2);
	}
	private function setUser($val) {
		$this->protect['user'] = $val;
		$this->makeFulluser();
	}
	private function setDomain($val) {
		$this->protect['domain'] = $val;
		$this->makeFulluser();
	}
	private function makeFulluser() {
		$this->readonly['fulluser'] = (!$this->protect['user'] || !$this->protect['domain']) ? null : $this->protect['user'] . '@' . $this->protect['domain'];
	}

	public function conn() {
		if ($this->readonly['con']) return $this->readonly['con'];

		$this->readonly['con'] = ldap_connect($this->protect['server'], $this->protect['port']);
		if (!$this->readonly['con']) return _::verbose("Error connection\n") && false;
		ldap_set_option($this->readonly['con'], LDAP_OPT_REFERRALS, 0);
		//ldap_set_option($this->readonly['con'], LDAP_OPT_PROTOCOL_VERSION, 3);
		//ldap_set_option($this->readonly['con'], LDAP_OPT_SIZELIMIT, 100000);

		return $this->readonly['fulluser'] ? $this->logon() : $this->readonly['con'];
	}
	public function logon($user = null, $pass = null) {
		//_::verbose();
		if (!$this->conn()) return _::verbose("Without connection\n") && false;
		if ($user) $this->user = $user;
		if ($pass) $this->protect['pass'] = $pass;
		if (!$this->readonly['fulluser']) return _::verbose("Without user\n") && false;
		disable_error_handler();
		$this->readonly['bind'] = @ldap_bind($this->readonly['con'], $this->readonly['fulluser'], $this->protect['pass']);
		restore_error_handler();
		if (!$this->readonly['bind']) {
			_::verbose("LDAP bind failed\n");
			return false;
		}
		return true;
	}
	public function load($filter, $attr = null, $fn = null, $dn = null) {
		if (!$this->conn()) return _::verbose("Without connection\n") && false;

		if (!$fn) $fn = $this->protect['fn']; //$fn='ldap_search'; // ldap_read  ldap_search ldap_list
		if (!$dn) $dn = $this->protect['dn'];
		$param = array(
			'conn' => $this->readonly['con'],
			'DN' => $dn,
			'filter' => $filter,
		);
		if ($attr) $param['attrs'] = is_array($attr) ? $attr : preg_split('/\s*[,;]\s*/', trim($attr));

		$data = array('count' => 0);
		$cookie = '';
		do {
			ldap_set_option($this->readonly['con'], LDAP_OPT_PROTOCOL_VERSION, 3);
			ldap_control_paged_result($this->readonly['con'], 500, true, $cookie);
			//$res=ldap_search($this->readonly['con'], $dn, $filter);
			$res = @call_user_func_array($fn, $param);
			if (!$res) return _::verbose("Error in search query\n") && false;
			//print_r(array($fn,$param));
			$entries = ldap_get_entries($this->readonly['con'], $res);
			//_::verbose(array($filter,$res)); 
			$entries['count'] += $data['count'];
			$data = array_merge($data, $entries);
			ldap_control_paged_result_response($this->readonly['con'], $res, $cookie);
			//print "{$data['count']}\n";
		} while ($cookie);

		/*
		$res=call_user_func_array($fn,$param);
		if(!$res) return _::verbose("Error in search query\n") && false;
		$data=ldap_get_entries($this->readonly['con'], $res);
		*/
		return $this->rebuildAttributes($data);
	}
	public function load2() { //implementar
		$l = ldap_connect('somehost.mydomain.com');
		$pageSize    = 100;
		$pageControl = array(
			'oid'        => '1.2.840.113556.1.4.319',
			'iscritical' => true,
			'value'      => sprintf("%c%c%c%c%c%c%c", 48, 5, 2, 1, $pageSize, 4, 0)

		);
		$controls = array($pageControl);

		ldap_set_option($l, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_bind($l, 'CN=bind-user,OU=my-users,DC=mydomain,DC=com', 'bind-user-password');

		$continue = true;
		while ($continue) {
			ldap_set_option($l, LDAP_OPT_SERVER_CONTROLS, $controls);
			$sr = ldap_search(
				$l, //ldap
				'OU=some-ou,DC=mydomain,DC=com', //base
				'cn=*', //filter
				['sAMAccountName'], //attributes 
				1,  //attributes_only default 0
				-1, //sizelimit 
				-1, //timelimit
				LDAP_DEREF_NEVER, //deref
				[] //
			);
			ldap_parse_result($l, $sr, $errcode, $matcheddn, $errmsg, $referrals, $serverctrls); // (*)
			if (isset($serverctrls)) {
				foreach ($serverctrls as $i) {
					if ($i["oid"] == '1.2.840.113556.1.4.319') {
						$i["value"][8]   = chr($pageSize);
						$i["iscritical"] = true;
						$controls        = [$i];
						break;
					}
				}
			}

			$info = ldap_get_entries($l, $sr);
			if ($info["count"] < $pageSize) {
				$continue = false;
			}

			for ($entry = ldap_first_entry($l, $sr); $entry != false; $entry = ldap_next_entry($l, $entry)) {
				$dn = ldap_get_dn($l, $entry);
			}
		}
	}
	public function userDetails($user = null) {
		if (!$user) $user = $this->protect['user'];
		$userDetails = $this->load(
			'(&(objectCategory=person)(objectClass=user)(sAMAccountName=' . $user . '))',
			//'(&(objectCategory=person)(objectClass=user)(sAMAccountName=F8034871))',
			'cn,ou,displayName,name,telephoneNumber,mobile,mail,personaltitle,businessCategory,department,division,sAMAccountName,manager,memberOf' //sn,employeeType,
		);
		if (!$userDetails) return false;
		$userDetails = $this->rebuildUser(array_shift($userDetails));
		$userDetails['manager'] = $this->userDN(@$userDetails['manager']);

		$fld = array('mobile', 'telephonenumber');
		foreach ($fld as $k) $userDetails[$k] = array_key_exists($k, $userDetails) ? preg_replace('/(\+55)?([1-9]{2})(\d{4,5})(\d{4})$/', '\2 \3-\4', @$userDetails[$k]) : '';

		if (@$userDetails['division'] == @$userDetails['department']) $userDetails['grpusr'] = @$userDetails['department'];
		elseif (@$userDetails['division'] && @$userDetails['department']) $userDetails['grpusr'] = @$userDetails['division'] . ' - ' . @$userDetails['department'];
		else $userDetails['grpusr'] = @$userDetails['division'] . @$userDetails['department'];
		$fld = array('division', 'department');
		foreach ($fld as $k) if (array_key_exists($k, $userDetails)) unset($userDetails[$k]);

		$grp = array();
		if (array_key_exists('memberof', $userDetails)) {
			foreach ($userDetails['memberof'] as $k => $dn) $this->loadMenber($grp, $dn);
			unset($userDetails['memberof']);
		}
		$userDetails['memberof'] = $grp;
		return $userDetails;
	}
	public function userPersonal($user = null, $fields = '') {
		if (!$user) $user = $this->protect['user'];
		return $userDetails = $this->load(
			'(&(objectCategory=person)(objectClass=user)(sAMAccountName=' . $user . '))',
			$fields //'cn,ou,displayName,name,telephoneNumber,mobile,mail,personaltitle,businessCategory,department,division,sAMAccountName,manager,memberOf' //sn,employeeType,
		);
		if (!$userDetails) return false;
		$userDetails = $this->rebuildUser(array_shift($userDetails));
		$userDetails['manager'] = $this->userDN(@$userDetails['manager']);

		$fld = array('mobile', 'telephonenumber');
		foreach ($fld as $k) $userDetails[$k] = array_key_exists($k, $userDetails) ? preg_replace('/(\+55)?([1-9]{2})(\d{4,5})(\d{4})$/', '\2 \3-\4', @$userDetails[$k]) : '';

		if (@$userDetails['division'] == @$userDetails['department']) $userDetails['grpusr'] = @$userDetails['department'];
		elseif (@$userDetails['division'] && @$userDetails['department']) $userDetails['grpusr'] = @$userDetails['division'] . ' - ' . @$userDetails['department'];
		else $userDetails['grpusr'] = @$userDetails['division'] . @$userDetails['department'];
		$fld = array('division', 'department');
		foreach ($fld as $k) if (array_key_exists($k, $userDetails)) unset($userDetails[$k]);

		$grp = array();
		if (array_key_exists('memberof', $userDetails)) {
			foreach ($userDetails['memberof'] as $k => $dn) $this->loadMenber($grp, $dn);
			unset($userDetails['memberof']);
		}
		$userDetails['memberof'] = $grp;
		return $userDetails;
	}
	public function allUsers() {
		$allUsers = $this->load(
			'(&(objectCategory=person)(objectClass=user)(sAMAccountName=*))',
			'cn,displayName,name,sAMAccountName'
		);
		if (!$allUsers) return false;
		return $allUsers;
		//foreach($allUsers as $k=>$v) $allUsers[$k]=$this->rebuildUser(array_shift($v));
	}
	public function userDN($dn) {
		$usr = $this->load(
			'(ou=*)',
			'cn,displayName,name,sAMAccountName',
			'ldap_read',
			$dn
		);
		return $usr ? $this->rebuildUser(array_shift($usr)) : false;
	}
	public function userFromName($name) {
		$usr = $this->load(
			'(&(objectCategory=person)(objectClass=user)(name=' . $name . '))',
			'cn,ou,displayName,name,telephoneNumber,mobile,mail,personaltitle,businessCategory,department,division,sAMAccountName,manager,memberOf',
			'ldap_search'
		);
		return $usr ? $this->rebuildUser(array_shift($usr)) : false;
	}
	public function userFromDisplayName($name) {
		$usr = $this->load(
			'(&(objectCategory=person)(objectClass=user)(displayName=' . $name . '))',
			'cn,ou,displayName,name,telephoneNumber,mobile,mail,personaltitle,businessCategory,department,division,sAMAccountName,manager,memberOf',
			'ldap_search'
		);
		return $usr ? $this->rebuildUser(array_shift($usr)) : false;
	}
	public function usersFromManager($dn) {
		$usr = $this->load(
			'(&(objectCategory=person)(objectClass=user)(manager=' . $dn . '))',
			'cn,displayName,name,sAMAccountName',
			'ldap_search' //,
			//$dn
		);
		/*
		$userDetails=$this->load( //UserDet
			'(&(objectCategory=person)(objectClass=user)(sAMAccountName='.$user.'))',
			//'(&(objectCategory=person)(objectClass=user)(sAMAccountName=F8034871))',
			'cn,ou,displayName,name,telephoneNumber,mobile,mail,personaltitle,businessCategory,department,division,sAMAccountName,manager,memberOf' //sn,employeeType,
		);
		
		//Menber
		if(!preg_match('/^(CN=[^,]+)(?:,(.*))?,OU=([^,]+)((?:,DC=[^,]*)+)$/',$dn,$ret)) return false;
		$ou=$ret[3];
		$out=$this->load(
			$ret[1],
			'cn,mail', //,member
			'ldap_search',
			'OU='.$ou.$ret[4]
		);
		
		//all
		$allUsers=$this->load(
			'(&(objectCategory=person)(objectClass=user)(sAMAccountName=T*))',
			'cn,displayName,name,sAMAccountName'
		);

		//User by DN
		$usr=$this->load(
			'(ou=*)',
			'cn,displayName,name,sAMAccountName',
			'ldap_read',
			$dn
		);
		*/
		return $usr;
		return $usr ? $this->rebuildUser(array_shift($usr)) : false;
	}
	private function rebuildUser($array) {
		($name = @$array['displayname']) || ($name = @$array['name']) || ($name = @$array['cn']);
		$array['user'] = @$array['samaccountname'];
		$array['name'] = $name;
		$fld = array('displayname', 'cn', 'samaccountname');
		foreach ($fld as $k) if (array_key_exists($k, $array)) unset($array[$k]);


		return $array;
	}
	private function loadMenber(&$grp, $dn) {
		//$list=preg_replace('/(.*),OU=([^,]+)((?:,DC=[^,]*)+)$/','',$dn);
		//if(preg_match('/^CN=([^,]+),(.*),OU=([^,]+)((?:,DC=[^,]*)+)$/',$list,$ret)){
		if (!preg_match('/^(CN=[^,]+)(?:,(.*))?,OU=([^,]+)((?:,DC=[^,]*)+)$/', $dn, $ret)) return false;
		$ou = $ret[3];
		$out = $this->load(
			$ret[1],
			'cn,mail', //,member
			'ldap_search',
			'OU=' . $ou . $ret[4]
		);
		if ($out) {
			$out = array_shift($out);
			$out['cn'] .= preg_replace('/,?\w+=/', '.', $ret[2]);
			//unset($out['dn']);
			$grp[$ou][$out['cn']] = @$out['mail'];
		}
	}
	private function rebuildAttributes($attr) {
		$out = array();
		foreach ($attr as $k => $v) {
			if (is_array($v)) {
				$v = $this->rebuildAttributes($v);
				$t = count($v);
				if ($t == 0) $v = null;
				elseif ($t == 1) $v = reset($v);
			} elseif ($k === 'count' || (is_numeric($k) && array_key_exists($v, $attr))) continue;
			$out[$k] = $v;
		}
		return $out;
	}
	public function arraytrTime($arr) {
		static $er = '/^(badpasswordtime|lastlogon|pwdlastset|accountexpires|lastlogontimestamp)$/';
		if (is_array($arr)) foreach ($arr as $k => $v) {
			if (is_array($v)) $arr[$k] = $this->arraytrTime($v);
			elseif (preg_match($er, $k) && preg_match('/^\d+$/', $v)) $arr[$k] = $this->dateConv($v);
		}
		return $arr;
	}
	public function dateConv($epoch) {
		$winSecs = (int)($epoch / 10000000); // divide by 10 000 000 to get seconds
		$ns = $epoch - ($winSecs * 10000000);
		$unixTimestamp = ($winSecs - 11644473600); // 1.1.1600 -> 1.1.1970 difference in seconds
		//$ADToUnixConvertor=((1970-1601) * 365.242190) * 86400; // unix epoch - AD epoch * number of tropical days * seconds in a day
		//$unixTsLastLogon=intval($winSecs-$ADToUnixConvertor); // unix Timestamp version of AD timestamp
		//$unixTsLastLogon=intval($secsAfterADEpoch-$ADToUnixConvertor); // unix Timestamp version of AD timestamp
		//return date(DateTime::RFC822, $unixTimestamp);
		return strftime('%F %T', $unixTimestamp) . ".$ns";
	}
}
