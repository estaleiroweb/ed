<?php
namespace Type;

class Boolean extends Mixed implements \Interfaces\Type {
	protected function _cast($value){
		if($this->isNull) return false;
		if($this->type=='boolean') return $this->value;
		if($this->type=='string') {
			$value=trim($value);
			return !$value || preg_match('/^((turn[ _]?)?off|fals[eo]|desligad[ao])$/i',$value)?false:true;
		}
		return (bool)$value;
	}
	public function length(){ return $this->isNull?0:1; }
}