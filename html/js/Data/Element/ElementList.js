;(function ($, window, document, undefined) {
	function ElementList(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementList.extends($.Element).jQuery();
})(jQuery, window, document);
