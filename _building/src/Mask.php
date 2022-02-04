<?php
class Mask {
	function str($str='',$mask='',$input=false){
		//09#L?Aa&C<>=\!
		$chars="09#L\\?Aa&C";
		$aTr=array(
		'0'=>'\d','L'=>'[a-zA-Z]','A'=>'[a-zA-Z0-9]','&'=>'.',
		'9'=>'\d?','#'=>'[\d\-\+ ]?','?'=>'[a-zA-Z]?','a'=>'[a-zA-Z0-9]?','C'=>'.?'
		);
		$exp='';
		$parts=preg_split ("/(?:(?<!\\\\);)|(?:(?<=\\\\\\\\);)/", $mask);
		$parts[0]=str_replace("\\\\",chr(1),$parts[0]);
		preg_match_all("/((?<!\\\\)[$chars]+)((?:(?<=\\\\)[$chars]|[^$chars])*)/",$parts[0],$p);
		if ($p[0]) {
			//Separa a mascara
			$p[2]=array_map("Mask::stripSlashes", $p[2]);
			$p[3]=array_map("Mask::addSlashesExpReg", $p[2]);
			foreach($p[1] as $key=>$value)
			$exp.="(".($p[4][$key]=strtr($value,$aTr)).")".(($p[3][$key])?"({$p[3][$key]})?":'');
			preg_match("/$exp/",$str,$p2);
			$str=array_shift($p2);
			foreach ($p[2] as $key=>$value) $p2[$key*2+1]=$value;
			if (!$input || $parts[1]!='') {
				if (!$input && $parts[2]!='') foreach ($p[1] as $key=>$value) {
					$p2[$key*2]=str_pad($p2[$key*2], strlen($value),$parts[2]);
				}
				$str=implode('',$p2);
			}
		}
		return $str;
	}
	function stripSlashes($item) {
		return str_replace(chr(1),"\\",preg_replace("/\\\\([09#L\\?Aa&C])/",'\1',$item));
	}
	function addSlashesExpReg($item) {
		return preg_replace("/([][(){}+*.:|?<>/\\-])/",'\\\1',$item);
	}

	# Retorna um número formatado.
	# -$number: Valor positivo ou negativo, inteiro ou real [requerido]
	# -$mask: Mascara com 5 entradas separadas por ; Ex: 'R$ %s;R$ -%s;0;_;2,.' Default: '%s;-%s'
	#         Para 'sprintf_1;sprintf _2;valorInput;preenchimento;number_format'
	#         sprintf_1: Mascara do formato positivo utilizando padroes de sprintf
	#         sprintf_2: idem sprintf_1 negativo.
	#         valorInput:
	#         number_format:
	# -$input: Se true, o valor de input vai ser retornado, se false o valor de display.
	function number($number,$mask='%s;-%s',$input=false){
		$retorno=$number=(double)$number;
		$parts=preg_split ("/(?<!\\\\);/", $mask);
		$n=abs($number);
		if (isset($parts[3]) && preg_match('/(\d+)(.)?(.)?/',$parts[3],$form)) {
			if (isset($form[2]) && !isset($form[3])) $form[3]='';
			$n=(isset($form[3]))?number_format($n,$form[1],$form[2],$form[3]):number_format($n,$form[1]);
		}
		if (!$input || $parts[2]!='') $retorno=sprintf($parts[($number<0 && $parts[1]!='')?1:0],$n);
		return $retorno;
	}

	# Retorna uma data e/ou hora formatada.
	# -$globalDate: Valor date/time/datetime no formato timestamp [requerido]
	# -$mask: Mascara (strftime) para converter a data default '%x %X'
	# -$alt: Valor alternativo de saida. caso $globalDate seja nulo ou zerado.
	function dateTime($globalDate,$mask=''){
		$alt='';
		if (!$mask) {
			$mask=(strpos($globalDate,'-')===false)?'':'%x';
			$mask.=(strpos($globalDate,':')===false)?'':(($mask)?' ':'').'%X';
		} else {
			$parts=preg_split ("/(?:(?<!\\\\);)|(?:(?<=\\\\\\\\);)/", $mask);
			$mask=str_replace('\;',';',array_shift($parts));
			$alt=str_replace('\;',';',array_shift($parts));
		}
		if ($alt==='') {
			$alt=preg_replace(
			array('/%%/','/%c/','/%x/','/%X/','/%D/','/%F/','/%T/','/%R/','/%Y/','/%y/','/%m/','/%d/','/%e/','/%H/','/%I/','/%M/','/%S/','/%p/','/%r/',"/%\\0/"),
			array("%\0",'%x %X',Mask::getLocaleFormatDate(),Mask::getLocaleFormatTime(),'%m/%d/%y','%Y-%m-%d','%H:%M:%S','%I:%M:%S %p','YYYY','YY','MM','DD','DD','HH','HH','II','SS','ap','a.p.','%'),
			$mask);
		}
		$datetime=strftime ($mask,strtotime($globalDate));
		if (!preg_replace ("/0|-|:| /", '',$globalDate))
		$datetime=($alt)?$alt:preg_replace ("/[1-9]/", '0',$datetime);
		return $datetime;
	}

	# Retona o formato de '%x' date desmenbrado
	public static function getLocaleFormatDate(){
		static $ret='';
		return $ret?$ret:$ret=strtr (
		strftime ('%x',strtotime('2001-02-03')),
		array('2001'=>'%Y','02'=>'%m','03'=>'%d','01'=>'%y','2'=>'%m','3'=>'%e'));
	}

	# Retona o formato de '%X' time desmenbrado
	public static function getLocaleFormatTime(){
		static $ret='';
		return $ret?$ret:$ret=strtr (
		strtolower(strftime ('%X',strtotime('13:02:04'))),
		array('01'=>'%I','13'=>'%H','02'=>'%M','2'=>'%M','04'=>'%S','4'=>'%S','am'=>'%p','pm'=>'%p'));
	}

	# Retona o formato de '%x %X' datetime desmenbrado
	function getLocaleFormatDateTime(){
		static $ret='';
		return $ret?$ret:$ret=Mask::getLocaleFormatDate().' '.Mask::getLocaleFormatTime();
	}

}