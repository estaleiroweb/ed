<?php

namespace EstaleiroWeb\ED\Screen;

use Scr\OutHtml;
class Mermaid { // extends ExternalPlugins
	protected $codes = [];

	public function __construct($mermaidRaw = null, $class = null) {
		$outHtml = OutHtml::singleton();
		$outHtml->style(__CLASS__, '/easyData/skin/default/css');
		$outHtml->addHead('<script type="module">import {mermaidDom} from "/easyData/js/Mermaid.js";</script>', 'mermaid');
		$this->__invoke($mermaidRaw, $class);
	}
	public function __invoke($mermaidRaw, $class = null) {
		if (!$mermaidRaw) return;
		$html = "<div class='mermaid$class'>$mermaidRaw</div>";
		$this->codes[] = $html;
		if ($class) $class = " $class";
		return $html;
	}
	public function __toString() {
		return implode('', $this->codes);
	}
}
