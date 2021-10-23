;(function ($, window, document, undefined) {
	function DataList(element, options) { $.Ed.call(this,element, options); }
	DataList.extends($.Ed).jQuery();
	DataList.defaults=$.extend({}, $.Ed.defaults, {
		showHideFieldsUpdate: false,
		swapBox: false,
		sortBox: false,
		sortBoxFrom: true,
		moveOnSelect: false,
		preserveSelectionOnMove: false,
		showFilter: true,
		showButtons: true,
		
		separator: ';',
		filterOn: 'text', // text | val
		filterTarget: '',
		filterSource: '',
		
		tipText_xxx: null,
		placeHolder_xxx: null,
		infoText_xxx: null,
		
		locale: 'pt-br',
		locales: {
			en:{
				tipText_xxx: 'Move Selected',
				placeHolder_xxx: 'Filter ExpReg',
				infoText_xxx: 'Showing all {total}/{selected} selected'
			},
			'pt-br':{
				tipText_xxx: 'Move Selected',
				placeHolder_xxx: 'Filter ExpReg',
				infoText_xxx: 'Showing all {total}/{selected} selected'
			}
		}
	});
	DataList.prototype.init_elements=function() {
		var _this=this;

		var idDataList=this.element.attr('id').replace(/_data$/,'');
		var $table=$('table#'+idDataList+'_table',this.element);
		var $page=$('input.dataList-input-page',this.element);
		
		$table.find('colgroup col').each(function(idx,item){ $(this).attr('idx',idx); });
		$table.find('thead tr').each(function(idx,item){
			$(this).find('th,td').each(function(idx,item){ $(this).attr('idx',idx).addClass('dataList-fieldHead'); });
		});
		
		var _key=$table.attr('key');
		var _url={
			'Url':$table.attr('url'),
			'Edit':$table.attr('urlEdit'),
			'Del':$table.attr('urlDel'),
			'Clone':$table.attr('urlClone'),
		}
		var $head=$table.find('thead tr');
		
		if(_key && (_url.Url || _url.Edit || _url.Del || _url.Clone)) {
			$table.find('colgroup').prepend('<col show="1" idx="action" />');
			var width=((_url.Url?1:0)+(_url.Edit?1:0)+(_url.Del?1:0)+(_url.Clone?1:0))*12;
			$($head[0]).prepend('<th rowspan="'+$head.length+'" idx="action" class="dataList-fieldHead" style="width:'+width+'px"><span class="glyphicon glyphicon-cog"></span></th>');
			$table.find('tbody > tr').each(function(idx,item){
				var htmlButtons=[];
				if(_url.Url)   htmlButtons.push('<button type="button" class="btn btn-default btn-sm" action="Url"  ><span class="glyphicon glyphicon-sunglasses"></span></button>');
				if(_url.Edit)  htmlButtons.push('<button type="button" class="btn btn-default btn-sm" action="Edit" ><span class="glyphicon glyphicon-edit"></span></button>');
				if(_url.Del)   htmlButtons.push('<button type="button" class="btn btn-default btn-sm" action="Del"  ><span class="glyphicon glyphicon-remove"></span></button>');
				if(_url.Clone) htmlButtons.push('<button type="button" class="btn btn-default btn-sm" action="Clone"><span class="glyphicon glyphicon-duplicate"></span></button>');
				var container=$(this).prepend('<td idx="action" class="dataList-field dataList-field-action"><div class="btn-group" role="group" aria-label="Ações">'+htmlButtons.join('')+'</div></td>');
			});
			$table.find('tbody > tr > td[idx="action"] button').click(function(e){
				var $this=$(this);
				var $tr=$this.parents('tr');
				var action=$this.attr('action');
				var url=_url[$this.attr('action')];
				//console.log(url);
				var keys=[];
				$.each(_key.split(/\s*[,;]\s*/),function(i,item){
					var field=item.replace(/^(`)([^`]+)\1$/,'$2');
					var val=$tr.find('input[field="'+field+'"]').val();
					//keys.push(encodeURI(field)+'='+encodeURI(val));
					keys.push(encodeURIComponent(field)+'='+encodeURIComponent(val));
				});
				keys=keys.join('&');
				if((/\?$/).test(url)) var sep='';
				else if((/\?/).test(url)) var sep='&';
				else var sep='?';
				//console.log(url+sep+keys)
				$.goURL(url+sep+keys);
			});
		}
		this.elements={
			idDataList:idDataList,
			id:$('input.dataList-input-id',this.element).val(),
			idFile:$('input.dataList-input-idFile',this.element).val(),
			form: $page[0].form,
			menu: $('div.dataList-btn-menu li a',this.element),
			table: $table,
			colsgroup: $('colgroup col',$table),
			cells: $('th,td',$table),
			headers: $('thead th',$table),
			filterBox: $('.dataList-filter',$table),
			filters: $('tr.dataList-filter input',$table),
			
			button_new: $('button.dataList-btn-new',this.element),
			button_filter: $('button.dataList-btn-filter',this.element),
			button_sort: $('button.dataList-btn-sort',this.element),
			button_showHide: $('button.dataList-btn-showHide',this.element),
			button_goPage_first: $('div.dataList-navBar button:eq(0)',this.element),
			button_goPage_previous: $('div.dataList-navBar button:eq(1)',this.element),
			button_goPage_next: $('div.dataList-navBar button:eq(2)',this.element),
			button_goPage_last: $('div.dataList-navBar button:eq(3)',this.element),
			button_goPage_pagination: $('li.dataList-pagination-num',this.element),
			button_goPage_pagination_previous: $('ul.pagination li:eq(0)',this.element),
			button_goPage_pagination_next: $('ul.pagination li:last',this.element),
			button_goPage_navBar: $('div.dataList-navBar input',this.element),
			button_quantLines: $('div.dataList-quantLines li a',this.element),
			
			info_records: $('div.dataList-records',this.element),
			input_quantLines: $('div.dataList-quantLines input',this.element),
			input_reset: $('input.dataList-input-reset',this.element),
			input_outformat: $('input.dataList-input-outFormat',this.element),
			input_default: $('input.dataList-input-default',this.element),
			input_showFilter: $('input.dataList-input-showFilter',this.element),
			input_showRecCount: $('input.dataList-input-showRecCount',this.element),
			input_showNavBars: $('input.dataList-input-showNavBars',this.element),
			input_showTable: $('input.dataList-input-showTable',this.element),
			input_page: $page,
			input_pages: $('input.dataList-input-pages',this.element),
			input_order: $('input.dataList-input-order',this.element),
			input_group: $('input.dataList-input-group',this.element),
			input_lstFields: $('input.dataList-input-lstFields',this.element),
			input_widthField: $('input.dataList-input-widthField',this.element),
			input_submit_button: $('input.dataList-input-submit_button',this.element),
		}
		$head.children().each(function(index){
			var $this=$(this);
			var style=$this.attr('style');
			var w=(/width\s*:\s*(auto|initial|inherit|\d+(?:\.\d+)?\s*(?:%|p[xtc]]|[cm]m|in|r?em|ex|ch|v[hw]|vmin|vmax)?)/i).exec(style);
			_this.rebuildWidth(index,w?w[1]:$this.css('width'));
		});
		var $cellH0=$table.find('tr:first th:first');
		if($cellH0.attr('idx')=='action'){
			var w=$table.find('tbody tr:first td:first div').width();
			if(w!=null) $cellH0.css('padding-right',(w-16)+'px');
		}
		//this.addElements=function(selector,element_name,base)
		return this; 
	}
	DataList.prototype.init_events=function() {
		var _this=this;
		this.elements.menu.click(function(e){
			var attr=null;
			var method=$(this).attr('action');
			switch(method){
				case 'plus':                                                 break;
				case 'filter': case 'sort':                                  break;
				case 'showHide': case 'showHideFilter':                      break;
				case 'copyTable': case 'copyFields': case 'copyFieldsTable': break;
				case 'copyURL': $.copyURL(e);                             return;
				case 'reset':                                                break;
				case 'help':                                                break;
				case 'exportCSV':      method='exportAll'; attr=e.ctrlKey?'csv':(e.shiftKey?'csv_comma':'csv_semicomma');       break;
				case 'exportExcelAll': method='exportAll'; attr=e.ctrlKey?'xls':'xml';       break;
				case 'exportExcel':    method='export';    attr=e.ctrlKey?'xls':'xml';       break;
				default: alert('Not implemented '+method); return;
			}
			//console.log(method);
			return _this[method](e,attr);
		});
		this.elements.headers.click(function(e){
			var incKey=e.ctrlKey || e.shiftKey;
			var order=_this.elements.input_order.val();
			var obj=_this.getFieldAttrs($(this));
			
			if(obj.field==undefined) return;
			//var field=this.element.find('colgroup col:eq('+$this.index()+')').attr('field')
			
			if (order) {
				//regulariza o order
				order=order.replace(/(^ +| +$)/,'').replace(/ +/g,' ').replace(/ *, */g,',');
				var orderList=order.split(',');
				var out=[];
				k=false;
				for(var i in orderList){
					var orderItem=orderList[i].split(' ');
					if (orderItem[0].replace(/(^`|`$)/g,'')==obj.field) {
						k=i;
						if(typeof(orderItem[1])!='string' || orderItem[1]=='') orderItem[1]='asc';
						var ord=orderItem[1].toLowerCase();
						if(ord=='asc') out.push('`'+obj.field+'` desc');
					} else if(incKey) out.push(orderList[i]);
				}
				if(k===false) out.push('`'+obj.field+'` asc');
				order=out.join(',');
			}
			else order='`'+obj.field+'` asc';
			
			_this.elements.input_order.val(order);
			_this.submit();
		});
		this.elements.cells.hover(function(e){
			var $this=$(this);
			$this.attr('title',$this.text());
		});
		this.elements.button_new.click(function(e){
			return _this.plus(e);
		});
		this.elements.button_filter.click(function(e){ //TODO IMPLEMENT
			var $this=$(this);
			var obj=$.data(_this.elements.button_filter,'obj');
			alert(obj.field);
			//botão ok / cancelar
			//lista de todos campos acessíveis 
			//com values de filtro na frente
			//se clicado aciona tela direita, default 0 ou hover
			//tela direita: OR e AND para ADD e escolha GLOB/REGEXP/EXACT associado ao tipo
			//verificador de máscara com alert de error e abertura automática do filter no campo
		});
		this.elements.filters.keypress(function(e){
			var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
			if (key==13) _this.submit();
		}).
		focus(function(e){
			$.data(_this.elements.button_filter,'obj',_this.getFieldAttrs($(this)));
			_this.elements.button_filter.show();
		});
		this.elements.button_sort.click(function(e){
			return _this.sort(e);
		});
		this.elements.button_showHide.click(function(e){
			return _this.showHide(e);
		});
		this.elements.button_goPage_first.click(function(e){
			return _this.goPage(1);
		});
		this.elements.button_goPage_previous.click(function(e){
			return _this.goPage('previous');
		});
		this.elements.button_goPage_next.click(function(e){
			return _this.goPage('next');
		});
		this.elements.button_goPage_last.click(function(e){
			return _this.goPage('last');
		});
		this.elements.button_goPage_pagination_previous.click(function(e){
			return _this.goPage('previous');
		});
		this.elements.button_goPage_pagination_next.click(function(e){
			return _this.goPage('next');
		});
		this.elements.button_goPage_pagination.click(function(e){
			return _this.goPage(Number($(this).attr('page')));
		});
		this.elements.button_goPage_navBar.change(function(e){
			return _this.goPage(Number($(this).val()));
		});
		this.elements.button_goPage_navBar.focus(function(e){
			var $this=$(this);
			$this.val(_this.elements.input_page.val());
			$this.attr('min',1).attr('max',_this.elements.input_pages.val());
			$this.attr('type','number');
			$this.select();
		});
		this.elements.button_goPage_navBar.blur(function(e){
			var $this=$(this);
			_this.elements.input_page.val($this.val());
			$this.attr('type','text');
			$this.val(_this.elements.input_page.val()+'/'+_this.elements.input_pages.val());
		});
		this.elements.button_goPage_navBar.hover(function(e){
			var $this=$(this);
			$this.attr('title',$this.val());
		});
		this.elements.button_goPage_navBar.keypress(function(e){
			var key=e.charCode?e.charCode:(e.which?e.which:e.keyCode);
			if (key==13) $(this).blur();
			else return (/[0-9]/).test(String.fromCharCode(key));
		});
		this.elements.button_quantLines.click(function(e){
			var value=$(this).attr('id');
			while (value=='_') {
				var retorno=Number(prompt('How many lines do you want to show (>0)?',50,'integer'));
				if (retorno==0) return;
				if (retorno<0) return alert ('It must be great than 0');
				else if (retorno<=3000 || confirm ('You choice the so high value.\nThe device may get a several time to process.\nDo you want realy dto do it?')) value=retorno;
			}
			_this.setLines(value);
			_this.submit();
		});
		$('tbody > tr > td',this.elements.table).mouseover(function(){
			var $this=$(this);
			if($this.find('#DataList_copy_td').length!=0 || $this.hasClass('dataList-field-action')) return;
			
			var $btn=$('#DataList_copy_td');
			if($btn.length) $btn.remove(); //
			
			//var $btn=$('<button id="DataList_copy_td" type="button" class="btn btn-default btn-xs float-sm-right" style="position: relative;opacity: 0.8;top: -10px; left: -10px;-moz-transform: scale(.7);-webkit-transform: scale(.7);-o-transform: scale(.7);-ms-transform: scale(.7);transform: scale(.7);" aria-label="Left Align"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span></button>');
			var $btn=$('<button id="DataList_copy_td" type="button" class="btn btn-default btn-xs pull-left" style="position: relative;opacity: 0.8;top: -10px; left: -10px;-moz-transform: scale(.7);-webkit-transform: scale(.7);-o-transform: scale(.7);-ms-transform: scale(.7);transform: scale(.7);margin-bottom: -22px;" aria-label="Left Align"><span class="glyphicon glyphicon-copy" aria-hidden="true"></span></button>');
			$btn.attr('data-copy',$this.text()).click(function(){$.copy($(this).attr('data-copy'));});
			//$this.attr({title:$this.text()}).prepend($btn);
			$this.prepend($btn);
		});
		return this;
	}
	
	DataList.prototype.submit=function(){
		this.showHide_save();
		this.elements.form.submit();
	}
	DataList.prototype.plus=function(e){
		var newLink=this.elements.table.attr('urlNew') || this.elements.table.attr('url');
		if(newLink!='') window.location=newLink;
		else alert('Not Link');
	}
	DataList.prototype.goPage=function(goPage){
		//alert(this.elements.input_page.val()); 
		//return;
		var step=(event.ctrlKey?10:1) * (event.shiftKey?5:1);
		var page=Number(this.elements.input_page.val());
		var page_old=page;
		var pages=Number(this.elements.input_pages.val());
		if(goPage=='previous') page-=step;
		else if(goPage=='next') page+=step;
		else if(goPage=='last') page=pages;
		else page=goPage;
		page=Math.max(1,Math.min(pages,page))
		if(page!=page_old) {
			this.elements.input_page.val(page);
			this.submit();
		}
	}
	DataList.prototype.setLines=function(value){
		var old=this.elements.input_quantLines.val();
		this.elements.input_quantLines.val(value);
		return old;
	}
	DataList.prototype.getFieldAttrs=function($column){
		if(!$column.hasClass('dataList-field') && !$column.hasClass('dataList-fieldHead')) {
			var $parent=$column.parents('.dataList-field,.dataList-fieldHead');
			if($parent.length==0) return false;
			$column=$($parent[0]);
		}
		var index=$column.index();
		$column=this.elements.table.find('thead tr:first th:eq('+index+')');
		var idx=$column.attr('idx');
		var $col=this.elements.table.find('colgroup col[idx="'+idx+'"]');
		//console.log([this.elements,index,idx,$col]);
		
		return $col.length?{
			index:index,
			idx:$col.attr('idx'),
			obj:$col,
			field:$col.attr('field'),
			fieldName:$col.attr('fieldName'),
			type:$col.attr('type'),
			format:$col.attr('format'),
		}:false;
	}
	DataList.prototype.showHideFilter=function(e){
		this.elements.filterBox.toggle('slow');
	}
	DataList.prototype.export=function(e,format){
		this.elements.input_outformat.val(format);
		//console.log([format,this.elements]);
		this.submit();
		this.elements.input_outformat.val('');
	}
	DataList.prototype.exportAll=function(e,format){
		var lines=this.setLines(0);
		this.export(e,format);
		this.setLines(lines);
	}
	DataList.prototype.reset=function(e){
		this.elements.input_reset.val(1);
		this.submit();
	}
	DataList.prototype.help=function(e){
		window.open('/easyData/help/dataList.php');
	}
	DataList.prototype.copyTable=function(e){
		var cols={};
		var value=[];
		this.elements.colsgroup.each(function (idx,obj){
			var $obj=$(obj);
			if(Number($obj.attr('show'))==1) if($obj.attr('fieldName')) cols[$obj.attr('fieldName')]=idx;
		});
		this.elements.table.find('tbody tr').each(function (idx,obj){
			var $obj=$(obj);
			var o={};
			for(var nm in cols) {
				var item=$obj.children().eq(cols[nm]);
				var v=null;
				if(item.length) {
					item=item.find('input[type="hidden"][field]');
					if(item.length) v=item.val();
				}
				o[nm]=v==''?null:v;
				//console.log([nm,item,o[nm]]);
			}
			value.push(o);
		});
		this.copy_convert(e,value);
	}
	DataList.prototype.copyFields=function(e){
		var value=[];
		this.elements.colsgroup.each(function (idx,obj){
			var $obj=$(obj);
			if($obj.attr('field')!=undefined) value.push($obj.attr('field'));
		});
		$.copy(value.join(','));
	}
	DataList.prototype.copyFieldsTable=function(e){
		var value=[];
		this.elements.colsgroup.each(function (idx,obj){
			var $obj=$(obj);
			if($obj.attr('field')!=undefined) value.push({
				'field':$obj.attr('field'),
				'fieldName':$obj.attr('fieldName'),
				'type':$obj.attr('type'),
				'format':$obj.attr('format'),
				'show':$obj.attr('show'),
				'idName':$obj.attr('idName')
			});
		});
		this.copy_convert(e,value);
	}
	DataList.prototype.copy_convert=function(e,value){
		var m='';
		if(e.ctrlKey) m=e.shiftKey?'convert2XML':'convert2CSV';
		else          m=e.shiftKey?'convert2JSON':'convert2XLS';
		$.copy(this[m](value));
	}
	DataList.prototype.copys=function(value){
		if(typeof window.clipboardData!='undefined') window.clipboardData.setData('Text', value);
		else if(typeof unsafeWindow!='undefined') {
			unsafeWindow.netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");  
			const clipboardHelper = Components.classes["@mozilla.org/widget/clipboardhelper;1"].getService(Components.interfaces.nsIClipboardHelper);  
			clipboardHelper.copyString(value);
		}
		else {
			var $o=$('<textarea>',{'val':value,'css':'display:none;'});
			$('body').append($o);
			$o.select();
			document.execCommand('copy');
			$o.remove();
		}
	}
	DataList.prototype.escape_string=function(value,escape_chars) {
		if(!escape_chars) escape_chars='\\\n\r\t\'"';
		for(var i=0;i<escape_chars.length;i++){
			var er=new RegExp('\\'+escape_chars.substr(i,1), 'g');
			value=value.replace(er,'\\x'+(('0'+escape_chars.charCodeAt(i).toString(16)).substr(-2).toUpperCase()));
		}
		return value;
	}
	DataList.prototype.htmlEscape=function(str) {
		return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	}
	DataList.prototype.json_serialize=function(value,escape_chars) {
		var ty=Object.prototype.toString.call(value);
		//console.log();
		switch(ty){
			//case '[object String]':   return '"'+(value.replace(/\n/g,'\\n').replace(/\r/g,'\\r').replace(/\t/g,'\\t')).replace(/([\\'"])/g,'\\$1')+'"';
			case '[object String]':   return '"'+this.escape_string(value,escape_chars)+'"';
			case '[object Number]':   return String(value);
			case '[object Boolean]':  return value?'true':'false';
			case '[object Function]': return value.prototype?value.prototype.constructor:value.toString(); //.replace(/function ([^(]+)\((.|\r|\n)*/,'$1');
			case '[object Null]':     return 'null';
			case '[object Array]': 
				var o=[];
				for(var i in value) o.push(this.json_serialize(value[i]));
				return '[\n   '+o.join(', \n').replace(/\n/g,'\n   ')+'\n]';
			case '[object Object]': 
				var o=[];
				for(var i in value) o.push(this.json_serialize(i)+': '+this.json_serialize(value[i]));
				return '{\n   '+o.join(', \n').replace(/\n/g,'\n   ')+'\n}';
			default: return "''";
		}
	}
	DataList.prototype.convert2XML=function(value,force) {
		var ty=Object.prototype.toString.call(value);
		switch(ty){
			case '[object String]':
				if((/\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?/).test(value)) var t='DateTime';
				else if((/[+-]?\d*(\.\d+)?(e[+-]?\d{1,3)/i).test(value)) var t='Number';
				else var t='String';
				return '<Data ss:Type="'+t+'">'+this.htmlEscape(value)+'</Data>';
			case '[object Number]':   return '<Data ss:Type="Number">'+value+'</Data>';
			case '[object Boolean]':  return '<Data ss:Type="Boolean">'+(value?'1':'0')+'</Data>';
			case '[object Function]': return this.convert2XML(value.prototype.constructor,true);
			case '[object Null]':     return '';
			case '[object Object]':   return this.convert2XML(force?this.json_serialize(value):Array(value));
			case '[object Array]':
				if(force) return this.convert2XML(this.json_serialize(value),true);
				else {
					var $table=this.element.find('table');
					var author='Helbert Fernandes (helbertfernandes@gmail.com)';
					var dt=new Date();
					var dtNow=dt.getFullYear()+'-'+
						('0'+(dt.getMonth()+1)).substr(-2)+'-'+
						('0'+(dt.getDay()+1)).substr(-2)+'T'+
						('0'+(dt.getHours()+1)).substr(-2)+':'+
						('0'+(dt.getMinutes()+1)).substr(-2)+':'+
						('0'+(dt.getSeconds()+1)).substr(-2)+'Z';
					var out=[];
					if(true) { /*Workbook*/
						out.push('<?xml version="1.0"?>');
						out.push('<?mso-application progid="Excel.Sheet"?>');
						out.push('<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">');
					}
					if(true) { /*DocumentProperties*/
						out.push('\t<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">');
						out.push('\t\t<Author>'+author+'</Author>');
						out.push('\t\t<LastAuthor>'+author+'</LastAuthor>');
						out.push('\t\t<Created>'+dtNow+'</Created>');
						out.push('\t\t<LastSaved>'+dtNow+'</LastSaved>');
						out.push('\t\t<Version>14.00</Version>');
						out.push('\t</DocumentProperties>');
					}
					if(true) { /*OfficeDocumentSettings*/
						out.push('\t<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">');
						out.push('\t\t<AllowPNG/>');
						out.push('\t</OfficeDocumentSettings>');
					}
					if(true) { /*ExcelWorkbook*/
						out.push('\t<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">');
						out.push('\t\t<WindowHeight>10035</WindowHeight>');
						out.push('\t\t<WindowWidth>21075</WindowWidth>');
						out.push('\t\t<WindowTopX>240</WindowTopX>');
						out.push('\t\t<WindowTopY>30</WindowTopY>');
						out.push('\t\t<ProtectStructure>False</ProtectStructure>');
						out.push('\t\t<ProtectWindows>False</ProtectWindows>');
						out.push('\t</ExcelWorkbook>');
					}
					if(true) { /*Styles*/
						out.push('\t<Styles>');
						out.push('\t\t<Style ss:ID="Default" ss:Name="Normal">'); /*Begin Style*/
						out.push('\t\t\t<Alignment ss:Vertical="Center"/>');
						out.push('\t\t\t<Borders/>');
						out.push('\t\t\t<Font ss:FontName="Arial" x:Family="Swiss" ss:Size="9" ss:Color="#000000"/>');
						out.push('\t\t\t<Interior/>');
						out.push('\t\t\t<NumberFormat/>');
						out.push('\t\t\t<Protection/>');
						out.push('\t\t</Style>');/*End Style*/
						out.push('\t\t<Style ss:ID="DefBody">');/*Begin Style*/
						out.push('\t\t\t<Borders>');
						out.push('\t\t\t\t<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>');
						out.push('\t\t\t\t<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>');
						out.push('\t\t\t\t<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>');
						out.push('\t\t\t\t<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>');
						out.push('\t\t\t</Borders>');
						out.push('\t\t</Style>');/*End Style*/
						out.push('\t\t<Style ss:ID="Head" ss:Parent="DefBody">');/*Begin Style*/
						out.push('\t\t\t<Font ss:FontName="Arial" x:Family="Swiss" ss:Size="9" ss:Color="#000000" ss:Bold="1"/>');
						out.push('\t\t\t<Interior ss:Color="#D9D9D9" ss:Pattern="Solid"/>');
						out.push('\t\t</Style>');/*End Style*/
						out.push('\t\t<Style ss:ID="Percent" ss:Parent="DefBody">');/*Begin Style*/
						out.push('\t\t\t<NumberFormat ss:Format="0%"/>');
						out.push('\t\t</Style>');/*End Style*/
						out.push('\t\t<Style ss:ID="Comma" ss:Parent="DefBody">');/*Begin Style*/
						out.push('\t\t\t<NumberFormat ss:Format="_-* #,##0.00_-;\-* #,##0.00_-;_-* &quot;-&quot;??_-;_-@_-"/>');
						out.push('\t\t</Style>');/*End Style*/
						out.push('\t</Styles>');
					}
					if(true) { /*Worksheet*/
						out.push('\t<Worksheet ss:Name="'+$table.attr('label')+'">');
						var aHead=[];
						for(var o in value[0]) aHead.push(o);
						var cols=aHead.length;
						var rows=value.length+1;
						out.push('\t\t<Table ss:ExpandedColumnCount="'+cols+'" ss:ExpandedRowCount="'+rows+'" x:FullColumns="1" x:FullRows="1" ss:DefaultRowHeight="15">');
						for(var o in value[0]) out.push('\t\t\t<Column ss:Width="50"/>'); //calcular largura posteriormente quando tiver tipos
						out.push('\t\t\t<Row ss:AutoFitHeight="1">');
						for(var o in value[0]) out.push('\t\t\t\t<Cell ss:StyleID="Head">'+this.convert2XML(o,true)+'</Cell>');
						out.push('\t\t\t</Row>');
						for(var i in value) {
							out.push('\t\t\t<Row ss:AutoFitHeight="1">');
							for(var o in value[i]) out.push('\t\t\t\t<Cell ss:StyleID="DefBody">'+this.convert2XML(value[i][o],true)+'</Cell>');
							out.push('\t\t\t</Row>');
						}
						out.push('\t\t</Table>');
						
						out.push('\t\t<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">');
						out.push('\t\t\t<Unsynced/>');
						out.push('\t\t\t<Selected/>');
						out.push('\t\t\t<FreezePanes/>');
						out.push('\t\t\t<FrozenNoSplit/>');
						out.push('\t\t\t<SplitHorizontal>1</SplitHorizontal>');
						out.push('\t\t\t<TopRowBottomPane>1</TopRowBottomPane>');
						out.push('\t\t\t<ActivePane>2</ActivePane>');
						out.push('\t\t\t<ProtectObjects>False</ProtectObjects>');
						out.push('\t\t\t<ProtectScenarios>False</ProtectScenarios>');
						out.push('\t\t</WorksheetOptions>');
						out.push('\t\t<AutoFilter x:Range="R1C1:R'+rows+'C'+cols+'" xmlns="urn:schemas-microsoft-com:office:excel"></AutoFilter>');
						out.push('\t</Worksheet>');
					}
					out.push('</Workbook>');
					return out.join('\n');
				}
			default:      return this.convert2XML(value.toString(),true);
		}
	}
	DataList.prototype.convert2XLS=function(value,sep,force) {
		return this.convert2CSV(value,'\t');
	}
	DataList.prototype.convert2JSON=function(value) {
		return this.json_serialize(value);
	}
	DataList.prototype.convert2CSV=function(value,sep,force) {
		var ty=Object.prototype.toString.call(value);
		if(!sep) sep=this.settings.separator;
		switch(ty){
			case '[object String]':   return this.json_serialize(value,'\\\n\r\t\'"'+sep);
			case '[object Number]':   
			case '[object Boolean]':  
			case '[object Null]':     return '';
			case '[object Function]': force=true;
			case '[object Object]':   return this.convert2XML(force?this.json_serialize(value):Array(value),sep);
			case '[object Array]':
				if(force) return this.convert2CSV(this.json_serialize(value),sep);
				else {
					var out=[];
					var l=[]
					for(var o in value[0]) l.push(this.convert2CSV(o,sep,true));
					out.push(l.join(sep));
					
					for(var i in value) {
						var l=[]
						for(var o in value[i]) l.push(this.convert2CSV(value[i][o],sep,true));
						out.push(l.join(sep));
					}
					return out.join('\n');
				}
			default: return '';
		}
	}
	DataList.prototype.rebuildWidth=function(index,width) {
		this.elements.table.find('tbody tr').each(function () {
			$(this).find('td:eq('+index+')').css('width',width);
		});
	}
	
	DataList.prototype.showHide=function(e){
		var className='dataList-showHide';
		var _this=this;
		var $sh=this.element.find('.'+className);
		if($sh.length==0) {
			$sh=$('<div class="panel panel-default '+className+'" style="display:none;"><div class="panel-body"><div class="row"></div></div></div>');
			this.element.find('table').parent().before($sh);
			$sh=this.element.find('.'+className);
		}
		if($sh.is(':visible')) {
			$sh.hide('slow');
			this.showHide_save();
		}
		else {
			var $row=$sh.find('.row');
			$row.html('');
			var fields=this.getFields(function(i,obj){
				var c=obj.objs.th.is(':visible')?' checked':'';
				$row.append("<div class='col-xs-12 col-sm-6 col-md-4 col-lg-3'><label><input type='checkbox' autocomplete='off'"+c+" value='"+obj.idx+"'> "+obj.name+"</label></div>");
			});
			$row.find('input').click(function(){_this.showHide_field(this)});
			$sh.show('slow');
		}
		return this;
	}
	DataList.prototype.showHide_field=function(obj){
		this.settings.showHideFieldsUpdate=true;
		var idx=$(obj).val();
		var _this=this;
		var fn=obj.checked?'show':'hide';
		var index=this.elements.table.find('thead tr:first [idx="'+idx+'"]').index();
		this.elements.table.find('thead [idx="'+idx+'"]').each(function () {
			$(this)[fn]();
		});
		this.elements.table.find('tbody tr').each(function () {
			$(this).find('td:eq('+index+')')[fn]();
		});
		//this.showHide_save();
		return this;
	}
	DataList.prototype.showHide_save=function(){
		if(!this.settings.showHideFieldsUpdate) return this;
		this.settings.showHideFieldsUpdate=false;
		var _this=this;
		var flds=[]
		this.elements.table.find('tbody tr:first td:visible').each(function(){
			//console.log($(this).prop("tagName"));
			var obj=_this.getFieldAttrs($(this));
			if(obj && obj.field!=undefined) flds.push(obj.field);
		});
		//console.log(flds);
		this.elements.input_lstFields.val(flds.join(','));
		//console.log(this.elements);
		//TODO ajax with session replace lstFields and see in php if show column with display:none
		var r=$.sessControl('tblDataList:'+this.elements.id,this.elements.idFile,{lstFields:flds},true);
		//console.log(r);
		return this;
	}
	DataList.prototype.sort=function(e){//TODO implement
		var className='dataList-sort';
		var _this=this;
		var $sh=this.element.find('.'+className);
		if($sh.length==0) {
			$sh=$(
				'<div class="panel panel-default '+className+'" style="display:none;">'+
					'<div class="panel-body">'+
						'<div class="row text-right">'+
							'<button class="btn btn-default" type="button">Cancelar</button>'+
							'<button class="btn btn-success" type="button">Order</button>'+
						'</div>'+
						'<div class="row">'+
							'<div class="dataList-order-box-fields col-xs-12 col-sm-5 col-md-5 col-lg-5"></div>'+
							'<div class="dataList-order-box-orders col-xs-12 col-sm-7 col-md-7 col-lg-7"></div>'+
						'</div>'+
					'</div>'+
				'</div>'
			);
			this.element.find('table').parent().before($sh);
			$sh=this.element.find('.'+className);
		}
		if($sh.is(':visible')) $sh.hide('slow');
		else {
			var $row1=$sh.find('.row:eq(1)');
			var $colsFields=$row1.find('div:eq(0)');
			var $colsOrders=$row1.find('div:eq(1)');
			$colsFields.html('');
			var order=this.elements.input_order.val().trim();
			order=order==''?[]:order.split(/\s*,\s*/);
			for(var i in order){
				order[i]=(/^`([^`]+)`(?:\s*(asc|desc))?$/i).exec(order[i]) || (/^(\w+)(?:\s+(asc|desc))?$/i).exec(order[i]);
				if(order[i]) {
					if(order[i][2]===undefined) order[i][2]='ASC';
					else order[i][2]=order[i][2].toUpperCase();
					order[i]={ field:order[i][1],dir:order[i][2]};
				}
			}
			var fnInitOrder=function(span,order){
				var $o=$(
					'<div class="input-group">'+
					span+
					'<span class="input-group-btn"><button class="btn btn-default" type="button"><span class="glyphicon glyphicon-sort-by-alphabet" aria-hidden="true"></span></button></span>'+
					'</div>'
				);
				if(order) $colsFields.find('span[order="'+(order-1)+'"]').parent().after($o);
				else $colsFields.append($o);
				
				$o.find('.btn-default').click(function(){
					var $main=$(this).parent().parent();
					var span=$main.find('span[field]')[0].outerHTML;
					$main.remove();
					fnOrderBy(span);
				});
			}
			var fnOrderBy=function(span){
				var $o=$(
					'<div class="input-group" dir="ASC">'+
					'<span class="input-group-btn"><button class="btn btn-primary" type="button"><span class="glyphicon glyphicon-arrow-down" aria-hidden="true"></span></button></span>'+
					span+
					'<span class="input-group-btn"><button class="btn btn-danger" type="button"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></span>'+
					'<span class="input-group-btn"><button class="btn btn-default" type="button"><span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button></span>'+
					'<span class="input-group-btn"><button class="btn btn-default" type="button"><span class="glyphicon glyphicon-triangle-top" aria-hidden="true"></span></button></span>'+
					'</div>'
				);
				$colsOrders.append($o);
				$o.find('.btn-primary').click(function(){
					var $this=$(this);
					var $main=$this.parent().parent();
					if($main.attr('dir')=='ASC'){
						$this.find('span').removeClass('glyphicon-arrow-down').addClass('glyphicon-arrow-up');
						$main.attr('dir','DESC');
					} else {
						$this.find('span').removeClass('glyphicon-arrow-up').addClass('glyphicon-arrow-down');
						$main.attr('dir','ASC');
					}
				});
				$o.find('.btn-danger').click(function(){
					var $main=$(this).parent().parent();
					var $span=$main.find('span[field]');
					var span=$span[0].outerHTML
					var order=Number($span.attr('order'));
					$main.remove();
					fnInitOrder(span,order);
				});
				$o.find('.btn-default:eq(0)').click(function(){
					var $main=$(this).parent().parent();
					var $to=$main.next();
					if($to.length==0) return;
					$main.insertAfter($to);
				});
				$o.find('.btn-default:eq(1)').click(function(){
					var $main=$(this).parent().parent();
					var $to=$main.prev();
					if($to.length==0) return;
					console.log($main.prev());
					$main.insertBefore($to);
				});
			}

			var fields=this.getFields(function(i,o){
				fnInitOrder('<span class="input-group-addon form-control" field="'+i+'" idx="'+o.idx+'" order="'+o.order+'">'+o.name+'</span>');
			});
			for(var i in order){
				var $span=$colsFields.find('span[field="'+order[i].field+'"]');
				var $btn=$span.next().find('button');
				$btn.click();
				if(order[i].dir=='DESC') $colsOrders.find('span[field="'+order[i].field+'"]').prev().find('button').click();
			}
			
			var $row0=$sh.find('.row:eq(0)');
			$row0.find('.btn-default').click(function(){
				_this.sort();
			});
			$row0.find('.btn-success').click(function(){
				var order=[];
				$colsOrders.find('.input-group').each(function(){
					var $this=$(this);
					var dir=$this.attr('dir');
					var field=$this.find('span[field]').attr('field');
					order.push('`'+field+'` '+dir);
				});
				_this.elements.input_order.val(order.join(', '));
				_this.submit();
			});
			
			
			//console.log([order,fields]);
			/*
			$row.html('');
			$row.find('input').click(function(){_this.showHide_field(this)});
			*/
			$sh.show('slow');
		}
	}
	DataList.prototype.getFields=function(callback){
		var _this=this;
		var fields={}
		this.elements.colsgroup.each(function(i){
			var $o=$(this);
			var name=$o.attr('fieldName');
			if(name==undefined) return;
			var idx=Number($o.attr('idx'));
			var $col=_this.elements.table.find('thead tr:first [idx="'+idx+'"]');
			var field=$o.attr('field');
			var obj={
				idx:idx,
				order:i,
				name:name,
				idname:$o.attr('idname'),
				type:$o.attr('type'),
				typenum:Number($o.attr('typenum')),
				format:$o.attr('format'),
				show:$o.attr('show')=='1'?true:false,
				visible: $col.is(':visible'),
				objs:{
					column: $o,
					th: $col
				}
			};
			if(callback) callback(field,obj);
			fields[field]=obj;
		});
		return fields;
	}
	DataList.prototype.reposition=function( ) { //TODO 
	}
	DataList.prototype.show=function( ) { //TODO ????
	}
	DataList.prototype.hide=function( ) { //TODO ????
	}
	DataList.prototype.update=function( content ) { //TODO bulk update or point update
		}
})(jQuery, window, document);
