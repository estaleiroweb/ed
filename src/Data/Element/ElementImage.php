<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementImage extends Element {
	protected $typeList = array('image');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'image';
		parent::__construct($name, $value, $id);
		//$this->protect['showLabel']=false;
		$this->x = false; //Largura do Thumb
		$this->y = false; //Altura do Thumb
		$this->toThumb = true;
		$this->href = '';
		$this->target = '';
		$this->title = '';
		$this->thumb = $this->OutHtml->config->php . '/thumb.php';
		$this->align = "absmiddle";
		$this->script();
	}
	function __toString() {
		$this->edit = true;
		return parent::__toString();
	}
	function makeContent() {
		if ($this->toThumb) {
			$a = array('x', 'y');
			$p = array();
			foreach ($a as $v) if ($value = $this->$v) $p[] = "$v=$value";
			$p[] = 'root=' . $this->OutHtml->config->root;
			$p[] = 'img=' . preg_replace('/\/+/', '/', '/' . $this->value);
			$thumb = $this->thumb . '?' . implode("&", $p);
			$ev = array('onclick' => "{$this->id}.click(this)");
		} else {
			$thumb = preg_replace('/\/+/', '/', '/' . $this->value);
			$ev = array();
		}
		//$ev=array('onclick'=>"alert({$this->id}).click");
		$linkFim = $linkIni = '';
		if (($href = $this->href)) {
			$target = $this->target;
			$target = $target ? ' target="' . $target . '"' : '';
			$linkIni = '<a href="' . $href . '"' . $target . '>';
			$linkFim = '</a>';
		}
		if (($title = $this->title)) $title = ' alt="' . $title . '"';
		$attr = $this->makeAttrib() . $this->makeAttribInput();
		return "$linkIni<img src='$thumb' border='0'$title {$this->buildStyles()}{$attr}{$this->makeEvents($ev)} />$linkFim";
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		return $this->makeContent();
	}
}
