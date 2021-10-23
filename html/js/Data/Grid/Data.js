window.DataElements=new Object()
Data=function(id){
	if(typeof(id)=='undefined') return
	this.id=id
	window.DataElements[id]=this
}
Data.prototype.isStartVariables=false 
Data.prototype.initVars=function(vars,force){
	for (var i in vars) {
		this[i]=document.getElementById(this.id+vars[i])
		if(!this[i] && !force) {
			alert("ERRO nesta página.\nAvise a Equipe de Sistemas.\nResponsável por esta manutenção: Helbert Fernandes 831 2318\n\nId:"+this.id+"\nLabel:"+this.label+"\nMétodo: "+i)
			return false
		}
	}
	return true
}
Data.prototype.startVariables=function(){ return false }

Data.prototype.getForm=function(){ return document.forms[0] }
Data.prototype.submit=function(){ 
	var btt=document.getElementById(this.id+'_submit_button')
	if (btt && btt.click) btt.click()
	else if (btt=this.getForm()) btt.submit()
	else alert('Não existe Formulário para submit')
}

Data.prototype.over=function(obj){ obj.className='over' }
Data.prototype.out=function(obj){ obj.className='' }
Data.prototype.copy=function(valor){//Copia algum valor para área de transferencia
	window.clipboardData.setData('Text',valor);
}
Data.prototype.getParentElement=function(obj,erTag){//captura o parent objeto que satisfaz o tag referido
	while (obj && typeof(obj.nodeName)=='string' && !erTag.test(obj.nodeName)) obj=obj.parentNode
	return obj
}
Data.prototype.htmlSlashes=function(value){
	return value.replace(/"/g,"\\x22").replace(/'/g,"\\x27").replace(/\t/g,"\\t").replace(/\n/g,"\\n").replace(/\r/g,"\\r").replace(/\\/g,"\\\\");
}
Data.prototype.unHtmlSlashes=function(value){
	return value.replace(/\\x22/g,'"').replace(/\\x27/g,"'").replace(/\\t/g,"\t").replace(/\\n/g,"\n").replace(/\\r/g,"\r").replace(/\\\\/g,"\\");
}
Data.prototype.htmlspecialchars=function(value){
	return value.replace(/"/g,'&quot;').replace(/'/g,'&#039;').replace(/\t/g,'&#009;').replace(/\n/g,'&#010;').replace(/\r/g,'&#013;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function fUrl(){
	var retorno=escape(location.href.substr(0,location.href.length-location.search.length))
	var obj={
		DataTab:['dataActive'],
		DataList:['showTable','values','showFilter','showRecCount','showNavBars','page','order','group','lstFields','widthField','dataLines']
	}
	for (var id in DataElements) {
		var o=DataElements[id]
		if(typeof(obj[o.objType])!='undefined') {
			var list=obj[o.objType]
			for(var idProp=0;idProp<list.length;idProp++) if (o[list[idProp]]) {
				var elem=o[list[idProp]]
				if(list[idProp]=='values') {
					for (var i=0;i<elem.length;i++)if (elem[i].value!='*') retorno+='&'+escape(elem[i].name)+'='+escape(elem[i].value)
				} else retorno+='&'+escape(elem.name)+'='+escape(elem.value)
			} else alert([id,o.label,o.objType,list[idProp],o[list[idProp]]])
		}
	}
	retorno+='&rawk='+Math.floor(Math.random( )*1000)
	retorno=window.copyUrlPath+'?'+retorno
	window.clipboardData.setData('Text',retorno)
	return retorno
}