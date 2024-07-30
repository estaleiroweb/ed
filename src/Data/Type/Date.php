<?php
namespace Type;

class Date extends DateTime implements \Interfaces\Type {
	protected $mask='%F';
	protected $maskDefault='%F';
	
	protected function rebuild(){
		return $this->value=$this->date;
	}
}