<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementText extends Element {
	protected $typeList = array('textarea');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'text';
		unset($this->displayAttr['value']);
		$this->protect['value'] = '';
		parent::__construct($name, $value, $id);
		$this->width = '100%';
		$this->wrap = 'off';
		$this->style();
	}
	function makeContent() {
		$value = $this->func($this->value, $this->name);
		$estilos = ($this->wrap == 'off') ? array() : array(
			'white-space' => 'pre-wrap',      /* css-3 */
			'white-space' => '-moz-pre-wrap', /* Mozilla, since 1999 */
			'white-space' => '-pre-wrap',     /* Opera 4-6 */
			'white-space' => '-o-pre-wrap',   /* Opera 7 */
			'word-wrap'  => 'break-word',    /* Internet Explorer 5.5+ */
		);
		$attr = $this->makeHtmlAttrId();
		$attr .= $this->makeAttrib() . $this->makeAttribInput();
		$attr .= $this->buildStyles($estilos);

		return "{$this->htmlLabel()}<span{$attr}><samp>{$value}</samp></span>";
		//return "{$this->htmlLabel()}<span{$this->buildStyles()}{$this->makeAttrib()}><pre>{$this->buildValue()}</pre></span>";
	}
	function buildValue() {
		return preg_replace('/(\r\n?|\n\r?)/', '<br>', $this->func(htmlspecialchars($this->value, true), $this->value));
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$id = $this->makeHtmlAttrId();
		$name = $this->makeHtmlAttrName();
		$events = $this->makeEvents();
		$attr = $this->makeAttrib() . $this->makeAttribInput();
		$v = $this->value;
		return $this->outControl("{$this->htmlLabel()}<textarea$id$name$attr$events{$this->buildStyles()}>{$v}</textarea>");
	}
}
