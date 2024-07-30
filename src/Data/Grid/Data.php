<?php

/**
 * Page-Level DocBlock Data
 * @package Easy
 * @subpackage Screen
 * @category Tools
 * @author Helbert Fernandes <helbertfernandes@yahoo.com.br>
 * @version 1.1 em 03/10/2006 15:00 - Helbert Fernandes
 */
/**
 * Classe Pai Data para manipulação de conjunto de dados
 *
 * @see new DataTab
 * @see new DataHtml
 * @see new DataList
 */
class Data extends OverLoadElements {
	protected $protect = array(
		'id' => null,
		'idHash' => null,
		'idSession' => null,
		'label' => null,
		'conn' => null,
		'view' => null,
		'outFormat' => null,
		'idHelp' => null,
		'refresh' => null,
		'showLabel' => null,
		'forceShowLabel' => null,
		'style' => null, //Estilos a mais, Verificar como ira funcionar
	);
	protected $varsCookied = array();
	protected $varsSessioned = array();
	protected $session, $oS;
	public $mediator, $outHtml;

	function __construct($label = false) {
		$this->setId();
		$this->label = $label;

		$this->outHtml = OutHtml::singleton();
		$this->mediator = MediatorPHPJS::singleton();
		$this->oS = SessControl::singleton($this->idSession);
		$this->session = &$this->oS->sess;
	}
	function __toString() {
		$id = $this->id;
		foreach ($this->varsCookied as $k) $this->protect[$k] = $this->mediator->{"{$this->protect['idHash']}_$k"};
		if (is_array(@$_REQUEST[$id])) {
			if (@$_REQUEST[$id]['reset']) {
				$this->oS->destroy_id();
				unset($_REQUEST[$id]['reset']);
			} else {
				foreach ($_REQUEST[$id] as $k => $v) $this->$k = $v;
				foreach ($this->varsSessioned as $k) if (array_key_exists($k, $_REQUEST[$id])) $this->session[$k] = $this->$k;
			}
		}
		foreach ($this->varsSessioned as $k) $this->protect[$k] = $this->session[$k];

		$class = get_class($this);
		$this->outHtml->script(__CLASS__, 'easyData');
		$this->outHtml->style(__CLASS__, 'easyData');
		$this->outHtml->script($class, 'easyData');
		$this->outHtml->style($class, 'easyData');
		$style = $this->style;
		if ($style) $this->outHtml->head["style_$id"] = "\t<style id='{$id}_style'>{$style}</style>";

		$this->outFormat = @$_REQUEST['__eD']['outFormat'];
		$this->outFormat->headScript[$id] = "$id=new {$class}('$this->idHash')";

		if (!isset($this->outHtml->prebody['form'])) {
			$this->outHtml->addPreBody('<form method="post">', 'form');
			$this->outHtml->addPosBody('</form>', 'form');
		}
		return '';
	}
	function __destruct() {
		foreach ($this->varsSessioned as $k) $this->session[$k] = $this->$k;

		$this->mediator->{"{$this->protect['idHash']}_sessid"} = $this->oS->id;
		$this->mediator->{"{$this->protect['idHash']}_id"} = $this->id;
		foreach ($this->varsCookied as $k) $this->mediator->{"{$this->protect['idHash']}_$k"} = $this->$k;
	}
	function set($nm, $val) {
		$this->protect[$nm] = $val;
	}
	function getDsn() {
		return $this->protect['conn'];
	}
	function setId($val = false) {
		$oId = Id::singleton();
		$this->protect['id'] = $val ? $val : $oId->id;
		$this->protect['idSession'] = $oId->getRootId() . ':' . $this->protect['id'];
		$this->protect['idHash'] = md5($this->protect['idSession']);
	}
	function setLabel($val) {
		$this->protect['label'] = $val ? $val : $this->protect['id'];
	}
	function setIdHelp($val) {
		$help = Help::singleton();
		$help->id = $val;
		$this->protect['idHelp'] = $val;
	}
	function setRefresh($val) {
		if ($val) {
			if ($val === true) $val = 0;
			else $val += 0;
			$rfr = Refresh::singleton();
			$this->outHtml->script['refresh'] = "fRfr($val)";
		} else $val = null;
		$this->protect['refresh'] = $val;
	}
	function setDsn($val) {
		$this->setConn($val);
	}
	function setConn($val) {
		if (!$val) return;
		$this->protect['conn'] = is_string($val) ? Conn::dsn($val) : Conn::singleton($val);
	}
	function setSql($val) {
		$this->setView($val);
	}
	function setView($val) {
		if (!$val) return;
		$this->protect['view'] = $val;
	}
	function setOutFormat($val) {
		static $formats = array('xls', 'xlsAll');
		if (!$val || !in_array($val, $formats)) return;
		$this->protect['outFormat'] = $val;
	}

	function convert2Bool($v) {
		$v_lower = strtolower($v);
		return !$v || $v == '' || $v == '0' || $v_lower == 'off' || $v_lower == 'false' || $v_lower == 'falso' || $v_lower == 'desligado' ? false : true;
	}
	function convert2String($v, $separator = ',', $relation = '=', $hasDad = false) {
		if ($hasDad) {
			$ini = '(';
			$fim = ')';
		} else $ini = $fim = '';
		if (is_string($v)) return $v;
		if (is_bool($v)) return $v ? 'On' : 'Off';
		if (is_null($v)) return 'Null';
		if (is_numeric($v)) return $v . '';
		if (is_object($v)) {
			$tmp = array();
			foreach ($v as $k => $value) $tmp = $k . $relation . $this->convert2String($value, $separator, $relation);
			$v = $tmp;
		}
		if (is_array($v)) return $ini . implode($separator, $v) . $fim;
	}
	function convert2Array($v) {
		if (is_array($v)) return $v;
		if (is_object($v) || is_bool($v) || is_numeric($v) || is_null($v)) return (array)$v;
		if (is_string($v)) return preg_split('/\s*[,;]\s*/', $v);
	}

	### Desnecessário Inicio ###
	function addSession($nm, $val) { //setSession
		$this->session[$nm] = $val;
	}
	function returnPrivate($var = 'session') {
		return $this->$var;
	}
	function query($sql) {
		$conn = $this->conn;
		$res = $conn->query($sql);
		if ($conn->error) die("SQL: $sql\n\n{$conn->error}\n");
		return $res;
	}
	function jsValue($value = '', $tipo = false) {
		if (!$tipo) $tipo = gettype($value);
		switch ($tipo) {
			case 'boolean':
				return $value ? "true" : "false";
			case 'integer':
			case 'double':
				return $value;
			case 'string':
				return "'" . addcslashes($value, "\0..\31\"'") . "'";
			case 'NULL':
				return 'null';
		}
		return "''";
	}
	### Desnecessário Fim ###
}
