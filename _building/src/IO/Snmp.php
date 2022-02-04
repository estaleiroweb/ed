<?php
class Snmp {
	public $host,$community,$version,$output;
	public $timeOut=10;
	function __construct($host='localhost',$community='public',$version=false){
		$this->host=$host;
		$this->community=$community;
		$this->version=$version;
	}
	function get($oid=''){
		$a=$this->lineSplit(`snmpget{$this->mountParam($oid)}`);
		return isset($a[1])?$a[1]:$a[0];
	}
	function walk($oid=''){
		return $this->walkSplit(`snmpwalk{$this->mountParam($oid)}`);
	}
	private function mountParam($oid){
		return implode('',array(
			$this->timeOut && $this->timeOut<>10?" -t {$this->timeOut}":'',
			$this->version?" -v {$this->version}":'',
			$this->output?" -O{$this->output}":'',
			$this->community?" -c {$this->community}":'',
			$this->host?" {$this->host}":'',
			" $oid",
		));
	}
	function walkSplit($text){
		$tmp=preg_split("/\r\n|\n\r|\n|\r/",trim($text));
		$out=array();
		foreach ($tmp as $l) if ($l){
			$l=$this->lineSplit($l);
			if (isset($l[1])) $out[$l[0]]=$l[1];
			else $out[]=$l[0];
		}
		return $out;
	}
	function lineSplit($text){
		$text=preg_replace(array("/ /","/(\".*?\")/e"),array("\t","str_replace(\"\\t\",' ',\"\\1\")"),$text);
		return explode("\t",$text);
	}
}