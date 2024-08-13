<?php
class MapArray implements Iterator, Countable, ArrayAccess {
	static public $debug=false;
	
	private $count=0;
	protected $data=array();
	
	final public function __construct(){
		$args=func_get_args();
		$c=count($args);
		if($c==1 && is_array($args[0])) {
			$this->data=$args[0];
			$c=count($this->data);
		} else $this->data=$args;
		$this->count=$c;
	}
	final public function __get($name)        { return @$this->data[$name]; }
	final public function __set($name,$value) { return $this->data[$name]=$value; }
	public function __toString()              { return $this->json(); }
	public function __invoke()                { return $this->data; }
	public function __debugInfo()             { return $this->data; }
    public function __sleep()                 { return array('data','count'); }
	public function __isset($name)            { return $this->offsetExists($name); }
	public function __unset($name)            { if($this->__isset($name)) unset($this->data[$name]); }
    public function __wakeup()                {}


	//*begin main methods
	protected function debug($text=null){                             //Print
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
	public function data(){ return $this->data; }
	public function json(){ return json_encode($this->data); }
	public function url($prefix=null, $separator='&'){ return http_build_query($this->data, $prefix, $separator); }
	public function order(){
		$out=array();
		foreach($this->data as $k=>$v) $out[]='`'.$k.'`'.($v?' '.$v:'');
		return implode(', ',$out);
	}
	public function tag($tag='div',$argId='id', $attr=null){
		$out=array();
		$attr=(array)$attr;
		foreach($this->data as $id=>$obj) {
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
	
	//*begin required implements methods
	final public function offsetGet($index) {                         // implements by ArrayAccess
		$this->debug();
		if(array_key_exists($index,$this->data)) return $this->data[$index];
		//return $this->offsetSet($index, null); //SubArray
	}
	final public function offsetSet($index, $value){                  // implements by ArrayAccess
		$this->debug();
		$index=(string)$index;
		if($index=='') die("Error index: $index");
		if(!array_key_exists($index,$this->data)) $this->count++;
		$this->data[$index]=$value;
	}
	final public function offsetUnset($index){                        // implements by ArrayAccess
		$this->debug();
		if(array_key_exists($index,$this->data)) {
			unset($this->data[$index]);
			$this->count--;
		}
	}
	final public function offsetExists($index){                       // implements by ArrayAccess
		return array_key_exists($index,$this->data);
	}
	final public function valid(){                                    // implements by Iterator
		return (bool)$this->count && $this->offsetExists(key($this->data));
	}
	final public function key(){                                      // implements by Iterator
		return key($this->data);
	}
	final public function current(){                                  // implements by Iterator
		return current($this->data);
	}
	final public function next(){                                     // implements by Iterator
	    if($this->valid()) throw new Exception('at end of '.get_called_class());
		return @next($this->data);
	}
	final public function previous(){                                 // implements by Iterator
	    if($this->valid()) throw new Exception('at begin of '.get_called_class());
		return @prev($this->data);
	}
	final public function rewind(){                                   // implements by Iterator
		return reset($this->data);
	}
	final public function count(){                                    // implements by Countable
		return $this->count;
	}
	/**///end required implements methods
	
	//*begin optional implements methods
	final public function keys(){                                     // implements array_keys()
		return array_keys($this->data); 
	}
	final public function values(){                                   // implements array_values()
		return array_values($this->data);
	}
	final public function chunk($size, $preserve_keys=false){         // implements array_values()
		return array_chunk($this->data, $size, $preserve_keys);
	}
	final public function merge(){                                    // implements by array_merge()
		$args=func_get_args();
		foreach($args as $a) $this->data=array_merge($this->data,(array)$a);
		return $this->data;
	}
	final public function pop(){                                      // implements array_pop()
		if(!$this->data) return;
		$this->count--;
		return array_pop($this->data);
	}
	final public function shift(){                                    // implements array_shift()
		if(!$this->data) return;
		$this->count--;
		return array_shift($this->data);
	}
}