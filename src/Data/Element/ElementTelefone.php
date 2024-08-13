<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementTelefone extends Element {
	protected $typeList = array('telefone');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'telefone';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'telefone';
		$this->size = 13;
		$this->width = '9em';
		$this->validate = 'chkTelefone';
		$this->OutHtml->script('validateform', 'easyData');
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$n = $this->required ? '' : 'Null';
		$this->validate = preg_replace('/(chkTelefone)(Null)?/', '\1' . $n, $this->validate);
		return parent::makeControl(array(
			'onkeypress'	=> "keypressOnlyNum(this,event)",
			'onkeyup'		=> "onkeyupMaxSizeAutoBlur(this,event,11)",
			'onfocus'		=> "onfocusOnlyNum(this,event)",
			'onblur'		=> "autoFormatTelefone(this,event)",
		));
	}
	function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		if (!$this->isEdit() && !$this->forceEdit) {
			if (preg_match('/^(0\d{3})-?(\d{2,3})-?(\d{4})$/', $value, $ret)) return "{$ret[1]}-{$ret[2]}-{$ret[3]}";
			elseif (preg_match('/^\(?([1-9]{2})?\)?(\d{4,5})-?(\d{4})$/', $value, $ret)) return ($ret[1] ? "({$ret[1]})" : '') . "{$ret[2]}-{$ret[3]}";
			elseif (preg_match('/^\(?([1-9]{2})?\)?(\d{3,5})$/', $value, $ret)) return ($ret[1] ? "({$ret[1]})" : '') . "{$ret[2]}";
			return $this->formatError($value);
		}
		return $value;
	}
}
