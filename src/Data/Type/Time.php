<?php
namespace Type;

class Time extends DateTime implements \Interfaces\Type {
	protected $mask='%T.%N';
	protected $maskDefault='%T.%N';
	
	protected function rebuild(){
		return $this->value=$this->time.'.'.$this->nanoseconds;
	}
}