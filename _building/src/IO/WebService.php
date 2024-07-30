<?php
/**
 * WebSevice Abstract
 *
 * @author Helbert
 * @package intelig
 * @version 1.0
 *
 */
class WebService {
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $overload=array();
	/**
	 * Inicializa as principais variaveis
	 */
	public function __construct($line=false){
		@session_start();
		if(is_array($line)) foreach ($line as $nm=>$val) $this->overload[$nm]=$val;
	}
	/**
	 * Retorna o valor da variavel sobrecarregada
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function __get($nm){
		$class=get_class($this);
		if(isset($_SESSION['vars'][$class][$nm])) return $_SESSION['vars'][$class][$nm];
		elseif(!isset($this->overload[$nm])) return;
		else return $_SESSION['vars'][$class][$nm]=$this->overload[$nm];
	}
	/**
	 * Sobrecarrega as variaveis
	 *
	 * @param string $nm Nome da Variavel
	 * @param mixed $val Valor
	 */
	public function __set($nm,$val){
		if(isset($this->overload[$nm])) $_SESSION['vars'][get_class($this)][$nm]=$this->overload[$nm]=$val;
	}
	/**
	 * Faz a sobrecarga de variaveis so de leitura
	 *
	 * @param string $nm Nome da Variavel
	 * @param mixed $val Valor
	 */
	protected function set($nm,$val){
		$_SESSION['vars'][get_class($this)][$nm]=$val;
	}
	/**
	 * Função que identifica qual script, função/método e linha foi chamado e imprime
	 *
	 */
	protected function lineDebug(){
		$bt=debug_backtrace(); //Generates a backtrace

		$out='['.$bt[0]['line'].']';
		$out.=@$bt[1]['file'].':';//The current file name. See also __FILE__
		$out.=@$bt[1]['class'];//The current class name. See also __CLASS__
		$out.=@$bt[1]['type'];//The current call type. If a method call, "->" is returned. If a static method call, "::" is returned. If a function call, nothing is returned
		$out.=@$bt[1]['function'];//The current function name. See also __FUNCTION__
		//$object=$bt[1]['object'];//The current object
		//$out.='('.serialize(@$bt[1]['args']).')';//If inside a function, this lists the functions arguments. If inside an included file, this lists the included file name(s)
		print "$out<br>\n";
	}
}