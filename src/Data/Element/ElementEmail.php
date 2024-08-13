<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementEmail extends Element {
	protected $typeList = array('email');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'email';
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'email';
		$this->width = '34em';
		$this->validate = 'chkEmail';
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		//$this->OutHtml->script('validateform','ed');
		$n = $this->required ? '' : 'Null';
		$this->validate = preg_replace('/(chkEmail)(Null)?/', '\1' . $n, $this->validate);
		return parent::makeControl();
	}
	function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		if ($this->isEdit() || $this->forceEdit) return $value;
		$email = preg_split('/\s*[,;]\s*/', $value);
		foreach ($email as $v) {
			if (!preg_match('/^[0-9a-z][0-9a-z\-_]*(\.[0-9a-z][0-9a-z\-_]*)*@[0-9a-z][0-9a-z\-_]*(\.[0-9a-z][0-9a-z\-_]*)+$/i', $v)) {
				return $this->formatError($value);
			}
		}
		return $value;
	}
}
