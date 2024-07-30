<?php
/**
 * Page-Level DocBlock DataTab
 * @package Easy
 * @subpackage Screen
 * @category Tools
 * @author Helbert Fernandes <helbertfernandes@yahoo.com.br>
 * @version 1.1 em 03/10/2006 15:00 - Helbert Fernandes
 */
 
 /**
 * Imprime um arquivo ou conteúdo
 *
 * @see new Data
 * @see new DataTab
 * @example
 * <code>
 * <?php
 * $dt=new DataHtml(); 
 * $dt->html="xxxxxxx";
 * print $dt;
 * ?>
 * </code>
 */
class DataHtml extends Data {
	public $html='';			//estrutura em html que vai conter os objetos
	public $isFile=false;   //se o $html é um arquivo ou tags Html

	//Imprime o objeto
	function __tostring(){
		//Adciona o objeto no Track
		$this->startClass();

		//Variaveis
		//$arguments=array_slice(func_get_args(),1);
		extract($GLOBALS);

		//Body
		$this->html=eval('return "'.str_replace('"','\\"',$this->isFile?$this->loadFile($this->outHtml):$this->html).'";');

		//Finalizando o objeto
		$this->endClass();
		return ($this->showLabel?"<h1>{$this->label}</h1>":'').$this->html;
	}
	function loadFile($file){
		if (preg_match('/\.php$/i',$file)) @include $file;
		else return @file_get_contents($file);
		return '';
	}
	function startClass(){
	}
	function endClass(){
	}
}
