<?php
require_once 'common_admin.php';

$frm=new Form();
//$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db_log;
$frm->tbl='tb_Logs';
$frm->start();

$fs=new Fieldset();
$frm->addFormTable('DtLog,CRUDS');
$frm->addFormTable('idUser');
$frm->addFormTable('idFile');
$frm->addFormTable('remoteAddr,remotePort');
$frm->addFormTable('serverAddr,serverPort');
$frm->addFormTable('requestMethod,HTTPs,frm_action');
$frm->addFormTable('request');
$frm->addFormTable('referer');

$lst=new tblDataList('Log');
$lst->view='vw_Logs';
$lst->lstFields='DtLog,Nome,File,CRUDS,remoteAddr,remotePort,serverAddr,serverPort,request,referer,requestMethod,HTTPs,frm_action';
$lst->order='DtLog desc';
$lst->showLabel=false;
//$lst->lstFields='field1,field2,field3';
$lst->url='?d=2';
$lst->lines=5;
print $lst;

print '<div class="container">';
$frm($fs($frm->getFormTable('100%'),'Cadastro'));
print '</div>';
