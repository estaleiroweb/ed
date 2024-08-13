<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementCheck extends Element {
	protected $typeList = array('check');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'check';
		$this->addClass('checkbox');
		parent::__construct($name, $value, $id);
		$this->protect['strRequired'] = '';
		$this->style();
	}
	function makeShowOut($out = '') {
		return $out;
	}
	function makeContent() {
		//return $this->htmlLabel($this->buildValue());
		$check = (int)$this->checked() ? ' checked' : '';
		//$attr=$this->makeAttrib().$this->makeAttribInput();
		$attr = $this->makeAttribInput();
		return $this->htmlLabel("<input{$this->makeHtmlAttrId()} onclick='return false;' type='checkbox'$check$attr />");
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$idDisplay = $this->makeHtmlAttrId();
		$id = $this->makeHtmlAttrId($this->preIdInput);
		$name = $this->makeHtmlAttrName();
		$value = (int)$this->checked();
		$check = $value ? ' checked' : '';
		//$this->events['onclick']=(@$this->events['onclick']?"{$this->events['onclick']};":"")."{$this->id}.change(this)";
		$attr = $this->makeAttribInput();
		if ($this->readonly) {
			$ev = ['onclick' => 'return false;'];
			$attr .= ' DISABLED';
		} else $ev = [];
		$events = $this->makeEvents($ev);
		//$attr=$this->makeAttrib().$this->makeAttribInput();
		//$d=!$this->disabled && $this->readonly?' disabled':'';
		//show($value);
		return $this->htmlLabel("<input$id$name type='hidden' value='$value' /><input$idDisplay type='checkbox'$check$attr$events />");
	}
	function buildValue() {
		return '<span class="glyphicon glyphicon-' . ($this->checked() ? 'check' : 'unchecked') . '" aria-hidden="true"></span>&nbsp;';
	}
	public function getValue() {
		$value = parent::getValue();
		return $this->required ? (int)$value : $value;
	}
	function checked() {
		$value = $this->value;
		return !($value === false || is_null($value) || $value === '' || $value === "\x0" || preg_match("/^(falso|false|f|desligado|down|off|0)$/i", $value));
	}
	function htmlLabel($html = true, $force = false) {
		$acceskey = '';
		$label = $this->label;
		if (preg_match('/&(\w)/', $label, $ret)) {
			$acceskey = " accesskey='{$ret[1]}'";
			$this->set('title', $this->title . "[Alt+{$ret[1]}]");
			$label = preg_replace('/&(\w)/', '<span>\1</span>', $label);
		}
		$for = '';
		if ($this->id) $for = " for='{$this->OutHtml->htmlSlashes($this->preIdDisplay .$this->id)}'";
		$r = ($this->required || $this->validate === '') && !$this->auto_increment && $this->isEdit() ? $this->strRequired : '';
		$class = get_class($this);
		if ($this->label && $this->showLabel) $html = "<label$acceskey class='$class'>$html$label</label>";
		else $this->removeClass('checkbox');
		$attr = $this->makeAttrib();
		//return "<span ed-element=\"check\" ed-class=\"ElementCheck\" label=\"Eu\" class=\"checkbox\">$html</span>";
		return "<span$attr>$html</span>";

		//<span ed-element="check" ed-class="ElementCheck" label="Eu" class="checkbox"><input id="i_id2" name="Eu" type="hidden" value="1"><input id="d_id2" type="checkbox" checked="" value="1"></span>

	}
}
