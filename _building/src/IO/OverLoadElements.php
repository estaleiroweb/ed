<?php
namespace Sys;
/**
 * OverLoadElements
 * PHP Version 5.x
 * @package HF
 * @link http://estaleiroweb.com.br/HF/scripts
 * @author Helbert Fernandes <helbertfernandes@gmail.com>
 * @copyright 0000 - 0000 Helbert Fernandes
 * @license http://estaleiroweb.com.br/HF/license GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */
class OverLoadElements {
	use \Traits\OO\Init,\Traits\OO\GetterAndSetter;
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $erro=array();
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
	public function getErro(){
		return $this->erro;
	}
	public function pr($text,$force=false){
		if($this->debug || $force) print $this->showVar($text);
	}
	public function showVar($text){
		$text=print_r($text,true);
		if(@$_SERVER['SHELL']) return utf8_encode($text)."\n";
		else return "<pre style='font-size:x-small; text-align:left;'>$text</pre>";
	}
}