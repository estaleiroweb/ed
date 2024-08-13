<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementCep extends Element {
	protected $typeList = array('cep');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'cep';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'cep';
		$this->validate = 'chkCep';
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$n = $this->required ? '' : 'Null';
		$this->validate = preg_replace('/(chkCep)(Null)?/', '\1' . $n, $this->validate);
		return parent::makeControl(array(
			'onkeypress'	=> "keypressOnlyNum(this,event)",
			'onkeyup'		=> "onkeyupMaxSizeAutoBlur(this,event,8)",
			'onfocus'		=> "onfocusOnlyNum(this,event)",
			'onblur'		=> "autoFormatCep(this,event)",
		));
	}
	function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		if ($this->isEdit() || $this->forceEdit) return $value;
		if (preg_match('/^(\d{5})(\d{3})$/', $value, $ret)) return "{$ret[1]}-{$ret[2]}";
		return $this->formatError($value);
	}
}
