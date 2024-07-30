<?php

namespace EstaleiroWeb\ED\Db\Detail;

use EstaleiroWeb\ED\Db\GetterAndSetter;
use Iterator;
use Countable;

abstract class Detail implements Iterator, Countable {
	use GetterAndSetter;

	const DELIMIT = "\xFE";
	protected $erTable;
	protected $erAlias;
	protected $conn;
	protected $query;
	protected $hash;
	protected $position = 0;
	protected $querys = [];
	protected $details = [];
	protected $tmp = [];
	protected $reserved_words = [];

	public function __construct($conn, $query) {
		$this->conn = $conn;
		$this->query = $query;
		$this->init_vars();
	}
	//public function __destruct() {}
	public function __toString() {
		return "{$this->query}";
	}
	public function __invoke() {
		$args = func_get_args();
		array_unshift($args, $this->querys);
		return call_user_func_array([$this->conn, 'query'], $args);
	}

	public function current() {
		return @$this->querys[$this->position];
	}
	public function key() {
		return $this->position;
	}
	public function next() {
		$this->position++;
	}
	public function rewind() {
		$this->position = 0;
	}
	public function valid() {
		return array_key_exists($this->position, $this->querys);
	}
	public function count() {
		return count($this->querys);
	}

	protected function init_vars() {
		$this->readonly['view'] = null;
		$this->readonly['sp'] = [];
		//$this->readonly['result']=null;
		$this->readonly['parameters'] = [];
		$this->readonly['error'] = false;
		return $this;
	}
	public function save($view) {
		//if(!$this->conn || !$this->view) return;
		$this->hash = md5(preg_replace(array('/^\s+/', '/\s+$/', '/\s+/'), array('', '', ' '), $view));
		if (array_key_exists($this->hash, $this->querys)) $this->readonly = $this->querys[$this->hash];
		else {
			$this->init_vars();
			$this->readonly['view'] = $view;
			$this->parser_query($view);
			//(show|set||||||||||||||||||)\b/i',$q)) continue;

			$this->querys[$this->hash] = $this->readonly;
		}
		return $this;
	}

	protected function parser_query($view) {
		$exe = false;
		$this->readonly['sp'] = [];
		$a = $this->regexp_table_alias('/^' . $this->erAlias . '$/i', $view);
		if ($a) {
			$this->fill_sp($view, [
				'type' => 'DQL',
				'full' => 'SELECT * FROM ' . $view,
				'alias' => [$a['alias'] => $a],
			]);
		} else {
			//split view
			$view = $this->split_query($view);
			foreach ($view as $k => $sp) $this->parser_query_splited($sp, $exe);
			//print_r([$this->tmp,$view]);exit;
		}
		return $this;
	}
	protected function parser_query_splited($view, &$exe) {
		//if($view[0]==self::DELIMIT) return $this->parser_query_splited($this->show_char($view),$exe);
		$sp = $this->show_char_all($view);
		if (preg_match('/^(SHOW|CALL|EXPLAIN|DESCRIBE|HELP)\b/i', $sp)) {
			$exe = true;
			return $this->fill_sp($view, $sp, array('type' => 'DQL'));
		}
		if (!preg_match('/^[\(\s]*SELECT\s/i', $sp)) return $this->fill_sp($view, $sp, array('type' => 'PRS'));
		$parts = $this->parts_of_query($view);
		print_r($parts);
		exit;

		if (preg_match_all("/(?<=\sfrom|\sjoin|\sstraight_join|call)[\s\(]+(?!select)(?:(?:{$this->erTable}\.)?{$this->erTable}(?:\s+(?!union|where|on|select|group|having|order|limit|procedure|(?:inner\s+|cross\s+|straight_|left\s+)?join)(?:as\s+)?{$this->erTable})?(?:\s*,\s*)?)+/i", $sp, $ret)) {
			$alias = [];
			foreach ($ret[0] as $v) {
				$a = $this->regexp_table_alias('/' . $this->erAlias . '/i', $v);
				$alias[$a['alias']] = $a;
			}
			$this->fill_sp($view, $sp, array('alias' => $alias));
		}
	}
	protected function parts_of_query($view) {
		$erSQLhead = 'ALL|DISTINCT|DISTINCTROW|HIGH_PRIORITY|MAX_STATEMENT_TIME\s*=\s*\d+|STRAIGHT_JOIN|SQL_SMALL_RESULT|SQL_BIG_RESULT|SQL_BUFFER_RESULT|SQL_CACHE|SQL_NO_CACHE|SQL_CALC_FOUND_ROWS';
		if (!preg_match('/^(?<begin>[\s(]+)?(?<select_word>\bSELECT\b)\s*(?<header>(?:(?:' . $erSQLhead . ')\s+)+)?/i', $view, $ret)) return $this->parts_of_query($this->show_char($view));
		$out = $this->strip_er_ret($view, $ret);
		$rest = '';
		$nest = false;
		if (preg_match('/(\s*(?:\)|\bUNION\b)(?:.|\s)*)$/', $view, $ret)) {
			$rest = $ret[1];
			$view = str_replace($rest, '', $view);
			$nest = true;
		}
		if (preg_match('/^(?<fields>(?:.|\s)+?)\s*\b(?<from_word>FROM)\b\s*/i', $view, $ret)) {
			$out = array_merge($out, $this->strip_er_ret($view, $ret));
			$ers = array(
				'update_share'  => 'FOR\s+UPDATE|LOCK\s+IN\s+SHARE\s+MODE',
				'outfile'  => 'INTO\s+OUTFILE',
				'procedure' => 'PROCEDURE',
				'limit'    => 'LIMIT',
				'order_by' => 'ORDER\s+BY',
				'having'   => 'HAVING',
				'group_by' => 'GROUP\s+BY',
				'where'    => 'WHERE',
			);
			$acumulate = [];
			foreach ($ers as $k => $v) if (preg_match('/\s*\b(' . $v . ')\b\s*((?:.|\s)+)?$/i', $view, $ret)) {
				if (@$ret[2]) $acumulate[$k] = $ret[2];
				$acumulate[$k . '_WORD'] = $ret[1];
				$view = $this->strip_er_retView_End($view, $ret);
				if ($k == 'where') $nest = true;
			}
			$out['from'] = $view;
			$out = array_merge($out, array_reverse($acumulate, true));
		} else $out['fields'] = $view;
		$out['end'] = $rest;
		//if(preg_match('/^(?<begin>(?:.|\s)*SELECT)\s+(?<header>(?:(?:'.$erSQLhead.')\s+)+)?/i',$view,$parts)) return $parts;
		if (@$out['from']) $out['from'] = $this->join2tables($out['from'], $tables);
		else $tables = null;
		return array(
			'query' => $out,
			'details' => array(
				'nest' => $nest,
				'tables' => $tables,
			),
		);
		/*SELECT
			[ALL | DISTINCT | DISTINCTROW ]
			  [HIGH_PRIORITY]
			  [MAX_STATEMENT_TIME = N]
			  [STRAIGHT_JOIN]
			  [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT]
			  [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
			select_expr [, select_expr ...]
			[FROM table_references
			  [PARTITION partition_list]
			[WHERE where_condition]
			[GROUP BY {col_name | expr | position}
			  [ASC | DESC], ... [WITH ROLLUP]]
			[HAVING where_condition]
			[ORDER BY {col_name | expr | position}
			  [ASC | DESC], ...]
			[LIMIT {[offset,] row_count | row_count OFFSET offset}]
			[PROCEDURE procedure_name(argument_list)]
			[INTO OUTFILE 'file_name'
				[CHARACTER SET charset_name]
				export_options
			  | INTO DUMPFILE 'file_name'
			  | INTO var_name [, var_name]]
			[FOR UPDATE | LOCK IN SHARE MODE]]
		*/
	}
	protected function join2tables($join, &$tables = []) {
		//return $join;
		if (preg_match('/^\s*' . self::DELIMIT . '(\d+)' . self::DELIMIT . '\s*$/', $join, $ret)) {
			$join = $this->tmp[$ret[1]];
			$sub = $join[0] == '(' ? preg_replace(array('/^\s*\(\s*/', '/\s*\)\s*$/'), '', $join) : $join;
			$this->join2tables($sub, $tables);
		}
		//if(preg_match('/^\s*'.$this->erAlias.'\s*(?:(?<join_type>,)(?<rest>(?:.|\s)+))?$/i',$join,$ret)
		//if(preg_match())
		return $join;
		/*
			$tmp=$this->tmp[$ret[1]];
			
			//if($tmp[0]=='('){
			//}
			if(preg_match('/^[\(\s]*SELECT\s/i',$tmp){
				//recursive class
				$tbl=self::singleton($this->conn);
				$tbl($tmp);
				$db=null;
				$join=$this->strip_er_retView_Begin($join,$ret);
				$alias=[];
				$ret=$this->strip_demiliters(;
				$tables[$alias]=array(
					//'db'=>null,
					'tbl'=>$tbl,
					'alias'=>$alias,
				);
				
			}
			else $join=$this->strip_er_retView_Begin($join,$ret,$tmp);
		}
		*/
	}
	protected function join2tables_drop_alias($join, &$tables = []) {
		if (preg_match('/^[\)\s]*' . self::DELIMIT . '(\d+)' . self::DELIMIT . '/', $join, $ret)) {
		}
	}
	protected function join2tables_on_using($join, &$tables = []) {
		if (preg_match('/^[\)\s]*' . self::DELIMIT . '(\d+)' . self::DELIMIT . '/', $join, $ret)) {
		}
	}

	protected function regexp_table_alias($er, $view) {
		if (!preg_match($er, $view, $ret)) return [];

		$conn = $this->conn;
		$ret = $this->strip_demiliters([
			'db' => $ret['db'] ? $ret['db'] : $conn->db,
			'tbl' => $ret['tbl'],
			'alias' => @$ret['alias'] ? $ret['alias'] : $ret['tbl'],
		]);
		return $ret;
	}
	protected function strip_demiliters($value) {
		return preg_replace(array('/^`/', '/`$/',), '', $value);
	}
	protected function fill_sp($view, $sp, $item = []) {
		$this->readonly['sp'][] = array_merge([
			'type' => 'SELECT',
			'compact' => $view,
			'query' => $sp,
			'nest' => false,
			'alias' => null,
			'details' => null,
			'fields' => null,
		], $item);
		return $this;
	}
	protected function split_query($view) {
		$this->tmp = ['\\\\', '\\\'', '\\"', '\'\'', '""'];
		foreach ($this->tmp as $k => $v) $view = str_replace($v, self::DELIMIT . $k . self::DELIMIT, $view);
		$view = preg_replace(array('/("[^"]*")/e', '/(\'[^\']*\')/e', '/(`[^`]*`)/e'), '$this->hiden_char("\1")', $view);
		do {
			$view = preg_replace('/(\([^\(\)]*\))/ie', '$this->hiden_char("\1")', $old = $view);
		} while ($view != $old);
		$view = preg_split('/\s*;\s*/', $view);
		return $view;
	}
	protected function hiden_char($text) {
		if ($text[0] == '`' && !preg_match('/(' . self::DELIMIT . '|[;,\.\'"\(\)\[\]])/', $text)) return $text;
		$c = count($this->tmp);
		$this->tmp[] = $text;
		return self::DELIMIT . $c . self::DELIMIT;
	}
	protected function show_char($text) {
		return preg_replace('/' . self::DELIMIT . '(\d+)' . self::DELIMIT . '/e', '$this->tmp[\1]', $text);
	}
	protected function show_char_all($text) {
		if (is_array($text)) foreach ($text as $k => $v) $text[$k] = $this->show_char_all($v);
		else do {
			$text = $this->show_char($old = $text);
		} while ($old != $text);
		return $text;
	}
	protected function strip_er_ret(&$view, $ret) {
		$view = $this->strip_er_retView_Begin($view, $ret);
		foreach ($ret as $k => $v) if (is_numeric($k)) unset($ret[$k]);
		else $ret[$k] = trim($v);
		return $ret;
	}
	protected function strip_er_retView_Begin($view, $ret, $replace = '') {
		return preg_replace('/^' . preg_quote($ret[0], '/') . '/', $replace, $view);
	}
	protected function strip_er_retView_End($view, $ret, $replace = '') {
		return preg_replace('/' . preg_quote($ret[0], '$/') . '/', $replace, $view);
	}
}
