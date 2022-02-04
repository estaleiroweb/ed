function MediatorPHPJS(){
	this.tmp
	this.execCmd='/fn/execCmd.php'
	this.sCookie=''
	this.oCookieEncode=new Object()
	this.oCookie=new Object()
	
	this.resetCookiesValues=function (){
		this.sCookie=document.cookie
		this.oCookieEncode=new Object()
		this.oCookie=new Object()
		if (!this.sCookie) return
		var out=this.sCookie.split(/; */)
		var outDiv
		for (var i=0;i<out.length;i++) 	{
			outDiv=out[i].split('=')
			this.oCookieEncode[outDiv[0]]=outDiv[1]
		}
	}
	this.setCookie=function (name, value,expires,path,domain,secure) {
		this.oCookie[name]=value
		document.cookie = name + "=" + this.urlencode(this.serialize(value)) +
		((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
		((path == null) ? "" : ("; path=" + path)) +
		((domain == null) ? "" : ("; domain=" + domain)) +
		((secure == true) ? "; secure" : "")
	}
	this.getCookie=function (coo,cookieValue){
		if (this.sCookie!=document.cookie) this.resetCookiesValues()
		if (typeof(this.oCookie[coo])=='undefined') {
			this.oCookie[coo]=typeof(this.oCookieEncode[coo])=='undefined'?cookieValue:this.unserialize(this.urldecode(this.oCookieEncode[coo]))
		}
		return this.oCookie[coo]
	}
	this.getCookies=function (){
		this.resetCookiesValues()
		for (var coo in this.oCookieEncode) this.oCookie[coo]=this.unserialize(this.urldecode(this.oCookieEncode[coo]))
		return this.oCookie
	}
	this.exec=function(cmd){
		var oHttp=new xmlHttpRequest(this.getCookie('URLEASY','')+this.execCmd+'?PHPSESSID='+this.getCookie('PHPSESSID',''),cmd,false)
		return oHttp.getText()
	}
	this.execPHP=function(cmd){
		return this.exec({cmd:cmd})
	}
	this.session=function(sess){
		if(typeof(sess)!='object') return
		var cmd=new Object()
		var s=this.clone(sess)
		if (s) {
			s['cmd']='__session__'
			for (key in s) cmd[key]=this.serialize(s[key])
		}
		this.exec(cmd)
	}
	this.setSession=function(sess){
		this.session(sess)
	}
	this.getSession=function(sess){
		var vSession=this.clone(sess)
		var cmd=''
		var v
		if(typeof(vSession)=='string') {
			v=vSession.split(/\s*[,;]\s*/)
			if(v.length==1) cmd='print serialize(@$_SESSION[\''+vSession+'\']);'
			else vSession=v
		}
		if(typeof(vSession)=='object') {
			//var isArray=(String(vSession.constructor).match(/^\s*function\s+(\w+)/)[1]=='Array')
			var isArray=(vSession instanceof Array)
			for(var i in vSession) {
				v=isArray?vSession[i]:i
				if(!v) continue
				cmd+='\t\''+v+'\'=>@$_SESSION[\''+v+'\'],\n'
			}
			cmd='print serialize(array(\n'+cmd+'));'
		}
		if (!cmd) return 
		return this.unserialize(this.execPHP(cmd))
	}
	this.urldecode=function (value){
		var r,er
		value=value.replace(/\+/g ,' ').replace(/%25/g ,'%')
		while (r=value.match(/%([89A-F].)/)) {
			er=new RegExp(r[0],"g")
			value=value.replace(er,String.fromCharCode(parseInt(r[1],16)))
		}
		return decodeURIComponent(value)
	}
	this.urlencode=function (value){
		if ((/[\x80-\xFF]/ ).test(value)) {
			var er=/[\x00-\x24\x26-\x2C\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\xFF]/
			value=value.replace(/%/g,'%25')
		} else {
			var er=/[!\'-\*~]/
			value=encodeURIComponent(value)
		}
		var c
		while (r=value.match(er)) {
			c=r[0].charCodeAt().toString(16).toUpperCase()
			value=value.replace(r[0],'%'+(c.length==1?'0':'')+c)
		}
		return value //.replace(/%20/g ,'+')
		//return value.replace(/%20/g ,'+')
	}
	this.serialize=function (value){
		var tipo=typeof(value)
		if (tipo=='undefined') return 'N;'
		else if (tipo=='nan') return 'N;'
		else if (value==null) return 'N;'
		tipo=String(String(value.constructor).replace(/^\s*function\s+/i,'').match(/\w+/))
		if (tipo=='Boolean') return 'b:'+(value?1:0)+";"
		if (tipo=='String') return 's:'+value.length+':"'+value+'";'
		if (tipo=='Number') return (Math.floor(value)==value?'i':'d')+':'+value+';'
		if (tipo=='Function') return ''
		if (tipo=='Array') {
			var tam=value.length
			var out='a:'+tam+':{'
			for (var i=0;i<tam;i++) out+=this.serialize(i)+this.serialize(value[i])
			return out+'}'
		}
		if (tipo=='Object') {
			var out='',tmp='',tam=0
			for (var i in value) if (tmp=this.serialize(value[i])) {
				tam++
				out+=this.serialize(i)+tmp
			}
			return 'a:'+tam+':{'+out+'}'
		}
		var out='',tmp='',tam=0
		for (var i in value) if (tmp=this.serialize(value[i])) {
			tam++
			out+=this.serialize(i)+tmp
		}
		return 'O:'+tipo.length+':"'+tipo+'":'+tam+':{'+out+'}'
	}
	this.unserialize=function (value){
		if (typeof(value)=='undefined') return
		var item=value.substr(0,2)
		if (item=='N;') { //N;
			this.tmp=value.substr(2)
			return null
		}
		if (item=='b:') { //b:1;
			var out=value.match(/^b\:([\d\.]+);/)
			this.tmp=value.substr(out[0].length)
			return Number(out[1])==0?false:true
		}
		if (item=='i:' || item=='d:') { //i:1;    |    d:1.1;
			var out=value.match(/^[id]\:([\d\.]+);/)
			this.tmp=value.substr(out[0].length)
			return Number(out[1])
		}
		if (item=='s:') { //s:1:"1";
			var out=value.match(/^s\:(\d+)\:/)
			var tam=Number(out[1])
			this.tmp=value.substr(out[0].length+tam+3)
			return value.substr(out[0].length+1,tam)
		}
		if (item=='a:') { //a:1:{i:0;s:1:"1";}    |    a:1:{s:3:"xxx";s:1:"1";}
			var out=value.match(/^a\:(\d+)\:\{/)
			var tam=Number(out[1])
			this.tmp=value.substr(out[0].length)
			var obj=new Array()
			for (var i=0;i<tam;i++) obj[this.unserialize(this.tmp)]=this.unserialize(this.tmp)
			this.tmp=value.substr(1)
			return obj
		}
		if (item=='O:') { //O:7:"Cookies":1:{s:7:"cookies";s:61:"PHPSESSID=14vb2kemegkussargemoh74hn2; Layout_header=Boolean&0";}
			var out=value.match(/^O\:(\d+)\:\"/)
			var tam=Number(out[1])
			var objName=value.substr(out[0].length,tam)
			value=value.substr(out[0].length+tam+2)
			out=value.match(/^(\d+)\:\{/)
			tam=Number(out[1])
			this.tmp=value.substr(out[0].length)
			var obj=eval('new '+objName+'()')
			for (var i=0;i<tam;i++) obj[this.unserialize(this.tmp)]=this.unserialize(this.tmp)
			this.tmp=value.substr(1)
			return obj
		}
		var oIni=false
		var obj=new Array()
		while (item=value.match(/^(\w+)\|(?:[bisaO]\:|N;)/)) {
			oIni=true
			this.tmp=value.substr(item[1].length+1)
			obj[item[1]]=this.unserialize(this.tmp)
		}
		return oIni?obj:value
	}
	this.clone=function(obj) {
		if(typeof(obj)!='object') return obj
		var newObj = (obj instanceof Array) ? [] : {}
		for (i in obj) {
			if (obj[i] && typeof obj[i] == 'object') newObj[i] = this.clone(obj[i])
			else newObj[i] = obj[i]
		}
		return newObj
	}
}
