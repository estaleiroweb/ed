<?php
class Fieldset extends Tag {
	protected $protect=array(
		'tag'=>'fieldset',
		'content'=>'',
		'legend'=>'',
		'opened'=>true,
	);

	public function __construct() {
		new JQuery();
		OutHtml::singleton()->script(__class__,'easyData')->style(__class__,'easyData');
		$this->add('class','ed');
	}
	public function __invoke(){
		$args=func_get_args();
		if($args) $this->content=array_shift($args);
		if($args) $this->legend=array_shift($args);
		$this->opened=$args?(bool)array_shift($args):true;
		return $this->__toString();
	}
	public function __toString(){
		$this->drop('class','closed');
		$div='<div>';
		if(!$this->opened){
			$div='<div style="display:none;">';
			$this->add('class','closed');
		}
		$out=$this->startTag(self::TAB_INIT);
		if($this->legend) $out.=self::TAB_INIT.self::TAB.'<legend>'.$this->legend.'</legend>'.self::LF;
		$out.=$div;
		$out.=$this->trContent($this->content,self::TAB_INIT.self::TAB);
		$out.='</div>';
		$out.=$this->endTag(self::TAB_INIT);
		return $out;
	}
}