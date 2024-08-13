;(function ($, window, document, undefined) {
	function ElementPasswd(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementPasswd.extends($.Element).jQuery();

	ElementPasswd.prototype.objConfirm=null;
	ElementPasswd.prototype.getObjConfirm=function(){
		if(!this.objConfirm) this.objConfirm=document.getElementById(this.preIdDisplay+this.id+this.idSub)
		return this.objConfirm
	}
})(jQuery, window, document);