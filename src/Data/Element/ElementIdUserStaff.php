<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Secure\Secure;

class ElementIdUserStaff extends ElementIdUser {
	protected $typeList = array('iduser');
	function __construct($name = '', $value = null, $id = null) {
		parent::__construct($name, $value, $id);
		$this->source = array('view' => '
			SELECT u.idUser, IFNULL(d.Nome,u.User) `User`
			FROM ' . Secure::$db . '.tb_Users u 
			JOIN ' . Secure::$db . '.tb_Users_x_tb_GrpUsr g ON u.idUser=g.idUser AND g.isMain AND g.idGrpUsr=' . Secure::$db . '.fn_get_idStaff(' . Secure::$db . '.fn_get_idUser())
			LEFT JOIN ' . Secure::$db . '.tb_Users_Detail d ON u.idUser=d.idUser
			ORDER BY IFNULL(d.Nome,u.User)
		');
	}
}
