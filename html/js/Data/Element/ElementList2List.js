;(function ($, window, document, undefined) {
	var pluginName = 'list2List',keyDefalts={},defaults = {
		swapBox: false,
		sortBox: false,
		sortBoxFrom: true,
		moveOnSelect: false,
		preserveSelectionOnMove: false,
		showFilter: true,
		showButtons: true,
		
		filterOn: 'text', // text | val
		filterTarget: '',
		filterSource: '',
		
		tipText_move: null,
		tipText_moveAll: null,
		tipText_remove: null,
		tipText_removeAll: null,
		tipText_orderUp: null,
		tipText_orderDown: null,
		tipText_filter: null,
		tipText_clearFilter: null,
		placeHolder_filter: null,
		infoText_all: null,
		infoText_filtered: null,
		infoText_empty: null,
		htmlButton_move: '<span class="glyphicon glyphicon-chevron-left"></span>',
		htmlButton_moveAll: '<span class="glyphicon glyphicon-chevron-left"></span><span class="glyphicon glyphicon-chevron-left"></span>',
		htmlButton_remove: '<span class="glyphicon glyphicon-chevron-right"></span>',
		htmlButton_removeAll: '<span class="glyphicon glyphicon-chevron-right"></span><span class="glyphicon glyphicon-chevron-right"></span>',
		htmlButton_orderUp: '<span class="glyphicon glyphicon-arrow-up"></span>',
		htmlButton_orderDown: '<span class="glyphicon glyphicon-arrow-down"></span>',
		htmlButton_clearFilter: '<span class="glyphicon glyphicon-remove"></span>',
		
		locale: 'pt-br',
		locales: {
			en:{
				tipText_move: 'Move Selected',
				tipText_moveAll: 'Move All',
				tipText_remove: 'Remove Selected',
				tipText_removeAll: 'Remove All',
				tipText_orderUp: 'Order to Up',
				tipText_orderDown: 'Order to Down',
				tipText_filter: 'Filter',
				tipText_clearFilter: 'Clear Filter',
				placeHolder_filter: 'Filter ExpReg',
				infoText_all: 'Showing all {total}/{selected} selected',
				infoText_filtered: '<span class="label label-warning">Filtered</span> {count} from {total}/{selected} selected',
				infoText_empty: 'Empty list',
			},
			'pt-br':{
				tipText_move: 'Move Selecionados',
				tipText_moveAll: 'Move Todos',
				tipText_remove: 'Remove Selecionados',
				tipText_removeAll: 'Remove Todos',
				tipText_orderUp: 'Ordena para Cima',
				tipText_orderDown: 'Ordena para Baixo',
				tipText_filter: 'Filtro',
				tipText_clearFilter: 'Limpa Filtro',
				placeHolder_filter: 'Filtro ExpReg',
				infoText_all: 'Mostrando todos {total}/{selected} selecionados',
				infoText_filtered: '<span class="label label-warning">Filtrado</span> {count} de {total}/{selected} selecionados',
				infoText_empty: 'Lista Vazia',
			},
		},
	}
	for(var i in defaults) keyDefalts[i.toLowerCase()]=i;
	function List2List(element, options) {
		this.element = $(element);
		this.settings = $.extend({}, defaults, options);
		this._name = pluginName;
		this.la = pluginName;
		this.init();
	}
	List2List.prototype = {
		init: function () {
			//console.log(_this.settings);
			{//Set Variables
				this.container = $('' +
				'<div class="row">'+
				'   <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">'+
				'      <div class="input-group">'+
				'         <input class="form-control" type="text">'+
				'         <span class="input-group-btn">'+
				'            <button class="btn btn-default" type="button"></button>'+
				'         </span>'+
				'      </div>'+
				'      <select multiple="multiple" class="form-control"></select>'+
				'      <div class="info-container"><span class="info"></span></div>'+
				'   </div>'+
				'   <div class="col-xs-5 col-sm-3 col-md-2 col-lg-2">'+
				'      <div class="btn-group btn-group-justified" role="group">'+
				'         <a href="javascript:void(0)" type="button" class="btn btn-default move"></a>'+
				'         <a href="javascript:void(0)" type="button" class="btn btn-default remove"></a>'+
				'      </div>'+
				'      <div class="btn-group btn-group-justified" role="group">'+
				'         <a href="javascript:void(0)" type="button" class="btn btn-default moveall"></a>'+
				'         <a href="javascript:void(0)" type="button" class="btn btn-default removeall"></a>'+
				'      </div>'+
				'      <div class="btn-group btn-group-justified" role="group">'+
				'         <a href="javascript:void(0)" type="button" class="btn btn-default orderup"></a>'+
				'         <a href="javascript:void(0)" type="button" class="btn btn-default orderdown"></a>'+
				'      </div>'+
				'   </div>'+
				'   <div class="col-xs-7 col-sm-9 col-md-5 col-lg-5">'+
				'      <div class="input-group">'+
				'         <input class="form-control" type="text">'+
				'         <span class="input-group-btn">'+
				'            <button class="btn btn-default" type="button"></button>'+
				'         </span>'+
				'      </div>'+
				'      <select multiple="multiple" class="form-control"></select>'+
				'      <div class="info-container"><span class="info"></span></div>'+
				'   </div>'+
				'</div>').insertBefore(this.element);
				var _this=this;
			}
			{//Check data-atributos
				$.each(this.element[0].attributes, function() {
					if(this.specified && this.name in keyDefalts) {
						var name=keyDefalts[this.name];
						var t=typeof _this.settings[name];
						if(t=='boolean') _this.settings[name]=!(/^(|0|fals[eo]|off|desligad[oa]|)$/i).test(this.value);
						else if(_this.settings[name]==null || t!='object') _this.settings[name]=String(this.value);
					}
				});
			}
			{//find Elements
				var boxButons=$(this.container.children()[1]);
				if(this.settings.swapBox) {
					var box1=$(this.container.children()[2]);
					var box2=$(this.container.children()[0]);
					var strClassMove='remove';
					var strClassRemove='move';
				}
				else {
					var box1=$(this.container.children()[0]);
					var box2=$(this.container.children()[2]);
					var strClassMove='move';
					var strClassRemove='remove';
				}
				this.elements={
					form:$(this.element[0].form),
					
					box1: box1,
					filterInput1: $('input', box1),
					filterClear1: $('button', box1),
					select1: $('select', box1),
					info1: $('.info-container .info', box1),
					
					box2: box2,
					filterInput2: $('input', box2),
					filterClear2: $('button', box2),
					select2: $('select', box2),
					info2: $('.info-container .info', box2),
					
					boxButons: boxButons,
					moveButton: $('.'+strClassMove, boxButons),
					removeButton: $('.'+strClassRemove, boxButons),
					moveAllButton: $('.'+strClassMove+'all', boxButons),
					removeAllButton: $('.'+strClassRemove+'all', boxButons),
					orderUpButton: $('.orderup', boxButons),
					orderDownButton: $('.orderdown', boxButons),
				}
			}
			{//Move os atributos para o lugar correto
				$.each(this.element[0].attributes, function() {
					if(this.specified && this.name!='multiple') {
						if(this.name=='class') return _this.container.addClass(this.value);
						else if(this.name=='disabled') return _this.disabled(this.value);
						else if(this.name in keyDefalts) return this;
						else if((/^(style|size)$/).test(this.name)) _this.elements.select2.attr(this.name, this.value);
						_this.elements.select1.attr(this.name, this.value);
					}
				});
				this.element.prop('multiple',true).hide();
				if(this.element.attr('name').substr(-2)!='[]') this.element.attr('name',this.element.attr('name')+'[]');
			}
			{//Move os options para o select correto
				$('option[selected],option:selected',this.element).remove().appendTo(this.elements.select1).removeAttr('selected');
				$('option',this.element).remove().appendTo(this.elements.select2).removeAttr('selected');
			}
			{//Set Texts
				this.elements.moveButton.attr('title',this.txt('tipText_move')).html(this.txt('htmlButton_move'));
				this.elements.removeButton.attr('title',this.txt('tipText_remove')).html(this.txt('htmlButton_remove'));
				this.elements.moveAllButton.attr('title',this.txt('tipText_moveAll')).html(this.txt('htmlButton_moveAll'));
				this.elements.removeAllButton.attr('title',this.txt('tipText_removeAll')).html(this.txt('htmlButton_removeAll'));
				this.elements.orderUpButton.attr('title',this.txt('tipText_orderUp')).html(this.txt('htmlButton_orderUp'));
				this.elements.orderDownButton.attr('title',this.txt('tipText_orderDown')).html(this.txt('htmlButton_orderDown'));
				this.elements.filterInput1.attr('title',this.txt('tipText_filter')).attr('placeholder',this.txt('placeHolder_filter'));
				this.elements.filterClear1.attr('title',this.txt('tipText_clearFilter')).html(this.txt('htmlButton_clearFilter'));
				this.elements.filterInput2.attr('title',this.txt('tipText_filter')).attr('placeholder',this.txt('placeHolder_filter'));
				this.elements.filterClear2.attr('title',this.txt('tipText_clearFilter')).html(this.txt('htmlButton_clearFilter'));
				this.elements.filterInput1.val(this.settings.filterTarget);
				this.elements.filterInput2.val(this.settings.filterSource);
				if(this.settings.sortBox) {
					this.elements.orderUpButton.hide();
					this.elements.orderDownButton.hide();
				}
				if(!this.settings.showFilter) this.hideFilter();
				if(!this.settings.showButtons) this.hideButtons();
				
				this.rebuild();
			}
			{//Set Events
				this.elements.select1.change(function(e){
					if(_this.settings.moveOnSelect) _this.elements.removeButton.click();
					_this.rebuild();
				});
				this.elements.select2.change(function(e){
					if(_this.settings.moveOnSelect) _this.elements.moveButton.click();
					_this.rebuild();
				});
				this.elements.select1.dblclick(function(e){
					_this.elements.removeButton.click();
				});
				this.elements.select2.dblclick(function(e){
					_this.elements.moveButton.click();
				});
				this.elements.moveButton.click(function(e){
					var $selecteds=_this.get_selected(2);
					if(_this.settings.preserveSelectionOnMove) $selecteds.clone().appendTo(_this.elements.select1).removeAttr('selected');
					else $selecteds.remove().appendTo(_this.elements.select1).removeAttr('selected');
					_this.rebuild();
					_this.element.change();
				});
				this.elements.removeButton.click(function(e){
					var $selecteds=_this.get_selected(1);
					if(_this.settings.preserveSelectionOnMove) $selecteds.remove();
					else $selecteds.remove().appendTo(_this.elements.select2).removeAttr('selected');
					_this.rebuild();
					_this.element.change();
				});
				this.elements.moveAllButton.click(function(e){
					_this.get_visible(2).remove().appendTo(_this.elements.select1).removeAttr('selected');
					_this.rebuild();
					_this.element.change();
				});
				this.elements.removeAllButton.click(function(e){
					_this.get_visible(1).remove().appendTo(_this.elements.select2).removeAttr('selected');
					_this.rebuild();
					_this.element.change();
				});
				this.elements.orderUpButton.click(function(e){
					_this.get_selected(1).each(function(){
						var $op=$(this);
						$op.first().prev().before($op);
					});
					_this.rebuild();
				});
				this.elements.orderDownButton.click(function(e){
					_this.get_selected(1).each(function(){
						var $op=$(this);
						$op.last().next().after($op);
					});
					_this.rebuild();
				});
				this.elements.filterClear1.click(function(e){
					_this.elements.filterInput1.val('')
					_this.rebuild();
				});
				this.elements.filterClear2.click(function(e){
					_this.elements.filterInput2.val('')
					_this.rebuild();
				});
				this.elements.filterInput1.keyup(function(e){
					_this.rebuild();
				});
				this.elements.filterInput2.keyup(function(e){
					_this.rebuild();
				});
			}
			return this;
		},
		disabled: function(value){
			var e=this.elements;
			
			e.filterInput1.prop('disabled',value);
			e.filterInput2.prop('disabled',value);
			e.filterClear1.prop('disabled',value);
			e.filterClear2.prop('disabled',value);
			e.select1.prop('disabled',value);
			e.select2.prop('disabled',value);
			
			if(value){
				e.moveButton.addClass('disabled');
				e.removeButton.addClass('disabled');
				e.moveAllButton.addClass('disabled');
				e.removeAllButton.addClass('disabled');
				e.orderUpButton.addClass('disabled');
				e.orderDownButton.addClass('disabled');
			}
			else this.rebuild();
			
			return this;
		},
		get_visible: function(num){
			return $('option',this.elements['select'+num]).filter(function(){ return $(this).css('display')!='none'; });
		},
		get_selected: function(num){
			return this.get_visible(num).filter(function(){ return $(this).is(':selected'); });
		},
		rebuild: function(){
			var e=this.elements;
			//console.log(['orderDownButton',this,e,$(this)])
			//sort
			if(this.settings.sortBox)  {
				this.sort($('option',e.select1));
				this.sort($('option',e.select2));
			}
			else if(this.settings.sortBoxFrom) this.sort($('option',e.select2));
			//alert(this.elements.select2.length);
			//filter
			this.filter(1).filter(2);
			
			//Disable / enable buttons
			var $visible1=this.get_visible(1);
			var $visible2=this.get_visible(2);
			var $select1=this.get_selected(1);
			var $select2=this.get_selected(2);
			
			if($select1.length) {
				e.removeButton.removeClass('disabled');
				if($visible1.first().prop('selected')) e.orderUpButton.addClass('disabled');
				else e.orderUpButton.removeClass('disabled');
				if($visible1.last().prop('selected')) e.orderDownButton.addClass('disabled');
				else e.orderDownButton.removeClass('disabled');
			}
			else {
				e.removeButton.addClass('disabled');
				e.orderUpButton.addClass('disabled');
				e.orderDownButton.addClass('disabled');
			}
			//alert($('option').filter('[visible]').length);
			if($select2.length) e.moveButton.removeClass('disabled');
			else e.moveButton.addClass('disabled');
			
			if($visible1.length) e.removeAllButton.removeClass('disabled');
			else e.removeAllButton.addClass('disabled');
			if($visible2.length) e.moveAllButton.removeClass('disabled');
			else e.moveAllButton.addClass('disabled');

			//info {count} from {total}/{selected}
			this.info(1).info(2);
			
			//duplicate info to select
			$('option',this.element).remove();
			this.element.append($('option',e.select1).clone());
			$('option',this.element).prop('selected',true).show();
			//e.select1.change();
			return this;
		},
		sort: function($options){
			var arr = $options.map(function(_, o) { return { t: $(o).text(), v: o.value }; }).get();
			arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
			$options.each(function(i, o) {
				o.value = arr[i].v;
				$(o).text(arr[i].t);
			});
			return this;
		},
		info: function(nBox){
			var $info=this.elements['info'+nBox];
			var $select=this.elements['select'+nBox];
			
			var total=$('option',$select).length;
			if(total) {
				var $filterInput=this.elements['filterInput'+nBox];
				var selected=$('option:selected',$select).length;
				var count=$('option:visible',$select).length;
				
				var txt=this.txt(count==total?'infoText_all':'infoText_filtered').replace(/\{total\}/ig,total).replace(/\{count\}/ig,count).replace(/\{selected\}/ig,selected);
			} else var txt=this.txt('infoText_empty');
			
			$info.html(txt);
			return this;
		},
		filter: function(nBox){
			var filter=this.elements['filterInput'+nBox].val();
			if(filter=='') $('option',this.elements['select'+nBox]).show();
			else {
				var _this=this;
				//alert(_this.settings.filterOn)
				//var n=0;
				try {
					var erFilter=filter==''?/./:new RegExp(filter,'i');
					$('option',this.elements['select'+nBox]).each(function(){
						var $this=$(this);
						//var value=_this.settings.filterOn=='text'?$this.text():$this.val();
						//if(n++==0) alert([value,erFilter.test(value)]);
						//$this[_this.settings.filterOn]()
						if(erFilter.test($this[_this.settings.filterOn]())) $this.show();
						else $this.hide();
					});
				}
				catch(err) {
				}
			}
			return this;
		},
		showFilter: function(){
			this.elements.filterInput1.parent().show();
			this.elements.filterInput2.parent().show();
		},
		hideFilter: function(){
			this.elements.filterInput1.parent().hide();
			this.elements.filterInput2.parent().hide();
		},
		showButtons: function(){
			this.elements.moveButton.parent().parent().show();
			var c='col-xs-12 col-sm-12 col-md-5 col-lg-5';
			this.elements.box1.attr('class',c);
			this.elements.box2.attr('class',c);
		},
		hideButtons: function(){
			this.elements.moveButton.parent().parent().hide();
			var c='col-xs-12 col-sm-12 col-md-6 col-lg-6';
			this.elements.box1.attr('class',c);
			this.elements.box2.attr('class',c);
		},
		txt:function(key){
			if(this.settings[key]!=null) return this.settings[key];
			var l=this.settings.locale in this.settings.locales?this.settings.locale:'en';
			return this.settings.locales[l][key];
		},
		destroy: function() {
			this.container.remove();
			this.element.show();
			$.data(this, 'plugin_' + pluginName, null);
			return this.element;
		}
	};
	
	$.fn.list2List = function(options) {
		//console.log(this)
		var args = arguments;
		var typeOptions=typeof options;
		
		if (options === undefined || typeOptions === 'object') {
			return this.each(function () {
				if (!$(this).is('select')) {
					$(this).find('select').each(function(index, item) {
						$(item).list2List(options);
					});
				}
				else if (!$.data(this, 'plugin_' + pluginName)) {
					$.data(this, 'plugin_' + pluginName, new List2List(this, options));
				}
			});
		}
		else if (typeOptions === 'string' && options[0] !== '_' && options !== 'init') {
			var returns;
			this.each(function () {
				var instance = $.data(this, 'plugin_' + pluginName);
				if (instance instanceof List2List) {
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
	$('[data-element="list2list"]').list2List();
});