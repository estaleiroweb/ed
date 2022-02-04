<?php
require_once 'common_admin.php';

print '
<style>
	select[ed-element="list2list"] option:first-child, div[ed-element="list2list"] div:first-child{
		font-weight: bold;
	}
	select[ed-element="list2list"] option:first-child:after, div[ed-element="list2list"] div:first-child:after{
		content: " (staff)";
	}
</style>
';
$frm=new Form();
//$frm->setActionUpdate(); //entrar default no modo de edição;
$frm->db=Secure::$db;
$frm->nav='users.php';
$frm->tbl='
SELECT
	u.idUser, u.idDomain, u.User, u.Ativo, 
	uc.Confirm, uc.DtUpdate DtConfirm,
	ud.Nome,ud.Sexo,
	ud.idGestor,ud.idCargo,
	ud.Matricula,ud.Niver,ud.CentroCusto,ud.Obs, 
	ud.idSite_Lotado,
	ud.idSite_Locado,
	ud.idUserUpd, ud.DtUpdate, u.DtGer
FROM tb_Users u
LEFT JOIN tb_Users_Confirm uc ON uc.idUser=u.idUser
LEFT JOIN tb_Users_Detail ud  ON ud.idUser=u.idUser
';
$frm->addField('Grupos',new ElementList2List)
	->__set('source','SELECT idGrpUsr,GrpUsr FROM tb_GrpUsr ORDER BY GrpUsr')
	->__set('saveTo',array(
		'table'=>'tb_Users_x_tb_GrpUsr',
		'value'=>'idGrpUsr',
		'key'=>$frm->key,
		'order'=>'Seq',
		'fields'=>array(
			'idUserUpd'=>Secure::$idUser,
		),
	));
$frm->start();
$idUser=$frm->fields['idUser']->value;
if(!$l->s) {
	$frm->setIsInsert(0)->setIsDelete(0);
	if(Secure::$idUser!=$idUser) $frm->setIsEdit(0)->isMss=false;
}

$frm->fields['Grupos']->function=array('ED_Links','field_idGrpUsr');
$frm->fields['idGestor']->function=array('ED_Links','field_idUser');

$fs=new Fieldset();
$frm->addFormTable('idUser,idDomain,User,Ativo');
if(!$frm->isActionInsert()) {
	$frm->addFormTable('Nome,Confirm');
	$frm->addFormTable('Matricula,Sexo,DtConfirm');
	$frm->addFormTable('idGestor,idCargo');
	$frm->addFormTable('Niver,CentroCusto');
	$frm->addFormTable('Obs');
	$frm->addFormTable('idUserUpd,DtUpdate,DtGer');
	$frm->addFormTable('Grupos');
}

print '<div class="container">';
$frm($fs($frm->getFormTable('100%'),'Cadastro'));

print '
<br>
<ul class="nav nav-tabs" id="user_subitens">
  <li role="presentation" class="active"><a href="users_edit_email.php?idUser='.$idUser.'" target="user_itens">Emails</a></li>
  <li role="presentation"><a href="users_edit_phone.php?idUser='.$idUser.'" target="user_itens">Telefones</a></li>
  <li role="presentation"><a href="users_edit_endereco.php?idUser='.$idUser.'" target="user_itens">Endereços</a></li>
  <li role="presentation"><a href="users_edit_doc.php?idUser='.$idUser.'" target="user_itens">Documentos</a></li>
</ul>
<iframe id="user_itens" name="user_itens" style="border: none; width: 100%; height: 900px;" marginwidth=0 marginheight=0></iframe>
</div>
<script>
$(document).ready(function(){
	$("#user_subitens li.active a").each(function(){ 
		$("#user_itens").attr("src",$(this).attr("href")); 
	});
	$("#user_subitens li a").click(function(){
		$("#user_subitens li.active").removeClass("active");
		$(this).parent().addClass("active");
	});
});
</script>
';
