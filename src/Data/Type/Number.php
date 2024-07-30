<?php
namespace Type;

class Number extends Mixed implements \Interfaces\Type {
	protected function _cast($value){
		if($this->isNull) return null;
		switch ($this->type) {
			case 'integer': case 'double': case 'float': case 'number': return $value;
			case 'object': $value=(array)$value;
			case 'array':  $value=preg_replace('/[\r\n]\s*\[([\'"])*.?\1\]\s*=>\s*/','',print_r($value,true));
			case 'string': break;
			default: $value=(string)$value;
		}
		return preg_match('[+-]?(\d+|(\d*\.\d+)|(\d+\.\d*))([eE][+-]\d+)?',$value,$ret)?$ret[0]:preg_replace('/\D/',$value);
	}
}
