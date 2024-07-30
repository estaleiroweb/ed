<?php
namespace EstaleiroWeb\ED\Data\Grid;

use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\ED\Tools\Id;

# Autor: Helbert Fernandes
# Descrição: Conjunto de classes do tblData para manipulação de conjunto de dados
#
# Histórico:
# Data: 02/03/2005 08:30 - Helbert Fernandes: Criação a partir da reunião e organização de outros arquivos

//Tabs de uma view
class tblDataFrame extends tblData {
	protected $baseId='DataFrame';
	public $id;
	public $tabs=[];

	//Imprime o objeto
	public function __construct($arr=null,$id=null){
		if($id) $this->id=$id;
		else {
			$oId=Id::singleton();
			$this->id=$oId->id;
		}
		if($arr) $this->add($arr);
	}
	public function __tostring(){
		if ($this->outFormat) return '';
		OutHtml::singleton()->script(__CLASS__,'ed');

		$tabActived=$this->tabActived?$this->tabActived:key($this->tabs);
		$idFrm='frm_'.$this->id;
		$out="<div class='container' id='{$this->id}'>";
		$out.='<ul class="nav nav-tabs dataTabFrame">';
		foreach($this->tabs as $label=>$url) {
			$active=$label==$tabActived?" class='active'":'';
			$out.="<li role='presentation'{$active}><a href='$url' target='{$idFrm}'>$label</a></li>";
		}
		$out.='</ul>';
		$out.="<iframe id='{$idFrm}' name='{$idFrm}' ed-element='tblDataFrame' style='border: none; width: 100%;' height='100px' marginwidth=0 marginheight=0 ></iframe>";
		$out.='</div>';
		return $out;
	}
	public function add($arr,$url=null){
		if(is_array($arr)) foreach($arr as $label=>$url) $this->tabs[$label]=$url;
		elseif($url) $this->tabs[$arr]=$url;
		return $this;
	}
}