;(function ($, window, document, undefined) {
	function ElementImage(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementImage.extends($.Element).jQuery();
	ElementImage.prototype.click=function(obj){
		var imgPath=obj.src.match(/img=(\/.*)$/)
		window.open(imgPath[1])
	}
})(jQuery, window, document);
