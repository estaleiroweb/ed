<?php
class GroupButton {
	public $arrayButtons,$chumk;
	
	function __construct($arrayButtons,$chumk=4){
		$this->arrayButtons=$arrayButtons;
		$this->chumk=$chumk;
	}
	function __tostring(){
		$out="<table border='0' cellspacing='0' cellpadding='10' align='center'>\n";
		$linhas=array_chunk($this->arrayButtons,$this->chumk);
		foreach ($linhas as $b) {
			$out.="<tr>\n";
			foreach ($b as $lnk) $out.="<td>{$lnk->__tostring()}</td>";
			$out.="</tr>\n";
		}
		$out.="</table>\n";
		return $out;
	}
}