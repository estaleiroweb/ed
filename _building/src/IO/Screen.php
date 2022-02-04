<?php
class Screen {
	static public function gauge($args=[]) { //['percent'=><val or calc by value>,'treshold'=>array(50,80),'inverse'=>false,'dec'=>0,'value'=><percent>,"min'=>0,'max'=>100]
		$treshold=[50,80];
		$inverse=false;
		$dec=0;
		$value=$percent=null;
		$min=0;
		$max=100;
		if(is_array($args)) extract((array)$args);
		else $percent=$args;
		
		if(is_null($percent)) $percent=100*$value/$max;
		if(is_null($value)) $value=$percent;
		$p=max(0,min(100,round($percent,$dec)));
		$percent_str=$p.'%';
		
		$a=$inverse?['danger','success']:['success','danger'];
		$class=($p<=$treshold[0]?$a[0]:($p<=$treshold[1]?'warning':$a[1]));
		
		return "<div class='progress-bg progress-$class' title='Value=$value\nTotal=$max\nPercent=$percent%'><div class='progress-bar progress-bar-$class' role='progressbar' aria-valuenow='$p' aria-valuemin='$min' aria-valuemax='$max' style='width:$percent%'>$percent_str</div></div>";
	}
	static public function gauge_inverse($args=[]) {
		if(!is_array($args)) $args=['inverse'=>true,'percent'=>$args];
		return self::gauge(array_merge(['inverse'=>true,],$args));
	}
	static public function bit($value) {
		return '<span class="glyphicon glyphicon-'.(self::isSetBit($value)?'ok':'remove').'" aria-hidden="true"></span>';
	}
	static public function isSetBit($value) {
		if(!is_numeric($value)) $value=ord($value);
		return $value?true:false;
	}
}
