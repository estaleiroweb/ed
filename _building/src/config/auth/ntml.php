--force
require_once 'common.php';

$url=getURL();
returnAuth(getAuthNTML());

if (@$_SERVER['HTTP_VIA']!=NULL) die('Proxy impede a autenticação NTLM (PHP)');
$ret=apache_request_headers(); // Recupera o cabeçalho personalizado
if(!@$ret['Authorization']){ //Se a autorização não é existente
	header('HTTP/1.0 401 Unauthorized'); //Envia ao Client o modo de identificação
	header('WWW-Authenticate: Negotiate'); //Insere o prompt NTLM
	header('WWW-Authenticate: NTLM',false); //Insere o prompt NTLM
	die ('Autorização cancelada (1)');//Sai do programa
}

//Autorização
$out=array();
if(substr(@$ret['Authorization'],0,5)=='NTLM '){ //Caso tenha uma autorização válida NTLM
	$chained64=base64_decode(substr($ret['Authorization'],5)); //Recupera e decodifica a informação da autorização
	$step=ord($chained64{8});
	switch ($step) {
	   case 1: //Este byte significa o estágio de processo de identificação (estágio 3)
			if (ord($chained64[13])!=178) die("Flag do NTLM ({$step})"); //Verifica o flag NTLM "0xb2" com posição 13 na mensagem: type-1-message (compatível IE 5.5+)
			$retAuth = "NTLMSSP".chr(000).chr(002).chr(000).chr(000).chr(000).chr(000).chr(000).chr(000);
			$retAuth .= chr(000).chr(040).chr(000).chr(000).chr(000).chr(001).chr(130).chr(000).chr(000);
			$retAuth .= chr(000).chr(002).chr(002).chr(002).chr(000).chr(000).chr(000).chr(000).chr(000);
			$retAuth .= chr(000).chr(000).chr(000).chr(000).chr(000).chr(000).chr(000);
			$retAuth =trim(base64_encode($retAuth));
			header('HTTP/1.0 401 Unauthorized'); //Envia ao Client o modo de identificação
			header('WWW-Authenticate: Negotiate'); //Insere o prompt NTLM
			header('WWW-Authenticate: NTLM '.$retAuth,false);//insere uma informação adicional
			die('Autorização cancelada (2)');//Sai do programa
	   case 3://Este byte significa o estágio de processo de identificação (estágio 5)
			$out['username']=extratChained64($chained64,38);
			if (!$out['username']) die("Login do NT vazio ({$step})");
			$out['domain']=extratChained64($chained64,30);
			$out['machine']=extratChained64($chained64,46);
			break;
	   default:
		   die("Estágio Desconhecido ({$step}) PHP");
		   break;
	}//Fim switch
} else die('Sem autorização (PHP)');

returnAuth($out);

function extratChained64($chained64,$pos){
	$lenght_domain=ord($chained64[$pos+1])*256 + ord($chained64[$pos]); // longueur du domain
	$offset_domain=ord($chained64[$pos+3])*256 + ord($chained64[$pos+2]); // position du domain.
	return str_replace("\0",'',substr($chained64, $offset_domain, $lenght_domain)); // decoupage du du domain
}
