<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementRaw extends Element {
	public function __construct($name = '', $value = null, $id = null) {
		parent::__construct($name, $value, $id);
		$this->showLabel = false;
		return $this->value;
	}
	public function makeContent() {
		return $this->value;
	}
	public function contentHidden() {
		return $this->makeContent();
	}
	public function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		return $this->makeContent();
	}
}
