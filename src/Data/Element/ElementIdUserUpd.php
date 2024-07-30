<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Secure\Secure;

class ElementIdUserUpd extends ElementIdUser {
	function __construct($name = '', $value = null, $id = null) {
		parent::__construct($name, $value, $id);
		$this->inputValue = Secure::$idUser;
		$this->readonly = true;
	}
}
