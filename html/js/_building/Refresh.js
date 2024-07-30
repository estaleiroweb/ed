window.refreshed=false
function fRfr(total){
	this.refresh=function () {
		window.refreshed=true
		location=location.href
	}
	this.pad=function (valor) {
		var v='00'+valor
		return v.substr(v.length-2,2)
	}
	if (window.refreshed) return
	var objTime=document.getElementById('NavBar_rfr')
	if (total==0) {
		if (objTime) objTime.innerHTML="Rfr"
		return
	}

	if (typeof(window.tempoInicio)=='undefined') window.tempoInicio=new Date()

	var t=new Date()
	if (!total) {
		this.refresh()
		return
	}
	tot=(total?total:300)*1000
	var segund=tot-(t-window.tempoInicio)
	var tempoRest=new Date(segund)

	if (objTime) objTime.innerHTML=this.pad(tempoRest.getMinutes())+':'+this.pad(tempoRest.getSeconds())

	if (Math.floor(segund/1000)<1) this.refresh()
	window.setTimeout("fRfr("+total+")",1000)
}