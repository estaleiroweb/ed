<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Data\Form\FormAlternative4ElementSearch;
use EstaleiroWeb\ED\Screen\OutHtml;

class ElementSearch extends ElementButton {
	protected $objHead = array();

	function __construct($name = '', $value = null, $id = null) {
		$this->protect['showClear'] = false;
		$this->protect['iconClear'] = 'glyphicon-remove';
		$this->protect['labelClear'] = '';
		$this->protect['attrClear'] = array();
		parent::__construct($name, $value, $id);
		$this->icon = 'glyphicon-search';
		$this->source = array();
		$this->noValidate = 1;
		//$this->updatable=true;
		$this->script();
		//new JQuery_UI();
	}
	public function start() { /*Start Form*/
		if (!parent::start()) return false;
		$source = $this->source;
		if (!$this->isDbSource($source) || !($source->key = $this->trArray(@$source->key))) {
			$this->edit = false;
			return false;
		}
		$objs = array();
		foreach ($source->key as $field_form => $field_source) {
			$o = $this->form->fields[$field_form];
			$objs[$field_form] = array('id' => $o->makeAttrId($o->preIdDisplay), 'source_name' => $field_source, 'key' => true);
		}
		if (!$this->form) $this->form = new FormAlternative4ElementSearch;
		if (@$source->db) $source->conn->select_db($source->db);
		else $source->db = $source->conn->db;

		$where = $this->buildWhere($source);
		$view ='SELECT * FROM (' . $source->view . ') as t';
		$sql = $view . ' WHERE ' . $where;
		$line = $source->conn->fastLine($sql);

		if ($source->getCells = $this->trArray(@$source->getCells)) {
			foreach ($source->getCells as $field_form => $field_source) {
				$o = $this->form->fields[$field_form];
				$o->readonly = true;
				$objs[$field_form] = array('id' => $o->makeAttrId($o->preIdDisplay), 'source_name' => $field_source, 'key' => false);
				if (array_key_exists($field_source, $line)) $o->value = $this->fnSource($line[$field_source], $field_source, $line, @$source->fn[$field_source]);
			}
		}
		$this->objHead = array(
			'id' => $this->makeAttrId($this->preIdDisplay),
			'idForm' => $this->form->id,
			'objs' => $objs,
			'source' => (array)$source,
		);
		$this->addHeader($this->objHead);
		return true;
	}
	function makeContent() {
		return '';
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$btn = parent::makeControl();
		if (!$this->showClear) return $btn;

		$oClear = new ElementButton();
		$oClear->id = $this->id;
		$oClear->preIdDisplay = 'c_';
		$oClear->icon = $this->iconClear;
		$oClear->label = $this->labelClear;
		$oClear->{'ed-form-id'} = $this->{'ed-form-id'};
		$oClear->attrs($this->attrClear);
		return "<div class='btn-group' role='group'>$btn$oClear</div>";

		//$btn="<button{$this->makeHtmlAttrId()} type='button' {$this->makeAttrib()}{$this->makeAttribInput()}{$this->buildStyles()}{$this->makeEvents()}>$label</button>";

		$clear = $this->protect['showClear'] ? "<button{$this->makeHtmlAttrId('c_')} type='button' {$this->protect['attrClear']}>{$this->protect['labelClear']}</button>" : '';
		return $btn . $clear;
	}
	function addHeader($header_objs = []) {
		parent::addHeader();
		OutHtml::singleton()->headScript[__CLASS__ . '_' . $header_objs['id']] = '$.ElementSearch.init_obj(' . json_encode_ex($header_objs) . ');';
		OutHtml::singleton()->jQueryScript[__CLASS__ . '_' . $header_objs['id']] = '$.ElementSearch.start_obj(' . json_encode_ex($header_objs['id']) . ');';
	}
	function trArray($value, $force = false) {
		if (is_object($value)) $value = (array)$value;
		elseif (!is_array($value)) $value = preg_split('/\s*[,;]\s*/', $value);
		$ret = array();
		foreach ($value as $k => $v) { //$k=fieldName this Form, $v=fieldName this query
			if (is_numeric($k)) $k = $v;
			if (!$k || (!is_string($v) && !is_numeric($v))) continue;
			if (!($force || array_key_exists($k, $this->form->fields) || array_key_exists($k, $this->form->key))) {
				$this->form->addField($k, new ElementString);
				$this->form->fields[$k]->width = '100%';
			}
			$ret[$k] = $v;
		}
		return $ret;
	}
	function buildWhere($source) {
		$where = array();
		foreach ($source->key as $k => $field_source) {
			($value = @$this->form->fields[$k]->value) || ($value = @$this->form->key[$k]);
			$where[] = $this->conn->fieldCompareValue($field_source, $value);
		}
		return implode(' AND ', $where);
	}

	function fnSource($value, $field, $group, $fn = null) {
		if (!$fn || $this->edit) return $value;
		return call_user_func($fn, $value, $field, $group, $this);
	}
}
