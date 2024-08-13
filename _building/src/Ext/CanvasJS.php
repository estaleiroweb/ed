<?php
class CanvasJS {
	protected $attr=array('ed-element'=>'canvasjs');
	
	public function __construct($source=null,$refresh=null){
		$this->source=$source;
		$this->refresh=$refresh;
	}
	public function __get($name){
		if(method_exists($this,$fn='get'.ucfirst($name))) return $this->$fn();
		return @$this->attr[$name];
	}
	public function __set($name,$value){
		if(method_exists($this,$fn='set'.ucfirst($name))) return $this->$fn($value);
		else $this->attr[$name]=$value;
		return $this;
	}
	public function __toString(){
		$ed=new Ed;
		
		//$ed->outHtml->script('canvasjs/canvasjs.min','easyData');$ed->script('canvasjs/jquery.canvasjs.min','easyData');
		$ed->outHtml->script('canvasjs/source/canvasjs','easyData');
		//$ed->outHtml->script('canvasjs/source/jquery.canvasjs','easyData');
		$ed->outHtml->script('canvasjs/source/locale/pt-br','easyData');
		$ed->outHtml->script('CanvasJS','easyData');

		//show($this->attr);
		$p=array();
		foreach($this->attr as $k=>$v) if(!is_null($v)) $p[]=$k.'="'.htmlentities($v,ENT_QUOTES).'"';
		$p=$p?' '.implode(' ',$p):'';
		return '<div'.$p.'></div>';
	}
}