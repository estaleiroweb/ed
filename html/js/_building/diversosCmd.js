function stripHtml(s) {
	return s.replace(/\&/g, "&amp;").replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\t/g, "&nbsp;&nbsp;&nbsp;").replace(/\n/g, "<br />");
}
function eetOldValue(obj){
	switch (obj.nodeName) {
		case 'INPUT':
			switch (obj.type.toLowerCase()) {
			   case 'radio':
			      var radios=document.getElementsByName(obj.name)
			      for (var i=0;i<radios.length;i++) if (radios[i].checked) {
						radios[0].oldValue=radios[i].value
						return
					}
					return
			   case 'checkbox':
					obj.oldValue=obj.checked
					return
			}
	   case 'SELECT': case 'TEXTAREA':
			obj.oldValue=obj.value
			return
		default:
			obj.oldValue=obj.innerHTML
			return
	}
}
function restoreValue(obj){
	switch (obj.nodeName) {
		case 'INPUT':
			switch (obj.type.toLowerCase()) {
			   case 'radio':
			      var radios=document.getElementsByName(obj.name)
			      for (var i=0;i<radios.length;i++) if (radios[i].value==radios[0].oldValue) {
						radios[i].checked=true
						return
					}
					return
			   case 'checkbox':
					obj.checked=obj.oldValue
					return
			}
	   case 'SELECT': case 'TEXTAREA':
			obj.value=obj.oldValue
			return
		default:
			obj.innerHTML=obj.oldValue
			return
	}
}
function dbUpdate(obj,parametros) {
	//Executa MySQL comando
	var url='/shared/tdt/php/httpReqUpdate.php?wkpTime='+wkpTime()
	bAsync=false
	var oHash
	if (typeof(parametros.hash)!='undefined') {
	   oHash=parametros.hash
	   parametros.hash=oHash.hash
	}
	var strParameters=http_build_query(parametros)
	var x=new xmlHttpRequest(url,strParameters,bAsync)
	var errorLog=''
	var msg=''
	var oXml=x.XmlHttp.responseXML
	var oTxt=x.XmlHttp.responseText
	var root=getTag(oXml,"root")
	if (root){
		var error=getTag(root,"error")
		if (error) errorLog=getValue(error)
		else if (!Number(getValue(getTag(root,"updated")))) errorLog='Nenhum registro Atualizado'
		msg=getValue(getTag(root,"msg"))
	} else errorLog=oTxt
	if (errorLog) {
		alert(errorLog+(msg?"\n"+msg:''))
	   restoreValue(obj)
	   if (typeof(parametros.restore)!='undefined') for (var i=0;parametros.restore.length;i++) restoreValue(parametros.restore[i])
		return
	}
	if (oHash.hash) {
		var hashNovo=getValue(getTag(root,"hash"))
	   oHash.hash=hashNovo
	}
	setOldValue(obj)
	showTemporyMessage("Atualizado",1000)
}
function showTemporyMessage(mess,tempo,alinhamento) {
	var d=document.getElementById
	if (typeof(mess)=='undefined') return
	if (typeof(tempo)=='undefined') tempo=5000
	if (typeof(window.TshowTemporyMessage)!='undefined') window.clearTimeout(window.TshowTemporyMessage)
	if (typeof(alinhamento)=='undefined' || alinhamento.substr(0,1)=='') alinhamento='cc'
	alinhamento=alinhamento.toLowerCase()
	var alW=alinhamento.substr(0,1)
	var alH=alinhamento.substr(1,1)

	var objDiv=d('TagShowTemporyMessage')
	if (!objDiv) {
		objDiv=document.createElement("div")
		document.body.appendChild(objDiv);
		objDiv.id='TagShowTemporyMessage';
		objDiv.style.position='absolute';
	}
	objDiv.innerHTML=mess;
	var oW=objDiv.clientWidth
	var oH=objDiv.clientHeight
	var dW=document.body.clientWidth
	var dH=document.body.clientHeight
	objDiv.style.top=alH=='t'?0:(alH=='b'?dW:Math.ceil((dH-oH)/2))
	objDiv.style.left=alW=='l'?0:(alW=='r'?dW:Math.ceil((dW-oW)/2))
	window.TshowTemporyMessage=window.setTimeout("document.getElementById('TagShowTemporyMessage').removeNode(true)",tempo)
}
function wkpTime () {
	var w=new Date()
	return String(w.getYear())+w.getMonth()+w.getDate()+w.getHours()+w.getMinutes()+w.getSeconds()+w.getMilliseconds()
}
function getTag(obj,tag) {
	var aTags=tag.replace(/\s/g,'').split('.')
	var tag=aTags.shift()
	if (typeof(obj.getElementsByTagName(tag))=='object' && typeof(obj.getElementsByTagName(tag)[0])=='object') {
		var oTag=obj.getElementsByTagName(tag)[0]
	} else return
	if (aTags.length) {
	   var oTagChild=getTag(oTag,aTags.join('.'))
	   return oTagChild
	} else return oTag
}
function getValue(tag) {
	return tag?(typeof(tag.content)=='undefined'?tag.text:tag.content):null
}
function htmlSpecial(html) {
   var tbl=new Array()
	tbl["&amp;"]=/&/g
	tbl["&lt;"]=/</g
	tbl["&gt;"]=/>/g
	tbl["&nbsp;&nbsp;&nbsp;"]=/\t/g
	tbl["<br>"]=/(\r\n|\n\r|\r|\r)/g
	tbl["&nbsp;"]=/ /g
	tbl["&quot;"]=/"/g
	for (var i in tbl) html=html.replace(tbl[i],i)
	return html
}
