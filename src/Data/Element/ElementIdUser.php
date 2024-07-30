<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Secure\Secure;

class ElementIdUser extends ElementCombo {
	protected $typeList = array('iduser');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'iduser';
		$this->displayAttr['ed-class'] = 'ElementCombo';
		$this->displayAttr['firstNameOnly'] = false;
		parent::__construct($name, $value, $id);
		$this->style();
		//$this->script();
		//$this->inputformat=$this->displayformat='iduser';
		$this->label = 'Usuário';
		//$this->readonly=true;
		$this->fn = array($this, 'linkUser');
		$s = Secure::$obj;
		//if(!$s) die('ERRO SECURE');
		//$this->inputValue=$s->user->idUser;
		//$this->sql ....
		$this->source = array('view' => '
			select u.idUser, ifnull(d.Nome,u.User) `User`
			from ' . Secure::$db . '.tb_Users u 
			left join ' . Secure::$db . '.tb_Users_Detail d using(idUser)
			order by ifnull(d.Nome,u.User)
		');
		$this->fields = 'User';
		$this->default = Secure::$idUser;
	}
	public function dtFormat($df) {
		return strftime('%x %X', strtotime($df));
	}
	public function linkUser($value, $field = '', $group = array()) {
		$s = Secure::$obj;
		if (!$s) return $value;
		//show(array($value,$field,$group));
		if (array_key_exists('idUser', (array)$group)) $idUser = $group['idUser'];
		elseif (is_numeric($field)) $idUser = $field;
		elseif (is_numeric($value)) $idUser = $value;
		else $idUser = null;
		$details = $s->user->loadDetails($idUser);

		if (!$details || !@$details['emails']) return $value;
		$email = false;
		foreach ($details['emails'] as $v) if ($v['EmailType'] == 'Business') $email = $v['Email'];
		if (!$email) {
			$d = current($details['emails']);
			$email = $d['Email'];
		}

		$out = array();
		$d = @$details['main'];
		if ($d) {
			$out[] = 'User: ' . ($d['Domain'] ? $d['Domain'] . '\\' : '') . $d['User'];
			if ($d['Nome']) $out[] = 'Nome: ' . $d['Nome'] . ($d['Sexo'] ? ' [' . $d['Sexo'] . ']' : '');
			if ($d['Cargo']) $out[] = 'Cargo: ' . $d['Cargo'];
			$out[] = 'Status: ' . ($d['Ativo'] ? 'Ativo' : 'Inativo') . ($d['Confirm'] ? ' [Confirmado ' . $this->dtFormat($d['DtConfirm']) . ']' : '');
			if ($d['DtExpires']) $out[] = 'Expires: ' . $this->dtFormat($d['DtExpires']);
			if ($d['Ip']) $out[] = 'Ip: ' . $d['Ip'] . ' [' . $this->dtFormat($d['DtIp']) . ']';
			if ($d['Gestor']) $out[] = 'Gestor: ' . $d['Gestor'] . ($d['Gestor_User'] ? ' [' . $d['Gestor_User'] . ']' : '');
			elseif ($d['Gestor_User']) $out[] = 'Gestor: ' . $d['Gestor_User'];
		}
		$d = @$details['staff'];
		if ($d) {
			if ($d['GrpUsr']) $out[] = 'Staff: ' . $d['GrpUsr'];
		}
		$l = @$details['emails'];
		foreach ($l as $d) if ($d['Email']) $out[] = 'Email: ' . $d['Email'] . ' [' . $d['EmailType'] . ']';
		$l = @$details['phones'];
		foreach ($l as $d) if ($d['Telefone']) $out[] = 'Telefone: ' . $d['Telefone'] . ' [' . $d['TipoContato'] . ']' . ($d['Obs'] ? ' ' . $d['Obs'] : '');
		$title = $out ? implode("\n", $out) : 'Quem alterou o registro';
		if ($this->displayAttr['firstNameOnly']) $value = preg_replace('/(\w+).*/', '\1', $value);
		return '<a href="mailto:' . $email . '" Title="' . $title . '">' . $value . '</a>';
	}
}
