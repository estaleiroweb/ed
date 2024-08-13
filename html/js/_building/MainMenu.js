function MainMenu(xmlPath,imgPath,systemProgram,systemUser) {
	window.systemUser=systemUser
	window.xmlMenu=new easyMenu(xmlPath+"/db_menu."+systemProgram,imgPath+'/mainMenu');
	window.xmlMenu.offset_x=-7;
	window.xmlMenu.offset_y=3;

	var ret=window.xmlMenu.mount()
	if (ret!==true) document.write("ERRO: "+ret)
}
function easyMenu(url,imgPath){
	//monta uma lista
	this.mount=function(obj){
	   if (!obj) {
	      document.write("<div id='__easyMenu__'></div>");
	      obj=document.getElementById('__easyMenu__');
		}
		if(!this.xml) if(!(this.xml=this.xmlLoad())) return 'Sem XML'; //verifica se xml está na memoria
		if (!obj.xml) { //verifica se xml do objeto ja foi configurado
			obj.xml=this.getObjXml(this.xml,"root");
			if (!obj.xml) return 'XML sem root';
			obj.isChild=true;
			this.mnuRoot=obj;
		}
		if (!obj.isChild) return false;
		if (!obj.easyMenu) obj.easyMenu=this; //verifica se objHtml tem referencia do objeto
		if (!obj.targetChild) obj.targetChild=obj;
		if (!obj.count) obj.count=this.count++;
		var oDiv=document.createElement("div");
		obj.targetChild.appendChild(oDiv);
		oDiv.style.display='none';
		oDiv.style.visibility='hidden';
		oDiv.className='easyMenu';
		var box=(this.getTxtXml(obj.xml,'isbox')=='0')?false:true;
		var oChilds=obj.xml.childNodes;
		var temp;
		var isNotFirst=false;
		var target=oDiv;
		if (box) {
			var oD=document.createElement("table");
			target.appendChild(oD);
		   oD.outerHTML="<table id='easyMenuTop' border='0' cellspacing='0'><tr><td class='left'></td><td class='center'></td><td class='right'></td></tr></table>";
		}
		var isdiv=true; //Tem separador - tofix: capturar o default
		var isfix=false; //A posição é fixa tofix: capturar o default
		var isout=true; //Apaga ao sair do menu - tofix: capturar o default
		var pos=(obj.targetChild.easyMenu_pos)?obj.targetChild.easyMenu_pos:'rt'; // idem
		var direct=(obj.targetChild.easyMenu_direct)?obj.targetChild.easyMenu_direct:'rb'; //idem
		var offset_x=this.offset_x;
		var offset_y=this.offset_y; //idem
		for (var i=0;i<oChilds.length;i++) {
		   switch (oChilds[i].nodeName) {
		      case 'item':
		         if (isNotFirst && isdiv) {
		            temp=document.createElement("div");
		            target.appendChild(temp);
		            temp.id='easyMenu_Sep';
					}
		         this.createItem(oChilds[i],target);
		         isNotFirst=true;
					break;
		      case 'isdiv':case 'isfix':case 'isout':case 'offset_x':case 'offset_y':
		         eval(oChilds[i].nodeName+'=Number(oChilds[i].text)');
		         break;
		      case 'pos':case 'direct':
		         eval(oChilds[i].nodeName+'=oChilds[i].text');
		         break;
		      case 'class':
		         oDiv.className=oChilds[i].text;
		         break;
			}
		}
		if (box) {
			var oD=document.createElement("table");
			target.appendChild(oD);
		   oD.outerHTML="<table id='easyMenuBottom' border='0' cellspacing='0'><tr><td class='left'></td><td class='center'></td><td class='right'></td></tr></table>";
		}
		if (!isfix) {
		   var b=document.body;
		   oDiv.style.position='absolute';
		   oDiv.style.top=0;
		   oDiv.style.left=0;
			oDiv.style.display='';
			//captura posição do objeto


			var obj_x=obj_y=0;
			var rctsObj=obj.getBoundingClientRect();
			if (rctsObj) {
				obj_x=rctsObj.left+b.scrollLeft;
				obj_y=rctsObj.top+b.scrollTop;
			}
			var rctsObj=oDiv.getBoundingClientRect();
			if (rctsObj) {
				div_x=rctsObj.left+b.scrollLeft;
				div_y=rctsObj.top+b.scrollTop;
			}
			var d_x=obj_x-div_x;
			var d_y=obj_y-div_y;
			var obj_w=obj.clientWidth;
			var obj_h=obj.clientHeight;
			var doc_w=b.clientWidth;
			var doc_h=b.clientHeight;
			var div_w=oDiv.scrollWidth;
			var div_h=oDiv.clientHeight;
			var x=0,y=0,r,str,retorno,temp,aTemp,j;
			var aP={//var,pos_inicial,
			   l: ['x',d_x,div_w+d_x,-1,'obj_x+x','<',d_x,offset_x], c: ['x',Math.floor(obj_w/2)+d_x,obj_w+d_x,0,'obj_x+x+div_w','>',doc_w+d_x,offset_x], r: ['x',obj_w+d_x,obj_w+d_x,1,'obj_x+x+div_w','>',doc_w+d_x,offset_x],
				t: ['y',d_y,div_h+d_y,-1,'obj_y+y','<',d_y,offset_y], m: ['y',Math.floor(obj_h/2)+d_y,obj_h+d_y,0,'obj_y+y+div_h','>',doc_h+d_y,offset_y], b: ['y',obj_h+d_y,obj_h+d_y,1,'obj_y+y+div_h','>',doc_h+d_y,offset_y]
			}
			pos=pos.toLowerCase();
			direct=direct.toLowerCase();
			//procura a melhor posição para caixa
			for (var contVer=0;contVer<3;contVer++){
			   retorno=true;
				for (i=0;i<pos.length;i++) {
					str=pos.charAt(i);
					if (typeof(aP[str])!='undefined') eval(aP[str][0]+'='+aP[str][1]);
				}
				for (i=0;i<direct.length;i++) {
					str=direct.charAt(i);
					if (typeof(aP[str])!='undefined') {
					   eval(aP[str][0]+'+='+((aP[str][1]-aP[str][2])+(aP[str][7]*aP[str][3])));
						if (eval(aP[str][4]+aP[str][5]+aP[str][6])) {
							eval(aP[str][0]+'='+(aP[str][6]-(eval(aP[str][4])-eval(aP[str][0]))));
						   retorno=false;
						}
						str=str.replace(/[rc]/,'l').replace(/[bm]/,'t');
						if (eval(aP[str][4]+aP[str][5]+aP[str][6])) {
							eval(aP[str][0]+'='+(aP[str][6]-(eval(aP[str][4])-eval(aP[str][0]))));
							retorno=false;
						}
					}
				}
				if (retorno) break;
				aTemp=[pos,direct];
				for (j=0;j<aTemp.length;j++){
					temp='';
					for (i=0;i<aTemp[j].length;i++) {
					   switch (aTemp[j].charAt(i)){
					      case 'r': temp+='l'; break;
					      case 'l': temp+='r'; break;
					      default: temp+=aTemp[j].charAt(i);
						}
					}
					aTemp[j]=temp;
				}
				pos=aTemp[0];
				direct=aTemp[1];
			}
		   //calcula a posição
		   oDiv.style.top=y;
		   oDiv.style.left=x;
		} else oDiv.style.display='';
		var dest;
		for (i=0;i<obj.targetChild.childNodes;i++){
		   dest=obj.targetChild.childNodes[i].targetChild;
		   dest.easyMenu_pos=pos;
		   dest.easyMenu_direct=direct;
		}
		obj.targetChild.isout=isout;
		oDiv.style.visibility='visible';
		return true;
	}
	// Cria um item
	this.createItem=function(xml,target){
	   if (xml && target) { // verifica xml tem conteudo
	      // cria item de menu
			var oTd,j,i;
			var img=this.getTxtXml(xml,'img');
			if (img) img=this.imgPath+'/'+img;
			var txt=this.getTxtXml(xml,'htext');
			var scr=this.getTxtXml(xml,'script');
			if (scr) txt+=eval(scr);
			var alt=this.getTxtXml(xml,'alt');
			var oTable=document.createElement("table");
			var oTBody=document.createElement("tbody");
			var oTr=document.createElement("tr");
			target.appendChild(oTable);
			oTable.appendChild(oTBody);
			oTBody.appendChild(oTr);

			oTable.url=this.getTxtXml(xml,'url');
			oTable.target=this.getTxtXml(xml,'target');
			oTable.id='easyMenu_item';
			oTable.xmlAlt=(alt)?alt:oTable.url;
			oTable.border='0';
			oTable.count=this.count++;
			oTable.cellSpacing='0';
			if (txt=='-') {
				oTable.className='null';
			} else {
				oTable.easyMenu=this;
				oTable.xml=xml;
				oTable.onmouseover=function () {easyMenu_mouseOver(this)};
				oTable.onmouseout=function () {easyMenu_mouseOut(this)};
				oTable.onclick=function () {easyMenu_mouseClick(this)};
				var t=this.getObjXml(xml,'item');
				oTable.isChild=(t)?true:false;
			}
			var aTr=[
			   {id:'easyMenu_left'},
			   {id:'easyMenu_img',innerHTML:'<div>'+((img && txt!='-')?'<img src="'+img+'" />':'')+'</div>'},
			   {id:'easyMenu_txt',innerHTML:(txt=='-')?'':txt},
			   {id:'easyMenu_a'+((oTable.isChild && txt!='-')?'On':'Off'),innerHTML:'<div></div>'},
			   {id:'easyMenu_right'}
			]
			for (i=0;i<aTr.length;i++) {
			   oTd=document.createElement("td");
			   oTr.appendChild(oTd);
			   for (j in aTr[i]) oTd[j]=aTr[i][j];
			}
			oTable.targetChild=oTd;
			return true;
		} else return false;
	}
	//captura um objeto xml
	this.getObjXml=function(obj,tag){
	   tag=tag.toLowerCase();
		for (var i=0;i<obj.childNodes.length;i++) {
		   if (obj.childNodes[i].nodeName.toLowerCase()==tag) {
		      return obj.childNodes[i];
			}
		}
	}
	//captura um objeto xml
	this.getTxtXml=function(obj,tag){
		var o=this.getObjXml(obj,tag);
		return (o)?o.text:'';
	}
	//carrega um arquivo
	this.xmlLoad=function () {
		if (!this.url) return;
		if (this.execEvent (this.onbeforeload)===false) return;
		var x=new xmlHttpRequest (this.url);
		return x.XmlHttp.responseXML;
	}
	//executa um evento
	this.execEvent=function (e){
		if (e) {
			if (typeof (e)=='string') return eval(e);
			else return e(this);
		}
	}
	//Propriedades:
	this.url=url
	this.xml=null
	this.imgPath=imgPath
	this.count=1
	this.offset_x=0
	this.offset_y=0 //idem
	
	window.easyMenuSuspensos=new Array();
	window.easyMenuDesactive=new Array();
	/*Events:
	onbeforeload
	onload
	*/
}
function easyMenu_mouseOver(obj){
	if (!obj.isActive) {
	   easyMenu_classChange(obj,'over');
	   obj.isActive=true;
	   window.status=obj.xmlAlt;
	   window.activeMenuItem=obj;
		var tam=obj.rows[0].cells.length-1;
		if(obj.rows[0].cells[tam].childNodes.length) obj.rows[0].cells[tam].childNodes[0].style.display='';
		else if (obj.isChild) obj.easyMenu.mount(obj);
	}
}
//mouse out em um menu
function easyMenu_mouseOut(obj){
   if (!obj.contains(event.toElement)) {
		var tam=obj.rows[0].cells.length-1;
		if(obj.rows[0].cells[tam].childNodes.length) obj.rows[0].cells[tam].childNodes[0].style.display='none';
		obj.isActive=false;
	   easyMenu_classChange(obj,'');
	   window.status='';
	}
}
function easyMenu_mouseClick(){
   var obj=window.activeMenuItem;
   window.activeMenuItem=null;
   if (obj && obj.url) {
		if (event.ctrlKey) window.open(obj.url);
		else if (obj.target) window.open(obj.url,obj.target);
      	else location=obj.url;
	}
}
function easyMenu_classChange(obj,classe){
   obj.className=classe;
   obj.rows[0].className=classe;
   for (var i=0;i<obj.rows[0].cells.length;i++) obj.rows[0].cells[i].className=classe;
}