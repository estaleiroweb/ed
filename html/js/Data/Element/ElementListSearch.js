eval('ElementListSearch='+(new ElementSearch).constructor.toString())
ElementListSearch.prototype=new ElementSearch()
ElementListSearch.prototype.constructor=ElementListSearch

ElementListSearch.prototype.oSerach=false
ElementListSearch.prototype.oErase=false
ElementListSearch.prototype.oSetUp=false
ElementListSearch.prototype.oSetDn=false
ElementListSearch.prototype.oTFoot=false
ElementListSearch.prototype.oTBody=false
ElementListSearch.prototype.oTBodyElement=false

ElementListSearch.prototype.getTBodyElement=function(){
	if (!this.oTBodyElement) {
		var oDisp=this.getObjDisplay()
		if (!oDisp) return
		var oTBody=oDisp.getElementsByTagName('tbody')
		if (!oTBody) return
		this.oTBodyElement=oTBody[0]
	}
	return this.oTBodyElement
}
ElementListSearch.prototype.getTBody=function(){
	if (!this.oTBody) {
		var oTBody=this.getTBodyElement()
		if (!oTBody) return
		this.oTBody=oTBody.rows
	}
	return this.oTBody
}
ElementListSearch.prototype.getTFoot=function(){
	if (!this.oTFoot) {
		var oDisp=this.getObjDisplay()
		if (!oDisp) return
		var oTFoot=oDisp.getElementsByTagName('tfoot')
		if (!oTFoot) return
		this.oTFoot=oTFoot[0]
	}
	return this.oTFoot
}
ElementListSearch.prototype.getObjFootLine=function(id){
	var oTFoot=this.getTFoot()
	if(!oTFoot) return
	var oInputs=oTFoot.getElementsByTagName('input')
	for (var i=0; i<oInputs.length; i++) if (oInputs[i].id==id) return oInputs[i]
}
ElementListSearch.prototype.getObjCheckInLine=function(obj){
	var oInputs=obj.getElementsByTagName('input')
	for (var i=0; i<oInputs.length; i++) if (oInputs[i].type=='checkbox') return oInputs[i]
}
ElementListSearch.prototype.setPositionButton=function(obj,oMv){
	var oChk=this.getObjCheckInLine(obj)
	if (oChk && oMv) oMv.disabled=oChk.checked
}
ElementListSearch.prototype.getSerachButton=function(){
	if (!this.oSerach) this.oSerach=this.getObjFootLine(this.id+'_search')
	return this.oSerach
}
ElementListSearch.prototype.getEraseButton=function(){
	if (!this.oErase) this.oErase=this.getObjFootLine(this.id+'_erase')
	if (!this.oErase) this.oErase={}
	return this.oErase
}
ElementListSearch.prototype.getSetUpButton=function(){
	if (!this.oSetUp) this.oSetUp=this.getObjFootLine(this.id+'_setUp')
	return this.oSetUp
}
ElementListSearch.prototype.getSetDnButton=function(){
	if (!this.oSetDn) this.oSetDn=this.getObjFootLine(this.id+'_setDn')
	return this.oSetDn
}
ElementListSearch.prototype.rebuild=function(){
	var oTBody=this.getTBody()
	if (!oTBody) return
	var cont=oTBody.length
	var oSearch=this.getSerachButton()
	var oErase=this.getEraseButton()
	var oSetUp=this.getSetUpButton()
	var oSetDn=this.getSetDnButton()
	if(oSearch) oSearch.disabled=(this.max!=0 && cont>=this.max)
	oErase.disabled=true
	if (cont) {
		for(var i=0; i<cont;i++) {
			oTBody[i].className=i&1?'par':'impar'
			var oChk=this.getObjCheckInLine(oTBody[i])
			if (oChk && oChk.checked) oErase.disabled=false
			if(this.showNumOrder) {
				var nCell=(oChk)?1:0
				var oCell=oTBody[i].cells[nCell]
				if (oCell) oCell.innerHTML=i+1
			}
		}
		if (oErase.disabled) {
			if (oSetUp) oSetUp.disabled=true
			if (oSetDn) oSetDn.disabled=true
		} else {
			this.setPositionButton(oTBody[0],oSetUp)
			this.setPositionButton(oTBody[cont-1],oSetDn)
		}
	}
}
ElementListSearch.prototype.check=function(obj,all){
	if (all) {
		var oDisp=this.getObjDisplay()
		var oInputs=oDisp.getElementsByTagName('input')
		for (var i=0; i<oInputs.length; i++) {
			if (oInputs[i].type=='checkbox') oInputs[i].checked=obj.checked
		}
	}
	this.rebuild()
}
ElementListSearch.prototype.positionObj=function(position,orientacao){
	var oTBody=this.getTBody()
	var oSource=oTBody[position]
	var oTarget=oTBody[position+orientacao]
	var tmp=oTarget.id
	oTarget.id=oSource.id
	oSource.id=tmp
	for(var i=0;i<oSource.cells.length; i++) {
		tmp=oTarget.cells[i].innerHTML
		oTarget.cells[i].innerHTML=oSource.cells[i].innerHTML
		oSource.cells[i].innerHTML=tmp
	}
}
ElementListSearch.prototype.positionAll=function(orientacao){
	var oTBody=this.getTBody()
	if (!oTBody) return
	var cont=oTBody.length
	if (orientacao==1) for(var i=cont-1; i>=0;i--) {
		var oChk=this.getObjCheckInLine(oTBody[i])
		if (oChk && oChk.checked) this.positionObj(i,orientacao)
	} else for(var i=0; i<cont;i++) {
		var oChk=this.getObjCheckInLine(oTBody[i])
		if (oChk && oChk.checked) this.positionObj(i,orientacao)
	}
	this.rebuild()
}
ElementListSearch.prototype.positionUp=function(){
	this.positionAll(-1)
}
ElementListSearch.prototype.positionDn=function(){
	this.positionAll(1)
}
ElementListSearch.prototype.erase=function(){
	if (!confirm('Tem certeza que deseja retirar este(s) Elemento(s)?')) return
	var oTBody=this.getTBody()
	if (!oTBody) return
	var cont=oTBody.length
	var rebuild=false
	for(var i=cont-1; i>=0;i--) {
		var oChk=this.getObjCheckInLine(oTBody[i])
		if (oChk && oChk.checked) {
			oTBody[i].removeNode(true)
			rebuild=true
		}
	}
	if (rebuild) this.rebuild()
}
ElementListSearch.prototype.setValue=function(value){
	//var oDisp=this.getObjDisplay()
	var oTBody=this.getTBodyElement()
	if (!oTBody) return
	var oTR=oTBody.insertRow()
	if (!oTR) return
	var oCell=oTR.insertCell()
	oCell.innerHTML="<input type='hidden' id='searchlistInserted' /><input class='checkbox' type='checkbox' onclick='"+ this.id + ".check(this,false)' />"
	if (this.showNumOrder) oTR.insertCell()
	var inputs=''
	for (var k in this.keyArgs) {
		var n=typeof(k)=='string'?k:this.keyArgs[k];
		var oInput=document.createElement("<INPUT type='hidden' name='"+this.name+"["+n+"][]' />");
		oCell.appendChild(oInput);
		oInput.value=typeof(value[this.keyArgs[k]])=='undefined'?'':value[this.keyArgs[k]]
	}
	for(var i=0;i<this.showFields.length;i++) {
		var oCell=oTR.insertCell()
		oCell.innerHTML=typeof(value[this.showFields[i]])=='undefined'?'':value[this.showFields[i]]
	}
	this.rebuild()
	this.onchange()
}
