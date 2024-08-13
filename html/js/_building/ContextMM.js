ContextMM=function(){}
ContextMM.prototype.id='easyContextMenu'
ContextMM.prototype.elements=new Array()
ContextMM.prototype.active=null
ContextMM.prototype.keyPressValue=null
ContextMM.prototype.onactivate=null
ContextMM.prototype.timeOut=null
ContextMM.prototype.outMethod=4

/**
 * Adciona um contextMenu para um tag
 *
 * tag: tag monitorado pelo contexto
 * evalSource: funcao ou metodo com parametros ou atributo que retorne o HTML do contexto
 * outMethod: [2] n|click|none n=numero que representa segundos para mouseout ou click
 * position: [right-down] left|center|right-up|middle|down
 * refererPosition: [right-top] left|center|right-up|middle|down
 */
ContextMM.prototype.add=function(tag,evalSource,outMethod,position,refererPosition){
	if(!tag || !evalSource) return
	oContextMM.elements.push({tag:tag,html:evalSource,outMethod:outMethod,position:position,refererPosition:refererPosition})
}
ContextMM.prototype.over=function(obj){ obj.className='over' }
ContextMM.prototype.out=function(obj){ obj.className='' }
ContextMM.prototype.keyPress=function(){ 
	var e=event;
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if (key==27) oContextMM.hide() 
}
ContextMM.prototype.clearTimeOut=function(){ 
	if (oContextMM.timeOut) {
		window.clearTimeout(oContextMM.timeOut)
		oContextMM.timeOut=false
	}
}
ContextMM.prototype.hide=function(){//Esconde o Context Menu
	if(oContextMM.active){
		document.onkeypress=oContextMM.keyPressValue
		document.getElementsByTagName('body')[0].onactivate=oContextMM.onactivate
		oContextMM.active.removeNode(true)
		oContextMM.active=null
		oContextMM.clearTimeOut()
	}
}
ContextMM.prototype.box=function(html){
	var out="<table border='0' cellspacing='0' class='Box'><tr>\n"
	out+="<td class='Box_tl'><div></div></td>\n"
	out+="<td class='Box_tc'><div></div></td>\n"
	out+="<td class='Box_tr'><div></div></td>\n"
	out+="</tr><tr>\n"
	out+="<td class='Box_ml'><div></div></td>\n"
	out+="<td class='Box_mc'>"+html+"</td>\n"
	out+="<td class='Box_mr'><div></div></td>\n"
	out+="</tr><tr>\n"
	out+="<td class='Box_bl'><div></div></td>\n"
	out+="<td class='Box_bc'><div></div></td>\n"
	out+="<td class='Box_br'><div></div></td>\n"
	out+="</tr></table>\n"
	return out
}
ContextMM.prototype.listen=function(obj){
	obj=obj?obj:event.srcElement
	while(obj) {
		for(var i in oContextMM.elements) if(obj==oContextMM.elements[i].tag) {
			event.returnValue=false
			return oContextMM.show(eval(oContextMM.elements[i].html),'',oContextMM.elements[i].outMethod,oContextMM.elements[i].position,oContextMM.elements[i].refererPosition)
		}
		obj=obj.parentNode
	}
}
ContextMM.prototype.contains=function(objMother,objChild){
	if (!objMother) return false
	while (objChild) if(objMother==objChild) return true
	else if(typeof(objChild.parentNode)!='undifined') objChild=objChild.parentNode
	return false
}
ContextMM.prototype.getDirectionOne=function(er,direction,def){
	var out=direction.match(/([-0-9])?\s*\b(r(?:ight)?|c(?:enter)?|l(?:eft)?)\b/i)
	if(!out) out=def
	if(typeof(out[0])=='undefined') out[0]=0
	if(typeof(out[1])=='undefined') out[1]=''
	out[0]=Number(out[0])
	out[1]=out[1].substr(0,1)
	return out
}
ContextMM.prototype.getDirection=function(direction,defH,defV){
	direction=direction?direction:''
	defH=defH?defH:[0,'rigth']
	defV=defV?defV:[0,'bottom']
	return {
		'h':oContextMM.getDirectionOne(/([-0-9])?\s*\b(r(?:ight)?|c(?:enter)?|l(?:eft)?)\b/i,direction,defH),
		'v':oContextMM.getDirectionOne(/([-0-9])?\s*\b(t(?:op)?|m(?:iddle)?|b(?:otton)?)\b/i,direction,defV)
	}
}
ContextMM.prototype.createDiv=function(id,html){
	var obj=document.getElementById(id)
	if(!obj) {
		obj=document.createElement("div")
		obj.id=id
		document.body.appendChild(obj)
	}
	obj.style.position='absolute'
	obj.style.visibility='hidden';
	//obj.style.display='none' 
	obj.innerHTML=oContextMM.box(html)
	return obj
}
ContextMM.prototype.boxOut=function(){ oContextMM.timeOut=setTimeout("oContextMM.hide()",oContextMM.outMethod*1000) }
ContextMM.prototype.boxOver=function(){ oContextMM.clearTimeOut() }
/**
 * Menu de contexto
 *
 * html: conteúdo do context
 * objBase: referencia do position
 * veja ContextMM.add
 */
ContextMM.prototype.show=function(html,objBase,outMethod,position,refererPosition){
	if (!html) return
	oContextMM.hide()
	if(objBase) {
		refererPosition=oContextMM.getDirection(refererPosition,[0,'rigth'],[0,'top'])
		/* //captura referencia do objBase em refererPosition
		obj.style.setExpression ('right',"document.getElementById('Layout_main').offsetWidth-document.getElementById('Layout_main').clientWidth")
		*/
		var x=document.body.clientWidth-event.clientX //event.clientX
		var y=document.body.clientHeight-event.clientY //event.clientY
	} else {
		//captura referencia x,y de event.srcElement
		var x=document.body.clientWidth-event.clientX //event.clientX
		var y=document.body.clientHeight-event.clientY //event.clientY
	}

	var obj=oContextMM.createDiv(oContextMM.id,html)

	//calcula onde ficará a caixa
	position=oContextMM.getDirection(position,[0,'rigth'],[0,'bottom']) //não funcionando, depois intergrar c/ 4 linhas abaixo
	if (x<obj.offsetWidth) obj.style.left=document.body.scrollLeft+event.clientX-obj.offsetWidth;
	else obj.style.left=document.body.scrollLeft+event.clientX;
	if (y<obj.offsetHeight) obj.style.top=document.body.scrollTop+event.clientY-obj.offsetHeight;
	else obj.style.top=document.body.scrollTop+event.clientY;
	
	
	//Comportamentos
	oContextMM.keyPressValue=document.onkeypress
	document.onkeypress=oContextMM.keyPress
	oContextMM.onactivate=document.getElementsByTagName('body')[0].onactivate //oContextMM.onactivate=document.getElementById('Layout_main').onactivate
	outMethod=outMethod?outMethod:4
	if(outMethod!='none') {
		document.getElementsByTagName('body')[0].onactivate=function() {
			if(!oContextMM.contains(document.getElementById(oContextMM.id),event.srcElement)) oContextMM.hide()
		}
		if(outMethod=Number(outMethod)) {
			oContextMM.outMethod=outMethod
			obj.onmouseout=oContextMM.boxOut
			obj.onmouseover=oContextMM.boxOver
			ContextMM.prototype.boxOut()
		}
	}
	obj.style.visibility='visible';
	obj.style.display='';
	obj.setActive()
//if ((/\<table/i).test(html)) alert(1)
	oContextMM.active=obj
}

window.oContextMM=new ContextMM()
document.oncontextmenu=oContextMM.listen