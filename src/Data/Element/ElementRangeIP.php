<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Tools\IpBin;

class ElementRangeIP extends ElementIP {
	protected $typeList = array('ip');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'ip';
		$this->inputAttr['nwtype'] = null;
		parent::__construct($name, $value, $id);
		$this->mov('type', 'inputAttr');
		$this->inputformat = $this->displayformat = 'ip';
		$this->placeholder = '0.0.0.0/0';
		$this->size = 18;
		$this->maxlength = 18;
		$this->width = '14em';
	}
	function format($format = '', $value = false, $type = false) {
		if ($value === false) $value = $this->value;
		if ($this->isEdit() || $this->forceEdit) return $value;
		if (preg_match('/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/(\d{1,2})$/', $value, $ret)) {
			$ok = true;
			$out = [];
			for ($i = 1; $i < 5; $i++) {
				$v = $ret[$i];
				if ($v > 255) {
					$ok = false;
					break;
				} else $v = (int)$v;
				$out[] = $v;
			}
			$ret[5] = (int)$ret[5];
			if ($ret[5] > 32) $ok = false;
			$checkIp = $this->ip;
			$ip = implode('.', $out);
			if ($ok && $checkIp) {
				$m = $ret[5] + 0;
				$ipDec = IpBin::INET_ATON($ip);
				$net = IpBin::ipNet($ipDec, $m);
				$BCast = IpBin::ipBCast($ipDec, $m);
				if (preg_match('/^\s*net(work)?\s*$/i', $checkIp)) {
					if ($ipDec !== $net) $ok = false;
				} elseif (preg_match('/^\s*b(road)?cast\s*$/i', $checkIp)) {
					if ($ipDec !== $BCast) $ok = false;
				} elseif ($ipDec == $net || $ipDec == $BCast) $ok = false;
			}
			if ($ok) return $ip . '/' . $ret[5];
		}
		return $this->formatError($value);
	}
}
