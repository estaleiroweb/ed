;(function ($, window, document, undefined) {
	function ElementString(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementString.extends($.Element).jQuery();
})(jQuery, window, document);
