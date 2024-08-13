<?php
class EMailArray implements Iterator, Countable, ArrayAccess {
	static public $debug=false;
	static $startedId;
	protected static $lf="\r\n";
	protected $args=array();
	
	public function __construct(){
		$args=func_get_args();
		if($args) $this->offsetSet(null, $args);
	}
	public function __get($name)              { return $this->offsetGet($name); }
	public function __set($name,$value)       { $this->offsetSet($name,$value); return $this; }
	public function __toString()              { return json_encode($this->args); }
	public function __invoke()                { return $this->args; }
	public function __debugInfo()             { return $this->args; }
    public function __sleep()                 { return array('args'); }
	public function __isset($name)            { return $this->offsetExists($name); }
	public function __unset($name)            { if($this->__isset($name)) unset($this->args[$name]); }
    public function __wakeup()                {}
	
	//*begin main methods
	public function data()                    { return $this->args; }
	public function json()                    { return json_encode($this->args); }
	public function url($prefix=null, $separator='&'){ return http_build_query($this->args, $prefix, $separator); }
	public function order(){
		$out=array();
		foreach($this->args as $k=>$v) $out[]='`'.$k.'`'.($v?' '.$v:'');
		return implode(', ',$out);
	}
	public function tag($tag='div',$argId='id', $attr=null){
		$out=array();
		$attr=(array)$attr;
		foreach($this->args as $id=>$obj) {
			$a=array($argId.'="'.htmlentities($id,ENT_QUOTES).'"');
			
			if(is_object($obj)) {
				foreach($obj as $k=>$v) if($k!='content') $a[]=$k.'="'.htmlentities($v,ENT_QUOTES).'"';
				$content=@$obj->content;
			}
			if(is_array($obj)) {
				$content=array();
				foreach($obj as $v) $content[]="\t<$tag>".htmlentities($v)."</$tag>\n";
				$content=implode('',$content);
			}
			else $content=htmlentities((string)$obj);
			$a=array_merge($a,$attr);
			$a=$a?' '.implode(' ',$a):'';
			$out[]="\t<$tag$a>$content</$tag>";
		}
		return implode("\n",$out);
	}
	protected function _checkAutoIncrement($index) {               //Get a next index
		if(is_null($index)) $index=($this->lastId+=is_null($this->lastId)?0:1);
		elseif(is_numeric($index)) $this->lastId=max($this->lastId,(int)$index);
		$this->debug("INDEX=$index/MAX={$this->lastId}");
		return $index;
	}
	protected function _new($value=array()) {                 //Create a new class
		if(!is_array($value)) $value=(array)$value;
		$parm=array();
		foreach($value as $k=>$v) $parm[]='$value["'.$k.'"]';
		$obj=eval('return new '. get_called_class() .'('.implode(', ',$parm).');');
		if($obj instanceof _) {
			$obj->readOnly['caller']=$this;
		}
		return $obj;
	}
	public function debug($text=null){
		if(!self::$debug) return;
		if(is_null($text)){
			$bt=debug_backtrace();
			$args=preg_replace('/^\[((.|\s)*)\]$/','\1',json_encode($bt[1]['args']));
			//$text=$bt[0]['file'].'['.$bt[0]['line'].']#'; //file[line]:
			$text='['.$bt[0]['line'].']#';
			$text.=isset($bt[1]['class'])?$bt[1]['class'].$bt[1]['type']:''; //class-> or class:: (if exists)
			$text.=$bt[1]['function'].'('.$args.');'; //function(
		}
		print strftime('[%F %T]: ')."$text\n";
	}
	
	//*begin required implements methods
	public function offsetGet($index) {                         // implements by ArrayAccess
		$this->debug();
		$index=$this->_checkAutoIncrement($index);
		if(array_key_exists($index,$this->args)) return $this->args[$index];
		return $this->offsetSet($index, null);
	}
	public function offsetSet($index, $value){                  // implements by ArrayAccess
		$this->debug();
		$index=$this->_checkAutoIncrement($index);
		if(is_object($value) && $value instanceof self) {
			$this->args[$index]=$value;
		}
		else {
			self::$startedId=array($this,$index);
			$this->args[$index]=$this->_new($value);
		}
		return $this->args[$index];
	}
	public function offsetUnset($index){                        // implements by ArrayAccess
		$this->debug();
		if(array_key_exists($index,$this->args)) {
			unset($this->args[$index]);
		}
	}
	public function offsetExists($index){                       // implements by ArrayAccess
		return array_key_exists($index,$this->args);
	}
	public function valid(){                                    // implements by Iterator
		return $this->offsetExists(key($this->args));
	}
	public function key(){                                      // implements by Iterator
		return key($this->args);
	}
	public function current(){                                  // implements by Iterator
		return current($this->args);
	}
	public function next(){                                     // implements by Iterator
	    if($this->valid()) throw new Exception('at end of '.get_called_class());
		return @next($this->args);
	}
	public function previous(){                                 // implements by Iterator
	    if($this->valid()) throw new Exception('at begin of '.get_called_class());
		return @prev($this->args);
	}
	public function rewind(){                                   // implements by Iterator
		return reset($this->args);
	}
	public function count(){                                    // implements by Countable
		return count($this->args);
	}
	/**///end required implements methods
	
	//*begin optional implements methods
	public function keys(){                                     // implements array_keys()
		return array_keys($this->args); 
	}
	public function values(){                                   // implements array_values()
		return array_values($this->args);
	}
	public function chunk($size, $preserve_keys=false){         // implements array_values()
		return array_chunk($this->args, $size, $preserve_keys);
	}
	public function merge(){                                    // implements by array_merge()
		$args=func_get_args();
		foreach($args as $a) $this->args=array_merge($this->args,(array)$a);
		return $this->args;
	}
	public function pop(){                                      // implements array_pop()
		if(!$this->args) return;
		return array_pop($this->args);
	}
	public function shift(){                                    // implements array_shift()
		if(!$this->args) return;
		return array_shift($this->args);
	}
}
