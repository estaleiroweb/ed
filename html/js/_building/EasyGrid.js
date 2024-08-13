function goPage(pg){
	var d=document.getElementById
	d("pg").value=Math.min(Number(d('hnpg').value),Math.max(1,pg))
	if (d("pg").value!=d("hpg").value) document.forms[0].submit()
}
function addPage(pg){
	var d=document.getElementById
	if (pg==0) goPage(Number(d('hnpg').value))
	else goPage(Number(d('hpg').value) + pg)
}
function makeOrder(order){
	var d=document.getElementById
	d('order').value=order+(d('order').value==order?" desc":"")
	document.forms[0].submit()
}
function showExcel(){
	var act=document.forms[0].action
	document.forms[0].action='?GridOutput=xls'
	document.forms[0].submit()
	document.forms[0].action=act
}
function lineOver(obj){
	obj.title=window.defaultStatus=obj.innerText
}
function lineOut(obj){
	window.defaultStatus=''
}
function orderOver(obj){
	window.defaultStatus=obj.innerText
	obj.title='Clique aqui para ordernar por este campo'
}
function  orderOut(obj){
	window.defaultStatus=''
}
function imgClick(obj,url){
	if (obj.title=='Excluir') if(!confirm('Deseja realmente excluir este item?')) return;
	//window.open(url)
	location=url
}
function imgOver(obj){
	obj.className='over'
	window.defaultStatus=obj.title
}
function  imgOut(obj){
	obj.className=''
	window.defaultStatus=''
}