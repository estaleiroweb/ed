;(function ($, window, document, undefined) {
	function ElementCalendar(element, options) {
		$.Element.call(this,element, options); // call super constructor.
	}
	ElementCalendar.extends($.Element).jQuery();

	ElementCalendar.dataObjs={
		format:null,                                               //string  - false; See momentjs' docs for valid formats. Format also dictates what components are shown, e.g. MM/dd/YYYY will not display the time picker.
		dayViewHeaderFormat:'MMMM YYYY',                           //string  - 'MMMM YYYY'; Changes the heading of the datepicker when in "days" view.
		extraFormats:['DD/MM/YY HH:mm:ss','DD/MM/YYYY HH:mm:ss'],  //object  - false; Allows for several input formats to be valid. 
		showTodayButton: true,                                     //boolean - Show the "Today" button in the icon toolbar. Clicking the "Today" button will set the calendar view and set the date to now.
		showClear: null,                                           //boolean - Show the "Clear" button in the icon toolbar. Clicking the "Clear" button will set the calendar to null.
		allowInputToggle: true,                                    //boolean - If true, the picker will show on textbox focus and icon click when used in a button group
		calendarWeeks: true,                                       //boolean - Shows the week of the year to the left of first day of the week.
		debug:false,                                               //boolean - Will cause the date picker to stay open after a blur event.

		stepping:null,                                             //number  - Number of minutes the up/down arrow's will move the minutes value in the time picker
		minDate:null,                                              //string  - Prevents date/time selections before this date. minDate will override defaultDate and useCurrent if either of these settings are the same day since both options are invalid according to the rules you've selected.
		maxDate:null,                                              //string  - Prevents date/time selections after this date. maxDate will override defaultDate and useCurrent if either of these settings are the same day since both options are invalid according to the rules you've selected.
		useCurrent:null,                                           //boolean - On show, will set the picker to the current date/time
		collapse:null,                                             //boolean - Using a Bootstraps collapse to switch between date/time pickers.
		locale:null,                                               //string  - See momentjs' docs for valid formats. Format also dictates what components are shown, e.g. MM/dd/YYYY will not display the time picker
		defaultDate:null,                                          //string  - Sets the picker default date/time. Overrides useCurrent
		disabledDates:null,                                        //object  - Disables selection of dates in the array, e.g. holidays ['06/05/2016', '21/05/2016']
		enabledDates:null,                                         //object  - Disables selection of dates NOT in the array, e.g. holidays
		icons:null,                                                //object  - Change the default icons for the pickers functions
		useStrict:null,                                            //boolean - Defines if moment should use strict date parsing when considering a date to be valid
		sideBySide:null,                                           //boolean - Shows the picker side by side when using the time and date together
		daysOfWeekDisabled:null,                                   //object  - Disables the section of days of the week, e.g. weekends (Accepts: array of numbers from 0-6)
		viewMode:null,                                             //string  - The default view to display when the picker is shown. Note: To limit the picker to selecting, for instance the year and month, use format: MM/YYYY (Accepts: 'decades','years','months','days')
		toolbarPlacement:null,                                     //string  - Changes the placement of the icon toolbar (Default: 'default' Accepts: 'default', 'top', 'bottom')
		showClose:null,                                            //boolean - Show the "Close" button in the icon toolbar. Clicking the "Close" button will call hide()
		widgetPositioning:null,                                    //object  - Default: {horizontal: 'auto',vertical: 'auto'} Accepts: object with the all or one of the parameters above {horizontal: 'auto', 'left', 'right', vertical: 'auto', 'top', 'bottom' }
		widgetParent:null,                                         //object  - On picker show, places the widget at the identifier (string) or jQuery object if the element has css position: 'relative'
		keepOpen:null,                                             //boolean - Will cause the date picker to stay open after selecting a date if no time components are being used.
		inline:null,                                               //boolean - Will display the picker inline without the need of a input field. This will also hide borders and shadows
		keepInvalid:null,                                          //boolean - Will cause the date picker to not revert or overwrite invalid dates
		keyBinds:null,                                             //object  - Allows for custom events to fire on keyboard press
		ignoreReadonly:null,                                       //boolean - Allow date picker show event to fire even when the associated input element has the readonly="readonly"property.
		disabledTimeIntervals:null,                                //object  - Disables time selection between the given moments .disabledTimeIntervals=[ [moment().hour(0).minutes(0), moment().hour(8).minutes(30)],[moment().hour(20).minutes(30), moment().hour(24).minutes(0)] ];
		focusOnShow:null,                                          //boolean - If false, the textbox will not be given focus when the picker is shown
		enabledHours:null,                                         //object  - Will allow or disallow hour selections (much like disabledTimeIntervals) but will affect all days
		disabledHours:null,                                        //object  - Will allow or disallow hour selections (much like disabledTimeIntervals) but will affect all days
		viewDate:null,                                             //string  - This will change the viewDate without changing or setting the selected date

		tooltips: {
			today: 'Hoje',
			clear: 'Limpa seleção',
			close: 'Fecha calendário',
			pickHour: 'Escolhe hora',
			pickMinute: 'Escolhe minuto',
			pickSecond: 'Escolhe segundo',
			incrementHour: 'Incrementa hora',
			incrementMinute: 'Incrementa minuto',
			incrementSecond: 'Incrementa segundo',
			decrementHour: 'Decrementa hora',
			decrementMinute: 'Decrementa minuto',
			decrementSecond: 'Decrementa segundo',
			selectTime: 'Seleciona hora',
			selectMonth: 'Seleciona mês',
			selectYear: 'Seleciona ano',
			selectDecade: 'Seleciona década',
			prevMonth: 'Mês anterior',
			prevYear: 'Ano anterior',
			prevDecade: 'Década anterior',
			prevCentury: 'Século anterior',
			nextMonth: 'Mês seguinte',
			nextYear: 'Ano seguinte',
			nextDecade: 'Década seguinte',
			nextCentury: 'Século seguinte'
		}
	};

	ElementCalendar.defaults=$.extend({}, $.Element.defaults, {
		'type': 'datetime',
		'mode': 'component',
		displayformat:'%d/%m/%Y %T',         //string  - see format
		inputformat:'%F',                    //string  - see format
		formatI:null,                        //string  - see format
	});
	ElementCalendar.defaults=$.extend({}, $.ElementCalendar.defaults, ElementCalendar.dataObjs);
	
	ElementCalendar.eReg=[
		[/%%/g,String.fromCharCode(0)],
		[/%t/g,'\t'],        //caracter tab
		[/%n/g,'\n'],        //caracter novalinha
		[/%h/g,'%b'],        //mesmo que %b
		[/%x/g,'%c'],        //representação preferida para a data para a localidade corrente sem a hora (ex: 12/31/99)
		[/%X/g,'%T'],        //representação preferida para a hora para a localidade corrente sem a data (ex: 23:13:48)
		[/%F/g,'%Y-%m-%d'],  //data cheia; o mesmo que %Y-%m-%d
		[/%T/g,'%H:%M:%S'],  //hora corrente, igual a %H:%M:%S
		[/%D/g,'%m/%d/%y'],  //mesmo que %m/%d/%y 
		[/%c/g,'%d/%m/%Y'],  //representação da data e hora preferida pela a localidade
		[/%R/g,'%H:%M'],     //hora em notação de 24 horas
		[/%r/g,'%I:%M:%S%p'],//hora em a.m. e p.m. notação
		
		[/%e/g,'D'],         //dia do mês como um número decimal, um simples dígito é precedido por espaço (de ' 1' até '31')
		[/%d/g,'DD'],        //dia do mês como um número decimal (de 01 até 31)
		[/%j/g,'DDD'],       //dia do ano como número decimal (de 001 até 366)
		[/%a/g,'ddd'],       //dia da semana abreviado de acordo com a localidade
		[/%A/g,'dddd'],      //nome da semana completo de acordo com a localidade
		[/%m/g,'MM'],        //mês como número decimal (de 01 até 12)
		[/%b/g,'MMM'],       //nome do mês abreviado de acordo com a localidade
		[/%B/g,'MMMM'],      //nome do mês completo de acordo com a localidade
		[/%y/g,'YY'],        //ano como número decimal sem o século (de 00 até 99)
		[/%Y/g,'YYYY'],      //ano como número decimal incluindo o século
		[/%g/g,'GG'],        //como %G, mas sem o século.
		[/%G/g,'GGGG'],      //o 4-dígito do ano correspodendo as ISO week number (see %V). Este tem o mesmo formato e valor que %Y, exceto que se o ISO week number pertence ao prévio ou próximo ano, aquele ano é usado ao invés deste.
		//[/%C/g,'??'],        //número do século (o ano dividido por 100 e truncado para um inteiro, de 00 até 99)
		
		[/%u/g,'E'],         //dia da semana como número decimal [1,7], com 1 representando Segunda-feira
		[/%w/g,'e'],         //dia da semana como número decimal, domingo sendo 0 (0..6)
		[/%U/g,'ww'],        //dia da semana do ano corrente como número decimal, começando com o primeiro domingo como o primeiro dia da primeira semana  
		[/%V/g,'W'],         //O número da semana corrente ISO 8601:1988 do ano corrente como um número decimal, de 01 até 53, onde semana 1 é a primeira semana que tem pelo menos 4 dias no ano corrente, e com segunda-feira como o primeiro dia da semana. (Use %G ou %g para o componente anual que corresponde ao dia da semana para o para o timestamp especificado.)
		[/%W/g,'WW'],        //dia da semana do ano corrente como número decimal, começando com o a segunda-feira como o primeiro dia da primera semana
		
		[/%k/g,'H'],         //hora ( 0..23)
		[/%l/g,'h'],         //hora ( 1..12)
		[/%H/g,'HH'],        //hora como um número decimal usando um relógio de 24-horas (de 00 até 23)
		[/%I/g,'hh'],        //hora como um número decimal usando um relógio de 12-hoas (de 01 até 12)
		[/%M/g,'mm'],        //minuto como número decimal
		[/%S/g,'ss'],        //segundo como um número decimal
		[/%N/g,'SSS'],       //nanoseconds (000000000..999999999) | Fractional seconds
		[/%p/g,'a'],         //um dos dois `AM' ou `PM' de acordo com o valor da hora dada, ou as strings correspondentes para a localidade
		[/%P/g,'A'],         //um dos dois `am' ou `pm' de acordo com o valor da hora dada, ou as strings correspondentes para a localidade
		[/%s/g,'x'],         //Unix ms timestamp
		
		[/%z/g,'ZZ'],        //+hhmm numeric time zone (e.g., -0400)
		[/%:z/g,'Z'],        //+hh:mm numeric time zone (e.g., -04:00)
		[/%::z/g,'Z'],       //+hh:mm:ss numeric time zone (e.g., -04:00:00)
		[/%:::z/g,'Z'],      //numeric time zone with : to necessary precision (e.g., -04, +05:30)
		[/%Z/g,'Z'],         //alphabetic time zone abbreviation (e.g., EDT)
		
		[/\x00/g,'%']
	];
	ElementCalendar.prototype.init=function(){
		//this.dad('init'); //parent().init();
		parent.this();
			
		switch(this.settings.type){
			case 'date':
				var displayformat='%d/%m/%Y';
				var inputformat='%F';
				var dayViewHeaderFormat='MMMM YYYY';
				var extraFormats=['DD/MM/YY', 'DD/MM/YYYY'];
				break;
			case 'time':
				var displayformat='%T';
				var inputformat='%T';
				var dayViewHeaderFormat='HH:mm';
				var extraFormats=['HH:mm', 'HH:mm:ss'];
				break;
			case 'year':
				var displayformat='%Y';
				var inputformat='%Y';
				var dayViewHeaderFormat='YYYY';
				var extraFormats=['YY', 'YYYY'];
				break;
			case 'datetime':
				var displayformat='%d/%m/%Y %T';
				var inputformat='%F %T';
				var dayViewHeaderFormat='MMMM YYYY'; 
				var extraFormats=['DD/MM/YY HH:mm:ss','DD/MM/YYYY HH:mm:ss']; 
				break;
			default:
				var displayformat='%d/%m/%Y %T';
				var inputformat='%F %T';
				var dayViewHeaderFormat='MMMM YYYY'; 
				var extraFormats=['DD/MM/YY HH:mm:ss','DD/MM/YYYY HH:mm:ss']; 
		}
		this.rebuildSettings({
			'showClear':this.settings.required,
			'displayformat':displayformat,
			'inputformat':inputformat,
			'dayViewHeaderFormat':dayViewHeaderFormat,
			'extraFormats':extraFormats
		});
		if(this.settings.format==null) this.settings.format=this.translateFormat(this.settings.displayformat);
		if(this.settings.formatI==null) this.settings.formatI=this.translateFormat(this.settings.inputformat);
		this.opt={}
		for(var i in $.ElementCalendar.dataObjs) if(this.settings[i]!=null) this.opt[i]=this.settings[i];
		//console.log(this.opt);
		//console.log(this.settings);
	}
	ElementCalendar.prototype.init_elements=function() {
		//this.dad('init_elements'); //parent().init_elements();
		parent.this();
		
		this.elements={
			oDsp:this.element.find('input[type="text"]'),
			oInp:this.element.find('input[type="hidden"]'),
			//oDsp:this.element.find('[displayformat]'),
			//oInp:this.element.find('[inputformat]'),
		};
		//if(!this.settings.defaultDate) this.settings.defaultDate=this.elements.oInp.val();
		var dt=this.elements.oInp.val();
		//console.log(this.elements);
		//console.log([dt,this.elements.oInp,this.settings.formatI]);
		if(!this.opt.defaultDate && dt) this.opt.date=this.opt.defaultDate=moment(dt,this.settings.formatI);
		this.settings.label=this.elements.oDsp.attr('label');
		this.settings.title=this.elements.oDsp.attr('title');
		return this; 
		//this.addElements=function(selector,element_name,base)
	}
	ElementCalendar.prototype.init_done=function(){
		//console.log(this.elements);
		if(this.elements.oDsp.length==0) return false;
		var _this=this;
			
		this.elements.oDsp.each(function(idx,obj){
			var $oDsp=$(this);
			var id=$oDsp.attr('id').replace(/^d_/,'');
			
			$oDsp.attr('placeholder',_this.settings.format);
			//console.log(_this.opt);
			$oDsp.datetimepicker(_this.opt);
			//return
				
			//var $oInp=_this.element.find('#i_'+id);
			var $oInp=_this.elements.oInp;
			if($oInp.length>0) {
				//$oInp.data('format',_this.settings.formatI);
				$oDsp.data('input',$oInp);
				$oDsp.on('dp.change', function (e) {
					var $oDsp=$(this);
					var $oInp=$oDsp.data('input');
					var dt=$oDsp.data('DateTimePicker').date();
					$oInp.val(dt==null?'':dt.format(_this.settings.formatI));
					//console.log([_this.settings.formatI,dt.format(_this.settings.formatI)]);
				});
			}
			_this.element.find('#b_'+id).click(function(e){
				$oDsp.data('DateTimePicker').toggle();
			});
		});

		if(this.elements.oDsp.length==2){
			var $oDsp_from=$($oDsp[0]);
			var $oDsp_to=$($oDsp[1]);
			$oDsp_to.data('DateTimePicker').options({useCurrent: false});//Important! See issue #1075

			$oDsp_from.on('dp.change', function (e) {
				$oDsp_to.data('DateTimePicker').minDate(e.date);
			});
			$oDsp_to.on('dp.change', function (e) {
				$oDsp_from.data('DateTimePicker').maxDate(e.date);
			});
		}
		//console.log(11);
	}
	ElementCalendar.prototype.translateFormat=function(format){
		for(var i in $.ElementCalendar.eReg) format=format.replace($.ElementCalendar.eReg[i][0],$.ElementCalendar.eReg[i][1]);
		return format;
	}
	ElementCalendar.prototype.getValue=function(i,format){
		if(!format) format=this.settings.formatI;
		if(i==undefined || i==null) {
			var out=[];
			for(var i=0;i<this.elements.oDsp.length;i++){
				var v=this.getValue(i,format);
				if(v!=null) out.push(v);
			}
			return out.join('~');
		}
		else {
			if(!this.elements.oDsp[i]) return null;
			var $oDsp=$(this.elements.oDsp[i]);
			var dp=$oDsp.data('DateTimePicker');
			if(dp===undefined || dp===null) return null;
			var dt=dp.date();
			if(dt==null) return null;
			return dt.format(format);
		}
	};
	ElementCalendar.prototype.setValue=function(v,i,format){
		if(!format) format=this.settings.formatI;
		if(i==undefined || i==null) {
			v=v.split('~');
			for(var i=0;i<this.elements.oDsp.length;i++) this.setValue(v[i],i,format);
		}
		else if((/~/).test(v)) this.setValue(v,null,format);
		else if(this.elements.oDsp[i]) {
			var $oDsp=$(this.elements.oDsp[i]);
			var dp=$oDsp.data('DateTimePicker');
			if(dp===undefined || dp===null) return this;
			dp.date(moment(v,format));
		}
		return this;
	};
})(jQuery, window, document);
