<?php

namespace EstaleiroWeb\ED\Tools;

class Health {
	public $os = array( // https://en.wikipedia.org/wiki/Uname#Table_of_standard_uname_output
		'Linux' =>               'Linux',
		'CYGWIN_NT-5.1' =>       'Linux',
		'CYGWIN_NT-6.1' =>       'Linux',
		'CYGWIN_NT-6.1-WOW64' => 'Linux',
		'FreeBSD' =>             'Linux',
		'GNU' =>                 'Linux',
		'GNU/kFreeBSD' =>        'Linux',
		'HP-UX' =>               'Linux',
		'Minix' =>               'Linux',
		'NetBSD' =>              'Linux',
		'OpenBSD' =>             'Linux',
		'QNX' =>                 'Linux',
		'ReliantUNIX-Y' =>       'Linux',
		'SINIX-Y' =>             'Linux',
		'SunOS' =>               'Linux',
		'ULTRIX' =>              'Linux',
		'Unix' =>                'Linux',
		'UnixWare' =>            'Linux',
		'AIX' =>                 'Linux',
		'DragonFly' =>           'Linux',
		'Haiku' =>               'Linux',
		'IS/WB' =>               'Linux',
		'Interix' =>             'Linux',
		'IRIX' =>                'Linux',
		'IRIX64' =>              'Linux',
		'MINGW32_NT-6.1' =>      'Linux',
		'MSYS_NT-6.1' =>         'Linux',
		'NONSTOP_KERNEL' =>      'Linux',
		'OS/390' =>              'Linux',
		'OS400' =>               'Linux',
		'OSF1' =>                'Linux',
		'SCO_SV' =>              'Linux',
		'sn5176' =>              'Linux',
		'UWIN-W7' =>             'Linux',

		'WIN32' =>               'Win',
		'WINNT' =>               'Win',
		'Windows' =>             'Win',

		'Dar' =>                 'OSX',
		'Darwin' =>              'OSX',
	);

	private function wmic($value, $category = 'OS') {
		return str_ireplace($value . '=', '', trim(`wmic {$category} get {$value} /Value`));
	}
	private function lnx($key) {
		return trim(`egrep  "^{$key}:" /proc/meminfo | sed -r 's/.*: +([0-9]+) .*/\\1/'`);
	}

	public function load($item = null) {
		$l = sys_getloadavg();
		if ($item === null) return $l;
		if ($item == 5 || $item == 1) return $l[1];
		if ($item == 15 || $item == 2) return $l[2];
		return $l[0];
	}
	public function cpu($item = null) {
		$idle = $used = 0;
		switch (@$this->os[PHP_OS]) { //Linux,Win,OSX
			case 'Linux':
				$a = preg_split('/\s+/', trim(`head -1 /proc/stat | sed -r 's/cpu\w* +//'`));/*
				#user: normal processes executing in user mode
				#nice: niced processes executing in user mode
				#system: processes executing in kernel mode
				#idle: twiddling thumbs
				#iowait: waiting for I/O to complete
				#irq: servicing interrupts
				#softirq: servicing softirqs
					 0      1    2      3
					 user   nice system idle     iowait irq   softirq  steal guest guest_nice 
				cpu  861098 231  113904 49088573 196395 15825 16409    0     0     0
				*/
				$idle = $this->percent($a[3], array_sum($a));
				$used = 100 - $idle;
				break;
			case 'Win':
				$used = $this->wmic('loadpercentage', 'cpu'); //wmic cpu get loadpercentage /Value
				$idle = 100 - $used;
				break;
			case 'OSX':
				break;
			default: //UNKNOWN
		}
		if ($item == 'idle') return $idle;
		if ($item == 'used') return $used;
		return array('used' => $used, 'idle' => $idle);
	}
	public function mem($item = null) {
		$opts = array('mem_total' => 0, 'mem_free' => 0, 'swap_total' => 0, 'swap_free' => 0);
		switch (@$this->os[PHP_OS]) { //Linux,Win,OSX
			case 'Linux':
				switch ($item) {
					case 'mem_total':
						return $this->lnx('MemTotal');
					case 'mem_free':
						return $this->lnx('MemTotal');
					case 'mem_used':
						return $this->mem('mem_total') - $this->mem('mem_free');
					case 'swap_total':
						return $this->lnx('MemTotal');
					case 'swap_free':
						return $this->lnx('MemTotal');
					case 'swap_used':
						return $this->mem('swap_total') - $this->mem('swap_free');
					default:
						$r = preg_split('/\s+/', trim($this->lnx('(Mem|Swap)(Total|Free)')));
						$opts = array('mem_total' => $r[0], 'mem_free' => $r[1], 'swap_total' => $r[2], 'swap_free' => $r[3]);
				}
				break;
			case 'Win':
				switch ($item) {
					case 'mem_total':
						return $this->wmic('TotalVisibleMemorySize'); //wmic OS get TotalVisibleMemorySize /Value   //get total memory
					case 'mem_free':
						return $this->wmic('FreePhysicalMemory');     //wmic OS get FreePhysicalMemory /Value       //get free memory ex: FreePhysicalMemory=877820
					case 'mem_used':
						return $this->mem('mem_total') - $this->mem('mem_free');
					case 'swap_total':
						return $this->wmic('TotalVirtualMemorySize'); //wmic OS get FreeVirtualMemory /Value        //get free swap
					case 'swap_free':
						return $this->wmic('FreeVirtualMemory');      //wmic OS get TotalVirtualMemorySize /Value   //get total swap TotalVirtualMemorySize=8203164
					case 'swap_used':
						return $this->mem('swap_total') - $this->mem('swap_free');
					default:
						foreach ($opts as $key => &$value) $value = $this->mem($key);
						//tasklist /FI "PID gt 0 " /FO LIST           //#ocupado por processo 
						//typeperf -qx "\Memory"                      //#lista de objetos
						//typeperf "\Memory\Available Bytes"          //#counter memory
						//wmic memorychip get capacity                //get total memory per processor
				}
				break;
			case 'OSX':
				return;
			default:
				return; //UNKNOWN
		}
		$opts['mem_free%'] = $this->percent($opts['mem_free'], $opts['mem_total']);
		$opts['mem_used%'] = 100 - $opts['mem_free%'];
		if ($item == 'mem_free%') return $opts['mem_free%'];
		if ($item == 'mem_used%') return $opts['mem_used%'];

		$opts['swap_free%'] = $this->percent($opts['swap_free'], $opts['swap_total']);
		$opts['swap_used%'] = 100 - $opts['swap_free%'];
		if ($item == 'swap_free%') return $opts['swap_free%'];
		if ($item == 'swap_used%') return $opts['swap_used%'];

		$opts['mem_used'] = $opts['mem_total'] - $opts['mem_free'];
		$opts['swap_used'] = $opts['swap_total'] - $opts['swap_free'];

		ksort($opts);
		return $opts;
	}
	public function percent($partOf, $total) {
		return 100 * $partOf / $total;
	}
}
