<?php
class OverLoadElements {
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $erro=array();
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $allParameters=array();
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $readonly=array();
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $protect=array();
	/**
	 * Constructor
	 */
	function __construct(){
		$args=func_get_args();
		if(count($args)==1 && is_array($args[0])) $this->loadKeyParameters($args);
		elseif($args) $this->loadAllParameters($args);
	}
	protected function loadAllParameters($args) {
		foreach($this->allParameters as $k=>$pr) {
			if(array_key_exists($k,$args)) $this->$pr=$args[$k];
			else break;
		}
	}
	protected function loadKeyParameters($args) {
		foreach($args as $k=>$v) $this->$k=$v;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function __get($name){
		if(method_exists($this,$fn='get'.ucfirst($name))) return $this->$fn();
		elseif(array_key_exists($name,$this->protect)) {
			return isset($this->protect[$name])?$this->protect[$name]:$this->get_backtrace($name);
		} elseif(array_key_exists($name,$this->readonly)) {
			return isset($this->readonly[$name])?$this->readonly[$name]:$this->get_backtrace($name);
		} elseif(in_array($name,$this->allParameters)) {
			$this->$name=$value=$this->get_backtrace($name);
			return $value;
		}
	}
	/**
	 * Sobrecarrega as variaveis
	 *
	 * @param string $nm Nome da Variavel
	 * @param mixed $val Valor
	 */
	public function __set($name,$value){
		if(method_exists($this,$fn='set'.ucfirst($name))) $this->$fn($value);
		elseif(array_key_exists($name,$this->protect)) $this->protect[$name]=$value;
		elseif(in_array($name,$this->allParameters)) $this->readonly[$name]=$value;
		return $this;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada de debug_backtrace
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	function get_backtrace($nm){
		$bt=debug_backtrace();
		foreach($bt as $line) if(
			isset($line['object']) && 
			$line['object']!==$this && 
			isset($line['object']->$nm) && 
			!is_null($line['object']->$nm)
		) return $this->$nm=$line['object']->$nm;
		if(isset($GLOBALS[$nm])) return $this->$nm=$GLOBALS[$nm];
	}
	/**
	 * Função que identifica qual script, função/método e linha foi chamado e imprime
	 *
	 */
	function lineDebug(){ 
		$bt=debug_backtrace(); //Generates a backtrace
		$out='['.$bt[0]['line'].']';
		$out.=@$bt[1]['file'].':';//The current file name. See also __FILE__
		$out.=@$bt[1]['class'];//The current class name. See also __CLASS__
		$out.=@$bt[1]['type'];//The current call type. If a method call, "->" is returned. If a static method call, "::" is returned. If a function call, nothing is returned
		$out.=@$bt[1]['function'];//The current function name. See also __FUNCTION__
		//$object=$bt[1]['object'];//The current object
		$out.='('.http_build_query(@$bt[1]['args'],'',',').')';//If inside a function, this lists the functions arguments. If inside an included file, this lists the included file name(s)
		print "$out\n";
	}
	/**
	 * Função que identifica qual script, função/método e linha foi chamado e imprime
	 *
	 */
	function called($level=0){
		$levelN=$level+1;
		$bt=debug_backtrace(); //Generates a backtrace
		$class=@$bt[$levelN]['object']?get_class($bt[$levelN]['object']):@$bt[$levelN]['class'];
		$out='['.$bt[$level]['line'].']';
		$out.=@$bt[$levelN]['file'].':';//The current file name. See also __FILE__
		$out.=$class;//The current class name. See also __CLASS__
		$out.=@$bt[$levelN]['type'];//The current call type. If a method call, "->" is returned. If a static method call, "::" is returned. If a function call, nothing is returned
		$out.=@$bt[$levelN]['function'];//The current function name. See also __FUNCTION__
		//$object=$bt[$levelN]['object'];//The current object
		$out.='('.http_build_query(@$bt[$levelN]['args'],'',',').')';//If inside a function, this lists the functions arguments. If inside an included file, this lists the included file name(s)
		return $out;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada
	 *
	 * @return array
	 */
	public function getProtect(){
		return $this->protect;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada
	 *
	 * @return array
	 */
	public function getErro(){
		return $this->erro;
	}
	public function pr($text,$force=false){
		if($this->debug || $force) $this->showVar($text);
	}
	public function showVar($text){
		$text=print_r($text,true);
		if(@$_SERVER['SHELL']) return utf8_encode($text)."\n";
		else return "<pre style='font-size:x-small; text-align:left;'>$text</pre>";
	}
}