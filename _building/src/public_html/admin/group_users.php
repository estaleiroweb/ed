<?php
require_once 'common_admin.php';

$frm=new Form();
$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db;
$frm->tbl='tb_GrpUsr';
$frm->addField('Users',new ElementList2List)
	->__set('sortBox','true')
	->__set('source','SELECT idUser,Nome FROM tb_Users_Detail ORDER BY Nome')
	->__set('saveTo',array(
		'table'=>'tb_Users_x_tb_GrpUsr',
		'value'=>'idUser',
		'key'=>$frm->key,
		//'order'=>'Seq',
		'fields'=>array(
			'idUserUpd'=>Secure::$idUser,
		),
	));
$frm->start();
$frm->fields['Users']->function=array('ED_Links','field_idUser');
$frm->fields['GrpUsr']->function=array('ED_Links','filter_GrpUsr');
$frm->fields['EMail']->function=array('ED_Links','Email');

$fs=new Fieldset();
$frm->addFormTable('GrpUsr');
$frm->addFormTable('EMail');
$frm->addFormTable('idUserUpd,DtUpdate,DtGer');
$frm->addFormTable('Obs');
$frm->addFormTable('Users');

$lst=new tblDataList('GroupUsers');
$lst->showLabel=false;
$lst->lstFields='GrpUsr,EMail';
$lst->order='GrpUsr';
$lst->lines=5;
$lst->function=array(
	'idGrpUsr'=>array('ED_Links','field_idGrpUsr'),
	'GrpUsr'=>array('ED_Links','field_idGrpUsr'),
	'EMail'=>array('ED_Links','Email'),
);
print $lst;

print '<div class="container">';
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
print '</div>';
