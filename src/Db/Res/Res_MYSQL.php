<?php

namespace EstaleiroWeb\ED\Db\Res;

class Res_MYSQL extends Res {
	//sssssspublic function __construct() {
		//_::show(func_get_args());
		//call_user_func_array('parent::'.__FUNCTION__,func_get_args());
	//}
	public function __construct2($dadObj, $sql, $verifyError = true, $dsn = '') {
		parent::__construct($dadObj, $sql, $verifyError, $dsn);
		if (@$this->conn->multi_query($sql)) $this->res = @$dadObj->store_result();
		$this->verifyError($sql);
		/*mysqli_result Object(
			[current_field] => 0
			[field_count] => 5
			[lengths] => Array(
			[0] => 3
			[1] => 3
			[2] => 1
			[3] => 19
			[4] => 23
			)
			[num_rows] => 181
			[type] => 0
		)*/
	}
	public function fields() {
		$out = array();
		if ($this->res) {
			$o = @$this->res->fetch_fields();
			if ($o) foreach ($o as $obj) $out[] = $obj->orgname ? $obj->orgname : $obj->name;
		}
		return $out;
	}
	public function reccount() {
		return $this->conn->fastValue('SELECT FOUND_ROWS()');
	}
}
