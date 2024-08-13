<?php
namespace Type;

class TimeStamp extends DateTime implements \Interfaces\Type {
	protected $mask='%s.%N';
	protected $maskDefault='%s.%N';
	
	protected function rebuild(){
		return $this->value=$this->ts;
	}
}