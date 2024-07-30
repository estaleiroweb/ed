function DataGraph(id){
	this.graph=function(idx){ //atualiza um grafico
		var oGrp=document.getElementById(this.id+'_img_'+idx)
		oGrp.src=this.getRemoteLine('graph',idx)
		oGrp.width=this.control.grp_x
		oGrp.height=this.control.grp_y
	}
	this.loadXML=function(func,idx,htmlTarget){ //carrega uma imformacao
		var oXml=new xmlHttpRequest()
		oXml.async=true
		oXml.htmlTarget=htmlTarget
		oXml.load(this.getRemoteLine(func,idx))
	}
	this.getRemoteLine=function(func,idx,hideXY){ //monta a linha remota de comando
		var p=[
			'grp_id='+this.id,
			'grp_function='+func,
			'grp_x='+(hideXY?0:this.control.grp_x),
			'grp_y='+(hideXY?0:this.control.grp_y),
			'grp_idGrp='+idx,
			'grp_gTitle='+(hideXY?1:0),
			'grp_resolution='+this.control.grp_resolution,
			'grp_data='+this.control.grp_data
		]
		for(var i in this.active[idx]) p.push('grp_active['+idx+']['+i+']='+this.active[idx][i])
		for(i in this.request) p.push(i+'='+escape(this.request[i]))
		return this.file+'?'+p.join('&')
	}
	this.graphCk=function(obj,idx,fldName){ //marca ou desmarca um item do grafico
		this.active[idx][fldName]=Number(obj.checked)
		this.graph(idx)
		this.changeInfo(idx)
	}
	this.changeShowInfo=function(){// atualiza, ativa ou desativa todas as infos
		var oAvg
		for (var idx in this.active) this.changeInfo(idx)
		this.changeGraph()
	}
	this.changeInfo=function(idx){// atualiza, ativa ou desativa todas as infos
		var oAvg=document.getElementById(this.id+'_avg_'+idx)
		oAvg.style.display=this.control.grp_showAvg?'':'none'
		if (this.control.grp_showAvg) this.loadXML('info',idx,oAvg)
	}
	this.changeGraph=function(){ //atualiza todos os graficos
		for (var idx in this.active) this.graph(idx)
	}
	this.changeResolution=function(obj){
		this.control.grp_resolution=obj.value
		this.changeAll()
	}
	this.changeSize=function(obj){
		var coor=obj.value.split(',')
		this.control.grp_x=coor[0]
		this.control.grp_y=coor[1]
		this.changeAll()
	}
	this.showHtml=function(idx){
		window.open(this.getRemoteLine('html',idx))
	}
	this.zoomImg=function(idx){
		window.open(this.getRemoteLine('graph',idx,true))
	}
	this.changeDate=function(cal){
		//var obj=eval(cal.params.inputField.id.replace(/_.*$/,''))
//alert(this.id);cal.hide();return
		if (this.control.grp_showDateRfs) {
			var oRdt=document.getElementById(this.id+'_rdt')
			if (oRdt.checked) window.setTimeout("document.getElementById('"+this.id+"_rdt').checked=true",600000)
			oRdt.checked=false
		}
		this.control.grp_data=cal.params.inputField.value
		cal.hide()
		this.changeAll()
	}
	this.changeTitle=function(){
		if(this.control.grp_cTitle) this.loadXML('title',0,document.getElementById(this.id+'_ttl'))
	}
	this.changeAll=function(){
		this.timeCount=0
		this.changeTitle()
		this.changeGraph()
		this.changeShowInfo()
	}
	this.autoRefresh=function(force){
		var oRdt=document.getElementById(this.id+'_rdt')
		this.timeCount++
		if(oRdt.checked && (this.timeCount>this.control.grp_refresh || force)) {
			this.refreshDate()
			return true
		}else return false
	}
	this.refreshDate=function(){
		if(typeof(this.control.grp_dateId)=='undefined') return
		var strData=this.timeStamp(new Date())
		this.control.grp_data=strData
		if (this.control.grp_dateId) {
			eval(this.control.grp_dateId+".setDateByTimeStamp(strData)")
			this.changeAll()
		}
	}
	this.pad2=function (texto){
		texto='00'+texto
		return texto.substr(texto.length-2)
	}
	this.timeStamp=function(dt){
		return dt.getFullYear()+'-'+this.pad2(dt.getMonth()+1)+'-'+this.pad2(dt.getDate())+' '+this.pad2(dt.getHours())+':'+this.pad2(dt.getMinutes())+':'+this.pad2(dt.getSeconds())
	}
	this.id=id
	this.timeCount=0
	this.interval=window.setInterval('o'+this.id+".autoRefresh()",1000)
	this.control=new Object()
	this.active=new Object()
	this.elemen=new Object()
	this.request=new Object()
}