<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementMacAddress extends Element {
	protected $typeList = array('macaddress');
	public function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'mac_address';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'macaddress';
		$this->validate = 'chkMac';
	}
	public function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$this->validate = 'chkMac' . ($this->required ? '' : 'Null');
		return parent::makeControl(array(
			'onkeypress'	=> "keypressOnlyHex(this,event)",
			'onkeyup'		=> "onkeyupMaxSizeAutoBlur(this,event,12)",
			'onfocus'		=> "onfocusOnlyHex(this,event)",
			'onblur'		=> "autoFormatMac(this,event)",
		));
	}
	public function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		//$v=str_pad(preg_replace('/[^0-9a-f]/i','',$value),12,'_');
		$v = preg_replace('/[^0-9a-f]/i', '', $value);
		if (preg_match('/^([0-9a-f_]{2})([0-9a-f_]{2})([0-9a-f_]{2})([0-9a-f_]{2})([0-9a-f_]{2})([0-9a-f_]{2})$/i', $v, $ret)) {
			unset($ret[0]);
			$value = strtolower(implode('-', $ret));
		} else $value = $this->formatError($value);
		return $value;
	}
}
