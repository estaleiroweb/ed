$(document).ready(function(){
	$('div[ed-class="ElementCalendar"]') //.each(function(idx,obj){
	.on('ed_init', function(e){
		var $o=$(this);
		var ty=$o.attr('ed-type');
		var md=$o.attr('mode');

		var $oDsp=$o.find('[displayformat]');
		if($oDsp.length==0) return false;
		var $oInp=$o.find('[inputformat]');
		
		var optTypes={
			format:'string',                  //false; See momentjs' docs for valid formats. Format also dictates what components are shown, e.g. MM/dd/YYYY will not display the time picker.
			dayViewHeaderFormat:'string',     //'MMMM YYYY'; Changes the heading of the datepicker when in "days" view.
			extraFormats:'object',            //false; Allows for several input formats to be valid. 
			stepping:'number',                //Number of minutes the up/down arrow's will move the minutes value in the time picker
			minDate:'string',                 //Prevents date/time selections before this date. minDate will override defaultDate and useCurrent if either of these settings are the same day since both options are invalid according to the rules you've selected.
			maxDate:'string',                 //Prevents date/time selections after this date. maxDate will override defaultDate and useCurrent if either of these settings are the same day since both options are invalid according to the rules you've selected.
			useCurrent:'boolean',             //On show, will set the picker to the current date/time
			collapse:'boolean',               //Using a Bootstraps collapse to switch between date/time pickers.
			locale:'string',                  //See momentjs' docs for valid formats. Format also dictates what components are shown, e.g. MM/dd/YYYY will not display the time picker
			defaultDate:'string',             //Sets the picker default date/time. Overrides useCurrent
			disabledDates:'object',           //Disables selection of dates in the array, e.g. holidays ['06/05/2016', '21/05/2016']
			enabledDates:'object',            //Disables selection of dates NOT in the array, e.g. holidays
			icons:'object',                   //Change the default icons for the pickers functions
			useStrict:'boolean',              //Defines if moment should use strict date parsing when considering a date to be valid
			sideBySide:'boolean',             //Shows the picker side by side when using the time and date together
			daysOfWeekDisabled:'object',      //Disables the section of days of the week, e.g. weekends (Accepts: array of numbers from 0-6)
			calendarWeeks:'boolean',          //Shows the week of the year to the left of first day of the week.
			viewMode:'string',                //The default view to display when the picker is shown. Note: To limit the picker to selecting, for instance the year and month, use format: MM/YYYY (Accepts: 'decades','years','months','days')
			toolbarPlacement:'string',        //Changes the placement of the icon toolbar (Default: 'default' Accepts: 'default', 'top', 'bottom')
			showTodayButton:'boolean',        //Show the "Today" button in the icon toolbar. Clicking the "Today" button will set the calendar view and set the date to now.
			showClear:'boolean',              //Show the "Clear" button in the icon toolbar. Clicking the "Clear" button will set the calendar to null.
			showClose:'boolean',              //Show the "Close" button in the icon toolbar. Clicking the "Close" button will call hide()
			widgetPositioning:'object',       //Default: {horizontal: 'auto',vertical: 'auto'} Accepts: object with the all or one of the parameters above {horizontal: 'auto', 'left', 'right', vertical: 'auto', 'top', 'bottom' }
			widgetParent:'object',            //On picker show, places the widget at the identifier (string) or jQuery object if the element has css position: 'relative'
			keepOpen:'boolean',               //Will cause the date picker to stay open after selecting a date if no time components are being used.
			inline:'boolean',                 //Will display the picker inline without the need of a input field. This will also hide borders and shadows
			keepInvalid:'boolean',            //Will cause the date picker to not revert or overwrite invalid dates
			keyBinds:'object',                //Allows for custom events to fire on keyboard press
			debug:'boolean',                  //Will cause the date picker to stay open after a blur event.
			ignoreReadonly:'boolean',         //Allow date picker show event to fire even when the associated input element has the readonly="readonly"property.
			disabledTimeIntervals:'object',   //Disables time selection between the given moments .disabledTimeIntervals=[ [moment().hour(0).minutes(0), moment().hour(8).minutes(30)],[moment().hour(20).minutes(30), moment().hour(24).minutes(0)] ];
			allowInputToggle:'boolean',       //If true, the picker will show on textbox focus and icon click when used in a button group
			focusOnShow:'boolean',            //If false, the textbox will not be given focus when the picker is shown
			enabledHours:'object',            //Will allow or disallow hour selections (much like disabledTimeIntervals) but will affect all days
			disabledHours:'object',           //Will allow or disallow hour selections (much like disabledTimeIntervals) but will affect all days
			viewDate:'string'                 //This will change the viewDate without changing or setting the selected date
		}
		var eReg=[
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
		var translateFormat=function(format){
			for(var i in eReg) format=format.replace(eReg[i][0],eReg[i][1]);
			return format;
		}
		var getAttr=function(attr,typ,opt){
			var value=$oInp.attr(attr);
			if(value===undefined) return false;
			else if(typ=='boolean') value=!(/^(0|false|off|)$/i).test(value);
			else if(typ=='number')  value=Number(value);
			else if(typ=='object')  value=eval('return '+value);
			opt[attr]=value;
		}
			
		$oDsp.each(function(idx,obj){
			var $oDsp=$(this);
			var id=$oDsp.attr('id').replace(/^d_/,'');
			var required=$oDsp.attr('required');

			var opt={
				showTodayButton: true,
				showClear: required===undefined || required=='0' || required=='off' || required=='false',
				allowInputToggle: true,
				calendarWeeks: true,
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
			}
			switch(ty){
				case 'datetime':
					var inputformat='YYYY-MM-DD HH:mm:ss';
					opt.format='DD/MM/YYYY HH:mm:ss';
					opt.dayViewHeaderFormat='MMMM YYYY'; 
					opt.extraFormats=['DD/MM/YY HH:mm:ss','DD/MM/YYYY HH:mm:ss']; 
					break;
				case 'date':
					var inputformat='YYYY-MM-DD';
					opt.format='DD/MM/YYYY';
					opt.dayViewHeaderFormat='MMMM YYYY';
					opt.extraFormats=['DD/MM/YY', 'DD/MM/YYYY'];
					break;
				case 'time':
					var inputformat='HH:mm:ss';
					opt.format='HH:mm:ss';
					opt.dayViewHeaderFormat='HH:mm';
					opt.extraFormats=['HH:mm', 'HH:mm:ss'];
					break;
				case 'year':
					var inputformat='YYYY';
					opt.format='YYYY';
					opt.dayViewHeaderFormat='YYYY';
					opt.extraFormats=['YY', 'YYYY'];
					break;
				default: return false;
			}
			
			opt.format=translateFormat($oDsp.attr('displayformat')) || opt.format;
			$oDsp.attr('placeholder',opt.format);
			for(var i in optTypes) getAttr(i,optTypes[i],opt);
			$oDsp.datetimepicker(opt);
				
			var $oInp=$o.find('#i_'+id);
			if($oInp.length>0) {
				$oInp.data('format',translateFormat($oInp.attr('inputformat')) || inputformat);
				$oDsp.data('input',$oInp);
				$oDsp.on('dp.change', function (e) {
					var $oDsp=$(this);
					var $oInp=$oDsp.data('input');
					var dt=$oDsp.data('DateTimePicker').date();
					$oInp.val(dt==null?'':dt.format($oInp.data('format')));
				});
			}
			$o.find('#b_'+id).click(function(e){
				$oDsp.data('DateTimePicker').toggle();
			});
		});

		if($oDsp.length==2){
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
	})
	.ed_init();

});
