<?php
require_once 'common_admin.php';

$frm=new Form();
$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db;
$frm->tbl='tb_Files';
$frm->addField('Grupos',new ElementList2List)
	->__set('sortBox','true')
	->__set('source','SELECT idGrpFile,GrpFile FROM tb_GrpFile ORDER BY GrpFile')
	->__set('saveTo',array(
		'table'=>'tb_Files_x_tb_GrpFile',
		'value'=>'idGrpFile',
		'key'=>$frm->key,
		'fields'=>array(
			'idUserUpd'=>Secure::$idUser,
		),
	));
$frm->start();
$frm->fields['Grupos']->function=array('ED_Links','field_idGrpFile');

$fs=new Fieldset();
$frm->addFormTable('File');
$frm->addFormTable('L,C,R,U,D,S');
$frm->addFormTable('idUserUpd,DtUpdate,DtGer');
$frm->addFormTable('Obs');
$frm->addFormTable('Grupos');

$lst=new tblDataList('Files');
$lst->showLabel=false;
$lst->view='
SELECT
  f.idFile,f.File,
  f.C,f.R,f.U,f.D,f.S,f.L,
  fn_L2Level(f.L) `Level`,
  f.CRUDS,
  fn_CRUDS2Bin(f.CRUDS) bCRUDS,
  f.Obs,
  f.idUserUpd,
  f.DtUpdate,f.DtGer
FROM tb_Files f
';
$lst->lstFields='File,C,R,U,D,S,L,Level,bCRUDS';
$lst->lines=5;
$lst->function=array(
	'idFile'=>array('ED_Links','field_idFile'),
	'File'=>array('ED_Links','field_idFile'),
);
print $lst;

print '<div class="container">';
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
print '</div>';
