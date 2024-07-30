//		$('#test_out').html('<pre>keyup:\n'+JSON.stringify($o.data(),' ',4)+'</pre>');
;(function ($, window, document, undefined) {
	function Element(element, options) {
		$.Ed.call(this,element, options); // call super constructor.
		//OtherSuperClass.call(this);
	}
	Element.extends($.Ed).jQuery();
	//Element.defaults={'eu':'tu'};
	//Element.defaults=$.extend({}, Ed.defaults, options);
	Element.modal_file=null;
	Element.defaults=$.extend({}, $.Ed.defaults, {
		'accept': null,
		'disabled': false,
		'list': null,
		'maxlength': 4294967295,
		'minlength':0,
		'multiple': false,
		'pattern': null,
		'placeholder': '',
		'readonly': false,
		'required': false,
		'size': null,
		'type': null,
		'label': null,
		'ed-form-fieldname': null,
		'ed-form-id': null,
		'ed-class': null,
		'name': null,
		'validate': null
	});
	Element.allObjs={}
	
	Element.prototype.erros=[];
	Element.prototype.init=function(){
		this.id=this.getId();
		this.form=$(this.element[0].form);
		var idForm=this.form && this.form[0]?this.form[0].id:null;
		this.idForm=this.element.attr('ed-form-id') || idForm;
		if(this.form.length==0) this.form=$('body');
		//console.log([this.name,this.getValue(),this.getDspValue()]);
		//this.getObjDisplay().css('border','1px solid #F00');
		//this.getObjDisplay().addClass('has-error');
		$.Element.allObjs[this.id]=this;
		return this; 
	};
	Element.prototype.getId=function(){
		return this.element.attr('id');
	}
	Element.prototype.rebuildSettings=function(ops){
		if(getType(ops)=='array') for(var i in ops) {
			var k=ops[i];
			if(this.settings[k]===null) this.settings[k]=false;
		}
		else for(var k in ops) if(this.settings[k]===null) this.settings[k]=ops[k];
		return this; 
	};
	Element.prototype.init_elements=function() {
		this.elements={};
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
	Element.prototype.init_events=function() {
		var _this=this;
		this.element.change(function(){ _this.valid(); });
		return this;
		/*
		var _this=this;
		{//Set Events
			this.elements.form.submit(function(e){
				var ch=_this.element.find('[ed-item="uploadlist-remove-check"]:checked');
				var len=ch.length;
				if(len!=0 && (
					!confirm('Voce está prestes a excluir '+len+' arquivo(s).\nDeseja realmente fazer isto?') ||
					!confirm('Tem certeza desta opeação?')
				)) e.preventDefault();
			});
		}*/
	}
	Element.prototype.getObjLabel=function(){
		var labels=this.element.parents('label');
		return labels.length==0?this.form.find('label[for="'+this.id+'"]'):labels[0];
	};
	Element.prototype.getObjDisplay=function(){
		var dsp=this.form.find('[for="'+this.id+'"][ed-diplay]');
		return dsp.length==0?this.element:dsp;
	};
	Element.prototype.getDspValue=function(){
		return this.getObjDisplay().val();
	};
	Element.prototype.getLabel=function(){
		var label=this.getObjLabel();
		if(label.length!=0) return label.text();
		var label=this.element.attr('ed-label');
		if(label) return label;
		var label=this.element.attr('label');
		if(label) return label;
		if(this.form_name) return this.form_name;
		return this.id;
	};
	Element.prototype.val=function(val){
		return val===undefined?this.getValue(): this.setValue(val);
	};
	Element.prototype.getValue=function(){
		return this.element[this.attVal()]();
	};
	Element.prototype.setValue=function(v){
		this.element[this.attVal()](v);
		return this;
	};
	Element.prototype.attVal=function(){
		return this.element[0].nodeName.match(/^(input|select|textarea)$/i)?'val':'text';
	}
	Element.prototype.valid=function(showError){
		if(this.settings.disabled || this.attVal()!='val' || this.element.attr('type')=='hidden') return true;
		var t;
		var val=this.getValue();
		this.erros=[];
		if(val==='' || val===null) {
			if(this.getSetting('required',false)) this.erros.push('Valor Requerido');
		} else {
			if(val<(t=this.getSetting('min',val))) this.erros.push('Valor ('+val+') menor que o mínimo: '+t);
			if(val>(t=this.getSetting('max',val))) this.erros.push('Valor ('+val+') maior que o máximo: '+t);
			var l=val.length;
			if(l<(t=this.getSetting('minlength',l))) this.erros.push('Tamanho ('+l+') insuficiente: '+t);
			if(l>(t=this.getSetting('maxlength',l))) this.erros.push('Tamanho ('+l+') exedente: '+t);
			
			var er=this.getSetting('pattern',false);
			if(er) {
				try{
					if(getType(er)!='regexp') er=eval(er);
					if(!er.test(val)) this.erros.push('Fora do padrão: '+er);
				}
				catch(e){
				}
			}
				var arr=[];
				if(this.check) arr.push(this.check);
				var validate=this.getSetting('validate',false);
				if(validate) {
					try{
						if(!getType(validate).match(/^(function|object)$/)) validate=eval(validate);
						arr.push(validate);
					}
					catch(e){
					}
				}
				for(var i=0;i<arr.length;i++) {
					try{
						var ret=arr[i](val,this);
						var tpRet=typeof ret;
						if(tpRet=='boolean') {
							if(!ret) this.erros.push('Valor não válido');
						} else if(ret!==undefined && ret!==null) this.erros.push(ret);
					}
					catch(e){
					}
				}
		}

		if(showError!==false) this.showHideError();
		//console.log(this.erros);

		return this.erros.length?false:true;
	};
	Element.prototype.getSetting=function(name,def){
		//console.log([name,this.settings[name],this.settings])
		return  (name in this.settings && this.settings[name]!==null && this.settings[name]!==undefined)?this.settings[name]:def;
	}
	Element.prototype.showHideError=function(name,def){
		var feedback=this.element.parent('.feedback');
		var helpBlock=feedback.find('.feedback-message');
		var errors=this.erros.join('<br>');
		var fn=this.erros.length?'addClass':'removeClass';
		//console.log(feedback)
		feedback[fn]('has-error');
		if(helpBlock.length) helpBlock[fn]('help-block').html(errors);
	}
	Element.o=function(fieldname,form_id){
		var p='[ed-form-id';
		if(form_id!=undefined) p+='="'+form_id+'"';
		p+='][ed-form-fieldname';
		if(fieldname!=undefined) p+='="'+fieldname+'"';
		p+=']';
		var $o=$(p);
		var l=$o.length;
		if(l==0) return;
		if(l==1) return Element.allObjs[$o.attr('id')];
		var ret=[];
		for(var i=0;i<l;i++) ret.push(Element.allObjs[$o[i].attr('id')]);
		return ret;
	}

	/*
		parents('[class~="form-inline"]')
			string:		<div class="form-group">  então label class="sr-only" e input type="email" class="form-control"  placeholder="Label se não existir" </div>
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
