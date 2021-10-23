<?php
/*
		$obj->label='Usuário';
		$obj->title='Quem alterou o registro';
		$obj->readonly=true;
		$obj->fn='Links::idUserMail';
		$obj->inputValue=$s->user;
		$obj->default=Secure::$idUser;
		
		$obj->source=array('view'=>'
			select u.idUser, ifnull(d.Nome,u.User) `User`
			from '.Secure::$db.'.tb_Users u 
			left join '.Secure::$db.'.tb_Users_Detail d using(idUser)
			order by ifnull(d.Nome,u.User)
		');
		$obj->fields='User';
		
		$obj->source=array('view'=>'vw_OC_Years');

*/

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Ext\BootstrapSelect;

class ElementCombo extends Element {
	protected $typeList = array('combo');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'combo';
		parent::__construct($name, $value, $id);
		$this->mov('value');
		$this->style();
		$this->script();
	}
	public function getWidth() {
		return $this->get('width');
	}
	function makeContent() {
		$out = $this->htmlLabel();
		$value = $this->buildOptionsValues($this->makeSource(), $v = $this->value);
		$v = htmlspecialchars($v);
		$out .= "<span{$this->makeHtmlAttrId()}{$this->buildStyles()}{$this->makeAttrib()} val='$v'>";
		return $this->showBox ? $out . $value . '</span>' : $value;
	}
	function makeContent_main() {
		$this->getFormVarFunc($field, $group);
		$value = $this->func($this->displayformat ? $this->format($this->displayformat) : $this->value, $field, $group);
		$out = $this->htmlLabel();
		$out .= $this->showBox ? "<span{$this->makeHtmlAttrId()}{$this->buildStyles()}{$this->makeAttrib()}>" . $value . '</span>' : $value;
		return $out;
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		new BootstrapSelect();
		$val = $this->makeSource();
		//(($v=$this->inputValue)!==false) || $v=$this->value;
		//$v=is_null($i=$this->inputValue)?$this->value:$i;
		$v = $this->value;
		$attr = $this->makeHtmlAttrId() . $this->makeAttrib() . $this->makeEvents() . $this->buildStyles();
		$attrI = $this->makeAttribInput();
		$name = $this->makeHtmlAttrName();
		if ($this->readonly) {
			$id = $this->makeHtmlAttrId($this->preIdInput);
			if (!array_key_exists($v, $val)) $v = key($val);
			$value = htmlspecialchars(@$val[$v], ENT_QUOTES);
			//$this->mov('value');
			return $this->outControl($this->htmlLabel() . "<input$id$name type='hidden' value='$v'$attrI /><input type='text' value='$value'$attr />");
		}
		$opt = implode("\n\t", $this->makeOptions($val, $v));
		$out = $this->htmlLabel() . "<select$name$attr$attrI>\n\t$opt</select>{$this->buildLinks()}";
		return $this->outControl($out);
	}
	function buildLinks() {
		if (!($links = $this->links)) return;
		$b = array('new', 'search');
		$out = array();
		foreach ($b as $k => $v) if (isset($links[$k])) $out[] = "<span class='Element_$k' onclick='location=\"$v\"'></span>";
		return implode('', $out);
	}
	function makeLinkButton() {
	}
}
