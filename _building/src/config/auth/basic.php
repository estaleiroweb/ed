--force
require_once 'common.php';

//Captura User e passwd
if (empty($_SERVER['PHP_AUTH_USER'])) addHeaders();
if (!@$_SERVER['PHP_AUTH_USER']) die('Sem dados de autenticação');
$ret=splitFullUserName($_SERVER['PHP_AUTH_USER']);

//Checa User e passwd
$oUser=Secure_User::singleton($_SERVER['PHP_AUTH_USER'],SECURE_FILE_FLAG_FIND_BY_NAME);
if(!@$oUser->idUser || $_SERVER['PHP_AUTH_PW']!=$oUser->getPassword()) {
	show('Credenciais erradas!');
	sleep(thisClass()->tryWait);
	addHeaders();
}

//Retorna ao APP
returnAuth(array('idUser'=>@$oUser->idUser,'username'=>$ret[2],'domain'=>$ret[1],'machine'=>$_SERVER['REMOTE_ADDR']));

function addHeaders(){
	header('HTTP/1.0 401 Unauthorized'); //Envia ao Client o modo de identificação
	header('WWW-Authenticate: Basic realm="'.thisClass()->realm.'", title="Login to "'.thisClass()->title.'"'); //Insere o prompt NTLM
	thisClass()->clearCounters();
	die ("Autorização cancelada");
}