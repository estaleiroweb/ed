;(function ($, window, document, undefined) {
	function ElementCheck(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementCheck.extends($.Element).jQuery();
	ElementCheck.defaults=$.extend({}, $.Element.defaults, {
		'checked': false
	});

	//ElementCheck.prototype.init=function(){
		//$.Element.call(this);
	//};
	ElementCheck.prototype.init_elements=function() {
		this.elements={
			input:this.element.find('input[type="hidden"]'),
			check:this.element.find('input[type="checkbox"]')
		};
		return this;
		/*
		var _this=this;
		{//find Elements
			this.elements={
				form:$(this.element.find('input')[0].form),
				clearFilter: this.element.find('[ed-item="uploadlist-clear-filter"]'),
			}
			}*/
		//this.addElements=function(selector,element_name,base)
	}
	ElementCheck.prototype.init_events=function() {
		//console.log(this.elements);
		var _this=this;
		this.element.click(function(e){
			_this.elements.input.val(_this.elements.check[0].checked?1:0);
			//console.log(_this.elements.check.checked);
		});
	}
	ElementCheck.prototype.getId=function(){
		return this.element.find('input[type="checkbox"]').attr('id');
	}
	ElementCheck.prototype.getValue=function(){
		return this.element[0].checked;
	};
	ElementCheck.prototype.setValue=function(v){
		v=v.cast(true);
		this.element[0].checked=v;
		this.elements.input.val(v?1:0);
		return this;
	};
})(jQuery, window, document);
