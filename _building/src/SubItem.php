<?php
class SubItem {
	public $close,$id,$html,$OutHtml,$item;
	public $preId='';
	public $posTitle='';

	function __construct($item='',$html='',$close=false){
		$oId=Id::singleton();
		$this->id=$oId->id;
		$this->html=$html;
		$this->close=$close;
		$this->item=$item;
		$this->OutHtml=OutHtml::singleton();
		$this->OutHtml->style(__CLASS__,'easyData');
		$this->OutHtml->script(__CLASS__,'easyData');
		$this->OutHtml->headScript[__CLASS__.$this->id]="window.{$this->id}=new SubItem('{$this->id}')";
		$this->mediator=MediatorPHPJS::singleton();
	}
	function __toString() {
		$var=$this->id;
		if (!is_null($this->mediator->$var)) $this->close=$this->mediator->$var;
		else $this->mediator->$var=$this->close;
		$class=$this->close?'closed':'opened';
		$idItem=preg_replace('/\W/','',$this->item);
		return "<div id='SubItem'><div class='$class' id='subItem_{$this->preId}$idItem'><h2 id='SubItem_title' onclick='{$this->id}.showHide(this)'><div onmouseover='{$this->id}.over(this)' onmouseout='{$this->id}.out(this)'>{$this->item}{$this->posTitle}</div></h2><div id='SubItem_content'>{$this->html}</div></div></div>";
	}
}
