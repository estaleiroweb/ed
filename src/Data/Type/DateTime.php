<?php
namespace Type;

class DateTime extends Mixed implements \Interfaces\Type {
	protected static $erLocaleFormat='';
	protected $ts=0;
	protected $date='0000-00-00';
	protected $time='00:00:00';
	protected $nanoseconds='000000000';
	protected $separator=' ';
	protected $tz='+0000';
	protected $tzStr='UTC';
	
	protected $mask='%F %T.%N';
	protected $maskDefault='%F %T.%N';
	private $lit_percent='%';
	//private $lit_percent='%';

	protected function _cast($value){
		if($this->isNull) return $this->null(); 
		switch($this->type){
			case 'float': case 'double': case 'number': case 'integer': $this->ts($value); return $this->value;
			case 'string': 
				if(strtolower($value)=='now') return $this->now();
				break;
			default:
				$o=new String($value);
				$value=$o->value();
		}
		$ano='(?<y>\d{2,4})(?:\s*[ya]\s*|-)';
		$mes='(?<m>\d{1,2})(?:\s*m\s*|-)';
		$dia='(?<d>\d{1,2})(?:\s*d\s*)?';
		$sep='(?<sep>[ T])?';
		$hor='(?<H>\d{1,2})(?:\s*h\s*|:)';
		$min='(?<M>\d{1,2})(?:\s*m(?:in)?\s*|:)';
		$sec='(?:(?<S>\d{1,2})(?:\s*s(?:ec)?\s*)?)?';
		$nan='(?:[\.,](?<N>\d+))?';
		$tz='(?:[+-](?<Z>\d+))?';
		$date=$ano.$mes.$dia;
		$time=$hor.'(?:'.$min.$sec.')?'.$tz;
		$er="/\b(?:$date)?$sep($time)?$nan\b/i";

		print_r(['======',$value]);
		if(preg_match($er,$value,$res)) {
			print_r($res);
		}
		//if($this->type=='string') return $value;
		//if($this->type=='array') return var_export($value,true);
		//if($this->type=='object') return var_export($value,true);
		return (string)$value;
	}
	protected function rebuild(){
		return $this->value=$this->date.$this->separator.$this->time.'.'.$this->nanoseconds;
	}
	final private function number2TsNano($value){
		return strripos($value,'E')===false?$value:number_format($value, 9, '.', '');
	}
	final private function getNano($tsNano){
		if(preg_match('/\.(\d+)/',$tsNano,$res)) return $res[1];
		return 0;
	}
	final private function str2DateTime($value){
		//1972-09-24T20:02:00.000000PM-0500
		//10hs:20mim:30sec
		//strftime('%x', strtotime('1972-02-20')) => 02/20/72
/*
General date syntax: Common rules. 
Calendar date items: 19 Dec 1994. 
Time of day items: 9:20pm. 
Time zone items: EST, PDT, UTC, ... 
Combined date and time of day items: . 
Day of week items: Monday and others. 
Relative items in date strings: next tuesday, 2 years ago. 
Pure numbers in date strings: 19931219, 1440. 
Seconds since the Epoch: @1078100502. 
Specifying time zone rules: TZ="America/New_York", TZ="UTC0". 
Authors of parse_datetime: Bellovin, Eggert, Salz, Berets, et al. 

*/
		$ano='(?<y>\d{2}|\d{4})(?:\s*[ya]\s*|-)';
		$mes='(?<m>\d{1,2})(?:\s*m\s*|-)';
		$dia='(?<d>\d{1,2})(?:\s*d\s*)?';
		$sep='(?<sep>[ T])?';
		$hor='(?<H>\d{1,2})(?:\s*h\s*|:)';
		$min='(?<M>\d{1,2})(?:\s*m(?:in)?\s*|:)';
		$sec='(?:(?<S>\d{1,2})(?:\s*s(?:ec)?\s*)?)?';
		$nan='(?:[\.,](?<N>\d+))?';
		$tz='(?:[+-](?<Z>\d+))?';
		$date=$ano.$mes.$dia;
		$time=$hor.'(?:'.$min.$sec.')?'.$tz;
		$er="/\b(?:$date)?$sep($time)?$nan\b/i";

	}
	final public function ts($value=null){
		if(is_bool($value)) {
			$this->dateTime($value);
			$this->nanoseconds($value);
		}
		elseif(!is_null($value)) {
			$this->dateTime($this->number2TsNano($value));
		}
		return $this->ts;
	}
	final public function date($value=null){
		if($value===true) $value=time();
		elseif($value===false) $value=0;
		if(!is_null($value)) {
			if(preg_match('/\.(\d+)/',$value,$res)) $this->nanoseconds($res[1]);
			if(!is_numeric($value)) $value=strtotime($value);
			$this->date=strftime('%F',$value);
			$this->ts=strtotime($this->date .' '. $this->time);
			$this->rebuild();
		}
		return $this->date;
	}
	final public function time($value=null){
		if($value===true) $value=time();
		elseif($value===false) $value=0;
		if(!is_null($value)) {
			if(preg_match('/\.(\d+)/',$value,$res)) $this->nanoseconds($res[1]);
			else $this->nanoseconds(0);
			if(!is_numeric($value)) $value=strtotime($value);
			$this->time=strftime('%T',$value);
			$this->ts=strtotime($this->date .' '. $this->time);
			$this->rebuild();
		}
		return $this->time;
	}
	final public function dateTime($value=null){
		if($value===true) $value=time();
		elseif($value===false) $value=0;
		if(!is_null($value)) {
			$this->nanoseconds($this->getNano($value));
			if(!is_numeric($value)) $value=strtotime($value);
			$this->ts=$value;
			$p=explode(' ',strftime('%F %T',$value));
			$this->date=$p[0];
			$this->time=$p[1];
			$this->rebuild();
		}
		return $this->date.$this->separator.$this->time;
	}
	final public function nanoseconds($value=null){
		if($value===true) {
			$t=explode(' ',microtime());
			$value=$t[0];
		}
		if(!is_null($value)) {
			$this->nanoseconds=str_pad($value,9,0,STR_PAD_LEFT);
			$this->ts=((int)$this->ts).'.'.$this->nanoseconds;
			$this->rebuild();
		}
		return $this->nanoseconds;
	}
	final public function microseconds($value=null){ return $this->nanoseconds($value); }
	final public function separator($value=null){
		if($value===true) $value=' ';
		elseif($value===false) $value='T';
		if(!is_null($value)) {
			$this->separator=$value;
			$this->rebuild();
		}
		return $this->separator;
	}
	final public function mask($value=null){
		if($value===true) $value=$this->maskDefault;
		if(!is_null($value)) {
			$this->mask=$value;
			$this->rebuild();
		}
		return $this->mask;
	}
	final public function now(){
		$this->ts(true);
		return $this->value;
	}
	final public function null(){
		$this->ts(false);
		return $this->value;
	}
	final public function mskHelp($value=null,$lang=0){
		static $relation=[
			'%%'=>    ['Literal',   'a literal %','a literal "%" character',],
			'%n'=>    ['Literal',   'a newline','caracter novalinha',],
			'%t'=>    ['Literal',   'a tab','caracter tab',],
			
			'%a'=>    ['Date.W',    'locale\'s abbreviated weekday name (e.g., Sun)','dia da semana abreviado de acordo com a localidade',],
			'%A'=>    ['Date.W',    'locale\'s full weekday name (e.g., Sunday)','nome da semana completo de acordo com a localidade',],
			'%u'=>    ['Date.W',    'day of week (1..7); 1 is Monday','dia da semana como número decimal [1,7], com 1 representando Segunda-feira',],
			'%w'=>    ['Date.W',    'day of week (0..6); 0 is Sunday','dia da semana como número decimal, domingo sendo 0',],
			'%U'=>    ['Date.W',    'week number of year, with Sunday as first day of week (00..53)','dia da semana do ano corrente como número decimal, começando com o primeiro domingo como o primeiro dia da primeira semana',],
			'%W'=>    ['Date.W',    'week number of year, with Monday as first day of week (00..53)','dia da semana do ano corrente como número decimal, começando com o a segunda-feira como o primeiro dia da primera semana',],
			'%V'=>    ['Date.W',    'ISO week number, with Monday as first day of week (01..53)','O número da semana corrente ISO 8601:1988 do ano corrente como um número decimal, de 01 até 53, onde semana 1 é a primeira semana que tem pelo menos 4 dias no ano corrente, e com segunda-feira como o primeiro dia da semana. (Use %G ou %g para o componente anual que corresponde ao dia da semana para o para o timestamp especificado.)',],
			'%d'=>    ['Date.D',    'day of month (e.g., 01)','dia do mês como um número decimal (de 01 até 31)',],
			'%e'=>    ['Date.D',    'day of month, space padded; same as %_d','dia do mês como um número decimal, um simples dígito é precedido por espaço (de 1 até 31)',],
			'%j'=>    ['Date.D',    'day of year (001..366)','dia do ano como número decimal (de 001 até 366)',],
			'%b'=>    ['Date.M',    'locale\'s abbreviated month name (e.g., Jan)','nome do mês abreviado de acordo com a localidade',],
			'%B'=>    ['Date.M',    'locale\'s full month name (e.g., January)','nome do mês completo de acordo com a localidade',],
			'%h'=>    ['Date.M',    'same as %b','mesmo que %b',],
			'%m'=>    ['Date.M',    'month (01..12)','mês como número decimal (de 01 até 12)',],
			'%g'=>    ['Date.Y',    'last two digits of year of ISO week number (see %G)','como %G, mas sem o século.',],
			'%G'=>    ['Date.Y',    'year of ISO week number (see %V); normally useful only with %V','o 4-dígito do ano correspodendo as ISO week number (see %V). Este tem o mesmo formato e valor que %Y, exceto que se o ISO week number pertence ao prévio ou próximo ano, aquele ano é usado ao invés deste.',],
			'%y'=>    ['Date.Y',    'last two digits of year (00..99)','ano como número decimal sem o século (de 00 até 99)',],
			'%Y'=>    ['Date.Y',    'year','ano como número decimal incluindo o século',],
			'%C'=>    ['Date.C',    'century; like %Y, except omit last two digits (e.g., 20)','número do século (o ano dividido por 100 e truncado para um inteiro, de 00 até 99)',],
			'%c'=>    ['Date',      'locale\'s date and time (e.g., Thu Mar  3 23:05:25 2005)','representação da data e hora preferida pela a localidade',],
			'%D'=>    ['Date',      'date; same as %m/%d/%y','mesmo que %m/%d/%y',],
			'%F'=>    ['Date',      'full date; same as %Y-%m-%d',],
			'%x'=>    ['Date',      'locale\'s date representation (e.g., 12/31/99)','representação preferida para a data para a localidade corrente sem a hora',],
			
			//Time:
			'%H'=>    ['Time.H',    'hour (00..23)','hora como um número decimal usando um relógio de 24-horas (de 00 até 23)',],
			'%I'=>    ['Time.H',    'hour (01..12)','hora como um número decimal usando um relógio de 12-hoas (de 01 até 12)',],
			'%k'=>    ['Time.H',    'hour ( 0..23)','hora ( 0..23)',],
			'%l'=>    ['Time.H',    'hour ( 1..12)','hora ( 1..12)',],
			'%M'=>    ['Time.M',    'minute (00..59)','minuto como número decimal',],
			'%N'=>    ['Time.N',    'nanoseconds (000000000..999999999)',],
			'%p'=>    ['Time.AMPM', 'locale\'s equivalent of either AM or PM; blank if not known','AM/PM',],
			'%P'=>    ['Time.AMPM', 'like %p, but lower case','um dos dois am ou pm de acordo com o valor da hora dada, ou as strings correspondentes para a localidade',],
			'%S'=>    ['Time.S',    'second (00..60)','segundo como um número decimal',],
			'%r'=>    ['Time',      'locale\'s 12-hour clock time (e.g., 11:11:04 PM)','hora em a.m. e p.m. notação',],
			'%R'=>    ['Time',      '24-hour hour and minute; same as %H:%M','hora em notação de 24 horas',],
			'%T'=>    ['Time',      'time; same as %H:%M:%S','hora corrente, igual a %H:%M:%S',],
			'%X'=>    ['Time',      'locale\'s time representation (e.g., 23:13:48)','representação preferida para a hora para a localidade corrente sem a data',],
			
			'%s'=>    ['Timestamp', 'seconds since 1970-01-01 00:00:00 UTC','segundos desde 1970-01-01 00:00:00 UTC',],
			
			'%z'=>    ['TimeZone',  '+hhmm numeric time zone (e.g., -0400)','time zone'],
			'%:::z'=> ['TimeZone',  'numeric time zone with : to necessary precision (e.g., -04, +05:30)',],
			'%::z'=>  ['TimeZone',  '+hh:mm:ss numeric time zone (e.g., -04:00:00)',],
			'%:z'=>   ['TimeZone',  '+hh:mm numeric time zone (e.g., -04:00)',],
			'%Z'=>    ['TimeZone',  'alphabetic time zone abbreviation (e.g., EDT)','nome ou abreviação (dependendo do sistema operacional)',],
		];
		if(is_null($value)) return $relation;
		return @$relation[$value][$lang];
	}
	final public function strftime($format=null,$timestamp=null,$tz=null){
		if(is_null($format))    $format=$this->mask;
		if(is_null($timestamp)) $timestamp=$this->ts;
		if(is_null($tz))        $tz=$this->tz;
		$timestamp=$this->number2TsNano($timestamp);
		$nn=$this->getNano($timestamp);
	}
}