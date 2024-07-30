<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementPasswd extends ElementString {
	protected $typeList = array('passwd');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'password';
		parent::__construct($name, $value, $id);
		$this->toPrint = 3; //0: nenhum , 1:View, 2: Confirm, 3: Ambos
		$this->labelConfirm = 'Confirme';
		$this->separador = 'passwd';
		$this->idSub = 'passwd';
	}
	function makeContent() {
		if ($this->toPrint == 2) return '';
		return $this->htmlLabel() . "<span{$this->makeHtmlAttrId()}{$this->buildStyles()}{$this->makeAttrib()}>" . preg_replace('/./', '*', $this->value) . '</span>';
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$divFim = $divIni = $confirm = '';
		if ($this->validate == 'confirmPasswd') {
			if ($this->toPrint == 2) return $this->makeConfirm();
			if ($this->toPrint & 2) {
				$divIni = '<' . $this->separador . '>';
				$divFim = '</' . $this->separador . '>';
				$confirm = $this->makeConfirm();
			}
		}
		if (!($this->toPrint & 1)) return '';
		$id = $this->makeHtmlAttrId();
		$name = $this->makeHtmlAttrName();
		$value = htmlspecialchars($this->inputformat ? $this->format($this->inputformat) : $this->value, ENT_QUOTES);
		$events = $this->makeEvents();
		$attr = $this->makeAttrib() . $this->makeAttribInput();
		return "$divIni{$this->htmlLabel()}<input$id$name type='password' value='$value'$attr{$this->buildStyles()}$events />$divFim$divIni$confirm$divFim";
	}
	function htmlLabel($dPoint = true, $force = false) {
		if ($this->toPrint & 1) return parent::htmlLabel($dPoint);
		elseif ($this->toPrint & 2) return $this->htmlLabelConfirm($dPoint);
	}
	function htmlLabelConfirm($dPoint = true) {
		if (!$this->edit) return '';
		$label = $this->label;
		$this->label = $this->labelConfirm;
		$htmlLabel = parent::htmlLabel($dPoint);
		$this->label = $label;
		return $htmlLabel;
	}
	function makeConfirm() {
		if (!$this->edit) return '';
		$name = $this->makeHtmlAttrName();
		$value = htmlspecialchars($this->inputformat ? $this->format($this->inputformat) : $this->value, ENT_QUOTES);
		$events = $this->makeEvents();
		$attr = $this->makeAttrib();
		$confirm = "{$this->htmlLabelConfirm()}<input{$this->makeHtmlAttrId('',$this->idSub)} type='password' value='$value'$attr{$this->buildStyles()}$events />";
		return $confirm;
	}
}
