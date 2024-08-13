//function fRfr(){}function fUrl(){}function fFlt(){}function fCnf(){}function fHlp(){}function fMsg(){}function fNav(){}function fNew(){}function fEdt(){}function fRec(){}

function NavBar() {
	//if(!(/MSIE/i).test(navigator.userAgent)) return
	navBar=new NavBar_object
	var botoes=navBar.getMiddleButton();
	if (botoes) {
		var m=new MediatorPHPJS
		var navShow=m.getCookie('NavBar_buttons',true)
		var mainMenu=m.getCookie('Layout_header',true)
		var NavBar_home		=navBar.getButton('NavBar_hom')
		var NavBar_buttons	="<div id='NavBar_buttons'"+(navShow?'':" style='display:none'")+">"+botoes+"</div>"
		var NavBar_margin	=mainMenu?0:navBar.endWidth
		var out="<div id='NavBar' style='margin-right:"+NavBar_margin+"px;'>"+NavBar_home+NavBar_buttons+"</div>"
		document.write(out)
	}
}
function NavBar_object() {
	this.endWidth=8
	this.fn={ //function: [id,title]
/*
fViw
fDel
*/
		NavBar_bck	: ['fBck','Volta para tela anterior (Voltar)'],
		NavBar_fwd	: ['fFwd','Avança para tela posterior (Avançar)'],
		NavBar_rfr	: ['fRfr','Tempo restante para o refresh automático (Refresh)'],
		NavBar_mnu	: ['Layout','Apresenta ou Oculta Todo o Cabeçalho \nJunto com a Barra de Menu (Menu)'],
		NavBar_url	: ['fUrl','Copia o endereço completo desta página para memória \n\"use edit-paste ou ctrl-v para colar\" (URL Copy)'],
		NavBar_flt	: ['fFlt','Apresenta ou Oculta a caixo de filtro e as guias (Filter)'],
		NavBar_cnf	: ['fCnf','Configurações de Campos de Navegação (Config)'],
		NavBar_hlp	: ['fHlp','Ajuda do BdConfig (Help)'],
		NavBar_msg	: ['fMsg','Mensageiro do BdConfig (Messeger)'],
		NavBar_nav	: ['fNav','Vai para a Tela de Navegação (Navigator)'],
		NavBar_new	: ['fNew','Cria um Novo registro (New)'],
		NavBar_viw	: ['fViw','Visualiza o Registro Ativo (View)'],
		NavBar_edt	: ['fEdt','Edita o Registro Ativo (Edit)'],
		NavBar_del	: ['fDel','Apaga o Registro Ativo (Delete)'],
		NavBar_rec	: ['fRec','Grava o Registro Ativo (Record)'],
		NavBar_hom	: ['NavBar_hideShowButtons','Mostra/Oculta esta Barra de Serviços'],
		NavBar_end	: ['NavBar_hideShowButtons','Mostra/Oculta esta Barra de Serviços']
	}
	this.getMiddleButton=function (){
		var b=''
		for (var i in this.fn) {
			if (i!='NavBar_hom' && i!='NavBar_end' && typeof (window[this.fn[i][0]])=='function') b+=this.getButton(i) 
		}
		if (b) b+=this.getButton('NavBar_end')
		return b
	}
	this.getButton=function (button){
		return "<div id='"+button+"' onclick='navBar.click(this)' onmouseover='navBar.over(this)' onmouseout='navBar.out(this)'></div>";
	}
	this.hideShowEnd=function (navStatus){
		document.getElementById('NavBar').style.marginRight=navStatus?0:this.endWidth
	}
	this.click=function(obj){
		window[this.fn[obj.id][0]]()
	}
	this.over=function(obj){
		obj.className='over'
		this.help(obj)
	}
	this.out=function(obj){
		obj.className=''
		window.defaultStatus=''
	}
	this.help=function(obj){
		obj.title=window.defaultStatus=this.fn[obj.id][1]
	}
}
function NavBar_hideShowButtons (){
	var navButtons=document.getElementById('NavBar_buttons')
	var m=new MediatorPHPJS
	if (navButtons.style.display) {
		navButtons.style.display=''
		m.setCookie('NavBar_buttons',true)
	} else {
		navButtons.style.display='none'
		m.setCookie('NavBar_buttons',false)
	}
}