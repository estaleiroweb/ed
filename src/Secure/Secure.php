<?php

namespace EstaleiroWeb\ED\Secure;

use EstaleiroWeb\Cache\Config;
use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\Ext\Bootstrap;
use EstaleiroWeb\ED\Ext\BootstrapSubmenu;
use EstaleiroWeb\ED\Ext\JQuery_Cookie;
use EstaleiroWeb\ED\Ext\JQuery_UI;
use EstaleiroWeb\ED\IO\_;
use EstaleiroWeb\ED\IO\SessControl;
use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\Traits\SingletonClass;
use Exception;

define('SECURE_LEVEL_FREE', 0);
define('SECURE_LEVEL_SECURED', 1);
define('SECURE_LEVEL_PARANOIC', 2);
define('FILE_FLAG_FIND_BY_NAME', 0);
define('FILE_FLAG_FIND_BY_ID', 1);
define('FILE_FLAG_CREATE_IF_NOT_EXISTS', 2);
//Parse_Conf::$ttl='-1 second';
class Secure extends Common {
	use SingletonClass;

	static public $ini, $conn;
	static public $levels = array('Free', 'Secured', 'Paranoic');
	static public $authFnMethod;  //db,ldap,ntml,external
	static public $cookie_path = '/';
	static public $cookie_expire = null;
	static public $timeRefreshMenu = 0;                    //A cada 600segundos = 10 minutos o menu será rechecado
	static public $frm_class = 'form-horizontal';          //form-inline form-horizontal
	static public $fgp_class = 'form-group form-group-sm'; //('' form-group-sm form-group-lg)
	static public $lbl_class = '';                         //sr-only
	static public $btn_class = 'btn btn-default btn-sm';   //(btn-default btn-primary btn-success btn-info btn-warning btn-danger btn-link) ('' btn-xs btn-sm btn-lg) (btn-block) (active) disabled="disabled"
	static public $ipt_class = 'form-control';             //('' input-sm input-lg)
	static public $ipt_style = '';                         // style="width:8em;"
	static public $obj, $db, $db_log;
	static public $idFile, $idUser;
	private $passwd;
	public $logonButton = [];

	private $sess;
	private $state_Layout = 'layout_unloged';
	private $access_Layout = 'layout_denied';
	public $title = 'Autenticação de Usuário';
	public $realm = 'Digite DOMINIO\USER e SENHA do sistema';
	protected $menu = []; // árvore de menus onde o último nível pode ser '' ou 'tags html' ou array('href'=>'content', 'target'=>'content', 'onclick'=>'script')
	protected $links = array(
		'home' => array('html' => 'Home', 'href' => '/'),
	);
	protected $aErrMessage = [
		0 => 'OK',
		1 => 'Empty Password',
		2 => 'Unknown user',
		3 => 'Inactive User',
		4 => 'Invalid Password',
		5 => 'Over try Login',
		6 => 'Expired Password',
		7 => 'Error Change Password',
		8 => 'User already Loged',
		9 => 'Unknown error',
	];

	final protected function __construct($noStart = false) {
		global $__autoload;

		$this->readonly = [
			'user' => null, //'domain'=>'','user'=>'','idUser'=>0,'machine'=>'','idDomain'=>'','ativo'=>0,'grpUsers'=>[],'UserDetails'=>[],
			'file' => null,
			'acc' => null, //'pCRUDS'=>0, 'CRUDS'=>0, 'status'=>1,
			'logon' => [],
			'flow-status' => 'non-started', //started, finished
			'Error' => '',
		];
		//show($_COOKIE);
		self::$cookie_path = $__autoload->host;
		$ini = Config::singleton();
		self::$ini = $ini->secure;
		//_::show(session_save_path());
		//_::show($ini);
		$this->connect();
		$this->menu();
		if ($noStart === true || !$this->secure_init($noStart)) return;
		if (is_null(self::$cookie_expire)) self::$cookie_expire = time() + 2592000; /*30 dias*/
		self::$obj = $this;
		$this->layout_init($noStart);
		//show($this->sess->get());
		//_::verbose();
		$this->readonly['file'] = $this->loadFile();
		Secure::$idFile = $this->readonly['file']->idFile;
		self::$conn->query('SET @Secure_idFile=' . Secure::$idFile);

		$this->readonly['user'] = $this->loadUser(); //Recupera User da sessão
		$arr = [];
		if ($this->isLoged()) {
			$arr['method'] = 'LOGED';
			$arr['user'] = $this->readonly['user']->fullUserName();
			($arr['keepalive'] = @$_POST['keepalive']) || $arr['keepalive'] = @$_COOKIE['keepalive'];
			setcookie('username', $arr['user'], self::$cookie_expire, self::$cookie_path);
			$_COOKIE['username'] = $arr['user'];
			if (@$_POST['logout'] == $this->getIdUser()) {
				$this->logOut();
				setcookie('keepalive', 0, self::$cookie_expire, self::$cookie_path);
				$_COOKIE['keepalive'] = 0;
				$arr['keepalive'] = 0;
			}
		} else {
			if (($arr['user'] = @$_POST['username'])) { /*Recupera por POST*/
				$arr['method'] = 'POST';
				$arr['keepalive'] = @$_POST['keepalive'];
				$arr['password'] = @$_POST['password'];
				$arr['new_password'] = @$_POST['new_password'];
				$arr['passwordBin'] = $this->dbFunction('fn_encode', $arr['password']);
			} elseif (($arr['keepalive'] = @$_COOKIE['keepalive'])) { /*Recupera por COOKIE*/
				$arr['method'] = 'COOKIE';
				$arr['user'] = @$_COOKIE['username'];
				$arr['passwordBin'] = @$_COOKIE['password'];
				$arr['password'] = $this->dbFunction('fn_decode', $arr['passwordBin']);
				$arr['new_password'] = null;
				//$this->logIn($_COOKIE['username']);
			}
			if ($arr['user']) {
				$err = $this->logIn($arr['user'], $arr['password'], $arr['new_password']);
				if ($err && strlen($err) < 3) {
					$this->error("ERROR[$err]: {$this->aErrMessage[$err]}", true);
				}
			}
		}

		setcookie('username', $arr['user'], self::$cookie_expire, self::$cookie_path);
		setcookie('keepalive', $arr['keepalive'], self::$cookie_expire, self::$cookie_path);
		setcookie('password', @$arr['passwordBin'], self::$cookie_expire, self::$cookie_path);
		$_COOKIE['username'] = $arr['user'];
		$_COOKIE['keepalive'] = $arr['keepalive'];
		$_COOKIE['password'] = @$arr['passwordBin'];

		$this->readonly['acc'] = $this->permition();
		if ($this->isLoged()) {
			$this->addLog();
			$this->state_Layout = 'layout_loged';
			Secure::$idUser = $this->readonly['user']->idUser;
			self::$conn->query('SET @Secure_idUser=' . Secure::$idUser);
		} else {
			$this->sess->menu = [];
			//if($this->readonly['autoLogon'] && $this->readonly['file']->L) $this->captureUserPassword(); //FIXME
		}
		//showme($_SESSION);
		//showme($_POST);
		//showme($arr);
		//showme($_COOKIE);

		$cruds = (int)@$this->readonly['acc']['CRUDS'];
		//show(Secure::$idUser);
		if ($cruds == 0) exit; //layout_denied default
		elseif (self::can_R($cruds)) {
			$this->access_Layout = 'layout_allow';
			print $this->__toString();
		} else $this->access_Layout = 'layout_nonRead';
	}
	public function __destruct() {
		if ($this->access_Layout != 'layout_allow') print $this->__toString();

		//_::verbose(__LINE__);
		//$_SESSION['Classes'][__CLASS__]['User']=serialize($this->readonly['user']);
	}
	public function __toString() {
		if ($this->readonly['flow-status'] != 'started') return '';
		$outHtml = OutHtml::singleton();
		if (!$outHtml->organize) return '';
		$this->readonly['flow-status'] = 'finished';
		$this->showError();
		return call_user_func([$this, $this->access_Layout]);
	}
	public function getLevel() {
		if (@$this->readonly['file']) return @$this->readonly['file']->level;
	}
	public function getCRUDS() {
		return @$this->readonly['acc']['CRUDS'];
	}
	public function getC() {
		return $this->can_C($this->getCRUDS());
	}
	public function getR() {
		return $this->can_R($this->getCRUDS());
	}
	public function getU() {
		return $this->can_U($this->getCRUDS());
	}
	public function getD() {
		return $this->can_D($this->getCRUDS());
	}
	public function getS() {
		return $this->can_S($this->getCRUDS());
	}
	public function getIdUser($obj = false) {
		if ($obj && $obj instanceof User) return $obj->idUser;
		if (!$this->isLoged()) return 0;
		$obj = $this->readonly['user'];
		return $obj && $obj instanceof User ? $obj->idUser : 0;
	}
	public function getIdFile($obj = false) {
		if (!$obj) $obj = $this->readonly['file'];
		return $obj && $obj instanceof File ? $obj->idFile : 0;
	}
	public function title($text, $h1 = true) {
		OutHtml::singleton()->title($text, $h1);
	}
	protected function menu() {
	}
	final private function secure_init($parameters) {
		if ($this->readonly['flow-status'] != 'non-started') return false;
		$this->readonly['flow-status'] = 'started';
		//_::verbose();
		_::verbose('Start ' . __CLASS__);
		//show(self::$ini);
		//_::verbose();
		$mainVar = array('autoLogon' => false, 'multiSession' => true, 'expiresSession' => 15, 'tryWait' => 10, 'tryTimes' => 3, 'expiresPassword' => 120,);
		foreach ($mainVar as $k => $v) {
			if (!isset(self::$ini[$k])) self::$ini[$k] = $v;
			else $v = self::$ini[$k];
			self::$conn->query("SET @{$k}=" . _::value2String($v));
			$this->readonly[$k] = $v;
		}
		$this->sess = SessControl::singleton(__CLASS__, 'main');
		if (!isset($this->sess->loggingStep)) $this->sess->loggingStep = 0;
		if (!isset($this->sess->loged))       $this->sess->loged = false;
		if (!isset($this->sess->token))       $this->sess->token = '';
		if (!isset($this->sess->menu))        $this->sess->menu = [];
		$this->sess->url = $GLOBALS['__autoload']->fullUrl;

		return !$parameters;
	}
	public function connect() {
		if (!self::$conn) {
			Secure::$db = Secure::$ini['db'];
			Secure::$db_log = Secure::$ini['db_log'];
			if (!@self::$ini) _::error('Configuration file "secure.ini" doesn\'t find');
			//if (!@self::$ini['dsn']) _::error('DSN (Data Source Name) doesn\'t find');
			self::$conn = Conn::dsn(self::$ini['dsn']);
		}
		return self::$conn;
	}
	public function layout_init($parameters) {
		$outHtml = OutHtml::singleton();
		if (!$outHtml->organize) return;
		$outHtml->doctype();
		//header('Content-Type: text/html; charset=utf-8');
		if (preg_match('/\bNoSecure\b/i', $parameters))       $outHtml->nocache();
		//if(!preg_match('/\bNoContentType\b/i',$parameters)) 
		$outHtml->contentType();
		//exit;
		if (!preg_match('/\bNoIE\b/i', $parameters))          $outHtml->ie();
		if (!preg_match('/\bNoMobile\b/i', $parameters))      $outHtml->mobile();
		if (!preg_match('/\bNoBootstrap\b/i', $parameters))   new Bootstrap();
		if (!preg_match('/\bNoJQuery\b/i', $parameters)) {
			new JQuery_UI();
			new JQuery_Cookie();
		}
		//if(!preg_match('/\bNoNavBar\b/i',$parameters))      $this->NavBar=NavBar::singleton();
		//if(!preg_match('/\bNoMediator\b/i',$parameters)) {
		//	$this->Mediator=MediatorPHPJS::singleton();
		//	foreach ($this->protect as $k=>$v) if (isset($_COOKIE[$k])) $this->protect[$k]=$this->Mediator->$k;
		//}
		$outHtml->compatibility_IE();
		//$this->history=History::singleton();
	}
	public function layout_logonStatus() {
		return call_user_func_array(array($this, $this->state_Layout), func_get_args());
	}
	public function layout_loged() {
		$lbl = @$this->logonButton['left'];
		$lbr = @$this->logonButton['right'];
		$outHtml = OutHtml::singleton();
		if (!$outHtml->organize) return;
		/*$outHtml->jQueryScript['secure_loged'] = '
			var totalSeconds = 900; //(15 * 60) 15 minutos em segundos
			var $loginTimer = $("#secure_btn_login_det");
			function startTimer() {
				let timer = setInterval(function () {
					totalSeconds--;
					let minutes = Math.floor(totalSeconds / 60);
					let seconds = totalSeconds % 60;
					$loginTimer.text(minutes.toString().padStart(2, "0") + ":" + seconds.toString().padStart(2, "0"));
					if (totalSeconds <= 0) {
						clearInterval(timer);
						$("#secure_btn_login").click();
					}
				}, 1000);
			}
		
			if ($loginTimer.length) startTimer();
		';*/
		return '
			<form method="POST" class="navbar-form navbar-right">' . $lbl . '
				<input type="hidden" name="logout" value="' . $this->getIdUser() . '"> 
				<button class="' . self::$btn_class . '" type="submit" id="secure_btn_login" title="Logout">
					<span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> ' . $this->readonly['user']->User . ': 
					<span id="secure_btn_login_det">Logout</span>
				</button>' . $lbr . '
			</form>
		';
	}
	public function layout_unloged() {
		$username = $this->getIdUser() ? $this->readonly['user']->User : @$_COOKIE['username'];
		$username = htmlspecialchars($username, ENT_QUOTES);
		//$ka_checked=$ka_active='';
		//if(@$_COOKIE['keepalive']) {
		$ka_checked = ' checked';
		$ka_active = ' active';
		//}
		//unchecked
		$outHtml = OutHtml::singleton();
		if (!$outHtml->organize) return;
		$outHtml->jQueryScript['secure_unloged'] = '
			$("#secure_chk_keepalive").change(function(){
				$obj=$(this).parent();
				$obj.find("span").removeClass("glyphicon-check glyphicon-unchecked").addClass(this.checked?"glyphicon-check":"glyphicon-unchecked");
				$.cookie(this.name, this.checked?1:0);
			}).change();
			$("#secure_txt_username").keyup(function(){
				var $forget=$("#secure_btn_forget");
				var $forgetTxt=$("#secure_spn_forget");
				if($(this).val().length==0) {
					$forget.attr("disabled","disabled");
					$forgetTxt.text("Fill username to Forget password");
				}
				else {
					$forget.removeAttr("disabled");
					$forgetTxt.text("Click here to Forget password");
				}
			}).keyup();
			$("#secure_btn_cancel").click(function(){
				$("#secure_container").hide("slow");
			});
			$("#secure_btn_login").click(function(){
				$("#secure_container").show("slow");
			});
		';

		return '
			<form method="POST" class="navbar-form navbar-right"> 
				<button class="' . self::$btn_class . '" type="button" id="secure_btn_login">
					<span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Login
				</button>
			</form>
			<table id="secure_container" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0,0, 0.4);"><tr><td>
			<div class="container">
			<div class="col-md-6 col-md-offset-3">
			<div class="jumbotron">
				<form method="POST" class="' . self::$frm_class . '" id="secure_form">
					<div class="' . self::$fgp_class . '">
						<label class="' . self::$lbl_class . '" for="secure_txt_username" title="User of the network company">Username:</label>
						<div class="input-group">
							<input class="' . self::$ipt_class . '" type="text" tabindex="1" name="username" id="secure_txt_username" value="' . $username . '" placeholder="user or domain\user"' . self::$ipt_style . '>
							<span class="input-group-btn">
								<div class="btn-group" data-toggle="buttons">
									<label class="' . self::$btn_class . $ka_active . '">
										<input type="checkbox" tabindex="3" name="keepalive" id="secure_chk_keepalive" value="1"' . $ka_checked . '> 
										<span class="glyphicon" aria-hidden="true"></span>
										Keep Alive
									</label>
								</div>
							</span>
						</div>
					</div>
					<div class="' . self::$fgp_class . '">
						<label class="' . self::$lbl_class . '" for="secure_txt_password">Password:</label>
						<div class="input-group">
							<input class="' . self::$ipt_class . '" type="password" tabindex="2" name="password" id="secure_txt_password" placeholder="Password"' . self::$ipt_style . '>
							<span class="input-group-btn">
								<button class="' . self::$btn_class . '" type="button" id="secure_btn_forget">
									<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> <span id="secure_spn_forget"></span>
								</button>
							</span>
						</div>
					</div>
					<div class="' . self::$fgp_class . ' text-right">
						<button class="' . self::$btn_class . ' btn-success" id="secure_btn_ok"     type="submit"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> LogIn</button>
						<button class="' . self::$btn_class . ' btn-danger"  id="secure_btn_cancel" type="button"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel</button>
					</div>
				</form>
			</div>
			</div>
			</div>
			</td></tr></table>
		';
	}
	public function layout_change_password() {
		$out = $this->layout_default();
		//print __FUNCTION__;
		return $out;
	}
	public function layout_denied() {
		$out = $this->layout_default();

		print '<div class="container text-danger bg-danger"><div class="row"><div class="col-md-12">Access denied on this page</div></div></div>';
		$this->clearCounters();
		return $out;
	}
	public function layout_allow() {
		$out = $this->layout_default();
		return $out;
	}
	public function layout_nonRead() {
		$out = $this->layout_default();
		print '<div class="container text-warning bg-warning"><div class="row"><div class="col-md-12">You don\'t have permition to read this content</div></div></div>';
		return $out;
	}
	public function layout_default() {
		$outHtml = OutHtml::singleton();
		if (!$outHtml->organize) return;
		new BootstrapSubmenu();
		$m = $this->checkMenu($this->menu);
		if (!$m) return;

		$m = $this->buildNavBar(array($this->buildMenu($m), $this->layout_logonStatus()));
		$outHtml->addPreBody($m . '<br/><br/><br/>');
	}
	public function showError() {
		if (!$this->readonly['Error']) return;
		$outHtml = OutHtml::singleton();
		if (!$outHtml->organize) return;
		$outHtml->addPreBody('<div class="container text-danger bg-danger"><div class="row"><div class="col-md-12">' . $this->readonly['Error'] . '</div></div></div>');
	}
	final private function logIn2($full_username, $passwd = null, $newPass = null, $forceLogIn = false) {
		global $__autoload;

		_::verbose('');
		/*
		$conn->query('
			INSERT IGNORE '.Secure::$db_log.'tb_TryLogIn 
			SET	`IP`='.$conn->addQuote($_SERVER['REMOTE_ADDR']).',
				`User`='.$conn->addQuote($full_username).',
				`Password`='.$conn->addQuote($passwd).',
				`Url`='.$conn->addQuote($__autoload->fullUrl)
		);
		*/
		$obj = User::singleton($full_username, FILE_FLAG_FIND_BY_NAME + FILE_FLAG_CREATE_IF_NOT_EXISTS);
		$idUser = $this->getIdUser($obj);
		if ($idUser) {
			$this->readonly['user'] = $obj;
			$err = $this->secure_check_passwd(User::splitFullUsername($full_username), $passwd, $newPass, $forceLogIn);
		} else {
			$err = 2; //Unknown user 
			_::error('ERROR sistêmico: Usuário e senha corretos, porém sistema não conseguiu carregá-lo', FATAL_ERROR);
		}
		return $this->changeStatus($err);
	}
	final private function logIn($full_username, $passwd = null, $newPass = null, $forceLogIn = false) {
		_::verbose('');
		if (!self::$authFnMethod) (self::$authFnMethod = @Secure::$ini['authMethod']) || (self::$authFnMethod = 'ldap');
		/*
			$conn->query('
				INSERT IGNORE '.Secure::$db_log.'tb_TryLogIn 
				SET	`IP`='.$conn->addQuote($_SERVER['REMOTE_ADDR']).',
					`User`='.$conn->addQuote($full_username).',
					`Password`='.$conn->addQuote($passwd).',
					`Url`='.$conn->addQuote($__autoload->fullUrl)
			);
		*/
		$err = call_user_func(array($this, 'secure_check_passwd_by_' . self::$authFnMethod), User::splitFullUsername($full_username), $passwd, $newPass, $forceLogIn);
		if (!$err) {
			$obj = User::singleton($full_username, FILE_FLAG_FIND_BY_NAME + FILE_FLAG_CREATE_IF_NOT_EXISTS);
			$idUser = $this->getIdUser($obj);
			//showme($obj);
			if ($idUser) {
				$this->readonly['user'] = $obj;
				$err = call_user_func(array($this, 'secure_check_passwd_by_' . self::$authFnMethod . '_end'), $passwd, $newPass, $forceLogIn);
			} else $err = 2; //Unknown user 
		}
		$this->changeStatus($err);
		return $err;
	}
	final private function secure_check_passwd() { //FIXME obsoleto
		return call_user_func_array(array($this, 'secure_check_passwd_by_' . self::$authFnMethod), func_get_args());
	}
	final private function secure_check_passwd_by_db($aFull_username = null, $passwd = null, $newPass = null, $forceLogIn = false) { //FIXME igual ldap
		/*[
			0=>'OK',
			1=>'Empty Password',
			2=>'Unknown user',
			3=>'Inactive User',
			4=>'Invalid Password',
			5=>'Over try Login',
			6=>'Expired Password',
			7=>'Error Change Password',
			8=>'User already Loged',
			9=>'Unknown error',
		];*/
		if (!$passwd) return 1; //Empty Password
		$line = Secure::$conn->fastLine(
			'
			SELECT u.* 
			FROM ' . Secure::$db . '.tb_Domain d 
			JOIN ' . Secure::$db . '.tb_Users u ON d.idDomain=u.idDomain AND u.User="' . Secure::$conn->escape_string($aFull_username['username']) . '" 
			WHERE d.Domain="' . Secure::$conn->escape_string($aFull_username['domain']) . '"'
		);
		if (!$line) return 2; //Unknown user
		if (!$line['Ativo']) return 3; //Inactive User

		$idUser = $line['idUser'];
		if ($passwd != $this->dbFunction('fn_User_GetPassword', $idUser)) return 4; //Invalid Password
		$token = $this->sess->loged ? $this->sess->token : '';
		$obj = $this->readonly['user'];
		$err = $obj->check($passwd, $forceLogIn, $token);
		if ($err == 6) {
			if ($newPass) $err = $obj->changePasswd($passwd, $newPass);
			else $this->layout_change_password();
		}
		return $err;
	}
	final private function secure_check_passwd_by_ldap($aFull_username = null, $passwd = null, $newPass = null, $forceLogIn = false) {
		// see secure_check_passwd_by_db comments
		if (!$passwd) {
			$idUser = $this->dbFunction('fn_User_GetId', $aFull_username);
			if ($idUser) $passwd = $this->dbFunction('fn_User_GetPassword', $idUser);
		}
		$this->passwd = $passwd;
		if (!$passwd) return 1; //Empty Password
		if (!User::check_by_ldapUser($aFull_username, $passwd)) return 4; //Invalid Password
		//return $this->readonly['user']->setPasswd($passwd); //Set Password and return Token
	}
	final private function secure_check_passwd_by_ntml($aFull_username = null, $passwd = null, $newPass = null, $forceLogIn = false) { //FIXME
		// see secure_check_passwd_by_db comments
	}
	final private function secure_check_passwd_by_external($aFull_username = null, $passwd = null, $newPass = null, $forceLogIn = false) { //FIXME
		// see secure_check_passwd_by_db comments
	}
	final private function secure_check_passwd_by_db_end($passwd = null, $newPass = null, $forceLogIn = false) {
		return $this->readonly['user']->check($passwd, $forceLogIn); //verifica password and return Token
	}
	final private function secure_check_passwd_by_ldap_end($passwd = null, $newPass = null, $forceLogIn = false) {
		$this->readonly['user']->ldap_importDetails();
		//$this->readonly['user']->setPasswd($this->passwd); //Set Password and return Token
		$token = $this->readonly['user']->buildToken();
		$this->readonly['user']->changeLogedStatus(null, $token);
		return $token;
	}
	final private function secure_check_passwd_by_ntml_end($passwd = null, $newPass = null, $forceLogIn = false) {
		return 0;
	}
	final private function secure_check_passwd_by_external_end($passwd = null, $newPass = null, $forceLogIn = false) {
		return 0;
	}

	final private function changeStatus($token) {
		if (strlen($token) < 3) return;
		//show($token);
		$obj = $this->readonly['user'];
		$err = $this->erroByToken($token);
		$line = $this->logOnTrErrors($err);
		//$line['idUser']=$idUser;
		//$line['token']=$token;
		$this->readonly['logon'] = $line;
		$this->sess->idUser = (int)@$obj->idUser;
		$this->sess->token = $token;
		$this->sess->loged = @$obj->Loged;

		if ($err) {
			$this->error($line['userMessageError'], true);
			setcookie('keepalive', '', self::$cookie_expire, self::$cookie_path);
		} else {
			$this->error();
		}
		return $this->sess->loged;
	}
	final private function logOut() {
		setcookie('username', null);
		$_COOKIE['username'] = null;
		setcookie('keepalive', null);
		$_COOKIE['keepalive'] = null;
		//$_COOKIE['keepalive']='';
		if ($this->getIdUser()) $this->readonly['user']->LogOut();
		$this->readonly['user'] = null;
		$this->readonly['logon'] = [];
		$this->sess->idUser = 0;
		$this->sess->loggingStep = 0;
		$this->sess->loged = false;
		$this->sess->token = '';
	}
	final private function captureUserPassword() { //Logar com algum usuário
		global $__autoload;
		_::verbose('');

		$cont = 0;
		$urls = array_values(self::$ini['logging']);
		while (($url = @$urls[$cont++]) && !_::checkHost($url));
		if ($url && $this->sess->loggingStep++ < $this->readonly['tryTimes']) {
			$this->exit2URL_File($url, array('PHPSESSID' => session_id(), 'URL' => $__autoload->fullUrl, 'AL' => $__autoload->thisFile));
		} else {
			$this->clearCounters();
			$this->exit2URL_File(self::$ini['URLs']['denied']);
		}
	}
	final protected function buildNavBar(array $navBars) {
		if (($home = @$this->links['home'])) {
			$home['href'] = (@$home['href'] && $this->permitionFile($home['href'])) ? $home['href'] : '';
			if (!@$home['html']) $home['html'] = 'Home';
		} else $home = array('html' => 'Home', 'href' => '/');

		return '	<nav class="navbar navbar-default navbar-fixed-top sticky-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			' . $this->buildAnchor($home, 'class="navbar-brand"') . '
		</div>
		<div class="navbar-collapse collapse" aria-expanded="true">
	' . implode("\n", $navBars) . '
		</div>
	</div>
	</nav>
';
	}
	final protected function buildMenu(array $aMenu, $ulClass = 'nav navbar-nav', $level = 0) {
		$tab = "\t";
		$lf = "\n";
		$intTab = str_repeat($tab, $level + 2);
		$liAttr = $level ? '' : ' class="dropdown"';
		$out = $intTab . '<ul class="' . $ulClass . '">' . $lf;
		foreach ($aMenu as $name => $itens) {
			if (!is_array($itens)) {
				if ($itens) $out .= $intTab . $tab . $itens . $lf;
				else $out .= $intTab . $tab . '<li class="divider"></li>' . $lf;
			} elseif ($this->isItemMenu($itens)) { //item
				$itens['html'] = $name;
				$out .= $intTab . $tab . '<li' . $liAttr . '>' . $this->buildAnchor($itens, 'tabindex="0"') . '</li>' . $lf;
			} else {
				if ($liAttr) {
					$attr = $liAttr;
					$caret = '<span class="caret"></span>';
				} else {
					$attr = ' class="dropdown-submenu"';
					$caret = '';
				}
				$out .= $intTab . $tab . '<li' . $attr . '>' . $lf;
				$out .= $intTab . $tab . $tab . '<a tabindex="0" data-toggle="dropdown" aria-expanded="false">' . $name . $caret . '</a>' . $lf;
				$out .= $this->buildMenu($itens, 'dropdown-menu', $level + 2);
				$out .= $intTab . $tab . '</li>' . $lf;
			}
		}
		$out .= $intTab . '</ul>' . $lf;
		return $out;
	}
	final protected function checkMenu($aMenu, $id = '', $level = 0) {
		if ($level == 0 && @$this->sess->menu) {
			$sess = $this->sess->menu;
			if ($sess[$id] && $sess[$id][0] + self::$timeRefreshMenu > time()) return $sess[$id][1];
		}
		if (is_array($aMenu)) {
			if ($this->isItemMenu($aMenu)) {
				if (!@$aMenu['href']) return false;
				$permition = $this->permitionFile($aMenu['href']);
				//print "== {$aMenu['href']}: $permition<br>";
				if ($permition === 0) return false;
			} else {
				$last = '';
				foreach ($aMenu as $name => &$itens) { //Retira elementos sem permissão e dividers duplicados
					$itens = $this->checkMenu($itens, $id, $level + 1);
					if ($itens === false || ($itens == '' && $last == '')) unset($aMenu[$name]);
					$last = $itens;
				}
				while ($aMenu) { //Retira elementos em branco do inicio
					if (reset($aMenu)) break;
					else array_shift($aMenu);
				}
				while ($aMenu) { //Retira elementos em branco do fim
					if (end($aMenu)) break;
					else array_pop($aMenu);
				}
			}
		} elseif (!$aMenu) return '';
		if ($level == 0) {
			$aMenu = (array)$aMenu;
			$sess = $this->sess->menu;

			$sess[$id] = array(time(), $aMenu);
			$this->sess->menu = $sess;
		} elseif (!$aMenu) return false;
		return $aMenu;
	}
	final protected function isItemMenu(array $aMenu) {
		return !$aMenu || array_key_exists('href', $aMenu);
	}
	final protected function buildAnchor(array $item, $attr = '') {
		global $__autoload;
		//$links=array('href', 'target', 'onclick');
		if (@$item['href']) $item['href'] = preg_replace('/^((https?:)?\/?\/?)(this|localhost)\b/i', 'http://' . $__autoload->host, $item['href']);
		if (@$item['html']) {
			$html = $item['html'];
			unset($item['html']);
		} else $html = 'Link';
		$out = '';
		foreach ($item as $k => $v) if ($v) $out .= ' ' . $k . '="' . htmlspecialchars($v, ENT_QUOTES) . '"';
		if ($attr) $attr = ' ' . trim($attr);
		return '<a' . $out . $attr . '>' . $html . '</a>';
	}
	/**
	 * Checa se há usuário logado
	 **/
	public function isLoged() {
		//show($this->readonly['user']);
		return $this->readonly['user'] ? $this->readonly['user']->Loged : false;
	}
	public function loadFile($file = false) {
		if (!$file) $file = $GLOBALS['__autoload']->url;
		return File::singleton($file, FILE_FLAG_FIND_BY_NAME + FILE_FLAG_CREATE_IF_NOT_EXISTS);
	}
	public function loadUser($user = null, $token = '') {
		if (!$user) $user = @$this->sess->idUser;
		if (!$token) $token = @$this->sess->token;
		if ($user) return User::singleton($user, is_numeric($user) ? FILE_FLAG_FIND_BY_ID : FILE_FLAG_FIND_BY_NAME, $token);
	}
	public function loadLastUser() {
		$idUser = Secure::$conn->fastValue('
			SELECT u.idUser FROM ' . Secure::$db . '.tb_Users u
			JOIN ' . Secure::$db . '.tb_Users_Token t USING(idUser)
			WHERE u.Ativo
			ORDER BY t.DtUpdate DESC
			LIMIT 1
		');
		if (!$idUser) return;
		return User::singleton($idUser, FILE_FLAG_FIND_BY_ID);
	}
	static function auth_return2Url() {
		$url = @$_GET['URL'];
		if (!$url) return false;
		$sessId = @$_GET['PHPSESSID'] ? "PHPSESSID={$_GET['PHPSESSID']}" : '';
		$div = strpos($url, '?') === false ? '?' : '&';
		_::goURL($url . $div . $sessId);
	}
	function clearCounters() {
		if (isset($this->sess->loggingStep)) unset($this->sess->loggingStep);
		if (isset($this->sess->realm))       unset($this->sess->realm);
	}
	/**
	 * Checa permissão File x User
	 **/
	public function permition($idFile = false, $idUser = false) {
		static $arr = [];
		//show('Manutenção: '. __CLASS__ . '->'. __FUNCTION__ ."($idFile,$idUser):". __LINE__);
		if (!$idFile) {
			$idFile = $this->getIdFile();
			if (!$idFile) return;
		}
		if ($idUser === false) $idUser = $this->getIdUser();
		$param = [
			(int)$idFile,
			(int)$idUser,
		];
		$k = join(',', $param);
		if (!key_exists($k, $arr)) {
			$arr[$k] = $this->dbProcedure('pc_Permition_File_by_idFile_idUser', $param);
		}
		return $arr[$k];
	}
	public function getGroupPermition() {
		$conn = $this->connect();
		$idFile = Secure::$idFile;
		$idUser = Secure::$idUser;
		return $conn->query_all("CALL db_Secure.pc_Permition_List_File_by_idFile_idUser($idFile,$idUser)");
	}
	public function permitionFile($file) {
		$oFile = File::singleton($file, FILE_FLAG_FIND_BY_NAME);
		$idFile = @$oFile->idFile;
		if (!$idFile) return false;
		$idUser = $this->getIdUser();
		$line = $this->permition($idFile, $idUser);
		return (int)@$line['CRUDS'];
	}
	public function permitionListUsers($idFile = false) {
		if (!$idFile) {
			$idFile = $this->getIdFile();
			if (!$idFile) return;
		}
		$param = array(
			(int)$idFile,
			31,
		);
		$grps = $this->dbProcedureAll('pc_Permition_GrpUsr_by_idFile_CRUDS', $param);
	}
	public function userListGrp($idGrpUsr) {
		return $this->dbProcedureAll('pc_User_ListGrp', $idGrpUsr);
	}
	function exit2URL_File($url = false, $parameteres = []) {
		_::verbose('');
		if ($url != '/' && $url[0] == '/') require_once $url;
		else {
			_::goURL(($url ? $url : '/') . ($parameteres ? '?' . http_build_query($parameteres) : ''));
			exit;
		}
	}
	function getUserDoamins() {
		return self::dbViewAll('vw_UserDomains');
	}
	static public function can_Create($cruds) {
		return $cruds & 16;
	}
	static public function can_Read($cruds) {
		return $cruds & 8;
	}
	static public function can_Update($cruds) {
		return $cruds & 4;
	}
	static public function can_Delete($cruds) {
		return $cruds & 2;
	}
	static public function can_Special($cruds) {
		return $cruds & 1;
	}
	static public function can_C($cruds) {
		return $cruds & 16;
	}
	static public function can_R($cruds) {
		return $cruds & 8;
	}
	static public function can_U($cruds) {
		return $cruds & 4;
	}
	static public function can_D($cruds) {
		return $cruds & 2;
	}
	static public function can_S($cruds) {
		return $cruds & 1;
	}
	private function addLog() {
		$param = array(
			$this->readonly['user']->idUser,
			$_SERVER['REMOTE_ADDR'],
			'$(( NOW() ))',
		);
		$sql = 'REPLACE ' . Secure::$db . '.tb_Users_Ip (idUser, Ip, DtUpdate) VALUES (' . $this->dbCheckParameters($param) . ');';
		//show($sql);
		self::$conn->query($sql);

		if (@$_SERVER['HTTP_REFERER']) {
			$oFile = File::singleton($_SERVER['HTTP_REFERER'], FILE_FLAG_FIND_BY_NAME);
			$referer = $oFile->idFile ? $oFile->idFile : $_SERVER['HTTP_REFERER'];
		} else $referer = '';
		$request = json_encode($_REQUEST);
		if (preg_match_all('/"frm_action_([^"]+)":"(\d+)"/', $request, $ret, PREG_SET_ORDER)) {
			$frm_action = [];
			foreach ($ret as $v) $frm_action[] = $v[1] . ':' . $v[2];
			$frm_action = implode(',', $frm_action);
		} else $frm_action = null;
		$param = array(
			'idUser' => $this->readonly['user']->idUser,
			'idFile' => $this->readonly['file']->idFile,
			'CRUDS' => $this->readonly['acc']['CRUDS'],
			'remoteAddr' => $_SERVER['REMOTE_ADDR'],
			'remotePort' => $_SERVER['REMOTE_PORT'],
			'serverAddr' => $_SERVER['SERVER_ADDR'],
			'serverPort' => $_SERVER['SERVER_PORT'],
			'request' => preg_replace(array('/(?<!\\\)"(frm_action_[^"]+|passw(ord)?d[^"]*)(?<!\\\)":"[^"]*"/', '/(\'\',|,\'\')/'), array('\'\'', ''), $request),
			'referer' => $referer,
			'requestMethod' => @$_SERVER['REQUEST_METHOD'],
			'HTTPs' => @$_SERVER['HTTPS'] ? 1 : 0,
			'frm_action' => $frm_action,
		);
		$sql = 'INSERT ' . Secure::$db_log . '.tb_Logs (' . implode(', ', array_keys($param)) . ') VALUES (' . $this->dbCheckParameters($param) . ');';
		//show([$sql,$param]);
		self::$conn->query($sql);
	}
}
