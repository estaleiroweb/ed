<?php
$idUser=$_REQUEST['idUser']+0;
if(!$idUser) die('Error');

require_once '/data/shared/fsc/autoload.php';
$l=Layout_Min::singleton();

$fs=new Fieldset();
$frm=new Form();
$frm->setActionUpdate();
$frm->db=Secure::$db;
$frm->tbl='tb_Users_Enderecos';
$frm->key='idUser,Pop';
$frm->start();
if(Secure::$idUser!=$idUser && !$l->s) $frm->setIsInsert(0)->setIsDelete(0)->setIsEdit(0)->isMss=false;

$frm->fields['idUser']->inputValue=$idUser;
$frm->addFormTable('idUser,Pop,EndType,Logradouro,Num');
$frm->addFormTable('Complemento');
$frm->addFormTable('Bairro');
$frm->addFormTable('Cidade,Uf');
$frm->addFormTable('Pais');
$frm->addFormTable('Obs');

$lst=new tblDataList('Users Pop#'.$idUser);
$lst->showLabel=false;
$lst->container=false;
$lst->view='SELECT * FROM '.$frm->tbl.' t WHERE idUser='.$idUser;
$lst->lstFields='Pop,EndType,Logradouro,Num,Bairro,Cidade,Uf';
$lst->lines=5;

print $lst;
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
