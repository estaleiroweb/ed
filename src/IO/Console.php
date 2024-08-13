<?php

namespace EstaleiroWeb\ED\IO;

use EstaleiroWeb\Traits\GetterAndSetter;

class Console {
	use GetterAndSetter;
	/**
	 * '\033[<EST>;<COR1>;<COR2m TEXTO';
	 * EST=Estilo
	 * COR1=cor da letraCOR2=cor do fundo
	 */
	protected $collor = [ // BGR = |RGB|
		'black' => 0,
		'red' => 1,
		'green' => 2,
		'yellow' => 3,
		'blue' => 4,
		'magenta' => 5,
		'cyan' => 6,
		'white' => 7,
		'gray' => 8,
	];
	protected $style = [
		'normal' => 0,
		'none' => 0,
		'bold' => 1,
		'low' => 2,
		'italic' => 3,
		'underline' => 4,
		'blink' => 5,
		'flash' => 6,
		'inverse' => 7,
		'hide' => 8,
	];

	protected $spcKeys = [
		'1'  => 'SOH', //CTRL+A
		'2'  => 'STX', //CTRL+B
		'3'  => 'ETX', //CTRL+C
		'4'  => 'EOT', //CTRL+D
		'5'  => 'ENQ', //CTRL+E
		'6'  => 'ACK', //CTRL+F
		'7'  => 'BEL', //CTRL+G
		'8'  => 'BS', //CTRL+H
		'9'  => 'TAB', //CTRL+I
		'10' => 'LF', //CTRL+J
		'11' => 'VT', //CTRL+K
		'12' => 'FF', //CTRL+L
		'13' => 'CR', //CTRL+M
		'14' => 'CR', //CTRL+N
		'15' => 'SI', //CTRL+O
		'16' => 'DLE', //CTRL+P
		'17' => 'DC1', //CTRL+Q
		'18' => 'DC2', //CTRL+R
		'19' => 'DC3', //CTRL+S
		'20' => 'DC4', //CTRL+T
		'21' => 'NAK', //CTRL+U
		'22' => 'SYN', //CTRL+V
		'23' => 'ETB', //CTRL+W
		'24' => 'CAN', //CTRL+X
		'25' => 'EM', //CTRL+Y
		'26' => 'SUB', //CTRL+Z
		'27' => 'ESC',
		'28' => 'FS',
		'29' => 'GS',
		'30' => 'RS',
		'31' => 'US',
		'127' => 'BKSCP',
		'27,10' => 'ALT+LF',
		'27,91,65' => 'ARROW+UP',
		'27,91,66' => 'ARROW+DN',
		'27,91,67' => 'ARROW+LEFT',
		'27,91,68' => 'ARROW+RIGHT',
		'27,91,49,126' => 'HOME',
		'27,91,50,126' => 'INS',
		'27,91,51,126' => 'DEL',
		'27,91,52,126' => 'END',
		'27,91,53,126' => 'PG+UP',
		'27,91,54,126' => 'PG+DN',
		'27,91,49,49,126' => 'F1',
		'27,91,49,50,126' => 'F2',
		'27,91,49,51,126' => 'F3',
		'27,91,49,52,126' => 'F4',
		'27,91,49,53,126' => 'F5',
		'27,91,49,55,126' => 'F6',
		'27,91,49,56,126' => 'F7',
		'27,91,49,57,126' => 'F8',
		'27,91,50'        => 'F9',
		'27,91,50,49,126' => 'F10',
		'27,91,50,51,126' => 'F11',
		'27,91,50,52,126' => 'F12',
	];

	/**
	 * Console functions
	 *
	 * @param array $options Associative array with options of style and options.
	 *  - Each stype started with _style_ is defined like _buildStyle_ method: 
	 *  > - styleNormalize 
	 *  > - styleLabel 
	 *  > - styleMax 
	 *  > - styleContent 
	 *  > - styleTitle 
	 *  > - styleOption 
	 *  > - styleDefaultOption 
	 *  > - styleUnderline 
	 *  - Other optinos: 
	 *  > - lfTitle 
	 *  > - cls 
	 */
	public function __construct(array $options = []) {
		$this->protect = [
			'styleNormalize' => (array) (@$options['styleNormalize'] ? $options['styleNormalize'] : [0]),
			'styleLabel' => (array) (@$options['styleLabel'] ? $options['styleLabel'] : [1, 'cyan']),
			'styleMax' => (array) (@$options['styleMax'] ? $options['styleMax'] : [1, 'green']),
			'styleContent' => (array) (@$options['styleContent'] ? $options['styleContent'] : [0, 'yellow']),
			'styleTitle' => (array) (@$options['styleTitle'] ? $options['styleTitle'] : ['underline', 'green']),
			'styleOption' => (array) (@$options['styleOption'] ? $options['styleOption'] : [['underline', 'bold'], 'white']),
			'styleDefaultOption' => (array) (@$options['styleOption'] ? $options['styleOption'] : [['underline', 'bold'], 'green']),
			'styleUnderline' => (array) (@$options['styleUnderline'] ? $options['styleUnderline'] : ['underline']),
			'lfTitle' =>  @$options['lfTitle'] ? $options['lfTitle'] : "\n\n",
			'cls' =>  @$options['cls'] ? $options['cls'] : true,
		];
		$this->readonly = [
			'actualStyle' =>  [0],
			'lastStyle' =>  [],
		];
	}
	/**
	 * Change / restore a style of Console
	 * @param array|string|null $option Initial option that can be modify after
	 *  - null:restore
	 *  - string: style option of ___construct_ method
	 *  - array: like _buildStyle_ method
	 * @return string Return a special string to print to change style Console
	 * @example self
	 * ```php
	 * $c=new Console;
	 * print $c->chStyle(['bold','underline'],'green','yellow') . 
	 *       'A text formated'. 
	 *       $c->chStyle() . PHP_EOL;
	 * print $c->chStyle([1,4],2,3) . 
	 *       'A same text formated'. 
	 *       $c->chStyle() . PHP_EOL;
	 * ```
	 */
	public function chStyle($option = null) {
		if (!$option) return $this->lastStyle ? $this->chStyle($this->lastStyle) : '';
		if (is_array($option)) {
			$this->readonly['lastStyle'] = $this->actualStyle;
			$this->readonly['actualStyle'] = $option;
			return call_user_func_array([$this, 'buildStyle'], $option);
		}
		if (is_string($option) && array_key_exists($k = 'style' . $option, $this->protect)) {
			return $this->chStyle($this->protect[$k]);
		}
		return '';
	}
	/**
	 * Build a special string to style of the Console
	 *
	 * @param array|string|int|null $style 
	 *  - null: unchange style
	 *  - int: Raw value. See attribute $this->style to see value options
	 *  - string: Higth level value. See attribute $this->style to see key options
	 *  - array: Blend options string|int
	 * @param string|int|null $collor change foreground color
	 *  - null: unchange collor
	 *  - int: Raw value. See attribute $this->collor to see value options
	 *  - string: Higth level value. See attribute $this->collor to see key options
	 * @param string|int|null $bgCollor change background color
	 *  - null: unchange collor
	 *  - int: Raw value. See attribute $this->collor to see value options
	 *  - string: Higth level value. See attribute $this->collor to see key options
	 * @return string Return a special string to print to change style Console
	 */
	public function buildStyle($style = null, $collor = null, $bgCollor = null) {
		$s = $c = $bc = '';
		if (!is_null($style)) {
			$arr = (array) $style;
			$s = [];
			foreach ($arr as $style) {
				if (array_key_exists($style, $this->style)) $style = $this->style[$style];
				$style = (int)$style;
				if ($style > 8 || $style < 0) $style = 0;
				$s[] = $style;
			}
			$s = implode(';', $s);
		}
		if (!is_null($collor)) {
			if (array_key_exists($collor, $this->collor)) $collor = $this->collor[$collor];
			$collor = (int)$collor;
			if ($collor > 8 || $collor < 0) $collor = 0;
			$c = ";3$collor";
		}
		if (!is_null($bgCollor)) {
			if (array_key_exists($bgCollor, $this->collor)) $bgCollor = $this->collor[$bgCollor];
			$bgCollor = (int)$bgCollor;
			if ($bgCollor > 8 || $bgCollor < 0) $bgCollor = 0;
			$bc = ";4$bgCollor";
		}

		return "\x1B[{$s}{$c}{$bc}m";
	}
	/**
	 * Clear screen of the Console
	 * @return self
	 */
	public function cls() {
		if ($this->cls) print "\x1B\x5BH\x1B\x5BJ";
		return $this;
	}
	/**
	 * Print a title formated
	 * @return self
	 */
	public function title($title) {
		print $this->chStyle('Title') . $title . $this->chStyle() . $this->lfTitle;
		return $this;
	}
	/**
	 * Create a menu options on Console
	 * 
	 * @param array $aOptions Options of menu in a vector
	 *  - Each item of vector is a array option. See @example
	 * @param string|null $title Title of menu
	 * @param string|null $text A text to print after menu
	 * @return bool Whe true=a option was choose, false=aborted
	 * @example Admin.php 
	 * ```php
	 * $c=new Console;
	 * while ($c->menu(
	 *      [
	 *         ['opt' => 'List DSN Connection', 'fn' => [$this, 'connList']],
	 *         ['opt' => 'Add DSN Connection', 'fn' => [$this, 'conn_Add']],
	 *         ['opt' => 'Remove DSN Connection', 'fn' => [$this, 'menu_ConnRm']],
	 *      ], 
	 *      'Easy Data DSN Menu',
	 *      'Help of Menu'
	 * ));
	 * ```
	 */
	public function menu(array $aOptions, $title = null, $text = null) {
		static $o = '123456789ABCDEFGHIJKLMNOPQRSTUVXWYZ';

		$tam = strlen($title);
		$this->cls();
		if ($tam) $this->title($title);

		$opt = [];
		foreach ($aOptions as $k => $line) {
			$opt[$o[$k]] = $k;
			$item = $this->chStyle('Option') . $o[$k] . $this->chStyle() . ' - ' . $line['opt'];
			$tam = max(20, $tam, strlen($item));
			print $item . PHP_EOL;
		}
		$traco = str_repeat('-', $tam);
		if ($text) print $traco . PHP_EOL . $text . PHP_EOL . $traco . PHP_EOL;
		print PHP_EOL;
		print 'Choice your option [' . $this->chStyle('Option') . '0' . $this->chStyle();
		print '/' . $this->chStyle('Option') . 'ESC' . $this->chStyle() . ' - To Exit]: ';

		$opt['0'] = $opt['ESC'] = null;
		$chr = $this->read(['/^(' . implode('|', array_keys($opt)) . ')$/i', '/^ESC$/']);
		print PHP_EOL;
		print $traco . PHP_EOL . PHP_EOL;

		if (!$chr) exit;
		$chr = strtoupper($chr[0]['value']);
		$k = @$opt[$chr];
		if (is_null($k)) return false;

		$callBack = @$aOptions[$k]['fn'];
		//print_r([$callBack,$k]);sleep(1);
		call_user_func($callBack, $k);
		return true;
	}
	public function pressKey($prompt = 'Press a key to continue...') {
		print $prompt;
		return $this->read();
	}
	public function confirm($text = '', array $options = ['Y' => 'Yes', 'N' => "No"]) {
		print $text;
		if (!$options) return $this->read();

		$aCtrl = ['LF', 'ESC'];
		$erChar = '/^[';
		$op = $aTxt = [];
		for ($i = 0; $i < 2; $i++) {
			$control = $aCtrl[$i];
			if ($options) {
				$k = strtoupper(mb_substr(key($options), 0, 1));
				$txt = array_shift($options);
			} else $k = '';
			if ($k == '' || array_key_exists($k, $aTxt)) {
				if ($k == '') {
					if (array_key_exists('Y', $aTxt)) {
						$k = 'N';
						$txt = 'No';
					} else {
						$k = 'Y';
						$txt = 'Yes';
					}
				} elseif (in_array($k, ['Y', 'S'])) {
					$k = 'N';
					$txt = 'No';
				} else {
					$k = 'Y';
					$txt = 'Yes';
				}
			}
			$erChar .= $k;
			$style = $i ? 'Option' : 'DefaultOption';
			if (preg_match('/^(.*?)(' . preg_quote($k, '/') . ')(.*?)$/i', $txt, $ret)) {
				$aTxt[$k] = $ret[1] . $this->chStyle($style) . $k . $this->chStyle() . $ret[3];
			} else {
				$aTxt[$k] = $this->chStyle($style) . $k . $this->chStyle() . ':' . $txt;
			}
			$op[$control] = $k;
		}

		$erChar .= ']$/i';
		$erCtrl = '/^(' . implode('|', $aCtrl) . ')$/i';

		print ' [' . implode('/', $aTxt) . ']:';
		$ret = $this->read([$erChar, $erCtrl]);
		print PHP_EOL;

		if (!$ret) return $op['ESC'];
		elseif ($ret[0]['type'] == 'control') return $op[$ret[0]['value']];
		return strtoupper($ret[0]['value']);
	}
	/**
	 * Read Line by STDIN
	 * @param string|null $prompt Prompt of content
	 * @param int|null $length Max length of content. Null is infinite
	 * @param int|null $timeout Timeout in seconds Null is infinite
	 * @param string|array|null $er Regular expression to check: string|array[0]=Char, array[1]=Controls, array[2]=Content,Null=accept every thing
	 * @param string|null $timeout Timeout in seconds Null is infinite
	 * @param array|null $default Default value.
	 * @param string|bool|null $show Show or not the digits. FALSE=Hide, NULL|TRUE=Show, String=Substitute char
	 * 
	 * @return string|bool|null Value. Null=Timeout, FALSE=Abort, String=Value
	 */
	public function readLine($prompt = null, $length = null, $timeout = null, $er = null, $default = null, $show = null) {
		if (is_null($show)) $show = true;
		$value = "$default";
		$er = $er ? array_values((array)$er) : [];
		print $prompt;
		$tam = mb_strlen($value);
		if (!is_null($length) && $tam >= $length) $value = substr($value, 0, $length);
		if ($show) {
			print $show === true  ? $value : str_repeat($show[0], $tam);
		}

		//while(1) print_r($this->read($erChar, $timeout));

		$pos = mb_strlen($value);
		while (true) {
			$oldValue = $value;
			$oldTam = mb_strlen($value);
			$oldPos = $pos;
			$arr = (array) $this->read($er, $timeout);
			if (!$arr) return null; //timeout
			foreach ($arr as $item) {
				$char = $item['value'];

				if ($item['type'] == 'control') {
					switch ($char) {
						case 'BKSCP':
							if ($pos) {
								$pos--;
								$value = mb_substr($value, 0, $pos) . mb_substr($value, $pos + 1);
							}
							break;
						case 'DEL':
							if ($pos < $oldTam) {
								$value = mb_substr($value, 0, $pos) . mb_substr($value, $pos + 1);
							}
							break;
						case 'ARROW+LEFT':
							if ($pos < $oldTam) $pos++;
							break;
						case 'ARROW+RIGHT':
							if ($pos) $pos--;
							break;
						case 'ARROW+UP':
						case 'PG+UP':
						case 'HOME':
							$pos = 0;
						case 'ARROW+DN':
						case 'PG+DN':
						case 'END':
							$pos = $oldTam;
							break;
						case 'ESC':
							return false;
						case 'ALT+LF':
						case 'LF':
							if (!@$er[2] || preg_match($er[2], $value)) break 3;
							print "\x07";
							break;
					}
				} else {
					if ($pos == $oldTam) $value .= "$char";
					else $value = mb_substr($value, 0, $pos) . $char . mb_substr($value, $pos);
					$pos += 1;
				}
				$tam = mb_strlen($value);
				$pos = max(min($pos, $tam), 0);
				$item['pos'] = $pos;
				$item['tam'] = $tam;
				if ($show) {
					if ($item['type'] == 'normal' && $pos == $tam) {
						print $show === true  ? $char : $show[0];
					} else {
						$scr = $show === true ? $value : str_repeat($show[0], $tam);
						print str_repeat(' ', $oldTam - $oldPos) . str_repeat("\x7F", $oldTam) . $scr . str_repeat("\x08", $tam - $pos);
					}
				}
				if (!is_null($length) && $tam >= $length) {
					$value = substr($value, 0, $length);
					if (!@$er[2] || preg_match($er[2], $value)) break;
					print "\x07";
				}
			}
		}
		return $value;
	}
	/**
	 * Multi read Line
	 * @param array $arr Associative array with all parameters of the readLine method add callbackFn
	 * 		$arr = [
	 *		'label' => [
	 *			'label' => 'DSN String',
	 *			'len' => 30,
	 *			'timeout' => 120,
	 *			'er' => ['/\w+/', null, null], //[ erChar, erControl, erContent ]
	 *			'default' => 'mysql:host=127.0.0.1;dbname=test',
	 *			'callbackFn' => null,
	 *			'show' => true,
	 *		],
	 *	];
	 * @return array Associative array key-value
	 */
	public function multRead(array $arr) {
		foreach ($arr as $k => $cfg) {
			do {
				$label = $this->chStyle('Label') . (@$cfg['label'] ? $cfg['label'] : $k) . $this->chStyle();
				if (@$cfg['len']) $label .= $this->chStyle('Max') . '(max ' . $cfg['len'] . ')' . $this->chStyle();
				$label .= ': ' . $this->chStyle('Content');
				$value = $this->readLine(
					$label,
					@$cfg['len'],
					@$cfg['timeout'],
					@$cfg['er'],
					@$cfg['default'],
					@$cfg['show']
				);
				print $this->chStyle() . PHP_EOL;

				if (is_null($value)) {
					print 'Time out' . PHP_EOL;
					return;
				}
				if ($value === false) {
					print 'Abort' . PHP_EOL;
					return;
				}
				if ($cfg['callbackFn']) {
					$chk = call_user_func($cfg['callbackFn'], $value);
					if ($chk === false) {
						print 'Wrong format' . PHP_EOL;
						continue;
					}
					$value = $chk;
				}
			} while (false);
			$arr[$k] = $value;
		}
		return $arr;
	}
	public function read($er = null, $timeout = null, $outOrd = false) {
		$er = $er ? array_values((array)$er) : [];
		$out = [];
		$time = microtime(true);
		readline_callback_handler_install('', function () {
		});
		$ord = '';
		$stdin = fopen("php://stdin", 'r');
		stream_set_blocking(STDIN, false);
		while (!$out) {
			$char = stream_get_contents($stdin, 1);
			$deltaT = microtime(true) - $time;
			if (!is_null($timeout) && $deltaT > $timeout) break;
			if (strlen($char) == 0) continue;
			$ord = ord($char);
			$c = stream_get_contents($stdin);
			$time = microtime(true);
			if ($ord == 27) {
				$l = strlen($c);
				for ($i = 0; $i < $l; $i++) $ord .= ',' . ord($c[$i]);
				$out[] = [
					'ord' => $ord,
					'value' => array_key_exists($ord, $this->spcKeys) ? $this->spcKeys[$ord] : $char . $c,
					'raw' => $char . $c,
					'type' => 'control',
				];
			} else {
				$char .= $c;
				$l = strlen($char);

				for ($i = 0; $i < $l; $i++) {
					$raw = $str = $char[$i];
					$ord = ord($str);
					$type = 'normal';
					$erCheck = @$er[0] ? $er[0] : '';

					if ($ord == 195) {
						$str .= $char[++$i];
						$raw .= $char[++$i];
					} else if (array_key_exists($ord, $this->spcKeys)) {
						$str = $this->spcKeys[$ord];
						$type = 'control';
						$erCheck = @$er[1] ? $er[1] : '';
					}
					if ($erCheck && !preg_match($erCheck, $str)) continue;
					$out[] = [
						'ord' => $ord,
						'value' => $str,
						'raw' => $raw,
						'type' => $type,
					];
				}
			}
		}
		fclose($stdin);
		readline_callback_handler_remove();
		stream_set_blocking(STDIN, true);

		return $out;
	}
	public function readlineSimple1($prompt = null) {
		print $prompt;
		return fgets(STDIN);
	}
	public function readlineSimple2($prompt = null) {
		return readline($prompt);
	}
}
