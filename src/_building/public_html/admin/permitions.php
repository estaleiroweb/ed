<?php
require_once 'common_admin.php';

$frm=new Form();
//$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db;
$frm->tbl='tb_Permitions';
$frm->key='idPermition';
$frm->start();
$frm->fields['idGrpUsr']->function=array('ED_Links','field_idGrpUsr');
$frm->fields['idGrpFile']->function=array('ED_Links','field_idGrpFile');

$frm->addFormTable('idGrpUsr,idGrpFile');
$frm->addFormTable(array('CRUDS'=>'C,R,U,D,S'));
$frm->addFormTable('idUserUpd,DtUpdate,DtGer');
$frm->addFormTable('Obs');

$lst=new tblDataList('Permitions');
$lst->showLabel=false;
$lst->view='
SELECT
  p.idPermition,
  p.idGrpUsr,
  u.GrpUsr,
  p.idGrpFile,
  f.GrpFile,
  p.C,p.R,p.U,p.D,p.S,
  p.CRUDS,
  db_Secure.fn_CRUDS2Bin(p.CRUDS) bCRUDS,
  p.Obs,
  p.idUserUpd,
  p.DtUpdate,
  p.DtGer
FROM db_Secure.tb_Permitions p
LEFT JOIN db_Secure.tb_GrpUsr u USING(idGrpUsr)
LEFT JOIN db_Secure.tb_GrpFile f USING(idGrpFile)
';
$lst->lstFields='GrpUsr,GrpFile,C,R,U,D,S,bCRUDS';
$lst->lines=5;
$lst->function=array(
	'idGrpUsr'=>array('ED_Links','field_idGrpUsr'),
	'GrpUsr'=>array('ED_Links','field_idGrpUsr'),
	'idGrpFile'=>array('ED_Links','field_idGrpFile'),
	'GrpFile'=>array('ED_Links','field_idGrpFile'),
);
print $lst;

$frm->container();

