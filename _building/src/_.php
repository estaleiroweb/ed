<?php
function add_include_path($path){
	if(is_array($path)) $path=implode(PATH_SEPARATOR,$path);
	set_include_path(get_include_path(). PATH_SEPARATOR .$path);
}

trait HtmlText{
	static $__ENCODE=null;
	
	public static function fixLink($link){
		do{
			$old=$link;
			$link=preg_replace('/([\/\\\])[^\/\\\]+?\1\.\.\1/','\1',$link);
		} while ($link!=$old);
		return $link;
	}
	public function getEncode() {
		$class=__TRAIT__;
		if(is_null($class::$__ENCODE)) {
			mb_detect_order('ASCII,UTF-8,ISO-8859-1,eucjp-win,sjis-win');
			$class::$__ENCODE=mb_detect_encoding('aeiouáéíóú');
			
			mb_internal_encoding($class::$__ENCODE);
			mb_http_output($class::$__ENCODE);
			mb_http_input($class::$__ENCODE);
			mb_regex_encoding($class::$__ENCODE);
			mb_language('uni');
		}
		return $class::$__ENCODE;
	}
	public function htmlConvert($text) {
		return mb_convert_encoding($text,$this->getEncode()); 
	}
	public function htmlScpChar($text,$quotes=ENT_NOQUOTES){ 
		return htmlentities($text,$quotes,$this->getEncode()); 
	}
}
trait Show{
	use HtmlText;
	
	static $__VERBOSE=false;
	static $DONE=false;
	
	public function nanoTime() { return trim(`date '+%F %T.%N'`); }
	public function prt($text) { print '['.$this->nanoTime().']: '.$text; }
	public function pr($text) { print $this->htmlConvert($text); }
	public function makeBox($message='',$title='',$class='makeBox',$tag='div'){
		$title=$title?"<h3>{$title}</h3>":'';
		if($message) $message="<pre>{$message}</pre>";
		return $this->htmlConvert("<{$tag} class='{$class}'>{$title}{$message}</{$tag}>\n");
	}
	public function show($text=true,$class='makeBox'){ 
		$c=__TRAIT__;
		$text=print_r($text,true);
		
		//Find the file and function caller
		$bt=debug_backtrace();
		foreach($bt as $k=>$v) { unset($bt[$k]['object']); } 
		$file=$line=null;
		$args=array();
		while($bt) {
			$oFrom=array_shift($bt);
			$class=@$oFrom['class'];
			$type=@$oFrom['type'];
			if(array_key_exists('file',$oFrom)) $file=$oFrom['file'];
			if(array_key_exists('line',$oFrom)) $line=$oFrom['line'];
			$function=$oFrom['function'];
			if(!array_key_exists('args',$oFrom)) continue;
			if($class==__CLASS__ && ($function==__FUNCTION__ || $function=='verbose')) continue;
			$args=$oFrom['args'];
			if($function=='__callStatic' && $class=='AL') continue;
			if($function=='call_user_func_array' && is_object(@$args[0][0]) && get_class($args[0][0])=='AL') continue;
			break;
		}
		$parm=array();
		$caller=$file.'['.$line.']:';    //file[line]:
		$caller.=$class?$class.$type:''; //class-> or class:: (if exists)
		$caller.=$function.'(';          //function(
		
		//Print
		if(@$_SERVER['SHELL'] || is_string($c::$__VERBOSE)){ //Line command print type
			foreach($args as $v) $parm[]=gettype($v);
			if($caller )$caller='==>'.$caller;
			$caller.=implode(',',$parm).');'; //arg1,arg2,...);
			$out=$caller?$caller."\n":'';
			if($text)$out.="$text\n";
			$out.="\n";
			if(is_string($c::$__VERBOSE)) return file_put_contents($c::$__VERBOSE,$out,FILE_APPEND);
		}
		else { //Browser print type
			foreach($args as $v) $parm[]='<span title="'.$this->htmlScpChar(print_r($v,true),ENT_QUOTES).'">'.gettype($v).'</span>';
			$caller=$this->htmlScpChar($caller).implode(',',$parm).');'; //arg1,arg2,...);
			$out=$this->makeBox($this->htmlScpChar($text),$caller,$class,'pre');
		}
		if($class===false) return $out;
		print $out;
	}
	public function veboseDefaultStyle(){
		$c=__TRAIT__;
		if($c::$DONE || @$_SERVER['SHELL']) return;
		$c::$DONE=true;
		$file=self::findFileDefault('verbose.css');
		if($file) print "<style>\n".file_get_contents($file)."</style>\n";
	}
	public function verbose($text=true,$class=null){
		$c=__TRAIT__;
		if(is_bool($text)) return $c::$__VERBOSE=$text;
		if($c::$__VERBOSE) $this->show($text,$class);
	}
}
trait Converters {
	public function value2String($value){ //Depreciada
		return $this->toString($value);
	}
	public function escapeString($text){
		return addcslashes($text, "\"'%_\0..\37!@\177..\377");
	}
	public function toString($value){
		if(is_null($value)) return 'NULL';
		if(is_bool($value)) return $value?'True':'False';
		if(is_numeric($value)) return $value;
		if(is_array($value) || is_object($value)) $value=serialize($value);
		return '"'.$GLOBALS['_']->escapeString($value).'"';
	}
	public function toBool($value) {
		if(is_string($value)) {
			$value=strtolower($value);
			if($value==='false' || $value==='falso' || $value==='off' || $value==='desligado' || $value==='0') return false;
		}
		return (bool)$value;
	}
	public function field_split($fields){ 
		return preg_split('/\s*[,;]\s*/',trim($fields)); 
	}
}
trait NetTools {
	public function nmap($host,$port){
		$er=\Scr\OutError::singleton();
		$er->disable();
		$conexao=@fsockopen($host, $port,$erro,$erro,15);
		if(($ret=(bool)$conexao)) @fclose($conexao);
		//print "Testing $host:$port ".($ret?'[OK]':'[ERROR]')."\n";
		$er->restore();

		return $ret;
	}
	public function getFreeRandomPort($host='127.0.0.1',$start=1000,$end=65536) {
		$i=0;
		do {
			$i++;
			$port=rand($start, $end);
			$nmap=$this->nmap($host,$port);
		} while(!$nmap && $i<100);
		if(!$nmap) return $port;
	}
	public function checkHost($url,$port=80){
		$urlSplit=parse_url($url);
		$host=@$urlSplit['host'];
		if(!$host || $host=='localhost' || $host=='127.0.0.1') return true;
		($p=@$urlSplit['port']) || ($p=$port);
		return (bool)@fsockopen($urlSplit['host'], $p, $errno, $errstr, 5);
	}
	public function goURL($url='/'){
		header('HTTP/1.0 301 Moved');
		header('Location: '.$url);
		exit;
	}
	public static function hostname(){//FIXME hostname to win || linux
		return trim(`hostname`);
	}
}
trait JSON {
	private static $__QUEUE=array();
	private static $__JSON_FNS=array();
	private static $__JSON_CONT=0;
	
	public function json_stripSlashes(&$value=null,$id=null){
		return $value=$this->json_stripSlashes_queue((string)$value,$id);
	}
	public function json_stripSlashes_get(){
		return $this->json_stripSlashes_queue();
	}
	public function json_stripSlashes_clear(){ 
		return $this->json_stripSlashes_queue(true);
	}
	public function json_stripSlashes_queue($value=null,$id=null){
		$class=__TRAIT__;
		
		if($id) {
			if(preg_match('/^%STRIP_SLASHES#\d+%$/',$id)) $name=$id;
			else return $id;
		}
		else {
			$id=null;
			$name='%STRIP_SLASHES#'.$class::$__JSON_CONT.'%';
			$class::$__JSON_CONT++;
		}
		$out=$name;
		if(is_null($value) || is_bool($value)) {
			$out=$id?$class::$__JSON_FNS[$name]:$class::$__JSON_FNS;
			if($value===true) {
				$class::$__JSON_FNS=array();
				$class::$__JSON_CONT=0;
			}
		}
		else $class::$__JSON_FNS[$name]=$value;
		return $out;
	}
	public function json_encode_full($mixed){
		if($a=is_array($mixed) || is_object($mixed)) {
			if($mixed===$GLOBALS) return '$GLOBALS';
			if(array_search($mixed,self::$__QUEUE)!==false) return '**RECURSION**';
			$tam=count(self::$__QUEUE);
			self::$__QUEUE[$tam]=$mixed;
			if($a) {
				$outO=array();
				$outA=array();
				$cont=0;
				$isObj=false;
				foreach($mixed as $k=>$v) {
					if($k!==$cont++) $isObj=true;
					$v=$this->json_encode_full($v);
					$outO[]=$this->json_encode_full($k).':'.$v;
					$outA[]=$v;
				}
				$out=$isObj?'{'.implode(', ',$outO).'}':'['.implode(', ',$outA).']';
			}
			else {
				$out=$this->json_encode_full([
					'type'=>'object',
					'name'=>get_class($mixed),
					'content'=>get_object_vars($mixed),
					'methods'=>get_class_methods($mixed),
				]);
			}
			unset(self::$__QUEUE[$tam]);
			return $out;
		}
		if(is_bool($mixed)) return $mixed?'True':'False';
		if(is_null($mixed)) return 'null';
		if(is_numeric($mixed)) return $mixed;
		if(is_resource($mixed)) {
			return $this->json_encode_full([
				'type'=>'resource',
				'name'=>(string)$mixed,
				'content'=>get_resource_type($mixed),
			]);
		}
		return '\''.addcslashes($mixed,"\x00..\x1F\x7E..\xFF'\"\\").'\'';
	}
	public function json_encode2($mixed){
		return strtr(
			preg_replace('/([\'"])(%STRIP_SLASHES#\d+%)\1/','\2',json_encode($mixed)),
			$this->json_stripSlashes_queue()
		);
	}
}
trait SessionConfig {
	protected function getSession($id=''){
		return @$_SESSION[__CLASS__][$id];
	}
	protected function setSession($value,$id=''){
		if(!@$_SESSION[__CLASS__]) $_SESSION[__CLASS__]=array();
		$_SESSION[__CLASS__][$id]=$value;
		return $value;
	}
	protected function delSession($id=''){
		unset($_SESSION[__CLASS__][$id]);
	}
}
trait Debug {
	static $DEBUG=null;
	
	public function debug($text=null){
		$class=__TRAIT__;
		if(is_bool($text)) $class::$DEBUG=$text;
		if(!$class::$DEBUG) return;
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
}
class _ implements Iterator, Countable, ArrayAccess {
	use \Show,\SessionConfig,\NetTools, \Debug;
	
	const cfgFileName='_.inc';
	const startFileName='_start.inc';
	
	static private $tmp=array();
	static private $cfg;
	static public $debug=false;
	static public $errors=array();
	
	protected $id,$caller;
	protected $nodes=array();
	protected $args=array();
	protected $assoc=array();
	protected $readOnly=array();
	protected $writable=array();
	
	public static function singleton(){
		global $_;
		static $started=false;
		
		{//Begin
			if($started) return $_;
			$started=true;
			if(__CLASS__!='_') return;
			if (!session_id()) session_start(); //$this->getSession($this->includeFile); //FIXME 
			$_SESSION=array();//FIXME DEL IT
			$thisFile=self::fileName();
		}
		if(@$_SESSION['_cfg'][$thisFile]) { //Load Config
			self::$cfg=&$_SESSION['_'][$thisFile];
			$cfg=&$_SESSION['_'][$thisFile];
		}
		else { //Start/Create Config
			$_SESSION['_'][$thisFile]=new \StdClass;
			self::$cfg=&$_SESSION['_'][$thisFile];
			$cfg=&$_SESSION['_'][$thisFile];
			
			$cfg->thisFile=$thisFile;
			$cfg->dir=dirname($cfg->thisFile);
			$cfg->fileName=__FILE__;
			$cfg->dirFileName=__DIR__; //dirname(__FILE__)
			$cfg->iniDir=null;
			$cfg->listClasses=array();
			$cfg->urlHistory=array();
			
			$cfg->iniDir=self::findFileDefault(self::cfgFileName);
			if(!$cfg->iniDir) die("ERROR ".self::cfgFileName." \n");
			require_once $cfg->iniDir. DIRECTORY_SEPARATOR .self::cfgFileName;

			$paths=explode(PATH_SEPARATOR,get_include_path());
			$list=array();
			$ext=spl_autoload_extensions();
			$cfg->extInc=explode(',',$ext);
			$cfg->erInc='/^([^.]+)('.str_replace(',','|',preg_quote($ext,'/')).')$/i';
			foreach($paths as $dir) {
				$dir=trim($dir);
				$files=preg_grep($cfg->erInc,scandir($dir));
				foreach($files as $file) $list[preg_replace($cfg->erInc,'\1',$file)]=$dir. DIRECTORY_SEPARATOR .$file;
			}
			self::$cfg->listClasses=array_merge($list,self::$cfg->listClasses);
		}
		if (@$_SERVER['SHELL']) { //Config for shell
			$cfg->lf="\n";
			$cfg->referer=null;
			$cfg->schema='file';
			($host=@$_SERVER['SSH_CONNECTION']) ||($host='localhost');
			$cfg->host=$host;
			if(preg_match('/(\d+)\s*$/',$host,$ret)) $cfg->port=$ret[1];
			$a=$GLOBALS['argv'];
			$cfg->url=array_shift($a);
			foreach($a as &$v) $v='"'.addslashes($v).'"';
			$cfg->fullUrl=$cfg->url.' '.implode(',',$a);
		}
		else{ //Config for Browser
			$cfg->lf="<br>\n";
			$cfg->referer=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
			$cfg->schema=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']='on')?'https':strtolower(preg_replace('/[^a-z]/i','',@$_SERVER['SERVER_PROTOCOL']))).'://';
			($cfg->host=@$_SERVER['SERVER_ADDR']) || ($cfg->host=@$_SERVER['HTTP_HOST']);
			$cfg->port=@$_SERVER['SERVER_PORT'];
			$cfg->url=$cfg->schema.$cfg->host.$_SERVER['SCRIPT_NAME'];
			$cfg->fullUrl=isset($_SERVER['REQUEST_URI'])?$cfg->schema.$cfg->host.$_SERVER['REQUEST_URI']:($cfg->url.(isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']?'?'.$_SERVER['QUERY_STRING']:''));
		}
		if(@$cfg->urlHistory[0]['fullUrl']!=$cfg->fullUrl) array_unshift($cfg->urlHistory,array('schema'=>$cfg->schema, 'host'=>$cfg->host, 'fullUrl'=>$cfg->fullUrl, ));
		{//Start spl_autoload register
			define('__INIDIR__',$cfg->iniDir);
			$_=new \_;
			spl_autoload_register([$_,'_autoload_list']);
			spl_autoload_register([$_,'_autoload_normal']);
			spl_autoload_register([$_,'_autoload_segment']);
			spl_autoload_register([$_,'_autoload_error']);
		}
		if($start=self::findFileDefault(self::startFileName)) require_once $start. DIRECTORY_SEPARATOR .self::startFileName; //Profile Default
		return $_;
	}
	public function __construct(){
		$this->id=$this->makeId();
		$this->args=func_get_args();
		return $this;
	}
	public function __get($nm){
		if(array_key_exists($nm,$this->readOnly))     { if(isset($this->readOnly[$nm])) return $this->readOnly[$nm]; }
		elseif(array_key_exists($nm,$this->writable)) { if(isset($this->writable[$nm])) return $this->writable[$nm]; }
		elseif(method_exists($this,$fn='get'.ucfirst($nm))) return $this->$fn();
		elseif(($ret=$this->_new($nm,null))!==false || !is_null($ret=$this->_getBacktrace($nm))) return $ret;
		trigger_error('Argument not exist: '.$nm,E_USER_NOTICE);
	}
	public function __set($nm,$value){
		if(array_key_exists($nm,$this->readOnly)) { 
			$bt=debug_backtrace();
			if(is_object(@$bt[0]['object']) && $bt[0]['object'] instanceof self) $this->readOnly[$nm]=$value;
		}
		elseif(method_exists($this,$fn='set'.ucfirst($nm))) return $this->$fn($value);
		elseif(($ret=$this->_new($nm,$value))!==false) return $ret;
		else $this->writable[$nm]=$value;
		return  $this;
	}
	public function __isset($nm){
		return isset($this->readOnly[$nm]) || isset($this->writable[$nm]) || isset($this->assoc[$nm]) || method_exists($this,$fn='get'.ucfirst($nm));
	}
	public function __unset($nm){
		if(array_key_exists($nm,$this->readOnly)) { 
			$bt=debug_backtrace();
			if(is_object(@$bt[0]['object']) && $bt[0]['object'] instanceof self) $this->readOnly[$nm]=null;
		}
		elseif(array_key_exists($nm,$this->writable)) unset($this->writable[$nm]);
		elseif(array_key_exists($nm,$this->assoc)) $this->assoc[$nm]=null;
	}
	public function __call($nm,$args){
		if($nm!='id' && $nm!='caller' && ($ret=$this->_new($nm,(array)$args))!==false) return $ret;
		trigger_error('Method '.$nm.' not found',E_USER_ERROR);
	}
	public static function __callStatic($nm,$args){
		return call_user_func_array(array($GLOBALS['_'],$nm),$args);
	}
	public function __toString(){
		return get_called_class();
	}
	public function __set_state($obj) {
		return array('args'=>$obj->args,'readOnly'=>$obj->readOnly,'writable'=>$obj->writable,'writable'=>$obj->assoc,);
	}
	public function __debugInfo() {
		return array('args'=>$this->nodes,'readOnly'=>$this->readOnly,'writable'=>$this->writable,'writable'=>$this->assoc,);
	}
	public function __clone() {
		$this->id=$this->makeId();
	}
	public function get($nm){ return $this->__get($nm); }
	public function set($nm,$value){ return $this->__set($nm,$value); }
	
	//*begin required implements methods
	public function offsetGet($index) {                         // implements by ArrayAccess
		$this->debug();
		if(array_key_exists($index,$this->nodes)) return $this->nodes[$index];
		return $this->offsetSet($index, null);
	}
	public function offsetSet($index, $value){                  // implements by ArrayAccess
		$this->debug();
		if(!is_object($value) || !($value instanceof _)) {
			$value=$this->_new(get_called_class(),$value,false);
		/*
		if(!is_array($value)) $value=(array)$value;
		$parm=array();
		foreach($value as $k=>$v) $parm[]='$value["'.$k.'"]';
		$obj=eval('return new '. get_called_class() .'('.implode(', ',$parm).');');
		if($this->assoc[$nm] instanceof _) {
			$obj->readOnly['caller']=$this;
		}
		return $obj;
		*/
		}
		if(is_null($index)) $this->nodes[]=$value;
		else $this->nodes[$index]=$value;
		
		return $value;
	}
	public function offsetUnset($index){                        // implements by ArrayAccess
		$this->debug();
		if(array_key_exists($index,$this->nodes)) unset($this->nodes[$index]);
	}
	public function offsetExists($index){                       // implements by ArrayAccess
		return array_key_exists($index,$this->nodes);
	}
	public function valid(){                                    // implements by Iterator
		return $this->offsetExists(key($this->nodes));
	}
	public function key(){                                      // implements by Iterator
		return key($this->nodes);
	}
	public function current(){                                  // implements by Iterator
		return current($this->nodes);
	}
	public function next(){                                     // implements by Iterator
		if($this->valid()) throw new Exception('at end of '.get_called_class());
		return @next($this->nodes);
	}
	public function previous(){                                 // implements by Iterator
		if($this->valid()) throw new Exception('at begin of '.get_called_class());
		return @prev($this->nodes);
	}
	public function rewind(){                                   // implements by Iterator
		return reset($this->nodes);
	}
	public function count(){                                    // implements by Countable
		return count($this->nodes);
	}
	/**///end required implements methods
	
	//*begin optional implements methods
	public function keys(){                                     // implements array_keys()
		return array_keys($this->nodes); 
	}
	public function values(){                                   // implements array_values()
		return array_values($this->nodes);
	}
	public function chunk($size, $preserve_keys=false){         // implements array_values()
		return array_chunk($this->nodes, $size, $preserve_keys);
	}
	public function merge(){                                    // implements by array_merge()
		$args=func_get_args();
		foreach($args as $a) $this->nodes=array_merge($this->nodes,(array)$a);
		return $this->nodes;
	}
	public function pop(){                                      // implements array_pop()
		if(!$this->nodes) return;
		return array_pop($this->nodes);
	}
	public function shift(){                                    // implements array_shift()
		if(!$this->nodes) return;
		return array_shift($this->nodes);
	}
	/**///end optional implements methods
	
	private function _makeObj($fn,$args=array()) {                 //Create a new class
		$args=array_values((array)$args);
		$this->verbose($fn.'('.($args?json_encode($args):'').')');
		$param=array();
		foreach($args as $k=>$v) $param[]='$args['.$k.']';
		$fn.='('.implode(', ',$param).');';
		return eval('return '.$fn);
	}
	private function _new($nm,$args,$store=true){
		static $methods=array();
		static $_dad=array();
		
		if(!class_exists($nm)) return false;
		if(!array_key_exists($nm,$methods)) {
			$methods[$nm]=get_class_methods($nm);
			sort($methods[$nm]);
			if($nm=='_') $_dad[$nm]=true;
			else {
				$p=class_parents($nm);
				$_dad[$nm]=(bool)@$p['_'];
			}
		}
		//$this->show($this->assoc);
		if($store && array_key_exists($nm,$this->assoc)) {
			if(!is_null($args)) {
				if(in_array('__invoke',$methods[$nm]))        $fn='';
				elseif(in_array('__construct',$methods[$nm])) $fn='->__construct';
				else                                          $fn=false;
				if($fn===false) {
					if(is_array($args) && !preg_grep('/^\d+$/',array_keys($args))) {
						$this->verbose('Set Values to $nm');
						foreach($args as $k=>$v) $this->assoc[$nm]->$k=$v;
					}
				}
				else $this->_makeObj('$this->assoc["'.$nm.'"]'.$fn,$args);
			}
			return $this->assoc[$nm];
		}
		else {
			if(!$_dad[$nm] && in_array('singleton',$methods[$nm])) $fn=$nm.'::singleton';
			else $fn='new '.$nm;
			
			$obj=$this->_makeObj($fn,$args);
			if($obj instanceof self) $obj->caller=$this;
		}
		if($store) $this->assoc[$nm]=$obj;
		return $obj;
	}
	private function _getBacktrace($nm){
		$bt=debug_backtrace();
		foreach($bt as $line) {
			if(isset($line['object']) && $line['object']!==$this) {
				$args=get_class_vars($line['object']);
				if(array_key_exists($nm, $args) && !is_null($args[$nm])) return $args[$nm];
			}
		}
		if(array_key_exists($nm,$GLOBALS)) return $GLOBALS[$nm];
	}
	private function _include($file){
		foreach(self::$cfg->extInc as $ext) if(@include($file.$ext)) return true;
		return false;
	}
	private function _autoload_list($class){
		$this->verbose($class);
		if(array_key_exists($class,self::$cfg->listClasses) && @include(self::$cfg->listClasses[$class])) spl_autoload($class);
	}
	private function _autoload_normal($class){
		//print "_autoload_normal $class\n";
		$this->verbose($class);
		self::$tmp['autoload_include_file']=str_replace('\\','/',$class);
		if($this->_include(self::$tmp['autoload_include_file'])) spl_autoload($class);
	}
	private function _autoload_segment($class){
		//print "_autoload_segment $class\n";
		$this->verbose($class);
		$d=dirname(self::$tmp['autoload_include_file']);
		$d=$d=='.'?'':($d.'/');
		$b=$inc=basename(self::$tmp['autoload_include_file']);
		$b=preg_split('/[_]+/',$b);
		$c=array_pop($b);
		$b=$b?implode('/',$b):$c;
		if($this->_include($d.$b.'/'.$inc)) spl_autoload($class);
	}
	private function _autoload_error($class){
		//throw new Exception("Class $class not found\n");
		$this->verbose($class);
		self::$errors[]='Class '.$class.' not found';
	}

	final public  function getId()         { return $this->id; }
	final public  function getCaller()     { return $this->caller; }
	final public  function getArgs()       { return $this->args; }
	final public  function getNodes()      { return $this->nodes; }
	final public  function getCfg()        { return self::$cfg; }
	final private function setId()         { }
	final private function setCaller()     { }
	final private function setArgs()       { }
	final private function setParameters() { }
	final private function setCfg()        { }
	
	public function makeId() { return uniqid(get_called_class().'#'); }
	public function remove($nm) {
		if(array_key_exists($nm,$this->assoc)) unset($this->assoc[$nm]);
		return $this;
	}
	public static function fileName(){
		$bt=debug_backtrace();
		while($bt) {
			$oBt=@array_pop($bt); 
			if(preg_match('/(require|include)(_once)?/i',$oBt['function'])) {
				$file_refer=file($oBt['file']);
				$file_refer=$file_refer[$oBt['line']-1];
				$file_refer=preg_replace('/^.*?(?:require|include)(?:_once)?\s*/i','return ',trim($file_refer));
				$file_refer=str_replace(array("\x5C\x5C","\x5C\x27","\x5C\x22"),array('\x5C','\x27','\x22'),$file_refer);
				$link=eval(preg_replace('/\$(\w+)/','$GLOBALS[\'\1\']',$file_refer));
				$d=dirname($link);
				if($d[0]=='.') $link=dirname($oBt['file']).'/'.$link;
				$link=self::fixLink($link);
				if(realpath($link)==__FILE__) return $link;
			}
		}
		return __FILE__;
	}
	public function extend($new,$default=null){
		if(!$default) return $new;
		if(is_array($new)) {
			$default=(array)$default;
			foreach($new as $k=>$v) $default[$k]=$v;
		}
		elseif(is_object($new)){
			if(!is_object($default)) $default=(object)$default;
			foreach($new as $k=>$v) $default->$k=$v;
		}
		elseif($new) $default=$new;
		return $default;
	}
	public function each($fn,$args=array(),&$ret=null){
		//$ret=new _array;
		//$class=get_called_class();$ret=new $class;
		//$ret=new _;
		$ret=array();
		foreach($this->nodes as $k=>$v) $ret[$k]=call_user_func($fn,$k,$v,$this,$args);
		return $this;
	}
	public function addListClasses($class,$path){
		self::$cfg->listClasses[$class]=$path;
	}
	public function mergeListClasses($list){
		self::$cfg->listClasses=array_merge(self::$cfg->listClasses,$list);
	}
	public static function findFile($fileName,$path,$recursive=true){
		//FIXME guardar em cache as procuras
		if(!is_array($path)) $path=array_unique(preg_split('/\s*[;]+\s*/',$path));
		foreach($path as $dirname) if(is_file("$dirname/$fileName")) return $dirname;
		if($recursive) {
			$p=array();
			foreach($path as $dirname) if($dirname!='/') $p[]=dirname($dirname);
			if($p) return self::findFile($fileName,$p,true);
		}
		return false;
	}
	public static function findFileDefault($fileName){
		($ret=self::findFile($fileName,array(self::$cfg->dir=>self::$cfg->dir,self::$cfg->dirFileName=>self::$cfg->dirFileName))) || ($ret=self::findFile($fileName,array('/etc','~/'),false));
		return $ret;
	}
	public function loadFileDefault($fileName,$dieIfErro=true){
		$cfg=$this->getSession($id=__FUNCTION__.'('.$fileName.')');
		if(!is_null($cfg)) return $cfg;
		$files=[preg_replace('/(\.\w+)$/','_'.$this->hostname().'\1',$fileName),$fileName];
		foreach($files as $f) if($f=$this->findFileDefault($f)) {
			$cfg=\Sys\ParseConf::ParseConf_file($f,true);
			return $this->setSession($cfg,$id);
		}
		if($dieIfErro) trigger_error($fileName.' inexistente',E_USER_ERROR);
		return $this->setSession(false,$id);
	}
	public function loadFileDefault2($fileName,$dieIfErro=true){
		static $saved=array();
		
		$cfg=&self::$cfg;
		if(isset($saved[$fileName])) return $saved[$fileName];
		$files=array(preg_replace('/(\.\w+)$/','_'.self::hostname().'\1',$fileName),$fileName);
		foreach($files as $idx=>$fileName) {
			if(!($dirname=self::findFile($fileName,array($cfg->iniDir=>$cfg->iniDir,$cfg->dir=>$cfg->dir,$cfg->dirFileName=>$cfg->dirFileName)))) {
				if(!$idx) continue;
				if($dieIfErro) die($fileName.' inexistente');
				return $saved[$fileName]=false;
			}
			//show(__FUNCTION__.'['.__LINE__."]: $dirname/$fileName\n");
			$cfg->loadFileDefault=$dirname;
		}
		return $saved[$fileName]=self::parse_conf_file($dirname.'/'.$fileName,true);
	}
}
_::singleton();
