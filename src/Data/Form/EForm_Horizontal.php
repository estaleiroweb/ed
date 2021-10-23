<?php

namespace EstaleiroWeb\ED\Data\Form;

class EForm_Horizontal extends EForm {
	public $args = 'class="EForm EForm-Horizontal form-horizontal"';
	public $labelWidth = 2;

	protected function parser_line($line, $fn = 'parser') {
		if (is_array($line)) {
			$out = '';
			foreach ($line as $k => $v) {
				$rest = 12 - $this->labelWidth;
				$out = "\t<div class=\"form-group\">\n";
				if (is_numeric($k)) {
					$out .= "\t\t<div class=\"col-sm-offset-{$this->labelWidth} col-sm-{$rest}\">\n";
				} else {
					$out .= "\t\t<label class=\"col-sm-{$this->labelWidth} control-label\">$k{$this->labelSeparator}</label>\n";
					$out .= "\t\t<div class=\"col-sm-{$rest}\">\n";
				}
				$out .= $this->parser_line($v, 'parser_raw');
				$out .= "\t\t</div>\n";
				$out .= "\t</div>\n";
			}
			return $out;
		} elseif (preg_match_all($this->erFields, $line, $ret, PREG_SET_ORDER + PREG_OFFSET_CAPTURE)) {
			while ($ret) {
				$item = array_pop($ret);
				$field = $item[1][0];
				$start = $item[0][1];
				$length = strlen($item[0][0]);
				$obj = $this->protect['fields'][$field];
				unset($this->protect['fields'][$field]);
				$line = substr_replace($line, $obj->hidden ? "\t$obj\n" : $this->$fn($obj), $start, $length);
			}
			$this->fields = $this->protect['fields'];
		}
		return $line;
	}
	protected function parser($obj) {
		$showLabel = $obj->showLabel;
		$label = $obj->label;
		$obj->showLabel = false;
		$rest = 12 - $this->labelWidth;

		$out = "\t<div class=\"form-group\">\n";
		if ($label && $showLabel) $out .= "\t\t<label for=\"{$obj->buildIdDisplay()}\" class=\"col-sm-{$this->labelWidth} control-label\">{$this->buildLabel($label,$obj)}</label>\n";
		$out .= "\t\t<div class=\"col-sm-{$rest}\">$obj</div>\n";
		$out .= "\t</div>\n";

		$obj->showLabel = $showLabel;
		return $out;
	}
	protected function parser_raw($obj) {
		return "<span class=\"pull-left\">$obj</span>";
	}
}
