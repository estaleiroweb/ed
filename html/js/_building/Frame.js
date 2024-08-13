//Funções da classe Frame

//Mostra ou apresenta o frame
function FrameOpenClose(obj){
	var cxMain=obj.parentElement.parentElement
	cxMain.className=cxMain.className?'':'closed';
}
