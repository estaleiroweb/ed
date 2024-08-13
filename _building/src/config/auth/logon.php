--force
require_once 'common.php';
//thisClass()->clearCounters();//verbose();

$auth=@$_POST[thisClassName()];
if(@$auth['lembrarSenha']) {
	//FIXME Enviar email
	
	$erro=0;
	if($erro==0) $html=elementMessage('Email enviado com sucesso','sucess');
	elseif($erro==1) $html=elementMessage('Usuário não existe');
	elseif($erro==2) $html=elementMessage('Erro no envio do Email','danger');
	outScreen($html,'Email Enviado');
} 

$fullUsername=@$auth['fullUsername'];
$s=Secure_User::splitFullUsername($fullUsername);
$domain=$s['domain'];
$username=$s['username'];

verbose($_POST);
$line=$username?thisClass()->login($domain,$username,@$auth['passwd'],@$auth['newPass'],@$auth['forceLogin']):array('logonError'=>-1);
verbose($line);
if($line['logonError']) { //Logar
	{//Introdução
	$processBarStyle=(int)@Secure::$ini['main']['processBarStyle'];
	$counter=0;
	$o=OutHtml::singleton();
	$o->headScript['urlHome']='window.urlHome="'.addslashes(@Secure::$ini['URLs']['home']).'"';
	$o->headScript['urlHome']='window.urlNewUser="'.addslashes(@Secure::$ini['URLs']['newUser']).'"';
	$o->addHead('Login','title');
	$o->contentType();
	new Bootstrap;
	//new JQuery;
	new JQuery_UI;
	$o->addHead('verbose','style','easyData');
	$o->addHead('Logon','style','easyData');
	$o->addHead('Logon','script','easyData');
	}
	$html='';
	if($line['logonError']==6 || $line['logonError']==7) { //Expired Password
		$title='Alterar Senha';
		$alertClass=$line['logonError']==6?'warning':'danger';
		$html.=elementUsername($fullUsername,false);
		$html.=elementPasswd();
		$html.=elementPasswd('','newPass',       'password', 'Nova Senha', 'Digite a nova senha');
		//$html.=elementPasswd('','confirmNewPass','password', 'Confirme',   'Cornfirme a nova senha');
		$html.='
<div class="clearfix input-group" id="confirmNewPassGroup">
	<input id="confirmNewPass" name="Secure[confirmNewPass]" type="password" placeholder="Confirme" class="form-control" value="" alt="Cornfirme a nova senha">
	<span class="input-group-addon alert-danger"><span class="glyphicon glyphicon-remove-circle"></span></span>
</div>';
		if($processBarStyle) $html.='
			<div class="progress">
				<div id="progressPasswd0" class="progress-bar progress-bar-danger"  role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
				<div id="progressPasswd1" class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
				<div id="progressPasswd2" class="progress-bar progress-bar-info"    role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
				<div id="progressPasswd3" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
			</div>';
		else $html.='
			<div class="progress">
				<div id="progressPasswd" class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
			</div>';
		$html.=elementButton('Alterar','btnChangePasswd');
	}
	elseif($line['logonError']==8) { //User Loged. To Force Login
		$title='Forçar Logins';
		$alertClass='info';
		$html.=elementUsername($fullUsername,true,'hidden');
		$html.=elementPasswd($auth['passwd'],'passwd','hidden');
		$html.=elementUsername('1',true,'hidden','forceLogin');
		$html.=elementButton('Forçar Entrada','btnForce');
	}
	else { //Tela Login, Esqueci a senha
		$title='Login';
		$alertClass='danger';
		$html.='<div id="mainScreen">';
		$html.=elementUsername($fullUsername);
		$html.=elementPasswd();
		$html.=elementButton();
		$html.='<ol class="breadcrumb">';
		$html.=elementLink();
		$html.=elementLink('btnNew','Criar Usuário');
		$html.='</ol>';
		$html.='</div><div id="forgotScreen" style="display:none;">';
		$html.=elementUsername('',true,'text','lembrarSenha');
		$html.=elementButton('Lembrar','btnRemember','Voltar','btnVoltarForgot','Digite dominio e usuario que deseja lembrar');
		$html.='</div>';
	}
	if($line['logonError']>0) {
		if($line['logonError']<6) $counter=thisClass()->tryWait;
		$html.=elementMessage(@$line['userMessageError'],$alertClass);
	}
	$o->headScript['restCounter']='window.restCounter='.$counter;
	outScreen($html,$title);
}

function tabs() { return "							"; }
function elementUsername($value='',$enable=true,$type='text',$id='fullUsername',$tip='Digite dominio e usuario', $placeholder='Usuário'){
	$c=thisClassName();
	$ro=$enable?' class="form-control"':' class="disabled form-control" readonly';
	$v=htmlspecialchars($value);
	$t=tabs();
	$input="$t<input id='$id' name='{$c}[$id]' type='$type' placeholder='$placeholder'$ro value='$v' alt='$tip'>\n";
	return $type=='hidden'?$input:"$t<div class='clearfix'>\n\t$input$t</div>\n";
}
function elementPasswd($value='',$id='passwd', $type='password', $placeholder='Senha', $tip='Digite sua senha'){
	return elementUsername($value,true,$type,$id,$tip,$placeholder);
	//return "$t<div class='clearfix'>\n$t\t<input id='$id' name='{$c}[$id]' type='$type' placeholder='$placeholder' value='$v' alt='$tip'>\n$t</div>\n";
}
function elementButton($value='Entrar',$id1='btnEntrar',$value2='Cancelar',$id2='btnCancelar'){
	$t=tabs();
	$btn=array();
	if($value) $btn[]="\n$t\t<button class='btn btn-primary' type='submit' id='$id1'>$value</button>";
	if($value2) $btn[]="\n$t\t<button class='btn btn-warning' type='button' id='$id2'>$value2</button>";
	$btn=implode('',$btn);
	return "$t<div class='buttons'>{$btn}\n$t</div>\n";
}
function elementLink($id='btnForgot',$text='Lembrar a Senha'){
	$t=tabs();
	return "$t<li>\n$t\t<a href='#' id='$id'>$text</a>\n$t</li>\n";
}
function elementMessage($text='',$class='info'){
	$t=tabs();
	if($text) return "$t<div class='clearfix alert alert-$class'>$text</div>\n";
}
function outScreen($html,$title='Login'){
	die("	<div class='container'>
		<div class='content'>
			<div class='row'>
				<div class='login-form'>
					<h2>$title</h2>
					<form method='POST'>
						<fieldset>\n{$html}
						</fieldset>
					</form>
				</div>
			</div>
		</div>
	</div>");
}
