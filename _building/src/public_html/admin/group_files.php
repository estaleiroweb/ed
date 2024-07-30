<?php
require_once 'common_admin.php';

$frm=new Form();
$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db;
$frm->tbl='tb_GrpFile';
$frm->addField('Files',new ElementList2List)
	->__set('sortBox','true')
	->__set('source','SELECT idFile,`File` FROM tb_Files ORDER BY `File`')
	->__set('saveTo',array(
		'table'=>'tb_Files_x_tb_GrpFile',
		'value'=>'idFile',
		'key'=>$frm->key,
		//'order'=>'Seq',
		'fields'=>array(
			'idUserUpd'=>Secure::$idUser,
		),
	));
$frm->start();
$frm->fields['Files']->function=array('ED_Links','field_idFile');
$frm->fields['GrpFile']->function=array('ED_Links','filter_GrpFile');

$fs=new Fieldset();
$frm->addFormTable('GrpFile');
$frm->addFormTable('idUserUpd,DtUpdate,DtGer');
$frm->addFormTable('Obs');
$frm->addFormTable('Files');

$lst=new tblDataList('GroupFiles');
$lst->showLabel=false;
$lst->lstFields='GrpFile';
$lst->lines=5;
$lst->function=array(
	'idGrpFile'=>array('ED_Links','field_idGrpFile'),
	'GrpFile'=>array('ED_Links','field_idGrpFile'),
);
print $lst;

print '<div class="container">';
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
print '</div>';
