<?php
class Tag {
	const TAB="\t";
	const TAB_INIT='';
	const LF="\n";
	protected $protect=array(
		'tag'=>'div',
		'content'=>'',
	);
	protected $attributes=array();
	
	public function __construct($tag=null){
		$this->tag=$tag;
	}
	public function __invoke(){
		$args=func_get_args();
		if($args) $this->content=count($args)==1?$args[0]:$args;
		return $this->__toString();
	}
	public function __toString(){
	//print __FUNCTION__.' '.__LINE__.':'.$this->content.'ssssssssssssssssssssss<br>';
		$tabIni=self::TAB;
		$out=$this->startTag($tabIni);
		$out.=$this->trContent($this->content,$tabIni.self::TAB);
		$out.=$this->endTag($tabIni);
		return $out;
	}
	public function __get($name){
		if(method_exists($this,$fn='get'.ucfirst($name))) return $this->$fn();
		elseif(array_key_exists($name,$this->protect)) return $this->protect[$name];
		elseif(array_key_exists($name,$this->attributes)) return $this->attributes[$name];
	}
	public function __set($name,$value=null){
		if(method_exists($this,$fn='set'.ucfirst($name))) $this->$fn($value);
		elseif(array_key_exists($name,$this->protect)) {
			if(!is_null($value)) $this->protect[$name]=$value;
		} else $this->attr($name,$value);
		return $this;
	}
	public function setClass($value=''){
		$this->attributes=$this->trValue($value);
		return $this;
	}
	public function removeAttr($attr){
		if(array_key_exists($attr,$this->attributes)) unset($this->attributes[$attr]);
		return $this;
	}
	public function attr($attr,$value=null){
		$this->attributes[$attr]=$this->trValue($value);
		return $this;
	}
	public function add($attr,$value){ //Add attribute value
		if(!array_key_exists($attr,$this->attributes)) $this->attributes[$attr]='';
		if($this->attributes[$attr]!='') $this->attributes[$attr].=' ';
		$this->attributes[$attr].=$this->trValue($value);
		return $this;
	}
	public function drop($attr,$value){ //Drop attribute value
		if(array_key_exists($attr,$this->attributes)) {
			$this->attributes[$attr]=trim(preg_replace('/ +'.preg_quote($value,'/').' +/',' ',' '.$this->attributes[$attr].' '));
		}
		return $this;
	}
	
	protected function trValue($value){
		if(is_object($value)) $value=(array)$value;
		if(is_array($value)) $value=implode(' ',$value);
		elseif(is_bool($value)) $value=(int)$value;
		return preg_replace('/  +/',' ',trim($value));
	}
	protected function trContent($value,$tabIni="\t"){
		if(is_object($value)) {
			$out=$value instanceof stdClass?$this->trContent((array)$value,$tabIni):"$value";
		} elseif(is_array($value)) {
			$out='';
			foreach($value as $k=>$v) {
				$v=$this->trContent($v).self::LF;
				$out.=is_numeric($k)?$v:'<label>'.self::LF.$k.$v.'</label>'.self::LF;
			}
			return $out;
		} elseif(is_bool($value)) $out=(int)$value;
		else $out=$value;
		return $tabIni.$out.self::LF;
	}
	protected function startTag($tabIni="\t"){
		$out=$tabIni.'<'.$this->tag;
		foreach($this->attributes as $k=>$v) $out.=' '.$k.'="'.htmlspecialchars($v,ENT_QUOTES).'"';
		$out.='>'.self::LF;
		return $out;
	}
	protected function endTag($tabIni="\t"){
		return $tabIni.'</'.$this->tag.'>'.self::LF;
	}
}