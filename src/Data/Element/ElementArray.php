<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementArray extends Element {
	public function setValue($val) {
		if (!is_null($val) && !is_array($val)) $val = (array)$val;
		$this->set('value', $val);
		return $this;
	}
	public function makeContent() {
		return var_export($this->value, true);
	}
	public function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		return var_export($this->value, true);
	}
	public function contentHidden() {
		return null;
	}
}
