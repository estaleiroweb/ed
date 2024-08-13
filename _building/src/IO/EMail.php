<?php

/**
 * @example
 *
 * EMail::$debug=true;
 * 
 * $e=new EMail();
 * $e->alarm=true;
 * $e->version='1.0';
 * $e->server='200.184.26.12';
 * $e->client=''; //localhost
 * $e->port=25;
 * $e->crypt='AUTO'; //AUTO: Automatic, TLS (Transport Layer Security), SSL
 * $e->requestAuth=false;//Requer autenticação
 * $e->logonMethod='AUTO'; //LOGIN, CRAM-MD5, NTLM, PLAIN
 * $e->forceLogonMethod=true;   //if logonMethod method not found, it auto choice other
 * $e->user='';
 * $e->password='';
 * $e->priority=1; //array(0=>'',1=>'High',2=>'Low',3=>'Normal'); //default=0
 * $e->headers='';
 * $e->realm=''; //for NTML
 * $e->timeLimit=7;
 * 
 * $e->from='user@domain.com.br';
 * $e->from='user@domain.com.br';
 * $e->from='Cicrano <user@domain.com.br>';
 * $e->from='user@domain.com';
 * 
 * $e->to='username@domain.com.br';
 * $e->to[]='Fulano <username@domain.com>';
 * $e->to[]='username@yahoo.com.br';
 * $e->to['username@domain.com.br']='Fulano';
 * $e->to['Fulano']='username@domain.com.br';
 * $e->to[]='a <user_a@host.com>; user_b@host.com;c <user_c@host.com>';
 * show($e->to);exit;
 * 
 * $e->cc=null;
 * $e->bcc=null; //cco
 * $e->replyTo=null;
 * $e->returnPath=null;
 * $e->returnReceiptTo=null;
 * $e->xSender=null;
 * 
 * $e->subject='Test email';
 * 
 * $body='<b>Test</b> de e-mail';
 * $e->body->charset='iso-8859-1';
 * $e->body->encoding='8bit';
 * $e->body['text/html']=$body; //text/plain
 * $e->body[]=strip_tags(preg_replace(array('/[\r\n]/','/(<\s*(?:\/(?:div|p|br)|br)\s*>)/'),array('',"\\1\r\n"),$body)); //text/plain
 * 
 * $e->file['eu.txt']='teste de arquivo anexo';
 * $e->file['tu.txt']='outro teste de arquivo anexo';
 * $e->file[]='path/full_file_name';
 * 
 * if(!$e->send()) print_r($e->error);
 */
class EMail {
	public static $debug=false;
	protected static $lf="\r\n";
	protected static $ANONYMOUS_ADDRESS='Anonymous <anonymous@anyone.com>';
	
	private $protect=array(
		//'sender'          =>2, //0:php_mail, 1:bash_sendmail, 2:sokect
		'version'         =>'1.0',
		'server'          =>null, //servidor de e-mail IP or FQDN
		'client'          =>'',
		'port'            =>25,
		'crypt'           =>'AUTO', //Automatic, TLS (Transport Layer Security), SSL
		'requestAuth'     =>false,  //Requer autenticação
		'logonMethod'     =>'AUTO', //LOGIN, CRAM-MD5, NTLM, PLAIN
		'forceLogonMethod'=>true,   //if logonMethod method not found, it auto choice other
		'user'            =>'',
		'password'        =>'',
		'from'            =>null,
		'to'              =>null,
		'cc'              =>null,
		'bcc'             =>null, //cco
		'replyTo'         =>null,
		'returnPath'      =>null,
		'returnReceiptTo' =>null,
		'xSender'         =>null,
		'priority'        =>0,//priorityStr
		'subject'         =>'',
		'headers'         =>'',
		'body'            =>null,
		'file'            =>null,
		'alarm'           =>false,
		'realm'           =>'',
		'timeLimit'       =>7,
	);
	private $error=array();
	//private $senders=array('php_mail','bash_sendmail','sokect');
	private $priorities=array(0=>'',1=>'High',2=>'Low',3=>'Normal');
	private $rawResponse='';
	private $methods=array('LOGIN', 'CRAM-MD5', 'NTLM', 'PLAIN');
	private $options=array();
	private $resMail;
	public $boundary='alternative boundary';

	public function __construct($p_array=false) {
		$this->boundary=md5(date('r', time()));
		$this->clearAddress();
		$this->body='';
		$this->file='';
		if (is_array($p_array)) foreach($p_array as $k=>$v) $this->$k=$v;
		if($this->to->count()) $this->send();
	}
	public function __set($nm,$val){
		$fn='set'.ucfirst($nm);
		if(method_exists($this,$fn)) $this->$fn($val);
		elseif(isset($this->protect[$nm])) $this->protect[$nm]=$val;
		return $this;
	}
	public function set($nm,$val){
		return $this->__set($nm,$val);
	}
	public function __get($nm){
		$fn='get'.ucfirst($nm);
		if(method_exists($this,$fn)) return $this->$fn();
		elseif(array_key_exists($nm,$this->protect)) return $this->protect[$nm];
	}
	public function setError($val=false){ 
		if(!$val) $this->error=array();
		else $this->error[]=$val;
		return false;
	}
	public function setAlarm($val=true){ $this->protect['alarm']=(bool)$val; }
	/*public function setSender($val=0){ 
		if(preg_match('/(php_mail)|(bash_sendmail)|(sokect)/i',$val,$ret)) $this->protect['sender']=count($ret)-1;
		else{ 
			$val=(int)$val;
			if($val>0 && $val<3) $this->protect['sender']=(int)$val;
		}
	}*/
	public function setPriority($val=0){
		if(!$val) $this->protect['priority']=0;
		elseif(preg_match('/(High|Alta)|(Low|Baixa)|(Normal)/i',$val,$ret)) $this->protect['priority']=count($ret)-1;
		else{ 
			$val=(int)$val;
			if($val>0 && $val<4) $this->protect['priority']=(int)$val;
		}
	}
	public function setPriorityStr($val=''){ $this->setPriority($val); }
	public function setBody($message=null,$type=null,$charset=null,$encoding=null) { $this->protect['body']=new EMailMessage($message,$type,$charset,$encoding); }
	public function setFile($filename=null,$data=null)                             { $this->protect['file']=new EMailFile($filename,$data); }
	public function setCrypt($val='AUTO')                                          { $this->protect['crypt']=preg_match('/^(AUTO|SSL|TLS)$/i',$val)?strtoupper($val):''; }
	public function setLogonMethod($val='AUTO')                                    { $this->protect['logonMethod']=preg_match('/^(AUTO|LOGIN|CRAM-MD5|NTLM|PLAIN)$/i',$val)?strtoupper($val):''; }
	public function setFrom($val=false)                                            { $this->protect['from']=new EMailAddress($val); $this->protect['from']->limit=1; }
	public function setTo($val=false)                                              { $this->protect['to']=new EMailAddress($val); }
	public function setCc($val=false)                                              { $this->protect['cc']=new EMailAddress($val); }
	public function setBcc($val=false)                                             { $this->protect['bcc']=new EMailAddress($val); }
	public function setReplyTo($val=false)                                         { $this->protect['replyTo']=new EMailAddress($val); }
	public function setReturnPath($val=false)                                      { $this->protect['returnPath']=new EMailAddress($val); }
	public function setReturnReceiptTo($val=false)                                 { $this->protect['returnReceiptTo']=new EMailAddress($val); }
	public function setXSender($val=false)                                         { $this->protect['xSender']=new EMailAddress($val); }
	public function setCco($val=false)                                             { $this->setBcc($val); }
	public function getError()                                                     { return $this->error; }
	public function getPriorities()                                                { return $this->priorities; }
	public function getPriorityStr()                                               { return @$this->priorities[$this->protect['priority']]; }
	//public function getSenders()                                                   { return $this->senders; }
	//public function getSender()                                                    { return $this->protect['sender'].': '.$this->senders[$this->protect['sender']]; }
	public function clearAddress(){
		$this->from=self::$ANONYMOUS_ADDRESS;
		$this->to='';
		$this->cc='';
		$this->bcc='';
		$this->replyTo='';
		$this->returnPath='';
		$this->returnReceiptTo='';
		$this->xSender='';
	}

 	public function send() {
		$headers=$this->makeHeaders();
		//Check
		if ($this->to->count()+$this->cc->count()+$this->bcc->count()==0)   $this->setError('Não existe destinatário');
		if ($this->alarm) {
			if("{$this->from}"==self::$ANONYMOUS_ADDRESS)                   $this->setError('Não existe remetente');
			if(!$this->protect['subject'])                                  $this->setError('Não existe assunto');
			if($this->protect['body']->count()==0)                          $this->setError('Não existe mensagem');
		}
		if($this->error) return false;
		
		
		$body=array_merge($this->protect['body'](),$this->protect['file']());
		$count=count($body);
		if($count==0) $body='';
		elseif($count==1) $body=$body[0];
		else {
			$boundary=md5(date('r', time())).'-alt';
			$separator='--'.$boundary.self::$lf;
			$body=implode($separator,$body);
			$body='Content-Type: multipart/alternative; boundary="'.$boundary.'"'.self::$lf.self::$lf.$separator.$body.'--'.$boundary.'--'.self::$lf;
		}

		$this->resMail=@fsockopen($this->server, $this->port, $errno, $errstr, 30);
		$this->debug("Connecting: {$this->server} {$this->port}\n");
		if (!is_resource($this->resMail)) return $this->setError($errstr);
		
		$ret=$this->sendBySocket($headers.$body);
		@fclose($this->resMail);
		$this->resMail=null;
		return $ret;
		/*$lf="\r\n";
		if($this->protect['sender']==2) { //Envio by socket
		}
		elseif($this->protect['sender']==1) { //Envio by bash command sendmail
			$cmd='echo "'.str_replace('"','\"',$headers.$lf.$lf.$body).'" | sendmail -t > /dev/null ;echo $?';
			$this->debug("$cmd\n");
			$ret=`$cmd`=='0';
			if(!$ret) return $this->setError('ERROR shell sendmail');
		} 
		else { //Envio by php command mail
			$ret=@mail($this->to,$this->subject,$body,$headers);
			if(!$ret) $this->setError('ERROR mail');
		}
		*/
	}
	public function getHost(){
		return trim(`ip route get {$this->server} | grep src | sed -r 's/.*( src ([0-9\.]+)).*/\\2/'`);
	}
	public function hmac($data, $key) {
		if(function_exists('hash_hmac')) return hash_hmac('md5', $data, $key);

		$bytelen=64; // byte length for md5
		if(strlen($key)>$bytelen) $key=pack('H*', md5($key));
		$key =str_pad($key, $bytelen, chr(0x00));
		$ipad=str_pad('',   $bytelen, chr(0x36));
		$opad=str_pad('',   $bytelen, chr(0x5c));
		$k_ipad=$key ^ $ipad;
		$k_opad=$key ^ $opad;

		return md5($k_opad . pack('H*', md5($k_ipad . $data)));
	}
	private function sendBySocket($body){
		$host=$this->client?$this->client:$this->getHost();
		if(!$this->sendCommand()) return false; //receive first line
		if($this->requestAuth) { //Auth
			if(!($parameters=$this->startSend('EHLO '.$host))) return false;
			if($logonMethod=$this->receiveAuthMethod()) {
				//sendAUTH_LOGIN() | sendAUTH_CRAM_MD5() | sendAUTH_NTML() | sendAUTH_PLAIN()
				if(!$this->{'sendAUTH_'.str_replace('-','_',$logonMethod)}()) return false;
			} elseif($this->error) return false;
		}
		elseif(!$this->startSend('HELO '.$host)) return false;
		
		$aCmd=array();
		$aCmd[]=array(250,'MAIL FROM: '.$this->from->email());
		$rcpt=array_keys(array_merge($this->protect['to'](),$this->protect['cc'](),$this->protect['bcc']()));
		foreach($rcpt as $email) $aCmd[]=array(250,'RCPT TO: <'.$email.'>');
		$aCmd[]=array(354,'DATA');
		$aCmd[]=array(250,str_replace("\r\n.\r\n","\r\n\\.\r\n",$body).'.');
		$aCmd[]=array(221,'QUIT');

		foreach($aCmd as $v) if(!$this->sendCommand($v[0],$v[1])) return false;
		return true;
	}
	private function sendCommand($reqCode=220,$command=null) {
		if(!is_resource($this->resMail)) return false;
		$this->rawResponse='';
		if ($command) {
			$this->debug("> $command\n");
			fwrite($this->resMail, "$command\r\n");
		}
		$limit=$this->timeLimit+time();
		//while (($line=fgets($this->resMail))) $this->rawResponse.=$line;
		if(0) while (!feof($this->resMail)){
			$line=fgets($this->resMail);
			//$this->debug(__LINE__.": ================\n");
			$this->debug("< $line");
			$this->rawResponse.=$line;
			if(@$line[3]==' ') break;
			$info=stream_get_meta_data($this->resMail);
			if($info['timed_out']) {
				$this->debug("# timed out\n");
				break;
			}
			if(time()>$limit) {
				$this->debug("# timed limit\n");
				break;
			}
		}
		while ($line=fgets($this->resMail)) {
			$this->debug("< $line\n");
			if (!$line) return false;
			$this->rawResponse.=$line;
			if (preg_match('/(...)(.)(.*)/i', $line, $regs)) {
				if($regs[2]==' ') break;
				//$this->retCmd=array('code'=>$regs[1],'msg'=>trim($regs[3]) ,'err'=>trim($regs[2]));
				//$this->pr("< {$this->retCmd['code']} {$this->retCmd['msg']}\n");
				//if ($this->retCmd['err']=='') return true;
			}else break;
		}

		$errors=$out=array();
		if(!$this->rawResponse) $errors[]='RESPONSE EMPTY';
		else {
			$ret=preg_split('/[\r\n]+/',$this->rawResponse);
			foreach($ret as &$line) {
				//$this->debug("< $line\n");
				if (preg_match('/^(...)(.)(.*)$/i', $line, $regs)) {
					if($reqCode==$regs[1]) $out[]=trim($regs[3]);
					else $errors[]=$line;
				}
			}
		}
		if($errors) {
			$this->setError('ERROR COMMAND: '.$command);
			$this->setError('EXPECTED CODE: '.$reqCode);
			foreach($errors as $line) $this->setError($line);
			return false;
		}
		return $out;
    }
	private function sendAUTH_LOGIN(){
		return	$this->sendCommand(334,'AUTH LOGIN') &&
				$this->sendCommand(334,base64_encode($this->user)) &&
				$this->sendCommand(235,base64_encode($this->password));
	}
	private function sendAUTH_CRAM_MD5(){
		$challenge=base64_decode(substr($this->rawResponse, 4));
		$response=$this->user.' '.$this->hmac($challenge, $this->password);
 		return	$this->sendCommand(334,'AUTH CRAM-MD5') &&
				$this->sendCommand(235, base64_encode($response));
	}
	private function sendAUTH_NTML(){
		$ntlm=new NTLM_SASL_Client;
		if($ntlm->errors) return $this->setError($ntlm->errors);
		$host=$this->client?$this->client:$this->getHost();
		$msg1=$ntlm->TypeMsg1($this->realm, $host);
		if(!$this->sendCommand(334,'AUTH NTLM ' . base64_encode($msg1))) return false;
		
		//Though 0 based, there is a white space after the 3 digit number
		$challenge=substr($this->rawResponse, 3);
		$challenge=base64_decode($challenge);
		$ntlm_res=$ntlm->NTLMResponse(substr($challenge, 24, 8),$this->password);
		$msg3=$ntlm->TypeMsg3($ntlm_res,$this->user,$this->realm,$host);
		
		return $this->sendCommand(235, base64_encode($msg3));
	}
	private function sendAUTH_PLAIN(){
		return	$this->sendCommand(334,'AUTH PLAIN') &&
				$this->sendCommand(235,base64_encode("\0" . $this->user . "\0" . $this->password));
	}
	private function startSend($hello){
		$crypt=$this->crypt;
		$parameters=$this->sendCommand(250,$hello);
		if(!$parameters) return false;
		$this->options=array();
		foreach($parameters as $op) if(preg_match('/^([A-Z]+) *(.*)$/',$op,$ret)) $this->options[$ret[1]]=@$ret[2];

		if($crypt=='AUTO') {
			if(array_key_exists('STARTTLS', $this->options)) $crypt='TLS';
			//elseif(array_key_exists('STARTSSL', $this->options)) $crypt='SSL';
			else $crypt='';
		}
		
		if($crypt=='') return $parameters;
		if($crypt=='TLS') $this->sendCommand(220,'STARTTLS');
		elseif($crypt=='SSL') ;//$this->sendCommand(220,'STARTTLS');
		return $this->sendCommand(250,$hello);
	}
	private function receiveAuthMethod(){
		$method=$this->logonMethod;
		if(!$method) return false;
		if(!array_key_exists('AUTH',$this->options)) return $this->setError('There are not any method to auth on the server');
		$auth=preg_split('/\s+/',$this->options['AUTH']);
		if($method=='AUTO') $method=reset($auth);
		if(in_array($method, $auth)) return $method;
		if($this->forceLogonMethod) return reset($auth);
		return $this->setError('Auth method choiced was $method. There are only: '.$this->options['AUTH']);
	}
	private function makeHeaders(){
		$headers=array();
		if ($this->headers) {
			$lastheader = null;
			$h=preg_split('/[\n\r]+/', $this->headers);
			foreach ($h as $header) {
				if (preg_match('/\s*([^:]+)\s*:\s*(.*)/i', $header, $ret)) {
					$name = trim($ret[1]);
					$lastheader = strtoupper($name);
					$headers[$lastheader] = array($name, trim($ret[2]));
				}elseif ($lastheader && preg_match('/^([ \t]+)(.*)/i', $header, $ret)) $headers[$lastheader][1].='; '.trim($ret[2]);
			}
		}
		//Parser
		$aParserHeaders=array(
			'MIME-Version'=>'version',
			'X-Priority'=>'priority','X-X-MSMail-Priority'=>'priorityStr',
			'From'=>'from','To'=>'to','Reply-To'=>'replyTo','Cc'=>'cc','Bcc'=>'bcc',
			'Return-Path'=>'returnPath','Return-Receipt-To'=>'returnReceiptTo',
			'X-Sender'=>'xSender',
			'Subject'=>'subject',
		);

		$headerlist=array();
		foreach($aParserHeaders as $k=>$v) {
			$ku=strtoupper($k);
			if (isset($headers[$ku])) {
				$this->$v=$headers[$ku];
				unset($headers[$ku]);
			}
			$value=$this->$v;
			$ok=false;
			if($k=='Subject') $ok=true;
			elseif(is_object($value)) $ok=(bool)$value->count();
			else $ok=(bool)$value;
			if($ok) $headerlist[]="$k: $value";
		}
		foreach ($headers as $k=>$v) $headerlist[] = "{$v[0]}: {$v[1]}";
		return implode("\r\n", $headerlist)."\r\n";
	}
	private function debug($text){ if(self::$debug) print $text; }
}
