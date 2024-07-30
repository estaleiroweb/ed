<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementCn extends Element {
	protected $typeList = array('cn');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'cn';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'cn';
		$this->validate = 'chkOnly19';
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$n = $this->required ? '' : 'Null';
		$this->validate = preg_replace('/(chkOnly19)(Null)?/', '\1' . $n, $this->validate);
		return parent::makeControl(array(
			'onkeypress'	=> "keypressOnly19(this,event)",
			'onkeyup'		=> "onkeyupMaxSizeAutoBlur(this,event,2)",
			'onfocus'		=> "onfocusOnly19(this,event)",
			'onblur'		=> "autoFormatCn(this,event)",
		));
	}
	function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		if ($this->isEdit() || $this->forceEdit) return $value;
		if (preg_match('/^[1-9]{2}$/', $value, $ret)) return $value;
		return $this->formatError($value);
	}
}
