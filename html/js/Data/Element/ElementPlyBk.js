function ElementPlyBk(id){
	this.writeBox=function (){
		this.oBox.innerHTML=(this.item+1)+'/'+this.itens.length
	}
	this.reset=function (){
		this.item=0
		this.writeBox()
	}
	this.buttonPlay=function (){
		this.oPlay.className=this.start?'pBkPause':'pBkPlay'
	}
	this.setPlay=function (st){
		if(typeof(st)=='undefined') this.start=!this.start
		else this.start=st
		this.buttonPlay()
	}
	this.setStop=function (){
		if (this.start) {
			this.setPlay(false)
			this.reset()
		}
	}
	this.setRew=function (){
		this.item--
		this.analizeItem()
		this.showItem()
	}
	this.setFF=function (){
		this.item++
		this.analizeItem()
		this.showItem()
	}
	this.setBox=function (){
	}
	this.runStep=function (){
		if (this.start) this.setFF()
		this.setTimer()
	}
	this.analizeItem=function (){
		if (this.item>=this.itens.length) this.item=0
		else if(this.item<0)this.item=this.itens.length-1
	}
	this.showItem=function (){
		if(this.action) eval(this.action+"('"+this.itens[this.item]+"')")
		this.writeBox()
	}
	this.clearTime=function (){
		if (typeof(this.timer)!='undefined') clearTimeout(this.timer)
	}
	this.setTimer=function (){
		this.clearTime()
		var t=this.time?this.time:10000
		this.timer=window.setTimeout(this.id+".runStep()",t)
	}
	if (typeof(this.start)=='undefined') this.start=false
	this.id=id
	this.item=0
	this.oBox=document.getElementById('Box_'+id)
	this.oPlay=document.getElementById('Play_'+id)
	this.setTimer()
}