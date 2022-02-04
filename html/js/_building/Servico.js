function showhide(obj,variab){
	var o=obj.parentElement
	o.className=o.className=='close'?'open':'close'
	var med=new MediatorPHPJS
	med.setCookie(variab,o.className)
}
function geraTrail(obj) {
	if(obj.value) {
		if (confirm('Tem certeza que deseja criar um trail '+obj.value+' associado\\nESTA OPÇÃO NÃO TEM ROLL BACK\\n\\nDeseja prosseguir?') && confirm('Tem certeza?')) {
			document.forms[0].submit()
		} else obj.options[0].selected=true
	}
}
