<?php

namespace EstaleiroWeb\ED\IO;

use EstaleiroWeb\ED\Db\Field\Field;
use EstaleiroWeb\ED\Db\Res\Res;
use EstaleiroWeb\ED\Ext\Ed;
use Composer\Autoload\ClassLoader;
use EstaleiroWeb\ED\Db\Raw;
use PDO;
use PDOStatement;

define('MESSAGE', 0);
define('NORMAL_MESSAGE', 1);
define('WARNING', 2);
define('CRITICAL_ERROR', 3);
define('FATAL_ERROR', 4);

class _ {
	static public $encode;
	static public $show_debug_all_erros = true;
	static public $verbose = false; // filename where put verbose data
	static public $verbose_trace = true; //$verbose_trace=true;
	static public $trap_error = 0;
	static public $trap_error_callback = NULL; // call_user_func_array => 'function' | array('obj|class','method')
	static public $trap_error_callback_parameters = [];
	static public $logFile;

	static public $fit_maxFieldLength = 30;
	static public $fit_maxLength = 180;
	static public $fit_separator = ' ';
	static public $fit_separatorStart = '';
	static public $fit_separatorEnd = '';
	static public $fit_headLineTop = '-';
	static public $fit_headLineBottom = '-';
	static public $fit_footLine = '=';
	static public $errorLevels = [
		0 => 'Message',
		1 => 'Normal',
		2 => 'Warning',
		3 => 'Critical',
		4 => 'Fatal'
	];

	static public function init() {
		if (@$_SERVER['SHELL']) session_set_save_handler(new FileSessionHandler(true), true);
		self::enable_error_handler();
		self::checkRequestMethod();
	}

	static public function pr($str) {
		print self::htmlConvert($str);
	}
	static public function prt($str) {
		print trim(`date '+[%F %T.%N]:'`) . ' ' . $str;
	}
	static public function verbose($text = true, $class = null) {
		if (is_bool($text)) return self::$verbose = $text;
		if (!isset(self::$verbose) || !self::$verbose) return;
		self::show($text, $class);
	}
	static public function error($message = '', $level = 0, $title = false) {
		$message = print_r($message, true);
		$trace = self::$verbose_trace ? self::compile_debug_backtrace(self::get_debug_backtrace(), true) : '';
		$level = min(floor(abs($level)), FATAL_ERROR);
		$title = self::$errorLevels[$level] . ($title ? ': ' : '') . $title;
		if (@$_SERVER['SHELL']) {
			print '###' . strip_tags($title) . "\n";
			if ($trace) print "$trace\n";
			if ($message) print "$message\n";
		} else {
			$class = strtolower(self::$errorLevels[$level]) . '_error';
			$message = self::htmlScpChar($message);
			print self::makeBox($trace . $message, $title, 'makeBox errorBox ' . $class);
		}
		if ($level == FATAL_ERROR) exit;
		return !$level;
	}
	static public function makeBox($message = '', $title = '', $class = null, $tag = 'div') {
		if (is_null($class)) $class = 'alert alert-info MakeBox';
		$title = $title ? "<h6>{$title}</h6>" : '';
		if ($message) $message = "<pre>{$message}</pre>";
		return self::htmlConvert("<{$tag} class='{$class}'>{$title}{$message}</{$tag}>\n");
	}
	static public function show($text = true, $classStyle = null) {
		if (is_object($text) && method_exists($text, '__debugInfo')) $text = $text->__debugInfo();
		$text = print_r($text, true);
		$bt = self::get_debug_backtrace();
		//Find the file and function caller
		$caller = self::trace_debug_backtrace($bt);
		$trace = self::$verbose_trace ? self::compile_debug_backtrace($bt) : '';
		$out = [];
		if (@$_SERVER['SHELL'] || is_string(self::$verbose)) {
			$caller .= $trace;
			if ($caller) $out[] = $caller;
			if ($text) $out[] = $text;
			if (self::$verbose != '' && is_string(self::$verbose)) return file_put_contents(self::$verbose, $out, FILE_APPEND);
		} else { //Browser print type
			$out[] = self::makeBox($trace . self::htmlScpChar($text), $caller, $classStyle);
		}
		$out = implode("\n", $out);

		if ($classStyle === false) return $out;
		print $out;
	}
	static public function showTable($arr, $showKey = '#', $maxLength = null, $countRecords = true, $classStyle = 'alert alert-primary MakeBox') {
		print self::getTable($arr, $showKey, $maxLength, $countRecords, $classStyle);
	}
	static public function getTable($arr, $showKey = '#', $maxLength = null, $countRecords = true, $classStyle = 'alert alert-primary MakeBox') {
		if (!$arr) return;
		$out = '';
		if (is_array($arr) && is_array(reset($arr))) {
			$keys = array_keys(reset($arr));
			$fn = function (&$arr) {
				return $arr ? array_shift($arr) : false;
			};
		} elseif (is_object($arr) && ($arr instanceof Res || $arr instanceof PDOStatement)) {
			$nCols = $arr->columnCount();
			if (!$arr) return;
			$keys = [];
			for ($i = 0; $i < $nCols; $i++) $keys[] = $arr->getColumnMeta($i)['name'];
			//foreach ($arr->fetch_fields() as $v) $keys[$v->name] = $v->name;
			//print_r($keys);
			$fn = function (&$arr) {
				return $arr->fetch(PDO::FETCH_ASSOC);
			};
		} else return;
		if ($showKey) array_unshift($keys, $showKey);
		//print_r(array($fn,$pr));return;

		if (@$_SERVER['SHELL']) {
			if (is_null($maxLength)) $maxLength = 30;
			$fnHeadTop = function ($arr, $flag, $countRecords, $lines) {
				return self::fit_showHeadLineTop($arr, $flag);
			};
			$fnHead = function ($arr, $flag) {
				return self::fit_Line($arr, $flag);
			};
			$fnHeadBottom = function ($arr, $flag) {
				return self::fit_showHeadLineBottom($arr, $flag);
			};
			$fnField = function ($value, $length, $align) {
				return self::fit_Field($value, $length, $align);
			};
			$fnLine = function ($line, $flag) {
				return self::fit_Line($line, $flag);
			};
			$fnFootLine = function ($arr, $flag, $countRecords, $lines) {
				$result = self::fit_showFootLine($arr, $flag);
				if ($countRecords) $result .= count($lines) . " Records\n";
				$result .= "\n";
				return $result;
			};
			$fnCmd = function ($result, $classStyle) use (&$out) {
				$out .= $result;
			};
		} else {
			if (is_null($maxLength)) $maxLength = 200;
			new Ed();
			$fnHeadTop = function ($arr, $flag, $countRecords, $lines) {
				//table-bordered table-sm 
				$result = '<div><div>[<a href="javascript:" onclick="$.copy(this.parentElement.nextSibling,\'html\')">Copy</a>]';
				if ($countRecords) {
					$recCount = count($lines);
					$result .= "<b>$recCount Records</b>";
				}
				$result .= '</div>';
				$result .= "<table class='table table-striped table-sm table-hover'>\n";
				return $result;
			};
			$fnHead = function ($arr, $flag) {
				$result = '<tr>';
				foreach ($arr as $v) $result .= "<th>$v</th>";
				$result .= "</tr>\n";
				return $result;
			};
			$fnHeadBottom = function ($arr, $flag) {
				return '';
			};
			$fnField = function ($value, $length, $align) {
				if ($align == STR_PAD_LEFT) $class = 'text-end';
				elseif ($align == STR_PAD_RIGHT) $class = 'text-start';
				elseif ($align == STR_PAD_BOTH) $class = 'text-center';
				else $class = '';
				return '<td class="' . $class . '">' . htmlspecialchars($value) . '</td>';
			};
			$fnLine = function ($line, $flag) {
				return '<tr>' . implode('', $line) . "</tr>\n";
			};
			$fnFootLine = function ($arr, $flag, $countRecords, $lines) {
				return '</table></div>';
			};
			$fnCmd = function ($result, $classStyle) use (&$out) {
				$out .= $result;
				//$out.=self::makeBox($result, '', $classStyle, 'pre');
			};
		}

		$head = [];
		foreach ($keys as $k) {
			$head['length'][$k] = min(max(1, strlen($k)), $maxLength);
			$head['align'][$k] = STR_PAD_LEFT;
			$head['alignHead'][$k] = $showKey == $k ? STR_PAD_LEFT : STR_PAD_RIGHT;
		}

		$idx = 0;
		$lines = [];
		while ($line = $fn($arr)) {
			//foreach($arr as $line) {
			if ($showKey) $line[$showKey] = $idx++;
			foreach ($head['length'] as $k => $len) {
				$v = @$line[$k];
				if (is_object($v)) $v = ($v instanceof Field || $v instanceof Raw) ? $v() : json_encode($v);
				elseif (is_array($v)) $v = json_encode($v);
				$line[$k] = $v;
				$head['length'][$k] = min(max($len, strlen($v)), $maxLength);
				if ($v && !is_numeric($v)) $head['align'][$k] = STR_PAD_RIGHT;
			}
			$lines[] = $line;
		}
		foreach ($keys as $k) {
			$head['orgname'][$k] = self::fit_Field($k, $head['length'][$k], @$head['alignHead'][$k]);
			$head['line'][$k] = str_repeat('-', $head['length'][$k]);
		}

		$result = $fnHeadTop($head['orgname'], true, $countRecords, $lines);
		$result .= $fnHead($head['orgname'], true);
		$result .= $fnHeadBottom($head['orgname'], true);

		foreach ($lines as $idx => $line) if (is_array($line)) {
			if ($showKey) $line = array_merge([$showKey => $idx], $line);
			foreach ($line as $k => $v) $line[$k] = $fnField($v, $head['length'][$k], $head['align'][$k]);
			$result .= $fnLine($line, true);
		} else {
			$result .= print_r($line, true);
			$result .= "\n";
		}

		$result .= $fnFootLine($head['orgname'], true, $countRecords, $lines);
		$fnCmd($result, $classStyle);

		return $out;
	}

	public function escape_string($str) {
		$e = mb_detect_encoding($str);
		if ($e != 'ASCII' && self::$encode != $e) {
			$str = mb_convert_encoding($str, self::$encode, $e);
		}
		return self::escapeString($str);
	}
	static public function escapeString($str) {
		return addcslashes($str, "\"'\\\0..\37\177");
	}
	static public function goURL($url = '/') {
		header('HTTP/1.0 301 Moved');
		header('Location: ' . $url);
		exit;
	}


	static public function checkRequestMethod() {
		$data = file_get_contents('php://input');
		if ($data == '') return;
		$m = @$_SERVER['REQUEST_METHOD'];
		if (!$m) return;
		if ($data[0] == '{') $GLOBALS['_' . $m] == json_decode($data);
		elseif (!in_array($m, ['GET', 'POST'])) parse_str($data, $GLOBALS['_' . $m]);
	}

	static public function htmlConvert($str) {
		return mb_convert_encoding($str, self::$encode);
	}
	static public function htmlScpChar($text, $quotes = ENT_NOQUOTES) {
		return htmlentities($text, $quotes, self::$encode);
	}

	static public function get_debug_backtrace() {
		$bt = debug_backtrace();
		$lOld = null;
		while ($bt) {
			$l = $bt[0];
			if (!preg_match('/^(show|verbose|error|(get|trace|compile)_debug_backtrace)$/', $l['function'])) break;
			$lOld = $l;
			array_shift($bt);
		}
		if ($lOld) {
			if ($bt) {
				array_unshift($bt, $bt[0]);
				$bt[0]['file'] = 'CALLER:' . $lOld['file'];
				$bt[0]['line'] = $lOld['line'];
			} else $bt = [$lOld];
		}
		return $bt;
	}
	static public function trace_debug_backtrace(&$bt) {
		$l = array_shift($bt);
		$caller = @$l['file'] ? $l['file'] . '[' . $l['line'] . ']:' : ''; //file[line]:
		$caller .= @$l['class'] ? $l['class'] . $l['type'] : ''; //class-> or class:: (if exists)
		if (@$l['function']) {
			$caller .= $l['function'] . '('; //function(
			$args = @$l['args'] ? array_values(@$l['args']) : [];
			if (@$_SERVER['SHELL'] || is_string(self::$verbose)) {
				//$caller.=implode(',',array_map('gettype',$args)).");\n"; //arg1,arg2,...);
				$amap = array_map(function ($v) {
					$v = json_encode($v);
					return strlen($v) > 10 ? substr($v, 0, 20) . '...' : $v;
				}, $args);
				//$amap=array_map('json_encode', $args);
				$caller .= implode(',', $amap) . ");\n"; //arg1,arg2,...);
			} else { //Browser print type
				$caller = self::htmlScpChar($caller);
				$out = [];
				foreach ($args as $k => $v) $out[] = '<span title="' . self::htmlScpChar(print_r($v, true), ENT_QUOTES) . '">' . gettype($v) . '</span>';
				$caller .= implode(',', $out); //arg1,arg2,...
				$caller .= ');';
			}
		}
		return $caller;
	}
	static public function compile_debug_backtrace($bt, $number = false) {
		if (!$bt) return '';
		$k = 1;
		$trace = [];
		while ($bt) $trace[$k++] = self::trace_debug_backtrace($bt);
		if (@$_SERVER['SHELL'] || is_string(self::$verbose)) {
			if ($number) foreach ($trace as $k => &$v) $v = $k . ': ' . $v;
			return implode('', $trace);
		} else { //Browser print type
			if ($number) foreach ($trace as $k => &$v) $v = '<b style="width:20px;">' . $k . '</b>: ' . $v;
			return '<pre><div>' . implode('</div><div>', $trace) . '</div></pre>';
		}
	}

	static public function error_handler($errno, $errstr, $errfile, $errline, $errcontext = '', $bt = [], $trigger = true) {
		static $trigger_error, $ntrigger_error;
		static $constanst = [
			E_ERROR => [E_ERROR, 'ERROR', 0, 'pow(2,0)'],
			E_WARNING => [E_WARNING, 'WARNING', 1, 'pow(2,1)'],
			E_PARSE => [E_PARSE, 'PARSE', 2, 'pow(2,2)'],
			E_NOTICE => [E_NOTICE, 'NOTICE', 3, 'pow(2,3)'],
			E_CORE_ERROR => [E_CORE_ERROR, 'CORE_ERROR', 4, 'pow(2,4)'],
			E_CORE_WARNING => [E_CORE_WARNING, 'CORE_WARNING', 5, 'pow(2,5)'],
			E_COMPILE_ERROR => [E_COMPILE_ERROR, 'COMPILE_ERROR', 6, 'pow(2,6)'],
			E_COMPILE_WARNING => [E_COMPILE_WARNING, 'COMPILE_WARNING', 7, 'pow(2,7)'],
			E_USER_ERROR => [E_USER_ERROR, 'USER_ERROR', 8, 'pow(2,8)'],
			E_USER_WARNING => [E_USER_WARNING, 'USER_WARNING', 9, 'pow(2,9)'],
			E_USER_NOTICE => [E_USER_NOTICE, 'USER_NOTICE', 10, 'pow(2,10)'],
			E_STRICT => [E_STRICT, 'STRICT', 11, 'pow(2,11)'],
			E_RECOVERABLE_ERROR => [E_RECOVERABLE_ERROR, 'RECOVERABLE_ERROR', 12, 'pow(2,12)'],
			E_DEPRECATED => [E_DEPRECATED, 'DEPRECATED', 13, 'pow(2,13)'],
			E_USER_DEPRECATED => [E_USER_DEPRECATED, 'USER_DEPRECATED', 14, 'pow(2,14)'],
			E_ALL => [E_ALL, 'ALL', 15, 'pow(2,15)-1'],
		];
		global $trap_error, $trap_error_callback, $trap_error_callback_PARAMETERS;

		if (!$trigger_error) {
			$trigger_error = E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE | E_USER_DEPRECATED;
			$ntrigger_error = E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_STRICT | E_RECOVERABLE_ERROR | E_DEPRECATED;
		}

		//E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_STRICT
		if ($trap_error & $errno && $trap_error_callback) call_user_func_array($trap_error_callback, $trap_error_callback_PARAMETERS);

		if (!@$GLOBALS['show_debug_all_erros'] || $errno == 8192) return;
		$message = [];
		self::error($errstr, 0, "<label>ERROR [$errno] </label><label>File: </label><i>$errfile [$errline]</i>");
		//show([$errstr, $errno,$constanst]);

		if ($trigger) {
			$er = $errno & $trigger_error;
			if (!$er) $er = E_USER_NOTICE;
			@trigger_error($errstr, $er);
			if ($errno & $ntrigger_error) {
				$er = [];
				foreach ($constanst as $k => $cfg) if ($k & $errno) $er[] = $cfg[1];
				print "\n";
			}
		}
	}
	static public function enable_error_handler($e = null) {
		if (is_null($e)) $e = E_ALL & ~E_NOTICE & ~E_STRICT;
		//function error_handler... if($trap_error & $errno && $trap_error_callback) call_user_func_array($__TRAP_CALLBACK,$__TRAP_CALLBACK_PARAMETERS);
		set_error_handler([__CLASS__, 'error_handler'], $e); // & ~E_WARNING
	}
	static public function disable_error_handler() {
		set_error_handler([__CLASS__, 'error_handler'], 0);
	}
	static public function logFile($file, $chunk_size = 0) {
		if (!self::$logFile) {
			self::$logFile = [];
			while (@ob_end_clean());
			@ob_start([__CLASS__, 'logFile_buffer'], $chunk_size);
		}
		self::$logFile[] = $file;
	}
	static public function logFile_buffer($buffer) {
		foreach (self::$logFile as $file) file_put_contents($file, $buffer, FILE_APPEND);
		@flush();
	}

	static public function fit_Lenght($value, $len = 1) {
		if (!self::$fit_maxFieldLength) return $value;
		return min(max($len, strlen($value)), self::$fit_maxFieldLength);
	}
	static public function fit_Field($value, $len = null, $pad = STR_PAD_RIGHT) { //STR_PAD_LEFT STR_PAD_RIGHT STR_PAD_BOTH 
		if (is_array($value)) $value = json_encode($value);
		if ($len === null) $len = self::$fit_maxFieldLength;
		if (!$len) return $value;
		return str_pad(substr($value, 0, $len), $len, ' ', $pad);
	}
	static public function fit_Line($line, $return = false) {
		$line = implode(self::$fit_separator, $line);
		if (self::$fit_maxLength) $line = substr($line, 0, self::$fit_maxLength);
		$out = self::$fit_separatorStart . $line . self::$fit_separatorEnd . "\n";
		if ($return) return $out;
		print $out;
	}
	static public function fit_showHeadLineTop($line, $return = false) {
		if (!self::$fit_headLineTop) return;
		$line = preg_replace('/./', self::$fit_headLineTop, $line);
		$out = self::fit_Line($line, $return);
		if ($return) return $out;
		print $out;
	}
	static public function fit_showHeadLineBottom($line, $return = false) {
		if (!self::$fit_headLineBottom) return;
		$line = preg_replace('/./', self::$fit_headLineBottom, $line);
		$out = self::fit_Line($line, $return);
		if ($return) return $out;
		print $out;
	}
	static public function fit_showFootLine($line, $return = false) {
		if (!self::$fit_footLine) return;
		$line = preg_replace('/./', self::$fit_footLine, $line);
		$out = self::fit_Line($line, $return);
		if ($return) return $out;
		print $out;
	}

	static public function checkHost($url, $port = 80) {
		$urlSplit = parse_url($url);
		$host = @$urlSplit['host'];
		if (!$host || $host == 'localhost' || $host == '127.0.0.1') return true;
		($p = @$urlSplit['port']) || ($p = $port);
		return (bool)@fsockopen($urlSplit['host'], $p, $errno, $errstr, 5);
	}
	static public function nmap($host, $port) {
		self::enable_error_handler(E_ALL & ~E_NOTICE & ~E_WARNING);
		$conexao = @fsockopen($host, $port, $erro, $erro, 15);
		if (($ret = (bool)$conexao)) @fclose($conexao);
		//print "Testing $host:$port ".($ret?'[OK]':'[ERROR]')."\n";
		restore_error_handler();

		return $ret;
	}
	static public function toBool($value) {
		if (is_string($value)) {
			$value = strtolower($value);
			if ($value === 'false' || $value === 'falso' || $value === 'off' || $value === 'desligado' || $value === '0') return false;
		}
		return $value ? true : false;
	}
	static public function fixLink($link) {
		do {
			$old = $link;
			$link = preg_replace('/([\/\\\])[^\/\\\]+?\1\.\.\1/', '\1', $link);
		} while ($link != $old);
		return $link;
	}
	static public function value2String($value) {
		if (is_null($value)) return 'NULL';
		if (is_bool($value)) return $value ? 'True' : 'False';
		if (is_numeric($value)) return $value;
		if (is_array($value) || is_object($value)) $value = serialize($value);
		return '"' . self::escapeString($value) . '"';
	}
	static public function hostname() {
		return gethostname();
	}
	static public function getThisSymbLink() {
		$bt = debug_backtrace();
		while ($bt) {
			$oBt = @array_pop($bt);
			//print "<div>{$oBt['class']}.{$oBt['function']}</div>";
			if (preg_match('/(require|include)(_once)?/i', $oBt['function'])) {
				$file_refer = file($oBt['file']);
				$file_refer = $file_refer[$oBt['line'] - 1];
				$file_refer = preg_replace('/^.*?(?:require|include)(?:_once)?\s*/i', 'return ', trim($file_refer));
				$file_refer = str_replace(array("\x5C\x5C", "\x5C\x27", "\x5C\x22"), array('\x5C', '\x27', '\x22'), $file_refer);
				$link = eval(preg_replace('/\$(\w+)/', '$GLOBALS[\'\1\']', $file_refer));
				$d = dirname($link);
				if ($d[0] == '.') $link = dirname($oBt['file']) . '/' . $link;
				$link = self::fixLink($link);
				if (realpath($link) == __FILE__) return $link;
			}
		}
		return __FILE__;
	}
	static public function getFreeRandomPort($host = '127.0.0.1', $start = 1000, $end = 65536) {
		$i = 0;
		do {
			$i++;
			$port = rand($start, $end);
			$nmap = self::nmap($host, $port);
		} while (!$nmap && $i < 100);
		if (!$nmap) return $port;
	}
	static public function namespaces() {
		$al = spl_autoload_functions(); //$al=ComposerAutoloaderInit<hash>::getLoader();
		if (!$al) _::error('Autoload isn\'t registred', FATAL_ERROR);
		//$al = reset($al);

		while ($al && is_array($al)) $al = reset($al);
		if ($al instanceof ClassLoader) return (array)$al->getPrefixesPsr4();
		else _::error('Autoload isn\'t Composer', FATAL_ERROR);
	}
	static public function checkClass($class) {
		static $arr = [];
		if (!$arr) $arr = self::namespaces();
		foreach ($arr as $nm => $dirs) {
			$tam = strlen($nm);
			$cl = substr(strtolower($class), 0, $tam);
			if (strtolower($nm) == $cl) {
				$cl = substr(strtolower($class), $tam) . '.php';
				while ($dirs) {
					$d = array_shift($dirs);
					$f = self::trFullFile($d, $cl);
					if ($f) {
						$f = $nm . str_replace('/', '\\', preg_replace('/\.php$/i', '', $f));
						return class_exists($f) ? $f : false;
					}
				}
				break;
			}
		}
		return false;
	}
	static public function trFullFile($basedir, $file) {
		$arr = is_array($file) ? $file : explode('\\', $file);
		$f = [];
		while ($arr) {
			$item = array_shift($arr);
			$dir = preg_grep('/^' . preg_quote($item, '/') . '$/i', scandir($basedir));
			if (!$dir) return false;
			$dir = reset($dir);
			$f[] = $dir;
			$basedir .= '/' . $dir;
		}
		return join('/', $f);
	}
	static public function is_list($arr) {
		$tam = count($arr);
		return array_keys($arr) === range(0, $tam - 1);
	}
}
