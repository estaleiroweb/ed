<?php
namespace EstaleiroWeb\ED\Data\Form;

use EstaleiroWeb\ED\Data\Element\ElementButton;
use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\DB\Tools\EasyView;
use EstaleiroWeb\ED\Ext\Bootstrap;
use EstaleiroWeb\ED\Ext\Ed;
use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\ED\Secure\Secure;
use EstaleiroWeb\ED\Tools\Id;

/**
 * @author Helbert Fernandes <helbert.fernandes@inteligtelecom.com.br>
 */
class Form {
	public $res, $where, $oldWhere, $whereFields, $hash, $line, $oS;
	public $printed = [];
	public $fields = [];
	public $OutHtml;
	public $messageTransp = '';
	public $showAction = true;
	public $viewAfterEdit = true;
	public $debug = false;
	public $formTable = [];
	public $onstart = [];
	public $onbeforestart = [];
	//public $onafterstart=[];
	public $onbeforeinsert = [];
	public $onafterinsert = [];
	public $onafternotinsert = [];
	public $onbeforedelete = [];
	public $onafterdelete = [];
	public $onafternotdelete = [];
	public $onbeforeupdate = [];
	public $onafterupdate = [];
	public $onafternotupdate = [];
	public $onaftersucess = [];
	public $unhashFields = [];
	public $whereUnash = [];
	public $sucess = false;
	public $rebuildId = false;
	public $sql = '';
	public $forceIncrementField = '';
	public $mngTables = ''; //No caso de view conplexa, quais tabelas serão inseridas/deletadas [default todas]
	public $ev;
	private $detailsFields = [];
	private $tmp, $dbVersion, $tblCore, $allWhere;
	private $objList = false;
	private $actionCmd = 'simple';
	private $started = false, $finshed = false;
	private $externalUpdates = [];
	private $relationFields = []; //aliasTableTarget.fieldTarget=[aliasSoruce.]fieldSource
	private $moreDetailsTbls = [];
	private $allTrKey = [];
	private $formAction = '';
	private $counters = [];
	private $creatFields = true;
	public $defaultButtons = true;
	public $found = true;
	public $trErrorValues = []; //Veja Class Form_CodeErrors
	public $trError = 1; //0: desligado, 1: apenas default, 2: apenas tradução, 3: ambos
	public $buttons = [];
	public $html = '';
	public $htmlBeforeContainer = '';
	public $isToString = true;
	public $showButtons = true;
	private $protected = [
		'id' => null,
		'tbl' => '', 'action' => null, 'oldAction' => '', 'mess' => '', 'onsubmit' => '',
		'key' => [], 'auto_increment' => '', 'nav' => '', 'title' => '', 'db' => '',
		'isEdit' => 1, 'isInsert' => 1, 'isDelete' => 1, 'isSave' => 1, 'isExecute' => 1,
		'CRUDS' => 31,
		'isNav' => 1, 'isMss' => 1,
		'saved' => false, 'inserted' => false, 'updated' => false, 'deleted' => false,
		'conn' => '',
	];

	public function __construct($id = false) {
		$oId = Id::singleton($id);
		$this->id = $id = $oId->id;
		$outHtml = OutHtml::singleton();
		new Ed();
		new Bootstrap();
		$outHtml->script(__CLASS__, 'easyData')->style(__CLASS__, 'easyData');
		//$outHtml->style($class,'easyData');
		//$outHtml->headScript[]="window.{$id}=new $class('{$id}');";
		//print "MANUTENÇÃO: <pre>".print_r($this->oldWhere,true)."</pre>";
		if (preg_grep('/^Secure$/', get_declared_classes()) && $s = Secure::$obj) {
			if ($s->access && $s->access['Nivel']) $this->protected['CRUDS'] = $s->access['CRUDS'];
		}
	}
	public function __toString() {
		if (!$this->isToString) return '';
		if (!$this->found && !$this->C) return '';
		//show(['aCRUDS'=>$this->bCRUDS($this->protected['action']),'fCRUDS'=>$this->bCRUDS($this->protected['CRUDS']),'found'=>$this->found]);
		$mss = $this->isMss && $this->mess ? $this->mess : '';
		return '<div data-element="form" ed-Class="EForm" ed-form-id="' . $this->id . '">' . $this->makeButtons() . '<div ed-method="mess">' . $mss . '</div></div>';
	}
	public function __invoke($html = '', $print = true) { //TODO replace startForm
		if (!$this->showAction) return;

		if ($this->isActionEdit()) {
			//new JQuery_Validate; //FIXME
			//$outHtml->script('validateform','easyData');
			$this->objList = true;
			//$submit=$this->protected['onsubmit']?" onsubmit='{$this->protected['onsubmit']}'":'';
			//$validate=' ed-action="validate"';
		} //else $validate='';
		$p = $this->isActionEdit() | $this->isActionDelete();
		$tab = "\t";
		$iniTab = $tab;
		//$outHtml->addFormTag($this->formAction," conn='{$this->conn}'");
		$out = "$iniTab<form id='frm_{$this->id}' action='{$this->formAction}' ed-form-id='{$this->id}' role='form' method='post' enctype='multipart/form-data'>\n";
		$out .= "$iniTab$tab<input type='hidden' ed-form-id='{$this->id}' name='frm_action_{$this->id}' id='frm_action_{$this->id}' value='$p' />\n";
		$out .= "$iniTab$tab<input type='hidden' ed-form-id='{$this->id}' name='frm_hash_{$this->id}' value='{$this->hash}' />\n";
		$out .= "$iniTab$tab<input type='hidden' ed-form-id='{$this->id}' name='frm_oldWhere_{$this->id}' value='" . htmlspecialchars($this->where, ENT_QUOTES) . "' />\n";
		if ($this->protected['key']) {
			foreach ($this->protected['key'] as $k => $v) {
				if ($k != $this->protected['auto_increment']) {
					//$out .= "$iniTab$tab<input type='hidden' ed-form-id='{$this->id}' name='{$this->id}[$k]' value='$v' />\n";
					$out .= "{$this->fields[$k]}";
				}
			}

			if ($this->isActionInsert()) {
				if (array_key_exists($this->protected['auto_increment'], $this->protected['key'])) {
					$this->protected['key'][$this->protected['auto_increment']] = 0;
					$out .= "{$this->fields[$k]}";
				}
			} elseif (array_key_exists($this->protected['auto_increment'], $this->protected['key'])) {
				//$out .= "$iniTab$tab<input type='hidde' ed-form-id='{$this->id}' name='{$this->id}[{$this->protected['auto_increment']}]' value='{$this->protected['key'][$this->protected['auto_increment']]}' />\n";
				$out .= "{$this->fields[$this->protected['auto_increment']]}";
			}
		}

		$out .= $html;
		//$outHtml->addBody($this->getHtmlKeys().$this->elementHash(),'keys');
		$out .= $this->__toString();
		$out .= "$iniTab</form>\n";
		if ($print) print $out;
		return $out;
	}
	public function __get($nm) {
		if (method_exists($this, $fn = 'get' . ucfirst($nm))) return $this->$fn();
		elseif (array_key_exists($nm, $this->protected)) return $this->protected[$nm];
	}
	public function __set($nm, $val) {
		if (method_exists($this, $fn = 'set' . ucfirst($nm))) return $this->$fn($val);
		elseif (array_key_exists($nm, $this->protected)) $this->protected[$nm] = $val;
		return $this;
	}

	public function getC() {
		return $this->getCRUDS(4);
	}
	public function getR() {
		return $this->getCRUDS(3);
	}
	public function getU() {
		return $this->getCRUDS(2);
	}
	public function getD() {
		return $this->getCRUDS(1);
	}
	public function getS() {
		return $this->getCRUDS(0);
	}
	public function setId($id) {
		$this->protected['id'] = $id;
		$k = "frm_action_{$id}";
		if (array_key_exists($k, $_REQUEST)) {
			$this->action = $_REQUEST[$k];
			$this->hash = @$_REQUEST["frm_hash_{$id}"];
			$this->oldWhere = @$_REQUEST["frm_oldWhere_{$id}"];
		}
	}
	public function setC($val = 1) {
		return $this->rebuildCRUDS($val, 4);
	}
	public function setR($val = 1) {
		return $this->rebuildCRUDS($val, 3);
	}
	public function setU($val = 1) {
		return $this->rebuildCRUDS($val, 2);
	}
	public function setD($val = 1) {
		return $this->rebuildCRUDS($val, 1);
	}
	public function setS($val = 1) {
		return $this->rebuildCRUDS($val, 0);
	}
	public function getIsInsert() {
		return $this->getR() && $this->getC();
	}
	public function getIsEdit() {
		return $this->getR() && $this->getU();
	}
	public function getIsDelete() {
		return $this->getR() && $this->getD();
	}
	public function getIsExecute() {
		return $this->getR() && $this->getS();
	}
	public function setIsInsert($val = 1) { /*$this->setR($val);*/
		return $this->setC($val);
	}
	public function setIsEdit($val = 1) { /*$this->setR($val);*/
		return $this->setU($val);
	}
	public function setIsDelete($val = 1) { /*$this->setR($val);*/
		return $this->setD($val);
	}
	public function setIsExecute($val = 1) { /*$this->setR($val);*/
		return $this->setS($val);
	}
	public function getCRUDS($l = null) {
		if (is_null($l)) return $this->protected['CRUDS'];
		$bit = 1 << $l;
		return $this->protected['CRUDS'] & $bit;
	}
	public function setCRUDS($val) {
		$val = (int) $val;
		$p = Secure::$obj ? Secure::$obj->access['pCRUDS'] : $val;
		$this->protected['CRUDS'] = $val & $p;
		return $this;
	}
	public function getConn() {
		if (!is_object($this->protected['conn'])) $this->setConn($this->protected['conn']);
		return $this->protected['conn'];
	}
	public function getDataBase() {
		return $this->protected['db'];
	}
	public function getDetailsFields() {
		$out = [];
		foreach ($this->detailsFields as $fieldName => $f) $out[$fieldName] = $this->getDetailsField($fieldName);
		return $out;
	}
	public function getHtmlKeys() {
		$out = '';
		if (!$this->protected['key'] || $this->isActionInsert()) {
			if (isset($this->protected['key'][$this->protected['auto_increment']])) $this->protected['key'][$this->protected['auto_increment']] = 0;
			return '';
		}
		if (isset($this->protected['key'][$this->protected['auto_increment']])) $out .= "<input type='hidden' name='{$this->id}[{$this->protected['auto_increment']}]' value='{$this->protected['key'][$this->protected['auto_increment']]}' />\n";
		//print htmlspecialchars($out)."<br>";
		return $out;
	}
	public function getInsertId() {
		$outHtml = OutHtml::singleton();
		$autoInc = $this->protected['auto_increment'];
		//print "<div>Mantutenção: autoInc=$autoInc</div>";
		if (!$autoInc) return true;
		$conn = $this->getConn();
		$id = $conn->insert_id();
		//print "<div>Mantutenção: id=$id</div>";
		if ($id && isset($this->protected['key'][$autoInc])) {
			$this->formAction = $outHtml->paramUrl($autoInc, $id);
			$this->protected['key'][$autoInc] = $id;
			if (isset($this->fields[$autoInc])) $this->fields[$autoInc]->value = $id;
			$this->line[$autoInc] = $_REQUEST[$autoInc] = $_POST[$autoInc] = $_GET[$autoInc] = $id;
			//$this->setActionUpdate();
		} else {
			return false;
		}
		return $id;
	}
	public function setActionSaving($val = 1, $force = false) {
		return $this->rebuildAction($val, 5, $force);
	}
	public function setActionInsert($val = 1, $force = false) {
		return $this->rebuildAction($val, 4, $force);
	}
	public function setActionView($val = 1, $force = false) {
		return $this->rebuildAction($val, 3, $force);
	}
	public function setActionUpdate($val = 1, $force = false) {
		return $this->rebuildAction($val, 2, $force);
	}
	public function setActionDelete($val = 1, $force = false) {
		return $this->rebuildAction($val, 1, $force);
	}
	public function setTbl($val) {
		$this->protected['tbl'] = $val;
		$this->sql = $this->debugSql();
		return $this;
	}
	public function setView($val) {
		return $this->setTbl($val);
	}
	public function setKey($val) {
		if (!is_array($val)) $val = preg_split('/\s*[,;]\s*/', $val);

		$out = [];
		foreach ($val as $k => $v) {
			if (is_numeric($k)) {
				$name = $v;
				$value = $this->getValueFieldKey($v);
			} else {
				$name = $k;
				$value = $v;
			}
			$out[$name] = $value;
			if ($this->protected['auto_increment'] == $name && $this->isActionInsert()) $out[$name] = 0;
		}
		$this->protected['key'] = $out;
		$this->rebuildAllTrKey();
		//showme($this->protected['key']);
		return $this;
	}
	public function setConn($val) {
		$this->protected['conn'] = $val = Conn::dsn($val);
		$this->selectDb();
		return $this;
	}
	public function setTitle($val) {
		$outHtml = OutHtml::singleton();
		print "<h1>$val</h1>";
		$outHtml->title($val);
		return $this;
	}
	public function setDataBase($val) {
		return $this->setDb($val);
	}
	public function setDb($val) {
		return $this->selectDb($val);
	}
	public function isActionSaving() {
		return $this->getAction(5);
	}
	public function isActionInsert() {
		return $this->getAction(4);
	}
	public function isActionView() {
		($out = $this->getAction(3)) || ($out = !$this->protected['action'] ? 1 << 3 : 0);
		return $out;
	}
	public function isActionUpdate() {
		return $this->getAction(2);
	}
	public function isActionDelete() {
		return $this->getAction(1);
	}
	public function isActionEdit() {
		return $this->isActionUpdate() | $this->isActionInsert();
	}
	public function getAction($l = null) {
		if (is_null($l)) return $this->protected['action'];
		$bit = 1 << $l;
		return $this->protected['action'] & $bit;
	}
	public function setAction($val, $force = false) {
		if (is_null($this->protected['action']) || $force) $this->protected['action'] = (int) $val;
		return $this;
	}
	public function edit() {
		return $this->setActionUpdate();
	}
	private function rebuildAction($val, $l, $force = false) {
		$bit = 1 << $l;
		if ($val === true) {
			$this->protected['action'] = $bit;
			return $this;
		}
		return $this->setAction($val ? $this->protected['action'] | $bit : $this->protected['action'] & ~$bit, $force);
	}
	public function start() {
		if ($this->started) return;
		if (!$this->startEvent('onbeforestart')) return false;
		if (implode('', $this->protected['key']) == '') $this->setActionInsert(1, true);
		if ($this->isActionInsert() && array_key_exists($this->protected['auto_increment'], $this->protected['key'])) {
			$this->protected['key'][$this->protected['auto_increment']] = 0;
		}
		if (!is_array($this->unhashFields)) $this->unhashFields = preg_split('/\s*[,;]\s*/', $this->unhashFields);
		$this->unhashFields = $this->unhashFields ? array_flip($this->unhashFields) : [];
		//$this->sql=$this->debugSql();
		if (!$this->sql || !$this->createWhere()) return;
		$this->started = true;
		if (!$this->oldWhere) $this->oldWhere = $this->where;

		$this->getQuery($this->oldWhere);
		$this->protected['oldAction'] = $this->protected['action'];

		if ($this->verifyAction()) $this->hash = $this->createFields();
		else $this->createFieldsRepeat();
		//verbose($this->line);
		$this->updateForceIncrement();
		$this->startAction();
		//$this->startForm();
		$this->res->close();
		$this->makeExternalUpdatesAction();
		if (!$this->startEvent('onstart')) return false;
		//if ($this->objList) {
		//      $f=$this->fields;
		//      $ids=[];
		//      foreach ($f as $k=>$v) $ids[]=$v->id;
		//      $outHtml->script['objList']="{$this->id}.fields='".implode(',',$ids)."'";
		//}
		return $this;
	}
	public function startEvent($event) {
		if (!$this->startEventObj($event, $this)) return false;
		//foreach ($this->fields as $field => $obj) if (!$this->startEventObj($event, $obj)) return false;
		return true;
	}
	private function startForm() {
		$outHtml = OutHtml::singleton();
		if (!$this->showAction) return;
		if ($this->isActionEdit()) {
			//$submit=$this->protected['onsubmit']?" onsubmit='{$this->protected['onsubmit']}'":'';
			$this->objList = true;
			$validate = ' ed-action="validate"';
		} else $validate = '';
		//$outHtml->addPreBody("<form id='frm_{$this->id}' class='Form' method='post'$submit>\n","form");
		$outHtml->addFormTag($this->formAction, $validate);
		//$outHtml->addPreBody("<form action='{$this->formAction}' role='form' method='post' enctype='multipart/form-data'$validate>\n",'form');
		//$outHtml->addPosBody("</form>\n",'form');
		//$outHtml->addBody("<FORM id='frm_{$this->id}' action='{$this->formAction}' class='Form' METHOD='post' ENCTYPE='multipart/form-data'$submit>\n","formBegin");
		$p = $this->isActionEdit() || $this->isActionDelete();
		$outHtml->addBody("<input type='hidden' name='frm_action_{$this->id}' id='frm_action_{$this->id}' value='$p'>\n", 'form_imputAction' . $this->id);
	}
	public function startAction() {
		//action=0:View, 1:Edit, 2:Insert, 4:Delete, 8:Execute
		//action=16:C, 8:R, 4:U, 2:D, 1:S
		//0|8:View, 1: Edit, 9:Updating, 2|3:Insert, 10:11:Inserting, 4:Delete, 12: Deleting
		if (!$this->protected['action']) $this->setActionView(true);
		$this->protected['action'] &= $this->protected['CRUDS'] + (1 << 5);

		$edit = $this->isActionEdit() ? true : null;
		if ($this->fields) foreach ($this->fields as $fieldName => $obj) {
			//if (is_null($obj->edit))
			//if($edit && is_null($obj->edit)) $obj->edit=$edit;
			$obj->{'ed-form-id'} = $this->id;
			$obj->start();
		}
		return $this;
	}
	public function startEventObj($event, $obj, $key = null) {
		$event = @$obj->$event;
		if (!$event) return true;
		if (is_string($event) || (is_array($event) && is_numeric(key($event)))) $event = array($event);
		foreach ($event as $fn) {
			if ($fn && call_user_func($fn, $obj) === false) return false;
		}
		return true;
	}
	public function getDetailsField($fieldName) {
		$o = @$this->detailsFields[$fieldName];
		if (!$o) return false;
		$out = [];
		foreach ($o as $k => $v) if (!preg_match('/^(conn|form|flags)$/i', $k)) $out[$k] = $v;
		return $out;
	}
	public function getDetailsTable($f) {
		$tbl = ($f->db ? "`$f->db`." : "") . "`$f->orgtable`";
		if (isset($this->moreDetailsTbls[$tbl])) {
			$this->moreDetailsTbls[$tbl][$f->orgname]['field'] = $f->field;
			return $this->moreDetailsTbls[$tbl][$f->orgname];
		}
		$this->moreDetailsTbls[$tbl] = [];
		$res = $this->prQuery("{$this->ev->spfull} LIMIT 0", __FUNCTION__, __LINE__, true);
		$finfo = $res->fetch_fields();
		if ($this->dbVersion) {
			$res = $this->prQuery("SELECT * FROM information_schema.COLUMNS c \nWHERE TABLE_SCHEMA='{$f->db}' AND TABLE_NAME='{$f->orgtable}'", __FUNCTION__, __LINE__, true);
			while ($l = $res->fetch_assoc()) {
				if ($l['COLUMN_DEFAULT'] === 'NULL') $l['COLUMN_DEFAULT'] = null;
				elseif ($l['COLUMN_DEFAULT'] === 'current_timestamp()') $l['COLUMN_DEFAULT'] = 'CURRENT_TIMESTAMP';
				elseif (!is_null($l['COLUMN_DEFAULT'])) $l['COLUMN_DEFAULT'] = preg_replace("/(^')([^']*)\\1/", '\2', $l['COLUMN_DEFAULT']);
				$this->moreDetailsTbls[$tbl][$l['COLUMN_NAME']] = array(
					'type' => $l['COLUMN_TYPE'],
					'default' => $l['COLUMN_DEFAULT'],
					'extra' => $l['EXTRA'],
					'comment' => $l['COLUMN_COMMENT'],
					'key' => $l['COLUMN_KEY'],
					'field' => '',
				);
				//if(Secure::$idUser==2 && $l['COLUMN_NAME']=='idOwner') { print '<pre>';var_dump($l);print '</pre>';}
			}
		} else {
			$res = $this->prQuery("EXPLAIN $tbl", __FUNCTION__, __LINE__, true);
			while ($l = $res->fetch_assoc()) $this->moreDetailsTbls[$tbl][$l['Field']] = array(
				'type' => $l['Type'],
				'default' => $l['Default'],
				'extra' => $l['Extra'],
				'comment' => '',
				'key' => $l['Key'],
				'field' => '',
			);
		}
		$res->close();
		//show($this->dbVersion);
		//show($this->moreDetailsTbls);
		$this->moreDetailsTbls[$tbl][$f->orgname]['field'] = $f->field;
		return $this->moreDetailsTbls[$tbl][$f->orgname];
	}
	public function getValueFieldKey($field, $changeInsert = true) {
		if (array_key_exists($this->id, $_REQUEST) && array_key_exists($field, $_REQUEST[$this->id])) $v = $_REQUEST[$this->id][$field];
		elseif (array_key_exists($field, $_REQUEST)) $v = $_REQUEST[$field];
		else $v = null;

		if ($changeInsert && is_null($v) && $this->getC()) { // && $this->protected['auto_increment'] == $field) {
			$this->setActionInsert(1, true);
			//$this->rebuildAction(1, 4);
			return '';
		}
		return $v;
	}
	public function getValueField($field) {
		return isset($_REQUEST[$this->id][$field]) ? $_REQUEST[$this->id][$field] : (isset($_REQUEST[$field]) ? $_REQUEST[$field] : null);
	}
	private function getQuery($where) {
		if ($this->res) $this->res->close();
		$this->res = $this->prQuery("{$this->ev->spfull} WHERE {$where}", __FUNCTION__, __LINE__, true);
		$l = $this->res->fetch_row();
		if ($l) $this->found = true;
		else {
			$this->found = false;
			$this->protected['mess'] = $this->isActionInsert() ? 'Novo registro' : ($this->whereFields ? 'Não existe este registro: <pre>' . $this->whereFields . '</pre>' : '');
		}
		$finfo = $this->res->fetch_fields();
		$this->allWhere = $this->line = [];
		//print '<pre>'.print_r(@$this->detailsFields,true).'</pre>';
		foreach ($finfo as $k => $f) {
			$value = @$l[$k];
			$field = $f->name ? $f->name : $f->orgname;

			$this->line[$field] = $value;
			if (@$this->detailsFields[$field]->fullName && @$this->detailsFields[$field]->orgname) {
				if (!isset($this->unhashFields[$field])) $this->allWhere[] = $this->detailsFields[$field]->fullName . (is_null($value) ? " IS NULL" : "={$this->parseLineValue($value,$field)}");
			}
		}
		//verbose($this->line);
		foreach ($this->counters as $fieldName => $value) $this->line[$fieldName] = $value;
		//if (isset($this->line['Contador']))  print __LINE__.'<pre>'.print_r($this->line['Contador'],true)."</pre>\n";
		$this->allWhere = implode(" AND ", $this->allWhere);
		return true;
	}
	public function getJoinWhere() {
		$s = preg_replace(
			array('/[\r\n\v\t \(\)]+/', '/^.*? join .*? ON /i', '/\s*((left|right|cross|inner) |straight_)?join .*? ON /i'),
			array(' ', '', ' AND '),
			$this->tblCore
		);

		$s = preg_split("/\s+(on|and)\s+/i", $s);
		$rel = [];
		foreach ($s as $k => $v) {
			//preg_match_all("/\s+(?:(?:`([^`]+)`|([^\.]+))\.)?(?:(?:`([^`]+)`|([^\=\s]+))=/",$s,$ret);
			$v = explode("=", $v);
			if (count($v) !== 2) continue;
			foreach ($v as $kl => $vl) {
				preg_match("/\s*(?:(`?)([^`\.]*)\\1\.)?(?:(`?)([^`\.]*)\\3)\s*/", $vl, $ret);
				//print "<pre>$vl\n".print_r($ret,true)."</pre>";
				if (@$this->ev->alias[$ret[2]]['tbl']) {
					$rel[$k][$kl]['alias'] = $ret[2];
					$rel[$k][$kl]['tbl'] = "`{$this->ev->alias[$ret[2]]['db']}`.`{$this->ev->alias[$ret[2]]['tbl']}`";
				} else $rel[$k][$kl]['tbl'] = $rel[$k][$kl]['alias'] = '';
				$rel[$k][$kl]['field'] = $ret[4];
			}
		}
		return $rel;
	}
	public function getFieldValueIncremented($fieldName, $where = '') {
		if (!($o = @$this->detailsFields[$fieldName])) return false;
		$Tam = 0;
		$andWhere = '';
		if ($where) {
			$andWhere = " AND $where";
			$where = " WHERE $where";
		}
		if ($o->type == 'text') {
			$line = $this->fastLine("SELECT MAX(LENGTH({$o->orgname})) Tam, COUNT(1) Quant FROM {$o->fullTable}$where");
			if ($line['Quant'] == 0) return $o->default;
			$Tam = $line['Tam'];
			$line = $this->fastLine("SELECT MAX({$o->orgname}) Max, COUNT(1) Quant FROM {$o->fullTable} WHERE LENGTH({$o->orgname})>=$Tam$andWhere");
		} else $line = $this->fastLine("SELECT MAX({$o->orgname}) Max, COUNT(1) Quant FROM {$o->fullTable}$where");
		//print "{$fieldName} [{$o->type}]: {$line['Quant']} -> {$line['Max']}<br>";
		if ($o->type == 'year') $line['Max'] = max($line['Max'], 1900);
		elseif ($o->type == 'time') return $line['Max'] ? strftime('%T', strtotime($line['Max'] . ' +1 SECOND')) : '00:00:00';
		elseif ($o->type == 'datetime') return $line['Max'] && $line['Max'] != '0000-00-00 00:00:00' ? strftime('%F %T', strtotime($line['Max'] . ' +1 SECOND')) : '1970-01-01 00:00:00';
		elseif ($o->type == 'date') return $line['Max'] ? strftime('%F', strtotime($line['Max'] . ' +1 DAY')) : '1000-01-01';
		elseif ($line['Quant'] == 0) return $o->default;
		return ++$line['Max'];
	}
	private function getHash() {
		$this->pr($this->line, 'Line', __LINE__);
		$chk = [];
		foreach ($this->line as $k => $v) if (!isset($this->unhashFields[$k])) $chk[$k] = $v;
		//if ($this->debug===0) print "<pre> ".__LINE__.': '.print_r($chk,true).'<pre>';
		//print '<pre>'.print_r($chk,true).'</pre>';
		return $this->line ? md5(implode('', $chk)) : '';
		//return $this->line?md5(implode('',$this->line)):'';
	}
	private function getNameField($fieldName) {
		return "{$this->id}[$fieldName]";
	}
	public function getPrintFields($afld = false) {
		if ($afld) return is_array($afld) ? $afld : preg_split('/\s*,\s*/', trim($afld));
		else return array_keys($this->fields);
	}
	public function getFieldValue($fieldName) {
		if (isset($_REQUEST[$this->id][$fieldName])) return $_REQUEST[$this->id][$fieldName];
		elseif (isset($this->protected['key'][$fieldName])) return $this->protected['key'][$fieldName];
		elseif (isset($this->counters[$fieldName])) return $this->counters[$fieldName];
		//elseif (array_key_exists($fieldName,$this->fields)) return $this->fields[$fieldName]->value;
		//elseif (isset($this->line[$fieldName])) return $this->line[$fieldName];
		else return false;
	}
	private function getValueRequestLine($fieldName) {
		$v = $this->getFieldValue($fieldName);
		if ($v === false) return false;
		return $this->phpValue2Sql($v);
	}
	public function getError() {
		$out = [];
		$conn = $this->getConn();
		if ($this->trError && $erNo = $conn->errno()) {
			if (($this->trError >> 1) & 1 && @$this->trErrorValues[$erNo]) $out[] = $this->trErrorValues[$erNo];
			if ($this->trError & 1) $out[] = $this->checkInt_ERR($conn->error());
			return implode("\n", $out);
		} else return '';
	}
	public function checkInt_ERR($text) {
		return preg_replace('/^Duplicate entry \'INT_ERR:(.*)\' for key \'PRIMARY\'$/', '$1', $text);
	}
	public function getFormTable($width = '') {
		if (!$this->formTable) return;
		if ($width) $width = " width='$width'";
		$out = "<table id='FormTable' border='0' cellspacing='0'$width>\n<tr>\n";
		$out .= implode("</tr>\n<tr>\n", $this->formTable);
		$out .= "</tr>\n</table>\n";
		$this->formTable = [];
		return $out;
	}
	public function setFieldLikeIncrement($fieldName) {
		if (!($o = @$this->detailsFields[$fieldName])) return false;
		$o->edit = false;
		$o->readonly = true;
		$o->autoIncrementValue = true;
		$o->default = $this->getFieldValueIncremented($fieldName);
		if ($this->isActionInsert()) $this->counters[$fieldName] = $o->default;
		return $o;
	}
	public function setDefaultValue($fieldName, $value) {
		$this->detailsFields[$fieldName]->default = $value;
		if (isset($this->fields[$fieldName])) $this->fields[$fieldName]->default = $value;
		$this->rebuildField($fieldName);
	}
	public function setFieldValue($fieldName, $value = null) {
		$_REQUEST[$this->id][$fieldName] = $value;
		if (isset($this->protected['key'][$fieldName])) $this->protected['key'][$fieldName] = $value;
		if (isset($this->counters[$fieldName])) $this->counters[$fieldName] = $value;
	}
	private function rebuildCRUDS($val, $l) {
		$bit = 1 << $l;
		return $this->setCRUDS($val ? $this->protected['CRUDS'] | $bit : $this->protected['CRUDS'] & ~$bit);
	}
	public function rebuildAllTrKey() {
		if (@$this->protected['key']) foreach ($this->protected['key'] as $field => $v) if (!isset($this->allTrKey[$field])) {
			if (@$this->detailsFields[$field]->fullName) $this->allTrKey[$field] = $this->detailsFields[$field]->fullName;
			else $this->allTrKey[$field] = "`$field`";
		}
	}
	public function rebuildValueField($field) {
		if (array_key_exists($field, $this->fields)) $this->fields[$field]->value = $this->getValueField($field);
		return $this;
	}
	public function rebuildField($fieldName) {
		if (!isset($this->fields[$fieldName])) return;
		//$objClass=get_class($this->fields[$fieldName]);
		if (!isset($this->detailsFields[$fieldName]->value)) $this->detailsFields[$fieldName]->value = $this->fields[$fieldName]->value;
		$this->fields[$fieldName]->value = $this->isActionInsert() ? $this->fields[$fieldName]->default : $this->detailsFields[$fieldName]->value; //NOTA: capturar valor padrão
	}
	public function addField($fieldName, $obj = null, $value = null, $default = null) {
		$conn = $this->getConn();
		if (!$obj) {
			if (isset($this->fields[$fieldName])) {
				$obj = $this->fields[$fieldName];
				$objClass = get_class($obj);
			} else {
				if (array_key_exists($fieldName, $this->detailsFields)) $objClass = $this->detailsFields[$fieldName]->element;
				else $objClass = 'ElementString';
				$obj = new $objClass;
				if (preg_match("/^Element/", $objClass)) $obj->default = $default;
				if (!$obj->label) $obj->label = $fieldName;
				$obj->conn = $conn;
			}
		} else {
			$obj->conn = $conn;
			if (!$obj->label) $obj->label = $fieldName;
			$objClass = get_class($obj);
			if (@$obj->updatable) $obj->value = $this->getValueField($fieldName);
			//if(@$obj->updatable) $this->onstart[$fieldName]='$this->fields["'.$fieldName.'"]->update();';
			if (preg_match("/^Element/", $objClass)) {
				if (!$obj->default) $obj->default = $default;
				if (preg_match("/^Element(Assoc|GroupCheck|GroupImg|ListSearch)$/", $objClass)) {
					$obj->action = $this->protected['action'];
					$obj->key = $this->protected['key'];
					$this->externalUpdates[$fieldName] = $obj;
					//}elseif ($objClass=="ElementSearch") {
					//      $this->onstart[$fieldName]='$this->fields["'.$fieldName.'"]->update();';
				}
			}
		}

		if (preg_match("/^Element/", $objClass)) {
			//if(is_null($obj->edit)) $obj->edit=$this->isActionEdit()?true:null;
			$obj->{'ed-form-id'} = $this->id;
			$obj->{'ed-form-fieldname'} = $fieldName;
			//show([$obj->label,$obj->sql]);
			if (isset($this->protected['key'][$fieldName]) && $obj->readonly) $obj->required = false;
			if ($o = @$this->detailsFields[$fieldName]) {
				if ($o->type == 'date') $obj->type = 'date';
				$attr = array(
					'type', 'conn', 'value', 'editForce', 'showLabel', 'edit',
					'default', 'readonly', 'title', 'tabindex', 'disabled', 'maxlength',
					'align', 'style', 'width', 'height', 'minlength', 'maxlength', 'hidden', 'unsigned', 'precision', 'scale',
					'class', 'accesskey', 'placeholder', 'autocomplete', 'wrap',
					'function', 'inputValue', 'href', 'target', 'rows', 'size', 'isdecimal', 'order',
					'label', 'inputformat', 'displayformat', 'required', 'max', 'min', 'saveMask', 'validate', 'fillChar',
					'source', 'sql', 'fields', 'separator',
					'url', 'attr', 'nwtype',
					'onchange', 'onload', 'onsubmit', 'onblur', 'onfocus', 'onclick', 'onkeypress', 'onkeyup', 'onkeydown',
					'oncontextmenu', 'ondblclick', 'onhelp', 'onmousedown', 'onmouseup', 'onmouseover', 'onmouseout',

					'format', 'dayViewHeaderFormat', 'extraFormats', 'minDate', 'maxDate', 'stepping', 'useCurrent', 'collapse',
					'locale', 'defaultDate', 'disabledDates', 'enabledDates', 'icons', 'useStrict', 'sideBySide', 'daysOfWeekDisabled',
					'calendarWeeks', 'viewMode', 'toolbarPlacement', 'showTodayButton', 'showClear', 'showClose', 'widgetPositioning',
					'widgetParent', 'keepOpen', 'inline', 'keepInvalid', 'keyBinds', 'ignoreReadonly', 'disabledTimeIntervals', 'allowInputToggle',
					'focusOnShow', 'enabledHours', 'disabledHours', 'viewDate',
				);
				//if (isset($o->edit) && !is_null($o->edit)) $obj->editForce=$obj->edit && $o->edit;
				foreach ($attr as $a) {
					//if ($a=='width' && $obj->type=='combo') continue;
					if (isset($o->$a) && !is_null($o->$a)) {
						//if($fieldName=='idAlarme' && $a=='hidden') showme($o->$a);
						$obj->$a = $o->$a;
					}
				}
				if (@$o->value) $value = $o->value;
			}

			$obj->value = $this->isActionInsert() ? $obj->default : $value; //NOTA: capturar valor padrão

			$obj->objectForm = $this; //Retirar mais tarde
			$obj->form = $this;
			//$obj->kin=&$this->fields;
			$obj->name = $this->getNameField($fieldName);
		}
		if ($fieldName == $this->protected['auto_increment']) $obj->auto_increment = true;
		$obj->debug = $this->debug;
		$this->fields[$fieldName] = $obj;
		return $obj;
	}
	public function addFormTable($args = false) { //false | array(['label']=>'campo,campo...') 
		if (!$args || is_string($args)) $args = $this->getPrintFields($args);
		$hidden = '';
		$cell = [];
		$mark = '';
		foreach ($args as $label => $arg) {
			if (is_string($label)) {
				$label = $label ? "<nobr><label>$label:</label></nobr>" : '&nbsp;';
				$cell[] = "<th id='formCell'>$label</th>";
			}
			if (is_string($arg) || $arg === false) $arg = $this->getPrintFields($arg);
			else continue;
			foreach ($arg as $fld) {
				if (@$this->printed[$fld]) continue;
				$classOld = '';
				$width = '';
				if (!isset($this->fields[$fld])) {
					if ($fld) {
						$fld = ($t = @eval("return $fld;")) ? $t : $fld;
						$cell[] = "<th id='formCell'>$fld</th>";
					}
					continue;
				}
				$this->printed[$fld] = true;
				$class = get_class($this->fields[$fld]);
				if ($classOld !== $class) {
					$classOld == $class;
					if ($mark) {
						$cell[] = "<td width='1'><nobr>$mark</nobr></td>";
						$mark = '';
					}
				}
				if (!preg_match("/^Element/", $class)) {
					if ($this->fields[$fld]->label && @$this->fields[$fld]->showLabel !== false && @$this->fields[$fld]->showLabel !== false) $cell[] = "<th id='FormCell'><label><nobr>{$this->fields[$fld]->label}: </nobr></label></th>";
					$cell[] = "<td>{$this->fields[$fld]->__tostring()}</td>";
				} else {
					if ($class == "ElementHidden" || $this->fields[$fld]->hidden) $hidden .= $this->fields[$fld]->__tostring();
					elseif (preg_match("/^Element(Check|Button|Search)$/", $class)) $mark .= $this->fields[$fld]->__tostring();
					else {
						if ($class == 'ElementPasswd') {
							$toPrint = $this->fields[$fld]->toPrint;
							if ($this->fields[$fld]->toPrint == 3) $this->fields[$fld]->toPrint = $toPrint & 1;
						}
						$prtLabel = $this->fields[$fld]->showLabel;
						if ($prtLabel && $this->fields[$fld]->label) {
							$label = $this->fields[$fld]->htmlLabel();
							if ($label) $cell[] = "<th id='FormCell'><nobr>$label</nobr></th>";
						}
						$this->fields[$fld]->showLabel = false;
						$w = '';
						if ($width = $this->fields[$fld]->width) {
							if (preg_match('/%$/', $width)) {
								$w = " width='{$this->fields[$fld]->width}'";
								$this->fields[$fld]->width = '100%';
							} else $w = '';
						}
						$dados = "{$this->fields[$fld]}";
						if ($dados) $cell[] = "<td$w>$dados</td>";
						if ($class == 'ElementPasswd' && $toPrint != 2 && $this->fields[$fld]->edit) {
							$this->fields[$fld]->toPrint = $toPrint & 2;
							if ($this->fields[$fld]->validate == 'confirmPasswd' && $this->fields[$fld]->toPrint) {
								if ($prtLabel && $this->fields[$fld]->label) {
									$this->fields[$fld]->showLabel = true;
									$label = $this->fields[$fld]->htmlLabel();
									if ($label) $cell[] = "<th id='FormCell'><nobr>$label</nobr></th>";
								}
								$this->fields[$fld]->showLabel = false;
								$dados = "{$this->fields[$fld]}";
								if ($dados) $cell[] = "<td$w>$dados</td>";
							}
							$this->fields[$fld]->toPrint = $toPrint;
						}
						$this->fields[$fld]->showLabel = $prtLabel;
						$this->fields[$fld]->width = $width;
					}
				}
			}
			if ($mark) {
				$cell[] = "<td><nobr>$mark</nobr></td>";
				$mark = '';
			}
		}
		if ($cell) {
			$out = $hidden . preg_replace("/^(\<th id\='FormCell)('\>)/", '\1First\2', array_shift($cell));
			if (count($cell) == 1) $out .= preg_replace("/^(<td) width='.*?'/", '\1', array_shift($cell));
			else $out .= "<td width='100%'><table border='0' cellspacing='0' cellpadding='0'><tr>" . implode('', $cell) . "</tr></table></td>";
			$this->formTable[] = $out;
		} elseif ($hidden) $this->formTable[] = $hidden;
	}
	public function addFormTableLine() {
		if ($args = func_get_args()) {
			$num = count($args);
			$args = implode('</td><td>', $args);
			if ($num == 1) $out = "<td colspan='2'>$args</td>";
			elseif ($num == 2) $out = "<td>$args</td>";
			else $out = "<td colspan='2'><table border='0' cellspacing='0' width='100%'><tr><td>$args</td></tr></table></td>";
		} else $out = '';
		$this->formTable[] = $out;
	}
	public function addFormTableTd($obj, &$cell) {
		$out = [];
		if ($obj->label && (@$obj->showLabel || @$obj->showLabel)) $out[] = $cell[] = "<th id='FormCell'><label><nobr>{$obj->label}: </nobr></label></th>";
		$out[] = $cell[] = "<td>{$obj->__toString()}<td>";
		return implode('', $out);
	}
	public function bCRUDS($cruds) {
		return str_pad(base_convert($cruds, 10, 2), 5, 0, STR_PAD_LEFT);
	}
	private function createWhere() {
		if (!$this->protected['key']) {
			$this->protected['mess'] = 'ERROR INTERNO: Não existe chave de pesquisa';
			return false;
		}
		$out = [];
		$this->whereFields = '';
		$conn = $this->getConn();
		foreach ($this->protected['key'] as $k => $v) {
			if ($v != '') $this->whereFields .= "\n   $k=>$v";
			$out[] = "{$this->allTrKey[$k]}='{$conn->escape_string($v)}'";
		}
		$this->where = implode(" AND ", $out);
		//print "============> <pre>".print_r($this->where,true)."</pre>";
		return true;
	}
	private function createFields() {
		if ($this->creatFields && $this->line) {
			foreach ($this->line as $fieldName => $value) {
				if (!array_key_exists($fieldName, $this->fields)) $this->addField($fieldName, '', $value);
			}
			$this->creatFields = false;
		}
		return $this->getHash();
	}
	private function createFieldsRepeat() { //repete os dados dos fields
		if ($this->line) foreach ($this->line as $fieldName => $value) if (!array_key_exists($fieldName, $this->fields)) {
			$v = isset($_REQUEST[$fieldName]) ? $_REQUEST[$fieldName] : '';
			$this->addField($fieldName, '', $v, $v);
		}
	}
	private function makeExternalUpdates() {
		if (!$this->externalUpdates) return true;
		$update = false;
		$id = $this->id;
		foreach ($this->externalUpdates as $f => $obj) if ($obj->update(@$_REQUEST[$id][$f], $this)) $update = true;
		return $update;
	}
	private function makeExternalUpdatesAction() {
		$edit = $this->isActionEdit() ? true : null;
		foreach ($this->externalUpdates as $f => $obj) {
			$obj->action = $this->protected['action'];
			//$obj->edit=$edit;
		}
	}
	private function makeButtons() {
		if (!$this->showButtons) return '';
		$buttons = $this->buttonNav() . $this->buttonInsert() . $this->buttonView() . $this->buttonEdit() . $this->buttonDelete() . $this->buttonSave() . $this->buttonOthers();
		return '<div class="btn-group" role="group">' . $buttons . '</div>';
	}
	public function button($method, $title = '', $text = '', $symbol = '', $class = '', $attr = []) {
		$bt = new ElementButton();
		//$bt->{'ed-element'}='button_ctrl';
		$bt->{'ed-method'} = $method;
		$bt->title = $title;
		$bt->label = $text;
		$bt->icon = $symbol;
		$bt->{'ed-form-id'} = $this->id;
		$bt->addClass($class);
		//$bt->{'ed-form-id'}=$this->id;
		//$bt->preIdMain='Form_button_';
		foreach ($attr as $k => $v) $bt->$k = $v;
		return $bt;
	}
	public function buttonOthers() {
		$out = '';
		foreach ($this->buttons as $v) $out .= "$v";
		return $out;
	}
	public function buttonInsert() {
		if ($this->getIsInsert() && !($this->isActionInsert())) {
			if ($this->defaultButtons) return $this->button('insert', 'Cria um registro', 'Inserir', 'glyphicon-plus', 'btn-warning');
		}
		return '';
	}
	public function buttonView() {
		if ($this->found && $this->getR() && !$this->isActionInsert() && ($this->isActionUpdate() || $this->isActionDelete())) {
			if ($this->defaultButtons) return $this->button('view', 'Muda para o modo de vizulização', 'Visualizar', 'glyphicon-eye-open', 'btn-info');
		}
		return '';
	}
	public function buttonEdit() {
		if ($this->found && $this->getIsEdit() && !($this->isActionInsert() || $this->isActionUpdate())) {
			if ($this->defaultButtons) return $this->button('edit', 'Muda para o modo de edição', 'Editar', 'glyphicon-pencil', 'btn-primary'); //pencil
		}
		return '';
	}
	public function buttonDelete() {
		if ($this->found && $this->getIsDelete() && !$this->isActionInsert()) {
			if ($this->defaultButtons) return $this->button('del', 'Apaga este registro', 'Excluir', 'glyphicon-trash', 'btn-danger');
		}
		return '';
	}
	public function buttonSave() {
		//show([$this->found,$this->getIsEdit(),$this->isActionInsert(),$this->isActionUpdate()]);
		if ($this->defaultButtons && $this->getIsEdit() && (($this->found && $this->isActionUpdate()) || $this->isActionInsert())) {
			return $this->button('save', 'Grava as Alterações', 'Salvar', 'glyphicon-floppy-disk', 'btn-success');
		}
		return '';
	}
	public function buttonNav() {
		if (!$this->protected['isNav'] || !$this->protected['nav']) return '';
		//$outHtml->headScript[]="function fNav(){ location='{$this->protected['nav']}' }";
		return $this->button('nav', 'Volta para página de navegação', 'Nav', 'glyphicon-home', '', array('ed-href' => $this->protected['nav']));
	}
	public function selectDb($db = false) {
		if ($db) $this->protected['db'] = $db;
		$conn = $this->getConn();
		if ($conn) {
			if (!$this->protected['db']) $this->protected['db'] = $conn->db;
			elseif ($this->protected['db'] != $conn->db) {
				$conn->select_db($this->protected['db']);
			}
		}
		return $this;
	}
	public function captureDetails() {
		$wAdd = 3;
		$res = $this->prQuery("{$this->ev->spfull} LIMIT 0", __FUNCTION__, __LINE__, true);
		$finfo = $res->fetch_fields();
		$chk_autoincrement = true;
		foreach ($finfo as $position => $f) {
			$width = $height = $type = $source = $scale = $length = $max_length = $min_length = $precision = null;

			$element = "ElementString";
			$f->field = $f->name ? $f->name : $f->orgname;
			$f->position = $position;
			$f->typeNum = $f->type;
			$f->flagsNum = $f->flags;
			$f->type = null;
			$f->fullName = $f->table ? "`{$f->table}`.`{$f->orgname}`" : '';
			//discover db
			if (isset($this->ev->alias[$f->table])) $f->db = $this->ev->alias[$f->table]['db'];
			elseif (isset($this->ev->alias[$f->orgtable])) $f->db = $this->ev->alias[$f->orgtable]['db'];
			else $f->db = '';
			$f->fullTable = $f->orgtable && $f->db ? "`{$f->db}`.`{$f->orgtable}`" : "";
			$f->required = $f->flags & 1;
			$f->primary_key = ($f->flags >> 1) & 1;
			$f->unique_key = ($f->flags >> 2) & 1;
			$f->multi_key = ($f->flags >> 3) & 1;
			$f->part_key = ($f->flags >> 14) & 1;
			$f->key = $f->primary_key ? "PRI" : ($f->unique_key ? "UNI" : ($f->multi_key ? "MUL" : ($f->part_key ? "PAR" : "")));
			$f->unsigned = ($f->flags >> 5) & 1;
			$f->zerofill = ($f->flags >> 6) & 1;
			$f->group = ($f->flags >> 15) & 1;
			$f->auto_increment = ($f->flags >> 9) & 1;
			$readonly = $f->auto_increment;
			$f->hidden = $f->auto_increment; // || $f->primary_key || array_key_exists($f->field, (array)$this->protected['key']);

			$moreDetails = $this->getDetailsTable($f);
			$inputValue = null;
			$default = @$moreDetails['default'];
			if (preg_match("/^(\w+)(?:\((.*)\))?(.*?)$/", @$moreDetails['type'], $t)) {
				$f->type = $type = $t[1];
				$type2 = '';
				if (array_key_exists($f->name, $this->fields)) {
					$element = get_class($this->fields[$f->name]);
					$type2 = $this->fields[$f->name]->type;
				}
				if ($type == 'set' || $type == 'enum') {
					if (preg_match_all("/'((?:'{2}|[^'])*)'/", $t[2], $out)) {
						$element = $type == 'set' ? 'ElementList' : 'ElementCombo';
						$source = [];
						foreach ($out[1] as $v) {
							$v = str_replace("''", "'", $v);
							$source[$v] = $v;
						}
					}
				} elseif ($type == 'year') {
					$element = 'ElementCalendar';
					$width = '4em';
					if ($f->required && $default == '') $default = strftime("%F");
				} elseif ($type2 != 'combo' && ($type == 'bit' || $type == 'year')) {
					$element = 'ElementNumber';
					$scale = (int) $t[2];
					if ($type == 'bit' && $scale == 1) $element = 'ElementCheck';
					else $width = (ceil($scale / 2) + $wAdd) . "em";
				} elseif (preg_match("/date|time/", $type)) {
					$element = 'ElementCalendar';
					$f->type = preg_match('/date|timestamp/', $type) ? 'date' : '';
					$f->type .= preg_match("/time/", $type) ? 'time' : '';
					if ($default == "CURRENT_TIMESTAMP") {
						$default = strftime("%F %T");
						//$inputValue='';
						$f->required = false;
						$f->edit = false;
						//$readonly=true;
					} elseif (
						preg_match('/0000-00-00( 00:00:00)?/', $default) ||
						($default == '' && $f->required && $type == 'timestamp')
					) $default = strftime("%F %T");
				} elseif ($type2 != 'combo' && preg_match("/text|char|blob|binary/", $type)) {
					$element = 'ElementText';
					$f->type = 'text';
					if (preg_match("/^tiny/", $type)) {
						$max_length = $length = 255;
						$height = "2em";
					} elseif ($type == 'text' || $type == 'blob') {
						$max_length = $length = 65535;
						$height = "4em";
					} elseif (preg_match("/^medium/", $type)) {
						$max_length = $length = 16777215;
						$height = "6em";
					} elseif (preg_match("/^long/", $type)) {
						$max_length = $length = 4294967295;
						$height = "10em";
					} else {
						$element = "ElementString";
						$max_length = $length = (int) $t[2];
					}
					if ($length > 80) $width = "100%";
					else $width = (ceil($length / 2) + $wAdd) . "em";
				} elseif ($type2 != 'combo' && preg_match("/int$/", $type)) {
					$element = 'ElementNumber';
					$f->isdecimal = 0;
					$f->type = 'number';
					$max_length = 255;
					$scale = $length = (int) $t[2];
					if ($type == 'tinyint') {
						if ($length == 1 && $f->unsigned) $element = 'ElementCheck';
					} elseif ($type == 'smallint') $max_length = 65535;
					elseif ($type == 'mediumint') $max_length = 16777215;
					elseif ($type == 'int') $max_length = 4294967295;
					elseif ($type == 'bigint') $max_length = 18446744073709551615;
					if (!$f->unsigned) {
						$length++;
						$max_length = floor($max_length / 2);
						$min_length = ($max_length + 1) * (-1);
					}
					$width = (ceil($length / 2) + $wAdd) . "em";
				} elseif ($type2 != 'combo' && preg_match("/float|double|decimal/", $type)) {
					$element = 'ElementNumber';
					$f->type = 'number';
					$min_length = 0;
					$tmp = explode(',', $t[2]);
					$precision = (int) @$tmp[1];
					$scale = (int) @$tmp[0];
					if ($type == 'decimal') {
						$scale = $scale < $precision ? 0 : $scale - $precision;
						$f->isdecimal = 1;
						$tmp = pow(10, $precision);
						$max_length = (pow(10, $scale) - 1) + (($tmp - 1) / $tmp);
						$length = strlen($max_length);
						if (!$f->unsigned) {
							$length++;
							$min_length = (-1) * $max_length;
						}
					} else {
						if ($type == 'float') {
							$f->isdecimal = 2;
							$max_length = 3.402823466E+38;
						} else {
							$f->isdecimal = 3;
							$max_length = 1.7976931348623157E+308;
						}
						if (!$f->unsigned) {
							$max_length = floor($max_length / 2);
							$min_length = ($max_length + 1) * (-1);
						}
					}
					$width = (ceil($scale / 2) + 1 + $wAdd) . "em";
				} else {
					$length = $t[2];
				} //geometry
			} else $f->type = $type = strtolower($f->vartype);

			$f->width = $width;
			$f->height = $height;
			$f->min = $min_length;
			$f->max = $max_length;
			$f->source = $source;
			$f->default = $default;
			$f->inputValue = $inputValue;
			$f->readonly = $readonly ? true : null;
			$f->form = $this;
			$f->element = $element;
			//$f->length=$length;
			$f->maxlength = $length;
			$f->precision = $precision;
			$f->scale = $scale;
			$titleParser = @$moreDetails['comment'];
			$er = "/^\[(.*?)\]\s*/";
			if (preg_match($er, $titleParser, $ret)) {
				$f->label = $ret[1];
				$titleParser = preg_replace($er, '', $titleParser);
			}
			$er = "/\s*\<\?(php\s+)?((.|\s)*?)\?\>\s*/i";
			if (preg_match($er, $titleParser, $ret)) {
				$cmd = str_replace('$this->', '$f->', $ret[2]);
				@eval($cmd);
				$titleParser = preg_replace($er, '', $titleParser);
			}
			if ($titleParser) $f->title = $titleParser;
			if (
				$f->element != $element && $f->width == $width &&
				!preg_match('/^Element(Email|(Cpf)?Cnpj|Cpf|Cep|Passwd|Telefone|IP(v6)?)$/', $f->element)
			) $f->width = null;
			$conn = $this->getConn();
			$f->flags = $conn->trNumFlag($f->flags);
			$this->detailsFields[$f->field] = $f;
			if ($f->auto_increment) {
				if (!$this->protected['auto_increment']) $this->protected['auto_increment'] = $f->field;
				$this->protected['key'][$f->field] = $this->getValueFieldKey($f->field, $chk_autoincrement);
				$chk_autoincrement = false;
				//show([$this->protected['key']);
			} elseif ($f->primary_key) $this->protected['key'][$f->field] = $this->getValueFieldKey($f->field, false);

			if ($f->primary_key && (!isset($this->allTrKey[$f->field]) || $f->auto_increment)) $this->allTrKey[$f->field] = $f->fullName;
		}
		$res->close();
		$this->rebuildAllTrKey();
	}
	public function debugSql() {
		$conn = $this->getConn();
		if (!$conn) {
			$this->protected['mess'] = 'ERROR INTERNO: Não existe conexão com o banco';
			return false;
		}
		if (!$this->protected['tbl']) {
			$this->protected['mess'] = 'ERROR INTERNO: Não existe tabela ou query';
			return false;
		}
		$this->ev = new EasyView($conn, $this->protected['tbl']);
		$this->dbVersion = $conn->get_server_info() >= '5.0';
		$sql = $this->ev->sp;
		if (!$this->mngTables) $this->mngTables = implode(",", array_keys($this->ev->alias));
		$this->tblCore = preg_replace("/^(?:.|\s)*?(\s+from\s+)/i", "", $this->ev->spfull);
		if (preg_match("/\b(where|union|limit|select)\b/i", $this->tblCore)) $this->protected['isDelete'] = $this->protected['isInsert'] = $this->protected['isEdit'] = false;

		$this->captureDetails();

		if (preg_match("/^\s*select\s+/i", $sql)) {
			$sql = $this->ev->spfull;
			$this->actionCmd = 'multi';
		} else $sql = preg_replace(array("/^(`?)(\w+)\\1(?:\.(`?)(\w+)\\3)?$/", "/\.``$/"), array("`\\2`.`\\4`", ""), $sql);

		return $sql;
	}
	public function elementHash() {
		return "<input name='frm_hash_{$this->id}' type='hidden' value='{$this->hash}' /><input name='frm_oldWhere_{$this->id}' type='hidden' value='" . htmlspecialchars($this->where, ENT_QUOTES) . "' />";
	}
	public function updateForceIncrement() {
		$conn = $this->getConn();
		//show(__function__);
		if (!($field = $this->forceIncrementField) || !$this->isActionInsert()) return;
		$where = [];
		foreach ($this->key as $k => $v) if ($k != $field) {
			if (is_null($v)) $v = ' IS NULL';
			else $v = "='{$conn->escape_string($v)}'";
			$where[] = "`$k`$v";
		}
		$where = $where ? ' WHERE ' . implode(' AND ', $where) : '';
		$res = $this->prQuery("{$this->ev->spfull}$where ORDER BY $field desc LIMIT 1", __FUNCTION__, __LINE__, true);
		$line = $res->fetch_assoc();
		$id = ($line ? $line[$field] : 0) + 1;
		if (array_key_exists($field, $this->fields)) {
			$this->fields[$field]->value = $id;
			$this->fields[$field]->readonly = true;
		}
		$_REQUEST[$this->id][$field] = $this->line[$field] = $id;
		//print 'MANUTENÇÃO: ('.__LINE__.')<pre>'.print_r($this->line[$field],true).'</pre>';
		if (isset($this->key[$field])) {
			$this->protected['key'][$field] = $_REQUEST[$this->id][$field];
			$this->createWhere();
		}
		$res->close();
	}
	private function verifyAction() {
		$out = true;
		if ($this->isActionSaving()) {
			$this->setActionSaving(0);
			$hash = $this->getHash();
			//$hash=$this->getOldHash();
			if (($this->isActionUpdate() || $this->isActionDelete()) && $this->hash) if ($hash != $this->hash) {
				$this->pr("Hash: {$hash}!={$this->hash}\n", 'Concorrência Hash', __LINE__);
				return $this->turnAction("Concorrencia. Outro usuário pode ter alterado este registro", false, 'setActionView');
			}
			if (!isset($_REQUEST[$this->id]) && !$this->isActionDelete()) {
				return $this->turnAction("Não existe campos neste formulário", false, 'setActionView');
			}
			$cmd2 = '';
			$this->saved = true;
			if ($this->isActionInsert()) {
				$cmd2 = "Insert";
				$this->inserted = true;
			} elseif ($this->isActionUpdate()) {
				$cmd2 = "Update";
				$this->updated = true;
			} elseif ($this->isActionDelete()) {
				$cmd2 = "Delete";
				$this->deleted = true;
			} else return;
			$cmd = $this->actionCmd . $cmd2;
			$cmdLower = strtolower($cmd2);
			if (!$this->startEvent('onbefore' . $cmdLower)) return false;
			$ret = $this->$cmd();
			if ($this->sucess) {
				if ($ret) $out = $ret[0];
				else {
					$this->createWhere();
					$this->getQuery($this->where);
				}
			}
			if ($this->sucess) {
				$this->hash = $this->createFields();
				//if(array_key_exists($k='Grupos',$this->fields)) showme([$this->fields[$k]->value,$_REQUEST[$this->id]]);
				foreach ($this->fields as $field => $obj) if (@$obj->updatable) {
					$obj->value = $this->getValueField($field);
					$obj->update();
					//showme($this->fields);
					//showme($obj);
				}

				if (!$this->startEvent('onaftersucess')) return false;
				if (!$this->startEvent('onafter' . $cmdLower)) return false;
				if ($this->deleted && $this->nav) goURL($this->nav);
			} elseif (!$this->startEvent('onafternot' . $cmdLower)) return false;
		}
		return $out;
	}
	private function fastLine($sql) {
		$res = $this->prQuery($sql);
		$line = $res->fetch_assoc();
		$res->close();
		return $line;
	}
	private function simpleInsert() {
		$keys = $values = [];
		//$this->pr($this->line);
		if ($this->line) foreach ($this->line as $fieldName => $v) if ($fieldName != $this->protected['auto_increment'] && ($v = $this->getValueRequestLine($fieldName)) !== false) {
			$keys[] = "`$fieldName`";
			$values[] = $v;
		}
		if (!$keys) return array($this->turnAction("Não há campos", false));
		$keys = implode(", ", $keys);
		$values = implode(", ", $values);
		$this->prQuery("INSERT {$this->protected['tbl']} ($keys) VALUES ($values)", __FUNCTION__, __LINE__);
		if ($er = $this->getError()) {
			return array($this->turnAction($er, false, 'setActionInsert'));
		}
		$conn = $this->getConn();
		if ($conn->affected_rows() && $this->getInsertId()) {
			$this->turnAction("Registro inserido com sucesso");
			$up = $this->makeExternalUpdates();
		} else {
			return array($this->turnAction("Não houve inserção", false, 'setActionInsert', false));
		}
	}
	private function simpleUpdate() {
		$set = [];
		//show(array_keys($this->fields));
		if ($this->line) foreach ($this->line as $fieldName => $v) if (($v = $this->getValueRequestLine($fieldName)) !== false) {
			$set[] = "`$fieldName`=$v";
			if (isset($this->protected['key'][$fieldName]) && isset($_REQUEST[$this->id][$fieldName])) $this->protected['key'][$fieldName] = $_REQUEST[$this->id][$fieldName];
		}
		$set = implode(", ", $set);
		$this->prQuery($sql = "UPDATE {$this->sql} SET $set WHERE {$this->oldWhere} LIMIT 1", __FUNCTION__, __LINE__);
		$conn = $this->getConn();
		$afect = $conn->affected_rows();
		if ($er = $this->getError()) {
			return array($this->turnAction($er, false, 'setActionUpdate'));
		}
		if ($this->makeExternalUpdates() || $afect) $this->turnAction('Registro alterado com sucesso');
		else {
			$this->turnAction('Não houve alteração', 0, 'setActionUpdate');
		}
	}
	private function simpleDelete() {
		$this->prQuery("DELETE FROM {$this->protected['tbl']} WHERE {$this->oldWhere} LIMIT 1", __FUNCTION__, __LINE__);
		if ($this->rebuildId) $this->prQuery("ALTER TABLE {$this->protected['tbl']} AUTO_INCREMENT=1");
		$conn = $this->getConn();
		$afect = $conn->affected_rows();
		if ($er = $this->getError()) {
			return array($this->turnAction($er, false, 'setActionView'));
		}
		if ($this->makeExternalUpdates() || $afect) {
			return array($this->turnAction("Registro apagado com sucesso", true, 'setActionInsert'));
		} else {
			return $this->turnAction("Registro NÃO pode ser apagado", false, 'setActionView');
		}
	}
	private function multiInsert() {
		$auto_increment = $inserts = $alias2Tbl = [];
		//$keys=$requireKeys=$keys2=$requireKeys2==[];
		$erTbl = '/^' . str_replace(',', '|', preg_quote($this->mngTables)) . '$/';
		foreach ($this->line as $fieldName => $v) {
			if (!$this->detailsFields[$fieldName]->orgname) continue;
			$alias = $this->detailsFields[$fieldName]->table;
			if (!($tbl = $this->detailsFields[$fieldName]->fullTable) || !preg_match($erTbl, $alias)) continue;
			$alias2Tbl[$alias] = $tbl;
			$field = $this->detailsFields[$fieldName]->orgname;
			if ($this->detailsFields[$fieldName]->auto_increment && !$auto_increment && isset($this->protected['key'][$fieldName])) {
				$auto_increment = array('tbl' => $tbl, 'field' => $field, 'alias' => $alias,);
				$inserts[$alias][$field] = "NULL";
				//$keys[$tbl][$field]=$keys2[$alias][$field]=$fieldName;
			} else {
				if (($v = $this->getValueRequestLine($fieldName)) !== false) $inserts[$alias][$field] = $v;
				//if (isset($this->protected['key'][$fieldName])) $keys[$tbl][$field]=$keys2[$alias][$field]=$fieldName;
				//if ($this->detailsFields[$fieldName]->key) $requireKeys[$tbl][$field]=$requireKeys2[$alias][$field]=$fieldName;
			}
		}
		$rel = $rel2 = $this->getJoinWhere();
		$c = 0;

		do foreach ($rel as $k => $v) {
			if (isset($inserts[$v[0]['alias']][$v[0]['field']])) {
				$inserts[$v[1]['alias']][$v[1]['field']] = &$inserts[$v[0]['alias']][$v[0]['field']];
			} elseif (isset($inserts[$v[1]['alias']][$v[1]['field']])) {
				$inserts[$v[0]['alias']][$v[0]['field']] = &$inserts[$v[1]['alias']][$v[1]['field']];
			} else {
				$joinField = $joinValue = false;
				if (!$v[0]['alias'] && $v[1]['alias']) {
					$joinField = $v[1];
					$joinValue = $v[0];
				} elseif ($v[0]['alias'] && !$v[1]['alias']) {
					$joinField = $v[0];
					$joinValue = $v[1];
				} else continue;
				$inserts[$joinField['alias']][$joinField['field']] = $this->phpValue2Sql($joinValue['field']);
				//if (isset($this->protected['key'][$joinField['field']])) $keys2[$joinField['alias']][$joinField['field']]=$joinField['field'];
				//if ($this->detailsFields[$joinField['field']]->key) $requireKeys2[$joinField['alias']][$joinField['field']]=$joinField['field'];
			}
			unset($rel[$k]);
		} while ($c <> ($c = count($rel)) && $c);

		$erro = [];
		$ok = false;
		//Primeira inserção
		if ($auto_increment) {
			//print '<pre>Manutencao:'.print_r($auto_increment,true).'</pre>';
			$tbl = $auto_increment['tbl'];
			$alias = $auto_increment['alias'];
			$inserts[$alias][$auto_increment['field']] = $id = null;
			if ($this->mountInsert($tbl, $inserts[$alias], $auto_increment['field'])) {
				if ($er = $this->getError()) $erro[] = $er;
				elseif (($id = $this->getInsertId())) {
					$ok = true;
					if ($id === true) $id = null;
				}
			} else $erro[] = "Erro ao montar o Insert da Table $tbl.{$this->messageTransp}";
			$inserts[$alias][$auto_increment['field']] = $id;
			unset($inserts[$alias]);
		}
		//if ($this->debug===0) print "<pre> ".__LINE__.': '.print_r($inserts,true).'<pre>';
		if (!$erro) {
			foreach ($inserts as $alias => $array) {
				//print __LINE__.": dddddd <br>";
				if ($this->mountInsert($alias2Tbl[$alias], $array, '', 'INSERT IGNORE')) {
					if ($er = $this->getError()) $erro[] = $er;
					else $ok = true;
				}
			}
		}
		if ($erro) {
			return array($this->turnAction(implode('<hr>', $erro), false, 'setActionInsert'));
		} elseif (!$this->makeExternalUpdates()) {
			return array($this->turnAction('Não houve inserção apenas nas dependências', false, 'setActionInsert', false));
		}
		if ($ok) $this->turnAction('Registro inserido com sucesso');
		else {
			return array($this->turnAction('Não houve inserção', false, 'setActionInsert', false));
		}
	}
	private function multiUpdate() {
		$set = [];
		foreach ($this->line as $fieldName => $v) if ($this->detailsFields[$fieldName]->orgname && ($v = $this->getValueRequestLine($fieldName)) !== false) {
			$f = $this->detailsFields[$fieldName]->fullName ? $this->detailsFields[$fieldName]->fullName : "`$fieldName`";
			$set[] = "$f=$v";
			if (isset($this->protected['key'][$fieldName]) && $_REQUEST[$this->id][$fieldName]) $this->protected['key'][$fieldName] = $_REQUEST[$this->id][$fieldName];
		}
		if (!$set) {
			return array($this->turnAction('Problema na view, não houve alteração', false, 'setActionUpdate'));
		}
		$set = implode(", ", $set);
		$limit = preg_match("/\b(join)\b/i", $this->tblCore) ? "" : " LIMIT 1";
		$this->prQuery("UPDATE {$this->tblCore} \nSET $set\nWHERE {$this->allWhere}$limit", __FUNCTION__, __LINE__);
		$conn = $this->getConn();
		$afect = $conn->affected_rows();
		if ($er = $this->getError()) {
			return array($this->turnAction($er, false, 'setActionUpdate'));
		}
		if ($this->makeExternalUpdates() || $afect) $this->turnAction("Registro alterado com sucesso");
		else {
			$this->turnAction('Não houve alteração', 0, 'setActionUpdate');
		}
	}
	private function multiDelete() {
		$limit = preg_match("/\b(join)\b/i", $this->tblCore) ? "" : " LIMIT 1";
		$sql = "DELETE {$this->mngTables} \nFROM {$this->tblCore} \nWHERE {$this->allWhere}$limit";
		$this->prQuery($sql, __FUNCTION__, __LINE__);
		if ($this->rebuildId) foreach ($this->ev->alias as $k => $v) {
			//if ($this->debug===0) print "<pre> ".__LINE__.': '.print_r($v,true).'<pre>';
			$this->prQuery("ALTER TABLE `{$v['db']}`.`{$v['tbl']}` AUTO_INCREMENT=1");
		}
		$conn = $this->getConn();
		$afect = $conn->affected_rows();
		if ($er = $this->getError()) {
			return array($this->turnAction($er, false, 'setActionView'));
		}
		if ($this->makeExternalUpdates() || $afect) {
			return array($this->turnAction("Registro apagado com sucesso", true, 'setActionInsert'));
		} else {
			return $this->turnAction("Registro NÃO pode ser apagado", false, 'setActionView');
		}
	}
	public function mountInsert($tbl, $array, $fieldAI = '', $cmd = 'INSERT') {
		//$this->pr(__LINE__.": MANUTENÇÃO");
		foreach ($array as $k => $v) if (is_null($v)) unset($array[$k]);
		//foreach ($array as $k=>$v) if (is_null($v)) $array[$k]='NULL';
		if (!$array) return false;
		$erro = '';
		//print '<pre>'.print_r($this->moreDetailsTbls,true).'</pre>';
		foreach ($this->moreDetailsTbls[$tbl] as $f => $v) {
			if ($fieldAI != $f && $v['key'] == 'PRI' && !isset($array[$f]) && is_null($v['default'])) {
				$erro .= "<br>Field `$f` necessita de ter um valor não nulo, pois é chave.";
			}
		}
		if ($erro) {
			$this->messageTransp .= $erro;
			return false;
		}
		$keys = "`" . implode("`,`", array_keys($array)) . "`";
		$values = implode(",", $array);
		$this->prQuery($sql = "$cmd $tbl \n($keys) VALUES \n($values)", __FUNCTION__, __LINE__);
		//print "<pre>$sql</pre>";
		return true;
	}
	private function parseLineValue($value, $field = '') {
		if (is_null($value)) return "NULL";
		if (is_numeric($value)) {
			if ($field && @$this->detailsFields[$field]->type == 'number') return $value;
			return "'$value'";
		}
		$conn = $this->getConn();
		return "'{$conn->escape_string($value)}'";
	}
	private function turnAction($mess = '', $sucess = true, $action = false, $loadDB = true) {
		$class = $sucess === 0 ? "bg-warning" : ($sucess ? "bg-success" : "bg-danger");
		$this->sucess = $sucess;
		if ($mess) $this->protected['mess'] = "<pre class='$class'>$mess</pre>";
		if ($action !== false) $this->$action(true);
		elseif ($this->viewAfterEdit) $this->setActionView(true);
		return $loadDB;
	}
	private function phpValue2Sql($value) {
		if ($value === false) return 'false';
		if ($value === '') return 'NULL';
		if (is_numeric($value)) return "'$value'";
		if (is_array($value)) $value = implode(',', $value);
		$conn = $this->getConn();
		return "'{$conn->escape_string($value)}'";
	}
	public function html($afld = false, $vertical = false) {
		$afld = $this->getPrintFields($afld);
		$pre = '';
		$out = [];
		$segundo = false;
		foreach ($afld as $fld) {
			if (@$this->printed[$fld]) continue;
			$this->printed[$fld] = true;
			$v = $this->fields[$fld]->__tostring();
			$class = get_class($this->fields[$fld]);
			if ($class == "ElementHidden") $pre .= $v;
			elseif ($segundo || preg_match("/Element(Radio|Check)/", $class)) $out[] = $v;
			else $out[] = "<span id='firstFieldLine'>$v</span>";
			$segundo = $vertical ? false : true;
		}
		if ($out) $pre .= "<div>" . implode($vertical ? "</div>\n<div>" : "&nbsp;", $out) . "</div>\n";
		return $pre;
	}
	public function htmlV($afld = false) {
		return $this->html($afld, true);
	}
	public function renewField2addFormTable($fields) {
		if (is_string($fields)) $fields = preg_split('/\s*[,;]\s*/', trim($fields));
		if (!is_array($fields)) return;
		foreach ($fields as $fld) $this->printed[$fld] = false;
	}
	public function prepare_cells($args = false) { //false | array(['label']=>'campo,campo...')
		$fn = __FUNCTION__;

		if (!$args || is_string($args)) $args = $this->getPrintFields($args);
		$hidden = '';
		$cell = [];
		$mark = '';
		$tamTot = 0;
		foreach ($args as $label => $arg) {
			if (is_string($label)) {
				$tam = strlen($label);
				$width = $tam * 8;
				$tamTot += $width;
				$cell[] = array(
					'content' => $label ? '<label><nobr>' . $label . '</nobr></label>' : '&nbsp;',
					'class' => 'ed-form-label' . (count($cell) ? ' ed-form-firstCell' : ''),
					'width' => $width,
				);
			}

			if (!$arg) continue;
			elseif (is_object($arg)) $arg = array($arg);
			elseif (is_string($arg) || $arg === false) $arg = $this->getPrintFields($arg);
			elseif (!is_array($arg)) continue;

			foreach ($arg as $fld) {
				//if()
				if (@$this->printed[$fld]) continue;
				$classOld = '';
				$width = '';
				if (!isset($this->fields[$fld])) {
					if ($fld) {
						$fld = ($t = @eval("return $fld;")) ? $t : $fld;
						$cell[] = "<th id='formCell'>$fld</th>";
					}
					continue;
				}
				$this->printed[$fld] = true;
				$class = get_class($this->fields[$fld]);
				if ($classOld !== $class) {
					$classOld == $class;
					if ($mark) {
						$cell[] = "<td width='1'><nobr>$mark</nobr></td>";
						$mark = '';
					}
				}
				if (!preg_match("/^Element/", $class)) {
					if ($this->fields[$fld]->label && @$this->fields[$fld]->showLabel !== false && @$this->fields[$fld]->showLabel !== false) $cell[] = "<th id='FormCell'><label><nobr>{$this->fields[$fld]->label}: </nobr></label></th>";
					$cell[] = "<td>{$this->fields[$fld]->__tostring()}<td>";
				} else {
					if ($class == "ElementHidden" || $this->fields[$fld]->hidden) $hidden .= $this->fields[$fld]->__tostring();
					elseif (preg_match("/^Element(Check|Button|Search)$/", $class)) $mark .= $this->fields[$fld]->__tostring();
					else {
						if ($class == 'ElementPasswd') {
							$toPrint = $this->fields[$fld]->toPrint;
							if ($this->fields[$fld]->toPrint == 3) $this->fields[$fld]->toPrint = $toPrint & 1;
						}
						$prtLabel = $this->fields[$fld]->showLabel;
						if ($prtLabel && $this->fields[$fld]->label) {
							$label = $this->fields[$fld]->htmlLabel();
							if ($label) $cell[] = "<th id='FormCell'><nobr>$label</nobr></th>";
						}
						$this->fields[$fld]->showLabel = false;
						$w = '';
						if ($width = $this->fields[$fld]->width) {
							if (preg_match("/\%$/", $width)) {
								$w = " width='{$this->fields[$fld]->width}'";
								$this->fields[$fld]->width = '100%';
							} else $w = " width='1'";
						}
						$dados = "{$this->fields[$fld]}";
						if ($dados) $cell[] = "<td$w>$dados<td>";
						if ($class == 'ElementPasswd' && $toPrint != 2 && $this->fields[$fld]->edit) {
							$this->fields[$fld]->toPrint = $toPrint & 2;
							if ($this->fields[$fld]->validate == 'confirmPasswd' && $this->fields[$fld]->toPrint) {
								if ($prtLabel && $this->fields[$fld]->label) {
									$this->fields[$fld]->showLabel = true;
									$label = $this->fields[$fld]->htmlLabel();
									if ($label) $cell[] = "<th id='FormCell'><nobr>$label</nobr></th>";
								}
								$this->fields[$fld]->showLabel = false;
								$dados = "{$this->fields[$fld]}";
								if ($dados) $cell[] = "<td$w>$dados<td>";
							}
							$this->fields[$fld]->toPrint = $toPrint;
						}
						$this->fields[$fld]->showLabel = $prtLabel;
						$this->fields[$fld]->width = $width;
					}
				}
			}
			if ($mark) {
				$cell[] = "<td width='1'><nobr>$mark</nobr></td>";
				$mark = '';
			}
		}
		if ($cell) {
			$out = $hidden . preg_replace("/^(\<th id\='FormCell)('\>)/", "\\1First\\2", array_shift($cell));
			if (count($cell) == 1) $out .= preg_replace("/^(<td) width='.*?'/", "\\1", array_shift($cell));
			else $out .= "<td width='100%'><table border='0' cellspacing='0' cellpadding='0'><tr>" . implode("", $cell) . "</tr></table></td>";
			$this->formTable[] = $out;
		}
	}
	public function build_cell(&$cell, &$tot, $arg, $class = '') {
		if (is_object($arg)) {
			$width = @$arg->width;
			if (!$width) $width = 150;
			$content = "$arg";
		} elseif (is_string($arg) && preg_match('/^[^<> ]+$/i', $arg)) $arg = $this->getPrintFields($arg);
		else {
			$arg = (string) $arg;
		}
		$tot += $width;
		$cell[] = array(
			'content' => $content,
			'class' => 'ed-form-label' . (count($cell) ? ' ed-form-firstCell' : ''),
			'width' => $width,
		);
		return $this;
	}
	public function cfgFields($method, $array, $value = '') {
		$array = is_string($array) ? preg_split("/\s*,\s*/", $array) : (array) $array;
		foreach ($array as $k => $v) {
			if (is_numeric($k)) {
				$k = $v;
				$v = $value;
			}
			if (!is_numeric($k) && array_key_exists($k, $this->fields)) $this->fields[$k]->$method = $v;
		}
		return $this;
	}
	public function pr($texto, $label = '', $line = '') {
		if ($this->debug) {
			if (is_array($texto)) $texto = print_r($texto, true);
			if ($label) $label = "<b>$label" . ($line ? " ($line)" : '') . ":</b>\n";
			if (preg_match('/^(\t| )+/', $texto, $ret)) $texto = preg_replace(array('/^(\t| )+/', "(\n|\r){$ret[0]}"), array('', '\1'), $texto);
			print "<hr><pre style='font-size:small;'>$label" . print_r($texto, true) . '</pre>';
		}
		return $this;
	}
	public function prQuery($sql, $label = '', $line = '', $error = false) {
		$this->pr($sql, $label, $line);
		$conn = $this->getConn();
		return $conn->query($sql, $error);
	}
	public function showHistory($tblHst, $idUserUpd = 'idUserUpd', $dtUpdateField = 'DtUpdate') {
		$out = $this->history($tblHst, $idUserUpd, $dtUpdateField);
		if (!$out) return;
		print '<div class="container"><h3>History</h3>';
		$class = ' class="bg-success"';
		foreach ($out as $dtUpdate => $fields) {
			if (array_key_exists('_', $fields)) {
				$id = $fields['_']['idUser'];
				$user = $id ? @' ' . $fields['_']['User'] . '(' . $id . ')' : '';
				unset($fields['_']);
			} else $user = '';

			print "<div><div$class><label>{$dtUpdate}:</label>$user</div><ul>\n";
			foreach ($fields as $field => $value) print "<li>{$field}=<i>" . htmlentities($value) . '</i></li>';
			print "</ul></div>\n";
			$class = '';
		}
		print '</div>';
	}
	public function history($tblHst, $idUserUpd = 'idUserUpd', $dtUpdateField = 'DtUpdate') {
		static $isUsers = [];

		$where = $this->conn->mountFieldsConpareValues($keys = $this->key);
		if (!$where) return;
		$keys[$dtUpdateField] = null;
		$keys[$idUserUpd] = null;
		$line = $out = [];
		$res = $this->conn->query("SELECT * FROM $tblHst WHERE $where ORDER BY $dtUpdateField");
		while ($l = $res->fetch_assoc()) {
			$arr = [];
			if (($idUser = @$l[$idUserUpd] + 0)) {
				if (!array_key_exists($idUser, $isUsers)) $isUsers[$idUser] = $this->conn->fastValue("SELECT d.Nome FROM db_Secure.tb_Users_Detail d WHERE d.idUser=$idUser");
				$arr['_'] = array('idUser' => $idUser, 'User' => $isUsers[$idUser],);
			}

			foreach ($l as $k => $v) if (!array_key_exists($k, $keys) && $v != @$line[$k]) $arr[$k] = $l[$k];
			if ($arr) $out[$l[$dtUpdateField]] = $arr;
			$line = $l;
		}
		return array_reverse($out);
	}
	public function bootstrap_row($args = false) { //false | array(['label']=>'campo,campo...')
		$tab = "\t";
		/*
                <div class="row">
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                  <div class="col-md-1">.col-md-1</div>
                </div>
                */
	}
	public function container($dobleButtons = false, $print = true) {
		$out = $this->htmlBeforeContainer;
		$out .= '<div class="container">';
		if ($this->found || $this->C) {
			if ($dobleButtons) $out .= "$this";
			$out .= $this($this->html . $this->getFormTable('100%'), false);
		} else $out .= $this();
		$out .= '</div>';
		if ($print) {
			print $out;
			return $this;
		}
		return $out;
	}
	public function show($dobleButtons = false, $print = true) {
		$out = $this->htmlBeforeContainer;
		if ($this->found || $this->C) {
			if ($dobleButtons) $out .= "$this";
			$out .= $this($this->html . $this->getFormTable('100%'), false);
		} else $out .= $this();
		if ($print) {
			print $out;
			return $this;
		}
		return $out;
	}
	static public function byData($obj, $preStartMethods = null, $preStartAttrs = null, $force = false) {
		$arr = [
			'nav' => null,
			'db' => null,
			'tbl' => null,
			'key' => null,
			'frm' => null,
			'frmMethods' => null,
			'frmAttrs' => null,
		];
		if (is_object($obj)) {
			$c = get_class($obj);
			if ($c == 'tblDataTab') {
				return self::byData(@$obj->tabs[$obj->tabActived], $preStartMethods, $preStartAttrs, $force);
			}
			if ($c == 'tblDataList') {

				($url = $obj->url) || ($url = '?');
				if (!$force && (!$obj->key || $url != '?')) return;
				//show([$obj->db,$obj->database]);
				$a = [];
				foreach ($arr as $k => $v) if ($obj->$k) $a[$k] = $obj->$k;
				if (!array_key_exists('tbl', $a)) {
					($a['tbl'] = @$obj->tb) || ($a['tbl'] = $obj->view);
				}
				return self::byData($a, $preStartMethods, $preStartAttrs, $force);
			}
			return;
		}
		$frm = __CLASS__;
		$frm = new $frm;
		if (!is_array($obj)) return $frm;
		foreach ($arr as $k => $v) if ((!array_key_exists($k, $obj) || !$obj[$k])) $obj[$k] = @$GLOBALS[$k];

		$fnAttrs = function (&$frm, $frmAttrs) {
			$frmAttrs = (array)$frmAttrs;
			foreach ($frmAttrs as $k => $v) $frm->$k = $v;
		};
		$fnMethods = function (&$frm, $frmMethods) {
			$frmMethods = (array)$frmMethods;
			foreach ($frmMethods as $k => $item) {
				if (is_numeric($k)) {
					if (is_string($item)) $frm->$item();
				} else call_user_func_array([$frm, $k], (array)$item);
			}
		};

		$fields = (array)(array_key_exists($key = 'frm', $obj) ? $obj[$key] : null);
		$fnAttrs($frm, $preStartAttrs);
		$fnAttrs($frm, array_key_exists($key = 'frmAttrs', $obj) ? $obj[$key] : null);
		$fnMethods($frm, $preStartMethods);
		$fnMethods($frm, array_key_exists($key = 'frmMethods', $obj) ? $obj[$key] : null);
		if (!array_key_exists('tbl', $obj) || !$obj['tbl']) {
			($obj['tbl'] = @$obj['tb']) || ($obj['tbl'] = $obj['view']);
		}
		$arrAttrs = ['db', 'key', 'nav', 'frm', 'tbl',];
		foreach ($arrAttrs as $k) if (array_key_exists($k, $arr)) $frm->$k = $obj[$k];
		$frm->start();
		foreach ($fields as $v) $frm->addFormTable($v);

		return $frm;
	}
}
