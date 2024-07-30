<?php
namespace Type;

class Float extends Number implements \Interfaces\Type {
	protected function _cast($value){
		return (float)parent::_cast($value);
	}
}