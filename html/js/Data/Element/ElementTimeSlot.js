eval('ElementTimeSlot='+(new Element).constructor.toString())
ElementTimeSlot.prototype=new Element()
ElementTimeSlot.prototype.constructor=ElementTimeSlot

ElementTimeSlot.prototype.getCheckers=function(){
	if (typeof (this.checkers)!='undefined') return this.checkers
	this.checkers=false
	var oTdCheckers=document.getElementById('ts_'+this.id)
	
	if(oTdCheckers) this.checkers=oTdCheckers.getElementsByTagName('input')
	return this.checkers
}
ElementTimeSlot.prototype.getTsString=function(tsbin){
	var ts=new Array()
	while (m=tsbin.match(/1+/)) {
		var t=m[0].length-1
		var s=m[0].replace(/1/g,'0')
		ts.push(t?m.index+'-'+(m.index+t):m.index)
		tsbin=tsbin.replace(/1+/,s)
	}
	return ts.join(',')
}
ElementTimeSlot.prototype.getAttr=function(elemen,value){ 
	var o=document.getElementById(elemen+this.id)
	if (typeof(value)!='undefined') {
		if(value==null) value=0
		o.innerHTML=value
	}
	return o.innerHTML 
}
ElementTimeSlot.prototype.getCh=function(value){ return this.getAttr('details_ch_',value) }
ElementTimeSlot.prototype.getBw=function(value){ return this.getAttr('details_bw_',value) }
ElementTimeSlot.prototype.getFree=function(value){ return this.getAttr('details_free_',value) }
ElementTimeSlot.prototype.getUsed=function(value){ return this.getAttr('details_used_',value) }
ElementTimeSlot.prototype.getTot=function(value){ return this.getAttr('details_tot_',value) }
ElementTimeSlot.prototype.getPercFree=function(value){ return this.getAttr('percent_free_',value) }
ElementTimeSlot.prototype.getPercUsed=function(value){ return this.getAttr('percent_used_',value) }
ElementTimeSlot.prototype.getTs=function(value){ return this.getAttr('details_ts_',value) }
ElementTimeSlot.prototype.rebuild=function(obj){
	var oCheckers=this.getCheckers()
	var objDisplay=this.getObjDisplay()
	var mark=false
	if(obj==oCheckers[0]) mark=oCheckers[0].checked
	if (oCheckers[0].checked && obj!=oCheckers[0] && !obj.checked) oCheckers[0].checked=false
	var bwTot=oCheckers[0].checked?2048:1984
	var oImput,m
	var ch=null
	var bw=0
	var used=0
	var valTs=0
	var tsbin=''
	for (var i=0;i<oCheckers.length;i++) {
		oImput=oCheckers[i]
		if(mark) oImput.checked=true
		if (oImput.checked) {
			used+=64
			if (oImput.disabled) tsbin+=' '
			else {
				if(ch==null) ch=i
				bw+=oImput.disabled?0:64
				valTs+=Math.pow(2,i)
				//alert([i,Math.pow(2,i),valTs])
				tsbin+='1'
			}
		} else tsbin+='0'
	}
	var free=bwTot-used
	
	this.getCh(ch)
	this.getBw(bw)
	this.getFree(free)
	this.getUsed(used)
	this.getTot(bwTot)
	this.getPercFree((free*100/bwTot).toFixed(2)+'%')
	this.getPercUsed((used*100/bwTot).toFixed(2)+'%')
	this.getTs(this.getTsString(tsbin))
	
	objDisplay.value=valTs
}
ElementTimeSlot.prototype.setObj=function(item,idCh){
	if (typeof(window[idCh])!='object') return
	window[idCh].setValue(this['get'+item]())
}
ElementTimeSlot.prototype.setValue=function(value){
	var oCheckers=this.getCheckers()
	var tsbin=''
	var tsbinValue=''
	var mark=value&1
	for (var i=0;i<oCheckers.length;i++) {
		oImput=oCheckers[i]
		var checked=(value>>i)&1
		tsbinValue+=checked?'1':'0'
		if (oImput.disabled) tsbin+=checked || (i && mark && !checked)?'1':'0'
		else tsbin+=i && mark && !checked?'1':'0'
	}
	if ((/1/).test(tsbin)) {
		alert("Os time slots setados\n"+this.getTsString(tsbinValue)+' \nestão com incoerências em\n'+this.getTsString(tsbin))
		return
	}
	for (var i=0;i<oCheckers.length;i++) if(!oCheckers[i].disabled) oCheckers[i].checked=(value>>i)&1
	this.rebuild()
}
