;(function ($, window, document, undefined) {
	function ElementRangeIP(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementRangeIP.extends($.Element).jQuery();
})(jQuery, window, document);


/*ElementRangeIP.prototype.init_elements=function() {
	parent.this();
	this.settings.validate=this.validate;
	return this; 
}*/
ElementRangeIP.prototype.init_events=function() {
	parent.this();
	var _this=this;
	this.element.blur(function(e){
		return _this.autoFormat(this,e);
	}).focus(function(e){
		return _this.onfocus(this,e);
	}).keypress(function(e){
		return _this.keypress(this,e);
	}).keyup(function(e){
		return _this.keyup(this,e);
	});
}
ElementRangeIP.prototype.autoFormat=function(obj,e) {
	obj.lastEvent=e.type;
	this.onfocus(obj,e,true);
	if(obj.value=='') return;

	var v=obj.value.replace(/\.{2,}/g,'.').replace(/\/{2,}/g,'/');
	var ret=v.match(/^(.*?)(?:\/(\d*))?$/)
	if(!ret) return v;
	var m=ret[2]===undefined?'':'/'+Number(ret[2]);
	v=ret[1].split(/[\.\/]/);
	var out=[]
	for (var i=0;i<v.length;i++) out.push(Number(v[i]));
	obj.value=out.join('.')+m;
}
ElementRangeIP.prototype.onfocus=function(obj,e,notSelect) {
	obj.lastEvent=e.type
	obj.value=obj.value.replace(/[^0-9\/\.]/ig,'')
	if(notSelect) return
	obj.select()
}
ElementRangeIP.prototype.keypress=function(obj,e){ 
	obj.lastEvent=e.type
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if ((/[0-9\/\.]/i).test(String.fromCharCode(key))) return;
	e.returnValue=false;
	return false;
}
ElementRangeIP.prototype.keyup=function(obj,e){ 
	var t=obj.lastEvent
	var maxsize=Number(this.element.attr('maxlength') || this.element.attr('size'));
	obj.lastEvent=e.type
	if (t!='focus' && obj.value.length>=maxsize) obj.blur() 
}
ElementRangeIP.prototype.check=function(val,obj){
	//$.Element.call(this,element, options);
	var error=false;
	var out='';
	var ret=val.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/(\d{1,2})$/);
	if(ret) {
		for(var i=1;i<ret.length;i++) {
			var n=Number(ret[i]);
			var min=0;
			var max=255;
			var s='.';
			if(i==5) { min=1; max=32; s='/'; }
			else if(i==1) { min=1; s=''; }
			if(n<min || n>max) { 
				n='?';
				error=true;
			}
			out+=s+n;
		}
	} else return 'Formato de Range IPv4 incorreto <0.0.0.0/0>';
	if(error) return 'IPv4 não reconhecido: '+out;
	else {
		var ip=obj.element.attr('nwtype');
		if(ip=='' || ip==undefined || ip==null) return;
		var m=Number(ret[5]);
		var ipDec=val.INET_ATON();
		var net=ipDec.INET_ipNet(m);
		var BCast=ipDec.INET_ipBCast(m);
		if(ip.match(/^\s*net(work)?\s*$/i)) {
			if(ipDec!==net) return 'Deve ser Net: '+net.INET_NTOA();
		} else if(ip.match(/^\s*b(road)?cast\s*$/i)) {
			if(ipDec!==BCast) return 'Deve ser BCast: '+BCast.INET_NTOA();
		} else{
			var err='';
			if(ipDec==net) err='Net';
			else if(ipDec==BCast) err='BCast';
			else return;
			return 'IP não pode ser '+err+': >'+net.INET_NTOA()+' e < '+BCast.INET_NTOA();
		}
	}
};
