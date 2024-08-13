<?php

namespace EstaleiroWeb\ED\DB\Tools;

use EstaleiroWeb\Cache\Config;
use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\IO\Vault;

# Autor: Helbert Fernandes
# Descrição: Ferramenta easyView responsável pelo tratamento de views dentro do MySQL
#
# Histórico:
# Data: 18/02/2005 16:00 - Helbert Fernandes: Criação a partir da reunião e organização de outros arquivos

class EasyView {
	private $protect = array(
		'conn' => false,
		'view' => '',
		'sp' => '',
		'spfull' => '',
		'result' => array(),
		'parameters' => array(),
		'error' => false,
		'details' => array(),
		'alias' => array()
	);
	private $img = array('dbs' => 'dbsIcon.gif', 'db' => 'dbIcon.gif', 'T' => 'tblsIcon.gif', 'tbT' => 'tblIcon.gif', 'V' => 'vwIcon.gif', 'tbV' => 'vwsIcon.gif', 'fd' => 'fldIcon.gif', 'SQL' => 'sqlIcon.gif', 'Ev' => 'evIcon.gif');
	private $fnView, $fnSQL, $tbView, $tbSQL;
	function __construct($dsn = array(), $view = '') {
		if ($dsn) $this->dsn = $dsn;
		$this->view = $view;
		if ($view) $this->exec();
	}
	function __get($var) {
		return isset($this->protect[$var]) ? $this->protect[$var] : null;
	}
	function __set($var, $value) {
		switch ($var) {
			case 'dsn':
			case 'conn':
				$var = 'conn';
				if (is_string($value)) {
					$dsn = new Vault();
					$value = $dsn($value);
				}
				if (is_array($value)) $value = $this->prepare($value, true);
				elseif (is_object($value) && @$value->dsn) $this->prepare($value->dsn, true);
				else {
					$this->tbView = 'db_System';
					$this->fnView = 'getView';
					$this->tbSQL = 'db_System.tb_Data_SQL';
					$this->fnSQL = 'getSQL';
				}
				break;
			case 'view':
				$this->close();
				$value = $this->trim($value);
				$this->protect['sp'] = '';
				$this->protect['result'] = array();
				$this->protect['parameters'] = array();
				$this->protect['error'] = false;
				$this->protect['details'] = array();
				$this->protect['alias'] = array();
				break;
			default:
				if (preg_match('/^extract\w+/', $var)) $this->protect[$var] = $value;
				return;
		}
		$this->protect[$var] = $value;
		$this->dataOk();
	}
	function prepare($dsn, $connect = false) {
		if (isset($dsn['easyview']) && $dsn['easyview']) {
			$this->tbView = $dsn['easyview'];
			$this->fnView = 'getView';
		} else {
			$this->tbView = '';
			$this->fnView = 'getViewEmpty';
		}
		if (isset($dsn['easysql']) && $dsn['easysql']) {
			$this->tbSQL = $dsn['easysql'];
			$this->fnSQL = 'getSQL';
		} else {
			$this->tbSQL = '';
			$this->fnSQL = 'getSQLEmpty';
		}
		if ($connect) return $this->protect['conn'] = Conn::dsn($dsn);
	}
	private function dataOk() {
		if (!$this->conn || !$this->view) return;
		$this->protect['sp'] = $this->replaceVar($this->query($this->protect['view']));
		$this->protect['spfull'] = $this->getSpFull();
	}
	function getSpFull() {
		$er = "/^{$this->getErTblAlias()}$/i";
		$spOld = $this->protect['sp'];
		while ($spOld != ($sp = preg_replace('/(\"|\')(.*?);(.*?)\1/', '\1\2' . chr(254) . '\3\1', $spOld))) $spOld = $sp;
		$sp = preg_split('/\s*;\s*/', $sp);
		foreach ($sp as &$q) {
			$q = trim($q);
			if (preg_match('/^show/', $q)) continue;
			if (preg_match($er, $q)) $q = "SELECT * FROM $q";
			elseif (preg_match('/\s+union\s+/i', $q)) $q = "SELECT * FROM ($q) as t";
		}
		return str_replace(chr(254), ";", implode(";\n", $sp));
	}
	private function getErTbl() {
		return '(?:`([^`]+)`|(\w+))';
	}
	private function getErTblAlias() {
		$c = $this->getErTbl();
		return "(?:$c\.)?$c(?:\s+(?:as\s+)?$c)?";
	}
	private function query($view = '', $ciclo = array()) {
		$db = $tbl = $alias = '';
		$fnView = $this->fnView;
		$fnSQL = $this->fnSQL;

		$c = $this->getErTbl();
		$erAlias = $this->getErTblAlias();
		$view = preg_replace('/\[(\w+)\]/', '\1', $view);
		if (!preg_match_all("/^$erAlias$/i", $view, $ret)) {
			if (!preg_match_all("/(?<=\sfrom|\sjoin|\sstraight_join|call)[\s\(]+(?!select)(?:(?:$c\.)?$c(?:\s+(?!union|where|on|select|group|having|order|limit|procedure|(?:inner\s+|cross\s+|straight_|left\s+)?join)(?:as\s+)?$c)?(?:\s*,\s*)?)+/i", $view, $ret)) {
				return $view;
			}
		}

		//print "<pre style='font-size:x-small;'>".print_r($view,true)."</pre><hr>";
		foreach ($ret[0] as $v) {
			$subst = $v;
			preg_match_all("/$erAlias/i", $v, $t);
			foreach ($t[0] as $k => $grp) {
				extract($this->joinVwName($t, $k));
				if (!$db && ($sql = $this->$fnSQL($tbl)) && $sql != $alias && !isset($ciclo[$tbl])) {
					$c = $ciclo;
					$c[$tbl] = $alias;
					$sql = $this->query($sql, $c);
					if (preg_match_all("/^$erAlias$/i", $sql, $ret)) {
						$ret = $this->joinVwName($ret, 0);
						$alias = $this->getAlias($ret['db'], $ret['tbl'], $ret['alias']);
						$sql = ($ret['db'] ? "`{$ret['db']}`." : "") . "`{$ret['tbl']}`";
						$subst = str_replace($grp, "$sql as `$alias`", $subst);
					} else {
						if ($ciclo || preg_match('/\s*select\s+/i', $view)) { // && $bView
							$alias = $this->getAlias($db, $sql, $alias ? $alias : $tbl);
							$sql = "(\n$sql\n) as `$alias`";
						}
						$subst = str_replace($grp, $sql, $subst);
					}
				} else {
					$alias = $this->getAlias($db, $tbl, $alias);
					if (!($this->$fnView($db, $tbl, $alias))) $this->consolida($tbl, $db, false);
				}
			}
			$view = str_replace($v, $subst, $view);
		}
		return $view;
	}
	private function joinVwName($t, $k) {
		return array(
			'db' => $t[1][$k] ? $t[1][$k] : $t[2][$k],
			'tbl' => $t[3][$k] ? $t[3][$k] : $t[4][$k],
			'alias' => $t[5][$k] ? $t[5][$k] : $t[6][$k]
		);
	}
	private function getAlias($db = '', $tbl = '', $alias = '') {
		if ($tbl) {
			if (!$db) $db = $this->conn->db;
			if (!$alias) $alias = $tbl;
			if (isset($this->protect['alias'][$alias])) $alias = "{$tbl}_" . count($this->protect['alias'][$alias]);
			$this->protect['alias'][$alias] = array('db' => $db, 'tbl' => $tbl);
		}
		return $alias;
	}
	private function getView($db = '', $tb = '') {
		$conn = $this->conn;
		if (!$db) $db = $conn->db;
		$key = "`$db`.`$tb`";
		if (isset($this->protect['details'][$key])) return true;
		$tb = $conn->escape_string($tb);
		$sql = "SELECT * FROM {$this->tbView}.tb_Data_View as vw WHERE Name='$tb'";
		if ($db) {
			$db = $conn->escape_string($db);
			$sql .= " AND `Schema`='$db'";
		}
		$res = $conn->query($sql);
		if ($conn->error) {
			show("$sql\n$conn->error");
			exit;
		}
		if ($res->num_rows == 0) {
			$this->protect['details'][$key] = array();
			return false;
		}
		if ($res->num_rows != 1) die("Erro no numero de registros em $sql");
		$line = $res->fetch_assoc();
		$this->protect['details'][$key] = array('view' => $line, 'fields' => array());
		$sql = "SELECT * FROM {$this->tbView}.tb_Data_Fields as fl WHERE idDataView={$line['idDataView']}";
		$res = $conn->query($sql);
		if ($conn->error) {
			show("$sql\n$conn->error");
			exit;
		}
		while ($line = $res->fetch_assoc()) $this->protect['details'][$key]['fields'][$line['FieldName']] = $line;
		return true;
	}
	private function getSQL($tb = '') {
		$conn = $this->conn;
		$tb = $conn->escape_string($tb);
		$sql = "SELECT * FROM {$this->tbSQL} as vw WHERE ViewId='$tb'";
		$res = $conn->query($sql);
		if ($res->num_rows == 0) return '';
		$line = $res->fetch_assoc();
		$this->protect['parameters'][$tb] = @$line['Parameters'];
		return $this->trim($this->replaceVar($line['StrSql']));
	}
	private function trim($txt) {
		return preg_replace(array('/^\s+/', '/\s+$/'), '', $txt);
	}
	private function getViewEmpty($db = '', $tb = '', $alias = '') {
		return array();
	}
	private function getSQLEmpty($tb = '') {
		return '';
	}
	private function replaceVar($text) {
		extract($GLOBALS, EXTR_SKIP);
		extract($_REQUEST, EXTR_SKIP);
		extract($_COOKIE, EXTR_SKIP);
		extract($_SERVER, EXTR_SKIP);
		extract($_FILES, EXTR_SKIP);
		$ret = @preg_replace(
			array('/\s*;\s*$/', '/\\{[\\$&](.+?)\\}/e', "/(\\$\w+)/e"),
			array("", "'\"'.eval('return \$\\1;').'\"'", "'\"'.eval('return \\1;').'\"'"),
			$text
		);
		if ($ret !== $text) $ret = $this->replaceVar($ret);
		return $ret;
	}
	function exec() {
		if (!$this->protect['sp']) return;
		$this->protect['result'] = array();
		$__result = false;
		$conn=$this->conn;
		if ($conn->multi_query($this->protect['sp'])) do {
			$this->protect['result'][] = $__r = $conn->store_result();
			if ($__r) $__result = $__r;
		} while ($conn->next_result());
		return $__result;
	}
	function close() {
		foreach ($this->protect['result'] as $res) $res->close();
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	function consolida($view = '', $db = '', $deleteOld = true) {
		if (!$this->conn || !$this->tbView) return;
		$conn = $this->conn;
		@ini_set('max_execution_time', 500000);
		$aDispForm = array("cgc" => '"00.000.000/0000-00","_"', "cnpj" => '"00.000.000/0000-00","_"', "cpf" => '"000.000.00-00","_"', "cep" => '"000000-000","_"');
		$aMinMaxInt = array('tinyint' => 127, 'smallint' => 32767, 'mediumint' => 8388607, 'int' => 2147483647, 'integer' => 2147483647, 'bigint' => 923372036854775807, 'decimal' => 923372036854775807, 'double' => 1.7977e+308, 'float' => 3.4029e+38);
		$aValdForm = array('cgc' => 'validateCnpj', 'cnpj' => 'validateCnpj', 'cpf' => 'validateCpf', 'mail' => 'validateEmail');
		$aValdkey = array('cgc' => '/\d/', 'cnpj' => '/\d/', 'cpf' => '/\d/', 'mail' => '/\w|\.|@|-/', 'fone' => '/[- ()0-9r+]/', 'fax' => '/[- ()0-9r+]/', 'tel' => '/[- ()0-9r+]/', 'celular' => '/[- ()0-9r+]/');
		$maxViewWidth = $this->consolidaGetDefaulValue($this->tbView, 'tb_Data_Fields', 'ViewWidth');
		$maxFormWidth = $this->consolidaGetDefaulValue($this->tbView, 'tb_Data_Fields', 'EditWidth');

		if ($deleteOld) {
			$conn->query("
				DELETE `{$this->tbView}`.`tb_Data_View` FROM `{$this->tbView}`.`tb_Data_View`
				LEFT JOIN `{$this->tbView}`.`vw_inf_Views`
				ON `tb_Data_View`.`Schema`=`vw_inf_Views`.`SCHEMA`
				AND `tb_Data_View`.`Name`=`vw_inf_Views`.`NAME`
				WHERE `vw_inf_Views`.`SCHEMA` IS NULL
			");
			$conn->query("
				DELETE `{$this->tbView}`.`tb_Data_Fields` FROM {$this->tbView}.`tb_Data_Fields` 
				LEFT JOIN `{$this->tbView}`.`vw_inf_Fields` 
				ON `tb_Data_Fields`.`idDataView`=`vw_inf_Fields`.`idDataView`
				AND `tb_Data_Fields`.`FieldName`=`vw_inf_Fields`.`FieldName`
				WHERE `vw_inf_Fields`.`FieldName` is NULL
			");
		}
		if ($view) {
			$where = " WHERE `NAME`='{$conn->escape_string($view)}' AND `SCHEMA`='{$conn->escape_string($db ?$db :$conn->db)}'";
		} else $where = '';

		$conn->query("
			INSERT IGNORE INTO `{$this->tbView}`.`tb_Data_View` (`Schema`,`Name`,`Label`,`ViewType`)
			SELECT `SCHEMA`, `NAME`, `NAME` AS `Label`, `Type`
			FROM `{$this->tbView}`.`vw_inf_Views` as v$where
		");

		$res = $conn->query("
			SELECT f.* FROM `{$this->tbView}`.`vw_inf_Fields` as f 
			JOIN `{$this->tbView}`.`tb_Data_View` as v ON f.idDataView=v.idDataView$where
		");
		while ($line = $res->fetch_assoc()) {
			$lform = array();
			//$lform["TabIndex"]=0;//[opcional]
			//$lform["ViewFilter"]=1;//[opcional]
			//$lform["ViewFunction"]=$line["ViewFunction"];
			//$lform["ValueFilter"]=$line["ValueFilter"];
			$lform["idDataView"] = $line["idDataView"];
			$lform["FieldName"] = $line["FieldName"];
			$lform["Label"] = $line["FieldName"];
			$lform["Type"] = $line["Type"];
			$lform["Key"] = $line["Key"];
			$lform["Title"] = $line["Comment"];
			$lform["Value"] = $line["ValueDefault"];
			$lform["Source"] = $line["SourceList"];
			$lform["Requeried"] = $line["Requeried"];
			$lform["Unsigned"] = $line["Unsigned"];
			$lform["ZeroFill"] = $line["ZeroFill"];
			$lform["Auto_increment"] = $line["Auto_increment"];

			$lform["Disabled"] = $line["FieldName"] == 'hash' || $line["Auto_increment"] ? 0 : 1;
			$lform["Form_Hidden"] = $line["FieldName"] == 'hash' || $line["Auto_increment"] ? 1 : 0;
			$lform["Align"] = $line["Precision"] === '' ? 'left' : 'right';

			//			$lform["ViewShow"]=$line["ViewShow"];
			//			$lform["ViewHidden"]=$line["ViewHidden"];

			$length = max((int)$line["Length"], (int)$line["Precision"]);
			$lform["EditWidth"] = min($length, $maxFormWidth);
			$lform["ViewWidth"] = min($length, $maxViewWidth);

			$lform["ValidateField"] = '';
			$lform["ValidateKey"] = '';

			switch ($line["Type"]) {
				case 'datetime':
				case 'timestamp':
					$lform["Element"] = 'Calendar';
					$lform["DisplayFormat"] = '"%x %X","_"';
					$lform["EditWidth"] = $lform["ViewWidth"] = 19;
					$lform["Min"] = $line["Type"] == 'datetime' ? '1000-01-01 00:00:00' : '1970-01-01 00:00:00';
					$lform["Max"] = $line["Type"] == 'datetime' ? '9999-12-31 23:59:59' : '2037-12-31 23:59:59';
					break;
				case 'date':
					$lform["Element"] = 'Calendar';
					$lform["DisplayFormat"] = '"%x","_"';
					$lform["EditWidth"] = $lform["ViewWidth"] = 10;
					$lform["Min"] = '1000-01-01';
					$lform["Max"] = '9999-12-31';
					break;
				case 'time':
					$lform["Element"] = 'Calendar';
					$lform["DisplayFormat"] = '"%h:%i:%s","_"';
					$lform["EditWidth"] = $lform["ViewWidth"] = 8;
					$lform["Min"] = '00:00:00';
					$lform["Max"] = '23:59:59';
					break;
				case 'year':
					$lform["Element"] = 'Calendar';
					$lform["DisplayFormat"] = '"%Y","_"';
					$lform["EditWidth"] = $lform["ViewWidth"] = 4;
					if ($line["Length"] == 2) {
						$lform["Min"] = '1970';
						$lform["Max"] = '2069';
					} else {
						$lform["Min"] = '1901';
						$lform["Max"] = '2155';
					}
					break;
				case 'text':
				case 'longtext':
				case 'blob':
				case 'longblob':
					$lform["Element"] = 'Text';
					break;
				case 'bit':
					$lform["Element"] = 'Check';
					break;
				case 'tinyint':
				case 'int':
				case 'integer':
				case 'smallint':
				case 'mediumint':
				case 'bigint':
				case 'real':
				case 'decimal':
				case 'dec':
				case 'float':
				case 'double':
					$lform["Element"] = ($line["Length"] == 1 && $line["Type"] == 'tinyint') ? 'Check' : 'Number';
					if ($line["Precision"] != '') $lform["DisplayFormat"] = '"%s","' . $line["Precision"] . ',' . $line["Decimal"] . ',".,"';
					$lform["Min"] = $line["Unsigned"] ? 0 : "-" . ($aMinMaxInt[$line["Type"]] + 1);
					$lform["Max"] = $line["Unsigned"] ? $aMinMaxInt[$line["Type"]] * 2 + 1 : $aMinMaxInt[$line["Type"]];
					break;
				case 'enum':
					$lform["Element"] = 'Combo';
					break;
				case 'set':
					$lform["Element"] = 'List';
					break;
				case 'varchar':
					$lform["Element"] = 'String';
					if (preg_match("/cgc|cnpj|cpf|cep|mail/i", $line["FieldName"], $ret)) {
						if (isset($aDispForm[strtolower($ret[0])])) $lform["DisplayFormat"] = $aDispForm[strtolower($ret[0])];
						if (isset($aValdForm[strtolower($ret[0])])) $lform["ValidateField"] = $aValdForm[strtolower($ret[0])];
						if (isset($aValdkey[strtolower($ret[0])])) $lform["ValidateKey"] = $aValdkey[strtolower($ret[0])];
					}
					break;
			}
			$lform["DegradeCut"] = (int)($length > $lform["ViewWidth"]);
			foreach ($lform as $k => $v) $lform[$k] = $conn->escape_string($v);
			$fields = '(`' . implode('`,`', array_keys($lform)) . '`)';
			$conn->query("INSERT IGNORE INTO `{$this->tbView}`.`tb_Data_Fields` $fields VALUES ('" . implode("','", $lform) . "')");
		}
	}
	private function consolidaGetDefaulValue($db, $tb, $fl) {
		$sql = "SELECT `COLUMN_DEFAULT` FROM `information_schema`.`COLUMNS` WHERE `TABLE_SCHEMA`='$db' AND `TABLE_NAME`='$tb' AND `COLUMN_NAME`='$fl'";
		$conn = $this->conn;
		$res = $conn->query($sql, false);
		if ($conn->error || !$res->num_rows) return 0;
		$line = $res->fetch_row();
		return $line[0];
	}

	//Packge
	function getAllHosts() {
		$dsn = new Vault;
		$allDsn = $dsn->contents();
		$dftDsn = $dsn();
		$strDftDsn = implode("\t", $dftDsn);
		$hostDefault = '';
		foreach ($allDsn as $k => $d) if (implode("\t", $d) == $strDftDsn) {
			$hostDefault = $k;
			break;
		}
		if ($hostDefault == '') $allDsn = array_merge(array("" => $dftDsn), $allDsn);
		if (!isset($_REQUEST['host'])) $_REQUEST['host'] = $hostDefault;
		$this->protect['tb_SQL'] = $allDsn[$_REQUEST['host']]['easysql'];
		$this->protect['tb_Cat'] = $allDsn[$_REQUEST['host']]['easycat'];
		$this->protect['db_View'] = $allDsn[$_REQUEST['host']]['easyview'];
		return $allDsn;
	}
	function getConn($dsn) {
		$this->protect['conn'] = Conn::dsn($_REQUEST['host']);
	}
	function makeGrp($text = '', $content = '', $icon = '', $status = false, $p1 = '', $p2 = '') {
		$img = $this->getImg($icon);
		$class = $status ? 'open' : 'close';
		$style = $status ? '' : " style='display:none;'";
		return "<div id='$p1,$p2'><div class='grptitle'><div class='$class' onclick='openClose(this,\"$p1\",\"$p2\")'></div><div class='title'>$img$text</div></div><div class='content'$style>$content</div></div>";
	}
	function makeItem($content = '', $exe = '', $icon = '', $cat = false, $id = '', $class = 'item') {
		$img = $this->getImg($icon);
		$catClass = array();
		$strCat = '';
		if ($exe) {
			$exe = " onclick=\"$exe\" onmouseover='changeMouse(this,true)' onmouseout='changeMouse(this,false)'";
			if ($id) $id = " id='$id'";
		}
		if ($cat !== false) {
			$cat = trim($cat);
			$catClass[] = "cat$cat";
			if ($cat && $class) $strCat = "<span class='cat'>($cat)</span>";
		}
		if ($class) $catClass[] = $class;
		$catClass = implode(' ', $catClass);
		return "<div$id class='$catClass'$exe>$img$content$strCat</div>\n";
	}
	function getImg($id = '') {
		$img = $this->img;
		$config = Config::singleton();
		if (!isset($img[$id])) return "";
		return "<img src='{$config->host}{$config->imgs}/icons/{$img[$id]}' />";
	}
	function execSql($sql) {
		$conn = $this->conn;
		$res = $conn->query($sql);
		return $res;
	}
	function getCateg($categ) {
		//Monta select categorias
		$tb_SQL = $this->tb_SQL;
		$tb_Cat = $this->tb_Cat;
		$db_View = $this->db_View;
		if (!$tb_Cat) return '';
		$selectCat = '';
		$sql = "SELECT " . ($db_View ? "idGroup, Cat,`Group`" : "CatId as idGroup, CatId as Cat, Descr as `Group`") . " FROM $tb_Cat as cat ORDER BY `Cat`";
		$res = $this->execSql($sql);
		while ($line = $res->fetch_assoc()) {
			$selectCat .= "\t<option value='{$line['idGroup']}'" . ($categ == $line['idGroup'] ? " selected" : "") . ">{$line['Cat']}</option>\n";
		}
		return $selectCat;
	}
	function getOptionsSelect($itens, $key) {
		$selectCat = '';
		foreach ($itens as $k => $v) $selectCat .= "\t<option value='$k'" . ($k == $key ? " selected" : "") . ">$v</option>\n";
		return $selectCat;
	}
}
