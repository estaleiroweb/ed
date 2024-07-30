<?php
require_once 'common_admin.php';

$lst=new tblDataList('Links URLs');
$lst([
	'showLabel'=>false,
	'db'=>Secure::$db,
	'view'=>'tb_URLs',
	'lstFields'=>'lnk,isTemporary,URL,Descr,DtLastVisit',
	'order'=>'lnk',
	'lines'=>3,
	'key'=>'idURL',
	'function'=>[
		'lnk'=>'lnk',
		'URL'=>'lnk',
	],
	'frm'=>[
		'lnk,isTemporary,idUser,DtGer,DtLastVisit',
		'URL,Descr',
		'QString',
	],
]);

$frm=$lst->frm(['setActionUpdate']);
print $lst;
if($frm) $frm->container();

function lnk($value,$fieldName,$fields){
	return "<a href='http://evoice/url?{$fields['lnk']}'>$value</a>";
}