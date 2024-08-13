<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementIPv6 extends Element {
	protected $typeList = array('ipv6');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'ipv6';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'ipv6';
		$this->validate = 'chkIPv6';
		$this->size = 39;
		$this->maxlength = 39;
		$this->width = '20em';
		$this->OutHtml->script('validateform', 'easyData');
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$n = $this->required ? '' : 'Null';
		$this->validate = preg_replace('/(chkIPv6)(Null)?/', '\1' . $n, $this->validate);
		return parent::makeControl(array(
			'onkeypress'	=> "keypressOnlyIPv6(this,event)",
			'onkeyup'		=> "onkeyupMaxSizeAutoBlur(this,event,39)",
			'onfocus'		=> "onfocusOnlyIPv6(this,event)",
			'onblur'		=> "autoFormatIPv6(this,event)",
		));
	}
	function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		if ($this->isEdit() || $this->forceEdit) return $value;
		$v = preg_replace('/::/', ':', $value, 1);
		if (!preg_match('/::/', $v)) {
			if (
				preg_match('/^[0-9a-f]{1,4}(:[0-9a-f]{1,4}){7}$/i', $value) ||
				preg_match('/^:(:[0-9a-f]{1,4}){1,7}$/i', $value) ||
				preg_match('/^([0-9a-f]{1,4}:){1,7}:$/i', $value) ||
				preg_match('/^[0-9a-f]{1,4}(:[0-9a-f]{1,4}){1,6}$/i', $v)
			) return $value;
		}
		return $this->formatError($value);
	}
}
