<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\Cache\Config;
use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\Ext\Ed;
use EstaleiroWeb\ED\Ext\JQuery_Cookie;
use EstaleiroWeb\ED\IO\MediatorPHPJS;
use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\ED\Tools\Id;

$Elements = [];
class Element {
	private $htmlView = '';
	private $htmlEdit = '';
	protected $typeList = ['text'];
	protected $started = false;
	protected  $protect = [
		'id' => '',
		'printed' => false,
		'debug' => false,
		'name' => null,
		'edit' => null,
		'editForce' => null,
		'showLabel' => true,
		'showBox' => true,
		'width' => null,
		'height' => null,
		'default' => null,
		'fn' => null,
		'strRequired' => '*',
		'type' => 'text',
		'hidden' => null,
		'inputValue' => null,
		'autoWidth' => null,
		'form' => null,
		'help' => null,
		'objectForm' => null,
		'caretWidth' => 4,
		'check_types' => ['match', 'in', 'key', 'regexp', 'glob', 'smart'],
		'error' => [],
		'attr' => [],

		'preIdMain' => 'fld_',
		'preIdDisplay' => 'd_',
		'preIdInput' => 'i_',
		'preIdButton' => 'b_',
		'inputformat' => null,
		'displayformat' => null,

		'updatable' => false,
		'conn' => null,
		'sql' => null,
		'fields' => null,
		'order' => null,
		'separator' => null,
		'groupSource' => null, //Fields names array or string,string which build the $value option
		'source' => null, /*
			sql (1 Field=$value, 2,..=$text) or array assoieted
			
			array(
				'table'=>'[db.]table',
				'value'=>'fieldname_value',
				['conn'=>'connection',]
				['primitiveKeys'=>true,]
				['key'=>array(
					'this=([db.]table) field'=>fixed_value,
				),]
				['order'=>'fieldname_seq',]
				['fields'=>array(
					//replacement variables $value, ($text or $text[n] or $text[$field]) and $seq|$key|$order 
					'this=([db.]table) field'=>'"fixed_value ".$value." ".$html." ".$field['name']." ".$seq." ".$source[value][field]',
				),]
				
				['conn'=>'connection',]
				'label'=>'Some Label',
				'view'=>'[db.]table or SELECT ',
				['primitiveKeys'=>true,]
				'order'=>'fieldname_seq',
				'key'=>'form_field',                        //when form_field == source_field
				'key'=>'form_field,form_field2',            //when form_field == source_field
				'key'=>array('form_field'=>'source_field'), //when form_field != source_field
				'getCells'=>'Cnl',
				['fields'=>array(
					//replacement variables $value, ($text or $text[n] or $text[$field]) and $seq|$key|$order 
					'this=([db.]table) field'=>'"fixed_value ".$value." ".$html." ".$field['name']." ".$seq." ".$source[value][field]',
				),]
			)
		*/
		'saveTo' => null, /*array(
			'table'=>'[db.]table',
			'value'=>'fieldname_value',
			['conn'=>'connection',]
			['primitiveKeys'=>true,]
			['key'=>array(
				'this=([db.]table) field'=>fixed_value,
			),]
			['order'=>'fieldname_seq',]
			['fields'=>array(
				//replacement variables $value, ($text or $text[n] or $text[$field]) and $seq|$key|$order 
				'this=([db.]table) field'=>'"fixed_value ".$value." ".$html." ".$field['name']." ".$seq." ".$source[value][field]',
			),]
		)*/
		//'fnOption'=>null, //if declareted, it is a function which replace the text instead auto join groupSource with separator

		'onstart' => null,
		'onafterinsert' => null,
		'onafterdelete' => null,
		'onafterupdate' => null,
		'onbeforeinsert' => null,
		'onbeforeupdate' => null,
		'onbeforedelete' => null,
		'value' => null,
	];
	protected $events = [
		'onactivate' => null,
		'onbeforeactivate' => null,
		'onbeforecut' => null,
		'onbeforedeactivate' => null,
		'onbeforeeditfocus' => null,
		'onbeforepaste' => null,
		'onblur' => null,
		'onchange' => null,
		'onclick' => null,
		'oncontextmenu' => null,
		'oncontrolselect' => null,
		'oncut' => null,
		'ondblclick' => null,
		'ondeactivate' => null,
		'ondrag' => null,
		'ondragend' => null,
		'ondragenter' => null,
		'ondragleave' => null,
		'ondragover' => null,
		'ondragstart' => null,
		'ondrop' => null,
		'onerrorupdate' => null,
		'onfilterchange' => null,
		'onfocus' => null,
		'onfocusin' => null,
		'onfocusout' => null,
		'onhelp' => null,
		'onkeydown' => null,
		'onkeypress' => null,
		'onkeyup' => null,
		'onload' => null,
		'onlosecapture' => null,
		'onmousedown' => null,
		'onmouseenter' => null,
		'onmouseleave' => null,
		'onmousemove' => null,
		'onmouseout' => null,
		'onmouseover' => null,
		'onmouseup' => null,
		'onmousewheel' => null,
		'onmove' => null,
		'onmoveend' => null,
		'onmovestart' => null,
		'onpaste' => null,
		'onpropertychange' => null,
		'onreadystatechange' => null,
		'onresize' => null,
		'onresizeend' => null,
		'onresizestart' => null,
		'onselect' => null,
		'onselectstart' => null,
		'onsubmit' => null,
		'ontimeerror' => null,

		'disabled' => null,
		'placeholder' => null,
		'tabindex' => null,
		'accesskey' => null,

		'saveMask' => null,
		'fillChar' => null,

		'remote' => null,
		'number' => null,
		'digits' => null,
		'date' => null,
		'email' => null,
		'url' => null,
	];
	public $methods = [];
	protected $displayAttr = [
		'ed-element' => 'string',
		'ed-form-id' => null,
		'ed-form-fieldname' => null,
		'ed-href' => null,
		'ed-method' => null,
		'ed-class' => null,
		'title' => null,

		'href' => null,
		'target' => null,
		'align' => null,
		'size' => null,
		'label' => null,
		'autocomplete' => null,
		'class' => null,
		'classLabel' => null,

		'auto_increment' => null,
		'rows' => null,
		'wrap' => null,
		'readonly' => null,
	];
	protected $inputAttr = [
		'minlength' => null,
		'maxlength' => null,
		'validate' => null,
		'required' => null,
	];
	protected $variables = [];
	public $style = [];
	public $details = []; //Depreciada
	public $OutHtml, $config;


	public function __construct($name = '', $value = null, $id = null) {
		$this->makeVariables();
		$this->type = $this->typeList[0];
		$this->OutHtml = OutHtml::singleton();
		$this->config = Config::singleton();
		$this->name = $name;
		if ($id != '') $this->id = $id;
		else {
			$oId = Id::singleton();
			$this->id = $oId->id;
		}
		$GLOBALS['Elements'][$this->id] = $this;
		if (!is_null($value)) $this->set('value', $value);
		if (is_null($this->class)) $this->addClass('form-control');
		//show($this->displayAttr['ed-class']);
		if (!$this->displayAttr['ed-class']) $this->displayAttr['ed-class'] = $this->OutHtml->baseClass(get_class($this));
		$this->displayAttr['ed-element'] = $this->makeEdElement($this->displayAttr['ed-class']);
		$this->OutHtml->style(__CLASS__, 'ed');
		$this->OutHtml->style($this->displayAttr['ed-class'], 'ed');
		$this->script();
	}
	public function __toString() {
		if ($this->printed) return '';
		$this->printed = true;
		if ($this->hidden) return $this->contentHidden();
		else return $this->isEdit() ? $this->makeControl() : $this->makeHref($this->makeContent());
	}
	public function __invoke() {
		$this->getFormVarFunc($field, $group);
		return $this->func($this->displayformat ? $this->format($this->displayformat) : $this->value, $field, $group);
	}
	public function __get($var) {
		if (method_exists($this, $fn = 'get' . ucfirst($var))) return $this->$fn();
		return $this->get($var);
	}
	public function __set($var, $value) {
		if (method_exists($this, $fn = 'set' . ucfirst($var))) return $this->$fn($value);
		return $this->set($var, $value);
	}
	public function __isset($name) {
		return isset($this->variables[$name]);
	}
	public function __unset($name) {
		if (isset($this->variables[$name])) $this->{$this->variables[$name]}[$name] = null;
	}
	public static function MetaElements() {
		$k = str_replace('.php', '', preg_grep('/^Element.+\.php$/', scandir(__DIR__)));
		return array_combine($k, str_replace('Element', '', $k));
	}
	protected function script($file = null) {
		new JQuery_Cookie();
		new Ed;
		$this->OutHtml->script(__CLASS__, 'ed');
		$this->OutHtml->script($file ? $file : get_class($this), 'ed');
		return $this;
	}
	protected function style($file = null) {
		$this->OutHtml->style($file ? $file : get_class($this), 'ed');
		return $this;
	}
	public function get($var) {
		if (array_key_exists($var, $this->variables)) {
			$key = $this->variables[$var];
			$ret = $this->$key;
			return $ret[$var];
		}
	}
	public function set($var, $value, $target = null) {
		if (array_key_exists($var, $this->variables)) $target = $this->variables[$var];
		if ($target) {
			$tmp = $this->$target;
			$tmp[$var] = $value;
			$this->$target = $tmp;
			$this->variables[$var] = $target;
		}
		return $this;
	}
	public function del($var) {
		if (array_key_exists($var, $this->variables)) {
			$target = $this->variables[$var];
			if (array_key_exists($var, $this->$target)) {
				$tmp = $this->$target;
				unset($tmp[$var]);
				$this->$target = $tmp;
			}
			unset($this->variables[$var]);
		}
		return $this;
	}
	public function mov($var, $target = 'protect') {
		$val = $this->$var;
		$this->del($var)->set($var, $val, $target);
		return $this;
	}

	public function setId($value) {
		return $this->set('id', $value);
	}
	//public function setLabel($value){ return $this->set('label',preg_replace('/[ _]+/',' ',$value)); }
	public function setType($value, $nm = 'type') {
		$list = $nm . 'List';
		if (is_numeric($value)) {
			if (array_key_exists($value, $this->$list)) return $this->set($nm, $this->$list[$value]);
		} elseif (in_array($value, $this->$list)) return $this->set($nm, $value);
		return $this->set($nm, $this->$list[0]);
	}
	public function setReadonly($value) {
		return $this->set('readonly', $value ? true : null);
	}
	public function setTabindex($value) {
		return ($value = (int)$value) ? $this->set('tabindex', $value) : $this;
	}
	public function setWrap($value) {
		return $this->set('wrap', is_string($value) ? $value : ($value ? 'soft' : 'off'));
	}
	public function setConn($value) {
		return $this->set('conn', is_object($value) ? $value : Conn::dsn($value));
	}
	public function setAutocomplete($value = false) {
		return $this->set('autocomplete', is_null($value) ? null : ($value ? 'on' : 'off'));
	}
	public function setSource($value, $keyIsText = true, $fields = null) {
		if (is_array($value)) return $this->set('source', $value);
		if (is_object($value)) return $this->set('source', (array)$value);
		if (preg_match('/^(\s|\()*(select|call)\b\s+`?./i', $value)) return $this->set('sql', $value);
		if (preg_match('/^\s*(\w+|`[^` ]+`)(\.(\w+|`[^` ]+`))?\s*$/i', $value)) return $this->set('sql', $value);
		$value = preg_split('/\s*[,;]\s*/', trim($value));
		if ($keyIsText) {
			$value = array_combine($value, $value);
			//$value=array_flip($value);
			//foreach($value as $k=>&$v) $value[$k]=$k;
			//show($value);
		}
		return $this->set('source', $value);
	}
	public function setFunction($value) {
		$this->fn = $value;
		return $this;
	}
	public function setError($value) {
		$this->protect['error'][] = $value;
		return false;
	}
	public function setClass($value) {
		$this->addClass($value);
		return false;
	}
	public function setGroupSource($value) {
		if (is_string($value)) $value = preg_split('/\s*[,;]\s*/', trim($value));
		return $this->set('groupSource', (array)$value);
	}
	public function getConn(&$conn = null) {
		if (!$conn) $conn = $this->get('conn');
		if (!$conn && ($form = $this->form)) {
			$conn = @$form->conn;
			$this->set('conn', $conn);
		}
		if (!is_object($conn)) $conn = Conn::dsn($conn);
		//show($conn);
		return $conn;
	}
	public function getSource() {
		($out = $this->get('sql')) || ($out = $this->get('source'));
		return $out;
	}
	public function getValue() {
		$value = $this->get('value');
		//if($this->label=='UserUpd') show([gettype($value),$value]);
		$inputValue = $this->inputValue;
		if ($this->isEdit()) {
			//if(get_class($this)=='ElementCalendar' && $this->id='id9') show($this->inputValue);
			//if(Secure::$idUser==2 && $this->label=='OCs') show(array($value,$this->inputValue,$this->default));
			//if(Secure::$idUser==2 && $this->label=='Owner') show(array($value,$this->inputValue,$this->default));
			if (!is_null($inputValue)) return $inputValue;
			if (is_null($value)) return $this->default;
		} elseif (is_null($value) && !is_null($inputValue)) return $inputValue;
		//if($this->label=='idDevice') show($value);
		return $value;
	}
	public function getName() {
		$name = $this->get('name');
		return $name == '' ? $this->preIdInput . $this->id : $name;
	}
	public function getLabel() {
		($label = $this->get('label')) || ($label = $this->label_reformat($this->get('name')));
		return $label;
	}
	public function getWidth() {
		static $wAdd = 3;
		static $wMax = 120;

		$width = $this->get('width');
		if (preg_match('/^(|\d+)$/', $width, $ret) && (
			($l = @$ret[1]) != '' ||
			($l = $this->length) != '' ||
			($l = $this->size) != '' ||
			($l = $this->scale) != '' ||
			($l = $this->maxlength) != '')) {
			$width = $l > $wMax ? '100%' : (ceil($l / 2) + $wAdd) . "em";
		}
		return $width;
	}
	public function label_reformat($label) {
		$erIn = array(
			'/([[:lower:]])(?=[[:upper:]])/',
			'/[ _]+/',
		);
		$erOut = array(
			'\1 ',
			' ',
		);
		return preg_replace($erIn, $erOut, $label);
	}
	public function getFunction() {
		return $this->fn;
	}

	public function makeSource($force = true, &$value = null) {
		$width = 0;
		$out = [];
		$value = $this->value;
		if (!is_array($value)) {
			if ($value == '') $value = [];
			else {
				$value = preg_split('/\s*[,;]\s*/', $value);
				$value = array_combine($value, $value);
			}
		}
		$source = $this->source;

		if (!$this->required && $force) $out[''] = '';
		if ($this->isDbSource($source)) {
			$inputValue = $this->inputValue;
			$sql = $this->rebuildSQL($source->view);
			if (!is_null($inputValue)) {
				$sqlFind = preg_replace('/(\blimit)\s+(\d(?:\s*,\s*\d+)?)([\)\s;]+)?$/i', '\1 0 \3', $sql);
				if ($sql == $sqlFind) $sqlFind .= ' LIMIT 0';
				$res = $this->query($sqlFind, $source->conn);
				$field = $res->fetch_fields();
				$res->close();
				$idField = $field[0]->name;
				$sql = 'SELECT * FROM (' . $sql . ') t WHERE `' . $idField . '` IN ("' . implode('","', $value) . '")';
			}
			$f = $this->fields ? preg_split('/\s*[,;]\s*/', $this->fields) : false;
			//print "<pre>".print_r($sql,true)."</pre>";
			$res = $this->query($sql, $source->conn);
			($separador = $this->separator) || ($separador = '-');
			while ($line = $res->fetch_assoc()) {
				$key = current($line);
				if (!is_null($inputValue) && !in_array($key, $value)) continue;
				if ($f) {
					$o = [];
					foreach ($f as $k => $v) if (isset($line[$v])) $o[] = $line[$v];
					$line = $o;
				} elseif (count($line) > 1) array_shift($line);
				$out[$key] = implode($separador, $line);
				$width = max($width, strlen($out[$key]));
			}
			$res->close();
		} else {
			foreach ($out as $v) $width = max($width, strlen($v));
			if ($source) foreach ($source as $k => $v) {
				$out[$k] = $v;
				$width = max($width, strlen($v));
			}
		}
		//$this->source=$source;
		//show($out);
		foreach ($value as $k => $v) {
			//foreach($value as $k=>$v) if(!array_key_exists($k,$out)) {
			if (array_key_exists($k, $out)) $v = $out[$k];
			else $out[$k] = $v;
			$width = max($width, strlen($v));
		}
		if ($this->autoWidth || !$this->width) $this->width = $width >= 120 ? '100%' : (ceil($width / 2) + $this->caretWidth) . 'em';
		return $out;
	}
	public function makeSourceList(&$value = null) {
		static $out = [];

		if (!$out) {
			$source = $this->source;
			if ($this->isDbSource($source)) {
				$sql = $this->rebuildSQL($source->view);
				//print "<pre>".print_r($sql,true)."</pre>";
				$res = $this->query($sql, $source->conn);
				while ($line = $res->fetch_assoc()) $out[current($line)] = $oldLine = $line;
				$res->close();
			} else $out = $this->source;
		}
		if ($out && is_array($tmp = current($out))) $value = key($tmp);
		return $out;
	}
	public function makeContent() {
		$this->getFormVarFunc($field, $group);
		$v = $this->value;
		$value = $this->func($this->displayformat ? $this->format($this->displayformat) : $v, $field, $group);
		$out = $this->htmlLabel();
		$attr = $this->makeHtmlAttrId();
		$attr .= $this->buildStyles();
		$attr .= $this->makeAttrib();
		$attr .= ' value=\'' . htmlspecialchars($v, ENT_QUOTES) . '\'';
		$out .= $this->showBox ? "<span$attr>$value</span>" : $value;
		return $out;
	}
	public function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		//$this->script();
		$v = $this->value;
		$value = htmlspecialchars($this->inputformat ? $this->format($this->inputformat, $v) : $v, ENT_QUOTES);
		//print "$v<br>";
		$attr = $this->makeHtmlAttrId();
		$attr .= $this->makeHtmlAttrName();
		$attr .= $this->makeAttrib($moreAttr);
		$attr .= $this->makeAttribInput();
		$attr .= $this->buildStyles();
		$attr .= $this->makeEvents($moreEvents);
		return $this->outControl("{$this->htmlLabel()}<input$attr type='$tp' value='$value' />");
		//<div class="valid-feedback"></div>
	}
	public function outControl($html) {
		$help = $this->help;
		$help = $help ? "<div class='help-block'>$help</div>" : '';
		$style = [];
		if ($v = $this->width) $style[] = "width: $v;";
		if ($v = $this->height) $style[] = "height: $v;";
		$style = $style ? ' style ="' . implode('', $style) . '"' : '';

		return "<span class='feedback'$style>$html$help<div class='feedback-message'></div></span>";
	}
	public function contentHidden() {
		$isEdit = $this->isEdit();
		$value = $this->OutHtml->htmlSlashes($this->inputformat ? $this->format($this->inputformat) : $this->value);
		$attr = $this->makeHtmlAttrId();
		$attr .= $this->makeHtmlAttrName();
		if ($isEdit) {
			$attr .= $this->makeAttrib();
			$attr .= $this->makeAttribInput();
			return "<input{$attr} type='hidden' value='$value' />"; //hidden
		}
		$attr .= $this->makeAttrib(array('class' => 'hide'));
		return "<span {$attr}>$value</span>";
	}
	public function makeShowOut($out = '') {
		if ($out) return "<span{$this->makeHtmlAttrId($this->preIdMain)} value='{$this->id}' class='{$this->makeClass()}'>$out</span>";
		else return '';
	}
	public function makeVariables() {
		$this->variables = [];
		$lst = array('protect', 'events', 'displayAttr', 'inputAttr');
		foreach ($lst as $var) foreach ($this->$var as $k => $v) $this->variables[$k] = $var;
	}
	public function makeAttrib($ext = []) {
		$ret = '';
		$ar = $this->displayAttr;
		$ext = array_merge($this->attr, $ext);
		//if ($ext) show($ext);
		//foreach ($ext as $k=>$v) if (!isset($ar[$k])) $ar[$k]=$v;
		$ret = [];
		foreach ($ext as $k => $v) $ret[] = " $k='{$this->OutHtml->htmlSpecial($v)}'";
		foreach ($ar as $k => $v) {
			$v = $this->$k;
			if (!is_null($v)) $ret[] = " $k='{$this->OutHtml->htmlSpecial($v)}'";
		}
		return implode('', $ret);
	}
	public function makeAttribInput($ext = []) {
		$ret = '';
		$ar = $this->inputAttr;
		foreach ($ext as $k => $v) $ar[$k] = $v;
		//foreach ($ext as $k=>$v) if (!isset($ar[$k])) $ar[$k]=$v;
		foreach ($ar as $k => $v) {
			$v = $this->$k;
			if (!is_null($v)) $ret .= " $k='{$this->OutHtml->htmlSpecial($v)}'";
		}
		return $ret;
	}
	public function makeEvents($ext = []) {
		$ret = '';
		$ar = $this->events;
		foreach ($ext as $k => $v) $ar[$k] = $v . (@$ar[$k] ? ";{$ar[$k]}" : "");
		foreach ($ar as $k => $v) if ($v) {
			$v = str_replace('"', '\"', is_array($v) ? implode(';', $v) : $v);
			$ret .= " $k=\"$v\"";
		}
		return $ret;
	}
	public function makeClass() {
		$class = $this->class ? " {$this->class}" : '';
		if ($this->isEdit()) {
			$class .= $this->validate ? ' valid' : '';
			if ($this->type != 'check') {
				$class .= !$this->readonly && ($this->required || $this->validate === '') ? ' required' : '';
				$class .= $this->readonly ? ' readonly' : '';
				$class .= $this->disabled ? ' disabled' : '';
			}
		}
		return $class;
	}
	public function makeHtmlAttrId($prefix = '', $sufix = '') {
		if (!$prefix) $prefix = $this->preIdDisplay;
		return $this->id === '' ? '' : " id='{$this->makeAttrId($prefix,$sufix)}'";
	}
	public function getDisplayId($prefix = '', $sufix = '') {
		return $this->makeAttrId($prefix == '' ? $this->preIdDisplay : $prefix, $sufix);
	}
	public function makeAttrId($prefix = 'fld_', $sufix = '') {
		return "$prefix{$this->OutHtml->htmlSlashes($this->id)}$sufix";
	}
	public function makeHtmlAttrName($pos = '') {
		return ' name=\'' . $this->OutHtml->htmlSlashes($this->name) . $pos . '\'';
	}
	public function makeOptions($source, $value = null) {
		$out = [];
		if (is_null($value) || $value === false) $value = [];
		else $value = (array)$value;
		//show($value);
		//$value=@array_flip($value);
		//show($source);

		foreach ($source as $k => $v) $out[] = "<option value='$k'" . (in_array($k, $value) ? ' selected' : '') . ">$v</option>";
		return $out;
	}
	public function makeHref($value) {
		if ($h = $this->href) {
			$target = ($t = $this->target) ? " target='{$t}'" : '';
			return "<a href='{$h}'$target>$value</a>";
		}
		return $value;
	}

	protected function makeAutoWidth($maxWidth = 0) {
		if (!$this->autoWidth || $this->width) return;
		if (!$maxWidth) $maxWidth = 10;
		$this->width = $maxWidth >= 120 ? '100%' : (ceil($maxWidth / 2) + 3) . 'em';
	}
	protected function buildOpitonHtml($source, $key, &$maxWidth = 0) {
		//($value=null,$fieldName='',$fields=[],$obj=null){
		$opt = $value = array_key_exists($key, $source) ? $source[$key] : $key;
		$fn = $this->fn; //fnOption
		if (is_array($value)) {
			if ($groupSource = $this->groupSource) {
				$out = [];
				foreach ($groupSource as $fld) if (array_key_exists($fld, $value)) $out[] = $value[$fld];
				if ($out) $value = $out;
			} else {
				$k = current($value);
				if ($k == $key && count($opt) > 1) array_shift($value);
			}
			$value = implode($this->separator, $value);
			if ($fn) {
				$fieldName = key($opt);
				$value = call_user_func($fn, $value, $fieldName, $opt, $this, $source);
			}
		} elseif ($fn) {
			$value = call_user_func($fn, $value, $key, array($key, $value), $this, $source);
		}
		//show($opt);
		$maxWidth = max($maxWidth, strlen(strip_tags($value)));
		return $value;
	}
	public function buildSqlListArray($sql = '', $key = [], $fields = [], $separador = ',', $edit = false) {
		$source = $this->source;
		if ($this->isDbSource($source)) {
			$conn = $source->conn;
			if (!$sql) $sql = $this->view;
		} else {
			if (!($conn = $this->conn)) return 'Conn não passado<br>';
			if (!$sql) $sql = $this->sql;
		}
		$res = $this->query($sql, $conn);
		$oF = $res->fetch_fields();
		$allNames = [];
		foreach ($oF as $v) if ($v->name != '__FIELDTMP__') $allNames[$v->name] = $v->name;
		if (!$key) $key = @key($allNames);
		if ($fields) {
			foreach ($fields as $k => $v) if (!isset($allNames[$v])) unset($fields[$k]);
		} else {
			@array_shift($allNames);
			$fields = $allNames;
		}
		$name = $this->OutHtml->htmlSlashes($this->name);
		$events = $this->makeEvents();
		$attr = $this->makeAttrib() . $this->makeAttribInput();
		$out = [];
		if ($key && $fields) while ($line = $res->fetch_assoc()) {
			$o = [];
			foreach ($fields as $k => $v) if (array_key_exists($v, $line)) $o[] = $line[$v];
			$value = $line[$key];
			$text = $this->func(implode($separador, $o), $key, $line); //,'fnOption'
			$check = $line['__FIELDTMP__'] ? ' checked' : '';
			if ($edit) {
				$id = $this->makeAttrId('', "_$value");
				$out[$value] = "<label for='$id'><input type='checkbox' value='$value' id='$id' name='{$name}[]'$attr$events$check /> $text</label>";
			} else $out[$value] = $text;
		}
		else $out[] = 'Key ou Fields não passado<br>';
		$this->hash == md5(implode(",", array_keys($out)));
		$res->close();
		return $out;
	}
	public function buildIdDisplay($sufix = '') {
		return $this->makeAttrId($this->preIdDisplay, $sufix);
	}
	public function buildStyles($styles = []) {
		$a = ['width', 'height', 'text-align'];
		$out = [];
		foreach ($a as $k) {
			if (($v = $this->$k) != '') $out[$k] = "$k:$v";
			//if (!isset($this->variables[$k])) continue;
			//$group=$this->variables[$k];
			//$group=$this->$group;
			//if (!is_null($group[$k]) && $group[$k]!=='' && $group[$k]!==false) $out[$k]="$k:{$group[$k]}";
		}
		if ($s = $this->style) {
			if (is_array($s)) {
				foreach ($s as $k => $v) {
					if (is_numeric($k)) $k = preg_replace('/^\s*([-\w]*)\s*\:.*$/m', '', $v);
					else $v = "$k:$v";
					$out[$k] = $v;
				}
			} else $out[] = $s;
		}
		if ($styles) {
			if (is_array($styles)) {
				foreach ($styles as $k => $v) {
					if (is_numeric($k)) $k = preg_replace('/^\s*([-\w]*)\s*\:.*$/m', '', $v);
					else $v = "$k:$v";
					$out[$k] = $v;
				}
			} else $out[] = $styles;
		}
		if ($out) return " style='" . implode("; ", $out) . "'";
		return '';
	}
	public function buildOptionsValues($source, $value = null) {
		$out = [];
		if (is_null($value)) $value = '';
		$value = @array_flip((array)$value);
		foreach ($value as $k => $v) $out[] = $this->func(array_key_exists($k, $source) ? $source[$k] : $v, $k, $source); //,'fnOption'
		return implode(',', $out);
	}

	public function start() { /*Start Form*/
		if ($this->started) return false;
		return $this->started = true;
	}
	public function rebuildSQL($sql) {
		$sql = trim($sql);
		$order = ($order = $this->Order) || ($order = $this->order) ? ' ORDER BY ' . $order : '';
		return preg_match('/\s/', $sql) ? $sql : ('SELECT * FROM ' . $sql . $order);
	}
	public function validadeCmd($cmd) { //TODO
		/*if (!$cmd) return;
		$m = MediatorPHPJS::singleton();
		$m->setIdTrace(2);
		$m->setSession('cmd', $cmd);
		$this->idCmd = $m->buildIdTrace();
		$a = $this->validate ? preg_split('/[;,]/', $this->validate) : [];
		$a[] = 'validadeCmd';
		$this->validate = implode(';', $a);*/
	}
	public function attr($attr, $value = null, $force = false) {
		if (is_null($value) && !$force) return @$this->displayAttr[$attr];
		$this->displayAttr[$attr] = $value;
		return $this;
	}
	public function attrs(array $attr) {
		foreach ($attr as $k => $v) $this->attr($k, $v, true);
	}
	public function removeAttr($attr) {
		if (array_key_exists($attr, $this->displayAttr)) unset($this->displayAttr[$attr]);
		return $this;
	}
	public function addClass($class, $k = 'class') {
		if ($class) {
			$c = $this->attr($k);
			if (preg_match($er = '/ +[^ ]+-(default|primary|success|info|warning|danger|link) +/', ' ' . $class . ' ', $ret)) $c = trim(preg_replace($er, ' ', ' ' . $c . ' '));
			$c .= ($c ? ' ' : '') . $class;
			$this->attr($k, $c);
		}
		return $this;
	}
	public function removeClass($class) {
		$c = ' ' . $this->attr('class') . ' ';
		$this->attr('class', trim(preg_replace('/ ' . preg_quote($class, '/') . ' /', '', $c)));
		return $this;
	}
	public function isEdit2() { //FIXME deprecieted
		return !is_null($this->editForce) ? $this->editForce : $this->edit;
		//return $this->edit || $this->OutHtml->edit;
	}
	public function isEdit() {
		//return $this->edit || $this->OutHtml->edit;
		if ($this->edit === false) return false;
		if (($form = $this->form)) return $form->isActionUpdate() || $form->isActionInsert();
		return !is_null($this->editForce) ? $this->editForce : $this->edit;
	}
	public function phpValue2jsValue($value) {
		if (is_null($value)) return "null";
		if (is_numeric($value)) return $value;
		if (is_bool($value)) return $value ? "true" : "false";
		if (is_array($value)) {
			$ret = [];
			if (array_keys($value) === range(0, count($value) - 1)) {
				foreach ($value as $v) $ret[] = $this->phpValue2jsValue($v);
				return '[' . implode(",", $ret) . ']';
			} else {
				foreach ($value as $k => $v) $ret[] = "'" . str_replace("'", "\\'", $k) . "':" . $this->phpValue2jsValue($v);
				return "{\r\n" . implode(",\r\n", $ret) . "\r\n}";
			}
		}
		return "'{$this->OutHtml->htmlSlashes($value)}'";
	}
	public function func_old($value, $field = false, $group = false, $func = 'fn') {
		$fn = $this->$func;
		if (!$fn) return $value;
		//show(array($fn,$value,$field,$group));
		return call_user_func($fn, $value, $field, $group, $this);
	}
	public function func($value, $field = false, $group = false, $fn = null) {
		if (!$fn) {
			if ($this->fn) $fn = $this->fn;
			elseif ($this->function) $fn = $this->function;
			else return htmlspecialchars($value);
			//htmlentities($value);

		}
		//show(array($fn,$value,$field,$group));
		return call_user_func($fn, $value, $field, $group, $this);
	}
	public function getFormVarFunc(&$fieldName = '', &$group = []) {
		if ($this->form) {
			$fieldName = $this->{'ed-form-fieldname'};
			$group = $this->form->line;
		} else $fieldName = $this->name;
	}
	public function format($format = '', $value = false, $type = false) {
		if ($type === false) $type = $this->type;
		if ($value === false) $value = $this->value;
		if ($type == 'cpfcnpj') {
			$value = preg_replace('/\D/', '', $value);
			$type = strlen($value) <= 11 ? 'cpf' : 'cnpj';
		}
		switch ($type) {
			case 'cnpj':
				//$v=str_pad(preg_replace('/\D/','',$value),14,'0');
				$v = preg_replace('/\D/', '', $value);
				if (preg_match("/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/", $v, $ret)) $value = "{$ret[1]}.{$ret[2]}.{$ret[3]}/{$ret[4]}-{$ret[5]}";
				else $value = $this->formatError($value);
				break;
			case 'cpf':
				//$v=str_pad(preg_replace('/\D/','',$value),11,'0');
				$v = preg_replace('/\D/', '', $value);
				if (preg_match('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', $v, $ret)) $value = "{$ret[1]}.{$ret[2]}.{$ret[3]}-{$ret[4]}";
				else $value = $this->formatError($value);
				break;
			case 'current':
				if ($format) $value = 'R$' . sprintf($format, $value);
				break;
			case 'number':
			case 'text':
			case 'string':
			case '':
			case 'int':
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'integer':
			case 'bigint':
			case 'float':
			case 'double':
			case 'decimal':
				if ($format) $value = sprintf($format, $value);
				break;
			default:
				break;
		}
		return $value;
	}
	public function formatError($value) {
		return (!$this->edit && !$this->forceEdit) ? "<b style='color:#FF0000;'>$value</b>" : $value;
	}
	public function htmlLabel($dPoint = true, $force = false) {
		if (!$force && (!$this->label || !$this->showLabel)) return '';
		$acceskey = '';
		$label = $this->label;
		if (preg_match('/&(\w)/', $label, $ret)) {
			$acceskey = " accesskey='{$ret[1]}'";
			$this->set('title', $this->title . "[Alt+{$ret[1]}]");
			$label = preg_replace('/&(\w)/', '<span>\1</span>', $label);
		}
		$for = '';
		$dPoint = $dPoint ? ":" : "";
		if ($this->id) $for = " for='{$this->OutHtml->htmlSlashes($this->preIdDisplay .$this->id)}'";
		$r = ($this->required || $this->validate === '') && !$this->auto_increment && $this->isEdit() ? $this->strRequired : '';
		$class = $this->attr('classLabel') . ' ' . $this->OutHtml->baseClass(get_class($this));
		return "<label$for$acceskey class='control-label $class'>$label$dPoint$r&nbsp;</label>";
	}
	protected function buildValueByName($name, $array) {
		if (is_array($name)) {
			$key = key($name);
			if (is_array($name[$key]) && array_key_exists($key, $array)) return $this->buildValueByName($name[$key], $array[$key]);
			return @$array[$key];
		} else {
			parse_str($name, $name);
			return $this->buildValueByName($name, $array);
		}
	}
	public function startEvent($event) {
		$event = @$this->$event;
		if (!$event) return true;
		$this->getFormVarFunc($field, $group);
		$value = $this->value;
		if (is_string($event) || (is_array($event) && is_numeric(key($event)))) $event = array($event);
		foreach ($event as $fn) if (call_user_func($fn, $value, $field, $group, $this) === false) return false;
		return true;
	}

	public function update($data = null) {
		if (!is_array($data) && !is_object($data)) $this->value = $data;
	}
	protected function update_list($values = false) {
		//if($form && (!$form->saved || !$form->sucess)) return true;
		$saveTo = $this->get('saveTo');
		if (!$saveTo || !@$saveTo['table']) return true;
		if ($values === false) $values = $this->value;
		//showme($this->value);

		//Get form properties
		if (($form = $this->form)) {
			$updated = $form->updated;
			$inserted = $form->inserted;
			$deleted = $form->deleted;
			$conn = array_key_exists('conn', $saveTo) ? Conn::dsn($saveTo['conn']) : $form->conn;
			//show($form->key);
		} else {
			$updated = true;
			$deleted = $inserted = false;
			$conn = array_key_exists('conn', $this->target) ? Conn::dsn($this->target['conn']) : $this->conn;
		}

		//show([$inserted,$updated,$deleted]);

		//Get Source e value
		$source = $this->makeSourceList($targetValue);
		if (!@$saveTo['value']) $saveTo['value'] = $targetValue;
		if (!$saveTo['value']) return true;

		//Buld arrays $key,$tmpInitTable,$whereUpd 
		$whereDel = $whereUpd = $key = $tmpInitTable = $tmpTable = [];
		if (array_key_exists('key', $saveTo)) {
			if (is_string($saveTo['key'])) {
				$saveTo['key'] = array_flip(preg_split('/\s*[,;]\s*/', $saveTo['key']));
				foreach ($saveTo['key'] as $k => $v) $saveTo['key'][$k] = $k;
			}
			if ($form) {
				foreach ($saveTo['key'] as $field => $v) {
					if (array_key_exists($v, $form->key)) $v = $form->key[$v];
					elseif (array_key_exists($v, $form->fields)) $v = $form->fields[$v]->value;
					elseif (!@$saveTo['primitiveKeys']) {
						if (array_key_exists($field, $form->key)) $v = $form->key[$field];
						elseif (array_key_exists($field, $form->fields)) $v = $form->fields[$field]->value;
					}

					$whereDel[] = 't.' . $conn->fieldCompareValue($field, $v);
					$this->update_list_addPrepare($conn, $field, $v, $key, $tmpInitTable, $whereUpd);
				}
			} else foreach ($saveTo['key'] as $field => $v) {
				$whereDel[] = 't.' . $conn->fieldCompareValue($field, $v);
				$this->update_list_addPrepare($conn, $field, $v, $key, $tmpInitTable, $whereUpd);
			}
		}
		//show($values);
		//Delete if have to do
		if ($deleted || !$values) {
			$sql = 'DELETE t.* FROM ' . $saveTo['table'] . ' t' . ($whereDel ? ' WHERE ' . implode(' AND ', $whereDel) : '');
			//show($sql);
			$conn->query($sql);
			return true;
		}

		//Buld arrays $key,$tmpTable,$whereUpd 
		$this->update_list_addPrepareRaw($conn, $saveTo['value'], '$value', $key, $tmpTable, $whereUpd);
		if (array_key_exists('order', $saveTo)) $this->update_list_addPrepareRaw($conn, $saveTo['order'], '$seq', $key, $tmpTable, $whereUpd);

		if (@$saveTo['fields']) foreach ($saveTo['fields'] as $field => $v) if (!array_key_exists($field, $key)) {
			$this->update_list_addPrepareRaw($conn, $field, $v, $key, $tmpTable);
		}

		//Create tmpTable
		$tmp = [];
		foreach ($values as $seq => $value) {
			$line = $tmpInitTable;
			foreach ($tmpTable as $field => $fieldMap) {
				$ret = $conn->addQuote($this->update_list_eval($fieldMap, $source, $values, $seq, $value));
				$line[] = $ret . ' as ' . $field;
			}
			$tmp[] = 'SELECT ' . implode(', ', $line);
		}

		$tmpName = 'tmp_' . $this->OutHtml->baseClass(__CLASS__) . '_' . time();
		$sql = 'CREATE TEMPORARY TABLE ' . $tmpName . " \n" . implode(" UNION ALL \n", $tmp);
		//show($sql);
		$conn->query($sql);

		if ($updated) {
			$whereDel[] = 's.' . $conn->fieldDelimiter($saveTo['value']) . ' IS NULL';
			$sql = '
			DELETE t.* FROM ' . $saveTo['table'] . ' t 
			LEFT JOIN ' . $tmpName . ' s ON ' . implode(' AND ', $whereUpd) . '
			WHERE ' . implode(' AND ', $whereDel);
			//show($sql);
			$conn->query($sql);
		}
		if ($updated || $inserted) {
			$sql = 'INSERT IGNORE ' . $saveTo['table'] . ' (' . implode(',', $key) . ') SELECT * FROM ' . $tmpName; //show($sql);
			$conn->query($sql);
		}
		$sql = 'DROP TABLE IF EXISTS ' . $tmpName; //show($sql);
		$conn->query($sql);
		//show($conn->query_all('SELECT * FROM db_URA.tb_Extensions_Files t WHERE t.`idEx`>=1940'));
		return true;
	}
	protected function update_list_eval($fieldMap, $source, $values, $seq, $value) {
		if (is_string($fieldMap) && $fieldMap) {
			$field = (array)@$source[$value];
			$html = $this->buildOpitonHtml($source, $value);
			return eval('return ' . $fieldMap . ';');
		}
		return $fieldMap;
	}
	protected function update_list_addPrepare($conn, $field, $value, &$key = [], &$tmpTable = [], &$whereUpd = []) {
		return $this->update_list_addPrepareRaw($conn, $field, $conn->addQuote($value) . ' as ' . $conn->fieldDelimiter($field), $key, $tmpTable, $whereUpd);
	}
	protected function update_list_addPrepareRaw($conn, $field, $value, &$key = [], &$tmpTable = [], &$whereUpd = []) {
		$fieldQuote = $conn->fieldDelimiter($field);
		$whereUpd[$field] = 's.' . $fieldQuote . '=t.' . $fieldQuote;
		$key[$field] = $fieldQuote;
		$tmpTable[$fieldQuote] = $value;
		return $value;
	}
	protected function update_list_delete($conn, $target, $whereDel) {
		$sql = 'DELETE FROM ' . $target['table'] . ' t' . ($whereDel ? ' WHERE ' . implode(' AND ', $whereDel) : '');
		//show($sql);
		$conn->query($sql);
		return true;
	}

	public function query($sql, $conn = false) {
		if ($this->debug) print '<pre>SQL: ' . $sql . '</pre>';
		if (!$conn) $conn = $this->conn;
		return $conn->query($sql);
	}
	public function pr($texto) {
		if ($this->debug) {
			$label = '<b>' . get_class($this) . "</b>\n";;
			if (preg_match('/^(\t| )+/', $texto, $ret)) $texto = preg_replace(array('/^(\t| )+/', "(\n|\r){$ret[0]}"), array('', '\1'), $texto);
			print "<hr><pre style='font-size:small;'>$label" . print_r($texto, true) . '</pre>';
		}
	}

	public function check_match($value, $pattern) {
		return $value == $pattern;
	}
	public function check_in($value, $pattern) {
		if (is_object($pattern)) $pattern = (array)$pattern;
		if (is_array($pattern)) return in_array($value, $pattern);
		return $this->check_match($value, $pattern);
	}
	public function check_key($value, $pattern) {
		if (is_object($pattern)) $pattern = (array)$pattern;
		if (is_array($pattern)) return array_key_exists($value, $pattern);
		return $this->check_match($value, $pattern);
	}
	public function check_regexp($value, $pattern) {
		return preg_match($pattern, $value);
	}
	public function check_glob($value, $pattern) {
		return fnmatch($pattern, $value);
	}
	public function check_smart($value, $pattern) {
		$pattern = '/' . preg_replace('/\s+/', '|', preg_quote($pattern, '/')) . '/i';
		return $this->check_regexp($value, $pattern);
	}
	public function makeEdElement($class) {
		$class = preg_replace('/^Element/', '', $class);
		$class = strtolower($class[0]) . substr($class, 1);
		return $class ? $class : 'string';
	}
	public function addHeader() {
		OutHtml::singleton()->headScript['modal_file'] = "$.Element.modal_file='{$this->config->ed['fn']}/modal.html';";
	}
	protected function isDbSource(&$source) {
		if (
			is_array($source) &&
			array_key_exists('view', $source) &&
			!preg_grep('/^(view|conn|label|order|key|getCells|fields|fn|funct(ion)?)$/', array_keys($source), PREG_GREP_INVERT)
		) {
			$this->getConn($source['conn']);
		} elseif ($this->conn && $this->sql) {
			$source = array(
				'conn' => $this->conn,
				'view' => $this->sql,
				'fields' => $this->fields,
				'order' => $this->order,
			);
		} else return false;
		$source = (object)$source;
		return true;
	}

	static function parser($name, $obj) {
		if (is_string($obj)) {
			//if(preg_match('/^\s*\{(.|\s)*\}\s*$/',$obj) $obj=json_decode($obj);
			if (preg_match('/^(Element)\w*$/', $obj)) return new $obj($name);
		}
		if (is_array($obj)) {
			if (array_key_exists('Element', $obj)) {
				$class = $obj['Element'];
				unset($obj['Element']);
			} else $class = 'ElementString';
			$vals = $obj;
			$obj = new $class($name);
			foreach ($vals as $k => $v) $obj->$k = $v;
			return $obj;
		} elseif (is_numeric($obj)) return new ElementNumber($name, $obj);
		elseif (is_bool($obj))    return new ElementCheck($name, $obj);
		elseif (!is_object($obj)) return new ElementString($name, $obj);
		return $obj;
	}
}
