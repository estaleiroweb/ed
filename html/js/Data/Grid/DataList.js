eval('DataList='+(new Data).constructor.toString())
DataList.prototype=new Data()
DataList.prototype.constructor=DataList

DataList.prototype.objType='DataList'
DataList.prototype.submiting=false
DataList.prototype.id=''
DataList.prototype.idList=''
DataList.prototype.label=''
DataList.prototype.overLineColor='#E6E7EE'
DataList.prototype.key=''
DataList.prototype.url=new Array()
DataList.prototype.allFields=new Array()
DataList.prototype.allFieldsName=new Array()
DataList.prototype.hiddenFields=''
DataList.prototype.begin=0
DataList.prototype.end=0
DataList.prototype.lines=0
DataList.prototype.pageCount=0
DataList.prototype.recCount=0
DataList.prototype.d2="' onmouseover='oContextMM.over(this)' onmouseout='oContextMM.out(this)'>"
DataList.prototype.d3="</div>"

DataList.prototype.startVariables=function(){ 
	if (this.isStartVariables) return true
	this.isStartVariables=this.initVars({
		resetValue:'_resetdefault',
		defaultValue:'_default',
		showFilter:'_showFilter',
		showRecCount:'_showRecCount',
		showNavBars:'_showNavBars',
		showTable:'_showTable',
		page:'_page',
		order:'_order',
		group:'_group',
		lstFields:'_lstFields',
		widthField:'_widthField'
	})
	this.d1="<div onclick='"+this.id+"."
	return this.isStartVariables
}
DataList.prototype.inicialize=function() {//Refaz o tamanho do cabeçalho do filtro
	if(!this.startVariables()) return
	this.initVars({
		dataFilter:'_Filter',
		dataFilterTable:'_FilterTable',
		dataOut:'_out',
		dataRecCount:'_recCount',
		dataNavLines:'_navLines',
		dataLines:'_lines',
		dataNavPage:'_navPage',
		dataTxtPg:'_txtPg',
		dataData:'_data',
		dataTable:'_table'
	},true)
	this.values=(tmp=document.getElementById(this.id+'_FilterTable'))?tmp.getElementsByTagName('input'):false
	//ContextMM.add(id,element,evalSource,position,outMethod)
	oContextMM.add(this.dataTable, this.id+".contextMenuGrid(event.srcElement)")
	oContextMM.add(this.dataFilter, this.id+".contextMenuFilter(event.srcElement)")
	if(this.values) for(var i=0;i<this.values.length;i++) oContextMM.add(this.values[i], this.id+".contextMenuFilterValue("+i+")")

	if(!this.dataFilterTable || !this.dataTable) return
	var lstFL=this.dataFilterTable.getElementsByTagName('th')
	var lstTB=this.dataTable.getElementsByTagName('th')
	var nMax=Math.min(lstFL.length,lstTB.length)
	for (var i=0;i<nMax;i++) lstFL[i].style.width=(lstTB[i].offsetWidth)?lstTB[i].offsetWidth-7:100
}
////////////////////////////////////////////////////////////Grid////////////////////////////////////////////////////////////
DataList.prototype.sort=function(ev,field) {//Monta o ORDER BY de acordo com a seleção
	if(!this.startVariables()) return
	var incKey=ev.ctrlKey || ev.shiftKey
	var order=this.order.value,oItem
	if (order) {
		//regulariza o order
		order=order.replace(/(^ +| +$)/,'').replace(/ +/g,' ').replace(/ *, */g,',') //faz Trim, deduplica espaços e regulariza separações
		var sentido=''
		var orderList=order.split(',')
		k=false
		for (var i=0;i<orderList.length;i++){
			var orderItem=orderList[i].split(' ')
			if (orderItem[0].replace(/(^`|`$)/g,'')==field) {
				k=i
				sentido=(typeof(orderItem[1])=='string' && orderItem[1].toLowerCase()=='desc')?'':' desc'
				orderList[i]='`'+field+'`'+sentido
				break
			}
		}
		if (incKey) {
			if(k===false) orderList.push('`'+field+'`')
		} else{
			if(k===false) orderList=['`'+field+'`']
			else orderList=[orderList[k]]
		}
		order=orderList.join(',')
	} else order='`'+field+'`'
	this.order.value=order
	this.submit()
}
DataList.prototype.getFullId=function(obj) {//Monta o parametro de passagem para as consultas
	var idKey=this.key.split(',')
	obj=this.getParentElement(obj,/tr/i)
	var objIdValue=obj.getAttribute('value')
	if (!objIdValue) return ''
	var idValue=objIdValue.split(',')
	var retorno=''
	for (var i in idKey) {
		if (typeof(idValue[i])=='undefined') return ''
		retorno+=((retorno)?'&':'')+idKey[i]+'='+escape(idValue[i])
	}
	return retorno
}
DataList.prototype.isAnchor=function(objMother,objChild) {//click em uma linha
	if (!objMother || !objChild) return false
	while (objMother!=objChild) {
		if (objChild.nodeName=='A' || objChild.onclick) return true
		objChild=objChild.parentNode
	}
	return false
}
DataList.prototype.clickList=function(obj,e) {//click em uma linha
	if (this.isAnchor(obj,(e.target)?e.target:e.srcElement)) return
	if (this.url && typeof(this.url['View'])=='string' && (chave=this.getFullId(obj))) {
		location=this.url['View']+((this.url['View'].indexOf('?')==-1)?'?':'&')+chave
	}
}
DataList.prototype.overList=function(obj,e) {//Mouse over em uma linha
	if (!obj.rowIndex) return
	//obj.bgColor='#E6E7EE'
	obj.bgColor=this.overLineColor
	/*
	var contem=oContextMM.contains(obj,e.fromElement)
	if (!contem) obj.bgColor='#E6E7EE'
	*/
	obj.title=''
	var cellElem=(e.target)?e.target:e.srcElement
	if (cellElem && cellElem.nodeName!='TR') {
		var temp=this.getTextObject(cellElem)
		obj.title=temp.replace(/(^ *| *$)/,'')
	}
}
DataList.prototype.outList=function(obj,force) {//Mouse out em uma linha
	if (!obj.rowIndex) return
	if (force) obj.bgColor = obj.getAttribute('linecolor')
	else if(oContextMM.contains(obj,event.toElement)) obj.bgColor=obj.getAttribute('linecolor')
}
DataList.prototype.goURL=function(obj,tipo) {//Executa uma determinada URL cadastrada
	oContextMM.hide()
	var oUrl={ 
		Del:['Deseja realmente deletar este registro?','Tem certeza mesmo?'] ,
		Duplicate: ['Deseja realmente duplicar este registro?'] ,
		Clone: ['Deseja realmente clonar este registro?']
	}
	if (oUrl[tipo]) {
		var confMenu=(typeof(oUrl[tipo])=='string')?[oUrl[tipo]]:oUrl[tipo]
		for (var i in confMenu) if (!confirm(confMenu[i])) return
	}
	if (this.url && typeof(this.url[tipo])=='string'){
		var link=this.url[tipo]
		if (tipo!='new') link+=(link.indexOf('?')==-1?'?':'&')+this.getFullId(obj)
		if (event.ctrlKey || tipo=='del') location=link
		else window.open(link,'_blank')
	}
}
DataList.prototype.refresh=function() { 
	oContextMM.hide()
	this.submit() 
}
DataList.prototype.submitList=function() {//Efetua o submit do form
	if(!this.startVariables()) return
	var texto=this.submiting?'Por favor, aguarde.<br>Atualizando...':'Atualizando...';
	var o=['dataRecCount','dataNavLines','dataNavPage','dataData']
	for (var i=0; i<o.length; i++) if(typeof(this[o[i]])!='undefined') this[o[i]].style.display='none'
	this.dataOut.innerHTML=texto
	if(!this.submiting) this.submit()
	this.submiting=true
}
////////////////////////////////////////////////////////////Nav////////////////////////////////////////////////////////////
DataList.prototype.pageGo=function(page) {//Vai para uma determinada página
	if(!this.startVariables()) return
	var pg=Number(page)
	if (pg<1 || pg>this.pageCount) {
		alert('O número de página deve ser entre 1 e '+this.pageCount+',\nque corresponde ao mínimo e máximo desta consulta.')
		return
	}else if (Number(this.page.value)!=pg){
		this.page.value=pg
		this.submit()
	}
}
DataList.prototype.pageMove=function(page) {//Movimenta n ou -n páginas
	if(!this.startVariables()) return
	var pg=Number(this.page.value)+page
	if (pg<1) pg=1
	else if (pg>this.pageCount) pg=this.pageCount
	if (Number(this.page.value)!=pg) {
		this.page.value=pg
		this.submit()
	}
}
DataList.prototype.pageLast=function() { 
	if(!this.startVariables()) return
	this.pageGo(this.pageCount) 
}
DataList.prototype.focusPage=function(obj) {//Evento ganhar foco na caixa de texto de número de páginas
	if(!this.startVariables()) return
	obj.value=this.page.value
	obj.select()
}
DataList.prototype.keyPressPage=function(obj,e) {//Evento ao precionar uma tecla na caixa de texto de número de páginas
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (key==13) {
		e.returnValue=false
		obj.blur()
	} else e.returnValue=(/[0-9]/).test(String.fromCharCode(key))?true:false
}
DataList.prototype.blurPage=function(obj) {//Evento ao sair da caixa de texto de número de páginas
	if(!this.startVariables()) return
	obj.value='página '+this.page.value + ' de '+ this.pageCount
}
////////////////////////////////////////////////////////////Lines////////////////////////////////////////////////////////////
DataList.prototype.changeLines=function(obj) {//Mostra n linhas
	if(!this.startVariables()) return
	if (obj.value=='_') {
		var saida=true;
		while (saida) {
			var retorno=prompt('Quantas linhas você desejar mostrar (maior que 10)?',50,'integer')
			retorno=Number(retorno)
			if (retorno==0) {
				this.dataLines.value=this.lines
				return
			}
			if (retorno<=10) alert ('O valor deve ser maior que 10')
			else if (retorno<=3000 || confirm ('Você escolheu um valor muito alto.\nO computador pode demorar muito tempo para processar estas informações.\nDeseja realmente fazer isso?')) saida=false
		}
		var oOption = document.createElement("option")
		obj.options.add(oOption)
		oOption.value=retorno
		oOption.selected=true
	}
	this.submit()
}
////////////////////////////////////////////////////////////Filter////////////////////////////////////////////////////////////
DataList.prototype.applyFilter=function() {//Verifica as Teclas precionadas
	this.showTable.value=1
	event.returnValue=false
	this.submit()
}
DataList.prototype.filterKeyPress=function(obj,e) {//Verifica as Teclas precionadas
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (key==13) {
		if(!this.startVariables()) return
		obj.value=obj.value
		obj.blur()
		this.applyFilter()
	}
}
DataList.prototype.getListFieldsHtml=''
DataList.prototype.getListFields=function() {//Captura todos os campos e monta um objeto com seus nomes e a selecção dos ativos
	if(!this.startVariables()) return
	if (this.getListFieldsHtml) return this.getListFieldsHtml
	var selec=','+this.lstFields.value+','
	var hiddenFields=','+(this.hiddenFields.replace(/\s*,\s*/g,','))+',';
	this.getListFieldsHtml="<td><div id='"+this.id+"_cnf' class='tblData_cnfBox'><table border='0' cellspacing='0'>"
	for (var i in this.allFields) if (hiddenFields.indexOf(','+this.allFields[i]+',')==-1){
		var active=selec.indexOf(','+this.allFields[i]+',')!=-1
		this.getListFieldsHtml+="<tr>"
		this.getListFieldsHtml+="<th><input id='cnfHidden' type='checkbox' value='"+this.htmlspecialchars(this.allFields[i])+"'"+(active?' checked':'')+"></th>"
		this.getListFieldsHtml+="<td><nobr>"+this.allFieldsName[i]+"</nobr></td>"
		this.getListFieldsHtml+="</tr>"
	}
	this.getListFieldsHtml+="</table></div></td>"
	return this.getListFieldsHtml
}
DataList.prototype.cnfHtml=''
DataList.prototype.cnf=function() {//Configuração via context menu
	if(!this.startVariables()) return
	if(!this.cnfHtml) {
		this.cnfHtml="<table border='0' cellspacing='0'>"
		this.cnfHtml+="<tr><td><h2>"+this.label+"</h2></td></tr>"
		this.cnfHtml+="<tr>"+this.getListFields()+"</tr>"
		this.cnfHtml+="</table><div id='tblData_cnfButtons'>"
		this.cnfHtml+="<input type='button' value='Ok' onclick='"+this.id+".cnfShow()'>"
		this.cnfHtml+="<input type='button' value='Exit' onclick='oContextMM.hide()'>"
		this.cnfHtml+="</div>"
	}
	//oContextMM.show(html,objBase,position,outMethod)
	oContextMM.show(this.cnfHtml)
	//oContextMM.show(this.cnfHtml,'','click')
}
DataList.prototype.cnfShow=function(noSubmit) {//envia a recofiguração dos campos ao servidor
	if(!this.startVariables()) return
	var main=document.getElementById(this.id+"_cnf")
	if (!main) return alert("Erro no Sistema: não foi possivel localizar o grupo de configuração de campos")
	var showFields=new Array()
	var campos=main.getElementsByTagName('input')
	if (campos) for(var i=0; i<campos.length;i++){
		if(campos[i].id='cnfHidden') {
			if(campos[i].checked) showFields.push(campos[i].value)
		}
	}
	if(showFields.length==0) return alert ('Voce tem que selecionar pelo menos um campo')
	this.lstFields.value=showFields.join(',')
	if (!noSubmit) {
		this.submit()
		oContextMM.hide()
	}
}
DataList.prototype.changeDefault=function() {
	if(!this.startVariables()) return
	oContextMM.hide()
	this.defaultValue.value=1
	this.submit()
}
DataList.prototype.reset=function() {//envia a recofiguração dos campos ao servidor
	if(!this.startVariables()) return
	oContextMM.hide()
	this.resetValue.value=1
	this.submit()
}
DataList.prototype.outFormat=function(formato,tudo) {//Rechama o FORM para ser aberto em outro aplicativo (EXCEL...)
	if(!this.startVariables()) return
	oContextMM.hide()
	document.getElementById('tblData.outFormat').value=formato
	document.getElementById('tblData.outFormatId').value=this.idList
	
	var numLines=this.dataLines.value
	if (tudo) this.dataLines.value=0
	this.submit()
	
	document.getElementById('tblData.outFormat').value=''
	document.getElementById('tblData.outFormatId').value=''
	this.dataLines.value=numLines
}
DataList.prototype.exportList=function() {//Rechama o FORM para ser aberto em outro aplicativo (Export...)
	alert(['Desenvolvimento',this.id, this.idList])
	var m=new MediatorPHPJS();
	
	var oDiv = document.createElement('<div id="tblDataList_downloadListContent">')
	document.body.insertBefore(oDiv, document.body.firstChild);
	oDiv.innerHTML=makeBox(
'<div id="tblDataList_downloadListItem">'+
'<table width="100%" border="0" cellspacing="0" cellpadding="0">'+
'	<tr><td>'+
'		<table width="100%" border="0" cellspacing="0" cellpadding="0">'+
'			<tr>'+
'				<td id="tblDataList_downloadList_txtTitle">&nbsp;</td>'+
'				<td id="tblDataList_downloadList_Button"><button disabled="disabled">Cancel</button></td>'+
'			</tr>'+
'		</table>'+
'	</td></tr>'+
'	<tr><td>'+
'		<table width="100%" border="0" cellspacing="0" cellpadding="0">'+
'			<tr>'+
'				<td id="tblDataList_downloadList_txtStatus">Iniciando Download...</td>'+
'				<td id="tblDataList_downloadList_txtSize">&nbsp;</td>'+
'			</tr></table>'+
'	</td></tr>'+
'	<tr><td id="tblDataList_downloadList_GaugeBase">'+
'    	<div id="tblDataList_downloadList_GaugeTxt">50%</div>'+
'    	<div id="tblDataList_downloadList_GaugeProgress" style="width:50%;">&nbsp;</div>'+
'	</td></tr>'+
'</table>'+
'</div>')

	var oDivBlock = document.createElement('<div id="tblDataList_downloadList">')
	document.body.insertBefore(oDivBlock, document.body.firstChild);

	var oTitle=document.getElementById('tblDataList_downloadList_txtTitle') 
	var oButton=document.getElementById('tblDataList_downloadList_Button') 
	var oStatus=document.getElementById('tblDataList_downloadList_txtStatus') 
	var oSize=document.getElementById('tblDataList_downloadList_txtSize') 
	var oGaugeTxt=document.getElementById('tblDataList_downloadList_GaugeTxt') 
	var oGaugeProgress=document.getElementById('tblDataList_downloadList_GaugeProgress') 
	
	
	return

	return m.execPHP('$conn=Conn::dsn("'+addslashes(dsn)+'"); $o=array(); $res=$conn->query("'+addslashes(sql)+'",false); while($l=$res->fetch_assoc()) $o[]=array_map("htmlentities",$l); print json_encode($o);');
}
DataList.prototype.getTextObject=function(obj) {//Captura o texto contido no objeto
	if(!this.startVariables()) return
	return (typeof obj.innerText=='undefined')?obj.textContent:obj.innerText
}
DataList.prototype.copyField=function(o,tipo) {//Copia um campo, linha ou tabela para a área de transferencia
	if(!this.startVariables()) return
	oContextMM.hide()
	var valor=''
	if (tipo==0) valor=this.getTextObject(o) //Field
	else {
		var aCampos=this.lstFields.value.split(',')
		if (tipo==1){// Line
			for (var i=0; i<o.cells.length;i++)
			valor+=aCampos[i]+'\t'+this.getTextObject(o.cells[i])+'\r\n'
		}else { //Table
			valor=aCampos.join('\t')+'\r\n'
			for (i=1;i<o.rows.length;i++) {
				var cells=new Array()
				for (j=0;j<o.rows[i].cells.length;j++) cells.push(this.getTextObject(o.rows[i].cells[j]))
				valor+=cells.join('\t')+'\r\n'
			}
		}
	}
	this.copy(valor)
}
DataList.prototype.copyFieldsName=function() {//Copia um campo, linha ou tabela para a área de transferencia
	if(!this.startVariables()) return
	oContextMM.hide()
	this.copy(this.lstFields.value)
}
DataList.prototype.filterAssistent=function(numField){
	oContextMM.hide()
	if(!this.values) return
	var args=[this.lstFields.value.split(',')[numField],this.values[numField].value,document]
	var ret=window.showModalDialog(window.easyDataUrl+'/fn/assDataListFilter.html',args,'center:yes;resizable:yes;status:no;scroll:no;dialogHeight:600px;dialogWidth:700px;')
	if (typeof(ret)!='undefined') {
		this.values[numField].value=ret
		this.applyFilter()
	}
}
DataList.prototype.showHiddenFilterBox=function(force) {
	oContextMM.hide()
	if(this.dataFilter) {
		force=typeof(force)=='undefined'?(this.dataFilter.style.display=='none'):force
		this.dataFilter.style.display=force?'':'none'
	}
}
DataList.prototype.activeElement=function(obj,status){ obj.className=(status)?'active':'' }
////////////////////////////////////////////////////////////events////////////////////////////////////////////////////////////
DataList.prototype.cxtMGridHtml=''
DataList.prototype.contextCommonBegin=function() {
	if(!this.startVariables()) return
	var out="<div id='options'>"
	out+=this.d1+'cnf()'+this.d2+'Show/Hide Fields...'+this.d3
	return out
}
DataList.prototype.contextCommonEnd=function() {
	var out=''
	out+=this.d1+'copyFieldsName()'+this.d2+'Copy Fields Names'+this.d3
	if(this.dataFilter) {
		var tmp=this.dataFilter.style.display=='none'?'Show':'Hide'
		out+=this.d1+'showHiddenFilterBox()'+this.d2+tmp+' Filter'+this.d3
	}
	out+=this.d1+'outFormat("xls",0)'+this.d2+'Export this page to Excel'+this.d3
	out+=this.d1+'outFormat("xls",1)'+this.d2+'Export all pages to Excel'+this.d3
	out+=this.d1+'exportList()'      +this.d2+'Test Export'+this.d3
	out+=this.d1+'changeDefault()'	 +this.d2+'Default'+this.d3
	out+=this.d1+'reset()'			 +this.d2+'Reset'+this.d3
	//out+=this.d1+'refresh()'			+this.d2+'Refresh'+this.d3
	out+='</div>'
	return out
}
DataList.prototype.contextMenuGrid=function(obj) {
	var out=this.contextCommonBegin()
	if(!out) return
	var oTd,oTr
	if((oTd=this.getParentElement(obj,/t[hd]/i)) && (oTr=this.getParentElement(oTd,/tr/i))){
		var names={
			View:'Detail...',
			'New':'New...',
			Edit:'Edit...',
			Del:'Delete',
			Duplicate:'Duplicate...',
			Clone:'Clone...',
			Nav:'Navigation...'
		}
		var o2=this.id+".dataTable"
		var o1=o2+".rows["+oTr.rowIndex+"]"
		var o0=o1+".cells["+oTd.cellIndex+"]"
		var chave=this.getFullId(oTr)
		if(this.url) for (var i in this.url) if (this.url[i] && (chave || i=='new')) out+=this.d1+'goURL('+o0+',"'+i+'")'+this.d2+(typeof(names[i])=='string'?names[i]:i+'...')+this.d3
		out+=this.d1+'copyField('+o0+',0)'+this.d2+'Copy Field'+this.d3
		out+=this.d1+'copyField('+o1+',1)'+this.d2+'Copy Line'+this.d3
		out+=this.d1+'copyField('+o2+',2)'+this.d2+'Copy Table'+this.d3
	}
	out+=this.contextCommonEnd()
	return out
}
DataList.prototype.contextMenuFilter=function(obj) {
	var out=this.contextCommonBegin()
	if(!out) return
	out+=this.contextCommonEnd()
	return out
}
DataList.prototype.contextMenuFilterValue=function(fieldNum) {
	var out=this.contextCommonBegin()
	if(!out) return
	out+=this.d1+'filterAssistent('+fieldNum+')'+this.d2+'Wizard...'+this.d3
	out+=this.contextCommonEnd()
	return out
}

////////////////////////////////////////////////////////////global////////////////////////////////////////////////////////////
function fCnf(obj){//Exibe caixa de configuração de campos
	var titleCnf='',bodyCnf=''
	for(var id in DataElements) if(DataElements[id].objType=='DataList'){
		titleCnf+="<td><h2>"+DataElements[id].label+"</h2></td>"
		bodyCnf+=DataElements[id].getListFields()
	}
	var cnf=''
	cnf="<table border='0' cellspacing='0'>"
	cnf+="<tr>"+titleCnf+"</tr>"
	cnf+="<tr>"+bodyCnf+"</tr>"
	cnf+="</table><div id='tblData_cnfButtons'>"
	cnf+="<input type='button' value='Ok' onclick='cnfShow()'>"
	cnf+="<input type='button' value='Exit' onclick='oContextMM.hide()'>"
	cnf+="</div>"
	//cnf=cnf.replace(/[\n\r\t]/g,'')
//alert(cnf)
	oContextMM.show(cnf)
	//oContextMM.show(cnfHtml,obj,'none','rigth-down','left-down')
}
function cnfShow(){
	var iId=''
	for(var id in DataElements) if(DataElements[id].objType=='DataList') {
		if(!iId) iId=id
		DataElements[id].cnfShow(true)
	}
	DataElements[iId].submit()
	oContextMM.hide()
}
function getCells(obj){
	if (!obj) return
	var og=obj.getElementsByTagName('span')
	if(!og) return
	og=og[0]
	if(!(/\_getCells$/).test(og.id)) return
	var id=og.id
	var out=new Array()
	var oi=og.getElementsByTagName('input')
	for(var i=0;i<oi.length;i++) out[oi[i].id.replace(id+'.','')]=oi[i].value
	return out
}
