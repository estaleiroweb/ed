;(function ($, window, document, undefined) {
	function EForm(element, options) {
		parent.this(element, options);
	}
	EForm.extends($.Ed).jQuery();

	EForm.prototype.actions={
		save:5,
		insert:4, /*C*/
		view:3,   /*R*/
		edit:2,   /*U*/
		del:1     /*D*/
	}
	/*EForm.prototype.init=function(){
		parent.this();
		return this; 
	};*/
	EForm.prototype.init_elements=function() {
		var idForm=this.element.attr('ed-form-id');
		var $action=$('#frm_action_'+idForm);
		this.elements={
			idForm:idForm,
			form:$($action[0].form),
			action: $action,
			hash: $('#frm_hash_'+idForm),
			oldWhere: $('#frm_oldWhere_'+idForm),
			buttonNav: $('[ed-method="nav"]',this.element),
			buttonInsert: $('[ed-method="insert"]',this.element),
			buttonView: $('[ed-method="view"]',this.element),
			buttonEdit: $('[ed-method="edit"]',this.element),
			buttonDel: $('[ed-method="del"]',this.element),
			buttonSave: $('[ed-method="save"]',this.element),
			errorMess: $('[ed-method="mess"]',this.element),
			itens:$('[ed-form-id="'+idForm+'"]'),
		}
		//console.log(this.elements)
		return this; 	
	}
	EForm.prototype.init_events=function() {
		//return this;
		var _this=this;
		this.elements.buttonNav.click(function(e){
			var href=$(this).attr('ed-href');
			if(href) location=href;
			return false;
		});
		this.elements.buttonInsert.click(function(e){
			return _this.setAction(_this.bin('insert'));
		});
		//this.elements.buttonNav.hover(function(e){ _this.validate(_this.elements.itens); });
		this.elements.buttonView.click(function(e){
			return _this.setAction(0);
		});
		this.elements.buttonEdit.click(function(e){
			return _this.setAction(_this.bin('edit'));
		});
		this.elements.buttonDel.click(function(e){
			if (!confirm("Do you want to remove this record?") || !confirm("Are you sure?")) return false;
			return _this.setAction(_this.bin('del')|_this.bin('save'));
		});
		this.elements.buttonSave.click(function(e){
			var valid=true;
			var valid=_this.validate(_this.elements.itens);
			
			if(valid) return _this.setAction(Number(_this.elements.action.val())|_this.bin('save'));
			return false;
		});
		return this;
	}
	EForm.prototype.setAction=function(val) {
		//console.log(['action: ',val]);
		var e=this.elements;
		e.action.val(val);
		e.form.submit();
		return this;
	}
	EForm.prototype.bin=function(val) { return 1<<this.actions[val]; }
})(jQuery, window, document);
