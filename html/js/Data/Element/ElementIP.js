;(function ($, window, document, undefined) {
	function ElementIP(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementIP.extends($.Element).jQuery();
})(jQuery, window, document);


/*ElementIP.prototype.init_elements=function() {
	parent.this();
	this.settings.validate=this.validate;
	return this; 
}*/
ElementIP.prototype.init_events=function() {
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
ElementIP.prototype.autoFormat=function(obj,e) {
	obj.lastEvent=e.type;
	this.onfocus(obj,e,true);
	if(obj.value=='') return;

	obj.value=obj.value.replace(/\.{2,}/g,'.');
	var ret=obj.value.split('.')
	var out=[]
	for (var i=0;i<ret.length;i++) out.push(Number(ret[i]));
	obj.value=out.join('.')
}
ElementIP.prototype.onfocus=function(obj,e,notSelect) {
	obj.lastEvent=e.type
	obj.value=obj.value.replace(/[^0-9\.]/ig,'')
	if(notSelect) return
	obj.select()
}
ElementIP.prototype.keypress=function(obj,e){ 
	obj.lastEvent=e.type
	var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
	if ((/[0-9\.]/i).test(String.fromCharCode(key))) return;
	e.returnValue=false 
	return false;
}
ElementIP.prototype.keyup=function(obj,e){ 
	var t=obj.lastEvent
	var maxsize=Number(this.element.attr('maxlength') || this.element.attr('size'));
	obj.lastEvent=e.type
	if (t!='focus' && obj.value.length>=maxsize) obj.blur() 
}
ElementIP.prototype.check=function(val){
	//$.Element.call(this,element, options);
	var error=false;
	var out=[];
	var ret=val.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
	if(ret) for(var i=1;i<ret.length;i++) {
		var n=Number(ret[i]);
		if(n<0 || n>255) { 
			n='???';
			error=true;
		}
		out.push(n);
	} else return 'Formato de IPv4 incorreto <0.0.0.0>';
	if(error) return 'IPv4 não reconhecido: '+(out.join('.'));
};
