<?php
$idUser=$_REQUEST['idUser']+0;
if(!$idUser) die('Error');

require_once '/data/shared/fsc/autoload.php';
$l=Layout_Min::singleton();

$fs=new Fieldset();
$frm=new Form();
$frm->setActionUpdate();
$frm->db=Secure::$db;
$frm->tbl='tb_Users_Emails';
$frm->key='idUser,Email';
$frm->start();
if(Secure::$idUser!=$idUser && !$l->s) $frm->setIsInsert(0)->setIsDelete(0)->setIsEdit(0)->isMss=false;

$frm->fields['idUser']->inputValue=$idUser;
$frm->addFormTable('idUser,Email,EmailType,Confirm');

$lst=new tblDataList('Users Email#'.$idUser);
$lst->showLabel=false;
$lst->container=false;
$lst->view='SELECT * FROM '.$frm->tbl.' t WHERE idUser='.$idUser;
$lst->lstFields='Email,EmailType,Confirm';
$lst->lines=5;
$lst->function=array(
	'Email'=>array('ED_Links','Email'),
);

print $lst;
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
