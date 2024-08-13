<?php

namespace EstaleiroWeb\ED\Data\Form;

class EForm_Col extends EForm {
	private $line = array();
	private $hasLabel = false;
	private $totalLine = 0;
	public $labelType = 0;
	public $tag = 'div';
	public $args = 'class="EForm EForm-Col"';

	protected function parser_line($line) {
		if (is_array($line)) {
			foreach ($line as $k => $v) {
				if (!is_numeric($k) && $k) $this->buildLabel($k);
				$this->parser_line($v);
			}
		} elseif (preg_match_all($this->erFields, $line, $ret, PREG_SET_ORDER + PREG_OFFSET_CAPTURE)) {
			$checkIni = 0;
			while ($ret) {
				$item = array_shift($ret);
				$field = $item[1][0];
				$start = $item[0][1];
				$length = strlen($item[0][0]);
				$obj = $this->protect['fields'][$field];
				unset($this->protect['fields'][$field]);
				if ($checkIni < $start) {
					$val = substr($line, $checkIni, $checkIni - $start);
					$this->line[] = array('content' => $val, 'length' => $this->len($val), 'html' => true);
				}
				$checkIni = $start + $length;
				$this->parser($obj);

				//$line=substr_replace($line,$obj->hidden?"\t$obj\n":$this->parser($obj),$start,$length);
			}
			$this->fields = $this->protect['fields'];
		}
		return '';
	}
	protected function parser($obj) {
		$class = get_class($obj);
		if ($obj->hidden || $class == 'ElementHidden') {
			return $this->line[] = array('content' => $obj, 'length' => 0);
		}
		$calc = $this->calc($obj);
		if (preg_match("/^Element(Check|Radio|Button|Search)$/", $class)) {
			return $this->line[] = array('content' => "$obj", 'length' => $calc, 'html' => true);
		}
		$showLabel = $obj->showLabel;
		$label = $obj->label;
		$obj->showLabel = false;
		$obj->width = '100%';

		if ($this->labelType) {
			if ($label && $showLabel) {
				$this->hasLabel = true;
				$this->buildLabel($label, $obj);
				$this->line[] = array('content' => "$obj", 'length' => $calc);
			} else $this->line[] = array('content' => "$obj", 'length' => $calc, 'html' => true);
		} else {
			if ($label && $showLabel) $this->buildLabel($label, $obj);
			$this->line[] = array('content' => "$obj", 'length' => $calc);
		}
		$obj->showLabel = $showLabel;
	}
	protected function buildLabel($label = '', $obj = null) {
		if ($label == '') return;
		if ($obj && $obj->required) $label .= EForm::$requiredMark;
		$label .= $this->labelSeparator;
		$this->line[] = array('content' => "<label>$label</label>", 'length' => $this->len($label), 'html' => $this->labelType);
	}
	protected function parser_lineIni() {
		$this->line = array();
		$this->hasLabel = false;
		$this->totalLine = 0;
		return "\t<div class=\"row\">\n";
	}
	protected function parser_lineEnd() {
		$out = '';
		$TRest = $this->totalLine;
		$CRest = 12;
		while ($this->line) {
			$l = array_shift($this->line);
			if ($l['length']) {
				$x = array('len' => $l['length'], 'CRest' => $CRest, 'TRest' => $TRest);
				$col = $TRest == $l['length'] || !$this->line ? $CRest : min($CRest, max(1, round($l['length'] * 12 / $this->totalLine)));

				$x['col'] = $col;
				//show($x);

				$out .= "\t\t<div class=\"col-sm-$col\">\n";
				if ($this->labelType == 1 && $this->hasLabel && @$l['html']) {
					$l['content'] = "<div>&nbsp;</div><div>{$l['content']}</div>";
				}
				$out .= "\t\t\t{$l['content']}\n";
				$out .= "\t\t</div>\n";
				$TRest -= $l['length'];
				$CRest -= $col;
			} else $out .= "\t\t{$l['content']}\n";
		}
		return "{$out}\t</div>\n";
	}
	protected function parser_lineBlank() {
		return "\t<div class=\"row\"><div class=\"col-md-12\"><hr></div></div>\n";
	}
	protected function len($val) {
		$tam = strlen(strip_tags($val));
		$this->totalLine += $tam;
		return $tam;
	}
	protected function calc($obj) {
		static $unit_conv = array(
			'px' => 16,
			'pt' => 12,
			'mm' => 4.23333,
			'cm' => .423333,
			'in' => .125, //96px = 2.54cm
			'pc' => 1,
			'em' => 1,
			'rem' => 1,
			'%' => 1,
		); //=1char
		static $def_tam = array(
			'ElementNumber' => 10,
			'ElementString' => 10,
			'ElementText' => 100,
		);
		$class = get_class($obj);
		if (preg_match("/^Element(Check|Radio|Button|Search)$/", $class)) {
			$tam = $this->len($obj->label) + 2;
		} else {
			$tam = $obj->width;
			if ($tam == '') ($tam = @$def_tam[$class]) || ($tam = 5);
			else {
				if (!preg_match('/^\s*(\d+(\.\d*)?|(\.\d+))\s*(.*)/', $tam, $ret)) $ret = array('100%', 100, '%');
				$tam = (int)$ret[1];
				$unit = strtolower($ret[2]);
				if (array_key_exists($unit, $unit_conv)) $tam /= $unit_conv[$unit];
			}
		}
		$this->totalLine += $tam;
		return $tam;
	}
}
