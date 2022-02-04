<?php
namespace DB;

class Error extends Pattern {
	function verifyError(){
		if($this->verifyError && $this->error()) $this->fatalError($this->sql);
	}
}