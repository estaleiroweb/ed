<?php

namespace EstaleiroWeb\ED\Data\Form;

class EForm_Inline extends EForm {
	public $args = 'class="EForm EForm-Inline form-inline"';
	public $labelType = 0;

	protected function parser($obj) {
		static $lbls = array('normal', 'placeholder', 'above');
		$showLabel = $obj->showLabel;
		$placeholder = $obj->placeholder;
		$obj->showLabel = false;
		$out = "\t<div class=\"form-group\">\n";
		$out .= call_user_func(array($this, 'parser_' . $lbls[$this->labelType]), $obj, $obj->label, $showLabel);
		$out .= "\t</div>\n";
		$obj->showLabel = $showLabel;
		$obj->placeholder = $placeholder;
		return $out;
	}
	protected function parser_normal($obj, $label, $showLabel) {
		$out = '';
		if ($label && $showLabel) $out = "<label for=\"{$obj->buildIdDisplay()}\">{$label}{$this->labelSeparator}</label>";
		$out .= "$obj";
		return $out == '' ? '' : "\t\t<nobr>$out</nobr>\n";
	}
	protected function parser_placeholder($obj, $label, $showLabel) {
		if ($label) $obj->placeholder = $label;
		return "\t\t$obj\n";
	}
	protected function parser_above($obj, $label, $showLabel) {
		if ($label && $showLabel) $out = "\t\t<div><label for=\"{$obj->buildIdDisplay()}\">{$label}{$this->labelSeparator}</label></div>\n";
		$out .= "\t\t<div>$obj</div>\n";
		return $out;
	}
	protected function parser_lineIni() {
		return "\t\t<div class=\"row\"><div class=\"col-md-12\">\n";
	}
	protected function parser_lineEnd() {
		return "\t\t</div></div>\n";
	}
}
