;(function ($, window, document, undefined) {
	function ElementGroupCheck(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementGroupCheck.extends($.Element).jQuery();
	ElementGroupCheck.prototype.init_elements=function() {
		//this.dad('init_elements'); //parents.init_elements();
		parent.this();
		this.elements.selAll=$('.select-group input',this.element);
		this.elements.itens=$('.select-item input',this.element);
		this.elements.$itens=[];
		for(var i=0;i<this.elements.itens.length;i++) this.elements.$itens[i]=$(this.elements.itens[i]);
		return this; 
	}
	ElementGroupCheck.prototype.init_events=function() {
		parent.this();
		var _this=this;
		//console.log(this.elements);
		//this.dad('init_events'); //parents.init_events();
		//console.log($.Element.allObjs);
		this.elements.selAll.click(function (e){ return _this.event_selAll(e,$(this));});
		this.elements.itens.click(function (e){ return _this.event_selItem(e);});
		this.event_selItem();
		return this;
	}
	ElementGroupCheck.prototype.event_selAll=function(e,$o) {
		this.elements.itens.prop('checked',$o.prop('checked'));
	}
	ElementGroupCheck.prototype.event_selItem=function(e) {
		var checked=$('.select-item input:checked',this.element).length;
		var c=false,i=false;
		if(checked){
			if(this.elements.itens.length==checked) c=true;
			else i=true;
		} 
		this.elements.selAll.prop('checked',c);
		this.elements.selAll.prop('indeterminate',i);
	}
})(jQuery, window, document);
