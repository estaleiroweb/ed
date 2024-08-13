/*
eval('ElementCombo='+(new Element).constructor.toString())
ElementCombo.prototype=new Element()
ElementCombo.prototype.constructor=ElementCombo

ElementCombo.prototype.setValue=function(value){
	var objInput=this.getObjInput()
	var objDisplay=this.getObjDisplay()
	if (objInput) objInput.value=value
	if (objDisplay) {
		if (objDisplay.disabled || objDisplay.readOnly) {
			objDisplay.value=typeof(this.opts[value])=='string'?this.opts[value]:value
		} else objDisplay.value=value
	}
	this.onchange()
}
*/
;(function ($, window, document, undefined) {
	function ElementCombo(element, options) {
		$.Element.call(this,element, options); // call super constructor.
		//OtherSuperClass.call(this);
	}
	ElementCombo.extends($.Element).jQuery();
	/*
	//ElementCombo.defaults={'eu':'tu'};
	//ElementCombo.defaults=$.extend({}, Element.defaults, options);
	ElementCombo.defaults=$.extend({}, $.Element.defaults, {
		'valid': null
	});
	
	ElementCombo.prototype.init=function(){
		console.log([this.name,this.getValue(),this.getDspValue()]);
		//this.getObjDisplay().css('border','1px solid #F00');
		this.getObjDisplay().addClass('has-error');
		return this; 
	};
	ElementCombo.prototype.init_elements=function() {
		var _this=this;
		this.id=this.element.attr('id');
		this.form=$(this.element[0].form);
		this.form_name=this.element.attr('name');
		if(this.form.length==0) this.form=$('body');		
		return this; 
		//this.elements={ //find Elements
		//	form:$(this.element.find('input')[0].form),
		//	clearFilter: this.element.find('[ed-item="uploadlist-clear-filter"]'),
		//}
		//this.addElements=function(selector,element_name,base)
	}
	ElementCombo.prototype.init_events=function() {
		var _this=this;
		return this;
		//this.elements.form.submit(function(e){ //Set Events
		//	var ch=_this.element.find('[ed-item="uploadlist-remove-check"]:checked');
		//	var len=ch.length;
		//	if(len!=0 && (
		//		!confirm('Voce está prestes a excluir '+len+' arquivo(s).\nDeseja realmente fazer isto?') ||
		//		!confirm('Tem certeza desta opeação?')
		//	)) e.preventDefault();
		//});
	}
	*/
})(jQuery, window, document);
