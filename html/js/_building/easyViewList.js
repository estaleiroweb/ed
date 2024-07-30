function openClose(obj,p1,p2){
	var oContent=obj.parentElement.parentElement.childNodes[1]
	obj.className=obj.className=='close'?'open':'close'
	oContent.style.display=oContent.style.display=='none'?'':'none'
	if(!oContent.innerHTML) {
		oContent.innerHTML='Carregando...'
		var oRsqt=new xmlHttpRequest()
		oRsqt.src='http://'+location.host+location.pathname
		oRsqt.parameteres='host='+document.getElementById('host').value+'&p1='+p1+'&p2='+p2
		oRsqt.htmlTarget=oContent
		oRsqt.async=true
		oRsqt.onloaded='checkerDiv()'
		oRsqt.load()
	}
}
function changeMouse(obj,bSent){
	var classStr=obj.className.replace(/over$/,'')
	if (bSent) classStr+='over'
	obj.className=classStr
	obj.title=obj.innerText
}
function showSql(obj,ViewId){
	checkerDiv(obj)
	parent.showSqlURL('easyViewEdtSql.php?host='+document.getElementById('host').value+'&ViewId='+ViewId)
}
function showField(obj,idField){
	checkerDiv(obj)
	parent.showSqlURL('easyViewEdtField.php?host='+document.getElementById('host').value+'&idField='+idField)
}
function showView(obj,idDataView){
	checkerDiv(obj)
	parent.showSqlURL('easyViewEdtTbl.php?host='+document.getElementById('host').value+'&idDataView='+idDataView)
}
function changeCat(obj){
	var v='DIV.cat'+obj.value
	var s=v && !obj.options[0].selected?'display:none;':'';
	var stylCat=document.getElementById('stylCat').styleSheet.rules
	for(var i=0;i<stylCat.length;i++) {
		stylCat[i].style.cssText=v==stylCat[i].selectorText?'':s
	}
}
function checkerDiv(obj){
	if (window.easyViewSelected) {
		var oldDiv=document.getElementById(window.easyViewSelected)
		if (oldDiv) oldDiv.className=oldDiv.className.replace(/(chk)?(over)?$/,'')
	}
	if (obj) {
		obj.className=obj.className.replace(/(chk)?(over)?$/,'')+'chkover'
		window.easyViewSelected=obj.id
	}else {
		if (oldDiv) oldDiv.className+='chk'
	}
}
window.easyViewSelected=''