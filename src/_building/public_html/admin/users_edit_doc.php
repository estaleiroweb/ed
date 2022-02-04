<?php
$idUser=$_REQUEST['idUser'];
if(!$idUser) die('Error');

require_once '/data/shared/fsc/autoload.php';
$l=Layout_Min::singleton();

$fs=new Fieldset();
$frm=new Form();
$frm->setActionUpdate();
$frm->db=Secure::$db;
$frm->tbl='tb_Users_Documentos';
$frm->key='idUser,TipoDocumento';
$frm->start();
if(Secure::$idUser!=$idUser && !$l->s) $frm->setIsInsert(0)->setIsDelete(0)->setIsEdit(0)->isMss=false;

$frm->fields['idUser']->inputValue=$idUser;
$frm->addFormTable('idUser,TipoDocumento,Documento');
$frm->addFormTable('Complementos');

$lst=new tblDataList('Users Documento#'.$idUser);
$lst->showLabel=false;
$lst->container=false;
$lst->view='SELECT * FROM '.$frm->tbl.' t WHERE idUser='.$idUser;
$lst->lstFields='TipoDocumento,Documento,Complementos';
$lst->lines=5;

print $lst;
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
