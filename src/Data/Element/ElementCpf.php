<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementCpf extends Element {
	protected $typeList = array('cpf');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'cpf';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'cpf';
		$this->validate = 'chkCPF';
		$this->size = 14;
		$this->maxlength = 14;
		$this->width = '9em';
		$this->OutHtml->script('validateform', 'ed');
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$n = $this->required ? '' : 'Null';
		$this->validate = preg_replace('/(chkCPF)(Null)?/', '\1' . $n, $this->validate);
		return parent::makeControl(array(
			'onkeypress'	=> "keypressOnlyNum(this,event)",
			'onkeyup'		=> "onkeyupMaxSizeAutoBlur(this,event,11)",
			'onfocus'		=> "onfocusOnlyNum(this,event)",
			'onblur'		=> "autoFormatCpf(this,event)",
		));
	}
}
