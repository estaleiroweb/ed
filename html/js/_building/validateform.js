function addDataObject(o,name,value){
	var a=name.split(/\[|\]/)
	var s='o'
	while(a.length){
		var n=a.shift()
		s+='["'+n+'"]'
		if(a.length) eval('if(typeof('+s+')=="undefined") '+s+'=new Object')
		else eval(s+'=value')
	} 
	return o
}
function validadeCmd(numero,id,elemts){
	var m=new MediatorPHPJS()
	//var cmd='$x=$_SESSION; for($i=0;$i<5;$i++) array_shift($x);  print_r($x); '//(@$_SESSION)[\''+elemts[id].idCmd+'\'][\'cmd\'];'
	var cmd=''
	var o,v,n
	var e=new Object()
	var all=document.getElementsByTagName('input')
	for(i in all) if((n=all[i].name?all[i].name:all[i].id)){ 
		addDataObject(e,n,typeof(all[i].value)=='string'?all[i].value:all[i].innerText)
	}
	var all=document.getElementsByTagName('select')
	for(i in all) if((n=all[i].name?all[i].name:all[i].id)){ 
		addDataObject(e,n,typeof(all[i].value)=='string'?all[i].value:all[i].innerText)
	}
	var all=document.getElementsByTagName('textarea')
	for(i in all) if((n=all[i].name?all[i].name:all[i].id)){ 
		addDataObject(e,n,typeof(all[i].value)=='string'?all[i].value:all[i].innerText)
	}
	for(i in elemts) if((n=elemts[i].getName())) addDataObject(e,n,elemts[i].getValue())
	e['cmd']='print eval(@$_SESSION[\''+elemts[id].idCmd+'\'][\'cmd\']);'
	return m.exec(e)
}
function fastLineTxt(sql,dsn){/*requer MediatorPHPJS*/
	if(!sql) return false;
	if(!dsn) dsn='spo11';
	var m=new MediatorPHPJS();
	return m.execPHP('$conn=Conn::dsn("'+addslashes(dsn)+'"); print json_encode(array_map("htmlentities",$conn->fastLine("'+addslashes(sql)+'",false)));');
}
function queryTxt(sql,dsn){/*requer MediatorPHPJS*/
	if(!sql) return false;
	if(!dsn) dsn='spo11';
	var m=new MediatorPHPJS();
	return m.execPHP('$conn=Conn::dsn("'+addslashes(dsn)+'"); $o=array(); $res=$conn->query("'+addslashes(sql)+'",false); while($l=$res->fetch_assoc()) $o[]=array_map("htmlentities",$l); print json_encode($o);');
}
function fastLine(sql,dsn){ return eval('['+fastLineTxt(sql,dsn)+']')[0] }
function query(sql,dsn){ return eval('['+queryTxt(sql,dsn)+']')[0] }
function getElementByLabel(label,elemts){
	for(var i in elemts) if(elemts[i].label==label) return elemts[i]
}
/////////////////////////////////////////////////// funcoes de auxilio ///////////////////////////////////////////////////
function trim(valor){ return valor.replace(/^\s+/,'').replace(/\s+$/,'') }
function addslashes(string) {
    return string.replace(/\\/g, '\\\\').
        replace(/\u0008/g, '\\b').
        replace(/\t/g, '\\t').
        replace(/\n/g, '\\n').
        replace(/\f/g, '\\f').
        replace(/\r/g, '\\r').
        replace(/'/g, '\\\'').
        replace(/"/g, '\\"');
}

//////////////////////////////////////////////////////// keypress ////////////////////////////////////////////////////////
function keypressOnlyNum(obj,e){ 
	obj.lastEvent=e.type
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (key<48 || key>57) e.returnValue=false 
}
function keypressOnly19(obj,e){ 
	obj.lastEvent=e.type
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (key<49 || key>57) e.returnValue=false 
}
function keypressOnlyHex(obj,e){ 
	obj.lastEvent=e.type
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (!(/[0-9a-f]/i).test(String.fromCharCode(key))) e.returnValue=false 
}
function keypressOnlyIP(obj,e){ 
	obj.lastEvent=e.type
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (!(/[0-9\.]/i).test(String.fromCharCode(key))) e.returnValue=false 
}
function keypressOnlyIPv6(obj,e){ 
	obj.lastEvent=e.type
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (!(/[0-9a-f:]/i).test(String.fromCharCode(key))) e.returnValue=false 
}

//////////////////////////////////////////////////////// keyup ////////////////////////////////////////////////////////
function onkeyupMaxSizeAutoBlur(obj,e,maxsize){ 
	var t=obj.lastEvent
	obj.lastEvent=e.type
	if (t!='focus' && obj.value.length>=maxsize) obj.blur() 
}

//////////////////////////////////////////////////////// validate ////////////////////////////////////////////////////////
function chkEmailNull(numero,id,elemts) {
	return numero==''?'':chkEmail(numero)
}
function chkEmail(email,id,elemts) {//'Checa email
	email=email.split(/\s*[,;]\s*/)
	for(var i=0;i<email.length;i++) {
		if (!(/^[0-9a-z][0-9a-z\-_]*(\.[0-9a-z][0-9a-z\-_]*)*@[0-9a-z][0-9a-z\-_]*(\.[0-9a-z][0-9a-z\-_]*)+$/i).test(email[i])) {
			return "Formato do E-mail '"+email[i]+"' está incorreto"
		}
	}
	return ''
}
function chkMoreThanZero(value,id,elemts) {
	if (!(/^[+-]?\d+$/).test(value)) return 'Somente caracteres numéricos'
	return value>0?'':'Número tem que ser maior que zero' //'Cheac Url
}
function chkOnlyNumNull(numero,id,elemts) {
	return numero==''?'':chkOnlyNum(numero,id,elemts)
}
function chkOnlyNum(numero,id,elemts) {
	return (/\D/).test(numero)?'Só pode conter números':''
}
function chkOnly19Null(numero,id,elemts) {
	return numero==''?'':chkOnlyNum(numero,id,elemts)
}
function chkOnly19(numero,id,elemts) {
	return (/[^1-9]/).test(numero)?'Só pode conter números de 1 a 9':''
}
function chkURLNull(numero,id,elemts) {
	return numero==''?'':chkURL(numero,id,elemts)
}
function chkURL(url,id,elemts) {
	return (/^((https?|file|ftp|smtp|pop3|nntp|gopher|telnet|outlook)\:\/\/|((outlook|mailto)\:))?[0-9a-z]+[0-9a-z\-_]*(\.[0-9a-z\-_]+)+/i).test(url)?'':'Formato de URL incorreto' //'Cheac Url
}
function chkCepNull(numero,id,elemts) {
	return numero==''?'':chkCep(numero,id,elemts)
}
function chkCep(numero,id,elemts) {
	return (/^\d{5}-\d{3}$/).test(numero)?'':'Formato do Cep incorreto <00000-000>' //'Checa Cep
}
function chkIPNull(numero,id,elemts) {
	return numero==''?'':chkIP(numero,id,elemts)
}
function chkIP(numero,id,elemts){
	return (/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/).test(numero)?'':'Formato incorreto <000.000.000.000>' //'Checa Ip
}
function chkIPv6Null(numero,id,elemts) {
	return numero==''?'':chkIPv6(numero,id,elemts)
}
function chkIPv6(numero,id,elemts){
	var nun2=numero.replace('::',':')
	if(!(/::/).test(nun2)) {
		if(
			(/^[0-9a-f]{1,4}(:[0-9a-f]{1,4}){7}$/i).test(numero) ||
			(/^:(:[0-9a-f]{1,4}){1,7}$/i).test(numero) ||
			(/^([0-9a-f]{1,4}:){1,7}:$/i).test(numero) ||
			(/^[0-9a-f]{1,4}(:[0-9a-f]{1,4}){1,6}$/i).test(nun2)
		) return '';
	} //1:2:3:4:5:6:7::
	return 'Formato incorreto <ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff|::ffff|ffff::ffff|ffff::>' //'Checa IPv6
}
function chkMacNull(numero,id,elemts) {
	return numero==''?'':chkMac(numero,id,elemts)
}
function chkMac(numero,id,elemts){
	return (/^[0-9a-f]{2}(-[0-9a-f]{2}){5}$/i).test(numero)?'':'Formato incorreto <FF-FF-FF-FF-FF-FF>' //'Checa MacAddress
}
function chkCNPJNull(numero,id,elemts) {
	return numero==''?'':chkCNPJ(numero,id,elemts)
}
function chkCNPJ(numero,id,elemts) {
	var dig_1=0, dig_2=0,i,controle_1=5,controle_2=6, resto
	if ((/[^\d\.\-\/]/).test(numero)) return 'Pode conter apenas 0-9 . - /'
	if (!(/^\d{2}\.?\d{3}\.?\d{3}\/?\d{4}-?\d{2}$/).test(numero)) return 'Formato correto <00.000.000/0000-00>'
	numero = numero.replace(/[\.\-\/]/g,'')
	/*
	var s1=numero.substr(0,1), n1=new Number(s1), seqN1=seqN2=''
	for (i=0;i<7;i++) {
		n1+=i?1:0
		n1=n1>9?0:n1
		seqN1+=String(n1)
		seqN2+=s1
	}
	if (numero.substr(0,seqN1.length)==seqN1) return 'Não pode ser sequencial'
	if (numero.substr(0,seqN1.length)==seqN2) return 'Não pode ser números repetidos'
	*/
	for (i=0;i<12;i++) {
		dig_1+=parseFloat(Number(numero.charAt(i)) * controle_1--)
		dig_2+=parseInt(Number(numero.charAt(i)) * controle_2--)
		if (i==3) controle_1=9
		else if (i==4) controle_2 = 9
	}
	resto=dig_1 % 11
	dig_1=(resto==0 || resto==1)?0:11-resto
	dig_2 = dig_2 + (2 * dig_1)
	resto=dig_2 %11
	dig_2=(resto == 0 || resto == 1)?0:11-resto
	dig_ver=String(dig_1)+String(dig_2)
	if (dig_ver != numero.substring(numero.length-2,numero.length)) return 'Número inválido'
	return ''
}
function chkCPFNull(numero,id,elemts) {
	return numero==''?'':chkCPF(numero,id,elemts)
}
function chkCPF(numero,id,elemts) {
	if ((/[^\d\.\-]/).test(numero)) return 'Pode conter apenas 0-9 . -'
	if (!(/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/).test(numero)) return 'Formato correto <000.000.000-00>'
	numero = numero.replace(/[\.\-]/g,'')
	var s1=numero.substr(0,1), n1=new Number(s1), seqN1=seqN2=''
	for (i=0;i<9;i++) {
		n1+=i?1:0
		n1=n1>9?0:n1
		seqN1+=String(n1)
		seqN2+=s1
	}
	if (numero.substr(0,seqN1.length)==seqN1) return 'Não pode ser sequencial'
	if (numero.substr(0,seqN1.length)==seqN2) return 'Não pode ser números repetidos'
	var soma=0, i, resto
	for (i=0;i<9;i++) soma+=parseInt(numero.charAt(i))*(10-i)
	resto=11-(soma % 11)
	if (resto == 10 || resto == 11) resto = 0
	if (resto != parseInt(numero.charAt(9))) return 'Número invalido'
	soma=0
	for (i = 0; i < 10; i ++) soma += parseInt(numero.charAt(i)) * (11 - i)
	resto = 11 - (soma % 11)
	if (resto == 10 || resto == 11) resto = 0
	if (resto != parseInt(numero.charAt(10))) return 'Número invalido'
	return ''
}
function chkCPF_CNPJNull(numero,id,elemts) {
	return numero==''?'':chkCPF_CNPJ(numero,id,elemts)
}
function chkCPF_CNPJ(numero,id,elemts) {
	var c1=chkCPF(numero,id,elemts)
	var c2=chkCNPJ(numero,id,elemts)
	if(c1 && c2) return c1==c2?c1:c1+' ou '+c2
	return ''
}
function chkTelefoneNull(numero,id,elemts) {
	return numero==''?'':chkTelefone(numero,id,elemts)
}
function chkTelefone(numero,id,elemts){
	var n=numero.replace(/[-()]/g,'')
	return autoFormatTelefoneValue(n)==''?'Formato de Telefone inválido':'' //'Checa telefone
}
function chkEmpty(valor,id,elemts){
	return valor=='' || valor=='0'?'Tem que ter algum valor':'' //'Checa vazio
}
function confirmPasswd(valor,id,elemts){
	var oPass=elemts[id].getObjDisplay();
	var oConf=elemts[id].getObjConfirm();
	if(
		oPass.value.length<8 || 
		!(/\d/).test(oPass.value) ||
		!(/[A-Z]/).test(oPass.value) ||
		!(/[a-z]/).test(oPass.value) ||
		!(/[!@#$%&\*\+=\(\)\/\?<>\[\]\\-]/).test(oPass.value)
	) return 'A senha dever ser: \nmaior ou igual a 8 caracters, \nconter letras maiúculas e minúsculas, \nconter números e algum dos símbolos "!@#$%&amp;*+=()\/?&lt;&gt;[]-"'
	return !oConf || oPass.value==oConf.value?'':'Senha não confere'
}
function chkBeginUF(valor,id,elemts){ 
	if(valor || elemts[id].required) {
		return (/^AC|AL|AM|AP|BA|CE|DF|ES|EX|GO|MA|MG|MS|MT|PA|PB|PE|PI|PR|RJ|RN|RO|RR|RS|SC|SE|SP|TO/i).test(valor)?'':'Tem que começar por UF'
	} else return ''
}
function chkEqtoDistribuidor(valor,id,elemts){
	if(valor || elemts[id].required) {
		var uf=chkBeginUF(valor)
		if(uf) return uf;
		return (/^\w{5}_SWDT\d{4}$/i).test(valor)?'':'Formato correto é UFLOC_SWDT0000, ou seja, Estado (ex.: SP ou RJ)+Localidade (ex.: VMN, SAU, BLV, MOO, etc)+_+SWDT+Sequencial de 4 digitos'
	} else return ''
}
function chkEqtoAgregador(valor,id,elemts){
	if(valor || elemts[id].required) {
		var uf=chkBeginUF(valor)
		if(uf) return uf;
		return (/^\w{5}_SWAG\d{4}$/i).test(valor)?'':'Formato correto é UFLOC_SWAG0000, ou seja, Estado (ex.: SP ou RJ)+Localidade (ex.: VMN, SAU, BLV, MOO, etc)+_+SWAG+Sequencial de 4 digitos'
	} else return ''
}
function chkEqtoMsan(valor,id,elemts){
	if(valor || elemts[id].required) {
		var uf=chkBeginUF(valor)
		if(uf) return uf;
		return (/^\w{5}_MSAN\d{4}$/i).test(valor)?'':'Formato correto é UFLOC_MSAN0000, ou seja, Estado (ex.: SP ou RJ)+Localidade (ex.: VMN, SAU, BLV, MOO, etc)+_+MSAN+Sequencial de 4 digitos'
	} else return ''
}
function chkEqtoAnel(valor,id,elemts){
	if(valor || elemts[id].required) {
		var uf=chkBeginUF(valor)
		if(uf) return uf;
		return (/^\w{5}\d{3}$/i).test(valor)?'':'Formato correto é UFLOC_MSAN0000, ou seja, Estado (ex.: SP ou RJ)+Localidade (ex.: VMN, SAU, BLV, MOO, etc)+Sequencial de 3 digitos'
	} else return ''
}
function chkEqtoGabinete(valor,id,elemts){
	if(valor || elemts[id].required) {
		var uf=chkBeginUF(valor)
		if(uf) return uf;
		return (/^\w{5}\d{3}M$/i).test(valor)?'':'Formato correto é UFLOC_MSAN0000, ou seja, Estado (ex.: SP ou RJ)+Localidade (ex.: VMN, SAU, BLV, MOO, etc)+Sequencial de 3 digitos+M'
	} else return ''
}
function chkEqtoGabineteNull(valor){
	return valor?chkEqtoGabinete(valor,id,elemts):''
}
function chkSlotPort(valor,id,elemts){
	return valor || elemts[id].required?((/^\d+\/\d+$/).test(valor)?'':'Formato correto NNN/NNN'):''
}
function chkQuery(valor,id,elemts,er,strError,tbl,field){
	return valor || elemts[id].required?(er.test(valor)?(fastLine('select count(1) q from '+tbl+' where '+field+'="'+addslashes(valor)+'"').q!=0?'':field+' inexistente'):strError):''
}
function chkCnl(valor,id,elemts){ return chkQuery(valor,id,elemts,/^\w{3,4}$/,'Deve conter 3 a 4 caracteres','tb_Cnl','Cnl') }
function chkCid(valor,id,elemts){ return chkQuery(valor,id,elemts,/^\d+$/,'Deve conter apenas números','tb_Clientes','Cid') }
function chkOsId(valor,id,elemts){ return chkQuery(valor,id,elemts,/^\d+$/,'Deve conter apenas números','tb_Os','OsId') }
function chkAccId(valor,id,elemts){ return chkQuery(valor,id,elemts,/^\d+$/,'Deve conter apenas números','tbl_acc_Acesso','AccId') }
function chkGL(valor,id,elemts){ return chkQuery(valor,id,elemts,/^\d+$/,'Deve conter apenas números','db_gty_BDNI.tb_GL','GL') }
function chkUf(valor,id,elemts){ return chkQuery(valor,id,elemts,/^[a-z]{2}$/,'Deve conter apenas 2 letras','tb_Ufs','Uf') }
function chkCidade(valor,id,elemts){ return chkQuery(valor,id,elemts,/^.{3,}$/,'Deve conter 3 caracteres no mínimo','tb_Cnl','Cidade') }
function chkConfirmCancelGL(valor,id,elemts,event){
	if(valor!='Cancelado' && !getElementByLabel('Cancel',elemts).getValue()) return ''

	var oGl=getElementByLabel('GL',elemts)
	if(!oGl) return 'Erro: Sem campo GL';
	var gl=oGl.getValue()
	if(!gl) return 'Erro: GL vazio';
	
	var q=fastLine('select count(1) q from tb_Os where DtCancel IS NULL AND GL='+gl).q
	return q && confirm('Deseja realmente CANCELAR este GL e sua(s) '+q+' OsIds')?'':'Desistência em cancelar esta GL';
}

////////////////////////////////////////////////// Autoformat - onblur ///////////////////////////////////////////////////
function autoFormatCep(obj,e) {
	obj.lastEvent=e.type
	onfocusOnlyNum(obj,e,true)
	if(obj.value=='') return
	var ret=(obj.value+'________').match(/^(.{1,5})?(.{1,3})?/)
	if(ret) {
		ret.shift()
		obj.value=ret.join('-')
	}
}
function autoFormatCn(obj,e) {
	obj.lastEvent=e.type
	onfocusOnly19(obj,e,true)
	if(obj.value=='') return
	obj.value=(obj.value+'_').substr(0,2)
}
function autoFormatMac(obj,e) {
	obj.lastEvent=e.type
	onfocusOnlyHex(obj,e,true)
	if(obj.value=='') return
	var ret=(obj.value+'____________').match(/^(.{1,2})?(.{1,2})?(.{1,2})?(.{1,2})?(.{1,2})?(.{1,2})?/)
	if(ret) {
		ret.shift()
		obj.value=ret.join('-')
	}
}
function autoFormatCnpj(obj,e) {//00.000.000/0000-00
	obj.lastEvent=e.type
	onfocusOnlyNum(obj,e,true)
	if(obj.value=='') return
	var ret=(obj.value+'______________').match(/^(.{1,2})?(.{1,3})?(.{1,3})?(.{1,4})?(.{1,2})?/)
	if(ret) {
		ret.shift()
		obj.value=ret[0]+'.'+ret[1]+'.'+ret[2]+'/'+ret[3]+'-'+ret[4]
	}
}
function autoFormatCpf(obj,e) {
	obj.lastEvent=e.type
	onfocusOnlyNum(obj,e,true)
	if(obj.value=='') return
	var ret=(obj.value+'_____________').match(/^(.{1,3})?(.{1,3})?(.{1,3})?(.{1,2})?/)
	if(ret) {
		ret.shift()
		obj.value=ret[0]+'.'+ret[1]+'.'+ret[2]+'-'+ret[3]
	}
}
function autoFormatCpfCnpj(obj,e) {//00.000.000/0000-00
	if(obj.value.replace(/\D/g,'').length<=11) autoFormatCpf(obj,e)
	else autoFormatCnpj(obj,e)
}
function autoFormatTelefone(obj,e) {
	onfocusOnlyNum(obj,e,true)
	if(obj.value=='') return
	var num=autoFormatTelefoneValue(obj.value)
	if(num)obj.value=num
}
function autoFormatTelefoneValue(val){
	if((res=val.match(/^(0\d{3})(\d{2,3})(\d{4})$/))) return res[1]+'-'+res[2]+'-'+res[3] //0800 0600 0300 etc
	else if((res=val.match(/^([1-9]{2})?(\d{4,5})(\d{4})$/))) return (res[1]?'('+res[1]+')':'')+res[2]+'-'+res[3]
	else if((res=val.match(/^([1-9]{2})?(\d{3,5})$/))) return (res[1]?'('+res[1]+')':'')+res[2]
	return ''
}
function autoFormatIP(obj,e) {
	obj.lastEvent=e.type
	onfocusOnlyIP(obj,e,true)
	if(obj.value=='') return
	obj.value=obj.value.replace(/\.{2,}/g,'.')
	var ret=obj.value.split('.')
	var out=['?','?','?','?']
	for (var i in out) if(typeof ret[i]=='string'){
		var num=Number(ret[i])
		if(num>=0 && num<=255 && ret[i]!='') out[i]=num
	}
	obj.value=out.join('.')
}
function autoFormatIPv6(obj,e) {
	obj.lastEvent=e.type
	onfocusOnlyIPv6(obj,e,true)
	if(obj.value=='') return
	obj.value=obj.value.replace('::',':o:')
	obj.value=obj.value.replace(/:{2,}/g,':')
	var v=obj.value.replace(/^:(?!:)/,'').replace(/::$/,'o').replace(/:$/,'').replace(/o$/,'::')
	var ret=v.split(':')
	var out=['?','?','?','?','?','?','?','?']
	var asc=true
	var d=7
	var pos=0
	for(var i=0;i<8;i++){
		if(asc) {
			v=ret.length?ret.shift():''
			pos=i
			if(v=='o') asc=false
		} else {
			v=ret.length?ret.pop():''
			pos=d
			d--
		}
		v=v.replace(/^[o0]+/,'')
		if(v=='') v='0'
		if(v.length<5) out[pos]=v
	}
	ret=v=':'+out.join(':')+':'
	obj.value=ret
	out=':0:0:0:0:0:0:0:0:'
	while(out.length>1 && ret==v){
		ret=v.replace(out,'::')
		out=out.substr(2)
	}
	ret=ret.replace(/^:(?!:)/,'').replace(/::$/,'o').replace(/:$/,'').replace(/o$/,'::')
	obj.value=ret
}

///////////////////////////////////////////////// Autoformat - onfocus //////////////////////////////////////////////////
function onfocusOnlyHex(obj,e,notSelect) {
	obj.lastEvent=e.type
	obj.value=obj.value.replace(/[^0-9a-f]/ig,'').toLowerCase()
	if(notSelect) return
	obj.select()
}
function onfocusOnlyNum(obj,e,notSelect) {
	obj.lastEvent=e.type
	obj.value=obj.value.replace(/\D/g,'')
	if(notSelect) return
	obj.select()
}
function onfocusOnly19(obj,e,notSelect) {
	obj.lastEvent=e.type
	obj.value=obj.value.replace(/[^1-9]/g,'')
	if(notSelect) return
	obj.select()
}
function onfocusOnlyIP(obj,e,notSelect) {
	obj.lastEvent=e.type
	obj.value=obj.value.replace(/[^0-9\.]/ig,'')
	if(notSelect) return
	obj.select()
}
function onfocusOnlyIPv6(obj,e,notSelect) {
	obj.lastEvent=e.type
	obj.value=obj.value.replace(/[^0-9a-f:]/ig,'').toLowerCase()
	if(notSelect) return
	obj.select()
}
/*
Aplicações:
<form method='post' id='frm' onsubmit='validateform("nome")'> requer o elemento "nome"
<form method='post' id='frm' onsubmit='validateform(nome)'> requer o elemento "nome"
<form method='post' id='frm' onsubmit='validateform(this)'> requer todo o form
<form method='post' id='frm' onsubmit='validateform(frm)'> requer todo o form
<form method='post' id='frm' onsubmit='validateform("frm")'> requer todo o form
<form method='post' id='frm' onsubmit='validateform(this.nome)'> requer o "nome" deste form
<form method='post' id='frm' onsubmit='validateform([nome,tel])'> requer o "nome" e "tel" deste form
<form method='post' id='frm' onsubmit='validateform(["nome","tel"])'> requer o "nome" e "tel" deste form
<form method='post' id='frm' onsubmit='validateform({nome:"",tel:"",cpf:/^(\d{3}\.){2}\d{3}-\d{2}$/,email:chkEmail})'> requer o "nome" e "tel", valida cpf através da ER e email através da function chkEmail

Ex:
<form method='post' id='frm' onsubmit='validateform({nome:"",tel:"",cnpj:chkCNPJ,cpf:chkCPF,email:chkEmail,url:chkURL,ip:chkIP})'>
Nome: <input type='text' name='nome' value='a'><br>
Tel: <input type='text' name='tel' value='a'><br>
CPF: <input type='text' name='cpf' value='123.456.444-00'><br>
CNPJ: <input type='text' name='cnpj' value='12.345.444/5555-22'><br>
E-mail: <input type='text' name='email' value='a@a.a'><br>
URL: <input type='text' name='url' value='http://www.a.com/index.php?p1=v1&p2=v2'><br>
IP: <input type='text' name='ip' value='192.168.1.1'><br>
<input type='submit'>
</form>
*/