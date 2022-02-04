function Mouse() {
	this.setClass=false
	this.classNameOut=false
	this.classNameOver='mOver'
	this.classNameSelectOut='mSelectOut'
	this.classNameSelectOver='mSelectOver'
	this.bgColorOut=false
	this.bgColorOver="#EEEEFF"
	this.bgColorSelect="#CCCCDD"
	this.bgColorSelectOver="#BBBBCC"
	this.select=false
	this.onselect=''
	this.over=function (obj){
		if (obj==this.select) this.change(obj,'SelectOver')
		else this.change(obj,'Over')
	}
	this.out=function (obj){
		if (obj==this.select) this.change(obj,'SelectOut')
		else this.change(obj,'Out')
	}
	this.click=function (obj){
		var lastSel=this.select
		this.select=obj
		if (lastSel) this.out(lastSel)
		this.over(obj)
		this.execute(this.onselect,obj)
	}
	this.change=function (obj,status){
		var param=this.setClass?'className':'bgColor'
		if (typeof(obj.classNameOld)=='undefined') obj.classNameOld=obj.className
		if (typeof(obj.bgColorOld)=='undefined') obj.bgColorOld=obj.bgColor

		obj[param]=this[param+status]===false?obj[param+'Old']:this[param+status]
		
//	  alert([v,param])
	}
	this.execute=function (onCmd,param){
		if (!onCmd) return
		if (typeof(onCmd)=='string') eval(onCmd)
		onCmd(param)
	}
}