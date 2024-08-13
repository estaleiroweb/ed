<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Ext\BootstrapDateTimePicker;

class ElementCalendar extends Element {
	protected $typeList = array('datetime', 'date', 'time', 'year');
	protected $modeList = array('component', 'input', 'inline');
	protected $modeBuilded = '';

	public function __construct($name = '', $value = null, $id = null) {
		$this->protect['to'] = null; {
			$this->inputAttr['mode'] = $this->modeList[0];
			$this->inputAttr['format'] = null;
			$this->inputAttr['dayViewHeaderFormat'] = null;
			$this->inputAttr['extraFormats'] = null;
			$this->inputAttr['minDate'] = null;
			$this->inputAttr['maxDate'] = null;
			$this->inputAttr['stepping'] = null;
			$this->inputAttr['useCurrent'] = null;
			$this->inputAttr['collapse'] = null;
			$this->inputAttr['locale'] = null;
			$this->inputAttr['defaultDate'] = null;
			$this->inputAttr['disabledDates'] = null;
			$this->inputAttr['enabledDates'] = null;
			$this->inputAttr['icons'] = null;
			$this->inputAttr['useStrict'] = null;
			$this->inputAttr['sideBySide'] = null;
			$this->inputAttr['daysOfWeekDisabled'] = null;
			$this->inputAttr['calendarWeeks'] = null;
			$this->inputAttr['viewMode'] = null;
			$this->inputAttr['toolbarPlacement'] = null;
			$this->inputAttr['showTodayButton'] = null;
			$this->inputAttr['showClear'] = null;
			$this->inputAttr['showClose'] = null;
			$this->inputAttr['widgetPositioning'] = null;
			$this->inputAttr['widgetParent'] = null;
			$this->inputAttr['keepOpen'] = null;
			$this->inputAttr['inline'] = null;
			$this->inputAttr['keepInvalid'] = null;
			$this->inputAttr['keyBinds'] = null;
			$this->inputAttr['debug'] = null;
			$this->inputAttr['ignoreReadonly'] = null;
			$this->inputAttr['disabledTimeIntervals'] = null;
			$this->inputAttr['allowInputToggle'] = null;
			$this->inputAttr['focusOnShow'] = null;
			$this->inputAttr['enabledHours'] = null;
			$this->inputAttr['disabledHours'] = null;
			$this->inputAttr['viewDate'] = null;
		}
		parent::__construct($name, $value, $id);
		$this->mov('value')->mov('ed-element', 'inputAttr')->mov('ed-class', 'inputAttr')->mov('ed-form-id', 'inputAttr')->mov('ed-form-fieldname', 'inputAttr')->mov('type', 'inputAttr')->mov('readonly', 'displayAttr');
	}

	public function setType($value, $nm = 'type') {
		switch ($value) {
			case 'datetime':
			case 'timestamp':
				$this->set('type', 'datetime');
				$this->inputformat = '%F %T';
				$this->displayformat = '%x %X';
				//$this->displayformat='%d/%m/%Y %T';
				$this->width = '19rem';
				break;
			case 'date':
				$this->set('type', 'date');
				$this->inputformat = '%F';
				$this->displayformat = '%x';
				//$this->displayformat='%d/%m/%Y';
				$this->width = '12rem';
				break;
			case 'time':
				$this->set('type', 'time');
				$this->inputformat = '%T';
				$this->displayformat = '%T';
				$this->width = '8rem';
				break;
			case 'year':
				$this->set('type', 'year');
				$this->inputformat = '%Y';
				$this->displayformat = '%Y';
				$this->width = '4rem';
				break;
		}
		return $this;
	}
	public function setMode($value) {
		return parent::setType($value, 'mode');
	}
	public function setInputformat($value) {
		if ($value) $this->set('inputformat', $value);
		return $this;
	}
	public function setDisplayformat($value) {
		if ($value) $this->set('displayformat', $value);
		return $this;
	}
	public function setValue($value, $target = 'value') {
		//show(array($value));
		if (is_null($value) || $value == '') return $this->set($target, $value);
		$len = strlen($value);
		if ($len == 4 || $len == 2) $value .= '-01-01';
		if (is_string($value)) $value = strtotime($value);
		if (is_numeric($value) && $value != 0) return $this->set($target, strftime("%F %T", $value));
		return $this;
	}
	public function setMinDate($value) {
		return $this->setValue($value, 'minDate');
	}
	public function setMaxDate($value) {
		return $this->setValue($value, 'maxDate');
	}
	public function setMin($value) {
		return $this->setMinDate($value);
	}
	public function setMax($value) {
		return $this->setMaxDate($value);
	}
	public function getMinDate() {
		$v = $this->get('minDate');
		return $v ? min($this->value, $v) : null;
	}
	public function getMaxDate() {
		$v = $this->get('maxDate');
		return $v ? max($this->value, $v) : null;
	}
	public function getMin() {
		return $this->getMinDate();
	}
	public function getMax() {
		return $this->getMaxDate();
	}

	function format($format = '', $value = false, $type = false) {
		if ($type === false) $type = $this->type;
		if ($value === false) $value = $this->value;
		if (!$format) $format = "%F %T";
		if ($type == "year") return $this->func(str_pad((int)$value, 4, "0", STR_PAD_LEFT));
		else {
			preg_match("/^(\d{2,4}-\d{1,2}-\d{1,2})? ?(\d{1,2}:\d{1,2}:\d{1,2})?$/", $value, $ret);
			if (!$value) return '';
			if (!@$ret[1]) $ret[1] = "1970-01-01";
			if (!@$ret[2]) $ret[2] = "00:00:00";
			//show(array(locale_get_default(),$format,"{$ret[1]} {$ret[2]}",strftime($format,strtotime("{$ret[1]} {$ret[2]}"))));
			return $this->func(strftime($format, strtotime("{$ret[1]} {$ret[2]}")));
		}
	}
	function filterFormat($format) {
		return preg_replace(
			array('/%%/', '/%c/', '/%x/', '/%X/', '/%D/', '/%F/', '/%T/', '/%R/', "/%\\0/"),
			array("%\0", '%x %X', '%F', '%T', '%m/%d/%y', '%Y-%m-%d', '%H:%M:%S', '%I:%M:%S %p', '%%'),
			$format
		);
	}
	function buildLocaleFormatDateTime() {
		return $this->buildLocaleFormatDate() . ' ' . $this->buildLocaleFormatTime();
	}
	function buildLocaleFormatDate() {
		return strtr(
			strftime('%x', strtotime('2001-02-03')),
			array('2001' => '%Y', '02' => '%m', '03' => '%d', '01' => '%y', '2' => '%m', '3' => '%e')
		);
	}
	function buildLocaleFormatTime() {
		return strtr(
			strtolower(strftime('%X', strtotime('13:02:04'))),
			array('01' => '%I', '13' => '%H', '02' => '%M', '2' => '%M', '04' => '%S', '4' => '%S', 'am' => '%p', 'pm' => '%p')
		);
	}
	public function buildValues() {
		$val = $this->value;
		if ($val && ($timestamp = @strtotime($val))) {
			$valueI = strftime($this->inputformat, $timestamp);
			$valueD = strftime($this->displayformat, $timestamp);
		} else $valueI = $valueD = '';
		return array('input' => $valueI, 'display' => $valueD);
	}

	public function makeContent() {
		//show($this->{'ed-class'});
		$v = $this->value;
		$val = $v ? $this->format($this->displayformat ? $this->displayformat : $this->displayformatDefault) : 'inexistente';
		$attr = $this->makeHtmlAttrId();
		$attr .= $this->buildAttr();
		$attr .= $this->buildStyles();
		$attr .= $this->makeAttrib();
		$attr .= ' value=\'' . htmlspecialchars($v, ENT_QUOTES) . '\'';
		return $this->htmlLabel() . "<span$attr>$val</span>";
	}
	public function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		new BootstrapDateTimePicker();
		$this->script()->style();
		$widthSwap = null;
		if (($to = $this->to)) {
			$dsp2 = $this->makeControl_separator() . $to->make_item($widthSwap);
			$unit = $to->makeWitdh_unit();
			$witdhTo = $unit != '%' && $unit == $this->makeWitdh_unit() ? $to->width : 0;
			$to->width = $widthSwap;
		} else {
			$dsp2 = '';
			$witdhTo = 0;
		}
		$dsp1 = $this->make_item($widthSwap, $witdhTo);
		$out = $this->htmlLabel() . $this->make_begin() . $dsp1 . $dsp2 . '</div>';
		$this->width = $widthSwap;
		return $this->outControl($out);
	}
	public function make_begin() {
		$attr = $this->buildAttr();
		$a = array('inputformat', 'displayformat');
		foreach ($a as $k) if (!is_null($v = $this->$k)) $attr .= $k . '="' . $v . '" ';
		return '<div' . $this->makeHtmlAttrId('bx_') . ' class="input-group"  ' . $attr . $this->buildStyles() . '>';
	}
	public function buildAttr() {
		$attr = '';
		foreach ($this->inputAttr as $k => $v) {
			$v = $this->$k;
			if (!is_null($v)) $attr .= $k . '="' . $v . '" ';
		}
		return $attr;
	}
	public function make_item(&$widthSwap, $add = 0) {
		$widthSwap = $this->makeWitdh_item($add);
		$val = $this->buildValues();
		return $this->makeControl_hidden($val['input']) . $this->makeControl_item($val['display']);
	}
	public function makeWitdh_item($add = 0) {
		$widthSwap = $this->width;
		$this->makeWitdh_value($this->makeWitdh_unit(), $add);
		return $widthSwap;
	}
	public function makeWitdh_unit() {
		return preg_replace('/[\d ]+/', '', $this->width);
	}
	public function makeWitdh_value($unit, $add) {
		$this->modeBuilded = $this->readonly || $this->disabled ? 'input' : $this->mode;
		$this->{'makeWitdh_' . $this->modeBuilded}($unit, $add);
	}
	public function makeWitdh_component($unit, $add) {
		$this->width = ($add + $this->width + 4) . $unit;
	}
	public function makeWitdh_input($unit, $add) {
		$this->width = ($add + $this->width) . $unit;
	}
	public function makeWitdh_inline($unit, $add) {
		$this->width = null;
	}
	public function makeControl_item($valueD) {
		return $this->{'makeControl_' . $this->modeBuilded}($valueD);
	}
	public function makeControl_component($valueD) {
		//class date
		$out = $this->makeControl_display($valueD);
		$out .= $this->makeControl_button();
		return $out;
	}
	public function makeControl_input($valueD) {
		//class date
		return $this->makeControl_display($valueD);
	}
	public function makeControl_inline($valueD) {
		//class date
		return '<div' . $this->makeHtmlAttrId($this->preIdDisplay) . $this->makeAttrib() . $this->makeEvents() . '></div>';
	}
	public function makeControl_hidden($valueI) {
		$id = $this->makeHtmlAttrId($this->preIdInput) . $this->makeHtmlAttrName();
		return '<input' . $id . ' type="hidden" value="' . $valueI . '" />';
	}
	public function makeControl_display($valueD) {
		$id = $this->makeHtmlAttrId($this->preIdDisplay);
		$format = ' displayformat="' . $this->displayformat . '"';
		$attr = $this->makeAttrib() . $this->makeEvents();
		return '<input' . $id . $attr . ' type="text" value="' . $valueD . '" />';
	}
	public function makeControl_button() {
		return '<span' . $this->makeHtmlAttrId($this->preIdButton) . ' class="input-group-addon" buttonformat="on"><span class="glyphicon glyphicon-calendar"></span></span>';
	}
	public function makeControl_separator() {
		return '<span class="input-group-addon">-</span>';
	}
}
