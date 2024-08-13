<?php

namespace EstaleiroWeb\ED\Tools;

use Exception;

class Tools {
	function nmap($port, $ip = 'localhost', $smart = true) {
		$port = (array)$port;
		$ip = (array)$ip;
		$out = array();
		$ret = false;
		$cont = 0;
		foreach ($ip as $i) {
			foreach ($port as $p) {
				$cont++;
				$conexao = @fsockopen($i, $p, $erro, $erro, 15);
				$ret = (bool)$conexao;
				if ($conexao) @fclose($conexao);
				$out[$i][$p] = $ret;
			}
		}
		if ($smart) return $cont <= 1 ? $ret : $out;
		else return $out;
	}
	function er_test($er) {
		try {
			preg_match($er, 'abc123');
		} catch (Exception $e) {
			return $e->getMessage();
		}
		$e = preg_last_error();
		if ($e == PREG_NO_ERROR) return '';
		if ($e == PREG_INTERNAL_ERROR) return 'INTERNAL ERROR';
		if ($e == PREG_BACKTRACK_LIMIT_ERROR) return 'BACKTRACK LIMIT ERROR';
		if ($e == PREG_RECURSION_LIMIT_ERROR) return 'RECURSION LIMIT ERROR';
		if ($e == PREG_BAD_UTF8_ERROR) return 'BAD UTF8 ERROR';
		if ($e == PREG_BAD_UTF8_OFFSET_ERROR) return 'BAD UTF8 OFFSET RROR';
	}
}
