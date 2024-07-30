<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementAssoc extends Element {
	protected $typeList = array('assoc');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'assoc';
		parent::__construct($name, $value, $id);
	}
	function setSource($view, $key = true, $fields = null) {
		if (!preg_match("/\s/", trim($view))) $view = "SELECT * FROM $view";
		if (is_string($fields)) $fields = preg_split("/\s*[;,]\s*/", trim($fields));
		$this->source = array('view' => $view, 'key' => $key, 'fields' => $fields);
	}
	function setTarget($tbl, $keySoruce, $keyTarget) {
		if (!is_array($keyTarget)) return;
		$this->target = array('tbl' => $tbl, 'keySoruce' => $keySoruce, 'keyTarget' => $keyTarget);
	}
	function makeContent() {
		if ($v = $this->buildListValues(" NOT")) $v = "<ul>\n\t<li>" . implode("</li>\n\t<li>", $v) . "</li>\n</ul>";
		else $v = "sem conteúdo";
		return "{$this->htmlLabel()}<span{$this->makeHtmlAttrId()}{$this->buildStyles()}>$v</span>";
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$source = $this->makeSource();
		$v = $source ? "$source{$this->buildButtons()}{$this->buildTarget()}" : "sem conteúdo";
		return "{$this->htmlLabel()}<span id='Edit_{$this->id}' class='ElementAssoc_Edit'{$this->buildStyles()}>$v</span>";
	}
	function makeSource($force = true, &$value = null) {
		return $this->buildSelectTag($this->buildListValues(), "ElementAssoc_Source");
	}
	function buildButtons() {
		$a = array('SourceAll' => 'addAll', 'SourceOne' => 'add', 'TargetOne' => 'sub', 'TargetAll' => 'subAll');
		$id = $this->id;
		foreach ($a as $k => $v) $a[$k] = "<div id='ElementAssoc_$k' onclick='$id.addAll(this)' onmouseover='$id.over(this)' onmouseout='$id.out(this)'></div>\n";
		return "<span id='ElementAssoc_Buttons'>\n" . implode("", $a) . "</span>\n";
	}
	function buildTarget() {
		return $this->buildSelectTag($this->buildListValues(" NOT"));
	}
	function buildSQL($not = "") {
		if (!$this->source || !$this->target) return array();
		$tbl = trim($this->protect['source']['view']);
		$tbl = preg_match("/\s/", $tbl) ? "($tbl)" : $tbl;
		return "
			SELECT s.* FROM $tbl s 
			LEFT JOIN {$this->protect['target']['tbl']} t 
			ON s.{$this->protect['source']['key']}=t.{$this->protect['target']['keySoruce']}{$this->buildWhereTarget()}
			WHERE t.{$this->protect['target']['keySoruce']} IS$not NULL
		";
	}
	function buildListValues($not = '') {
		if (!$this->conn) return array();
		$sql = $this->buildSQL($not);
		return $this->buildSqlListArray($sql, $this->protect['source']['key'], $this->protect['source']['fields']);
	}
	function buildSelectTag($array, $class = "ElementAssoc_Target") {
		$id = '';
		$name = '';
		if ($class == "ElementAssoc_Target") {
			//$id=$this->makeHtmlAttrId();
			$name = $this->makeHtmlAttrName();
		}
		if ($opt = $this->makeOptions($array)) return "<select multiple='multiple' id='$class'$id$name onchange='{$this->id}.select(this)'>\n\t" . implode("\n\t", $opt) . "</select>";
		return '';
	}
	function buildWhereTarget() {
		$where = array();
		if ($arr = (array)$this->protect['target']['keyTarget']) {
			foreach ($arr as $k => $v) $where[] = "t.$k='$v'";
			$where = " AND " . implode(" AND ", $where);
		}
		return $where;
	}
	public function update($data = null) {
	}
}
