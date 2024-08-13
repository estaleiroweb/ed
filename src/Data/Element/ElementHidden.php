<?php

namespace EstaleiroWeb\ED\Data\Element;

class  ElementHidden extends Element {
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'hidden';
		parent::__construct($name, $value, $id);
		$this->protect['hidden'] = true;
	}
	function __tostring() {
		$this->protect['hidden'] = true;
		return parent::__tostring();
	}
}
