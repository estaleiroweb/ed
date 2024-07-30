//Mostra ou apresenta o box
function BoxShowHidden(obj){
	var oTbl=obj.parentElement.parentElement.parentElement
	if (typeof(oTbl.hiddenStatus)=='undefined') oTbl.hiddenStatus=false
	oTbl.hiddenStatus=!oTbl.hiddenStatus
	var c
	var st=oTbl.hiddenStatus?'none':''
	
	for (var tRow=0; tRow<oTbl.rows.length; tRow++) {
		for (var tCell=0; tCell<oTbl.rows[tRow].cells.length; tCell++){
			c=oTbl.rows[tRow].cells[tCell]
			if (!(/Box_neverHidden/).test(c.className)) {
				c.style.display=st
			}else{
				if (oTbl.hiddenStatus) {
					c.w=c.width
					c.h=c.height
					c.width=c.clientWidth
					c.height=c.clientHeight
				}else{
					c.width=c.w
					c.height=c.h
				}
			}
		}
	}
}

function makeBox(content) {
	return	"<table border='0' cellspacing='0' class='Box' >"+
			"<tr><td class='Box_tl'><div>&nbsp;</div></td><td class='Box_tc'><div>&nbsp;</div></td><td class='Box_tr'><div>&nbsp;</div></td></tr>"+
			"<tr><td class='Box_ml'><div>&nbsp;</div></td><td class='Box_mc'>"+content+"</td><td class='Box_mr'><div>&nbsp;</div></td></tr>"+
			"<tr><td class='Box_bl'><div>&nbsp;</div></td><td class='Box_bc'><div>&nbsp;</div></td><td class='Box_br'><div>&nbsp;</div></td></tr>"+
			"</table>"
}