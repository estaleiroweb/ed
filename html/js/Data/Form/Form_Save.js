function fRec(){
	if (window.formReferer) window[window.formReferer].save()
}