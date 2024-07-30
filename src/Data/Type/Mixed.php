<?php
namespace Type;

abstract class Mixed implements \Interfaces\Type {
	protected $value=null;
	protected $raw=null;
	protected $type='null';
	protected $clas=null;
	protected $isNull=true;
	
    final public static function init($value=null) { 
		$class=__CLASS__;
		return new $class($value); 
	}
	final public function __construct($value=null){ $this->_init($value); }
	final public function __invoke($value=null){
		if(is_null($value)) return $this->value;
		$this->_init($value);
	}
	final public function __get($name){
		if($name=='value' || $name=='val') return $this->value;
		if($name[0]!='_' && method_exists($this,$name)) return $this->$name();
		trigger_error("There isn't $name property");
	}
	final public function __set($name,$value){ 
		if($name=='value' || $name=='val') $this->_init($value);
		trigger_error("There isn't $name property");
	}
	final public function __toString(){ return $this->toStringRaw(); }
	
	final private function _init($value){
		$this->isNull=is_null($value);
		$this->raw=$value;
		$this->type=gettype($value);
		if($this->type=='object') {
			$this->class=get_class($value);
			if(get_class($this)==$this->class) return $this->value=$value->value();
			if(substr($this->class,0,strlen(__NAMESPACE__)+1)==__NAMESPACE__ .'\\') $value=$value->value;
		}
		$this->value=$this->_cast($value);
	}
	final protected function _default($value=null,$default=''){
		return is_null($value)?$default:$value;
	}
	
	final public function value()         { return $this->value; }
	final public function val()           { return $this->value; }
	final public function is_null()       { return $this->isNull; }
	final public function type()          { return get_class($this); }
	final public function raw()           { return $this->raw; }
	final public function rawType()       { return $this->type; }
	final public function rawClass()      { return $this->class; }
	
	final public function toString()      { return new String($this->value); }
	final public function toMap()         { return new Map($this->value); }
	final public function toBoolean()     { return new Boolean($this->value); }
	final public function toBool()        { return new Boolean($this->value); }
	final public function toNumber()      { return new Number($this->value); }
	final public function toInteger()     { return new Integer($this->value); }
	final public function toFloat()       { return new Float($this->value); }
	final public function toObject()      { return new Object($this->value); }
	final public function toDateTime()    { return new DateTime($this->value); }
	final public function toDate()        { return new Date($this->value); }
	final public function toTime()        { return new Time($this->value); }
	final public function toTimeStamp()   { return new TimeStamp($this->value); }
	
	final public function toStringRaw()   { return $this->toString()->value; }
	final public function toArray()       { return $this->toMap()->value; }
	final public function toArrayRaw()    { return $this->toMap()->value; }
	final public function toBoolRaw()     { return $this->toBool()->value; }
	final public function toBooleanRaw()  { return $this->toBoolean()->value; }
	final public function toNumberRaw()   { return $this->toNumber()->value; }
	final public function toObjectRaw()   { return $this->toObject()->value; }
	final public function toDateTimeRaw() { return $this->toDateTime()->value; }
	final public function toDateRaw()     { return $this->toDate()->value; }
	final public function toTimeRaw()     { return $this->toTime()->value; }
	final public function toTimeStampRaw(){ return $this->toTimeStamp()->value; }

	final public function len()           { return $this->length(); }
	final public function count()         { return $this->length(); }
	final public function size()          { return $this->length(); }
	final public function sizeOf()        { return $this->length(); }
	public function length()              { return strlen($this->value); }
	
	abstract protected function _cast($value);

	public function _DateTime(){
		switch ($this->type) {
			case 'null':  case 'string': return $this->value;
			case 'array': case 'object': return var_export($this->value,true);
		}
		return $this->value;
	}
	public function _Date(){
		switch ($this->type) {
			case 'null':  case 'string': return $this->value;
			case 'array': case 'object': return var_export($this->value,true);
		}
		return $this->value;
	}
	public function _Time(){
		switch ($this->type) {
			case 'null':  case 'string': return $this->value;
			case 'array': case 'object': return var_export($this->value,true);
		}
		return $this->value;
	}
	public function _TimeStamp(){
		$value=$this->value;
		switch ($this->type) {
			case 'null':  return 0;
			case 'object': $value=(array)$value;
			case 'array':  $value=preg_replace('/[\r\n]\s*\[([\'"])*.?\1\]\s*=>\s*/','',print_r($value,true));
			case 'string': break;
			default: $value=(string)$value;
		}
		return preg_replace('/\D/',$value);
	}
}