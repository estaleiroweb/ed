<?php

namespace EstaleiroWeb\ED\Tools;

use EstaleiroWeb\ED\IO\_;

class Monitor {
	static public $REF_TABLE = 1;
	public $host = 'localhost';
	public $communit = "inTpu8";
	public $maxLoad = 3;
	public $maxCpu = 80;
	public $maxMen = 80;
	public $maxMenSwap = 80;
	public $numProcess = '';
	public $mssStopOverLoad = '';
	private $thisFullProcess;

	function memory() {
		$get = `free | grep Mem`;
		$mem = preg_split('/ +/', $get);
		array_shift($mem);
		/*
			0-total
			1-used
			2-free
			3-shared
			4-buffers
			5-cached
		*/
		$mPerc = ($mem[1] / $mem[0]) * 100;
		_::verbose("Mem Used: {$mem[1]}/{$mem[0]} ({$mPerc}%)\n");
		return $mPerc;
	}
	function memorySwap() {
		$get = `free | grep Swap`;
		$mem = preg_split('/ +/', $get);
		array_shift($mem);
		/*
			0-total
			1-used
			2-free
		*/
		$mPerc = ($mem[1] / $mem[0]) * 100;
		_::verbose("Swap Used: {$mem[1]}/{$mem[0]} ({$mPerc}%)\n");
		return $mPerc;
	}
	function load($host = false, $communit = false) {
		$host = $host === false ? $this->host : $host;
		$communit = $communit === false ? $this->communit : $communit;
		//return (float)snmpget($host,$communit,"UCD-SNMP-MIB::laLoadFloat.1");
		//$get=`w | grep 'load average'`;
		$ret = (float)`cut -d' ' -f 1 /proc/loadavg`;
		_::verbose('Load: ' . $ret . ' (' . trim(`cat /proc/loadavg`) . ")\n");
		return $ret;
	}
	function cpu() {
		$get = `head -1 /proc/stat`;
		$cpu = preg_split('/ +/', $get);
		array_shift($cpu);
		$total = array_sum($cpu);
		//return $cpu;
		/*
			0-user: normal processes executing in user mode
			1-nice: niced processes executing in user mode
			2-system: processes executing in kernel mode
			3-idle: twiddling thumbs
			4-iowait: waiting for I/O to complete
			5-irq: servicing interrupts
			6-softirq: servicing softirqs
		*/
		$ret = (1 - $cpu[3] / $total) * 100;
		_::verbose("CPU Total: $total, Ocupado: {$ret}%) ({$get})\n");
		return $ret;
	}
	function stopOverCpu($maxCpu = false) {
		$maxCpu = $maxCpu === false ? $this->maxCpu : $maxCpu;
		$cpu = $this->cpu();
		if ($cpu > $maxCpu) $this->prOverLoad(__FUNCTION__ . ": cpu=$cpu");
	}
	function stopOverMemory($maxMen = false) {
		$maxMen = $maxMen === false ? $this->maxMen : $maxMen;
		$mem = $this->memory();
		if ($mem > $maxMen) $this->prOverLoad(__FUNCTION__ . ": mem=$mem");
	}
	function stopOverMemorySwap($maxMen = false) {
		$maxMen = $maxMen === false ? $this->maxMenSwap : $maxMen;
		$swap = $this->memorySwap();
		if ($swap > $maxMen) $this->prOverLoad(__FUNCTION__ . ": swap=$swap");
	}
	function stopOverLoad($maxLoad = false, $host = false, $communit = false) {
		$maxLoad = $maxLoad === false ? $this->maxLoad : $maxLoad;
		$load = $this->load($host, $communit);
		if ($load > $maxLoad) $this->prOverLoad(__FUNCTION__ . ": load=$load");
	}
	function prOverLoad($fn = '') {
		print $this->mssStopOverLoad ? $this->mssStopOverLoad : "Over Load: $fn\n";
		exit;
	}
	function listProcess($cmd = '', $filter = '') {
		$get = `{$this->cmdProcess($cmd,$filter)}`;
		_::verbose("Process: $get\n");
		preg_match_all("/(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.*)/", eval("return '$get';"), $process, PREG_SET_ORDER);
		return $process;
	}
	function numProcess($cmd = '', $filter = '') {
		exec($this->cmdProcess($cmd,$filter),$get);
		$i=count($get);
		_::verbose("NumProcess: $i\n");
		return $this->numProcess = $i;
	}
	function thisProcess() {
		$pinfo = pathinfo($this->thisScript());
		return $pinfo['basename'];
	}
	function thisScript() {
		return $_SERVER['SCRIPT_NAME'];
	}
	function overProcess($numProcess, $cmd = '', $filter = '') {
		return $numProcess < $this->numProcess($cmd, $filter) ? true : false;
	}
	function stopOverProcess($numProcess, $cmd = '', $filter = '') {
		if ($this->overProcess($numProcess, $cmd, $filter)) $this->prOverLoad(__FUNCTION__);
	}
	function overThisProcess($numProcess, $filter = '') {
		return $this->overProcess($numProcess, $this->thisProcess(), $filter);
	}
	function stopOverThisProcess($numProcess, $filter = '') {
		if ($this->overThisProcess($numProcess, $filter)) $this->prOverLoad(__FUNCTION__ . '(' . $this->numProcess . ')');
	}
	function cmdProcess($cmd = '', $filter = '') {
		if ("$cmd$filter" == '') {
			$cmd = $this->thisProcess();
			$grep = '';
		} else $grep = $filter === '' ? '' : " | grep \"$filter\"";
		if ($cmd) return "ps --no-heading -fC $cmd$grep";
		$grep .= $grep ? " | grep -v \"grep $filter\"" : " | grep -v \"ps --no-heading -ef\"";
		return "ps --no-heading -ef$grep";
	}
	function stopOthersProcess() {
		$pid = getmypid();
		$process = $this->listProcess($this->thisProcess());
		foreach ($process as $l) if ($l[2] != $pid) {
			_::verbose("KillProcess: {$l[2]}\n");
			`kill -9 {$l[2]}`;
		}
	}
	function thisFullProcess() {
		$pid = getmypid();
		$cmd = "ps --no-heading -o cmd -p$pid";
		_::verbose(__FUNCTION__ . ": $cmd\n");
		return trim(`$cmd`);
	}
	function process($pr = null, $columns = 'cmd', $ref = null) {  //columns: https://ss64.com/bash/ps_keywords.html
		if (is_null($pr))  $pr = '-p' . getmypid();
		elseif ($pr != '-e') $pr = "-C '$pr'";
		if (!$columns) $columns = 'class,rtprio,ni,pri,psr,pcpu,stat,comm,cmd,stat,euid,ruid,tty,tpgid,sess,pgrp,ppid,tt,user,fname,tmout,f,wchan';
		$columns = preg_split('/\s*[,;]\s*/', $columns);

		$cmd = $head = array();
		foreach ($columns as $o) if ($o) {
			$cmd[] = "ps --no-heading -o tid,$o $pr";
			$head[] = $o;
		}
		if (!$cmd) return;
		$bordeaux = '===bordeaux===';
		$cmd = implode('&& echo "===bordeaux===";', $cmd);

		_::verbose(__FUNCTION__ . ": $cmd\n");
		$pr = `$cmd`;
		if (!$pr) return;
		$pr = explode($bordeaux, "\n$pr");
		$process = array();
		if (is_null($ref)) $ref = self::$REF_TABLE;
		if ($ref) {
			$e1 = 'pid';
			$e2 = 'col';
		} else {
			$e1 = 'col';
			$e2 = 'pid';
		}

		foreach ($pr as $k => $prList) {
			if (preg_match_all('/\n *(\d+) *(.*)/', $prList, $ret)) {
				foreach ($ret[1] as $j => $pid) {
					$col = 'pid';
					$process[$$e1][$$e2] = $pid;
					$col = $head[$k];
					$process[$$e1][$$e2] = trim($ret[2][$j]);
				}
			}
		}
		return $process;
	}
	function stopOverThisFullProcess($numProcess) {
		$pr = $this->process($this->thisProcess(), 'cmd', 0);
		if (!$pr) return;
		$pr = $pr['cmd'];
		$this->thisFullProcess = $this->thisFullProcess();

		$pr = array_filter($pr, array($this, 'processCompare'));
		$c = count($pr);
		if ($c > $numProcess) {
			print "Over Full Process: $c (limit $numProcess)\n";
			exit;
		}
	}
	function processCompare($pr) {
		return $this->thisFullProcess == $pr;
	}
}
