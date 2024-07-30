;(function ($, window, document, undefined) {
	function ElementNumber(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementNumber.extends($.Element).jQuery();
	ElementNumber.defaults=$.extend({}, $.Element.defaults, {
		'max': null,
		'min': null,
		'step': null,
		
		'unsigned':false,
		'isdecimal':false,
		'integer':false,
		'float':false,
		
		'scale':null,
		'precision':null,
		'maxlength':255
	});
	
	ElementNumber.prototype.init=function(){
		//this.dad('init'); //parent.this();//parent.init();
		parent.this();
		
		//console.log(JSON.stringify(this.settings.max));
		//this.rebuildSettings(['max','min','step','scale','precision']);
		//console.log(this.settings);
		this.shift=0;
		this.pressedKey=false;
		this.start=0;
		this.startPoint=0;
		this.end=0;
		this.objLength=0;
		this.splitedLen=[0];
		this.splited=[];
		this.objText='';
		this.leftWord='';
		this.rightWord='';
		this.value='';
		this.eReg=/^([-+]?)(\d*)((?:\.\d*)?)((?:E[-+]?)?)(\d*)$/i;
		return this; 
	};
	ElementNumber.prototype.init_events=function() {
		//this.dad('init_events'); //parents.init_events();
		parent.this();

		var _this=this;
		this.element.on('keydown',  function (e){ return _this.event_keydown(e);  });
		this.element.on('keypress', function (e){ return _this.event_keypress(e); });
		this.element.on('keyup',    function (e){ return _this.event_keyup(e);    });
		this.element.on('click',    function (e){ return _this.event_click(e);    });
		this.element.on('paste',    function (e){ return _this.event_paste(e);    });
		this.element.on('cut',      function (e){ return _this.event_cut(e);      });
		this.element.on('drop',     function (e){ return _this.event_drop(e);     });
		return this;
		/*{//Set Events
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
	ElementNumber.prototype.initVars=function(){
		this.objText=this.element.getSelection();
		this.start=this.objText.start;
		this.end=this.objText.end;
		var v=this.element.val();
		
		this.leftWord=v.substr(0,this.start);
		this.rightWord=v.substr(this.end);
		
		this.value=v.replace(/[^0-9.eE+-]/g,'').toUpperCase();
		this.value=this.value.replace(/^([0-9+-]*\.\d*)\..*/g,'$1');
		this.value=this.value.replace(/^([+-]?[0-9.]+)[+-].*/,'$1');
		if(this.value!=this.element.val()) {
			var opts={leftWord:false,rightWord:false}; //,objText:false
			for(var i in opts) this[i]=this[i].replace(/[^0-9.eE+-]/g,'').toUpperCase();
			this.objLength=this.objText.text.length;
			this.start=this.leftWord.length;
		}
		return this;
	}
	ElementNumber.prototype.splitVars=function(){
		this.splited=this.value.match(this.eReg) || ['','','','','',''];//0-All, 1-sign, 2-integer, 3-.decimal, 4-E+ expSign, 5-expValue
		this.splitedLen=[0];
		var tam=0;
		for(var i=1;i<=5;i++) {
			tam+=this.splited[i].length;
			this.splitedLen[i]=tam;
		}
		return this;
	}
	ElementNumber.prototype.rebuildStart=function(point,newValue){
		var oldValue=this.splited[point];
		var start=this.start;
		var tamOld=oldValue.length;
		if(newValue!==false) {
			this.splited[point]=newValue;
			if(this.startPoint<=start) {
				var tamNew=newValue.length;
				var delta=tamOld-tamNew;
				this.start=start-delta;
				this.end=this.start+this.objLength;
			}
		}
		
		this.startPoint+=tamOld;
	}
	ElementNumber.prototype.rebuild=function(){
		this.startPoint=0;
		this.rebuildStart(1,this.splited[1]=='+' || (this.splited[1]=='-' && this.settings.unsigned)?'':false);
		
		var valPoint=this.splited[2].replace(/^0+([1-9]\d*)$/,'$1');
		if(this.settings.scale!==null) {
			if(valPoint.length>this.settings.scale) valPoint=valPoint.substr(0,this.settings.scale);
		}
		if(this.settings.required && valPoint=='') valPoint='0';
		this.rebuildStart(2,valPoint==this.splited[2]?false:valPoint);
		
		var valPoint=this.splited[3];
		if(this.settings.precision===0 || this.settings.integer) {
			if(valPoint!='') valPoint='';
		}
		else if(this.settings.precision!==null){
			var tmp=this.settings.precision+1;
			if(valPoint.length>tmp) valPoint=valPoint.substr(0,tmp);
		}
		this.rebuildStart(3,valPoint==this.splited[3]?false:valPoint);
		
		var valPoint=this.splited[4].toUpperCase();
		var valPoint2=this.splited[5];
		if(this.settings.isdecimal || this.settings.integer) valPoint=valPoint2='';
		else{
			var tam=valPoint2.length;
			if(tam){
				if(valPoint=='' || valPoint=='E') valPoint='E+';
				if(tam>3) valPoint2=valPoint2.substr(0,3);
			}
			else if(valPoint=='E') valPoint='E+';
		}
		this.rebuildStart(4,valPoint==this.splited[4]?false:valPoint);
		this.rebuildStart(5,valPoint2==this.splited[5]?false:valPoint2);
		
		this.splitedLen=[0];
		var tam=0;
		for(var i=1;i<=5;i++) {
			tam+=this.splited[i].length;
			this.splitedLen[i]=tam;
		}
		
		this.plot();
	}
	ElementNumber.prototype.plot=function(){
		if(this.splited[4]!='' && this.splited[2]+this.splited[3]=='') {
			this.splited[2]='0';
			this.start=this.start+1;
		}

		var v=this.splited[1]+this.splited[2]+this.splited[3]+this.splited[4]+this.splited[5];
		if(v!=this.element.val()){
			this.element.val(v);
			this.value=v;
			this.end=this.start+this.objLength;
			this.element.setSelection(this.start,this.objLength);
		}
	}
	ElementNumber.prototype.reapply=function(e,txt){
		this.initVars();
		var i=this.leftWord+txt;
		var v=i+this.rightWord;
		if(this.eReg.test(v)) {
			this.value=v;
			this.splitVars();
			this.objLength=0;
			this.start=i.length;
			this.rebuild();
			return true;
		}
		return e.returnValue=false;
	}

	ElementNumber.prototype.checkShift=function(e,direction){
		if(e.shiftKey){
			if(this.shift==0) this.shift=direction;
		}
		else {
			this.shift=0;
			if(direction==1) this.start=this.end;
			else this.end=this.start;
		}
	}
	ElementNumber.prototype.setPos=function(e,start){
		this.start=Math.min(Math.max(start,this.splitedLen[1]),this.splitedLen[5]);
		this.objLength=0;
		this.end=start;
		this.element.setSelection(start,0);
		return e.returnValue=false;
	}
	ElementNumber.prototype.repos=function(e){
		this.objLength=Math.abs(this.end-this.start);
		if(this.start>this.end) {
			this.start=this.end;
			this.end=this.start+this.objLength;
			this.shift*=-1;
		}
		if(this.objLength==0) this.shift=0;

		this.element.setSelection(this.start,this.objLength);
		this.pressedKey=true;
		return e.returnValue=false;
	}
	ElementNumber.prototype.reposLeft=function(e,point){
		if(point>this.splitedLen[3] && point<this.splitedLen[4]) point=this.splitedLen[3];
		else if(point<this.splitedLen[1] && !e.shiftKey) point=this.splitedLen[1];
		else if(point<0) point=0;
		this.start=point;
		if(!e.shiftKey) this.end=point;
		return this.repos(e);
	}
	ElementNumber.prototype.reposRight=function(e,point){
		if(point>this.splitedLen[3] && point<this.splitedLen[4]) point=this.splitedLen[4];
		else if(point>this.splitedLen[5]) point=this.splitedLen[5];
		this.end=point;
		if(!e.shiftKey) this.start=point;
		return this.repos(e);
	}
	ElementNumber.prototype.setLeft=function(e,inc){
		return this.reposLeft(e,this.start+inc);
	}
	ElementNumber.prototype.setRigth=function(e,inc){
		return this.reposRight(e,this.end+inc);
	}
	ElementNumber.prototype.setPgUp=function(e,inc){
		if(inc==-1) {
			for(var i=5;i>0;i--) if(this.splitedLen[i]<this.start) return this.reposLeft(e,this.splitedLen[i]);
			return this.reposLeft(e,0);
		}
		else {
			for(var i=1;i<=5;i++) if(this.splitedLen[i]>this.start) return this.reposLeft(e,this.splitedLen[i]);
			return this.reposLeft(e,this.splitedLen[5]);
		}
	}
	ElementNumber.prototype.setPgDn=function(e,inc){
		if(inc==1) {
			for(var i=1;i<=5;i++) if(this.splitedLen[i]>this.end) return this.reposRight(e,this.splitedLen[i]);
			return this.reposRight(e,this.splitedLen[5]);
		}
		else {
			for(var i=5;i>0;i--) if(this.splitedLen[i]<this.end) return this.reposRight(e,this.splitedLen[i]);
			return this.reposRight(e,0);
		}
	}
	ElementNumber.prototype.setHome=function(e,inc){
		return this.reposLeft(e,inc==-1?0:this.splitedLen[5]);
	}
	ElementNumber.prototype.setEnd=function(e,inc){
		return this.reposRight(e,inc==1?this.splitedLen[5]:0);
	}

	ElementNumber.prototype.event_keydown=function(e){
		if(this.element.attr('readOnly') || this.element.attr('disabled')) return e.returnValue=false;
		
		this.pressedKey=false;
		this.initVars();
		this.splitVars();
		var c=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
		
		if(c==35 || (e.ctrlKey && (c==34 || c==40))) { //End Ctrl+PgDn Ctrl+Down
			this.checkShift(e,1);
			return this.shift>=0?this.setEnd(e,1):this.setHome(e,1);
		}
		else if(c==36 || (e.ctrlKey && (c==33 || c==38))) { //Home Ctrl+PgUp Ctrl+Up
			this.checkShift(e,-1);
			return this.shift<=0?this.setHome(e,-1):this.setEnd(e,-1);
		}
		else if(c==33 || c==38 || (e.ctrlKey && c==37)) { //PgUp Up Ctrl+<
			this.checkShift(e,-1);
			return this.shift<=0?this.setPgUp(e,-1):this.setPgDn(e,-1);
		}
		else if(c==34 || c==40 || (e.ctrlKey && c==39)) { //PgDn Down Ctrl+>
			this.checkShift(e,1);
			return this.shift>=0?this.setPgDn(e,1):this.setPgUp(e,1);
		}
		else if(c==37) { //Left
			this.checkShift(e,-1);
			return this.shift<=0?this.setLeft(e,-1):this.setRigth(e,-1);
		}
		else if(c==39) { //Right
			this.checkShift(e,1);
			return this.shift>=0?this.setRigth(e,1):this.setLeft(e,1);
		}
	}
	ElementNumber.prototype.event_keypress=function(e){
		this.pressedKey=true;
		this.rebuild();
		var c=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
		var v=this.element.val();
		var len=v.length;
		//console.log([v,len,this.settings.maxlength]);
		if(len>=this.settings.maxlength) return e.returnValue=false;

		if (c>=48 && c<=57) { //>=0 n <=9
			if(this.start==0) {
				if(this.splitedLen[1]!=0) this.setPos(e,this.splitedLen[1]);
			}
			else if(this.start>this.splitedLen[3] && this.start<=this.splitedLen[4]) this.setPos(e,this.splitedLen[4]);
			this.reapply(e,String.fromCharCode(c));
			return e.returnValue=false;
		}
		else if (c==44 || c==46) { //, or .
			if(this.settings.integer || (!this.settings.isdecimal && !this.settings.float) || this.settings.precision===0) return e.returnValue=false;
			else if(this.splited[3]=='') {
				if(this.start>=this.splitedLen[1] && this.start<=this.splitedLen[2]) {
					this.reapply(e,'.');
					return e.returnValue=false;
				}
				else {
					this.splited[3]='.';
					this.start=this.splitedLen[2]+1;
				}
			}
			else return this.setPos(e,this.splitedLen[2]+1);
		}
		else if (c==101 || c==69) { //e or E
			if(!this.settings.float) return e.returnValue=false;
			if(this.settings.isdecimal || this.settings.integer) {
				return e.returnValue=false;
			}
			if(this.splited[4]=='') {
				this.splited[4]='E+';
				this.start=this.splitedLen[4]+2;
			}
			else this.setPos(e,this.splitedLen[4]);
		}
		else if (c==43) { //+
			if(this.start<=this.splitedLen[3]) {
				if(this.settings.unsigned) return e.returnValue=false;
				this.splited[1]='';
				this.setPos(e,this.start-this.splitedLen[1]);
			}
			else this.splited[4]='E+';
		}
		else if (c==45) { //-
			if(this.start<=this.splitedLen[3]) {
				if(this.settings.unsigned || this.splited[1]=='-') return e.returnValue=false;
				this.splited[1]='-'
				this.start=this.start+1;
			}
			else this.splited[4]='E-';
		}
		
		this.plot();
		return e.returnValue=false;
	}
	ElementNumber.prototype.event_keyup=function(e){
		if(!this.pressedKey) {
			var c=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
			
			if (c==46 || c==8) { //=DEL or =BS
				this.value=this.element.val();
				this.splitVars();
				this.rebuild();
			}
			else if(e.ctrlKey && c==88) { //crtl+X
			}
		}
	}
	ElementNumber.prototype.event_click=function(e){
		if(e.shiftKey) return true;
		this.initVars();
		if(this.objLength!=0) return true;
		this.splitVars();
		var point=this.start;
		if(point>this.splitedLen[3] && point<this.splitedLen[4]) this.start=this.splitedLen[3];
		else if(point<this.splitedLen[1]) this.start=this.splitedLen[1];
		else return true;
		this.element.setSelection(this.start,0);
		return false;
	}
	ElementNumber.prototype.event_paste=function(e){
		if(e.originalEvent!==undefined) this.reapply(e,e.originalEvent.clipboardData.getData('Text').replace(/[^0-9.eE+-]/g,''));
		return false;
	}
	ElementNumber.prototype.event_cut=function(e){
		this.initVars();
		var v=this.leftWord+this.rightWord;
		if(this.eReg.test(v)) return true;
		return e.returnValue=false;
	}
	ElementNumber.prototype.event_drop=function(e){
		if(e.originalEvent!==undefined) this.reapply(e,e.originalEvent.dataTransfer.getData('Text').replace(/[^0-9.eE+-]/g,''));
		return false;
		//this.initVars().splitVars().rebuild();
	}
	ElementNumber.prototype.getValue=function(){
		//console.log(1234);
		var v=this.element[this.attVal()]();
		return v=='' || v===null?null:Number(v);
	};
	ElementNumber.prototype.setValue=function(v){
		this.reapply(window.event,v);
		return this;
	};
})(jQuery, window, document);
