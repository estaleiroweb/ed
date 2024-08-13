<?php
namespace Evoice\Data\Grid;

use EstaleiroWeb\ED\Screen\OutHtml;

# Autor: Helbert Fernandes
# Descrição: Conjunto de classes do tblData para manipulação de conjunto de dados
#
# Histórico:
# Data: 02/03/2005 08:30 - Helbert Fernandes: Criação a partir da reunião e organização de outros arquivos

//Tabs de uma view
class tblDataTab extends tblData {
	protected $baseId='DataTab';
	public $tabs=array();	//lista de tabs
	//private $tabActived;

	//Imprime o objeto
	public function __tostring(){
		if($this->tabActived=='') $this->tabActived=key($this->tabs);
		//print json_encode($_POST);

		$out='';
		$objAct=@$this->tabs[$this->tabActived];
		if (!$this->outFormat && (!is_object($objAct) || !@$objAct->outFormat)) {
			$outHtml=OutHtml::singleton();
			$outHtml->title($this->label,$this->showLabel);
			$out.=self::TAB.'<ul class="nav nav-tabs tblDataTab">'.self::LF;
			$out.=self::TAB.self::TAB.'<input type="hidden" name="'.$this->baseId.'['.$this->idHtml.'][tabActived]" value="'.$this->buildIdHtml($this->tabActived).'" />'.self::LF;
			foreach($this->tabs as $id=>$obj) {
				$label=is_object($obj) && @$obj->label!=''?$obj->label:$id;
				$out.=self::TAB.self::TAB.'<li role="presentation"';
				$out.=($id==$this->tabActived?' class="active"':'');
				$out.='><a href="#" id="'.$this->buildIdHtml($id).'">';
				$out.=$label.'</a></li>'.self::LF;
			}
			$out.=self::TAB.'</ul>'.self::LF;
			
			//$this->addForm();
			$outHtml->script(__CLASS__,'easyData');
			$this->saveSession();
		}
		if(is_object($objAct) && ($showLabel=@$objAct->showLabel) && !@$objAct->forceShowLabel) {
			$objAct->showLabel=false;
			$out.="$objAct";
			$objAct->showLabel=$showLabel;
		} else $out.="$objAct";
		
		return '<form role="form" method="POST"><div class="container">'.$out.'</div></form>';
	}
	/*protected function saveSession(){
		//if($this->saveSession) $this->oSess->active=$this->active;
		if($this->saveSession) $this->oSess->set(['tabActived'=>$this->active['tabActived']]);
	}*/
	//Adciona uma Tab
	public function add(){//$obj
		$args=func_get_args();
		foreach ($args as $obj) {
			if(is_object($obj)) {
				if($obj instanceof tblData || @$obj->id) {
					$obj->oDad=$this;
					$this->tabs[$obj->id]=$obj;
				}
				elseif(@$obj->name) $this->tabs[$obj->name]=$obj;
			} 
			elseif(is_array($obj)) $this->add_array($obj);
			else $this->tabs[]=$obj;
		}
		if($this->tabActived=='') $this->tabActived=key($this->tabs);
		return $this;
	}
	private function add_array($array,$label=null){
		if(array_key_exists('view',$array)) $this->add($this->create_DataList_ByArray($array,$label));
		else foreach($array as $k=>$v) {
			if(is_array($v)) $this->add_array($v,$k);
			elseif(is_object($v)) $this->add($v);
			else $this->tabs[$k]=$v;
		}
	}
	private function create_DataList_ByArray($array,$label=null){
		$o=new tblDataList($label);
		$o->oDad=$this;
		foreach($array as $k=>$v) $o->$k=$v;
		//show($array);
		//show($o->lstFields);
		return $o;
	}
}