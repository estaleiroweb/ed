<?php
require_once 'common_admin.php';

$frm=new Form();
//$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db;
$frm->tbl='tb_Cargos';
$frm->start();

$fs=new Fieldset();
$frm->addFormTable('Cargo');
$frm->addFormTable('idUserUpd,DtUpdate');
$frm->addFormTable('Obs');

$lst=new tblDataList('Post');
$lst->showLabel=false;
$lst->lstFields='Cargo';
$lst->lines=5;
$lst->function=array(
	'idCargo'=>array('ED_Links','filter_Post'),
	'Cargo'=>array('ED_Links','filter_Post'),
);
print $lst;

print '<div class="container">';
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
print '</div>';
