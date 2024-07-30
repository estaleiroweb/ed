<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementRadio extends ElementCombo {
	protected $typeList = array('radio');
	public function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'radio';
		$this->protect['divBegin'] = '';
		$this->protect['divEnd'] = '';
		parent::__construct($name, $value, $id);
		$this->protect['separador'] = '';
	}
	public function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->protect['strRequired'] = '';
		$id = $this->makeHtmlAttrId();
		$name = $this->makeHtmlAttrName();
		$events = $this->makeEvents();
		$attr = $this->makeAttrib() . $this->makeAttribInput();
		$styles = $this->buildStyles();

		$source = $this->makeSource();
		$radios = array();
		foreach ($source as $k => $v) {
			$chk = $this->value == $k ? ' checked' : '';
			$radios[] = "<input$id$name type='radio' value='$k'$chk$attr$events$styles /><label>$v</label>";
		}
		$begin = $this->divBegin ? $this->divBegin : '';
		$end = $this->divEnd ? $this->divEnd : '';
		return $this->htmlLabel() . $begin . implode($end . $this->separador . $begin, $radios) . $end . $this->buildLinks();
	}
}
