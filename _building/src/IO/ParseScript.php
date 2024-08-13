<?php
namespace Sys;

class ParseScript {
	use \Traits\OO\GetterAndSetter;
	const LIMIT_STR="\xFE";
	private $trDn=array(
		'\\\\'=>"\xFEA",
		'\'\''=>"\xFEB",
		'\\\''=>"\xFEC",
		'""'  =>"\xFED",
		'\\"' =>"\xFEE",
		'\\`' =>"\xFEF",
	);
	private $trUp=array();
	
	public function __construct($value=null){ 
		$this->trUp=array_flip($this->trDn);
		$this->__invoke($value);
	}
	public function __invoke($value=null){ return $this->setScript_raw($value)->readonly['script']; }
	public function setScript_raw($value){
		$this->readonly=array(
			'script_raw'=>$value,
			'script'=>null,
			'parts'=>array(),
		);
		$this->protect=array();
		if($value!='') {
			$value=strtr($value,$this->trDn);
			if(preg_match_all('/(["\'`])([^\1]*?)\1/',$value,$ret,PREG_SET_ORDER+PREG_OFFSET_CAPTURE)) while($ret) {
				$item=array_pop($ret);
				$item[0][0]=strtr($item[0][0],$this->trUp);
				$this->addPart($value,$item);
			}
			$this->readonly['script']=$this->parserDn($value);
		}
		return $this;
	}
	public function parser($value,$force=false){
		$out=array();
		$er='/'.self::LIMIT_STR.'(\d+)'.self::LIMIT_STR.'/';
		if(preg_match_all($er,$value,$ret,PREG_SET_ORDER+PREG_OFFSET_CAPTURE)) {
			print_r($ret);
			$ini=0;
			do{
				$item=array_shift($ret);
				$out[]=substr($value,$ini,$item[0][1]-$ini);
				$ini=$item[0][1]+strlen($item[0][0]);
				$out[]=$this->parser($this->readonly['parts'][$item[1][0]],true);
			}while($ret);
			$out[]=substr($value,$ini);
		} else $out[]=$value;
		return $force?implode('',$out):$out;
	}
	private function parserDn($value){
		while(preg_match_all('/\([^\(\)\{\}\[\]]*?\)|\[[^\(\)\{\}\[\]]*?\]|\{[^\(\)\{\}\[\]]*?\}/',$value,$ret,PREG_SET_ORDER+PREG_OFFSET_CAPTURE)) {
			while($ret) $this->addPart($value,array_pop($ret));
		}
		while(preg_match_all('/\([^\)]*?\)|\[[^\]]*?\]|\{[^\}]*?\}/',$value,$ret,PREG_SET_ORDER+PREG_OFFSET_CAPTURE)) {
			while($ret) $this->addPart($value,array_pop($ret));
		}
		return $value;
	}
	private function addPart(&$value,$item){
		$i=count($this->readonly['parts']);
		$key=self::LIMIT_STR.$i.self::LIMIT_STR;
		$this->readonly['parts'][$i]=$item[0][0];
		$value=substr_replace($value,$key,$item[0][1],strlen($item[0][0]));
	}
	public function parserDn2($value,$er){
		while(preg_match_all('/\([^\(\)\{\}\[\]]*?\)|\[[^\(\)\{\}\[\]]*?\]|\{[^\(\)\{\}\[\]]*?\}/',$value,$ret,PREG_SET_ORDER+PREG_OFFSET_CAPTURE)) {
			while($ret) $this->addPart($value,array_pop($ret));
		}
		while(preg_match_all('/\([^\)]*?\)|\[[^\]]*?\]|\{[^\}]*?\}/',$value,$ret,PREG_SET_ORDER+PREG_OFFSET_CAPTURE)) {
			while($ret) $this->addPart($value,array_pop($ret));
		}
		return $value;
	}
}
/*
\b(BEGIN|LOOP|REPEATWHILE|CASE|IF)\b(.+|\s+)
\b(END\s+(?:LOOP|REPEAT|WHILE|CASE|IF)?)\b
*/
