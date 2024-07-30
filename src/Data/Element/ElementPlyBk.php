<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementPlyBk extends Element {
	protected $typeList = array('playback');
	public $split = ',';
	public $play = true;
	public $showPlay = true;
	public $showFF = true;
	public $showStop = true;
	public $showRew = true;
	public $showBox = true;
	public $showBorder = true;
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'play_back';
		parent::__construct($name, $value, $id);
		$this->itens = false;
		$this->start = true;
		$this->time = 1000;
		$this->action = '';
		if ($value) $this->itens = $value;
	}
	function __set($var, $value) {
		switch ($var) {
			case 'itens':
				if (is_array($value)) $value = array_values($value);
				else $value = explode($this->split, $value);
				$this->set($var, $value);
				break;
			default:
				parent::__set($var, $value);
		}
	}
	function __toString() {
		$this->script();
		$this->style();
		$ret = "<span{$this->makeHtmlAttrId($this->preIdMain)} class='{$this->type}{$this->makeClass()}'>";
		if ($this->isEdit()) {
			$ret .= $this->makeControl();
		} else $ret .= $this->makeContent();
		$ret .= "</span>";
		return $ret;
	}
	function makeContent() {
		$ret = $this->makeControl();
		return $ret;
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$id = $this->makeHtmlAttrId();
		$play = (int)$this->play;
		$ret = "<div$id class='PlayBack' value='$play'>\r\n";
		if ($this->showBorder) {
			$ret .= "<table border='0' cellspacing='0'>\r\n";
			$ret .= "<tr><td class='PlayBackTL'></td><td class='PlayBackTC'></td><td class='PlayBackTR'></td></tr>\r\n";
			$ret .= "<tr><td class='PlayBackML'></td><td class='PlayBackMC'>";
		}
		if ($this->showPlay) $ret .= $this->button('Play', $this->start ? 'pBkPause' : 'pBkPlay');
		if ($this->showStop) $ret .= $this->button('Stop', 'pBkStop');
		if ($this->showRew) $ret .= $this->button('Rew', 'pBkRew');
		if ($this->showBox) $ret .= $this->button('Box', 'pBkBox', "1/" . count((array)$this->itens));
		if ($this->showFF) $ret .= $this->button('FF', 'pBkFF');
		if ($this->showBorder) {
			$ret .= "</td><td class='PlayBackMR'></td></tr>\r\n";
			$ret .= "<tr><td class='PlayBackBL'></td><td class='PlayBackBC'></td><td class='PlayBackBR'></td></tr>\r\n";
			$ret .= "</table>\r\n";
		}
		$ret .= "</div>\r\n";
		return $ret;
	}
	function isEdit() {
		return true;
	}
	function button($type, $class, $value = '', $id = '') {
		$id = $this->makeHtmlAttrId($type . "_");
		return "<div$id class='$class' onclick='{$this->id}.set$type()'>$value</div>";
	}
}
