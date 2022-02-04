/**
 * Captura o navegador corrente
 * Properties:
 * @parm name String Retorna o nome do browser
 * @parm nickName String Retorna o apelido do browser com 2 letras em maiúsculo
 * @parm fullName String Retorna o nome completo do browser
 * @parm version String Retorna a versão do browser
 * @parm os String Retorna o sistema operacional
 * @parm distribution String Retorna a distribuição do sistema operacional
 * @parm osVersion String Retorna a versão do sistema operacional
 * @parm isIE boolean Retorna true se for Internet Explorer
 * @parm isGC boolean Retorna true se for Google Chrome
 * @parm isFF boolean Retorna true se for Mozilla Firefox
 * @parm isAS boolean Retorna true se for Apple Safari
 * @parm isNS boolean Retorna true se for Netscape
 * @parm isMZ boolean Retorna true se for Mozilla
 **/
function getNavigator(){
	this.name=this.nickName=this.fullName=this.version=this.os=this.distribution=this.osVersion=''
	this.isIE=this.isGC=this.isFF=this.isAS=this.isNS=this.isMZ=this.isKQ=false
	var browsers={
		'MSIE':['IE','Microsoft Internet Explorer'],
		'Chrome': ['GC','Google Chrome'],
		'Firefox': ['FF','Mozilla Firefox'],
		'Safari':['AS','Apple Safari'],
		'Netscape':['NS','Netscape'],
		'Mozilla':['MZ','Mozilla'],
		'Konqueror':['KQ','Mozilla Konqueror']
	}
	//Captura Browser
	var ret
	(ret=navigator.userAgent.match(/(MSIE|Chrome|Firefox|Safari|Netscape|Konqueror)(?:\/| )([\.0-9]*)/i)) || (ret=navigator.userAgent.match(/(Mozilla)(?:\/| )([\.0-9]*)/i))
	if(ret) {
		this.name=ret[1]
		this.version=ret[2]
	}
	if(typeof(browsers[this.name])!='undenided') {
		this.nickName=browsers[this.name][0]
		this.fullName=browsers[this.name][1]
		this[browsers[this.name][0]]=true
	}
	//Sistema Operacional
	ret=navigator.userAgent.match(/(Windows|Linux|Mac)/i)
	if(ret) {
		this.os=ret[1]
		ret=navigator.userAgent.match(/(NT|(?:Open)?SuSE|Red\s*Hat|Debian|Slackware|Fedora|Scratch|GoboLinux|CentOS|Mandrake|Guaranix|Gentoo|Foresight|Tutoo|Mandriva|Conectiva|Kurumin|Freedows|Knoppix|(?:[KX]?U|Flux|Goo?)?buntu|PCLinuxOS|Satux)(?:\/| |-)?([-\.0-9a-z_]*)/i)
		if(ret){
			this.distribution=ret[0]
			this.osVersion=ret[1]
		}
	}
	
	/**
	  * @return String Texto contendo informações sobre o Navegador
	  */
	this.showNavigator=function(){
		return 'userAgent: ' + navigator.userAgent+'<br>\n'+
		'appName: ' + navigator.appName+'<br>\n'+
		'appVersion: ' + navigator.appVersion+'<br>\n'
	}
	/**
	  * @return String Texto contendo informações sobre o Browsers Suportados
	  */
	this.showBrowsers=function(){
		var out=''
		for(var i in browsers) out+=i+': '+browsers[i][0]+'=>'+browsers[i][1]+'<br>\n'
		return out
	}
}