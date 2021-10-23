<?php

namespace EstaleiroWeb\ED\Tools;

use EstaleiroWeb\Traits\GetterAndSetter;

class Range {
	use GetterAndSetter;

	public function __construct() { {
			$this->protect = array(
				'start' => null,
				'end' => null,
				'step' => null,
				'min' => null,
				'max' => null,
			);
		}
		//$this->init(func_get_args());
		$this->init();
	}
	public function __invoke() {
		$this->init();
		return $this->toArray();
	}

	public function getLimits() {
		return $this->protect;
	}
	public function getRange() {
		return $this->getLimits();
	}
	public function getMin() {
		return $this->protect['min'];
	}
	public function getMax() {
		return $this->protect['max'];
	}
	public function getStart() {
		return is_null($this->protect['start']) ? $this->protect['min'] : $this->protect['start'];
	}
	public function getEnd() {
		return is_null($this->protect['end']) ? $this->protect['max'] : $this->protect['end'];
	}
	public function getStep() {
		return $this->protect['step'];
	}

	public function setMin($value) {
		return $this->checkRangeLimits('min', $value);
	}
	public function setMax($value) {
		return $this->checkRangeLimits('max', $value);
	}
	public function setStart($value) {
		return $this->checkRangeLimits('start', $value);
	}
	public function setEnd($value) {
		return $this->checkRangeLimits('end', $value);
	}
	public function setStep($value) {
		$this->protect['step'] = $value ? $value + 0 : null;
		return $this;
	}

	public function has($value, $checkStep = false) {
		$start = $this->getStart();
		$end = $this->getEnd();
		if (!is_null($start) && $start > $value) return false;
		if (!is_null($end)   && $end < $value)   return false;
		if ($checkStep && ($step = $this->getStep())) {
			if (is_null($start) && is_null($end)) return true;
			if (is_null($start)) {
				$a = range($value, $end, $step);
				$value = $end;
			} elseif (is_null($end)) $a = range($start, $value, $step);
			else $a = range($start, $end, $step);
			return in_array($value, $a);
		}
		return true;
	}
	public function toArray() {
		if (is_null($this->protect['start']) || is_null($this->protect['end'])) return null;
		return range($this->protect['start'], $this->protect['end'], $this->protect['step']);
	}
	public function initOne($arg) {
		$arg = explode('-', $arg, 2);
		if (count($arg) == 1) $arg[1] = $arg[0];
		foreach ($arg as &$v) if (is_numeric($v)) $v += 0;
		return $this->init(array($arg));
	}
	public function swap(&$v1, &$v2) {
		$tmp = $v1;
		$v1 = $v2;
		$v2 = $tmp;
	}
	public function rebuildMinMax(&$min, &$max) {
		if (!is_null($min) && !is_null($max) && $max < $min) $this->swap($min, $max);
	}

	private function checkRangeLimits($nm, $value) {
		$value === '' ? null : $value;
		$this->protect[$nm] = $value;
		$this->rebuildMinMax($this->protect['min'], $this->protect['max']);
		$this->rebuildMinMax($this->protect['start'], $this->protect['end']);
		if (!is_null($this->protect['start'])) {
			if (!is_null($this->protect['min']) && $this->protect['start'] < $this->protect['min']) $this->protect['start'] = $this->protect['min'];
			elseif (!is_null($this->protect['max']) && $this->protect['start'] > $this->protect['max']) $this->protect['start'] = $this->protect['max'];
		}
		if (!is_null($this->protect['end'])) {
			if (!is_null($this->protect['min']) && $this->protect['end'] < $this->protect['min']) $this->protect['end'] = $this->protect['min'];
			elseif (!is_null($this->protect['max']) && $this->protect['end'] > $this->protect['max']) $this->protect['end'] = $this->protect['max'];
		}
		return $this;
	}
}
