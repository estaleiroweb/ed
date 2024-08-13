;(function ($, window, document, undefined) {
	function ElementCn(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementCn.extends($.Element).jQuery();
})(jQuery, window, document);
