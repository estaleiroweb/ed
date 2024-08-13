function Layout() {
	var d=document.getElementById
	if (d('Layout_header').style.display=='none')	{
		d('Layout_header').style.display=''
		d('Layout_main').style.paddingTop=30
		var out=true
	} else {
		d('Layout_header').style.display='none'
		d('Layout_main').style.paddingTop=0
		var out=false
	}
	if (typeof (navBar)!='undefined') navBar.hideShowEnd(out)
	var m=new MediatorPHPJS
	m.setCookie('Layout_header',out)
}