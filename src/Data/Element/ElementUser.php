<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Secure\Secure;

class ElementUser extends Element {
	protected $typeList = array('user');
	function __construct($name = '', $value = null, $id = null) {
		parent::__construct($name, $value, $id);
		$this->inputformat = $this->displayformat = 'user';
		$s = Secure::singleton();
		$this->label = 'Usuário';
		$this->title = 'Quem alterou o registro';
		$this->readonly = true;
		$this->fn = 'Links::idUserMail';
		$this->inputValue = $s->user;
	}
}
