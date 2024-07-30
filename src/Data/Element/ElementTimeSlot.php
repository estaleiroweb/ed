<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementTimeSlot extends Element {
	protected $typeList = array('timeslot');
	public $tsFull = 4294967295;
	public $ts;

	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'timeslot';
		parent::__construct($name, $value, $id);
		$this->protect['strRequired'] = "";
		$this->ts = array_fill(0, 32, array('Error' => 0, 'Edit' => 1, 'Checked' => 0, 'Descr' => '', 'ErrorMsg' => ''));
	}
	function makeContent() {
		return $this->makeControl([], [], 'Content');
	}
	function makeControl($moreEvents = [], $moreAttr = [], $fnPre = 'Control') {
		$this->script();
		$fnTs = "fnTs$fnPre";
		$fnImput = "fnImput$fnPre";

		$attr = $this->makeAttrib() . $this->makeAttribInput();
		$style = $this->buildStyles();
		$events = $this->makeEvents(array('onclick' => "{$this->id}.rebuild(this)"));

		$descrOld = $this->ts[0]['Descr'];
		$out = "<span class='group' title='$descrOld'>\n";
		$used = 0;
		$inputValue = $fnPre == 'Control' ? $this->inputValue : false;
		$this->setValue($value = $inputValue === false ? $this->value : $inputValue);
		foreach ($this->ts as $ts => $v) {
			$owner = $v['Edit'] ? 'Edit' : '';
			$error = $v['Error'] ? 'Error' : '';
			$errorMsg = $v['ErrorMsg'] ? " title='{$v['ErrorMsg']}'" : '';
			$checked = $v['Checked'] ? 'on' : 'off';
			$used += $v['Checked'] * 64;
			$out .= $v['Descr'] == $descrOld ? "" : "</span>\n<span class='group' title='{$v['Descr']}'>\n";
			$descrOld = $v['Descr'];
			$out .= "\t<table cellspacing='0' cellpadding='0' class='$checked$owner $error'$errorMsg><tr><th>$ts</th></tr><tr><td>{$this->$fnTs($checked,$owner,$events)}</td></tr></table>\n";
		}
		$out .= "</span>\n";
		$tsbin = str_pad(base_convert($this->value, 10, 2), 32, '0', STR_PAD_LEFT);

		$bw = strlen(str_replace("0", "", $tsbin)) * 64;
		$bwTot = $this->ts[0]['Checked'] ? 2048 : 1984;
		$free = $bwTot - $used;
		$ts = str_word_count(str_replace(array(0, 1), array(' ', 'A'), strrev($tsbin)), 2);
		foreach ($ts as $k => &$v) {
			$t = strlen($v) - 1;
			$v = $t ? "$k-" . ($k + $t) : $k;
		}
		$ts = implode(",", $ts);
		$id = $this->id;
		$freePerc = sprintf("%0.2f%%", $free * 100 / $bwTot);
		$usedPerc = sprintf("%0.2f%%", $used * 100 / $bwTot);
		return "{$this->htmlLabel()}<table cellspacing='0' cellpadding='0'>
		<tr><td id='ts_{$id}'><nobr>$out</nobr></td></tr>
		<tr><td class='details'>{$this->$fnImput($value)}
			<label>Ch: </label><span id='details_ch_{$id}'>" . strpos(strrev($tsbin), '1') . "</span> 
			<label>Bw: </label><span id='details_bw_{$id}'>$bw</span> 
			<label>Free: </label><span id='details_free_{$id}'>$free</span>/<span id='percent_free_{$id}'>$freePerc</span>
			<label>Use: </label><span id='details_used_{$id}'>$used</span>/<span id='percent_used_{$id}'>$usedPerc</span>
			<label>Tot: </label><span id='details_tot_{$id}'>$bwTot</span> 
			<label>Ts: </label><span id='details_ts_{$id}'>$ts</span>
		</td></tr>
		</table>";
	}
	function fnTsContent($checked, $owner, $events) {
		return "<span class='$checked'></span>";
	}
	function fnTsControl($checked, $owner, $events) {
		$c = $checked == 'on' ? ' checked' : '';
		$d = $owner == 'Edit' ? '' : ' disabled';
		return "<input type='checkbox' class='$owner'$c$d$events />";
	}
	function fnImputContent() {
		return '';
	}
	function fnImputControl($value) {
		$id = $this->makeHtmlAttrId();
		$name = $this->makeHtmlAttrName();
		return "<input$id$name type='hidden' value='{$value}' />";
	}
	function setValue($value) {
		$source = $this->makeSource();
		//print "<pre>".print_r($source,true)."</pre>";

		foreach ($source as $tsbin => $descr) $this->setTimeSlot($tsbin, $descr);
		$this->setTimeSlot($value);
	}
	function setTimeSlot($tsbin, $descr = false) {
		$tsbin = strrev(str_pad(base_convert($tsbin, 10, 2), 32, '0', STR_PAD_LEFT));
		$edit = $descr === false;
		if ($tsbin[0]) {
			$error = $this->ts[0]['Error'] | $this->ts[0]['Checked'];
			if ($error) $this->ts[0]['ErrorMsg'] = "ERRO: ESTE TIME SLOT ESTÁ MARCADO MAIS DE UMA VEZ";
			$this->ts[0]['Error'] |= $error;
			$this->ts[0]['Edit'] = $edit;
			$this->ts[0]['Checked'] = 1;
			if ($descr) $this->ts[0]['Descr'] = $descr;
		}
		for ($ts = 1; $ts < 32; $ts++) {
			if ($tsbin[$ts]) {
				$error = $this->ts[$ts]['Error'] | $this->ts[$ts]['Checked'];
				if ($error) $this->ts[$ts]['ErrorMsg'] = "ERRO: ESTE TIME SLOT ESTÁ MARCADO MAIS DE UMA VEZ";
				$this->ts[$ts]['Error'] |= $error;
				$this->ts[$ts]['Checked'] = 1;
				$this->ts[$ts]['Edit'] = $edit;
				if ($descr) $this->ts[$ts]['Descr'] = $descr;
				if (!$edit) $this->ts[0]['Edit'] = 0;
			} elseif ($this->ts[0]['Checked']) {
				$this->ts[$ts]['Error'] = 1;
				$this->ts[$ts]['ErrorMsg'] = "ERRO: PRIMEIRO TIME SLOT ESTÁ MARCADO";
			}
		}
	}
}
