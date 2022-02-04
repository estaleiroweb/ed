<?php
$idUser=$_REQUEST['idUser'];
if(!$idUser) die('Error');

require_once '/data/shared/fsc/autoload.php';
$l=Layout_Min::singleton();

$fs=new Fieldset();
$frm=new Form();
$frm->setActionUpdate();
$frm->db=Secure::$db;
$frm->tbl='tb_Users_Telefones';
$frm->key='idUser,Telefone';
$frm->start();
if(Secure::$idUser!=$idUser && !$l->s) $frm->setIsInsert(0)->setIsDelete(0)->setIsEdit(0)->isMss=false;

$frm->fields['idUser']->inputValue=$idUser;
$frm->addFormTable('idUser,Telefone,TipoContato');
$frm->addFormTable('Obs');

$lst=new tblDataList('Users Telefone#'.$idUser);
$lst->showLabel=false;
$lst->container=false;
$lst->view='SELECT * FROM '.$frm->tbl.' t WHERE idUser='.$idUser;
$lst->lstFields='Telefone,TipoContato,Obs';
$lst->lines=5;

print $lst;
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
