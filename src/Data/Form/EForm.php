<?php

namespace EstaleiroWeb\ED\Data\Form;

use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\Traits\GetterAndSetter;

class EForm {
	use GetterAndSetter;
	static public $requiredMark = '*';

	protected $erFields = '';
	protected $content = '';
	public $tag = 'div';
	public $args = 'class="EForm EForm-Normal"';
	public $labelSeparator = ':&nbsp;';

	public function __construct(array $fields = array()) {
		$this->fields = $fields;
	}
	public function __toString() {
		OutHtml::singleton()->style(__CLASS__, 'ed');
		return "<{$this->tag} {$this->args}>\n{$this->content}</{$this->tag}>\n";
	}
	public function __invoke() {
		$args = func_get_args();

		if ($args) {
			$this->content .= $this->parser_lineIni();
			foreach ($args as $v) $this->content .= $this->parser_line($v);
			$this->content .= $this->parser_lineEnd();
		} else $this->content .= $this->parser_lineBlank();
		//show(array_keys($this->fields));
		return $this;
	}
	final public function set_fields(array $fields) {
		if ($fields) {
			$this->protect['fields'] = $fields;
			$this->erFields = array();
			foreach ($fields as $k => $v) $this->erFields[] = preg_quote($k, '/');
			$this->erFields = '/[,; ]?\b(' . implode('|', $this->erFields) . ')\b[,; ]?/';
		} else {
			$this->protect['fields'] = array();
			$this->erFields = '';
		}
	}
	protected function parser_line($line) {
		if (is_array($line)) {
			$out = '';
			foreach ($line as $k => $v) {
				if (!is_numeric($k)) {
					$out .= "\t<div class=\"form-group\">\n";
					$out .= "\t\t<label>$k{$this->labelSeparator}</label>\n";
					$out .= "\t</div>\n";
				}
				$out .= $this->parser_line($v);
			}
			return $out;
		} elseif ($this->erFields && preg_match_all($this->erFields, $line, $ret, PREG_SET_ORDER + PREG_OFFSET_CAPTURE)) {
			while ($ret) {
				$item = array_pop($ret);
				$field = $item[1][0];
				$start = $item[0][1];
				$length = strlen($item[0][0]);
				$obj = $this->protect['fields'][$field];
				unset($this->protect['fields'][$field]);
				$line = substr_replace($line, $obj->hidden ? "\t$obj\n" : $this->parser($obj), $start, $length);
			}
			$this->fields = $this->protect['fields'];
		}
		return $line;
	}
	protected function parser($obj) {
		$showLabel = $obj->showLabel;
		$label = $obj->label;
		$obj->showLabel = false;

		$out = "\t<div class=\"form-group\">\n";
		if ($label && $showLabel) $out .= "\t\t<label for=\"{$obj->buildIdDisplay()}\">{$this->buildLabel($label,$obj)}</label>\n";
		$out .= "\t\t$obj\n";
		$out .= "\t</div>\n";

		$obj->showLabel = $showLabel;
		return $out;
	}
	protected function parser_lineIni() {
		return '';
	}
	protected function parser_lineEnd() {
		return '';
	}
	protected function parser_lineBlank() {
		return "\t<hr>\n";
	}
	protected function buildLabel($label, $obj = null) {
		if ($obj && $obj->required) $label .= EForm::$requiredMark;
		return $label . $this->labelSeparator;
	}
}
