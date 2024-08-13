eval('DataTab='+(new Data).constructor.toString())
DataTab.prototype=new Data()
DataTab.prototype.constructor=DataTab

DataTab.prototype.objType='DataTab' 

DataTab.prototype.inicialize=function(){ 
	if (this.isStartVariables) return true
	this.isStartVariables=this.initVars({tabBar:'_tabs',dataActive:'_active'})
	return this.isStartVariables
}
DataTab.prototype.swapTab=function(tab){
	if(!this.inicialize()) return
	if (this.active==tab) {
		if (DataElements[this.active] && DataElements[this.active].objType=='DataList') DataElements[this.active].showTable.value=Math.abs(Number(DataElements[this.active].showTable.value)-1)
		else return
	} else this.dataActive.value=tab
	//if (tblData_obj[this.active] && tblData_obj[this.active].objType=='tblDataList') this.submit()
	this.submit()
}
Data.prototype.over=function(obj){ if (obj.className=='desativ') obj.className='hover' }
Data.prototype.out=function(obj){ if (obj.className=='hover') obj.className='desativ' }