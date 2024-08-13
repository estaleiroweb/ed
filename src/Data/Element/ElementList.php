<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementList extends ElementCombo {
	function __set($var, $value) {
		if (($var == 'value' || $var == 'inputValue' || $var == 'default') && is_array($value)) $value = implode(",", $value);
		parent::__set($var, $value);
	}
	function makeContent() {
		return "{$this->htmlLabel()}<span{$this->makeHtmlAttrId()}{$this->buildStyles()}{$this->makeAttrib()}>{$this->buildOptionsValues($this->makeSource(), explode(",",$this->value))}</span>";
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		if (!$this->size) $this->set('size', max(2, count((array)$this->source)), "displayAttr");
		$val = $this->makeSource();
		$v = $this->value;
		$vExplod = explode(",", $v);
		$attr = $this->makeHtmlAttrId() . $this->makeAttrib() . $this->makeAttribInput() . $this->makeEvents() . $this->buildStyles();
		if ($this->readonly) {
			$value = array();
			foreach ($vExplod as $vItem) {
				if (!isset($val[$vItem])) $vItem = key($val);
				$value[] = htmlspecialchars(@$val[$vItem], ENT_QUOTES);
			}
			$value = implode(",", $value);
			return $this->htmlLabel() . "<input{$this->makeHtmlAttrName()} type='hidden' value='$v' /><input$attr type='text' value='$value' />";
		}
		$opt = $this->makeOptions($val, $vExplod);
		return $this->htmlLabel() . "<select multiple{$this->makeHtmlAttrName('[]')}$attr>\n\t" . implode("\n\t", $opt) . "</select>{$this->buildLinks()}";
	}
}
