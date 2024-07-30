;(function ($, window, document, undefined) {
	function ElementFile(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementFile.extends($.Element).jQuery();
})(jQuery, window, document);
