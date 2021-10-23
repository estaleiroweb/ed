;(function ($, window, document, undefined) {
	function ElementUser(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementUser.extends($.Element).jQuery();
})(jQuery, window, document);
