<?php
class DataTable_ColVis extends Geters_Seters {
	protected $protect=array(
		'buttonText'=> '<span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>',
		'restore'=>'Restore',
		'showAll'=>'Show all',
		'showNone'=>'Show none',
		//'exclude'=> '', //Fileds1,Field2,...
		//'groups'=>array(), // array( array('title'=>'Titulo','columns': 'filed1,field2...'), ... ),
	);
	function setExclude($val){ $this->protect['exclude']=$val; }
	function setRestore($val){ $this->protect['restore']=$val; }
	function setShowAll($val){ $this->protect['showAll']=$val; }
	function setShowNone($val){ $this->protect['showNone']=$val; }
	function setButtonText($val){ $this->protect['buttonText']=$val; }
	function setOrder($val){ $this->protect['order']=$val; } //'alpha'
	function setActivate($val){ $this->protect['activate']=$val; } //'mouseover'
	function setGroup($title,$columns){ $this->protect['groups'][]=array('title'=>$title,'columns'=>$columns); }
	function setLabel($val){ $nm='label';$this->protect[$nm]=$this->toSetFunction($nm,$val); }
	function setStateChange($val){ $nm='stateChange';$this->protect[$nm]=$this->toSetFunction($nm,$val); }
	
	function getLabel(){ return $this->toGetFunction('label'); }
	function getStateChange(){ return $this->toGetFunction('stateChange'); }
}
