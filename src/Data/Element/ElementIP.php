<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementIP extends Element {
	protected $typeList = array('ip');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'ip';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'ip';
		//$this->validate='chkIP';
		$this->placeholder = '0.0.0.0';
		$this->size = 15;
		$this->maxlength = 15;
		$this->width = '10em';
		//$this->OutHtml->script('validateform','easyData');
	}
	//function makeControl($moreEvents=array(),$moreAttr=array()){
	//$this->script();
	//$n=$this->required?'':'Null';
	//$this->validate=preg_replace('/(chkIP)(Null)?/','\1'.$n,$this->validate);
	//return parent::makeControl();
	//}
	function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		if ($this->isEdit() || $this->forceEdit) return $value;
		if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/', $value, $ret)) {
			unset($ret[0]);
			$nok = false;
			foreach ($ret as $k => $v) if ($v > 255) {
				$nok = true;
				break;
			} else $ret[$k] = (int)$v;
			if (!$nok) return implode('.', $ret);
		}
		return $this->formatError($value);
	}
}
