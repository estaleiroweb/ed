<?php
/**
* X_MailBox
* PHP Version 5.x
* @package X
* @link http://estaleiroweb.com.br/X/scripts
* @author Helbert Fernandes <helbertfernandes@gmail.com>
* @copyright 0000 - 0000 Helbert Fernandes
* @license http://estaleiroweb.com.br/X/license GNU Lesser General Public License
* @note This program is distributed in the hope that it will be useful - WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
* FITNESS FOR A PARTICULAR PURPOSE.
*/
class X_MailBox extends OverLoadElements {
	/**
	* The EMail class Version number.
	* @type string
	*/
	const VERSION = '2.0';
	/**
	* It will be showed or not step-by-step the mail process
	* @type boolean
	*/
	static public $debug=false;
	/**
	* The method which the email will be passed.
	* @options 0:mail, 1:smtp, 2:sendmail, 3:qmail
	* @type integer
	*/
	private $engine=0;
	/**
	* The most as possible of the default configuration written by ini file or array throw parameter 
	* @type array
	*/
	protected $protected=array(
		/**
		* Show errors.
		* @type boolean
		*/
		'showErrors'      =>false,
		/**
		* Email priority.
		* @options  0:Null, 1:High, 3:Normal, 5:Low, 2:Low
		* @note When null or 0, the header is not set at all.
		* @type integer
		*/
		'priority'        =>0,
		/**
		* The character set of the message.
		* @note The other value useful is UTF-8
		* @type string
		*/
		'charset'         =>'iso-8859-1',
		/**
		* The MIME Content-type of the message.
		* @note The other value useful is text/html
		* @type string
		*/
		'contentType'     =>'text/plain',
		/**
		* The message encoding.
		* @options "7bit", "8bit", "binary", "base64", and "quoted-printable".
		* @type string
		*/
		'encoding'        =>'8bit',
		/**
		* The From email address for the message.
		* @note It cam be only email too. Ex: root@localhost
		* @note When this one contain special chars in the name it will must be used quote. Ex: "root (a root@user)" <root@localhost>
		* @type string
		*/
		'from'            =>'root <root@localhost>',
		/**
		* The Sender email of the message.
		* If not empty, will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
		* @type string
		*/
		'returnPath'      =>'',
		/**
		* The Subject of the message.
		* @type string
		*/
		'replyTo'         =>'',
		'returnReceiptTo' =>'',
		'xSender'         =>'',
		'subject'         =>'',
		'to'              =>'',
		'cc'              =>'',
		'cco'             =>'', //bcc
		'version'         =>'1.0',

		'smtp_server'          =>'200.184.26.12', //intspo1012 servidor de e-mail
		'smtp_port'            =>25,
		
		'headers'         =>'',
		'messages'        =>array(), //message
		'files'           =>array(), //file
	);
	
	/**
	* X_MailBox instance by 3 ways form
	* @example $obj=new X_MailBox();
	* @example $obj=new X_MailBox('/full_path/contact/email.ini');
	* @example $obj=new X_MailBox(array('engine'=>'smtp','smtp_server'=>'0.0.0.0','from'=>'my@x_mailbox.com'));
	*/
	public function __construct($arg=null,$perfil='default'){
		if(is_string($arg)) $config=loadFileDefault($arg);
		elseif(is_array($arg)) $config=$arg;
		else $config=null;
		if(is_null($arg) || !$config) $config=loadFileDefault('mailBox.ini');
		if($config) {
			if(array_key_exists($perfil,$config)) $config=$config[$perfil];
			elseif(!array_key_exists('engine',$config)) $config=reset($config);
			foreach($config as $k=>$v) {
			}
		}
	}
	/**
	* Create a New Email to send
	* @note If the parameters to, subject and body was passed this one will be auto send without $obj->send() method.
	* @note All parameters cam be passed by associative array.
	* @example $obj=$X_MailBox->new(); $obj->body='example';$obj->to='my@x_mailbox.com';$obj->send();
	* @example $obj=$X_MailBox->new(); $obj->body='example';$obj->send('my@x_mailbox.com');
	* @example $obj=$X_MailBox->new(array('to'=>$to,'subject'=>$subject,'body'=>$body,'priority'=>$priority,'from'=>$from,'cc'=>$cc,'cco'=>$cco,'type'=>$type));
	*/
	public function new($options=null){
		//$this->connect();
		if(is_array($options)) {
		}
	}
/*
$a=array('to'=>$to,'subject'=>$subject,'body'=>$body,'priority'=>$priority,'from'=>$from,'cc'=>$cc,'cco'=>$cco,'type'=>$type='html|text|rich',$charset,$autoSend=true);

$e=new X_MailBox(['/full_path/contact/email.ini']); // || $e=EMail::{'/full_path/contact/email.ini'}() || new XMailBox($array_cfg);
$x=$e->new();
$x=$e->new($a);
$x->send(array $to| $to | null);

$y=$e->receive(); // array(id=>email,id=>email,...)
$e->remove($id);
$e->reply($id,$a); //$to already filled, but cam be added
$e->replyAll($id,$a); //$to,$cc,$cco already filled, but cam be added
$e->forward($id,$a);
$e->close();
*/
}
class yyy {
	/**
	* The PHPMailer Version number.
	* @type string
	*/
	const VERSION='2';
	private $protect=array(
		'engine'          =>2,                       // [integer] {0:mail <default>, 1:smtp, 2:sendmail, 3:qmail}
		'priority'        =>0,                       // [integer] Email priority. {0:NULL <default>, 1:High, 2:Low, 3:Normal}
		'charset'         =>'iso-8859-1',            // [string]  The character set of the message. {"iso-8859-1"  <default>, "UTF-8", ...}
		'contentType'     =>'text/plain',            // [string]  The MIME Content-type of the message. {"text/plain" <default>, "text/html", ...}
		'encoding'        =>'8bit',                  // [string]  The message encoding. {"7bit", "8bit" <default>, "binary", "base64", "quoted-printable"}

		'from'            =>'root <root@localhost>', // [string]  The From email address for the message
		'server'          =>'200.184.26.12', //intspo1012 servidor de e-mail
		'port'            =>25,
		'version'         =>'1.0',
		'to'              =>'',
		'cc'              =>'',
		'bcc'             =>'', //cco
		'replyTo'         =>'',
		'returnPath'      =>'',
		'returnReceiptTo' =>'',
		'xSender'         =>'',
		'subject'         =>'',
		'headers'         =>'',
		'messages'        =>array(), //message
		'files'           =>array(), //file
		'alarm'           =>false,
		'debug'           =>false,
	);
	private $error=array();
	private $senders=array('php_mail','bash_sendmail','sokect');
	private $priorities=array('','High','Low','Normal');
	private $retCmd=array('code'=>null,'msg'=>null,'err'=>null);
	private $boundary;

	public function __construct($p_array=false) {
		$this->boundary.=md5(date('r', time()));
		if (is_array($p_array)) foreach($p_array as $k=>$v) $this->$k=$v;
		//if($this->to) $this->mail();
	}
	public function __set($nm,$val){
		$fn='set'.ucfirst($nm);
		if(method_exists($this,$fn)) $this->$fn($val);
		elseif(isset($this->protect[$nm])) $this->protect[$nm]=$val;
	}
	public function __get($nm){
		$fn='get'.ucfirst($nm);
		if(method_exists($this,$fn)) return $this->$fn();
		elseif(isset($this->protect[$nm])) return $this->protect[$nm];
	}
	public function setFrom($val=false){ 
		if(!$val) $this->protect['from']='anonimo';
		elseif($this->isEmail($val,false)) $this->protect['from']=$val; 
	}
	public function setTo($val=false){ $this->_setMail('to',$val); }
	public function setReplyTo($val=false){ $this->_setMail('replyTo',$val); }
	public function setCc($val=false){ $this->_setMail('cc',$val); }
	public function setBcc($val=false){ $this->_setMail('bcc',$val); }
	public function setCco($val=false){ $this->setBcc($val); }
	public function setReturnPath($val=false){ $this->_setMail('returnPath',$val); }
	public function setReturnReceiptTo($val=false){ $this->_setMail('returnReceiptTo',$val); }
	public function setXSender($val=false){ $this->_setMail('xSender',$val); }
	public function setError($val=false){ 
		if(!$val) $this->error=array();
		else $this->error[]=$val; 
	}
	public function setAlarm($val=true){ $this->protect['alarm']=(bool)$val; }
	public function setDebug($val=true){ $this->protect['debug']=(bool)$val; }
	public function setSender($val=0){ 
		if(preg_match('/(php_mail)|(bash_sendmail)|(sokect)/i',$val,$ret)) $this->protect['engine']=count($ret)-1;
		else{ 
			$val=(int)$val;
			if($val>0 && $val<3) $this->protect['engine']=(int)$val;
		}
	}
	public function setPriority($val=0){
		if(!$val) $this->protect['priority']=0;
		elseif(preg_match('/(High|Alta)|(Low|Baixa)|(Normal)/i',$val,$ret)) $this->protect['priority']=count($ret)-1;
		else{ 
			$val=(int)$val;
			if($val>0 && $val<4) $this->protect['priority']=(int)$val;
		}
	}
	public function setPriorityStr($val=''){ $this->setPriority($val); }
	public function setMessages($val=''){ }
	public function setMessage($val='',$contentType=false,$charset=false){ 
		$this->protect['messages']=array();
		if($val) $this->addMessage($val,$contentType,$charset);
	}
	public function setFile($val='',$data=false){ 
		$this->protect['files']=array();
		if($val) $this->addFile($val,$data);
	}
	public function getError(){ return $this->error; }
	public function getPriorityStr() { return @$this->priorities[$this->protect['priority']]; }
	public function getSenders() { return $this->senders; }
	public function getSender() { return $this->protect['engine'].': '.$this->senders[$this->protect['engine']]; }
	public function getPriorities() { return $this->priorities; }
	public function addTo($val){ $this->_addMail('to',$val); }
	public function addReplyTo($val){ $this->_addMail('to',$val); }
	public function addCc($val){ $this->_addMail('cc',$val); }
	public function addBcc($val){ $this->_addMail('bcc',$val); }
	public function addCco($val){ $this->addBcc($val); }
	public function addReturnPath($val){ $this->_addMail('returnPath',$val); }
	public function addReturnReceiptTo($val){ $this->_addMail('returnReceiptTo',$val); }
	public function addXSender($val){ $this->_addMail('xSender',$val); }
	public function addMessage($val,$contentType=false,$charset=false){ 
		$this->protect['messages'][]=new EMailMessage($val,$contentType?$contentType:$this->protect['contentType'],$charset?$charset:$this->protect['charset']); 
	}
	public function addFile($val,$data=false){ $this->protect['files'][]=new EMailFile($val,$data); }

 	public function send() {
		$headers=$this->makeHeaders();
		//Check
		if (!$this->to) $this->setError('Não existe destinatário');
		if ($this->alarm) {
			if(!$this->from=='anonimo') $this->setError('Não existe remetente');
			if(!$this->subject) $this->setError('Não existe assunto');
			if(!$this->message) $this->setError('Não existe mensagem');
		}
		if($this->error) return false;

		$lf="\r\n";
		if(count($this->protect['messages'])>1) { //Multiplas mensagens
			$boundary=$this->boundary.'-alt';
			$headerBoundary='Content-Type: multipart/alternative; boundary="'.$boundary.'"';
			if($this->protect['files']) $body=$headerBoundary.$lf.$lf;
			else {
				$body='';
				$headers.=$lf.$headerBoundary;
			}
			foreach ($this->protect['messages'] as $m) $body.="--$boundary$lf$m$lf";
			$body.="--$boundary--$lf";
		}elseif($this->protect['messages']) { //Uma mensagem
			$m=reset($this->protect['messages']);
			if($this->protect['files']) $body="$m$lf";
			else {
				$h=$m->getHeader();
				if($h) $headers.=$lf.$h;
				$body=$m->message.$lf;
			}
		} else $body=$lf; //Nenhuma mensagem
		if($this->protect['files']) { //Arquivos
			$boundary=$this->boundary.'-mixed';
			$headers.=$lf.'Content-Type: multipart/mixed; boundary="'.$boundary.'"';
			$body="--$boundary$lf$body";
			foreach ($this->protect['files'] as $data) $body.="--$boundary$lf$data$lf";
			$body.="--$boundary--$lf";
		}
		if($this->protect['engine']==2) { //Envio by socket
			return $this->sendBySocket($headers.$lf.$lf.$body.'.');
		}
		elseif($this->protect['engine']==1) { //Envio by bash command sendmail
			$cmd='echo "'.str_replace('"','\"',$headers.$lf.$lf.$body).'" | sendmail -t > /dev/null &';
			$this->pr("$cmd\n");
			$this->pr(`$cmd`);
			return true;
		} 
		else { //Envio by php command mail
			return @mail($this->to,$this->subject,$body,$headers);
		}
	}

	private function _setMail($nm,$val=false){ 
		if(!$val) $this->protect[$nm]='';
		elseif($this->isEmail($val)) $this->protect[$nm]=$val;
	}
	private function _addMail($nm,$val){ 
		$fn='set'.ucfirst($nm);
		$this->$fn($this->protect[$nm].(($this->protect[$nm] && $val)?'; ':'').$val);
	}
	private function isEmail(&$email,$multi=true){
		$aEmail=preg_split('/\s*[,;]\s*/',trim($email));
		$ok=true;
		$out=array();
		$er='[0-9a-z][0-9a-z\-_]*';
		$erMail="$er(?:\.$er)*@$er(?:\.$er)+";
		foreach($aEmail as $v) if(preg_match("/^(?:($erMail)|(.+)<($erMail)>)$/i",$v,$ret)) $out[]=@$ret[2].'<'.@$ret[1].@$ret[3].'>';
		else $ok=false;
		if(!$multi && count($out)>1) $ok=false;
		if($ok) $email=implode('; ',$out);
		return $ok;
	}
	private function sendBySocket($body){
		$conn=@fsockopen($this->server, $this->port, $errno, $errstr, 30);
		if (!$conn) {
			$this->error[]=$errstr;
			return false;
		}
		$aCmd=array();
		$aCmd[]=array(220,null);
		$aCmd[]=array(250,'HELO '.$this->server);
		$aCmd[]=array(250,'MAIL FROM: '.preg_replace('/.*<(.*?)>.*/','<\1>',$this->from));
		$receivers=array('to','cc','bcc');
		foreach($receivers as $k) if(($to=$this->$k)) {
			if(!preg_match_all('/<.*?>/',$to,$ret)) $ret=array(array($to));
			foreach($ret[0] as $to) $aCmd[]=array(250,'RCPT TO: '.$to);
		}
		$aCmd[]=array(354,'DATA');
		$aCmd[]=array(250,str_replace("\r\n.\r\n","\r\n\\.\r\n",$body));
		$aCmd[]=array(221,'QUIT');

		$ok=true;
		foreach($aCmd as $v) if(!$this->command($conn, $v[1]) || $this->retCmd['code']!=$v[0]) {
			$ok=false;
			break;
		}
		@fclose($conn);
		return $ok;
	}
	private function command($conn, $command=null) {
		$this->retCmd=array('code'=>null,'msg'=>null,'err'=>null);
		if ($command) {
			$this->pr("> $command\n");
			fwrite($conn, "$command\n");
		}
		while ($line = fgets($conn)) {
			if (!$line) return false;
			if (preg_match('/(...)(.)(.*)/i', $line, $regs)) {
				$this->retCmd=array('code'=>$regs[1],'msg'=>trim($regs[3]) ,'err'=>trim($regs[2]));
				$this->pr("< {$this->retCmd['code']} {$this->retCmd['msg']}\n");
				if ($this->retCmd['err']=='') return true;
			}else return false;
		}
		return false;
    }
	private function makeHeaders(){
		$headers = array();
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
		$headerlist=array();
		$aParserHeaders=array(
			'MIME-Version'=>'version',
			'X-Priority'=>'priority','X-X-MSMail-Priority'=>'priorityStr',
			'From'=>'from','To'=>'to','Reply-To'=>'replyTo','Cc'=>'cc','Bcc'=>'bcc',
			'Return-Path'=>'returnPath','Return-Receipt-To'=>'returnReceiptTo',
			'X-Sender'=>'xSender',
			'Subject'=>'subject',
		);

		foreach($aParserHeaders as $k=>$v) {
			$ku=strtoupper($k);
			if (isset($headers[$ku])) {
				$this->$v=$headers[$ku];
				unset($headers[$ku]);
			}
			$value=$this->$v;
			if($value || $k=='Subject') $headerlist[]="$k: $value";
		}
		foreach ($headers as $k=>$v) $headerlist[] = "{$v[0]}: {$v[1]}";
		return implode("\r\n", $headerlist);
	}
	private function pr($text){ if($this->protect['debug']) print $text; }
}
class EMailMessage{
	public $contentType, $message, $charset, $encoding;
	
	public function __construct($message,$contentType='text/plain',$charset='iso-8859-1',$encoding='8bit') { 
		$this->message=$message;
		$this->contentType=$contentType;
		$this->charset=$charset;
		$this->encoding=$encoding;
	}
	public function __toString(){
		$lf="\r\n";
		return	$this->getHeader().$lf.$lf.$this->message.$lf;
	}
	public function getHeader(){
		$out=array();
		if($this->contentType) $out[]="Content-Type: {$this->contentType}".($this->charset?"; charset=\"{$this->charset}\"":'');
		if($this->encoding) $out[]="Content-Transfer-Encoding: {$this->encoding}";
		return implode("\r\n",$out);
	}
}
class EMailFile{
	public $contentType, $charset, $data;
	private $filename;

	function __construct($filename,$data=false) {
		$this->data=(!$data && is_file($filename))?file_get_contents($filename):$data;
		$p=pathinfo($filename);
		$this->filename=$p['basename'];
		$ext=$p['extension'];
		if(isset(MimeType::$content_types[$ext])) $this->contentType=MimeType::$content_types[$ext];
		else $this->contentType="application/$ext";
	}
	function __toString(){
		$lf="\r\n";
		return	"Content-Type: {$this->contentType}; name=\"{$this->filename}\"$lf".
				'Content-Transfer-Encoding: base64'.$lf.
				'Content-Disposition: attachment'.$lf.$lf.
				chunk_split(base64_encode($this->data)).$lf;
	}
}
