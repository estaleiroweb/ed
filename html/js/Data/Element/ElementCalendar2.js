eval('ElementCalendar='+(new Element).constructor.toString())
ElementCalendar.prototype=new Element()
ElementCalendar.prototype.constructor=ElementCalendar

ElementCalendar.prototype.calend=new Object()
ElementCalendar.prototype.getVars=function(){
	if (typeof(this.isrebuild)=='undefined') {
		this.required=typeof(this.required)=='undefined'?0:this.required
	}
}
ElementCalendar.prototype.inputformat='%F %T'
ElementCalendar.prototype.displayformat='%d/%m/%Y %T'
ElementCalendar.prototype.setDate=function (setDt,frm){
	var oInp=this.getObjInput()
	var oDisp=this.getObjDisplay()
	if(!this.calend.ifFormat && !this.calend.daFormat) this.start()
	if(!frm) frm=this.calend.daFormat
	var sInputDt=''
	var sDisplayDt=''
	if(setDt) {
		this.calend.date = Date.parseDate(setDt, frm)
		sInputDt=this.calend.date.print(this.calend.ifFormat)
		sDisplayDt=this.calend.date.print(this.calend.daFormat)
		/*
		var sDt=setDt.match(/(\d+)(-|\/)(\d+)\2(\d+)/)
		var Hs=setDt.match(/\d+:\d+(:\d+)?/)
		if (!Hs) Hs=''
		if (sDt) {
			if (Hs) Hs=' '+Hs
			if (sDt[2]=='-') {
				sInputDt=sDt[1]+'-'+sDt[3]+'-'+sDt[4]
				sDisplayDt=sDt[4]+'/'+sDt[3]+'/'+sDt[1]
			} else {
				sInputDt=sDt[4]+'-'+sDt[3]+'-'+sDt[1]
				sDisplayDt=sDt[1]+'/'+sDt[3]+'/'+sDt[4]
			}
		}
		sInputDt+=Hs
		sDisplayDt+=Hs
		*/
	}
	oInp.value=sInputDt
	oDisp.value=sDisplayDt
	this.onchange()
}
ElementCalendar.prototype.setDateByTimeStamp=function (setDt){ this.setDate(setDt,'%F %T') }
ElementCalendar.prototype.start=function () {
	this.getVars()
	this.calend['ifFormat']=this.inputformat
	this.calend['daFormat']=this.displayformat
	this.calend['inputField']=this.preIdInput+this.id
	this.calend['displayArea']=this.preIdDisplay+this.id
	this.calend['button']=this.preIdButton+this.id
	this.calend=Calendar.setup(this.calend)
}
ElementCalendar.prototype.setValue=function(value){ this.setDate(value) }
ElementCalendar.prototype.onkeypress=function(e) {
	var carac=Date.trFormat(this.calend.daFormat).replace(/%./g,'').replace(/-/g,'\\-').replace(/\[/g,'\\[').replace(/\]/g,'\\]').replace(/\(/g,'\\(').replace(/\)/g,'\\)')
	var expReg=new RegExp('[\\d'+carac+']')
	if (!expReg.test(String.fromCharCode(e.keyCode))) e.returnValue=false
}
ElementCalendar.prototype.formValidate=function(value,id,elemts){
	//var dateFmt = this.calend.inputField ? this.calend.ifFormat : this.calend.daFormat;
	this.calend.date = Date.parseDate(value, this.calend.daFormat)
	var out='';
	if (typeof(this.min)!='undefined') {
		var dtMin=Date.parseDate(this.min, this.calend.ifFormat)
		if(this.calend.date<dtMin) out+='maior que '+dtMin.print(this.calend.daFormat)
	}
	if (typeof(this.max)!='undefined') {
		var dtMax=Date.parseDate(this.max, this.calend.ifFormat)
		if(this.calend.date>dtMax) out+=(out?' e ':'')+'menor que '+dtMax.print(this.calend.daFormat)
	}
	return out?'Data deve ser '+out:''
}
