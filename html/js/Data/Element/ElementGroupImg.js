eval('ElementGroupImg='+(new Element).constructor.toString())
ElementGroupImg.prototype=new Element()
ElementGroupImg.prototype.constructor=ElementGroupImg

ElementGroupImg.prototype.getObjFileBar=function(){ return document.getElementById(this.id+'_fileBar') }
ElementGroupImg.prototype.getObjControl=function(){ return document.getElementById(this.id+'_control') }
ElementGroupImg.prototype.getObjMss=function(){ return document.getElementById(this.id+'_mss') }
ElementGroupImg.prototype.getObjChk=function(obj){ 
	var oTbl=this.getObjDadById(obj,'ElementGroupImg_Table')
	return this.getObj('ElementGroupImg_Chk',oTbl)
}
ElementGroupImg.prototype.getSubId=function(obj){ return this.getObjChk(obj).value }

ElementGroupImg.prototype.change=function(obj,txt,sInput){
	var oTarget=this.getObj(sInput,obj.parentElement)
	var v=prompt(txt,oTarget.value)
	if (v!=null) oTarget.value=v
	return oTarget.value
}
ElementGroupImg.prototype.click=function(obj){
	var o=this.getObj('ElementGroupImg_Img',obj.parentElement)
	var imgPath=o.src.match(/img=(\/.*)$/)
	window.open(imgPath[1])
}
ElementGroupImg.prototype.remove=function(obj){
	if(!confirm('Deseja realmente apagar esta imagem?')) return
	var oTbl=this.getObjDadById(obj,'ElementGroupImg_Table')
	var oChk=this.getObj('ElementGroupImg_Chk',oTbl)
	oChk.value=0
	oTbl.removeNode()
	this.numRows--
	this.rebuild()
}
ElementGroupImg.prototype.changeDescr=function(obj){
	var v=this.change(obj,'Digite a Descrervação da imagem','ElementGroupImg_Descr')
	var oTbl=this.getObjDadById(obj,'ElementGroupImg_Table')
	var oFt=this.getObj('ElementGroupImg_Foto',oTbl)
	oFt.title=v
}
ElementGroupImg.prototype.rebuild=function(){
	var oFiles=this.getObjFileBar().childNodes
	var nod=document.createElement(oFiles[0].outerHTML)
	nod.disabled=false
	var cont=0
	for(var i=0; i<oFiles.length; i++) if(oFiles[i].value.replace(/ /g,'').length==0) oFiles[i].removeNode()
	else cont++

	var dis=(this.max && (this.numRows+cont)>=this.max)
	cont=0
	for(var i=0; i<oFiles.length; i++) if(oFiles[i].value.replace(/ /g,'').length==0) {
		oFiles[i].disabled=dis
		cont++
	}
	if(cont==0 && !dis) {
		nod.disabled=dis
		this.getObjFileBar().appendChild(nod)
	}
}
