;(function ($, window, document, undefined) {
	function Ed_Element(element, options) {
		$.Ed.call(this,element, options); // call super constructor.
		//OtherSuperClass.call(this);
	}
	Ed_Element.extends($.Ed).jQuery();
	//Ed_Element.defaults={
	//	'eu':'tu',
	//};
	//Ed_Element.defaults=$.extend({}, Ed_Element.defaults, options);
	Ed_Element.defaults=$.extend({}, $.Ed.defaults, {
		'accept': null,
		'checked': null,
		'disabled': null,
		'list': null,
		'max': null,
		'min': null,
		'maxlength': null,
		'multiple': null,
		'pattern': null,
		'placeholder': null,
		'readonly': null,
		'required': null,
		'size': null,
		'step': null,
		'type': null,
		'valid': null
	});
	
	Ed_Element.prototype.init=function(){
		console.log([this.name,this.getValue(),this.getDspValue()]);
		//this.getObjDisplay().css('border','1px solid #F00');
		this.getObjDisplay().addClass('has-error');
		return this; 
	};
	Ed_Element.prototype.init_elements=function() {
		var _this=this;
		this.id=this.element.attr('id');
		this.form=$(this.element[0].form);
		this.form_name=this.element.attr('name');
		if(this.form.length==0) this.form=$('body');		
		return this; 
		/*{//find Elements
			this.elements={
				form:$(this.element.find('input')[0].form),
				clearFilter: this.element.find('[ed-item="uploadlist-clear-filter"]'),
			}
		}*/
		//this.addElements=function(selector,element_name,base)
	}
	Ed_Element.prototype.init_events=function() {
		var _this=this;
		return this;
		/*{//Set Events
			this.elements.form.submit(function(e){
				var ch=_this.element.find('[ed-item="uploadlist-remove-check"]:checked');
				var len=ch.length;
				if(len!=0 && (
					!confirm('Voce est� prestes a excluir '+len+' arquivo(s).\nDeseja realmente fazer isto?') ||
					!confirm('Tem certeza desta opea��o?')
				)) e.preventDefault();
			});
		}*/
	}
	Ed_Element.prototype.getObjLabel=function(){
		var labels=this.element.parents('label');
		return labels.length==0?this.form.find('label[for="'+this.id+'"]'):labels[0];
	};
	Ed_Element.prototype.getObjDisplay=function(){
		var dsp=this.form.find('[for="'+this.id+'"][ed-diplay]');
		return dsp.length==0?this.element:dsp;
	};
	Ed_Element.prototype.getValue=function(){
		return this.element.val();
	};
	Ed_Element.prototype.getDspValue=function(){
		return this.getObjDisplay().val();
	};
	Ed_Element.prototype.getLabel=function(){
		var label=this.getObjLabel();
		if(label.length!=0) return label.text();
		var label=this.element.attr('ed-label');
		if(label) return label;
		var label=this.element.attr('label');
		if(label) return label;
		if(this.form_name) return this.form_name;
		return this.id;
	};

/*
	parents('[class~="form-inline"]')
		string:		<div class="form-group">  ent�o label class="sr-only" e input type="email" class="form-control"  placeholder="Label se n�o existir" </div>
					<div class="form-group"><label class="sr-only" for="exampleInputEmail3">Email address</label><input type="email" class="form-control" id="exampleInputEmail3" placeholder="Email"></div>
					<div class="form-group"><label class="sr-only" for="exampleInputAmount">Amount (in dollars)</label><div class="input-group"><div class="input-group-addon">$</div><input type="text" class="form-control" id="exampleInputAmount" placeholder="Amount"><div class="input-group-addon">.00</div></div></div>
		checkbox:	<div class="checkbox"><label><input type="checkbox"> Remember me</label></div>
		button:		<button type="submit" class="btn btn-primary">Transfer cash</button>
	parents('[class~="form-horizontal"]')
		string:		<div class="form-group"><label for="inputEmail3" class="col-sm-2 control-label">Email</label><div class="col-sm-10"><input type="email" class="form-control" id="inputEmail3" placeholder="Email"></div></div>
		checkbox:	<div class="form-group"><div class="col-sm-offset-2 col-sm-10"><div class="checkbox"><label><input type="checkbox"> Remember me</label></div></div></div>
		button:		<div class="form-group"><div class="col-sm-offset-2 col-sm-10"><button type="submit" class="btn btn-default">Sign in</button></div></div>

	.form-control
		inputs (text, password, datetime, datetime-local, date, month, time, week, number, email, url, search, tel, and color), textarea, select 
	.radio, .radio-inline, 
		radio
	.checkbox, .checkbox-inline
		checkbox
	.control-label .col-sm-2
		label
	disabled | readonly
		<fieldset disabled>, inputs, radio, checkbox, textarea, select
		parent-tag <div class='disabled'>
	.help-block
		span, div, p, etc use aria-describedby="helpBlock" at the input|element
	.has-warning, .has-error, .has-success



	<p class="text-muted">...</p>
	<p class="text-primary">...</p>
	<p class="text-success">...</p>
	<p class="text-info">...</p>
	<p class="text-warning">...</p>
	<p class="text-danger">...</p>

	<p class="bg-primary">...</p>
	<p class="bg-success">...</p>
	<p class="bg-info">...</p>
	<p class="bg-warning">...</p>
	<p class="bg-danger">...</p>

	<div class="pull-left">...</div>
	<div class="pull-right">...</div>
	<div class="element">...</div>
	<div class="another-element">...</div>

	<nav class="navbar-left">...</nav>
	<nav class="navbar-right">...</nav>

	<div class="center-block">...</div>
	<div class="clearfix">...</div>

	<div class="show">...</div>
	<div class="hidden">...</div>

	<a class="sr-only sr-only-focusable" href="#content">Skip to main content</a>

	<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<span class="caret"></span>
	<h1 class="text-hide">Custom heading</h1>

	aria-label=text | aria-labelledby=idLabel|Span|Div
	aria-describedby
	aria-hidden=true|false
	aria-checked=true|false
	aria-controls=st1
	aria-required=
	aria-checked
	aria-controls
	aria-haspopup

	role=tabpanel|tablist|tooltip|menu|menubar|menuitem|menuitemcheckbox|menuitemradio|separator

*/
})(jQuery, window, document);
