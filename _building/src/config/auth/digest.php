--force
require_once 'common.php';
/*
thisClass()->clearCounters();
$ret=array(
	'fullUsername'=>'admin',
	'nonce'=>'1234567890',
	'response'=>'667f6a568cd6dc10515d4883b5acba55',
	'cnonce'=>'79ad6ab87def9f69',
	'opaque'=>'1c9e8ba9f61faf243012684685a44d07',
	'nc'=>'0',
	'qop'=>'a',
	'uri'=>'/',
	'realm'=>'Digite DOMINIO, USER e SENHA do sistema',
	'A1'=>'7d8ac68919224a58f92b099c0ed62a15',
	'A2'=>'71998c64aea37ae77020c49c00f73fa8',
	'valid_response'=>'5089b1854bfb6f1492fb86367662165e',
);
$pass='admin';


$ret['B1']=$A1=md5($ret['fullUsername'].':'.thisClass()->realm.':'.$pass);
$ret['B2']=$A2=md5($_SERVER['REQUEST_METHOD'].':'.$ret['uri']);
$ret['valid_respons2']=$valid_response=md5($A1.':'.$ret['nonce'].':'.$ret['nc'].':'.$ret['cnonce'].':'.$ret['qop'].':'.$A2);
show($ret);
exit;
*/

//Captura User e passwd
if (empty($_SERVER['PHP_AUTH_DIGEST'])) addHeaders();
if (!($ret=http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))) die('Sem dados de autenticação');

//Checa User e passwd
$oUser=Secure_User::singleton($ret['fullUsername'],SECURE_FILE_FLAG_FIND_BY_NAME);
$ret['idUser']=@$oUser->idUser;
$ret['pass']=$oUser->getPassword();
if(!$ret['idUser'] || !checkPasswd($ret,$oUser->getPassword())) {
	show('Credenciais erradas!');
	show($ret);
	/*
	header("HTTP/1.0 200 OK");
	header("HTTP/1.0 202 Accepted",false);
	header("HTTP/1.0 204 No Content",false);
	header("HTTP/1.0 203 Non-Authoritative Information",false);
	header("HTTP/1.0 205 Reset Content",false);
	header('HTTP/1.1 401 Unauthorized');
	*/
	header('WWW-Authenticate: Newauth');
	exit;
	//sleep(thisClass()->tryWait);
}

//Retorna ao APP
returnAuth($ret);

function http_digest_parse($txt){// function to parse the http auth header
	// protect against missing data
	$needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
	$ret = array();
	preg_match_all('/(\w+)=(?:([\'"])(.*?)\2|([^\s,]+?))/', $txt, $matches, PREG_SET_ORDER);
	foreach ($matches as $m) {
		$ret[$m[1]] = str_replace('\\\\','\\',$m[3] ? $m[3] : $m[4]);
		unset($needed_parts[$m[1]]);
	}
	$ret['fullUsername']=$ret['username'];
	if(($o=splitFullUserName($ret['username']))) {
		$ret['domain']=$o[1];
		$ret['username']=$o[2];
	} else $ret['domain']='';
	$ret['machine']=$_SERVER['REMOTE_ADDR'];
	return $needed_parts ? false : $ret;
}	
function checkPasswd(&$ret,$pass){
	//$A1=md5($ret['fullUsername'].':'.thisClass()->realm.':'.$pass);
	$A1=md5($ret['fullUsername'].':'.thisClass()->realm.'-'.getmyuid().':'.$pass);
	$A2=md5($_SERVER['REQUEST_METHOD'].':'.$ret['uri']);
	$valid_response=md5($A1.':'.$ret['nonce'].':'.$ret['nc'].':'.$ret['cnonce'].':'.$ret['qop'].':'.$A2);
	
	$ret['A1']=$A1;
	$ret['A2']=$A2;
	$ret['valid_response']=$valid_response;
	return ($ret['response']==$valid_response);
}
function addHeaders(){
	header('HTTP/1.1 401 Unauthorized');
	header('WWW-Authenticate: Digest realm="'.thisClass()->realm.'",qop="auth",nonce="1234567890",opaque="'.md5(thisClass()->realm).'"');
	//header('WWW-Authenticate: Digest realm="'.thisClass()->realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5(thisClass()->realm).'"');
	thisClass()->clearCounters();
	die ("Autorização cancelada");
}
