<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementGroupCheck extends Element {
	protected $typeList = array('groupcheck');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'group_check';
		parent::__construct($name, $value, $id);
		$this->separador = ' - ';
		$this->mov('required', 'displayAttr');
		//$this->mov('class','inputAttr');
		$this->mov('class');
	}
	//function setSource($view,$key,$fields){
	//	if (!preg_match("/\s/",trim($view))) $view="SELECT * FROM $view";
	//	if (is_string($fields)) $fields=preg_split("/\s*[;,]\s*/",trim($fields));
	//	$this->source=array('view'=>$view,'key'=>$key,'fields'=>$fields);
	//}
	function setTarget($tbl, $keySoruce) {
		$this->target = array('tbl' => $tbl, 'keySoruce' => $keySoruce);
	}
	function makeContent() {
		$out = "{$this->htmlLabel()}<div{$this->makeHtmlAttrId()}{$this->makeAttrib()}>";

		$attr = $this->makeAttribInput();
		$attr .= $this->buildStyles();

		$source = $this->makeSource(false, $value);
		$sep = ($sep = $this->separador) ? "<span>{$this->separador}</span>" : ' ';
		foreach ($value as $k => $l) {
			if (array_key_exists($k, $source)) $l = $source[$k];
			$label = htmlspecialchars($this->inputformat ? $this->format($this->inputformat, $l) : $l, ENT_QUOTES);
			$out .= "<span class='select-item'><label$attr>$label</label>{$sep}</span>";
		}
		$out .= '</div>';
		return $out;
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$out = "{$this->htmlLabel()}<div class='checkbox'{$this->makeHtmlAttrId()}{$this->makeAttrib()}>";

		$attr = $this->makeAttribInput();
		$attr .= $this->buildStyles();
		$attr .= $this->makeEvents();

		$source = $this->makeSource(false, $value);
		$sep = ($sep = $this->separador) ? "<span>{$this->separador}</span>" : ' ';
		if ($source) {
			$out .= "<span class='select-group'><label$attr class='control-label'><input type='checkbox' />Selecionar Tudo/Nada</label>{$sep}</span>";
			foreach ($source as $k => $l) {
				$label = htmlspecialchars($this->inputformat ? $this->format($this->inputformat, $l) : $l, ENT_QUOTES);
				$kHtml = htmlspecialchars($k, ENT_QUOTES);
				$nm = $this->makeHtmlAttrName("[$kHtml]");
				$chk = array_key_exists($k, $value) ? ' checked' : '';
				$out .= "<div class='select-item checkbox'><label$attr class='control-label'> <input$nm type='checkbox' value='$kHtml'$chk />$label</label>{$sep}</div>";
			}
		}
		$out .= '</div>';
		return $out;
	}
}
