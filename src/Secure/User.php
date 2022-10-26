<?php

namespace EstaleiroWeb\ED\Secure;

use EstaleiroWeb\ED\IO\_;
use EstaleiroWeb\ED\IO\Ldap;

class User extends Common {
	static private $instance = array('loadById' => array(), 'loadByName' => array(),);
	static private $domains = array();
	static public $default_domain;
	static public $auth_server;
	static public $auth_port = 389;
	static public $ldap_con;
	public $details;
	protected $readonly;

	static public function singleton($fullName = false, $flags = 0, $token = '') { //SEE SECURE_FILE_FLAG_* Ex: SECURE_FILE_FLAG_FIND_BY_NAME + SECURE_FILE_FLAG_CREATE_IF_NOT_EXISTS
		$c = __CLASS__;
		if (is_array($fullName)) $fullName = implode('\\', $fullName);
		if ($flags & 1) $fn = 'loadById'; //SECURE_FILE_FLAG_FIND_BY_ID
		else $fn = 'loadByName';      //SECURE_FILE_FLAG_FIND_BY_NAME
		if (!array_key_exists($fullName, self::$instance[$fn])) {
			self::$instance[$fn][$fullName] = $obj = new $c();
			$obj->startVars();
			$createIfNotExists = (bool)($flags & 2); //SECURE_FILE_FLAG_CREATE_IF_NOT_EXISTS
			if ($fullName) $obj->$fn($fullName, $token, $createIfNotExists);
		} else $obj = self::$instance[$fn][$fullName];
		return $obj;
	}
	static public function rebuildStaticVars() {
		$ldap = new Ldap();

		//show(Secure::$ini);
		(self::$default_domain = @Secure::$ini['main']['domain']) || (self::$default_domain = $ldap->domain);
		(self::$auth_server = @Secure::$ini['auth']['server']) || (self::$auth_server = $ldap->server);
		(self::$auth_port = @Secure::$ini['auth']['port']) || (self::$auth_port = $ldap->port);
		//show(self::$default_domain);
	}
	private function startVars() {
		$this->readonly = array('idUser' => 0, 'idDomain' => '', 'Domain' => '', 'User' => '', 'Ativo' => 0, 'DtExpires' => null, 'Confirm' => null, 'Token' => null, 'Loged' => false, 'Error' => 'Unloaded');
	}
	private function load($line = null, $token = '') {
		if (@$this->readonly['Loged']) return $this->error('User loged');
		_::verbose("Load User {$line['User']}[{$line['idUser']}]");
		$this->startVars();
		if (!$line) return $this->error('Not Find');
		foreach ($line as $k => $v) $this->readonly[$k] = $v;
		$this->checkToken($line['idUser'], $token);
		return $line;
	}
	function loadById($idUser = null, $token = null, $createIfNotExists = false) {
		_::verbose('Load idUser ' . $idUser);
		if (!$idUser) $idUser = $this->readonly['idUser'];
		return $this->load($this->loadInfo($idUser, $token), $token);
	}
	function loadByName($fullName, $token = null, $createIfNotExists = false) {
		if ($this->readonly['Loged']) return $this->error('User loged');
		_::verbose('Load User ' . $fullName);
		$idUser = $this->getId($fullName);
		if ($idUser) return $this->loadById($idUser, $token);
		return $createIfNotExists ? $this->create($fullName) : $this->load();
	}
	static function splitFullUsername($fullUsername) {
		if (is_array($fullUsername)) return $fullUsername;
		$s = preg_split('/[\/\\\]/', $fullUsername, 2);
		if (count($s) == 1) array_unshift($s, self::$default_domain);
		return array('domain' => $s[0], 'username' => $s[1]);
	}
	function checkPasswordLevel($oldPasswd, $newPasswd) {
		if (!$newPasswd) return 'Senha vazia';
		$canBeEquals = @Secure::$ini['passwdRules']['canBeEquals'];
		$ers = (array)@Secure::$ini['passwdRulesRegExp'];
		$out = $ers ? "As Regras devem ser satisfeitas: \n" . implode(', ', array_keys($ers)) : '';
		if (!$canBeEquals) {
			$out .= ($out ? "\n" : '') . 'Senha não pode ser a mesma da anterior';
			if ($newPasswd == $oldPasswd) return $out;
		}
		foreach ($ers as $k => $er) if (!preg_match($er, $newPasswd)) return $out;
	}
	private function logInError($error = '') {
		return $this->readonly['Loged'] = $this->error($error);
	} //FIXME
	private function checkErrorFunction() {
		return $this->logInError(Secure::$conn->error());
	}      //FIXME
	function save($data) { //FIXME
		_::verbose('Save User ');
		/*//FIXME
		
		if(!$this->readonly['idFile']) return;
		$fields=array('File','L','C','R','U','D','S','Obs',);
		$set=array();
		foreach($fields as $k) if(array_key_exists($k,$data)) $set[]="`$k`=".Secure::$conn->addQuote($data[$k]);
		if($set) {
			$set=implode(',',$set);
			Secure::$conn->query('UPDATE '.Secure::$db.'.tb_Files SET $set WHERE idFile='.$this->readonly['idFile']);
			$this->loadById();
		}
		*/
	}
	public function changeLogedStatus($idUser, $status) {
		if ($idUser == $this->readonly['idUser']) $this->readonly['Loged'] = (bool)$status;
	}
	public function _check_by_ldap($passwd) { //FIXME obsoleto
		$ldap_con = ldap_connect(self::$auth_server, self::$auth_port);
		if (!$ldap_con) die("Could not connect to LDAP server.\n");
		$ldap_fulluser = $this->readonly['User'] . '@' . $this->readonly['Domain']; //Dominio local ou global
		_::disable_error_handler();
		$out = @ldap_bind($ldap_con, $ldap_fulluser, $passwd);
		restore_error_handler();
		return $out;
	}
	static public function check_by_ldapUser($aUser, $passwd) {
		if (!self::$ldap_con) self::$ldap_con = new Ldap();
		$out = self::$ldap_con->logon($aUser['username'], $passwd);
		return $out;
	}
	public function ldap_importDetails($user = null, $force = false) {
		if (!self::$ldap_con) return;
		if ($user) {
			$fulluser = $this->readonly['Domain'] . '/' . $user;
			$idUser = $this->getId($fulluser) + 0;
			//print "Manutenção: $fulluser ($idUser)\n"; 
			if ($idUser) {
				if (!$force) return $idUser;
			} else {
				$oUser = $this->create($fulluser);
				$idUser = $oUser['idUser'] + 0;
				//print_r(array(__LINE__=>array($fulluser=>$idUser)));
			}
		} else {
			$user = $this->readonly['User'];
			$idUser = $this->readonly['idUser'] + 0;
		}
		//show($user);
		$ldap = self::$ldap_con;
		$det = $ldap->userDetails($user);
		$this->details = $det;
		$conn = Secure::$conn;
		$db = Secure::$db;
		if (!$det) {
			$conn->query("UPDATE $db.tb_Users u SET u.Ativo=0 WHERE u.idUser={$idUser}");
			return "$idUser";
		}
		//print_r($det); 
		_::verbose($det);
		$idGestor = $det['manager']['user'] ? $this->ldap_importDetails($det['manager']['user'], $force === true ? true : false) : 0;
		if (!$idGestor) $idGestor = $idUser;
		$ou = @$det['ou'];
		$dn = @$det['dn'];
		($businesscategory = @$det['businesscategory']) || ($businesscategory = @$det['personaltitle']);
		$obs = trim("{$ou}\n{$dn}");
		$grpusr = "'{$conn->escape_string($det['grpusr'])}'";
		//print_r($det);
		{ //Details
			$sql = "
				UPDATE $db.tb_Users_Detail u
				SET u.Nome='{$conn->escape_string($det['name'])}',
					u.Matricula='{$conn->escape_string($user)}',
					u.idGestor=$idGestor,
					u.idCargo=$db.fn_User_GetIdCargo('{$conn->escape_string($businesscategory)}'),
					u.Obs=IFNULL(u.Obs,'{$conn->escape_string($obs)}'),
					u.DtUpdate=NOW()
				WHERE u.idUser={$idUser}
			";
			//print $sql;
			$conn->query($sql);
			$conn->query($sql = "
				UPDATE $db.tb_Users_Confirm u
				SET u.Confirm=1
				WHERE u.idUser={$idUser}
			");
			$conn->query($sql = "
				UPDATE $db.tb_Users u
				SET u.Ativo=1
				WHERE u.idUser={$idUser}
			");
		} { //Mail
			if (@$det['mail']) $conn->query($sql = "
				INSERT $db.tb_Users_Emails (idUser,EmailType,Email,Confirm) 
				VALUES ({$idUser},'Business','{$conn->escape_string($det['mail'])}',1)
				ON DUPLICATE KEY UPDATE Confirm=1
			");
		} { //Phones
			$content = array();
			if (@$det['mobile']) $content[] = "({$idUser},'{$conn->escape_string($det['mobile'])}','Mobile')";
			if (@$det['telephonenumber']) $content[] = "({$idUser},'{$conn->escape_string($det['telephonenumber'])}','Business')";
			if ($content) {
				$content = implode(', ', $content);
				$conn->query($sql = "
					INSERT $db.tb_Users_Telefones (idUser,Telefone,TipoContato) VALUES 
					$content
					ON DUPLICATE KEY UPDATE TipoContato=VALUES(TipoContato)
				");
			}
		} { //User Groups
			$grps = array_merge((array)@$det['memberof']['Applications'], (array)@$det['memberof']['Distribution Lists']);
			$grps[$det['grpusr']] = '';

			$content = array();
			foreach ($grps as $k => $v) {
				$k = $conn->escape_string($k);
				$content[$k] = "('{$k}','{$conn->escape_string($v)}',1)";
			}
			$values = implode(', ', $content);
			$content = implode('\',\'', array_keys($content));
			$conn->query("
			INSERT $db.tb_GrpUsr (GrpUsr,EMail,isLdap) VALUES 
			$values
			ON DUPLICATE KEY UPDATE EMail=IF(VALUES(EMail)='',EMail,VALUES(EMail)), isLdap=1
		");
			$conn->query("
			INSERT IGNORE $db.tb_Users_x_tb_GrpUsr (idUser,idGrpUsr)
			SELECT {$idUser} idUser, idGrpUsr FROM $db.tb_GrpUsr
			WHERE GrpUsr IN ('$content')
		");
			$conn->query("
			DELETE u.* FROM $db.tb_Users_x_tb_GrpUsr u
			JOIN $db.tb_GrpUsr g ON g.idGrpUsr=u.idGrpUsr AND g.isLdap 
			AND g.GrpUsr NOT IN ('$content')
			WHERE u.idUser=$idUser
		");

			$idGrpUsr = $conn->fastValue("SELECT idGrpUsr FROM $db.tb_GrpUsr WHERE GrpUsr=$grpusr") + 0;
			$sql = "
			UPDATE $db.tb_Users_x_tb_GrpUsr u 
			SET u.isMain=NULL
			WHERE u.idUser={$idUser} AND u.idGrpUsr!=$idGrpUsr
		";
			$conn->query($sql = "
			UPDATE $db.tb_Users_x_tb_GrpUsr u 
			SET u.isMain=NULL
			WHERE u.idUser={$idUser} AND u.idGrpUsr!=$idGrpUsr
		");
			$conn->query("
			UPDATE $db.tb_Users_x_tb_GrpUsr u 
			SET u.isMain=1
			WHERE u.idUser={$idUser} AND u.idGrpUsr=$idGrpUsr
		");
		}
		return $idUser;
	}

	public function check_by_ntml() { //FIXME
	}
	public function fullUserName() {
		if (!$this->readonly['User']) return '';
		$out = array();
		if ($this->readonly['Domain']) $out[] = $this->readonly['Domain'];
		$out[] = $this->readonly['User'];
		return implode('\\', $out);
	}

	###Mirror of Database###
	public function getId($fullName = false) {
		if (!$fullName) return $this->readonly['idUser'];
		$s = self::splitFullUsername($fullName);
		$param = array(
			$s['domain'],
			$s['username'],
		);
		return $this->dbFunction('fn_User_GetId', $param);
	}
	public function getIdDomain($domain, $create_if_not_exists = false) {
		$param = array(
			$domain,
			(int)$create_if_not_exists,
		);
		return $this->dbFunction('fn_User_GetIdDomain', $param);
	}
	public function getRandPasswd($tam = 10) {
		return $this->dbFunction('fn_User_GetRandPasswd', (int)$tam);
	}
	public function getPassword($idUser = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		return $this->dbFunction('fn_User_GetPassword', (int)$idUser);
	}
	public function getTokenBin() {
		return $this->dbFunction('fn_User_GetTkbin');
	}
	public function check($passwd, $forceLogIn = null, &$token = null, $idUser = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		$param = array(
			(int)$idUser,
			$passwd,
			(int)$forceLogIn,
			$token,
		);
		$err = $token = $this->dbFunction('fn_User_Check', $param);
		$this->changeLogedStatus($idUser, !$this->erroByToken($token));
		return $err;
	}
	public function checkTryLogIn($idUser = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		return $this->dbFunction('fn_User_CheckTryLogIn', (int)$idUser);
	}
	public function checkToken($idUser = null, $token = '') {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		$param = array(
			(int)$idUser,
			$token,
		);
		$status = $this->dbFunction('fn_User_CheckToken', $param);
		$this->changeLogedStatus($idUser, (bool)$status);
		return $status;
	}
	public function logOn($domain, $user, $passwd, $forceLogIn = false, &$token = '') { //FIXME
		$param = array(
			$domain,
			$user,
			$passwd,
			(int)$forceLogIn,
			$token,
		);
		$line = $this->dbFunction('pc_User_LogOn', $param);
		$token = $line['Token'];
		$this->changeLogedStatus($this->readonly['idUser'], (bool)$token);
		return $line;
	}
	public function isActive() {
		$idUser = $this->readonly['idUser'];
		if ($idUser) return $this->dbFunction('fn_User_IsActive', (int)$idUser);
		$this->error('User wasn\'t load');
		return 0;
	}
	public function loadDetails($idUser = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		if (!$idUser) return;
		$idUser = (int)$idUser;

		$det = array(
			'main' => $this->dbProcedure('pc_User_Details', $idUser),
			'staff' => $this->dbProcedure('pc_User_Detais_Staff', $idUser),
			'Groups' => $this->dbProcedureAll('pc_User_Detais_Groups', $idUser),
			'emails' => $this->dbProcedureAll('pc_User_Details_Emails', $idUser),
			'phones' => $this->dbProcedureAll('pc_User_Details_Phones', $idUser),
			'addresses' => $this->dbProcedureAll('pc_User_Details_Addresses', $idUser),
			'workers' => $this->dbProcedureAll('pc_User_Details_Workers', $idUser),
		);
		if ($idUser == $this->readonly['idUser']) $this->readonly['Details'] = $det;
		return $det;
	}
	public function create($fullName = '', $passwd = '', $email = '') {
		if ($fullName) $s = self::splitFullUsername($fullName);
		else $s = array('domain' => $this->readonly['Domain'], 'username' => $this->readonly['User']);
		if (!@$s['username']) return;
		_::verbose('Create User ' . $s['username']);
		if (!$passwd) {
			$passwd = $this->getRandPasswd();
			//$email=$s['username'].'@'.self::$default_emailHost;
		}
		$param = array(
			$s['domain'],
			$s['username'],
			$passwd,
			$email,
		);
		$idUser = $this->dbFunction('fn_User_Create', $param);
		if ($idUser) {
			$this->buildToken($idUser);
			return $this->loadById($idUser);
		}
	}
	public function changePasswd($oldPasswd, $newPasswd, $idUser = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		if (!$idUser) return $this->error('Usuário não carregado');
		_::verbose('Muda Password ' . $this->readonly['User']);
		$erroPass = $this->checkPasswordLevel($oldPasswd, $newPasswd);
		if ($erroPass) return $this->error($erroPass);

		$param = array(
			(int)$idUser,
			$oldPasswd,
			$newPasswd,
		);
		$err = $token = $this->dbFunction('fn_User_ChangePasswd', $param);
		$this->changeLogedStatus($idUser, !$this->erroByToken($token));
		return $err;
	}
	public function setPasswd($passwd, $idUser = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		if (!$idUser) return $this->error('Usuário não carregado');
		//$erroPass=$this->checkPasswordLevel('',$passwd);
		//show(['Manutenção:',$idUser,$erroPass]);
		//if($erroPass) return $this->error($erroPass);

		$param = array(
			(int)$idUser,
			$passwd,
		);
		$err = $token = $this->dbFunction('fn_User_SetPasswd', $param);
		$this->changeLogedStatus($idUser, !$this->erroByToken($token));
		return $err;
	}
	public function loadInfo($idUser = null, $token = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		$param = array(
			(int)$idUser,
			$token,
		);
		return $this->dbProcedure('pc_User_Info', $param);
	}
	public function logOut($idUser = null, $token = null) {
		if (!$idUser) {
			$idUser = $this->readonly['idUser'];
			if (!$token) $token = $this->readonly['Token'];
		}
		$param = array(
			(int)$idUser,
			$token,
		);
		$this->startVars();
		return $this->dbFunction('fn_User_Logout', $param);
	}
	public function buildToken($idUser = null) {
		if (!$idUser) $idUser = $this->readonly['idUser'];
		return $this->dbFunction('fn_User_BuildToken', (int)$idUser);
	}
}
User::rebuildStaticVars();
