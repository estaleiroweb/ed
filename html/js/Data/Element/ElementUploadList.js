;(function ($, window, document, undefined) {
	var pluginName = 'elementUploadList',keyDefalts={},defaults = {
	}
	for(var i in defaults) keyDefalts[i.toLowerCase()]=i;
	function ElementUploadList(element, options) {
		this.element = $(element);
		this.settings = $.extend({}, defaults, options);
		this._name = pluginName;
		this.la = pluginName;
		this.init();
	}
	ElementUploadList.prototype = {
		init: function () {
			var _this=this;
			{//find Elements
				this.elements={
					form: $(this.element.find('input')[0].form),
					submit: this.element.find('button[type="submit"]'),
					fileBox: this.element.find('[ed-item="uploadList-file-box"]'),
					filterBox: this.element.find('[ed-item="uploadList-filter-box"]'),
					removeBox: this.element.find('[ed-item="uploadList-remove-files-box"]'),
					
					selectAll: this.element.find('[ed-item="uploadList-select-all"]'),
					showDateTime: this.element.find('[ed-item="uploadList-show-datetime"]'),
					showSize: this.element.find('[ed-item="uploadList-show-size"]'),
					
					removeGrp: this.element.find('[ed-item="uploadList-remove-grp"]'),
					removeCheck: this.element.find('[ed-item="uploadList-remove-check"]'),
					removeFile: this.element.find('[ed-item="uploadList-remove-file"]'),
					
					filter: this.element.find('[ed-item="uploadList-filter"]'),
					clearFilter: this.element.find('[ed-item="uploadList-clear-filter"]'),
				}
			}
			var $fileTag=this.elements.fileBox.find('input');
			var _fileTagName=$fileTag.attr('name');
			if(!(/\[\]$/.test(_fileTagName))) $fileTag.attr('name',_fileTagName+'[]');
			{//Set Events
				this.elements.form.submit(function(e){
					var ch=_this.element.find('[ed-item="uploadList-remove-check"]:checked');
					var len=ch.length;
					if(len!=0 && (
						!confirm('Voce está prestes a excluir '+len+' arquivo(s).\nDeseja realmente fazer isto?') ||
						!confirm('Tem certeza desta opeação?')
					)) e.preventDefault();
				});
				this.elements.clearFilter.click(function(e){
					_this.elements.filter.val('');
					_this.elements.filter.keyup();
				});
				this.elements.filter.keyup(function(e){
					//console.log(e);
					var filter=_this.elements.filter.val();
					filter=filter==''?'.':filter.replace(/ +/g,'|').replace(/[(){}\[\]\.\$\^\*\+\-\?\:]/g,'\\$1');
					var er=new RegExp('('+filter+')','i');
					var filtered=total=0;
					_this.elements.removeGrp.each(function(idx,item){
						total++;
						var $this=$(this);
						var $item=$this.find('[ed-item="uploadList-remove-file"]');
						if($item.length==0 || er.test($item.text())) return $this.show();
						filtered++;
						$this.hide();
						$this.find('[ed-item="uploadList-remove-check"]').prop('checked',false);
						return true;
					});
					_this.setSelectAllStatus();
				});
				this.elements.removeCheck.click(function(e){
					_this.setSelectAllStatus();
				});
				this.elements.selectAll.click(function(e){
					_this.elements.removeCheck.prop('checked',_this.elements.selectAll.prop('checked'));
					_this.elements.selectAll.prop('indeterminate', false);
				});
				this.elements.showDateTime.click(function(e){
					var checked=_this.elements.showDateTime.prop('checked');
					_this.elements.removeGrp.each(function(idx,item){
						var $this=$(this);
						var $item=$this.find('div[ed-item="uploadList-remove-file-datetime"]');
						var len=$item.length;
						if(checked && len==0) {
							var ts=moment(new Date(Number($this.attr('timestamp'))*1000)).format('DD/MM/YYYY HH:mm:ss');
							var $sub=$this.append('<div ed-item="uploadList-remove-file-datetime" class="label label-default pull-left">'+ts+'</div>');
						}
						else if(!checked && len!=0) $item.remove();
					});
				});
				this.elements.showSize.click(function(e){
					var checked=_this.elements.showSize.prop('checked');
					_this.elements.removeGrp.each(function(idx,item){
						var $this=$(this);
						var $item=$this.find('div[ed-item="uploadList-remove-file-size"]');
						var len=$item.length;
						if(checked && len==0) {
							var sz=_this.humanSize(Number($this.attr('size')));
							var $sub=$this.append('<div ed-item="uploadList-remove-file-size" class="label label-default pull-right">'+sz+'</div>');
						}
						else if(!checked && len!=0) $item.remove();
					});
				});
				this.elements.removeFile.mouseover(function(e){
					var $this=$(this);
					$this.attr('title',$this.text());
				});
				this.implementEventsFilter();
			}
			//console.log(this.elements);
			return this;
		},
		setSelectAllStatus: function(){
			var selected=this.element.find('[ed-item="uploadList-remove-files-box"] [ed-item="uploadList-remove-check"]:checked:visible').length;
			if(selected==0) this.elements.selectAll.prop('checked',false).prop('indeterminate', false);
			else if(selected==this.element.find('[ed-item="uploadList-remove-files-box"] [ed-item="uploadList-remove-check"]:visible').length) this.elements.selectAll.prop('checked',true).prop('indeterminate', false);
			else this.elements.selectAll.prop('indeterminate', true);
		},
		humanSize: function(size){//bytes
			var count=0;
			var med=['B','K','M','G','T','P','E','Z','Y'];
			while(size>=1000 && count<=8) {
				count++;
				size/=1024;
			}
			return String(parseFloat(size).toFixed(2)).substr(0,4).replace(/\.$/,'')+med[count];
		},
		implementEventsFilter:function(){
			var _this=this;
			this.elements.fileBox.find('button').unbind().click(function(){
			//console.log($(this));
				var $main=$($(this).parents('div.input-group')[0]);
				var $file=$main.find('input');
				if($file.val()=='') return false;
				$file.val('');
				_this.resumeFileUpload();
			});
			this.elements.fileBox.find('input').unbind().change(function(){
				var $file=$(this);
				if(!_this.file_check($file)) return $file.val('');
				var $main=$($file.parents('div.input-group')[0]);
				_this.addFileUpload($main.clone());
			});
		},
		addFileUpload:function($grp){
			var $filesEmpty=this.fileUploadEmpty();
			if($filesEmpty.length==0) {
				$grp.find('input').val('');
				this.elements.fileBox.append($grp);
				this.implementEventsFilter();
			}
		},
		resumeFileUpload:function(){
			var $filesEmpty=this.fileUploadEmpty();
			if($filesEmpty.length>1) {
				$($($filesEmpty[0]).parents('div.input-group')[0]).remove();
				this.resumeFileUpload();
			}
		},
		fileUploadEmpty:function(){
			return this.elements.fileBox.find('input').filter(function(){
				return $(this).val()=='';
			});
		},
		file_check:function($file){
			var pattern=this.element.attr('file_pattern');
			if(pattern===undefined) return true;
			var checkBy=this.element.attr('file_pattern_checkBy');
			if(checkBy===undefined || !this['check_'+checkBy]) return true;
			var value=$file.val().replace(/^.*[\/\\]/,'');
			//console.log([checkBy,pattern,value]);
			if(this['check_'+checkBy](value,pattern)) {
				var rep=this.elements.removeCheck.filter(function() {
					return $(this).val() == value;
				}).length;
				if(rep==0 || confirm('Arquivo já existe. Deseja substitui-lo?')) return true;
				return false;
			}
			alert('Padrão não aceito: '+pattern);
			return false;
		},
		regexp_quote:function(pattern){
			return pattern.replace(/([\.\\()\/{}\[\]?*+])/g,'\\$1');
		},
		
		check_match:function(value,pattern){
			return value==pattern;
		},
		check_in:function(value,pattern){
			var obj = eval("(function(){return " + pattern + ";})()");
			if(Array.isArray(obj)) {
				var ret=obj.find(function(e){return e==value;});
				return (ret===undefined)?false:true;
			}
			return this.check_match(value,pattern);
		},
		check_key:function(value,pattern){
			var obj = eval("(function(){return " + pattern + ";})()");
			if(Object.prototype.toString.call(obj)=='[object Object]') {
				return (obj[value]===undefined)?false:true;
			}
			console.log([pattern,value,Object.prototype.toString.call(obj)]);
			return this.check_match(value,pattern);
		},
		check_regexp:function(value,pattern){
			var obj = eval("(function(){return " + pattern + ";})()");
			return obj.test(value);
		},
		check_glob:function(value,pattern){
			pattern='/^'+this.regexp_quote(pattern).replace(/\\\*/g,'.*').replace(/\\\?/g,'.').replace(/\s+/g,'|')+'$/i';
			return this.check_regexp(value,pattern);
		},
		check_smart:function(value,pattern){
			pattern='/'+this.regexp_quote(pattern).replace(/\s+/g,'|')+'/i';
			return this.check_regexp(value,pattern);
		},
	};
	
	$.fn.elementUploadList = function(options) {
		//console.log(this)
		var args = arguments;
		var typeOptions=typeof options;
		
		if (options === undefined || typeOptions === 'object') {
			return this.each(function () {
				if (!$.data(this, 'plugin_' + pluginName)) {
					$.data(this, 'plugin_' + pluginName, new ElementUploadList(this, options));
				}
			});
		}
		else if (typeOptions === 'string' && options[0] !== '_' && options !== 'init') {
			var returns;
			this.each(function () {
				var instance = $.data(this, 'plugin_' + pluginName);
				if (instance instanceof ElementUploadList) {
					var subOptions=Array.prototype.slice.call(args, 1);
					if (typeof instance[options] === 'function') returns = instance[options].apply(instance, subOptions);
					else instance[options]=subOptions;
				}
			});
			return returns !== undefined ? returns : this;
		}
	}
})(jQuery, window, document);

$(document).ready(function(){
	$('[ed-element="uploadList"]').elementUploadList();
});