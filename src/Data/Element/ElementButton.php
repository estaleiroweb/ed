<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementButton extends Element {
	protected $typeList = array('button', 'reset', 'submit');
	function __construct($name = '', $value = null, $id = null) {
		$this->protect['noShowWhenView'] = false;
		$this->protect['icon'] = '';
		$this->displayAttr['noValidate'] = true;
		$this->protect['showLabel'] = false;
		$this->addClass('btn btn-default');
		parent::__construct($name, $value, $id);
	}
	function makeShowOut($out = '') {
		return $out;
	}
	function makeContent() {
		if ($this->noShowWhenView) return '';
		else return $this->makeControl();
	}
	function makeControl($moreEvents = array(), $moreAttr = array(), $tp = 'button') {
		$this->script();
		$value = $this->value;
		$onclick = $value ? ['onclick' => $this->func($value)] : [];
		//$value=$this->value;
		if (is_null($label = $this->label)) $label = $this->id;
		$icon = $this->icon ? '<span class="glyphicon ' . $this->icon . '"></span> ' : '';
		$attr = $this->makeAttrib() . $this->makeAttribInput();
		$this->value = null;
		$out = "<button{$this->makeHtmlAttrId()} type='{$this->type}'{$attr}{$this->buildStyles()}{$this->makeEvents($onclick)}>$icon$label</button>";
		$this->value = $value;
		return $out;
	}
	function contentHidden() {
		return '';
	}
	function link($url, $fields) {
		$fields = explode(",", $fields);
		$param = array();
		foreach ($fields as $k) if (isset($this->form->fields[$k])) {
			$param[] = "$k={$this->form->fields[$k]->value}";
		}
		if (!$param) return;
		$url .= (strpos($url, '?') === false) ? '?' : '&';
		$url .= implode('&', $param);
		$this->value = "window.open('$url')";
	}
}
