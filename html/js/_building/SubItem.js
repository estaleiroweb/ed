SubItem=function(id){ this.id=id }
SubItem.prototype.showHide=function(obj){ 
	var o=obj.parentElement
	var closed=o.className=='opened'
	o.className=closed?'closed':'opened'
	var med=new MediatorPHPJS
	med.setCookie(this.id,closed)
}
SubItem.prototype.over=function(obj){ obj.className='over' }
SubItem.prototype.out=function(obj){ obj.className='' }

