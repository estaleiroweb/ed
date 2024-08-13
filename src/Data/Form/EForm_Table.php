<?php

namespace EstaleiroWeb\ED\Data\Form;

class EForm_Table extends EForm {
	private $lineStarted = false;
	public $tag = 'table';
	public $args = 'class="EForm EForm-Table" border="0" cellspacing="0" cellpadding="0" width="100%"';

	protected function parser_line($line) {
		$out = '';
		if (is_array($line)) {
			foreach ($line as $k => $v) {
				if (!is_numeric($k)) $out .= $this->buildLabel($k);
				$out .= $this->parser_line($v);
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
				if ($checkIni < $start) $out .= $this->buildLabel(substr($line, $checkIni, $checkIni - $start), null, 'td');
				$checkIni = $start + $length;
				$out .= $obj->hidden ? $obj : $this->parser($obj);
				//$line=substr_replace($line,$obj->hidden?"\t$obj\n":$this->parser($obj),$start,$length);
			}
			$this->fields = $this->protect['fields'];
		}
		return $out;
	}
	protected function parser($obj) {
		$class = get_class($obj);
		$out = '';
		if ($class == "ElementHidden") return $obj;
		if (preg_match("/^Element(Check|Radio|Button|Search)$/", $class)) {
			if (!$this->lineStarted) $out .= $this->buildLabel();
			return "$out<td>$obj</td>";
		}

		$showLabel = $obj->showLabel;
		$label = $obj->label;
		$obj->showLabel = false;

		if ($label && $showLabel) $out .= $this->buildLabel($label, $obj, 'th');
		elseif (!$this->lineStarted) $out .= $this->buildLabel();
		$out .= "\t\t<td>$obj</td>\n";

		$obj->showLabel = $showLabel;
		return $out;
	}

	protected function buildLabel($label = '', $obj = null, $tag = 'th') {
		if ($obj && $label && $obj->required) $label .= EForm::$requiredMark;
		$s = $label == '' ? '' : $this->labelSeparator;
		$out = "<$tag width='1'><nobr>$label$s</nobr></$tag>"; // id='formCell'
		if (!$this->lineStarted) {
			$this->lineStarted = true;
			$out .= '<td><table border="0" cellspacing="0" cellpadding="0">';
		}
		return $out;
	}
	protected function parser_lineIni() {
		return "<tr>";
	}
	protected function parser_lineEnd() {
		$out = $this->lineStarted ? '</table></td>' : '';
		$this->lineStarted = false;
		return $out;
	}
	protected function parser_lineBlank() {
		return "\t<tr><td colspan=2><hr></td></tr>\n";
	}
}
