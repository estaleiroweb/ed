<?php
class Gauge {
	private $cont;
	public $total;
	public $bar_len=50;
	public $bar_ini='[';
	public $bar_end=']';
	private $oldTam,$timeStart;

	public function __construct($total=100){
		$this->total=$total;
		$this->start();
	}
	public function __toString(){ return $this->__invoke(); }
	public function __invoke($cont=null){
		$this->cont=min($this->total,is_null($cont)?($this->cont+1):$cont);
		
		$perc=$this->cont/$this->total;
		$txt=sprintf('%.1f%% (%d/%d)',$perc*100,$this->cont,$this->total).' ';

		$deltaT=microtime(true)-$this->timeStart;
		$x=0;
		$t=[];
		if($perc>.01 && $deltaT>1) $x=($deltaT * (1-$perc)) / $perc;
		if($deltaT>1) $t[]=$this->time_tr($deltaT);
		if($x>1) $t[]=$this->time_tr($x).' rest';
		$txt.=implode('/',$t);

		$bk=str_repeat(chr(8),$this->oldTam);
		$spc=str_repeat(' ',$this->oldTam);
		$this->oldTam=$this->bar_len+strlen($txt)+strlen($this->bar_ini.$this->bar_end);
		return $bk.$spc.$bk.$this->bar($perc).$txt;
	}
	public function start(){
		$this->oldTam=0;
		$this->cont=0;
		$this->timeStart=microtime(true);
	}
	public function bar($perc){
		$fullfill=$this->bar_len*$perc;
		$fill1=(int)$fullfill;
		$fill2=round($fullfill-$fill1,0);
		return $this->bar_ini.
			str_repeat('█',$fill1).
			str_repeat('▌',$fill2).
			str_repeat('-',$this->bar_len-$fill1-$fill2).
			$this->bar_end;
	}
	private function time_tr($time){
		$m='sec';
		if($time>60) {
			$m='min';
			$time/=60;
			if($time>60) {
				$m='hs';
				$time/=60;
				if($time>24) {
					$m='D';
					$time/=24;
				}
			}
		}
		return number_format($time,2).$m;
	}
}