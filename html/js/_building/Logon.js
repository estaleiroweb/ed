$(document).ready(function(){
	if(window.restCounter)  bloaqueioTemporizado();
	else {
		statusBtnEntrar();
		statusBtnRemember();
	}
	statusBtnAlterar();
	howMuchStrongPasswd();
	$("#fullUsername").keyup(function(){
		statusBtnEntrar();
		statusBtnAlterar();
	});
	$("#passwd").keyup(function(){
		statusBtnEntrar();
		statusBtnAlterar();
	});
	$("#newPass").keyup(function(){
		statusBtnAlterar();
		howMuchStrongPasswd();
	});
	$("#confirmNewPass").keyup(function(){
		statusBtnAlterar();
	});
	$("#lembrarSenha").keyup(function(){
		statusBtnRemember();
	});
	$("#btnForgot").click(function(){
		$("#mainScreen").hide();
		$("#forgotScreen").show();
	});
	$("#btnVoltarForgot").click(function(){
		$("#forgotScreen").hide();
		$("#mainScreen").show();
		$("#lembrarSenha").val('');
		statusBtnRemember();
	});
	$("#btnCancelar").click(function(){
		window.location=window.urlHome;
	});
	$("#btnNew").click(function(){
		window.location=window.urlNewUser;
	});
});
function statusBtnEntrar(){
	if($("#fullUsername").val()=='' || $("#passwd").val()=='') $("#btnEntrar").attr("disabled", "disabled");
	else $("#btnEntrar").removeAttr("disabled");
}
function statusBtnAlterar(){
	var confirmNewPass=$("#confirmNewPass").val();
	if(typeof confirmNewPass=='undefined') return;
	if(
		!checkConfirmPasswd() ||
		$("#fullUsername").val()=='' || 
		$("#passwd").val()=='' || 
		$("#newPass").val()=='' || 
		$("#confirmNewPass").val()==''
	) $("#btnChangePasswd").attr("disabled", "disabled");
	else $("#btnChangePasswd").removeAttr("disabled");
}
function statusBtnRemember(){
	if($("#lembrarSenha").val()=='') $("#btnRemember").attr("disabled", "disabled");
	else $("#btnRemember").removeAttr("disabled");
}
function bloaqueioTemporizado(){
	$("#fullUsername").attr("disabled", "disabled");
	$("#passwd").attr("disabled", "disabled");
	$("#btnEntrar").attr("disabled", "disabled");
	window.htmlBtnEntrar=$("#btnEntrar").html();
	window.oInterval=setInterval(temporizador,1000);
	temporizador();
}
function desbloaqueioTemporizado(){
	$("#fullUsername").removeAttr("disabled");
	$("#passwd").removeAttr("disabled");
	$("#btnEntrar").html('Entrar');
	window.htmlBtnEntrar=$("#btnEntrar").html(window.htmlBtnEntrar);
	statusBtnEntrar();
	clearInterval(window.oInterval);
}
function temporizador(){
	$("#btnEntrar").html(window.htmlBtnEntrar+' <span class="badge">'+window.restCounter+'</span>');
	if(window.restCounter) return window.restCounter--;
	desbloaqueioTemporizado();
}
function checkConfirmPasswd(){
	var confirmNewPass=$("#confirmNewPass").val();
	if(typeof confirmNewPass=='undefined') return;
	$("#confirmNewPassGroup > span").removeClass( "alert-danger alert-success");
	$("#confirmNewPassGroup > span > span").removeClass( "glyphicon-ban-circle glyphicon-ok-circle glyphicon-remove-circle");
	
	if($("#confirmNewPass").val()=='') {
		$("#confirmNewPassGroup > span > span").addClass("glyphicon-ban-circle");
	} 
	else {
		if($("#newPass").val()==$("#confirmNewPass").val()) {
			$("#confirmNewPassGroup > span").addClass("alert-success");
			$("#confirmNewPassGroup > span > span").addClass("glyphicon-ok-circle");
			return true;
		}
		else{
			$("#confirmNewPassGroup > span").addClass("alert-danger");
			$("#confirmNewPassGroup > span > span").addClass("glyphicon-remove-circle");
		}
	}
	return false;
}
function howMuchStrongPasswd(){
	var passwd=$("#newPass").val();
	if(typeof passwd=='undefined') return;
	var valorPerc=0;
	var classProgress=''
	if(passwd!='') {
		var tam=passwd.length;
		if((/[0-9]/).test(passwd)) valorPerc+=tam;
		if((/[a-z]/).test(passwd)) valorPerc+=tam*1.5;
		if((/[A-Z]/).test(passwd)) valorPerc+=tam*1.5;
		if((/[!@#$%&\*\+=\(\)\/\?<>\[\]\\-]/).test(passwd)) valorPerc*=1.5;
		if((/ /).test(passwd)) valorPerc*=2;
		valorPerc=Math.min(100,Math.round(valorPerc));
	}

	if(valorPerc>75) classProgress='progress-bar-success';
	else if(valorPerc>50) classProgress='progress-bar-info';
	else if(valorPerc>25) classProgress='progress-bar-warning';
	else classProgress='progress-bar-danger';
	
	$("#progressPasswd").attr("aria-valuenow", valorPerc).css("width",valorPerc+"%").removeClass( "progress-bar-success progress-bar-info progress-bar-warning progress-bar-danger").addClass(classProgress);

	var stepProcess=25;
	var perc=[];
	for(var i=0;i<3;i++) {
		if(valorPerc>stepProcess) {
			setProcessBar(i,stepProcess);
			valorPerc-=stepProcess;
		} 
		else { 
			setProcessBar(i,valorPerc);
			valorPerc=0;
		}
	}
	setProcessBar(i,valorPerc);
}
function setProcessBar(id,val){
	$("#progressPasswd"+id).attr("aria-valuenow", val).css("width",val+"%");
}