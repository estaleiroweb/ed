<?php
/*
	Todas as páginas de internet e redes TCP/IP são endereçadas o que chamamos de IP.
	Por exemplo, o nome de uma URL www.google.com.br é traduzida para um endereço IP 177.43.115.178.
	
	Cada endereço IP pertence a uma Rede específica e delimitada.
	É como um agrupamento de IPs
	Este agrupamento é feito através da máscara de rede ex: 255.255.255.0
	
	Existem duas formas de representar uma máscara. Da forma nominal (255.255.255.0) ou canônica /24
	Um /24 significa que temos 24 bits setados dos 32 cuja as representações ficaria:
		Canonica:    /24
		Binária:     11111111 11111111 11111111 00000000
		Hexadecimal: FF       FF       FF       0
		Decimal:     255      255      255      0
	
	Para facilitar a organização dos IPs convertemos eles para uma forma decimal, assim conseguimos localizá-los ou ordená-los com maior facilidade.
	
	As funções abaixo tratam das conversões de IP e Máscara para Nominal, Decimál ou Canônica
	
	Veja sobre IP em http://pt.wikipedia.org/wiki/Endere%C3%A7o_IP
*/
class IpBin {
	################################### Basic Methods ###################################
	/**
	 * Operação AND de grandes Números
	 *
	 * @param double $a
	 * @param double $b
	 * @return double $a & $b
	 */
	#####
	static function decAnd($a,$b){ return (string)gmp_strval(gmp_and("$a","$b")); }
	/**
	 * Operação Xor de grandes Números
	 *
	 * @param double $a
	 * @param double $b
	 * @return double $a ^ $b
	 */
	static function decXor($a,$b){ return (string)gmp_strval(gmp_xor("$a","$b")); }
	/**
	 * Operação Or de grandes Números
	 *
	 * @param double $a
	 * @param double $b
	 * @return double $a | $b
	 */
	static function decOr($a,$b){ return (string)gmp_strval(gmp_or("$a","$b")); }
	/**
	 * Operação Not de grandes Números
	 *
	 * @param double $a
	 * @param double $bits
	 * @return double ~$a
	 */
	static function decNot($a,$bits=0){ return self::decXor($a,pow(2,$bits?$bits:floor(log($a,2))+1)-1); }

	################################### Specific Methods ###################################
	/**
	 * Traduz um número inteiro em TimeSlot Division
	 *
	 * @param double $dec
	 * @return string TimeSlot Division
	 */
	static function ts($dec) {
		$bin=str_word_count(str_replace('0',' ',str_replace('1','A',strrev (base_convert($dec, 10, 2)))),2);
		foreach ($bin as $key=>$value) $bin[$key]=$key.((strlen($value)==1)?'':'-'.($key+strlen($value)-1));
		return implode(',',$bin);
	}
	/**
	 * Converte Ip para IpDec
	 *
	 * @param string $ip
	 * @return double IpDec
	 */
	static function INET_ATON($ip){
		$out=0;
		$ip=array_values(array_reverse(explode('.',$ip)));
		foreach($ip as $k=>$v) $out+=$v*pow(256,$k);
		return $out;
	}
	/**
	 * Converte IpDec para Ip
	 *
	 * @param double $ipDec
	 * @return string Ip
	 */
	static function INET_NTOA($ipDec) {
		$out=array();
		for($k=3;$k>=0;$k--) {
			$m=pow(256,$k);
			$ip=floor($ipDec/$m);
			$out[]=$ip;
			$ipDec-=$ip*$m;
		}
		return implode('.',$out);
	}
	/**
	 * Converte IpDec para Ip. O mesmo que INET_NTOA
	 *
	 * @param double $ipDec
	 * @return string Ip
	 */
	static function ipDec2ip($ipDec){ return self::INET_NTOA($ipDec); }
	/**
	 * Converte Ip para IpDec. O mesmo que INET_ATON
	 *
	 * @param string $ip
	 * @return double IpDec
	 */
	static function ip2ipDec($ip){ return self::INET_ATON($ip); }
	/**
	 * Converte IpDec em IpBin
	 *
	 * @param double $ipDec
	 * @return string IpBin
	 */
	static function ipDec2ipBin($ipDec){ return str_pad(base_convert($ipDec,10,2),32,'0',STR_PAD_LEFT); }
	static function ip2bin($ipDec){ return self::ipDec2ipBin($ipDec); }

	/**
	 * Converte MaskDec em Canonica
	 *
	 * @param double $maskDec
	 * @return integer M 0~32
	 */
	static function maskDec2m($maskDec){ return strlen(str_replace('0','',decbin($maskDec))); }
	/**
	 * Converte Mask canonica em MaskDec
	 * Ex: m2maskDec(24) retorna 167772161
	 *     INET_NTOA(m2maskDec(24)) retorna '255.255.255.0'
	 *
	 * @param integer $m 0~32
	 * @return double
	 */
	static function m2maskDec($m){ return (pow(2,$m)-1)*pow(2,32-$m); }
	/**
	 * Retorna o endereço net em decimal a partir do IpDec e Maks Canonica
	 *
	 * @param double $ipDec
	 * @param integer $m 0~32
	 * @return double NetDec
	 */
	static function ipNet($ipDec,$m){ return self::decAnd($ipDec,self::m2maskDec($m)); }
	/**
	 * Enter description here...
	 *
	 * @param double $ipDec
	 * @param integer $m
	 * @return double
	 */
	static function ipBCast($ipDec,$m){ 
		return self::ipNet($ipDec,$m)+pow(2,32-$m)-1;
		//return self::decOr($ipDec,self::decNot(self::m2maskDec($m),32)); 
	}
	/*
	function decBCast($ipDec,$maskDec){ return self::decOr($ipDec,self::decNot($maskDec,32)); }
	function dec2net($ipDec,$maskDec){ return self::dec2ip(self::decIpNet($ipDec,$maskDec)); }
	function dec2bcast($ipDec,$maskDec){ return self::dec2ip(self::decBCast($ipDec,$maskDec)); }
	*/
	################################### Degrease Methods ###################################
	/**
	 * Transforma IpDec para Ip
	 *
	 * @param double $ipDec
	 * @return string Ip
	 */
	################################### Depreciated Methods ###################################
	static function dec2ip($ipDec){
		$hex=chunk_split(str_pad(base_convert($ipDec, 10, 16),8,'0',STR_PAD_LEFT),2,'.');
		return implode('.',sscanf($hex,"%x.%x.%x.%x"));
	}
	/**
	 * Transforma Ip para IpDec
	 *
	 * @param string $ip
	 * @return $ipDec IpDec
	 */
	static function ip2dec($ip){
		$blocos=sscanf($ip,"%u.%u.%u.%u");
		$retorno=0;
		foreach ($blocos as $key=>$value) $retorno+=$value*(pow(256,3-$key));
		return $retorno;
	}
	
	static function dec2Tsi($dec) { self::ts($dec); }
	/**
	 * Converte Mascara Dec para Mascara canonica
	 * Ex: maskDec2m(167772161) retorna 24
	 *     maskDec2m(INET_ATON('255.255.255.0')) retorna 24
	 * @param double $m
	 * @return integer M
	 */
	static function dec2cidr($maskDec){
		$notMask=self::decNot($maskDec);
		$bits=floor(log($maskDec,2))+1;
		$notBits=floor(log($notMask,2))+1;
		$markOk=pow(2,$bits)-pow(2,$notBits);
		return ($markOk==$maskDec)?$bits - $notBits:false;
	}
	static function decIpNet($ipDec,$maskDec){ return self::decAnd($ipDec,$maskDec); }
	static function decBCast($ipDec,$maskDec){ return self::decOr($ipDec,self::decNot($maskDec,32)); }
	static function dec2net($ipDec,$maskDec){ return self::dec2ip(self::decIpNet($ipDec,$maskDec)); }
	static function dec2bcast($ipDec,$maskDec){ return self::dec2ip(self::decBCast($ipDec,$maskDec)); }
	###########################################################################################
	static function dec2ipfrom($ip,$maskDec){
		$len=strlen(str_replace("0","",base_convert($maskDec, 10, 2)));
		return ($len>=31)?'':self::dec2ip(self::decMoveIp(self::decIpNet($ip,$maskDec),$maskDec,1));
	}
	static function dec2ipto($ipDec,$mask){
		$len=strlen(str_replace("0","",base_convert($mask, 10, 2)));
		return ($len>=31)?'':self::dec2ip(self::decMoveIp(self::decBCast($ipDec,$mask),$mask,-1));
	}
	static function stepMask($maskDec,$value){
		$binNotMask=base_convert(self::decNot(abs($maskDec)),10,2);
		$pointStep=array_filter(preg_split('//', str_replace('0',' ',strrev($binNotMask)), -1, PREG_SPLIT_NO_EMPTY),'is_numeric');
		$strValue=strrev(base_convert($value,10,2));
		if (strlen($strValue)>count($pointStep)) return false;
		$count=$step=0;
		foreach ($pointStep as $exp=>$base) {
			$step+=(strlen($strValue)>$count && $strValue[$count++])?pow(2,$exp):0;
		}
		return $step*(($value)?($value/abs($value)):1);
	}
	static function getStepIp($ip,$maskDec){
		$dSig=str_word_count(str_replace('0','A',self::ip2bin($maskDec)), 2);
		$strIp=self::ip2bin($ip);
		foreach ($dSig as $key=>$value) $dSig[$key]=substr($strIp,$key,strlen($value));
		return base_convert(implode('',$dSig),2,10);
	}
	static function decMoveIp($ipDec,$maskDec,$step){
		$net=self::decIpNet($ipDec,$maskDec);
		$stepCalc=self::stepMask($maskDec,self::getStepIp($ipDec,$maskDec)+$step);
		if ($stepCalc===false) return false;
		$calc=$stepCalc+$net;
		$bCast=self::decBCast($ipDec,$maskDec);
		return ($calc>$bCast || $calc<$net)?false:$calc;
	}
	static function dec2nip($maskDec){ return bindec(str_replace('0','',decbin(self::decNot($maskDec))))+1; }
	static function dec2niputil($mask){
		$numIp=self::dec2nip($mask)-2;
		return ($numIp>1)?$numIp:0;
	}
	static function dec2Class($ipDec){
		$dNet=array(
			'A'=>array(0000000000,2147483647,'Production'),
			'B'=>array(2147483648,3221225471,'Production'),
			'C'=>array(3221225472,3758096383,'Production'),
			'D'=>array(3758096384,4026531839,'Multicast'),
			'E'=>array(4026531840,4294967295,'Research')
		);
		foreach ($dNet as $key=>$value) if ($ipDec>=$value[0] && $ipDec<=$value[1]) return array('Class'=>$key,'Applic'=>$value[2]);
		return array('Class'=>'E','Applic'=>'Research');
	}
	static function dec2Class2($ipDec){
		$dNet=array(
			'A'=>array(2147483648,'Production'),
			'B'=>array(1073741824,'Production'),
			'C'=>array(536870912,'Production'),
			'D'=>array(268435456,'Multicast'),
			'E'=>array(0,'Research')
		);
		foreach ($dNet as $key=>$value) if (!self::decAnd($ipDec,$value[0])) return array('Class'=>$key,'Applic'=>$value[1]);
		return array('Class'=>'E','Applic'=>'Research');
	}
	static function dec2ClassSpc($ipDec){
		$dNet=array(
			array(0,8,'Rede corrente (só funciona como endereço de origem) RFC 1700'),//0.0.0.0/8
			array(167772160,8,'Rede Privada RFC 1918'),//10.0.0.0/8
			array(234881024,8,'Rede Pública RFC 1700'),//14.0.0.0/8
			array(654311424,8,'Reservado RFC 1797'),//39.0.0.0/8
			array(2130706432,8,'Localhost RFC 3330'),//127.0.0.0/8
			array(2147483648,16,'Reservado (IANA) RFC 3330'),//128.0.0.0/16
			array(2851995648,16,'Zeroconf RFC 3927'),//169.254.0.0/16
			array(2886729728,12,'Rede Privada RFC 1918'),//172.16.0.0/12
			array(3221159936,16,'Reservado (IANA) RFC 3330'),//191.255.0.0/16
			array(3221225984,24,'Documentação RFC 3330'),//192.0.2.0/24
			array(3227017984,24,'IPv6 para IPv4 RFC 3068'),//192.88.99.0/24
			array(3232235520,16,'Rede Privada RFC 1918'),//192.168.0.0/16
			array(3323068416,15,'Teste de benchmark de redes RFC 2544'),//198.18.0.0/15
			array(3758096128,24,'Reservado RFC 3330'),//223.255.255.0/24
			array(3758096384,4,'Multicasts (antiga rede Classe D) RFC 3171'),//224.0.0.0/4
			array(4026531840,4,'Reservado (antiga rede Classe E) RFC 1700'),//240.0.0.0/4
			array(4294967295,32,'Broadcast'),//255.255.255.255
		);
		
		foreach ($dNet as $line) if($ipDec>=$line[0] && $ipDec<=self::ipBCast($line[0],$line[1])) return $line[2];
	}
	static function dec2ClassMask($ipDec){
		if(preg_match('/01/',decbin($ipDec))) return false;
		$dNet=array(
			'A'=>array(16777215,),
			'B'=>array(65535,),
			'C'=>array(255,),
			'All'=>array(0,),
		);
		foreach ($dNet as $key=>$value) if (!self::decAnd($ipDec,$value[0])) return $key;
	}
	static function dec2ipcat($ipDec){
		$dNet=array(
		array(167772160,184549375),//10.0.0.0    ~ 10.255.255.255  | 0A.00.00.00 ~ 0A.FF.FF.FF
		array(2886729728,2887778303),//172.16.0.0  ~ 172.31.255.255  | AC.10.00.00 ~ AC.1F.FF.FF
		array(3232235520,3232301055) //192.168.0.0 ~ 192.168.255.255 | C0.A8.00.00 ~ C0.A8.FF.FF
		);
		foreach ($dNet as $value) if ($ipDec>=$value[0] && $ipDec<=$value[1]) return 'Private';
		return 'Public';
	}
}