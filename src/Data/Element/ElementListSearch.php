<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\Cache\Config;

class ElementListSearch extends ElementSearch {
	private $numOrder = 0;
	private $varsTmp = array();
	protected $typeList = array('listsearch');

	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'list_search';
		$config = Config::singleton();
		$this->site = "{$config->root}{$config->site}";
		$this->protect['tbl'] = '';
		$this->protect['order'] = '';
		$this->protect['key'] = array();
		$this->protect['isPesquisa'] = true;
		parent::__construct($name, $value, $id);
		$this->keyArgs = array();
		$this->showNumOrder = true;
		$this->showFields = '';
		$this->max = 0;
		$this->name = '';
		$this->isDelete = true;
		$this->updated = true;
	}
	function makeContent() {
		return $this->makeControl();
	}
	function buildKeyMain() {
		if (!@$this->varsTmp['key']) $this->varsTmp['key'] = $this->trArray($this->protect['key']);
		return $this->varsTmp['key'];
	}
	function buildWhereSqlMain() { //construindo where Main
		if (!@$this->varsTmp['where']) {
			$this->where = array();
			$this->objs = array();
			$this->args = array();
			$this->varsTmp['select'] = $this->parserWhereGetFields($this->buildKeyMain());
			$this->varsTmp['where'] = implode(" AND ", $this->where);
		}
		return $this->varsTmp['where'];
	}
	function parserWhereGetFields($array, $content = false, $key = false) {
		$select = array();
		foreach ($array as $k => $v) {
			if (is_array($v)) $this->parserWhereGetFields($v, $content, $k);
			else {
				if ($key) $k = $key;
				if (isset($content[$k])) $value = $content[$k];
				else {
					$value = @$this->form->fields[$k]->value == '' ? @$this->form->key[$k] : $this->form->fields[$k]->value;
					if (isset($this->form->fields[$k])) {
						$id = $this->form->fields[$k]->id;
						$this->conditions['objs'][$v][$id] = $id;
						$this->conditions['args']['key'][$v] = $v;
					}
				}
				$select[$v] = "'{$this->conn->escape_string($value)}' as `$v`";
				$this->where[] = "`$v`='{$this->conn->escape_string($value)}'";
			}
		}
		return $select;
	}
	function buildSqlMain() { //cconstruindo SQL Main
		$order = "";
		$where = $this->buildWhereSqlMain();
		if ($this->protect['order']) {
			$order = " ORDER BY {$this->protect['order']}";
			$where .= " AND {$this->protect['order']}>0";
		}
		return "SELECT * FROM {$this->protect['tbl']} tbl WHERE $where$order";
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$args = $this->args;
		//print "<pre>".print_r($args,true)."</pre>";
		if (!(@$this->protect['tbl']) || !@$this->protect['key'] || !@$args['view'] || !@$args['key']) return;
		if ($this->isEdit()) {
			$this->methods['rebuild'] = null;
		}

		$args['key'] = $this->trArray($args['key'], true);
		$args['getCells'] = $this->trArray(@$args['getCells'], true);
		if ($this->showFields) $this->showFields = $this->trArray($this->showFields, true);
		elseif (@$args['getCells']) $this->showFields = $args['getCells'];
		else $this->showFields = $args['key'];
		$args['getCells'] = implode(',', array_unique(array_merge($args['getCells'], $args['key'], $this->showFields)));

		//Captura o Conn Main
		//$this->buildFunctions();
		$this->connArg = @$args['conn'];
		$this->getConn($this->conn);
		$this->getConn($this->connArg);
		if (!$this->conn || !$this->conn) return;
		if ($this->db && $this->conn->db != $this->db) $this->conn->select_db($this->db);
		if (@$args['db']) $this->connArg->select_db($args['db']);

		$args['conn'] = @$this->connArg->dsn['dsnName'];
		if (@$this->connArg->db && $this->connArg->db != @$this->connArg->dsn['db']) $args['db'] = $this->connArg->db;

		$sql = $this->buildSqlMain();

		//construindo SQL Arg
		$sqlArg = "SELECT * FROM ({$args['view']}) as t";

		$cabNumOrder = $this->showNumOrder ? "<th>N</th>" : "";
		$attr = $this->makeAttrib(array('class' => $this->type)) . $this->makeAttribInput();
		$out = "\n<table{$this->makeHtmlAttrId()}{$attr}{$this->buildStyles()} border='0' cellspacing='0' cellpadding='0'>\n";
		$out .= "\t<thead><tr>{$this->makeCheckButton('', 'true')}$cabNumOrder{$this->buildTableHead($this->showFields)}</tr></thead>\n";
		$out .= "\t<tbody>\n";
		$res = $this->conn->query($sql);
		$even = '';
		while ($l = $res->fetch_assoc()) {
			$inputs = '';
			if ($this->isEdit()) foreach ($args['key'] as $k => $v) {
				$value = htmlspecialchars($l[$k], ENT_QUOTES);
				$inputs .= "<input type='hidden'{$this->makeHtmlAttrName("[$k][]")} value='$value' />";
			}
			$even = $even == 'par' ? 'impar' : 'par';

			$this->where = array();
			$this->parserWhereGetFields($args['key'], $l);
			$where = implode(" AND ", $this->where);
			$sql = "$sqlArg \nWHERE " . implode(" AND ", $this->where);
			$resArg = $this->connArg->query($sql);
			$htmlLine = '';
			if ($lArg = $resArg->fetch_assoc()) {
				$error = '';
				foreach ($this->showFields as $v) {
					($value = $this->func(@$lArg[$v], $v, $lArg, true)) != '' || ($value = "&nbsp;");
					$htmlLine .= "<td>$value</td>";
				}
			} else {
				foreach ($this->showFields as $v) {
					($value = $this->func(@$l[$v], $v, $l, true)) != '' || ($value = "&nbsp;");
					$htmlLine .= "<td>$value</td>";
				}
				$error = " id='error'";
			}
			$resArg->close();
			$out .= "\t\t<tr$error class='$even'>{$this->makeCheckButton($inputs)}{$this->makeNumOrder()}$htmlLine</tr>\n";
		}

		$res->close();
		$out .= "\t</tbody>{$this->editLine(count($this->showFields) + 1 + ((int)$this->isCheckButton()))}</table>\n";

		$this->showFields = array_values($this->showFields);
		$this->objs = false;
		$this->keyArgs = $args['key'];
		$args['view'] = preg_replace("/\"/", '\\"', $args['view']);
		unset($args['key']);
		$args['site'] = $this->site;
		$this->args = $args;

		return '<div class="alert alert-light" role="alert">'.$out.'</div>';
	}
	function buildId($pre) {
		return " id='$pre{$this->id}'";
	}
	function buildTableHead($fields) {
		return "<th>" . implode("</th><th>", array_keys($fields)) . "</th>";
	}
	function editLine($nCols) {
		if ($this->isEdit()) return "
	<tfoot>
		<tr><td colspan='$nCols'>
			<table width='100%' border='0' cellspacing='0' cellpadding='0'>
				<tr>
					<td width='100%'>{$this->makeSearchButton()}{$this->makePositionButton()}</td>
					<td width='1'>{$this->makeDeleteButton()}</td>
				</tr>
			</table>
		</td></tr>
	</tfoot>
		";
	}
	function makeNumOrder() {
		if (!$this->showNumOrder) return;
		$this->numOrder++;
		return "<td class='numOrder'>{$this->numOrder}</td>";
	}
	function makeCheckButton($inputs = '', $all = 'false') {
		if ($this->isCheckButton()) {
			$tag = $all == 'false' ? 'td' : 'th';
			return "<$tag width='1'><input class='checkbox' type='checkbox' onclick='{$this->id}.check(this,$all)' />$inputs</$tag>";
		}
	}
	function isCheckButton() {
		return $this->isEdit() && ($this->isDelete || $this->order);
	}
	function makeSearchButton() {
		if (!$this->isPesquisa) return '';
		$events = $this->makeEvents(array('onclick' => "{$this->id}.show();"));
		return "<input id='{$this->id}_search' value='Pesquisa' type='button' $events disabled />";
	}
	function makePositionButton() {
		if (!$this->order) return;
		$setas = array('Up' => 'ñ', 'Dn' => 'ò');
		$out = '&nbsp;';
		foreach ($setas as $ori => $seta) {
			$out .= "<input id='{$this->id}_set$ori' class='position' value='$seta' type='button' onclick='{$this->id}.position$ori()' disabled />";
		}
		return $out;
	}
	function makeDeleteButton() {
		if (!$this->isDelete) return;
		return "<input id='{$this->id}_erase' value='Excluir' type='button' onclick='{$this->id}.erase()' disabled />";
	}
	public function update($data = null) {
		if ($this->readonly) return true;
		$action = $this->action;
		$this->getConn($this->conn);
		//print $this->form->action&3?"OK<br>":"NO<br>";
		if (!$this->isEdit() || !$this->conn || !$this->protect['tbl'] || !$this->protect['key']) return false;
		$args = $this->args;
		$where = $this->buildWhereSqlMain();
		if (!$where) return false;
		if ($action & 4) { //Deleting
			$sql = "DELETE FROM {$this->protect['tbl']} WHERE {$where}";
			$this->conn->query($sql, false);
		} else {
			//print "MANUTENÇÃO: <pre>".print_r($data,true)."</pre>";
			$selectAll = array();
			$keyView = @array_keys(current($data));
			$selectKey = $this->varsTmp['select'];
			$insertKey = $this->where;

			if ($this->protect['order']) {
				$sqlFim = "DELETE FROM {$this->protect['tbl']} WHERE {$where} AND `{$this->protect['order']}`<1";
				$this->conn->query($sqlFim, false);
				$sqlIni = "UPDATE {$this->protect['tbl']} SET `{$this->protect['order']}`=(-1*ABS(`{$this->protect['order']}`)) WHERE {$where}";
			} else {
				$sqlIni = "DELETE FROM {$this->protect['tbl']} WHERE {$where}";
				$sqlFim = "";
			}
			//print "keyView:<pre>".print_r($keyView,true).";</pre>";
			//print "INI:<pre>$sqlIni</pre>";
			//print __LINE__.": MANUTENÇÃO<br>";
			$this->conn->query($sqlIni);
			//$this->conn->commit();
			//update/insert
			if ($data) foreach ($keyView as $k) {
				$select = $selectKey;
				$update = $insert = $insertKey;
				$item = $k + 1;
				if ($this->protect['order']) {
					$insert[] = "`{$this->protect['order']}`=$item";
					$select[$this->protect['order']] = "$item as `{$this->protect['order']}`";
				}
				foreach ($data as $field => $listValue) {
					$value = $this->conn->escape_string($listValue[$k]);
					$select[$field] = "'$value' as `$field`";
					$w = "`$field`='$value'";
					$insert[] = $w;
					$update[] = $w;
				}
				$selectAll[] = "SELECT " . implode(", ", $select);
				if ($sqlFim) {
					$sqlMid = "UPDATE {$this->protect['tbl']} \nSET " . implode(", ", $insert) . " \nWHERE " . implode(" AND ", $update);
					//print "MID:<pre>$sqlMid</pre>";
					$this->conn->query($sqlMid, false);
				}
			}
			//$this->conn->commit();
			if ($sqlFim) {
				//print "FIM:<pre>$sqlFim;</pre>";
				$this->conn->query($sqlFim);
			}
			if ($selectAll) {
				$selectAll = implode(" UNION ALL \n", $selectAll);
				$join = $w = array();
				foreach ($select as $k => $v) {
					$join[] = "s.`$k`=t.`$k`";
					$w[] = "t.`$k` IS NULL";
				}
				$join = implode(" AND ", $join);
				$w = implode(" AND ", $w);
				$sqlIns = "
				INSERT IGNORE {$this->protect['tbl']} (`" . implode("`,`", array_keys($select)) . "`)
				SELECT s.* FROM (
					$selectAll
				) s
				LEFT JOIN {$this->protect['tbl']} t ON $join
				WHERE $w
				";
				//print "INS:<pre>$sqlIns;</pre>";
				$this->conn->query($sqlIns, false);
			}
		}
		$this->conn->commit();
		$this->args = $args;
		return true;
	}
}
