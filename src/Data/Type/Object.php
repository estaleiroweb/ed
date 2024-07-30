<?php
namespace Type;

class Object extends Mixed implements \Interfaces\Type {
	protected function _cast($value){
		if($this->isNull) return null;
		if($this->type=='object') return $value;
		$o=new Map($value);
		return (object)$o->value();
	}
	public function length(){ return count($this->value); }
}