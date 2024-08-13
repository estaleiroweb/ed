;(function ($, window, document, undefined) {
	function Wizard(element, options) {
		parent.this(element, options);
	}
	Wizard.extends($.Ed).jQuery();
	//Wizard.defaults={
	//	'eu':'tu',
	//};
	//Wizard.defaults=$.extend({}, Wizard.defaults, options);
	Wizard.defaults=$.extend({}, $.Ed.defaults, {
		'step': null
	});
	/*Wizard.prototype.init=function(){
		parent.this();

		//console.log(this.element);
		//console.log(this.id);
		return this; 
		console.log([this.name,this.getValue(),this.getDspValue()]);
		//this.getObjDisplay().css('border','1px solid #F00');
		this.getObjDisplay().addClass('has-error');
	};*/
	Wizard.prototype.init_elements=function() {
		this.elements={fields:[]};
		var er=RegExp('^'+this.id.replace(/[.+*?:\(\)\[\]\{\}\/\\]/g,'\\$1')+'_');
		//console.log(er)
		for(var i=0;i<this.element[0].length;i++){
			var $e=$(this.element[0][i]);
			//var nm=$e.attr('name');
			var id=$e.attr('id');
			if($e.hasClass('Wizard-Control')) this.elements[id.replace(er,'')]=$e;
			else this.elements.fields.push($e);
		}
		//console.log(this.elements);
		//console.log($.Element.allObjs);
		
		return this; 	
	}
	Wizard.prototype.init_events=function() {
		//console.log(this.element)
		var _this=this
		if(this.elements.backward) this.elements.backward.click(function(){ _this.action(-1); _this.btnDisable(); });
		if(this.elements.forward)  this.elements.forward.click(function(){ _this.action(1); _this.btnDisable(); });
		if(this.elements.commit)   this.elements.commit.click(function(){ _this.action(); _this.btnDisable(); });
		if(this.elements.cancel)   this.elements.cancel.click(function(){ _this.action(0); _this.btnDisable(); });
		if(this.elements.start)    this.elements.start.click(function(){ _this.action(0); _this.btnDisable(); });
		//this.element.submit(function(ev){_this.submit(ev) });
		return this;
		var _this=this;
	}
	Wizard.prototype.btnDisable=function(){
		var a=['backward','forward','commit','cancel','start'];
		for(var i=0;i<a.length;i++) if(this.elements[a[i]]) this.elements[a[i]].prop('disabled',true);
	};
	Wizard.prototype.getObjLabel=function(){
		var labels=this.element.parents('label');
		return labels.length==0?this.form.find('label[for="'+this.id+'"]'):labels[0];
	};
	Wizard.prototype.action=function(dir){
		if((dir===1 || dir===undefined) && !this.valid()) return;
		if(dir===undefined) this.elements.exec.val(1);
		else if(dir===0) this.elements.step.val('');
		else {
			var oStep=this.elements.step;
			var numSteps=Number(this.elements.numSteps.val());
			oStep.val(Math.min(numSteps,Math.max(0,Number(oStep.val())+dir)));
		}
		this.element.submit();
	}
	Wizard.prototype.valid=function(){ return this.validate(this.elements.fields); }
	Wizard.prototype.submit=function(ev){
		//console.log(this.elements);
		//return ;
		//ev.preventDefault();
	}
})(jQuery, window, document);

