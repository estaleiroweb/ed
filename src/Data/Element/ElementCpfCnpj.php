<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementCpfCnpj extends Element {
	protected $typeList = array('cpfcnpj');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'cpf cnpj';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'cpfcnpj';
		$this->validate = 'chkCPF_CNPJ';
		$this->size = 16;
		$this->maxlength = 16;
		$this->width = '10em';
		$this->OutHtml->script('validateform', 'ed');
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$n = $this->required ? '' : 'Null';
		$this->validate = preg_replace('/(chkCPF_CNPJ)(Null)?/', '\1' . $n, $this->validate);
		return parent::makeControl(array(
			'onkeypress'	=> "keypressOnlyNum(this,event)",
			'onkeyup'		=> "onkeyupMaxSizeAutoBlur(this,event,14)",
			'onfocus'		=> "onfocusOnlyNum(this,event)",
			'onblur'		=> "autoFormatCpfCnpj(this,event)",
		));
	}
}
