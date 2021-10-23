<?php

namespace EstaleiroWeb\ED\Data\Form;

use EstaleiroWeb\ED\Data\Element\ElementString;

class FormAlternative4ElementSearch {
	public $fields = array();
	public $key = array();

	function __construct() {
		if (@$GLOBALS['Elements']) foreach ($GLOBALS['Elements'] as $id => $obj) {
			$this->fields[$obj->name] = $obj;
		}
	}
	function addField($fieldName, $obj = null, $value = '', $default = '') {
		if (!$obj) {
			if (isset($this->fields[$fieldName])) $obj = $this->fields[$fieldName];
			else {
				$obj = new ElementString();
				$obj->default = $default;
				$obj->label = $fieldName;
			}
		} elseif (!$obj->label) $obj->label = $fieldName;

		$obj->edit = true;
		$obj->objectForm = $this; //Retirar mais tarde
		$obj->form = $this;
		$obj->name = $fieldName;
		$this->fields[$fieldName] = $obj;
	}
}
