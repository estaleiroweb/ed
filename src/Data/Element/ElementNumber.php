<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementNumber extends Element {
	protected $typeList = array('number');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'number';
		$this->displayAttr['min'] = null;
		$this->displayAttr['max'] = null;
		$this->displayAttr['unsigned'] = null;
		$this->displayAttr['range'] = null;
		$this->displayAttr['step'] = null;
		$this->displayAttr['rangelength'] = null;

		$this->displayAttr['scale'] = null;
		$this->displayAttr['isdecimal'] = null;
		$this->displayAttr['precision'] = null;
		parent::__construct($name, $value, $id);
		$this->style['text-align'] = 'right';
	}
	function __set($var, $value) {
		if (preg_match('/^(value|inputValue|default)$/', $var) && !is_null($value) && $value !== '') $value += 0;
		parent::__set($var, $value);
	}
	private function prepareValue() {
		if ($this->isdecimal) {
			if (!is_null($this->precision)) $this->value = round($this->value, $this->precision);
		} elseif (!is_null($this->value) && $this->value != '') {
			//} elseif(!is_null($this->isdecimal)) {
			//if($this->label=='CpeId') print __LINE__.":[$this->label] {$this->value}--<br>";
			$this->value = round($this->value);
		}
		if ($this->unsigned && $this->value < 0) $this->value = abs($this->value);
	}
	function makeContent() {
		$this->prepareValue();
		return parent::makeContent();
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$this->prepareValue();
		$id = $this->id;
		return parent::makeControl();
	}
}
