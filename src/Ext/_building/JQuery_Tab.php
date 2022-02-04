<?php
class JQuery_Tab extends OverLoadElements {
	protected $protect=array(
		'id'=>'jQueryTab_content',
		'active'=>null,
		'before'=>null,
		'after'=>null,
		'classItem'=>array(),
		'classTab'=>array(),
	);
	protected $readonly=array(
		'elements'=>array(),
	);
	protected $allParameters=array('id','elements');
	function __toString(){
		if(!$this->readonly['elements']) return '';
		new Bootstrap;
		$id=$this->protect['id'];
		$idTab=$id.'_tab';
		$idTabs=$id.'_tabs';
		//$id=$idTab=$idTabs='tabs';
		($act=$this->protect['active']) || ($act=1);
		$before=$this->protect['before']?preg_replace('/\s+$/','',$this->protect['before'])."\n":'';
		$after=$this->protect['after']?preg_replace('/\s+$/','',$this->protect['after'])."\n":'';
		
		$out="\t<div id='$id'>\n{$before}";
		$out.="\t\t<ul id='$idTabs' class='nav nav-tabs' data-tabs='tabs'>\n";
		$cont=0;
		$content="\t\t<div id='{$id}-tab-content' class='tab-content'>\n";
		foreach($this->readonly['elements'] as $k=>$v) {
			$cont++;
			$i=$idTab.'-'.$cont;
			($classItem=@$this->protect['classItem'][$k]) || ($classItem=@$this->protect['classItem'][$cont]);
			($classTab=@$this->protect['classTab'][$k])   || ($classTab=@$this->protect['classTab'][$cont]) || ($classTab='tab-pane');
			if($k==$act || $cont==$act) {
				$classItem.=($classItem?' ':'').'active';
				$classTab.=' active';
			}
			$classItem=$classItem?" class='$classItem'":'';
			$out.="\t\t\t<li role='presentation'$classItem><a href='#$i' data-toggle='tab'>$k</a></li>\n";
			$content.="\t\t\t<div id='$i' class='$classTab'>\n$v";
			$content.="\t\t\t</div>\n";
		}
		$content.="\t\t</div>\n";
		$out.="\t\t</ul>\n";
		$out.=$content;
		$out.="{$after}\t</div>\n";
		return $out;
	}
	function add($title,$content){
		$this->readonly['elements'][$title]=$content;
	}
}