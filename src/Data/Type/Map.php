<?php
namespace Type;

class Map extends Mixed implements \Interfaces\Type {
	protected function _cast($value){
		switch ($this->type) {
			case 'array': return $value;
			case 'null':  return [];
			case 'string': return preg_split('/\s*[,;]\*/',$value);
			//case 'object': return (array)$value;
		}
		return (array)$value;
	}
	public function length(){ return count($this->value); }
}