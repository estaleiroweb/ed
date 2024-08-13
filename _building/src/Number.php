<?php
class Number extends OverLoadElements {
	protected $defaultLanguage='pt_br';
	protected $protect=array(
		'language'=>null,
		'ucfirst'=>false,
	);
	function __construct(){}
	/*
	 * $genero  single|female
	 */
	function writeOut($num,$gender='single') { 
		if(!$this->protect['language']) $this->language=$this->defaultLanguage;
		$num=$this->clearNum($num);
		//$num=preg_replace('/["]/','',$num);
		$currency=$this->getCurrency($num);
		$num=preg_replace('/[^0-9\.]/','',$num);
		$int=preg_replace(array('/\.\d*/','/^0+/'),'',$num);
		$dec=preg_replace('/\d*\./','',$num);
		if($int=='') $int=0;
		if($currency) {
			$str_currency=$this->protect['string']['currency'][$currency];
			$gender='currency';
			if(@$str_currency['female']) $gender='female|'.$gender;
			$str_currency=' '.$this->getValue($str_currency,$int==1?$gender:'plural',true);
			
			preg_match('/^(\d{2})/',$dec.'00',$ret);
			$dec=$ret[1]+0;
			if($dec) $str_decimal=$this->getCentness($ret[1]).' '.$this->getValue($this->protect['string']['cent'],$ret[1]==1?'single':'plural',true);
			else $dec=$str_decimal='';
		} else {
			$str_currency='';
			if(preg_match_all('/\d/',preg_replace('/0+$/','',$dec),$ret)){
				$str_decimal=' '.$this->getValue($this->protect['string']['decimal']);
				foreach($ret as $k=>$v) $str_decimal.=' '.$this->getValue($this->protect['string']['number'][$v]);
			} else $dec=$str_decimal='';
		}
		if(!preg_match_all('/\d{0,3}/',strrev($int),$ret)) return '';
		$str=array();
		if(!$dec && $int==0) {
			$str[]=$this->getValue($this->protect['string']['number'][0]).$str_currency;
		} else {
			foreach($ret[0] as $k=>$v) if($v!='') {
				$v=strrev($v);
				$base=' '.$this->getBase($k,$v==1?'single':'plural');
				$n=$this->getCentness($v,$gender);
				array_unshift($str,$n.$base);
			}
			$str=array(implode(', ',$str).$str_currency);
		}
		if($str_decimal) $str[]=$str_decimal;
		$str=implode($this->protect['string']['concat']['and'],$str);
		return $str;
	}
	function spell($num) {
		if(!$this->protect['language']) $this->language=$this->defaultLanguage;
		$erSyb=preg_quote(implode('',array_keys($this->protect['string']['symbol'])),'/');
		if(!preg_match_all('/(?:(?<number>\d)|(?<symbol>['.$erSyb.'])|(?<literal>[^0-9'.$erSyb.']+))/',$num,$ret,PREG_SET_ORDER)) return '';
		
		$out=array();
		foreach($ret as $v) {
			if(isset($v['literal'])) $out[]=$v['literal'];
			elseif(isset($v['symbol'])) $out[]=$this->getValue($this->protect['string']['symbol'][$v['symbol']]);
			elseif(isset($v['number'])) $out[]=$this->getValue($this->protect['string']['number'][$v['number']]);
		}
		
		return implode(', ',$out);
	}
	function currency($num) {
		if(!$this->protect['language']) $this->language=$this->defaultLanguage;
		$num=$this->clearNum($num);
		
		$currency=$this->getCurrency($num);
		$num=preg_replace('/[^0-9\.]/','',$num);

		$int=strrev(implode(',',str_split(strrev(preg_replace(array('/\.\d*/','/^0+/'),'',$num)),3)));
		$dec=substr(preg_replace('/\d*\./','',$num).'00',0,2);

		return $currency.$int.'.'.$dec;
	}
	private function clearNum($num) {
		return preg_replace('/["\(\)\[\]\{\}\+\-\=\_\*\'\/\|\\\,;<>]/','',$num);
	}
	private function getCurrency($num) {
		$c=array_keys($this->protect['string']['currency']);
		$er=array();
		foreach($c as $k) $er[]=preg_quote($k,'/');
		return (preg_match('/('.implode('|',$er).')/',$num,$ret))?$ret[1]:'';
	}
	private function getCentness($num,$gender='single',$concat=''){
		$num=(int)$num;
		if($num==0) return '';
		if(array_key_exists($num,$this->protect['string']['number'])) return $concat.$this->getValue($this->protect['string']['number'][$num],$gender);
		preg_match('/^(\d)(\d*)/',$num,$ret);
		$rest=$ret[2]+0;
		$key=$ret[1].preg_replace('/./','0',$ret[2]);
		if(array_key_exists($key,$this->protect['string']['number'])) {
			$k=$this->protect['string']['number'][$key];
			$c=$this->getValue($k,$rest && is_array($k) && array_key_exists('hundred',$k)?'hundred':$gender);
			return $concat.$c.$this->getCentness($ret[2],$gender,$this->protect['string']['concat']['and']);
		}
		$key='1'.preg_replace('/./','0',$ret[2]);
		if(!array_key_exists($key,$this->protect['string']['number'])) return '';
		return $concat.$this->getCentness($ret[1],'single',' ').$this->getValue($this->protect['string']['number'][$key],$ret[1]==1?$gender:'plural').$this->getCentness($ret[2],$gender,$this->protect['string']['concat']['and']);
	}
	private function getValue($mixed,$key='single',$force_ucfirst=false) {
		$out='';
		if(!is_array($mixed)) $out=$mixed;
		elseif(array_key_exists($key,$mixed)) $out=$mixed[$key];
		elseif(($k=array_values(preg_grep("/^$key\$/",array_keys($mixed))))) $out=$mixed[$k[0]];
		elseif(array_key_exists('single',$mixed)) $out=$mixed['single'];
		elseif(array_key_exists('female',$mixed)) $out=$mixed['female'];
		elseif(array_key_exists('currency',$mixed)) $out=$mixed['currency'];
		elseif(array_key_exists('plural',$mixed)) $out=$mixed['plural'];
		if($force_ucfirst || $this->protect['ucfirst']) $out=ucfirst($out);
		return $out;
	}
	private function getBase($base,$gender='single'){
		$base+=0;
		if(isset($this->protect['string']['base'][$base])) return $this->getValue($this->protect['string']['base'][$base],$gender);
		$b2=count($this->protect['string']['base'])-1;
		return $this->getBase($base-$b2,$gender).$this->protect['string']['concat']['of'].$this->getValue($this->protect['string']['base'][$b2],'plural');
	}
	function setLanguage($language){
		$this->protect['language']=$language;
		$this->protect['string']['number']=$this->getStringNumber();
		$this->protect['string']['base']=$this->getStringBase();
		$this->protect['string']['currency']=$this->getStringCurrency();
		$this->protect['string']['cent']=$this->getStringCent();
		$this->protect['string']['decimal']=$this->getStringDecimal();
		$this->protect['string']['concat']=$this->getStringConcat();
		$this->protect['string']['symbol']=$this->getStringSymbol();
	}
	function getStringNumber() {
		static $out=array();
		if(!$out) {
			$out[$this->defaultLanguage]=array(
				'zero',array('female'=>'uma','currency'=>'hum','single'=>'um',),array('single'=>'dois','female'=>'duas'),'três','quatro','cinco','seis','sete','oito','nove',
				'dez','onze','doze','treze','quatorze','quinze','dezesseis','dezessete','dezoito','dezenove',
				20=>'vinte',30=>'trinta',40=>'quarenta',50=>'ciquenta',60=>'sessenta',70=>'setenta',80=>'oitenta',90=>'noventa',
				100=>array('single'=>'cem','hundred'=>'cento',),200=>'duzentos',300=>'trezentos',400=>'quatrocentos',500=>'quinhentos',600=>'seiscentos',700=>'setecentos',800=>'oitocentos',900=>'novecentos',
			);
		}
		return @$out[$this->language]?$out[$this->language]:$out[$this->defaultLanguage];
	}
	function getStringBase() {
		static $out=array();
		if(!$out) {
			$out[$this->defaultLanguage]=array(
				0=>'',
				1=>'mil',
				2=>array('single'=>'milhão','plural'=>'milhões'),
				3=>array('single'=>'bilhão','plural'=>'biliões'),
				4=>array('single'=>'trilhão','plural'=>'triliões'),
				5=>array('single'=>'quatrilhão','plural'=>'quatriliões'),
				6=>array('single'=>'quintilião','plural'=>'quintiliões'),
				7=>array('single'=>'sextilião','plural'=>'sextiliões'),
				8=>array('single'=>'septilião','plural'=>'septiliões'),
				9=>array('single'=>'octilião','plural'=>'octiliões'),
				10=>array('single'=>'nonilião','plural'=>'noniliões'),
				11=>array('single'=>'decilião','plural'=>'deciliões'),
				12=>array('single'=>'undecilião','plural'=>'undeciliões'),
				13=>array('single'=>'duodecilião','plural'=>'duodeciliões'),
				14=>array('single'=>'tredecilião','plural'=>'tredeciliões'),
				15=>array('single'=>'quatridecilião','plural'=>'quatrideciliões'),
				16=>array('single'=>'quindecilião','plural'=>'quindeciliões'),
				17=>array('single'=>'sexdecilião','plural'=>'sexdeciliões'),
				18=>array('single'=>'septendecilião','plural'=>'septendeciliões'),
				19=>array('single'=>'octodecilião','plural'=>'octodeciliões'),
				20=>array('single'=>'novendecilião','plural'=>'novendeciliões'),
				21=>array('single'=>'vigintilião','plural'=>'vigintiliões'),
				22=>array('single'=>'unvigintilião','plural'=>'unvigintiliões'),
				23=>array('single'=>'duovigintilião','plural'=>'duovigintiliões'),
				24=>array('single'=>'tresvigintilião','plural'=>'tresvigintiliões'),
				25=>array('single'=>'quatrivigintilião','plural'=>'quatrivigintiliões'),
				26=>array('single'=>'quinquavigintilião','plural'=>'quinquavigintiliões'),
			);
		}
		return @$out[$this->language]?$out[$this->language]:$out[$this->defaultLanguage];
	}
	function getStringCurrency() {
		static $out=array();
		if(!$out) {
			$out[$this->defaultLanguage]=array(
				'R$'=>array('single'=>'real','plural'=>'reais'),
				'CR$'=>array('single'=>'cruzeiro real','plural'=>'cruzeiros reais'),
				'Cr$'=>array('single'=>'cruzeiro','plural'=>'cruzeiros'),
				'Cz$'=>array('single'=>'cruzado','plural'=>'cruzados'),
				'NCr$'=>array('single'=>'cruzeiro novo','plural'=>'cruzeiros novos'),
				'NCz$'=>array('single'=>'cruzado novo','plural'=>'cruzados novos'),
				'$'=>array('single'=>'dólar, escudo cabo-verdiano, peso argentino, peso chileno, peso dominicano, dólar da guiana, dólar da jamaica, boliviano','plural'=>'dólares'),
				'US$'=>array('single'=>'dólar americano','plural'=>'dólares americano'),
				'€'=>array('single'=>'euro','plural'=>'euros'),
				'¥'=>array('single'=>'iene','plural'=>'ienes'),
				'£'=>array('female'=>'libra esterlina/lira turca','plural'=>'libras esterlina'),
				'Q'=>array('single'=>'quetzal','plural'=>'quetzais'),
				'L'=>array('female'=>'lempira','plural'=>'lempiras'),
				'¢'=>array('single'=>'colón','plural'=>'colóns'),
				'Ø'=>array('single'=>'libra argentina','plural'=>'libras argentina'),
				'Mex$'=>array('single'=>'peso mexicano','plural'=>'pesos mexicano'),
				'$MN'=>array('single'=>'peso cubano','plural'=>'pesos cubano'),
				'Kc'=>array('female'=>'coroa checa','plural'=>'coroas checa'),
				'C$'=>array('single'=>'dólar do canadá','plural'=>'dólares do canadá'),
				'NZ$'=>array('single'=>'dólar da nova zelândia','plural'=>'dólares da nova zelândia'),
				'A$'=>array('single'=>'dólar da austrália','plural'=>'dólares da austrália'),
				'HK$'=>array('single'=>'dólar de hong kong','plural'=>'dólares de hong kong'),
				'Bs'=>array('single'=>'bolívar venezuelano','plural'=>'bolívares venezuelano'),
				'Rp'=>array('female'=>'rupia','plural'=>'rupias'),
				'CFA'=>array('single'=>'franco cfa','plural'=>'francos cfa'),
				'Fr'=>array('single'=>'franco suíço','plural'=>'francos suíço'),
				'Kz'=>array('single'=>'kwanza','plural'=>'kwanzas'),
				'kr'=>array('female'=>'coroa','plural'=>'coroas'),
				'zl'=>array('single'=>'zloty','plural'=>'zloty'),
				'Ft'=>array('single'=>'florim húngaro','plural'=>'florins húngaro'),
				'COP'=>array('single'=>'peso colombiano','plural'=>'pesos colombiano'),
				'W$'=>array('female'=>'wonka de dólar','plural'=>'wonkas de dólar'),
				'AZB'=>array('female'=>'amzonbia','plural'=>'amzonbias'),
			);
		}
		return @$out[$this->language]?$out[$this->language]:$out[$this->defaultLanguage];
	}
	function getStringCent() {
		static $out=array();
		if(!$out) {
			$out[$this->defaultLanguage]=array('single'=>'centavo','plural'=>'centavos');
		}
		return @$out[$this->language]?$out[$this->language]:$out[$this->defaultLanguage];
	}	
	function getStringDecimal() {
		static $out=array();
		if(!$out) {
			$out[$this->defaultLanguage]='ponto';
		}
		return @$out[$this->language]?$out[$this->language]:$out[$this->defaultLanguage];
	}	
	function getStringConcat() {
		static $out=array();
		if(!$out) {
			$out[$this->defaultLanguage]=array(
				'and'=>' e ',
				'of'=>' de ',
			);
		}
		return @$out[$this->language]?$out[$this->language]:$out[$this->defaultLanguage];
	}	
	function getStringSymbol() {
		static $out=array();
		if(!$out) {
			$out[$this->defaultLanguage]=array(
				' '=>'espaço',
				'!'=>'exclamação',
				'@'=>'arroba',
				'#'=>'cerquilha',
				'$'=>'cifrão',
				'%'=>'por cento',
				'¨'=>'trema',
				'&'=>'E comercial',
				'*'=>'asterisco',
				'('=>'abre parenteses',
				')'=>'fecha parenteses',
				'-'=>'traço',
				'_'=>'sublinhado',
				'='=>'igual',
				'+'=>'mais',
				'\''=>'aspas simples',
				'"'=>'aspas dupla',
				'´'=>'acento agudo',
				'`'=>'crase',
				'['=>'abre couchetes',
				'{'=>'abre chaves',
				']'=>'fecha couchetes',
				'}'=>'fecha chaves',
				'~'=>'til',
				'^'=>'circunflexo',
				'\\'=>'contra barra',
				'|'=>'barra vertical',
				','=>'vírgula',
				'<'=>'menor que',
				'.'=>'ponto',
				'>'=>'maior que',
				';'=>'ponto e vírgula',
				':'=>'dois pontos',
				'/'=>'barra',
				'?'=>'interrogação',
			);
		}
		return @$out[$this->language]?$out[$this->language]:$out[$this->defaultLanguage];
	}	
	function getCountry(){
		return array(
			'AFA'=>array('currency'=>'Afegano','local'=>'Afeganistao',),
			'ZAR'=>array('currency'=>'Rand','local'=>'Africa Do Sul',),
			'ALL'=>array('currency'=>'Lek','local'=>'Albania',),
			'EUR'=>array('currency'=>'Euro','local'=>'Alemanha',),
			'ADP'=>array('currency'=>'Peseta','local'=>'Andorra',),
			'AON'=>array('currency'=>'Novo Cuanza','local'=>'Angola',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Anguilla',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Antigua E Barbuda',),
			'ANG'=>array('currency'=>'Florim','local'=>'Antilhas Holandesas',),
			'SAR'=>array('currency'=>'Rial','local'=>'Arabia Saudita',),
			'DZD'=>array('currency'=>'Dinar Argelino','local'=>'Argelia',),
			'ARS'=>array('currency'=>'Peso','local'=>'Argentina',),
			'AMD'=>array('currency'=>'Dram','local'=>'Armenia',),
			'AWG'=>array('currency'=>'Florim','local'=>'Aruba',),
			'AUD'=>array('currency'=>'Dolar','local'=>'Australia',),
			'EUR'=>array('currency'=>'Euro','local'=>'Austria',),
			'AZM'=>array('currency'=>'Manat','local'=>'Azerbaijao',),
			'BSD'=>array('currency'=>'Dolar','local'=>'Bahamas',),
			'BHD'=>array('currency'=>'Dinar','local'=>'Bahrein',),
			'BDT'=>array('currency'=>'Taca','local'=>'Bangladesh',),
			'BBD'=>array('currency'=>'Dolar','local'=>'Barbados',),
			'BYB'=>array('currency'=>'Rublo','local'=>'Belarus',),
			'EUR'=>array('currency'=>'Euro','local'=>'Belgica',),
			'BZD'=>array('currency'=>'Dolar','local'=>'Belize',),
			'BMD'=>array('currency'=>'Dolar','local'=>'Bermudas',),
			'BOB'=>array('currency'=>'Boliviano','local'=>'Bolivia',),
			'BAM'=>array('currency'=>'Marco','local'=>'Bosnia-herzegovina',),
			'BWP'=>array('currency'=>'Pula','local'=>'Botsuana',),
			'BRL'=>array('currency'=>'Real','local'=>'Brasil',),
			'BND'=>array('currency'=>'Dolar','local'=>'Brunei',),
			'BGN'=>array('currency'=>'Lev','local'=>'Bulgaria',),
			'BIF'=>array('currency'=>'Franco','local'=>'Burundi',),
			'BTN'=>array('currency'=>'Ngultrum','local'=>'Butao',),
			'CVE'=>array('currency'=>'Escudo','local'=>'Cabo Verde',),
			'XAF'=>array('currency'=>'Franco','local'=>'Camaroes',),
			'KHR'=>array('currency'=>'Riel','local'=>'Camboja',),
			'CAD'=>array('currency'=>'Dolar','local'=>'Canada',),
			'QAR'=>array('currency'=>'Rial','local'=>'Catar',),
			'KYD'=>array('currency'=>'Ilhas, Dolar','local'=>'Cayman',),
			'KZT'=>array('currency'=>'Tenge','local'=>'Cazaquistao',),
			'XAF'=>array('currency'=>'Franco','local'=>'Chade',),
			'CLP'=>array('currency'=>'Peso','local'=>'Chile',),
			'CNY'=>array('currency'=>'Iuan Renmimbi','local'=>'China',),
			'CYP'=>array('currency'=>'Libra','local'=>'Chipre',),
			'SGD'=>array('currency'=>'Dolar','local'=>'Cingapura',),
			'COP'=>array('currency'=>'Peso','local'=>'Colombia',),
			'KMF'=>array('currency'=>'Ilhas, Franco','local'=>'Comores',),
			'EUR'=>array('currency'=>'Euro','local'=>'Comunidade Europeia',),
			'KPW'=>array('currency'=>'Won','local'=>'Coreia Do Norte',),
			'KRW'=>array('currency'=>'Won','local'=>'Coreia Do Sul',),
			'BUA'=>array('currency'=>'Bua','local'=>'Costa Do Marfim',),
			'CRC'=>array('currency'=>'Colon','local'=>'Costa Rica',),
			'KWD'=>array('currency'=>'Dinar/kwait','local'=>'Coveite',),
			'HRK'=>array('currency'=>'Kuna','local'=>'Croacia',),
			'CUP'=>array('currency'=>'Peso','local'=>'Cuba',),
			'DKK'=>array('currency'=>'Coroa','local'=>'Dinamarca',),
			'DJF'=>array('currency'=>'Franco','local'=>'Djibuti',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Dominica',),
			'EGP'=>array('currency'=>'Libra','local'=>'Egito',),
			'SVC'=>array('currency'=>'Colon','local'=>'El Salvador',),
			'AED'=>array('currency'=>'Dirham','local'=>'Emirados Arabes Unidos',),
			'ECS'=>array('currency'=>'Sucre','local'=>'Equador',),
			'ERN'=>array('currency'=>'Nakfa','local'=>'Eritreia',),
			'SKK'=>array('currency'=>'Coroa','local'=>'Eslovaquia',),
			'SIT'=>array('currency'=>'Tolar','local'=>'Eslovenia',),
			'EUR'=>array('currency'=>'Euro','local'=>'Espanha',),
			'USD'=>array('currency'=>'Dolar','local'=>'Estados Unidos',),
			'EEK'=>array('currency'=>'Coroa','local'=>'Estonia',),
			'ETB'=>array('currency'=>'Birr','local'=>'Etiopia',),
			'EUR'=>array('currency'=>'Euro','local'=>'Euro',),
			'FKP'=>array('currency'=>'Libra','local'=>'Falkland',),
			'FJD'=>array('currency'=>'Dolar','local'=>'Fiji',),
			'PHP'=>array('currency'=>'Peso','local'=>'Filipinas',),
			'EUR'=>array('currency'=>'Euro','local'=>'Finlandia',),
			'TWD'=>array('currency'=>'Novo Dolar','local'=>'Formosa (taiwan)',),
			'EUR'=>array('currency'=>'Euro','local'=>'Franca',),
			'XAF'=>array('currency'=>'Franco','local'=>'Gabao',),
			'GMD'=>array('currency'=>'Dalasi','local'=>'Gambia',),
			'GHC'=>array('currency'=>'Cedi','local'=>'Gana',),
			'GEL'=>array('currency'=>'Lari','local'=>'Georgia',),
			'GIP'=>array('currency'=>'Libra','local'=>'Gibraltar',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Granada',),
			'EUR'=>array('currency'=>'Euro','local'=>'Grecia',),
			'DKK'=>array('currency'=>'Coroa','local'=>'Groelandia',),
			'EUR'=>array('currency'=>'Euro','local'=>'Guadalupe',),
			'USD'=>array('currency'=>'Dolar','local'=>'Guam',),
			'GTQ'=>array('currency'=>'Quetzal','local'=>'Guatemala',),
			'GYD'=>array('currency'=>'Dolar','local'=>'Guiana',),
			'EUR'=>array('currency'=>'Euro','local'=>'Guiana Francesa',),
			'GNF'=>array('currency'=>'Franco','local'=>'Guine',),
			'XAF'=>array('currency'=>'Franco','local'=>'Guine Equatorial',),
			'GWP'=>array('currency'=>'Peso','local'=>'Guine-bissau',),
			'HTG'=>array('currency'=>'Gourde','local'=>'Haiti',),
			'HNL'=>array('currency'=>'Lempira','local'=>'Honduras',),
			'HKD'=>array('currency'=>'Dolar','local'=>'Hong Kong',),
			'HUF'=>array('currency'=>'Forint','local'=>'Hungria',),
			'YER'=>array('currency'=>'Rial','local'=>'Iemen',),
			'GBP'=>array('currency'=>'Libra','local'=>'Ilha De Man',),
			'AUD'=>array('currency'=>'Dolar','local'=>'Ilha Natal',),
			'AUD'=>array('currency'=>'Dolas','local'=>'Ilha Norkfolk',),
			'BHD'=>array('currency'=>'Dinar','local'=>'Ilhas Bahrein',),
			'KYD'=>array('currency'=>'Dolar','local'=>'Ilhas Cayman',),
			'KMF'=>array('currency'=>'Franco','local'=>'Ilhas Comores',),
			'NZD'=>array('currency'=>'Dolar','local'=>'Ilhas Cook',),
			'GBP'=>array('currency'=>'Libra','local'=>'Ilhas Do Canal',),
			'FKP'=>array('currency'=>'Libra','local'=>'Ilhas Malvinas',),
			'USD'=>array('currency'=>'Dolar','local'=>'Ilhas Marianas',),
			'USD'=>array('currency'=>'Dolar','local'=>'Ilhas Marshall',),
			'SBD'=>array('currency'=>'Dolar','local'=>'Ilhas Salomao',),
			'INR'=>array('currency'=>'Rupia','local'=>'India',),
			'IDR'=>array('currency'=>'Rupia','local'=>'Indonesia',),
			'IRR'=>array('currency'=>'Rial','local'=>'Ira',),
			'IQD'=>array('currency'=>'Dinar','local'=>'Iraque',),
			'EUR'=>array('currency'=>'Euro','local'=>'Irlanda',),
			'ISK'=>array('currency'=>'Coroa','local'=>'Islandia',),
			'ILS'=>array('currency'=>'Shekel','local'=>'Israel',),
			'EUR'=>array('currency'=>'Euro','local'=>'Italia',),
			'YUM'=>array('currency'=>'Novo Dinar','local'=>'Iugoslavia',),
			'JMD'=>array('currency'=>'Dolar','local'=>'Jamaica',),
			'JPY'=>array('currency'=>'Iene','local'=>'Japao',),
			'JOD'=>array('currency'=>'Dinar','local'=>'Jordania',),
			'AUD'=>array('currency'=>'Dolar','local'=>'Kiribati',),
			'KWD'=>array('currency'=>'Dolar','local'=>'Kuait',),
			'LAK'=>array('currency'=>'Quipe','local'=>'Laos',),
			'LSL'=>array('currency'=>'Loti','local'=>'Lesoto',),
			'LVL'=>array('currency'=>'Lat','local'=>'Letonia',),
			'LBP'=>array('currency'=>'Libra','local'=>'Libano',),
			'LRD'=>array('currency'=>'Dolar','local'=>'Liberia',),
			'LYD'=>array('currency'=>'Dinar','local'=>'Libia',),
			'CHF'=>array('currency'=>'Franco','local'=>'Liechtenstein',),
			'LTL'=>array('currency'=>'Lita','local'=>'Lituania',),
			'EUR'=>array('currency'=>'Euro','local'=>'Luxemburgo',),
			'MOP'=>array('currency'=>'Pataca','local'=>'Macau',),
			'MKD'=>array('currency'=>'Dinar','local'=>'Macedonia',),
			'MGF'=>array('currency'=>'Fr.malgaxe','local'=>'Madagascar',),
			'MYR'=>array('currency'=>'Ringgit','local'=>'Malasia',),
			'MWK'=>array('currency'=>'Kwacha','local'=>'Malaui',),
			'MVR'=>array('currency'=>'Rufia','local'=>'Maldivas',),
			'MTL'=>array('currency'=>'Lira','local'=>'Malta',),
			'MAD'=>array('currency'=>'Dirham','local'=>'Marrocos',),
			'EUR'=>array('currency'=>'Euro','local'=>'Martinica',),
			'MUR'=>array('currency'=>'Rupia','local'=>'Mauricio',),
			'EUR'=>array('currency'=>'Euro','local'=>'Mayotte',),
			'MXN'=>array('currency'=>'Peso','local'=>'Mexico',),
			'MMK'=>array('currency'=>'Quiate','local'=>'Mianmar (birmania)',),
			'USD'=>array('currency'=>'Dolar','local'=>'Micronesia',),
			'MZM'=>array('currency'=>'Metical','local'=>'Mocambique',),
			'MDL'=>array('currency'=>'Leu','local'=>'Moldova',),
			'EUR'=>array('currency'=>'Euro','local'=>'Monaco',),
			'MNT'=>array('currency'=>'Tugrik','local'=>'Mongolia',),
			'EUR'=>array('currency'=>'Euro','local'=>'Montenegro',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Montserrat',),
			'NAD'=>array('currency'=>'Dolar','local'=>'Namibia',),
			'AUD'=>array('currency'=>'Dolar','local'=>'Nauru',),
			'NPR'=>array('currency'=>'Rupia','local'=>'Nepal',),
			'NIO'=>array('currency'=>'Cordoba','local'=>'Nicaragua',),
			'NGN'=>array('currency'=>'Naira','local'=>'Nigeria',),
			'NOK'=>array('currency'=>'Coroa','local'=>'Noruega',),
			'NZD'=>array('currency'=>'Dolar','local'=>'Nova Zelandia',),
			'OMR'=>array('currency'=>'Rial','local'=>'Oma',),
			'EUR'=>array('currency'=>'Euro','local'=>'Paises Baixos',),
			'USD'=>array('currency'=>'Dolar','local'=>'Palau',),
			'ILS'=>array('currency'=>'Shekel','local'=>'Palestina',),
			'USD'=>array('currency'=>'Dolar','local'=>'Panama',),
			'PGK'=>array('currency'=>'Kina','local'=>'Papua Nova Guine',),
			'PKR'=>array('currency'=>'Rupia','local'=>'Paquistao',),
			'PYG'=>array('currency'=>'Guarani','local'=>'Paraguai',),
			'PEN'=>array('currency'=>'Novo Sol','local'=>'Peru',),
			'XPF'=>array('currency'=>'Franco','local'=>'Polinesia Francesa',),
			'PZN'=>array('currency'=>'Zloty','local'=>'Polonia',),
			'PLN'=>array('currency'=>'Republica Da, Zloty','local'=>'Polonia',),
			'USD'=>array('currency'=>'Dolar','local'=>'Porto Rico',),
			'EUR'=>array('currency'=>'Euro','local'=>'Portugal',),
			'QAR'=>array('currency'=>'Rial','local'=>'Qatar',),
			'KES'=>array('currency'=>'Xelim','local'=>'Quenia',),
			'GBP'=>array('currency'=>'Libra','local'=>'Reino Unido',),
			'XAF'=>array('currency'=>'Franco','local'=>'Rep. Centro Africana',),
			'DOP'=>array('currency'=>'Peso','local'=>'Republica Dominicana',),
			'SKK'=>array('currency'=>'Coroa','local'=>'Republica Eslovaca',),
			'CZK'=>array('currency'=>'Coroa','local'=>'Republica Tcheca',),
			'ROL'=>array('currency'=>'Leu','local'=>'Romenia',),
			'RWF'=>array('currency'=>'Franco','local'=>'Ruanda',),
			'RUB'=>array('currency'=>'Rublo','local'=>'Russia',),
			'EUR'=>array('currency'=>'Euro','local'=>'Saint Pierre',),
			'USD'=>array('currency'=>'Dolar','local'=>'Samoa',),
			'SHP'=>array('currency'=>'Libra','local'=>'Santa Helena',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Santa Lucia',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Sao Cristovao E Nevis',),
			'EUR'=>array('currency'=>'Euro','local'=>'Sao Marinho',),
			'STD'=>array('currency'=>'Dobra','local'=>'Sao Tome E Principe',),
			'XCD'=>array('currency'=>'Dolar','local'=>'Sao Vicente E Granadinas',),
			'SLL'=>array('currency'=>'Leone','local'=>'Serra Leoa',),
			'SCR'=>array('currency'=>'Rupia','local'=>'Seychelles',),
			'SYP'=>array('currency'=>'Libra','local'=>'Siria',),
			'SOS'=>array('currency'=>'Xelim','local'=>'Somalia',),
			'LKR'=>array('currency'=>'Rupia','local'=>'Sri Lanka',),
			'SZL'=>array('currency'=>'Lilangeni','local'=>'Suazilandia',),
			'SDD'=>array('currency'=>'Dinar','local'=>'Sudao',),
			'SEK'=>array('currency'=>'Coroa','local'=>'Suecia',),
			'CHF'=>array('currency'=>'Franco','local'=>'Suica',),
			'SRG'=>array('currency'=>'Florim','local'=>'Suriname',),
			'TJR'=>array('currency'=>'Rublo','local'=>'Tadjiquistao',),
			'THB'=>array('currency'=>'Bath','local'=>'Tailandia',),
			'TWD'=>array('currency'=>'Novo Dolar','local'=>'Taiwan (formosa)',),
			'TZS'=>array('currency'=>'Xelim','local'=>'Tanzania',),
			'TPE'=>array('currency'=>'Escudo','local'=>'Timor Leste',),
			'USD'=>array('currency'=>'Dolar','local'=>'Timor-leste',),
			'TOP'=>array('currency'=>'Paanga','local'=>'Tonga',),
			'TTD'=>array('currency'=>'Dolar','local'=>'Trinidad E Tobago',),
			'TND'=>array('currency'=>'Dinar','local'=>'Tunisia',),
			'TRY'=>array('currency'=>'Nova Lira','local'=>'Turquia',),
			'UAH'=>array('currency'=>'Hyvnia','local'=>'Ucrania',),
			'UGX'=>array('currency'=>'Xelim','local'=>'Uganda',),
			'UYU'=>array('currency'=>'Peso','local'=>'Uruguai',),
			'UZS'=>array('currency'=>'Som','local'=>'Uzbequistao',),
			'VUV'=>array('currency'=>'Vatu','local'=>'Vanuatu',),
			'EUR'=>array('currency'=>'Euro','local'=>'Vaticano',),
			'VEB'=>array('currency'=>'Bolivar','local'=>'Venezuela',),
			'VND'=>array('currency'=>'Dongue','local'=>'Vietna',),
			'ZMK'=>array('currency'=>'Quacha','local'=>'Zambia',),
			'ZWD'=>array('currency'=>'Dolar','local'=>'Zimbabue',),
		);
	}
}