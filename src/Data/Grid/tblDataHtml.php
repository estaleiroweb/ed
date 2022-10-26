<?php
namespace EstaleiroWeb\ED\Data\Grid;
# Autor: Helbert Fernandes
# Descrição: Conjunto de classes do tblData para manipulação de conjunto de dados
#
# Histórico:
# Data: 02/03/2005 08:30 - Helbert Fernandes: Criação a partir da reunião e organização de outros arquivos

//Tabs de uma view
class tblDataHtml extends tblData {
	var $html='';			//estrutura em html que vai conter os objetos
	var $isFile=false;   //se o $html é um arquivo ou tags Html

	//Imprime o objeto
	function __tostring(){
		//Adciona o objeto no Track

		//Variaveis
		$arguments=array_slice(func_get_args(),1);
		extract($GLOBALS);
		$outFormat=$this->outFormat;
		$outHtml=$this->html;
		$headHtml=$footHtml='';

		//Body
		//capturar o arquivo se existir e isFile=true
		//if ($this->isFile) $outHtml=$this->loadFile($outHtml);
		$outHtml=eval('return "'.str_replace('"','\\"',$outHtml).'";');

		//Finalizando o objeto
		$this->saveSession();
		return $outFormat?$outHtml:$headHtml.$outHtml.$footHtml;
	}
}