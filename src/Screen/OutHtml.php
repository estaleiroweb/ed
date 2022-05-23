<?php

namespace EstaleiroWeb\ED\Screen;

use EstaleiroWeb\ED\IO\_;
use EstaleiroWeb\Cache\Config;
use EstaleiroWeb\ED\IO\MimeType;
use EstaleiroWeb\ED\Secure\Secure;

/**
 * Page-Level DocBlock OutHtml
 * @package Easy
 * @subpackage Screen
 * @category Tools
 * @author Helbert Fernandes <helbertfernandes@yahoo.com.br>
 * @version 1.1 em 03/10/2006 15:00 - Helbert Fernandes
 */
/**
 * Imprime organizadamente todo o conteudo na tela
 *
 * @link  ../doc/OutHtml.doc
 * @example
 * <code>
 * 	$out=OutHtml::singleton();
 * </code>
 */
class OutHtml {
	static $lf = "\n";
	static private $instance;
	static public $defaultContext = 'web';
	/**
	 * Flag para classe Element
	 *
	 * @var boolean
	 */
	public $edit = false;
	/**
	 * Imprime ou nao um <script> das principais variaveis utilizadas como caminhos no server
	 *
	 * @var boolean
	 */
	public $setPath = false;
	/**
	 * Flag indicando se ha contendo a ser imprimido.
	 *
	 * @var boolean
	 */
	public $fill = false;
	/**
	 * Tabela de codigo utilizada
	 *
	 * @var string
	 */
	public $charset = 'utf-8'; //iso-8859-1
	/**
	 * Flag indicando se e uma aplicacao em shell
	 *
	 * @var boolean
	 */
	public $isLineCommand = false;
	/**
	 * Flag indicado se e para organizar ou nao o documento
	 *
	 * @var boolean
	 */
	public $organize = true;
	/**
	 * Flag indicado se pode adicionar Head, Foot, PreBody ou PosBoy
	 *
	 * @var boolean
	 */
	public $isAddContent = true;
	/**
	 * Flag indicando de pode imprimir os <script>
	 *
	 * @var boolean
	 */
	public $isPrintScript = true;
	/**
	 * Conteudo posicionado dentro do <head>
	 *
	 * @var array
	 */
	public $head = [];
	/**
	 * Lista de Atributos do <body>
	 *
	 * @var array
	 */
	public $attibute = [];
	/**
	 * Conteudo que se posiciona dentro do <body> logo no inicio
	 *
	 * @var array
	 */
	private $strDoctype = '';
	/**
	 * Conteudo que se posiciona dentro do <body> logo no inicio
	 *
	 * @var array
	 */
	public $prebody = [];
	/**
	 * Conteudo que se posiciona dentro do <body> bem no fim
	 *
	 * @var array
	 */
	public $posbody = [];
	/**
	 * Conteudo que se posiciona apos do <body> bem no fim
	 *
	 * @var array
	 */
	public $foot = [];
	/**
	 * O script a ser executado que fica no cabecalho do documento
	 *
	 * @var array
	 */
	public $headScript = [];
	/**
	 * O script a ser executado que fica no cabecalho do documento
	 *
	 * @var array
	 */
	public $jQueryScript = [];
	/**
	 * O script a ser executado que fica no rodape do documento
	 *
	 * @var array
	 */
	public $script = [];
	/**
	 * Parser do config.ini
	 *
	 * @var Config
	 */
	public $config;
	public $htmlTag = '';
	public $contentType = 'html';
	/**
	 * Tipos de DOCTYPE
	 *
	 * @var array
	 */
	public $doctype = array(
		'html5' =>                   '<!DOCTYPE html>',
		'w3c_html4.01' =>			'<!doctype html PUBLIC "-//W3C//DTD HTML 4.01//EN">',
		'w3c_html4.01_trans' =>		'<!doctype html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">',
		'w3c_html4.01_frame' =>		'<!doctype html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">',
		'w3c_html4.01/w3c' =>		'<!doctype html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
		'w3c_html4.01_trans/w3c' =>	'<!doctype html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
		'w3c_html4.01_frame/w3c' =>	'<!doctype html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
		'w3c_html4.0' =>				'<!doctype html PUBLIC "-//W3C//DTD HTML 4.0//EN">',
		'w3c_html4.0_trans' =>		'<!doctype html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">',
		'w3c_html4.0_frame' =>		'<!doctype html PUBLIC "-//W3C//DTD HTML 4.0 Frameset//EN">',
		'w3c_xhtml1.0_trans/w3c' =>	'<!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
		'w3c_xhtml1.0_frame/w3c' =>	'<!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
		'w3c_xhtml1.0_stric/w3c' =>	'<!doctype html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
		'w3c_xhtml1.1/w3c' =>		'<!doctype html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
		'w3c_html3.2' =>				'<!doctype html PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">',
		'ietf_html2.0' =>			'<!doctype html PUBLIC "-//IETF//DTD HTML 2.0//EN">',
		'ietf_html1.0' =>			'<!doctype html PUBLIC "-//IETF//DTD HTML Level 1//EN">',
		'ms3.01' =>					'<!doctype html PUBLIC "-//Microsoft/DTD Microsoft Internet Explorer 3.01 HTML//EN">'
	);
	public $buffer = [];
	public $title;
	public $titleSequence = [];
	private $savedBuffer = [];
	static public $isSaveBuffer = false;

	/**
	 * Inicializa a organizacao de conteudo
	 */
	static public function singleton() {
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	private function __construct() {
		_::verbose('Start ' . __CLASS__);
		$this->config = Config::singleton();
		//_::show($this->config);
		//_::show($_SERVER);
		$this->organize = $this->http = !isset($_SERVER['SHELL']);
		$this->isLineCommand = !$this->organize;
		if ($this->http) {
			$this->__ob_memory(ob_get_contents());
			$this->stopBuffer();
			ob_start(array(&$this, '__ob_memory'), 0);
		}
	}
	public function __ob_memory($buffer) {
		if (self::$isSaveBuffer) $this->savedBuffer[] = $buffer;
		else $this->buffer[] = $buffer;
	}
	public function stopBuffer() {
		while (@ob_end_clean());
		return $this;
	}
	/**
	 * Finaliza e exibe na tela todo o conteudo de modo organizado
	 */
	public function __destruct() {
		_::verbose('Close ' . __CLASS__);
		$this->stopBuffer();
		if ($this->fill || $this->buffer) {
			//Junta todo o corpo do documento
			if ($this->organize) {
				array_unshift($this->headScript, 'window.ed=' . json_encode($this->config->ed) . ';');
				$body = '';
				if ($this->prebody) $body .= implode(self::$lf, $this->prebody) . self::$lf;
				if ($this->buffer) $body .= implode('', (array)$this->buffer) . self::$lf;
				if ($this->posbody) $body .= implode(self::$lf, $this->posbody) . self::$lf;
				$body = preg_replace(array('/\s+$/', '/\<\s*\/\s*(html|body)\s*\>/i'), array('', ''), $body);
				//if ($this->setPath && $this->isPrintScript && isset($this->config)) $this->addHead($this->config->getJScript(),'JSConfigPath');
				//Retira itens nao pertencentes ao corpo do documento

				if (preg_match_all('/<\s*title\s*>(.*?)<\s*\/title\s*>\s*/i', $body, $res)) foreach ($res[1] as $k => $v) {
					$body = str_replace($res[0][$k], '', $body);
					$v = trim($v);
					if ($v) $this->title($v);
				}
				if (preg_match_all('/\s*<\s*(meta|link)(?:.|\s)*?>\s*/i', $body, $res)) foreach ($res[0] as $k => $v) {
					$body = str_replace($v, '', $body);
					$this->addHead(trim($v));
				}
				$extru = array('!doctype' => '', 'html' => '', 'body' => '');
				if (preg_match_all('/<\s*(!\s*DOCTYPE\s+|html|body)([^>]*?)>(\r?\n)?/i', $body, $res)) foreach ($res[1] as $k => $v) {
					$body = str_replace($res[0][$k], '', $body);
					$v = strtolower(preg_replace('/\s/', '', $v));
					if ($v) $extru[$v] = $res[2][$k];
				}
				if (preg_match_all('/<\s*head\s*>((?:.|\s)*?)<\s*\/head\s*>/i', $body, $res)) foreach ($res[1] as $k => $v) {
					$body = str_replace($res[0][$k], '', $body);
					$v = trim($v);
					if ($v) $this->addHead($v);
				}
				if (preg_match_all('/\s*<\s*style\s*>((?:.|\s)*?)<\s*\/style\s*>/i', $body, $res)) foreach ($res[0] as $v) {
					$body = str_replace($v, '', $body);
					$v = trim($v);
					if ($v) $this->addHead($v);
				}
				//$body=preg_replace('/(\r?\n){2,}','\1',$body);
				print $this->strDoctype ? $this->strDoctype : $this->extru('!doctype', $extru, '', false);

				if ($this->htmlTag) print $this->htmlTag;
				else print $this->extru('html', $extru);

				print '<head>' . self::$lf;
				if ($this->head) print implode(self::$lf, $this->head) . self::$lf;
				$this->printHead();
				print '</head>' . self::$lf;

				print $this->extru('body', $extru, $this->attibute);
				if ($body) print $body . self::$lf;
				print '</body>' . self::$lf;

				if ($this->foot) print implode(self::$lf, $this->foot) . self::$lf;
				print '</html>';
				$this->printScript();
			} else {
				if ($this->head) print implode(self::$lf, $this->head);
				if ($this->headScript || $this->jQueryScript) $this->printHead();
				$body = implode('', $this->prebody) . implode('', (array)$this->buffer) . implode('', $this->posbody);
				if ($body) print $body;
				if ($this->foot) print implode(self::$lf, $this->foot);
				if ($this->script) $this->printScript();
			}
		}
	}
	/**
	 * Imprime tags especiais
	 * 
	 * @param string $tag Nome do tag a ser impresso
	 * @param array $extru Array com a extrutura dos tags
	 * @param string $att Atributos adcionais do tag
	 * @param boolean $print Se e para imprimir o tag se nao existir atributos detro de extru
	 */
	private function extru($tag, $extru, $att = '', $print = true) {
		if (is_array($att)) {
			if ($att) {
				foreach ($att as $k => $v) $att[$k] = "$k='$v'";
				$att = ' ' . implode(' ', $att);
			} else $att = '';
		}
		if ($extru[$tag]) {
			$e = " " . trim($extru[$tag]);
			return '<' . $tag . $e . $att . '>' . self::$lf;
		} elseif ($print) return '<' . $tag . $att . '>' . self::$lf;
	}
	/**
	 * Imprime scripts do Rodape
	 */
	private function printScript() {
		$this->printAllScript($this->script);
		return $this;
	}
	/**
	 * Imprime scripts do cabecalho
	 */
	private function printHead() {
		$s = $this->headScript;
		if ($this->jQueryScript) {
			$s[] = '$(document).ready(function(){';
			$j = preg_replace(array('/^/', '/(\n)/'), array("\t", "\\1\t"), $this->jQueryScript);
			foreach ($j as $v) $s[] = $v;
			$s[] = '});';
		}
		$this->printAllScript($s);
		if ($s) print self::$lf;
		return $this;
	}
	/**
	 * Imprime scripts a executar
	 */
	private function printAllScript($s) {
		if ($this->isPrintScript && $s) {
			$script1 = [];
			$script2 = [];
			foreach ($s as $k => $v) if (is_numeric($k)) $script1[] = $v;
			else $script2[] = $v;
			$script1 = array_merge($script1, $script2);
			print self::$lf . "\t<script language='javascript'>" . self::$lf . "\t\t";
			print implode(self::$lf . "\t\t", $script1);
			print self::$lf . "\t</script>";
		}
		return $this;
	}
	//adiciona conteudo($value) a uma variavel($var) no inicio($position=0) ou no fim
	/**
	 * Enter description here...
	 *
	 * @param array $var
	 * @param string $value
	 * @param string|interger $position Localizacao do conteudo. NULL: Adiciona em Sequência (direção ao centro do body), -1|<0 Adiciona contrária ao NULL, >=0:Adiciona na posição exata
	 */
	private function add(&$var, $value = '', $position = null) {
		if (!$this->isAddContent) return $this;
		if ($value) {
			if (is_null($position)) $var[] = $value;
			elseif (is_numeric($position) && $position < 0) array_unshift($var, $value);
			else $var[$position] = $value;
			/*
			if ("$position"==="0") array_unshift($var,$value);
			elseif ($position) $var[$position]=$value;
			else  $var[]=$value;
			*/
			$this->fill = true;
		}
		return $this;
	}
	public function head($value, $position = null) {
		if (!$this->isPrintScript && preg_match('/<\s*script.*>/i', $value)) return $this;
		$value = preg_replace('/\s*$/', '', str_replace(self::$lf, self::$lf . "\t", $value));
		return $this->add($this->head, "\t" . $value, $value);
		/*
		if(is_null($position)) $this->head[]=$value;
		else $this->add($this->head,$value,$position);
		return $this;
		*/
	}
	//adiciona no cabecalho
	/**
	 * Adciona um conteudo dentro de <head>. Ha alguns topicos pre programados
	 * addHead('um titulo','title') : define o tag <title>
	 * addHead('titulo','title') : define o tag <title>
	 * addHead('arquivo_sem_extensao','style') : define o tag <link> com um arquivo dentro de skin/default/css
	 * addHead('arquivo_sem_extensao','script') : define o tag <script> com um arquivo dentro de js
	 * addHead('titulo','nocache') : nao grava cache do browse
	 * addHead('url;segundos','refresh') : define o tag <meta> de refresh
	 * addHead('tipo','type') : define o content-type de acordo com a variavel $content_types
	 * addHead('<tag>') : define uma instrucao qualquer
	 *
	 * @param string $value
	 * @param string $position
	 */
	public function addHead($value, $position = null, $referer = null) {
		if (!$this->isAddContent) return $this;

		$opt = array(
			array($value, $position),
			array($position, $value),
		);
		foreach ($opt as $k => $o) {
			switch (strtolower($o[0])) {
				case 'nocache':
					return $this->nocache();
				case 'jquery':
					return $this->jquery();
				case 'jqueryui':
					return $this->jqueryUI();
				case 'title':
					return $this->title($o[1]);
				case 'style':
					return $this->style($o[1], $referer);
				case 'script':
					return $this->script($o[1], $referer);
				case 'redirect':
					return $this->redirect($o[1]);
				case 'refresh':
					return $this->refresh($o[1]);
				case 'doctype':
					return $this->doctype($o[1]);
				case 'file':
					return $this->attachment($o[1]);
				case 'type':
					return $this->contentType($o[1]);
			}
		}
		return $this->head($value, $position);
	}
	/**
	 * Adiciona um conteudo no <body> entre o prebody e o posbody
	 *
	 * @param string $value Conteudo em si. Tags ou texto
	 * @param string $position Localizacao do conteudo. NULL: Adiciona em sequência de do Body, -1|<0 Adiciona contrária ao NULL, >=0:Adiciona na posição exata
	 */
	public function addBody($value = '', $position = null) {
		return $this->add($this->buffer, $value, $position);
		/*
		if ($position===null) $this->buffer[]=$value;
		elseif (is_numeric($position) && $position<0) array_unshift($this->buffer,$value);
		else $this->buffer[$position]=$value;
		return $this;
		*/
	}
	/**
	 * Adiciona um conteudo no <body> logo no inicio
	 *
	 * @param string $value Conteudo em si. Tags ou texto
	 * @param string $position Localizacao do conteudo. NULL: Adiciona em Sequência (direção ao centro do body), -1|<0 Adiciona contrária ao NULL, >=0:Adiciona na posição exata
	 */
	public function addPreBody($value = '', $position = null) {
		return $this->add($this->prebody, $value, $position);
		/*
		if (!$this->isAddContent) return;
		if ($position===null) $this->prebody[]=$value;
		elseif (is_numeric($position) && $position<0) array_unshift($this->prebody,$value);
		else $this->prebody[$position]=$value;
		$this->fill=true;
		return $this;
		*/
	}
	/**
	 * Adiciona um conteudo no <body> bem ao final
	 *
	 * @param string $value Conteudo em si. Tags ou texto
	 * @param string $position Localizacao do conteudo. NULL: Adiciona em Sequência (direção ao centro do body), -1|<0 Adiciona contrária ao NULL, >=0:Adiciona na posição exata
	 */
	public function addPosBody($value = '', $position = null) {
		if (!$this->isAddContent) return;
		if ($position === null) array_unshift($this->posbody, $value);
		if (is_numeric($position) && $position < 0)  $this->prebody[] = $value;
		else $this->prebody[$position] = $value;
		$this->fill = true;
		return $this;
	}
	/**
	 * Adiciona um conteudo no rodape
	 *
	 * @param string $value Conteudo em si. Tags ou texto
	 * @param string $position Localizacao do conteudo. Veja $this->add
	 */
	public function addFoot($value = '', $position = null) {
		return $this->add($this->foot, $value, $position);
	}
	/**
	 * Adiciona um atributo no body
	 *
	 * @param string $attrib Nome do atributo Ex: bgcolor
	 * @param string $value Valor do atributo Ex: #FFFFFF
	 */
	public function addAttribute($attrib, $value) {
		$this->attibute[$attrib] = $this->htmlSlashes($value);
		$this->fill = true;
		return $this;
	}
	/**
	 * Apaga o conteudo do cabecalho printado ate o momento
	 *
	 */
	public function clearHead() {
		$this->head = [];
		return $this;
	}
	/**
	 * Apaga o conteudo meio do corpo e se true, apaga todo o body
	 *
	 * @param boolean $tudo se true apaga tambem atributos prebody e posbody
	 */
	public function clearBody($tudo = false) {
		if ($tudo) {
			$this->stopBuffer();
			$this->prebody = [];
			$this->posbody = [];
			$this->attibute = [];
		}
		$this->buffer = [];
		return $this;
	}
	/**
	 * Apaga o conteudo do rodape printado ate o momento
	 */
	public function clearFoot() {
		$this->foot = [];
		return $this;
	}
	/**
	 * Apaga todo o conteudo printado ate o momento
	 */
	public function clearAll() {
		$this->clearHead();
		$this->clearBody(true);
		$this->clearFoot();
		$this->script = [];
		$this->headScript = [];
		$this->jQueryScript = [];
		return $this;
	}
	/**
	 * Faz o slashes real
	 *
	 * @param string $value
	 * @return string
	 */
	public function htmlSlashes($value) {
		return strtr($value, array('"' => "\\x22", "'" => "\\x27", "\t" => "\\t", "\n" => "\\n", "\r" => "\\r", "\\" => "\\\\"));
	}
	/**
	 * Codifica o HTML
	 *
	 * @param string $value
	 * @return string
	 */
	public function htmlSpecial($value) {
		if (is_array($value)) return $this->htmlSpecial(json_encode($value));
		if (is_object($value)) $value = "$value";
		return htmlspecialchars($value, ENT_QUOTES);
	}
	public function saveBuffer() {
		$this->savedBuffer = [];
		self::$isSaveBuffer = true;
		return $this;
	}
	public function loadBuffer() {
		self::$isSaveBuffer = false;
		return implode('', $this->savedBuffer);
	}
	public function clearBuffer() {
		$this->savedBuffer = [];
		return $this;
	}
	public function paramUrl($parm, $value = '', $insert = true) {
		if (!session_id()) @session_start();
		$url = str_replace("{$GLOBALS['__autoload']->dir}/", "", $_SERVER["SCRIPT_NAME"]);

		$query = preg_grep("/^" . preg_quote($parm) . "\=/", explode("&", $_SERVER['QUERY_STRING']), PREG_GREP_INVERT);
		if ($insert) $query[] = "$parm=$value";
		$query = implode("&", $query);
		$query = $query ? "?$query" : '';
		$result = "{$_SERVER['SCRIPT_NAME']}$query";

		if (!isset($_SESSION['urlsDone'])) $_SESSION['urlsDone'] = [];
		$_SESSION['urlsDone'][$url] = $result;
		return $result;
	}
	public function baseClass($val) {
		return preg_replace('/.*\\\\([^\\\\]+)$/', '\1', $val);
	}
	public function getHeadPath($value, $referer = null, $sub = 'js') {
		static $arr = [];
		$k = "$value|$referer|$sub";
		if (key_exists($k, $arr)) return $arr[$k];
		if (preg_match('/^(http:)\/\//i', $value)) return $value;
		$value = $this->baseClass($value);
		if (is_null($referer)) $referer = self::$defaultContext;
		if ($referer[0] == '/') {
			$referer=preg_replace('/\/$/','',$referer);
			$path = $referer;
			$root = $this->config->root . $referer;
		} elseif (property_exists($this->config, $referer)) {
			$path = $this->config->{$referer}[$sub];
			$root = $this->config->root . $path;
		} else {
			if (is_file($referer)) $referer = dirname($referer);
			$path = preg_replace('/^' . preg_quote($this->config->root, '/') . '/', '', $referer);
			$root = $referer;
		}
		/*
		if ($path[0] == '.') $root = $this->config->fullpath . substr($path, 1);
		elseif ($path[0] == '/') $root = $this->config->root . $path;
		else $root = $this->config->fullpath . '/' . $path;
		*/
		$file = $value . '.' . $sub;
		$root .= '/' . $file;
		$out = $path . '/' . $file;
		
		if (is_file($root)) $out .= '?updateTS=' . (int)@filectime($root);
		return $arr[$k] = $out;
	}
	public function meta($name, $content = null, $attr = 'name') {
		$content = $content ? 'content="' . $this->htmlSpecial($content) . '"' : '';
		return $this->add($this->head, "\t<meta {$attr}=\"$name\" $content />", __FUNCTION__ . '#' . $attr . ':' . $name);
	}
	public function nocache() {
		@header("expires: Mon, 26 Jul 1990 05:00:00 GMT");
		@header("last-modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		@header("cache-control: private");
		@header("pragma: no-cache");
		/*
		$value ="\t<meta http-equiv='expires' content='Mon, 26 Jul 1990 05:00:00 GMT' />".self::$lf;
		$value.="\t<meta http-equiv='last-modified' content='".gmdate("D, d M Y H:i:s")." GMT' />".self::$lf;
		$value.="\t<meta http-equiv='cache-control' content='private' />".self::$lf;
		$value.="\t<meta http-equiv='pragma' content='no-cache' />";
		*/
		return $this;
	}
	public function jquery() {
		return $this->script('http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
		//return $this->add($this->head,'<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" language="JavaScript" type="text/JavaScript"></script>',__FUNCTION__);
	}
	public function jqueryUI() {
		return $this->script('http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js');
	}
	public function title($value, $h1 = false) {
		if (@$this->head['title']) {
			array_unshift($this->titleSequence, $value);
			$value = implode('-', $this->titleSequence);
		}
		$this->add($this->head, "\t<title>" . strip_tags($value) . "</title>", __FUNCTION__);
		if ($h1) {
			print '<div class="container"><h1>' . $value . '</h1></div>';
			$this->title = $value;
		}
		return $this;
	}
	public function style($value = 'style', $referer = '.') {
		if ($value == 'style') return $this->style(preg_replace('/\.[^\.]+$/', '', basename($_SERVER['SCRIPT_FILENAME'])), $referer);
		if (preg_match('/^https?:\/+/i', $value)) return $this->add($this->head, "\t<link href='{$value}' rel='stylesheet' type='text/css' />", __FUNCTION__ . '#' . $value);
		$lnk = $this->getHeadPath($value, $referer, 'css');
		if ($lnk) $this->add($this->head, "\t<link href='{$lnk}' rel='stylesheet' type='text/css' />", __FUNCTION__ . '#' . $value);
		return $this;
	}
	public function script($value = 'script', $referer = '.') {

		if (!$this->isPrintScript) return $this;
		if ($value == 'script') {
			//_::show([$value, $referer,$_SERVER['SCRIPT_FILENAME']]);
			return $this->script(preg_replace('/\.[^\.]+$/', '', basename($_SERVER['SCRIPT_FILENAME'])), $referer);
		}
		if (preg_match('/^https?:\/+/i', $value)) return $this->add($this->head, "\t<script src='{$value}' language='JavaScript' type='text/JavaScript'></script>", __FUNCTION__ . '#' . $value);
		$lnk = $this->getHeadPath($value, $referer, 'js');
		if ($lnk) $this->add($this->head, "\t<script src='{$lnk}' language='JavaScript' type='text/JavaScript'></script>", __FUNCTION__ . '#' . $referer . '/' . $value);
		return $this;
	}
	public function redirect($url = '/', $time = 0) {
		$url = $url ? ';URL=' . $url : '';
		if (!$this->isLineCommand) $this->meta('refresh', $time . $url, 'http-equiv');
		return $this;
	}
	public function refresh($time = 30, $url = null) {
		if (!is_numeric($time)) {
			$p = explode(';', $time);
			$url = null;
			if (count($p) == 2) {
				$time = $p[0];
				$url = $p[1];
			}
		}
		return $this->redirect($url, $time);
	}
	public function doctype($value = null) {
		if (!$value) $this->strDoctype = "<!doctype html>\n";
		elseif (@$this->doctype[$value]) $this->strDoctype = "{$this->doctype[$value]}\n";
		else $this->strDoctype = "<!doctype $value>\n";
		return $this;
	}
	public function attachment($value) {
		@header("Content-Disposition: attachment; filename=$value");
		$ext = preg_replace('/^.*\.(.*?)$/i', '\1', $value);
		if ($ext) {
			if (isset(MimeType::$content_types[$ext])) @header('content-type: ' . MimeType::$content_types[$ext] . "; charset={$this->charset}");
			else @header("content-type: application/{$ext}; charset={$this->charset}");
		}
		//@header ('content-type: application/'.);
		return $this;
	}
	public function contentType($type = null) {
		if (!$type) $type = $this->contentType;
		// http://www.iana.org/assignments/media-types/
		// http://www.usf.uni-osnabrueck.de/infoservice/wwwfaq/mime.htm
		$type = strtolower(trim($type));
		if (!$type) $type = 'html';
		if (isset(MimeType::$content_types[$type])) $type = MimeType::$content_types[$type];
		elseif (strpos($type, '/') === false) $type = "application/$type";
		if (@$_SERVER['SHELL']) @header("content-type: $type; charset={$this->charset}");
		else $this->meta('content-type', $type . '; charset=' . $this->charset, 'http-equiv');
		return $this;
	}

	public function html5() {
		$this->doctype('html5');
		$this->ie();
	}
	public function ie() {
		return $this->meta('X-UA-Compatible', 'IE=edge', 'http-equiv');
	}
	public function mobile() {
		return $this->meta('viewport', 'width=device-width, initial-scale=1, shrink-to-fit=no');
	}
	public function description($value = '') {
		return $this->meta('description', $value);
	}
	public function author($value = '') {
		return $this->meta('author', $value);
	}
	public function keywords($value = '') {
		return $this->meta('keywords', is_array($value) ? implode(', ', $value) : $value);
	}
	public function favicon($icon = 'favicon.ico', $iconApple = null) {
		$this->add($this->head, '	<link href="' . $icon . '" rel="shortcut icon" type="image/x-icon" />', __FUNCTION__ . '#generic');
		if (is_null($iconApple)) $iconApple = $icon;
		if ($iconApple) {
			$this->add($this->head, '	<link href="' . $iconApple . '" rel="apple-touch-icon" type="image/x-icon" />', __FUNCTION__ . '#apple');
		}
		return $this;
	}
	public function compatibility_IE() {
		return $this->add($this->head, '	<!--[if lt IE 9]>
		<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<!--[if lte IE 6]>
		<link rel="stylesheet" type="text/css" href="http://universal-ie6-css.googlecode.com/files/ie6.1.1.css" media="screen, projection" />
	<![endif]-->', __FUNCTION__);
	}
	public function addFormTag($action = '', $attr = '') {
		$this->addPreBody("<form action='{$action}' role='form' method='post' enctype='multipart/form-data'$attr>\n", 'form');
		$this->addPosBody("</form>\n", 'form');
	}
	static function tabs($links) {
		$s = Secure::$obj;
		//$dirName=dirname($GLOBALS['__autoload']->url);
		$baseName = basename($GLOBALS['__autoload']->url);
		$o = OutHtml::singleton();
		$o->addBody('<div class="container"><ul class="nav nav-tabs">');
		foreach ($links as $k => $v) {
			$i = (array)$v;
			$i = $i[0];
			//if($s->permitionFile($dirName.'/'.$i)){
			if ($s->permitionFile($i)) {
				$o->addBody('<li role="presentation"' . (in_array($baseName, (array)$v) ? ' class="active"' : '') . '><a href="' . $i . '">' . $k . '</a></li>');
			}
		}
		$o->addBody('</ul></div>');
	}
}
