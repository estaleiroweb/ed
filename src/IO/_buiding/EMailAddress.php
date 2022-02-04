<?php
class EMailAddress extends EMailArray {
	public $limit=0;
	
	public function __toString() { 
		$out=array();
		foreach($this->args as $email=>$name) {
			if($name) $email=' <'.$email.'>';
			$out[]=$name.$email;
		}
		return implode('; ',$out);
	}
	public function email() { 
		return implode('; ',array_keys($this->args));
	}
	public function offsetSet($index, $value){                  // implements by ArrayAccess
		$this->debug();
		if(!$value) {
			if(!$index || is_numeric($index)) return;
			$value=$index;
			$index='';
		}
		if(is_string($value)) {
			if(preg_match('/[,;]/',$value)) return $this->offsetSet($index, preg_split('/\s*[,;]\s*/',$value));
			if(preg_match('/[,;]/',$index)) return $this->offsetSet($index, preg_split('/\s*[,;]\s*/',$index));
			if(!$this->checkEmail($index,$value)) return;
			if($this->limit!=0 && count($this->args)>=$this->limit) $this->pop();
			$this->args[$index]=$value;
			return $index;
		}
		elseif(is_array($value)) {
			$out=array();
			foreach($value as $k=>$v) if(($ret=$this->offsetSet($k, $v))) {
				if(is_array($ret)) $out=array_merge($out,$ret);
				else $out[]=$ret;
			}
			return $out;
		}
		elseif(is_object($value)) return $this->offsetSet($index, (array)$value);
	}
	public function emails() { return array_keys($this->args); }
	protected function checkEmail(&$email,&$name){
		$er='[0-9a-z][0-9a-z\-_]*';
		$erMail="/$er(?:\.$er)*@(?:$er(?:\.$er)+|(?:\d{1,3}\.){3}\d{1,3})/i";
		
		$tests=array($name=>$email,$email=>$name);
		$email=$name='';
		foreach($tests as $key=>$value) if($value) {
			if(preg_match($erMail,$value,$ret)) {
				$email=$ret[0];
				$name=trim($key && !is_numeric($key)?$key:preg_replace(array('/[<>@-]+/','/ {2,}/',),array('',' ',),str_replace($email,'',$value)));
				return $email;
			}
		}
		return false;
	}
}
