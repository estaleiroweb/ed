;(function ($, window, document, undefined) {
	function ElementButton(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementButton.extends($.Element).jQuery();
})(jQuery, window, document);
