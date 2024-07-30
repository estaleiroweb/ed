<?php
class DataFieldAnalyze {
	private $unTypes;
	private $types=array(
		'NULL',
		'BIT',
		'YEAR',
		'TINYINT','SMALLINT','MEDIUMINT','INTEGER','BIGINT',
		'DECIMAL','FLOAT','DOUBLE',
		'DATE','TIME','DATETIME',
		'CHAR','VARCHAR',
		'SET','EMUM',
		'TEXT','MEDIUMTEXT','LONGTEXT',
	);
	private $numType=0;
	private $oldNumType=0;
	private $oldValue=0;
	private $protected=array(
		'type'=>null,
		'nulls'=>0,
		'lastValue'=>null,
		'lastLength'=>null,
		'maxLength'=>null,
		'minLength'=>null,
		'min'=>null,
		'max'=>null,
		'length'=>null,
		'precision'=>0,
		'isZeroFill'=>false,
		'isUnsigned'=>true,
		'isAutoIncrement'=>false,
		'dateFormat'=>'dd/mm/YYYY',
		'values'=>array(),
	);
	function __construct(){
		$this->protected['values']=array();
		$this->unTypes=array_flip($this->types);
	}
	private function __get($nm){
		
		$fnSet='get'.ucfirst($nm);
		if(method_exists ($this,$fnSet)) return $this->$fnSet();
		elseif(isset($this->protected[$nm])) return $this->protected[$nm];
	}
	function __toString(){
		return print_r($this->result(),true).print_r($this->protected,true);
	}
	function add($value){
		$isNull=is_null($value);
		$this->setLastValue($value);
		if(!$isNull) {
			$this->protected['max']=max($value,$this->protected['max']);
			$this->protected['min']=max($value,$this->protected['min']);
			$this->protected['maxLength']=max($this->protected['lastLength'],$this->protected['maxLength']);
			$this->protected['minLength']=max($this->protected['lastLength'],$this->protected['minLength']);
			$this->setNumType($this->numType);
		} else $this->protected['nulls']++;
		$this->addValues();
		$this->oldValue=$this->protected['lastValue'];
		$this->oldNumType=$this->numType;
	}
	function nextType(){
		$this->setNumType($this->numType+1);
	}
	function setType($type){
		$numType=$this->getNumType($type);
		if($numType>$this->numType) $this->setNumType($numType);
	}
	function getType($numType=null){
		$t=$numType===null?$this->numType:$numType;
		return $this->types[$t];
	}
	function getNumType($type=null){
		$t=$type===null?$this->numType:(int)@$this->unTypes[$type];
		return $t;
	}
	public function escape_string($texto){
		return addcslashes($texto, "\x00..\x1F\x7E..\xFF'\"\\");
	}
	function result($notAutoIncremet=false){
		if($this->numType==0) return '';
		$quantValue=array_flip($this->protected['values']);
		krsort($quantValue);
		$numBIT=$this->getNumType('BIT');
		$numYEAR=$this->getNumType('YEAR');
		$numCHAR=$this->getNumType('CHAR');
		$numDECIMAL=$this->getNumType('DECIMAL');
		$numDATE=$this->getNumType('DATE');
		$isDec=$this->numType>=$numDECIMAL && $this->numType<$numDATE;
		$isInt=$this->numType>$numYEAR && $this->numType<$numDECIMAL;

		$notnull=$this->protected['nulls']?' NOT NULL':'';
		$default=reset($quantValue);
		$quant=(int)@key($quantValue);
		$default=$default && $quant>1?(' DEFAULT '.(is_null($default)?'NULL':'"'.$this->escape_string($default).'"')):'';

		$tam='';

		$autoincrement='';
		$unsigned='';
		if($this->numType>=$numCHAR) {
			if($this->protected['maxLength']<=255){
				$tam="({$this->protected['maxLength']})";
				//verifica enum/set se for muda tam e default
			}
		}elseif($this->numType<$numDATE){
			if($this->$numBIT && !$notAutoIncremet && count($quantValue)==1) {
				print "{$this->numType}\n";
				$autoincrement=' AUTO_INCREMENT PRIMARY KEY';
				$default='';
				$this->setType('INTEGER');
				$this->protected['length']=11;
				$this->protected['isAutoIncrement']=true;
			}
			if($this->protected['length']) $tam='('.$this->protected['length'].($isDec?','.$this->protected['precision']:'').')';
			if($isInt || $isDec) $unsigned=$this->protected['isUnsigned']?' UNSIGNED':'';
		}

		return "{$this->protected['type']}$tam$unsigned$notnull$autoincrement$default";
	}
	private function addValues(){
		$value=$this->protected['lastValue'];
		$this->protected['values'][$value]=1+@$this->protected['values'][$value];
	}
	private function setNumType($numType){
		$this->numType=$numType;
		$this->protected['type']=$this->types[$numType];
		$fn="setType_{$this->protected['type']}";
		$this->$fn();
	}
	private function setLastValue($value){
		$this->protected['lastValue']=$value;
		$this->protected['lastLength']=strlen($value);
		if(!is_null($value)) {
			$this->protected['max']=max($value,$this->protected['max']);
			$this->protected['min']=max($value,$this->protected['min']);
			$this->protected['maxLength']=max($this->protected['lastLength'],$this->protected['maxLength']);
			$this->protected['minLength']=max($this->protected['lastLength'],$this->protected['minLength']);
		}
	}
	private function setType_LONGTEXT(){
		$this->protected['length']=null;
	}
	private function setType_MEDIUMTEXT(){
		if($this->protected['maxLength']>16777215)$this->nextType();
		$this->protected['length']=null;
	}
	private function setType_TEXT(){
		if($this->protected['maxLength']>65535)$this->nextType();
		$this->protected['length']=null;
	}
	private function setType_EMUM(){
		$this->nextType();
	}
	private function setType_SET(){
		$this->nextType();
	}
	private function setType_VARCHAR(){
		if($this->protected['maxLength']>255)$this->setType('TEXT');
	}
	private function setType_CHAR(){
		if($this->protected['maxLength']>4) $this->nextType();
	}
	private function setType_DATETIME(){
		if($this->oldNumType) $this->setType('CHAR');
		else {
			$date=$this->isDate($this->protected['lastValue']);
			$time=$this->isTime($this->protected['lastValue']);
			$datetime="$date $time";
			if(!$date || !$time) $this->nextType();
			elseif($datetime!=$this->protected['lastValue']) $this->setLastValue($datetime);
		}
	}
	private function setType_TIME(){
		if($this->oldNumType) $this->setType('CHAR');
		else {
			$time=$this->isTime($this->protected['lastValue'],true);
			if(!$time) $this->nextType();
			elseif($time!=$this->protected['lastValue']) $this->setLastValue($time);
		}
	}
	private function setType_DATE($noForce=false){
		if($this->oldNumType) $this->setType('CHAR');
		else {
			$date=$this->isDate($this->protected['lastValue'],true);
			if(!$date) $this->nextType();
			elseif($date!=$this->protected['lastValue']) $this->setLastValue($date);
		}
	}
	private function setType_DOUBLE(){
		if($this->isNumeric()) $this->setPrecision();
	}
	private function setType_FLOAT(){
		if($this->isNumeric()){
			if(preg_match('/e[+-]?(\d*)$/i',$this->protected['lastValue'],$ret) && $ret[1]>38) $this->nextType();
			else $this->setPrecision();
		}
	}
	private function setType_DECIMAL(){
		if($this->isNumeric()){
			if($this->protected['maxLength']>11 || stripos($this->protected['lastValue'],'e')!==false) $this->nextType();
			else $this->setPrecision();
		}
	}
	private function setType_BIGINT(){
		if($this->isInteger()){
			if($this->protected['min']>=0) {
				if($this->protected['lastValue']>18446744073709551615) $this->nextType();
			}elseif($this->protected['lastValue']<-9223372036854775808 || $this->protected['lastValue']>9223372036854775807) $this->nextType();
		}
	}
	private function setType_INTEGER(){
		if($this->isInteger()){
			if($this->protected['min']>=0) {
				if($this->protected['lastValue']>4294967295) $this->nextType();
			}elseif($this->protected['lastValue']<-2147483648 || $this->protected['lastValue']>2147483647) $this->nextType();
		}
	}
	private function setType_MEDIUMINT(){
		if($this->isInteger()){
			if($this->protected['min']>=0) {
				if($this->protected['lastValue']>16777215) $this->nextType();
			}elseif($this->protected['lastValue']<-8388608 || $this->protected['lastValue']>8388607) $this->nextType();
		}
	}
	private function setType_SMALLINT(){
		if($this->isInteger()){
			if($this->protected['min']>=0) {
				if($this->protected['lastValue']>65535) $this->nextType();
			}elseif($this->protected['lastValue']<-32768 || $this->protected['lastValue']>32767) $this->nextType();
		}
	}
	private function setType_TINYINT(){
		if($this->isInteger()){
			if($this->protected['min']>=0) {
				if($this->protected['lastValue']>255) $this->nextType();
			}elseif($this->protected['lastValue']<-128 || $this->protected['lastValue']>127) $this->nextType();
		}
	}
	private function setType_YEAR(){
		if($this->isInteger()){
			$v=$this->protected['lastValue'];
			if($v>=0 && $v<=9) $this->protected['length']=max($this->protected['length'],2);
			elseif($v>=1901 && $v<=2155) $this->protected['length']=4;
			else $this->nextType();
		}
	}
	private function setType_BIT(){
		if($this->protected['maxLength']>1 || ord($this->protected['lastValue'])>1) $this->nextType();
		$this->protected['length']=1;
	}
	private function setType_NULL(){
		if(!is_null($this->protected['lastValue'])) $this->nextType();
	}
	private function setPrecision(){
		if(preg_match('/((\d*)(?:\.(\d*))?)(?:e([+-]?\d+))?/i',$this->protected['lastValue'],$ret)){
			$this->protected['precision']=max($this->protected['precision'],strlen((int)@$ret[3]));
			$this->protected['length']=max($this->protected['length'],strlen($ret[1]));
		}
	}
	private function isInteger(){
		if($this->isNumeric()){
			if(ceil($this->protected['lastValue'])==$this->protected['lastValue']) {
				$this->protected['length']=max($this->protected['length'],strlen(abs($this->protected['lastValue'])));
				return true;
			}
			$this->setType('DECIMAL');
		}
		return false;
	}
	private function isNumeric(){
		if(is_numeric($this->protected['lastValue'])) {
			if($this->protected['lastValue']<0) $this->protected['isUnsigned']=false;
			if(preg_match('/^00/',$this->protected['lastValue'])) $this->protected['isZeroFill']=true;
			return true;
		}
		$this->setType('DATE');
		return false;
	}
	private function isDate($date,$force=false){
		if($force) {
			$i='^';
			$f='$';
		} else $i=$f='';
		$fdate=preg_split('/[-\.\/]/',$this->protected['dateFormat']);
		
		$out=array('y'=>false,'m'=>false,'d'=>false);
		if(preg_match("/$i(\d{2,4})-(\d{1,2})-(\d{1,2})$f/",$date,$ret)){
			$out['y']=$this->isDate_y($ret[1]);
			$out['m']=$this->isDate_m($ret[2]);
			$out['d']=$this->isDate_d($ret[3]);
		}elseif(preg_match("/$i(\d{1,4}|\w+)([-\.\/])(\d{1,4}|\w+)\\2(\d{1,4}|\w+)$f/",$date,$ret)){
			unset($ret[0]);
			unset($ret[2]);
			$ret=array_values($ret);
			foreach($fdate as $k=>$cmd){
				$cmd=strtolower($cmd[0]);
				$fn="isDate_$cmd";
				$out[$cmd]=$this->$fn($ret[$k]);
			}
		} 
		if ($out['y'] && $out['m'] && $out['d']) {
			$date=implode('-',$out); 
			if($date==strftime('%F',strtotime($date))) return $date;
		}
		return false;
	}
	private function isDate_d($dia){
		if(!is_numeric($dia) || $dia==0 || $dia>31) return false;
		return str_pad($dia,2,0,STR_PAD_LEFT);
	}
	private function isDate_m($mes){
		if(!is_numeric($mes)) {
			if(preg_match('/^(jan(?:nuary|neiro)?)|(feb(?:bruary)?|fev(?:ereiro)?)|(mar(?:ch|[cç]o)?)|(apr(?:il)?|abr(?:il)?)|(may|maio?)|(jun(?:e|ho)?)|(jul(?:y|ho)?)|(aug(?:ust)?|ago(?:sto)?)|(sep(?:ptember)?|set(?:embro)?)|(oct(?:ober)?|out(?:rubro)?)|(nov(?:ember|embro)?)|(dec(?:ember)?|dez(?:embro)?)$/i',$mes,$ret)) {
				$mes=count($ret)-1;
			} else return false;
		}
		if($mes==0 || $mes>12) return false;
		return str_pad($mes,2,0,STR_PAD_LEFT);
	}
	private function isDate_y($ano){
		$tam=strlen($ano);
		if(!is_numeric($ano) || ($tam!=2 && $tam!=4)) return false;
		if($tam==2) $ano+=2000;
		return $ano;
	}
	private function isTime($time,$force=false){
		if($force) {
			$i='^';
			$f='$';
		} else $i=$f='';
		$out=array('h'=>false,'m'=>false,'s'=>false);
		if(preg_match("/$i\b(\d{1,2}):(\d{1,2})(?::(\d{1,2}))?\b(?: ?([ap])m?)?$f/i",$time,$ret)) {
			$out['h']=$this->isTime_h($ret[1],@$ret[4]);
			$out['m']=$this->isTime_ms($ret[2]);
			$out['s']=$this->isTime_ms(@$ret[3]);
		}
		if($out['h'] && $out['m'] && $out['s']) return implode(':',$out);
		else return false;
	}
	private function isTime_h($hour,$apm){
		if(strtolower($apm)=='p') $hour+=12;
		if($hour>24) return false;
		return str_pad($hour,2,0,STR_PAD_LEFT);
	}
	private function isTime_ms($time){
		if($time>60) return false;
		return str_pad($time,2,0,STR_PAD_LEFT);
	}
}
