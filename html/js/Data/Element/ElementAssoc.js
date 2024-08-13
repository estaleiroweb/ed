;(function ($, window, document, undefined) {
	function ElementAssoc(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementAssoc.extends($.Element).jQuery();
	ElementAssoc.prototype.getSelected=function(obj){
		var out=new Object();
		return obj.options.length
	}
	ElementAssoc.prototype.select=function(obj){
		//var a=this.getObj('ElementAssoc_Source')
		var a=this.getSelected(obj)
		alert(a)
	}
	ElementAssoc.prototype.addAll=function(obj){}
	ElementAssoc.prototype.add=function(obj){}
	ElementAssoc.prototype.sub=function(obj){}
	ElementAssoc.prototype.subAll=function(obj){}
})(jQuery, window, document);
