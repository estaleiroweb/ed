<?php
require_once 'common_admin.php';

$lst=new tblDataList('Users');
$lst->db=Secure::$db;
$lst->view='
SELECT
  u.idUser,
  u.idDomain,
  d.Domain,
  u.User,
  ud.Nome,
  g.idGrpUsr,
  g.GrpUsr Staff,
  u.Ativo,
  uc.Confirm,
  ud.Sexo,
  ud.idCargo,
  ud.Matricula,
  ud.Niver,
  ud.CentroCusto,
  c.Cargo,
  u.DtUpdate,
  u.DtGer
FROM tb_Users u
LEFT JOIN tb_Domain d USING(idDomain)
LEFT JOIN tb_Users_Confirm uc USING(idUser)
LEFT JOIN tb_Users_Detail ud  USING(idUser)
LEFT JOIN tb_Users_x_tb_GrpUsr ug ON u.idUser=ug.idUser AND ug.isMain
LEFT JOIN tb_GrpUsr g USING(idGrpUsr)
LEFT JOIN tb_Cargos c USING(idCargo)
';
$lst->showLabel=false;
$lst->lstFields='Domain,User,Staff,Ativo,Confirm,Nome,Sexo,Matricula,Niver,CentroCusto,Cargo';
$lst->url='users_edit.php';
$lst->key='idUser';
$lst->function=array(
	'idUser'=>array('ED_Links','field_idUser'),
	'User'=>array('ED_Links','field_idUser'),
	'Nome'=>array('ED_Links','field_idUser'),
	'idGrpUsr'=>array('ED_Links','field_idGrpUsr'),
	'Staff'=>array('ED_Links','field_idGrpUsr'),
	'idCargo'=>array('ED_Links','field_idCargo'),
	'Cargo'=>array('ED_Links','field_idCargo'),
);
print $lst;
