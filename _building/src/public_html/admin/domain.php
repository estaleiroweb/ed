<?php
require_once 'common_admin.php';

$frm=new Form();
//$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db;
$frm->tbl='tb_Domain';
$frm->start();

$fs=new Fieldset();
$frm->addFormTable('Domain');
$frm->addFormTable('idUserUpd,DtUpdate');
$frm->addFormTable('Obs');

$lst=new tblDataList('Domain');
$lst->showLabel=false;
$lst->lstFields='Domain';
$lst->lines=5;
$lst->function=array(
	'idDomain'=>array('ED_Links','filter_Domain'),
	'Domain'=>array('ED_Links','filter_Domain'),
);
print $lst;

print '<div class="container">';
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
print '</div>';
