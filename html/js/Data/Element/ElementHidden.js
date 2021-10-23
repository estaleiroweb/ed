;(function ($, window, document, undefined) {
	function ElementHidden(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementHidden.extends($.Element).jQuery();
})(jQuery, window, document);
