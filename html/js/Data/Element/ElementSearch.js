;(function ($, window, document, undefined) {
	function ElementSearch(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementSearch.extends($.Element).jQuery();
	
	ElementSearch.objs={}
	ElementSearch.init_obj=function(objs){
		if(!objs.source || !objs.objs) return;
		$.ElementSearch.objs[objs.id]=objs;
	}
	ElementSearch.start_obj=function(id){
		if(!$.ElementSearch.objs[id] || $.Element.allObjs[id]) return;
		var o=$.ElementSearch.objs[id];
		$('<button id="'+id+'" type="button" ed-element="search" ed-form-id="'+o.idForm+'" ed-class="ElementSearch" novalidate="1"></button>').elementSearch();
	}
	ElementSearch.prototype.init=function(){
		//this.dad('init'); //parents.init();
		parent.this();
		this.idClear=this.id.replace(/^d_/,'c_');
		
		//init buttom
		//console.log([this.name,this.getValue(),this.getDspValue()]);
		//this.getObjDisplay().css('border','1px solid #F00');
		//this.getObjDisplay().addClass('has-error');
		return this; 
	};
	ElementSearch.prototype.init_elements=function() {
		//this.dad('init_elements'); //parents.init_elements();
		parent.this();
		this.elements.clear=$(this.element.parent().find('button#'+this.idClear));
		this.elements.relation=$.ElementSearch.objs[this.id];
		this.elements.children={}
		this.elements.keys={}
		this.addChild('key');
		this.addChild('getCells');
		//console.log(this.elements);
		return this; 
	}
	ElementSearch.prototype.init_events=function() {
		var _this=this;
		//this.dad('init_events'); //parents.init_events();
		parent.this();
		//console.log($.Element.allObjs);
		this.element.on('click',         function (e){ return _this.event_click_search(e);    });
		this.elements.clear.on('click',  function (e){ return _this.event_click_clear(e);     });
		return this;
	}
	ElementSearch.prototype.init_done=function() {
		//console.log(this.getValue());
	}
	ElementSearch.prototype.addChild=function(k){
		if(!this.elements.relation || !this.elements.relation.source || !this.elements.relation.source[k] || !this.elements.relation.objs) return;
		for(var targetName in this.elements.relation.source[k]) {
			var e=this.elements.relation.objs[targetName];
			var targetId=e.id;
			var oTarget=$.Element.allObjs[targetId];
			this.elements.keys[targetId]=e.key;
			if(oTarget) this.elements.children[targetId]=oTarget;
		}
	}
	
	ElementSearch.prototype.event_click_search=function(e){
		var resize=function(e,$obj) {
			var $iFrame=$(this);
			var $oFBody=$iFrame.contents().find('body');
			console.log({
				//o:$this,
				outerWidth: $oFBody.outerWidth(true),
				outerHeight: $oFBody.outerHeight(true),
				innerWidth: $oFBody.innerWidth(),
				innerHeight: $oFBody.innerHeight(),
				width: $oFBody.width(),
				height: $oFBody.height(),
				scrollWidth: $oFBody[0].scrollWidth,
				scrollHeight: $oFBody[0].scrollHeight,
				availWidth:screen.availWidth,
				availHeight:screen.availHeight,
				screen_width:screen.width,
				screen_height:screen.height,
				html:$oFBody.html(),
				'body':$oFBody
			});
			//$iFrame.width(20);
			return;
			window.dialogWidth='1px';
			window.dialogHeight='1px';
			var d=$oFBody.contentWindow.document.body;
			var w=Math.min(screen.availWidth-70,d.scrollWidth+d.offsetWidth);
			var h=Math.min(screen.availHeight-90,d.scrollHeight+d.offsetHeight);
			window.dialogWidth=w+'px';
			window.dialogLeft=Math.round((screen.availWidth-w)/2)+'px';
			window.dialogHeight=h+'px';
			window.dialogTop=Math.round((screen.availHeight-h)/2)+'px';
			/*alert([
				w,h,
				'\nWidth:',screen.availWidth,d.clientWidth,d.offsetWidth,d.scrollWidth,
				'\nHeight:',screen.availHeight,d.clientHeight,d.offsetHeight,d.scrollHeight
			])*/
		}
		//console.log(this.elements.relation);
		//monta parametros
		//Show popup + parametros
		//recebe popup
		//set values
		//onload="resize(this)" id="list" 
		
		var lf="\n";
		{var header=this.settings.label?
			'      <div class="modal-header">'+lf+
			'        <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">'+lf+
			'          <span aria-hidden="true">&times;</span>'+lf+
			'        </button>'+lf+
			'        <h5 class="modal-title" id="exampleModalLabel">'+this.settings.label+'</h5>'+lf+
			'      </div>'+lf:'';
		}
		{var html=''+
			'<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">'+lf+
			'  <div class="modal-dialog" role="document">'+lf+
			'    <div class="modal-content">'+lf+header+
			
			
			'      <div class="modal-body">'+lf+
			'        <iframe height="100%" width="100%" scrolling="auto" frameborder="0"></iframe>'+lf+
			'      </div>'+lf+
			
			'      <div class="modal-footer">'+lf+
			'        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>'+lf+
			'      </div>'+lf+
			
			'    </div>'+lf+
			'  </div>'+lf+
			'</div>'+lf;
		}
		var $oDiv=$(html).prependTo($('body'));
		var $iFrame=$oDiv.find('iframe');
		var $oFBody=$iFrame.contents().find('body');
		
		$iFrame.load(resize);
		$iFrame.attr('src','/easyData');
		//$oFBody.html('<div>ola ola ola ola ola ola ola ola ola ola ola ola ola ola ola ola ola </div>');
		
		
		$oDiv.modal('show')
		.on('hidden.bs.modal',function(e){
			$oDiv.remove();
		})
		//.on('shown.bs.modal',function(e){
		//	resize(e);
		//});
		//this.resize($oFBody);
		//console.log($oDiv);
		
		
		return;
		if (!$.Element.modal_file) return;
		var pref='center:yes;resizable:yes;status:no;scroll:no;dialogHeight:1px;dialogWidth:1px;';
		var v=window.showModalDialog($.Element.modal_file+'?time='+(new Date()),this.elements.relation,pref);
		if(v) this.setValue(v)
	}
	ElementSearch.prototype.event_click_clear=function(e){
		var c=this.elements.children;
		for(var i in c) c[i].setValue(null);
	}
	ElementSearch.prototype.resize=function(e,$obj) {
		var $iFrame=$(this);
		var $oFBody=$iFrame.contents().find('body');
		console.log({
			//o:$this,
			outerWidth: $oFBody.outerWidth(true),
			outerHeight: $oFBody.outerHeight(true),
			innerWidth: $oFBody.innerWidth(),
			innerHeight: $oFBody.innerHeight(),
			width: $oFBody.width(),
			height: $oFBody.height(),
			scrollWidth: $oFBody[0].scrollWidth,
			scrollHeight: $oFBody[0].scrollHeight,
			availWidth:screen.availWidth,
			availHeight:screen.availHeight,
			screen_width:screen.width,
			screen_height:screen.height,
			html:$oFBody.html(),
			'body':$oFBody
		});
		//$iFrame.width(20);
		return;
		window.dialogWidth='1px';
		window.dialogHeight='1px';
		var d=$oFBody.contentWindow.document.body;
		var w=Math.min(screen.availWidth-70,d.scrollWidth+d.offsetWidth);
		var h=Math.min(screen.availHeight-90,d.scrollHeight+d.offsetHeight);
		window.dialogWidth=w+'px';
		window.dialogLeft=Math.round((screen.availWidth-w)/2)+'px';
		window.dialogHeight=h+'px';
		window.dialogTop=Math.round((screen.availHeight-h)/2)+'px';
		/*alert([
			w,h,
			'\nWidth:',screen.availWidth,d.clientWidth,d.offsetWidth,d.scrollWidth,
			'\nHeight:',screen.availHeight,d.clientHeight,d.offsetHeight,d.scrollHeight
		])*/
	}

	ElementSearch.prototype.getValue=function(){
		var c=this.elements.children;
		var out={};
		for(var i in c) {
			var l=c[i].settings.label;
			out[l]=c[i].getValue();
		}
		return out;
	};
	ElementSearch.prototype.setValue=function(v){
		var c=this.elements.children;
		for(var i in c) {
			var l=c[i].settings.label;
			if(v[l]!=undefined) c[i].setValue(v[l]);
		}
		return this;
	};
})(jQuery, window, document);

/*
eval('ElementSearch='+(new Element).constructor.toString())
ElementSearch.prototype=new Element()
ElementSearch.prototype.constructor=ElementSearch

ElementSearch.prototype.args=new Object()
ElementSearch.prototype.values=new Object()
ElementSearch.prototype.objs=new Object()
ElementSearch.prototype.file=false

ElementSearch.prototype.setValue=function(value){
	for (var k in value) if(typeof(this.objs[k])=='object') {
		var o=this.objs[k]
		for(var i=0;i<o.length;i++){
			var oElement=window[o[i]]
			if (typeof(oElement)!='undefined') oElement.setValue(value[k])
		}
	}
	this.onchange()
}
ElementSearch.prototype.show=function(){
	if (!this.file) return
	this.values=window.showModalDialog(this.file+'?time='+(new Date()),this.args,'center:yes;resizable:yes;status:no;scroll:no;dialogHeight:1px;dialogWidth:1px;')
	if (this.values) this.setValue(this.values)
}
ElementSearch.prototype.clear=function(){
	for (var k in this.objs) if(typeof(this.objs[k])=='object') {
		var o=this.objs[k]
		for(var i=0;i<o.length;i++){
			var oElement=window[o[i]]
			if (typeof(oElement)!='undefined') oElement.setValue('')
		}
	}
	this.onchange()
}
*/