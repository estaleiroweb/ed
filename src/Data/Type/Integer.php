<?php
namespace Type;

class Integer extends Number implements \Interfaces\Type {
	protected function _cast($value){
		return (int)parent::_cast($value);
	}
}