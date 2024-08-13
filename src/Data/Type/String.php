<?php
namespace Type;

class String extends Mixed implements \Interfaces\Type {
	protected function _cast($value){
		if($this->isNull) return '';
		if($this->type=='string') return $value;
		if($this->type=='array') return var_export($value,true);
		if($this->type=='object') return var_export($value,true);
		return (string)$value;
	}

	/*Normal methods*/
	public function addcslashes($charlist=null){ 
		return addcslashes($this->value,$this->_default($charlist,"\x00..\x1F\x7F..\xFF\x22\x27\2F\x5C"));
	}
	public function addslashes(){ return addslashes($this->value); }
	public function bin2hex(){ return bin2hex($this->value); }
	
	public function explode($delimiter=null,$limit=null){return explode($this->_default($delimiter,','),$this->value,$limit);}
	public function split($pattern=null,$limit=null) { return $this->preg_split($pattern,$limit); }
	public function preg_split($pattern=null,$limit=null,$flags=null){
		return preg_split($this->_default($pattern,'/\s*[,;]\*/'),$this->value,$limit,$flags);
	}
	
	/*Extended methods*/
	public function dump(){ //FIXME
		return ($out=@eval("return {$this->value};"))?$out:$this->value;
	}
	public function fromArray(array $value){ //FIXME
		$out=@eval("return $value;");
		return $out?$out:preg_split('/\s*[\n\r]+\s*/',$value);
	}
}