<?php
class EMailMessage extends EMailArray {
	public $charset;  //ISO-8859-1 | US-ASCII | UTF-8
	public $encoding; //8bit | quoted-printable
	private $options=array();
	
	public function __construct($message=null,$type=null,$charset=null,$encoding=null) {
		$this->set($message,$type,$charset,$encoding);
	}
	public function __toString(){
		$message=$this->__invoke();
		$count=count($message);
		if($count==0) return '';
		if($count==1) return $message[0];
		$boundary=md5(date('r', time())).'-alt';
		$separator='--'.$boundary.self::$lf;
		$out='Content-Type: multipart/alternative; boundary="'.$boundary.'"'.self::$lf.self::$lf;
		$out.=$separator;
		$out.=implode($separator,$message);
		$out.='--'.$boundary.'--'.self::$lf;
		return $out;
	}
	public function __invoke(){
		$message=array();
		foreach($this->args as $type=>$body) {
			$charset=@$this->options[$type]['charset'];
			$encoding=@$this->options[$type]['encoding'];
			$header='Content-Type: '.$type.($charset?'; charset="'.$charset.'"':'').self::$lf;
			if($encoding) $header.='Content-Transfer-Encoding: '.$encoding.self::$lf;
			$message[]=$header.self::$lf.$body.self::$lf;
		}
		return $message;
	}
	public function offsetSet($index, $value){                                      // implements by ArrayAccess
		$this->debug();
		if(!$value) return;
		if(!$index || is_numeric($index)) $index='text/plain';
		if(is_object($value)) $value=(array)$value;
		if(is_array($value)) $value=implode("\n",$value);
		if(!is_string($value)) $value=(string)$value;
		$this->args[$index]=$value;
		$this->options[$index]=array('charset'=>$this->charset,'encoding'=>$this->encoding);
		return $value;
	}
	public function set($message=null,$type=null,$charset=null,$encoding=null){
		$this->charset=$charset;
		$this->encoding=$encoding;
		if($message) $this->offsetSet($type, $message);
	}
}
