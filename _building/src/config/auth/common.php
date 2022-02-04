--force
@header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
@header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
@header("Cache-Control: post-check=1, pre-check=1");
@header("Cache-Control: no-store, no-cache, public, max-age=0, s-maxage=0, must-revalidate",false);
@header("Pragma: no-cache");
//session_set_cookie_params(0);
//session_cache_limiter("public, no-store");
//session_cache_expire(1);

thisClass($this);
getURL();

function thisClass($obj=false){
	static $c=null;
	if($obj) thisClassName($c=$obj);
	return $c;
}
function thisClassName($obj=false){
	static $c='Secure';
	if($obj) $c=get_class($obj);
	return $c;
}
function getURL(){
	static $url='';
	if($url) return $url;
	if(!@$_SESSION['Classes'][thisClassName()]['url']) {
		session_unset();// Eliminar todas as variáveis de sessão.
		session_destroy();// Finalmente, destruição da sessão.
		die ("ERROR: Não existe URL de Origem");
	}
	return $url=$_SESSION['Classes'][thisClassName()]['url'];
}
function returnAuth($ret){
	$url=getURL();
	thisClass()->clearCounters();
	
	show('Passou!');
	show($ret);
	//show($_SERVER);
	exit;
	//foreach ($ret as $k=>$v) $_SESSION['secury']['auth'][$k]=$v;
//	print http_build_query($ret);
	header("HTTP/1.0 200 OK");
	header("HTTP/1.0 202 Accepted",false);
	header("HTTP/1.0 204 No Content",false);
	header("HTTP/1.0 203 Non-Authoritative Information",false);
	header("HTTP/1.0 205 Reset Content",false);
//	header("HTTP/1.0 301 Moved");
	header("Location: $url");
	exit;
}
function splitFullUserName($username){
	return preg_match('/^(?:(.*)\\\\)?(.+)$/',$username,$ret)?$ret:array(2=>$username);
}