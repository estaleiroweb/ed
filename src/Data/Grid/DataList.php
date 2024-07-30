<?php

/*
 * Autor: Helbert Fernandes
 * Descrição: Classe Pai Data para manipulação de conjunto de dados
 *
 * Histórico:
 * Data: 06/07/2005 22:00 - Helbert Fernandes
 */
/*
 * //SQL_CALC_FOUND_ROWS
 * //SELECT FOUND_ROWS()
 *
 * //SQL_BUFFER_RESULT
 * //STRAIGHT_JOIN
 * //PROCEDURE ANALYSE([max_elements default 256,[max_memory default 8192]])
 */
/*
 * SELECT
 * [ALL | DISTINCT | DISTINCTROW ] [HIGH_PRIORITY] [STRAIGHT_JOIN] [SQL_SMALL_RESULT] [SQL_BIG_RESULT] [SQL_BUFFER_RESULT] [SQL_CACHE | SQL_NO_CACHE] [SQL_CALC_FOUND_ROWS]
 * select_expr [, select_expr ...]
 * [INTO variable [, variable ...]]
 * [FROM table_references
 * [WHERE where_condition]
 * [GROUP BY {col_name | expr | position} [ASC | DESC], ... [WITH ROLLUP]]
 * [HAVING where_condition]
 * [ORDER BY {col_name | expr | position} [ASC | DESC], ...]
 * [LIMIT {[offset,] row_count | row_count OFFSET offset}]
 * [PROCEDURE procedure_name(argument_list)]
 * [INTO OUTFILE 'file_name' export_options | INTO DUMPFILE 'file_name' | INTO var_name [, var_name]]
 * [FOR UPDATE | LOCK IN SHARE MODE]
 * ]
 */
class DataList extends Data
{

	private $res, $nReg, $nLines, $nPg, $nAll, $regI, $regF;

	protected $varsSessioned = array(
		'fields',
		'order',
		'page',
		'lines',
		'showTable',
		'showFilter',
		'values',
		'width',
		'viewDetail',
		'sqlFull',
		'allFields'
	);

	function __construct($label = false, $conn = false, $view = false)
	{ {
			$this->protect = array_merge($this->protect, array(
				'viewDetail' => null,
				'sqlFull' => null,
				'allFields' => array(), // Todos os campos
				'fields' => null, // [''] Lista de campos a serem mostrados na ordem correta (lstFields)
				// 'fields'=>null,//[array()] Todos os campos da view com seus nomes ('campo1'=>'name','campo2'=>'name','campo3'=>'name')
				'order' => null, // [''] lista de campos do order by
				'page' => 1, // Numero da página ativa
				'lines' => 50, // Numero de linhas a serem mostradas
				'showTable' => null, // [false] Mostra ou não a tabela
				'showFilter' => null, // [true] Exibe ou não a tarja de campos com filtro
				'showRecCount' => null, // [true] Exibe ou não o número total de registros
				'showNavBars' => null, // [true] Exibe ou não o número de linhas e a barra de navegação a serem mostradas (caso false exibirá todas as linhas)
				'showHead' => null, // [true] Mostra ou não o cabeçalho de Filtros
				'function' => null, // [array()] Aplica funções aos campos
				'values' => null, // [array()] Array com a lista de campos com seus valores ('campo1'=>'value','campo2'=>'value','campo3'=>'value')
				'width' => null, // [array()] Array com a lista de campos com suas larguras
				'widthField' => null, // [33em] Largura máxima do campo
				'format' => null, // [array()] Array com a lista de campos com seus valores ('campo1'=>'value','campo2'=>'value','campo3'=>'value')
				'lock' => null, // [array()] bloqueia filtros dos campos com funções ('campo1','campo2','campo3')
				'hiddenFields' => null, // [''] Campos a serem ocultos
				'group' => null, // [''] lista de campos para o grupo
				'groupFields' => null, // [''] lista de campos para amostra o grupo com , count, max, miv, avg, etc
				'limit' => null // [false] Limite inicial
			));
		}
		/*
         * var $elementFilter=array(); //Tipo diferente de elemento para o filtro
         * var $events=array();
         * var $row='';
         * var $getCells='';
         * var $noCut='';
         * var $recordCount=0;
         * var $sql;
         * private $tblDataOutObj;
         */
		parent::__construct($label);
		$this->conn = $conn;
		$this->view = $view;
	}

	function __toString()
	{
		parent::__toString();

		$id = $this->id;
		$this->start();
		if ($this->error)
			return show($this->error);

		$this->inicializeDataList();

		$fields = $this->fields;
		$allFields = $this->allFields;
		$viewDetail = $this->viewDetail;
		$out = '';

		$out .= "<div id='DataList'>\n";
		$out .= "\t<div id='DataList_Flt'{$this->getStyleDisplay($viewDetail['ShowFilter'])}>{$this->filter()}</div>\n";
		if (true || $viewDetail['ShowTable']) {
			$where = $this->where();
			$res = $this->sqlCalc($detailView, $this->session['spfull']);
			/*
             * $recCount=$detailView['recCount'];
             * $LinesView=$detailView['ShowNavBar']?($detailView['LinesView']=max(1,(int)$detailView['LinesView'])):0;
             * $LinesView=$LinesView?$LinesView:$recCount;
             * $pgTotal=$detailView['pgTotal']=ceil($recCount/$LinesView);
             * $pg=$detailView['pg']=max(1,min($pgTotal,$detailView['pg']));
             * $recIni=($pg-1)*$LinesView;
             * $regFim=min($recCount,$recIni+$LinesView);
             * $this->session['detailView']=$detailView;
             * if ($res) {
             * if ($recIni && $detailView['ShowNavBar']) $conn->data_seek($recIni);
             * }else {
             * $this->pr("$view LIMIT $recIni,$LinesView");
             * $res=$conn->query($detailView['ShowNavBar']?"$view LIMIT $recIni,$LinesView":$view);
             * }
             */
			$out .= "\t<div id='DataList_GrpAll'>\n";
			// $out.="\t\t<div id='DataList_Grp'{$this->getStyleDisplay($detailView['GroupBy'])}>{$this->group()}</div>\n";
			$out .= "\t\t<div id='DataList_GDt'>";
			if ($detailView['ShowRecordCount'])
				$out .= "\t\t\t<div id='DataList_Inf'>{$this->information($detailView)}</div>\n";
			$LinesView = $detailView['LinesView'] ? $detailView['LinesView'] : $detailView['recCount'];
			if ($detailView['recCount']) {
				if ($detailView['ShowNavBar']) {
					if ($detailView['recCount'] > min(50, $LinesView))
						$out .= "\t\t\t<div id='DataList_NumList'>{$this->numList($LinesView)}</div>\n";
					if ($detailView['recCount'] > $LinesView)
						$out .= "\t\t\t<div id='DataList_Nav'>{$this->nav()}</div>\n"; // NavBarDataList
				}
				$out .= "\t\t\t<div id='DataList_Dtd'>{$this->grid($res)}</div>\n"; // Grid
			}
			$out .= "\t\t</div>\n\t</div>\n";
			$res->close();
		}
		$out .= "</div>\n";
		//$this->endClass();

		// $out.=$this->showVar($this);
		// $out.=$this->showVar($ev);
		// $out.=$this->showVar($_SESSION);
		// $out.=$this->showVar($viewDetail);
		// $out.=$this->showVar($this->protect['allFields']);
		return $out;
	}

	function start()
	{
		static $done = false;
		if ($done)
			return;
		$done = true;

		$conn = $this->conn;
		$view = $this->view;
		if (!$conn)
			$this->error['conn'] = 'Parametro indefinido';
		if (!$view)
			$this->error['view'] = 'Parametro indefinido';
		if ($this->error)
			return;
		$this->checkDefaultValues();
		if (!$this->protect['viewDetail']) {
			$this->protect['viewDetail'] = new Conn_mysqli_details($conn, $view);
			$this->protect['allFields'] = $this->protect['viewDetail']->fields();
			$this->protect['sqlFull'] = $this->protect['viewDetail']->sqlFull;
			if (!is_array($this->protect['allFields']))
				$this->error['view'] = $this->protect['allFields'];
		}
		if ($this->error)
			return;
		if (!$this->result())
			return;
		if (!$this->protect['allFields'])
			$this->protect['allFields'] = $this->protect['viewDetail']->getFields($this->res);
		// confere fields
	}

	function result($step = 0)
	{
		if (preg_match('/\blimit\s+\d+(\s*,\s*\d+)?(\s|\))*/i', $this->protect['sqlFull'])) {
			$this->protect['sqlFull'] = "SELECT * FROM (\n{$this->protect['sqlFull']}\n) t";
			$step = 1;
		}
		if ($this->protect['showTable']) { // =showTable
			// $value='=aaa!*\&|<>234';$w=$this->protect['viewDetail']->parserCondition($value);print_r($w);exit;
			// print_r($this->protect['viewDetail']->lineDebug());exit;
			// print_r(Conn_mysqli_details::lineDebug());exit;
			// print_r($this->protect['viewDetail']->protect);

			// Monta o WHERE e adciona o order no fim
			$where = $sql = $sql2 = ''; //implementar
			$conn = Conn::dsn(); //implementar
			$ev = new StdClass; //implementar
			// Monta a Query
			if ($where) {
				if (!preg_match("/^select/i", $sql))
					$sql = "$sql2 $where";
				else
					$sql = "SELECT * FROM ($sql) as tblDataList $where";
			} else
				$sql = $ev->spfull;
			if ($this->group) {
				$sql .= " GROUP BY $this->group";
				if ($this->groupFields)
					$sql = preg_replace('/^(select\s+)\*/i', "\\1{$this->groupFields}", $sql);
			}
			$where = $this->protect['viewDetail']->mountWhere($this->protect['values']);
			$sql .= $where;
			if ($this->protect['order'])
				$sql .= "\nORDER BY {$this->protect['order']}";
			$sql .= $this->limit ? "\nLIMIT {$this->limit}" : '';
		} else
			$sql = "{$this->protect['sqlFull']}\nLIMIT 0"; // !showTable
		$this->res = $conn->query($sql, false);
		if ($error = $this->res->error()) {
			if ($step == 1) {
				$this->protect['viewDetail'] = null;
				$this->protect['sqlFull'] = null;
				$this->protect['allFields'] = null;
				$this->error['View'] = $error;
				return false;
			}
			$this->protect['sqlFull'] = "SELECT * FROM (\n{$this->protect['sqlFull']}\n) t";
			return $this->result(1);
		}
		return true;
	}

	function checkDefaultValues()
	{
		$defaultValues = array(
			'fields' => '',
			'order' => '',
			'page' => 1,
			'lines' => 50,
			'showTable' => false,
			'showFilter' => true,
			'showRecCount' => true,
			'showNavBars' => true,
			'showHead' => true,
			'function' => array(),
			'values' => array(),
			'width' => array(),
			'widthField' => '33em',
			'format' => array(),
			'lock' => array(),
			'hiddenFields' => '',
			'group' => '',
			'groupFields' => '',
			'limit' => false
		);
		foreach ($defaultValues as $k => $v) {
			$tmp = $this->$k;
			if (is_null($tmp))
				$this->protect[$k] = $v;
			elseif (gettype($tmp) != gettype($v)) {
				if (is_bool($v))
					$this->protect[$k] = $this->convert2Bool($tmp);
				elseif (is_string($v))
					$this->protect[$k] = $this->convert2String($tmp);
				elseif (is_array($v))
					$this->protect[$k] = $this->convert2Array($tmp);
				else
					$this->protect[$k] = $tmp;
			}
		}
	}

	function grid($res)
	{
		$id = $this->id;
		$detailView = $this->detailView;
		$allFields = $this->allFields;
		$lstHead = $lstField = array();
		$aFld = explode(",", $this->fields);
		$out = "<table border='0' cellspacing='0'>\n";
		$out .= "\t<thead id='DataList_DtdHead'>\n\t\t<tr>\n{$this->gridHead($res,$aFld,$detailView,$allFields,$id)}\t\t</tr>\n\t</thead>\n";
		$out .= "\t<tbody id='DataList_DtdBody'>\n{$this->gridBody($res,$aFld,$detailView,$allFields,$id)}\t</tbody>\n";
		$out .= "</table>\n";
		$box = new Box($out);
		return $box->__tostring();
	}

	function gridHead($res, $aFld, $detailView, $allFields, $id)
	{
		$out = '';
		$order = preg_split('/\s*,\s*/', $detailView['OrderBy']);
		$onmouse = " onmouseover='{$id}.overDtdHead(this)' onmouseout='{$id}.outDtdHead(this)'";
		foreach ($aFld as $k => $v) {
			$k = (int) $k;
			$f = $allFields[$k];
			if ($f['ViewHidden'] || !$f['ViewShow'])
				continue;
			$label = htmlspecialchars($f['Label'], ENT_QUOTES);
			$erName = preg_quote($f['name']);
			$erTable = preg_quote($f['table']);
			if ($ret = preg_grep("/^\s*(?:(`?)$erTable\\1\.)?(`?)$erName\\2(\s+\w)?\s*$/", $order)) {
				$ret = $ret[0];
				$ret = isset($ret[3]) ? strtolower($ret[3]) : '';
				$orderClass = $ret == 'desc' ? $ret : 'asc';
			} else
				$orderClass = '';
			$o = $orderClass == 'desc' ? 0 : 1;
			$title = $f['Title'] ? " alt='{$f['Title']}'" : '';
			$out .= "\t\t\t<th id='DataList_DtdHeadLine$orderClass'><div id='{$id}_fld$k' onclick='orderby($k,$o,event)'$onmouse$title>$label</div></th>\n";
		}
		return $out;
	}

	function gridBody($res, $aFld, $detailView, $allFields, $id)
	{
		$out = '';
		$nReg = 0;
		$order = preg_split('/\s*,\s*/', $detailView['OrderBy']);
		$onmouseLine = " onmouseover='{$id}.over(this)' onmouseout='{$id}.out(this)'";
		$onmouseCell = " onmouseover='{$id}.overDtdCell(this)' onmouseout='{$id}.outDtdCell(this)'";
		while ($line = $res->fetch_row()) {
			$classLine = $nReg++ & 1;
			$out .= "\t\t<tr id='DataList_DtdBodyLine$classLine'$onmouseLine>\n";
			foreach ($aFld as $k => $v) {
				$k = (int) $k;
				$f = $allFields[$k];
				if ($f['ViewHidden'] || !$f['ViewShow'])
					continue;
				$value = @$line[$k];

				if ($f['DisplayFormat']) {
				}

				$value = $value === '' ? "&nbsp;" : $value;
				$out .= "\t\t\t<td><div id='{$id}_fld$k'>$value</div></td>\n";
			}
			$out .= "\t\t</tr>\n";
		}
		return $out;
	}

	function nav()
	{
		$id = $this->id;
		$detailView = $this->detailView;
		if ($detailView['pgTotal'] <= 1)
			return '';
		$first = $previous = $next = $last = "'";
		if ($detailView['pg'] > 1) {
			$first = "Act' title='Vai para primeira página' onclick='{$id}.gotoPage(1)'";
			$previous = "Act' title='Vai para página anterior' onclick='{$id}.gotoPage(" . ($detailView['pg'] - 1) . ")'";
		}
		if ($detailView['pg'] < $detailView['pgTotal']) {
			$next = "Act' title='Vai para página seguinte' onclick='{$id}.gotoPage(" . ($detailView['pg'] + 1) . ")'";
			$last = "Act' title='Vai para última página' onclick='{$id}.gotoPage({$detailView['pgTotal']})'";
		}
		$onmouse = " onmouseover='{$id}.overNav(this)' onmouseout='{$id}.outNav(this)'";
		$botoes = "<div id='DataList_NavGoFirst$first$onmouse></div>";
		$botoes .= "<div id='DataList_NavGoPrevious$previous$onmouse></div>";
		$botoes .= "<div id='DataList_NavText'><input type='text' value='{$detailView['pg']} de {$detailView['pgTotal']}' title='Mostra ou altera a página corrente' onfocus='{$id}.focusNav(this)' onchange='{$id}.gotoPage(this.value)' onkeypress='{$id}.keypressNav(this,event)' onblur='{$id}.blurNav(this)' /></div>";
		$botoes .= "<div id='DataList_NavGoNext$next$onmouse></div>";
		$botoes .= "<div id='DataList_NavGoLast$last$onmouse></div>";
		return "<div id='DataList_NavLeft'></div><div id='DataList_NavMiddle'>$botoes</div><div id='DataList_NavRight'></div>";
	}

	function numList($LinesView)
	{
		$lines = array();
		$lines[$LinesView] = $LinesView;
		$lines['10'] = 10;
		$lines['20'] = 20;
		$lines['30'] = 30;
		$lines['50'] = 50;
		$lines['100'] = 100;
		$lines['200'] = 200;
		$lines['Todos'] = 0;
		asort($lines, SORT_NUMERIC);
		$out = "<span class='preElement'>Mostrar </span>\n<select onChange='{$this->id}.changeNumLines(this)'>\n";
		foreach ($lines as $key => $value)
			$out .= "<option value='$value'" . ($LinesView == $value ? ' selected' : '') . ">$key</option>\n";
		$out .= "<option value='_'>Conf</option>\n</select>\n<span class='posElement'> Registros</span>";
		return $out;
	}

	function information($detailView)
	{
		$s = $detailView['recCount'] > 1 ? 's' : '';
		$range = $detailView['recCount'] > $detailView['LinesView'] ? " (" . ($detailView['recIni'] + 1) . " a " . min($detailView['recCount'], $detailView['recIni'] + $detailView['LinesView']) . ")" : '';
		return "{$detailView['recCount']} Registro$s$range";
	}

	function getStyleDisplay($flag)
	{
		return $flag ? "" : " style='display:none;'";
	}

	function filter()
	{
		$detailView = $this->detailView;
		$allFields = $this->allFields;
		$id = $this->id;
		$style = $lstHead = $lstField = array();
		$aFld = explode(",", $this->fields);
		foreach ($aFld as $k => $v) {
			$k = (int) $k;
			$f = $allFields[$k];
			if ($f['ViewHidden'] || !$f['ViewShow'])
				continue;
			$style[$k] = $this->getStyleField($f['id'], $f);
			if (!$f['ViewFilter'])
				continue;
			$value = htmlspecialchars($f['ValueFilter'], ENT_QUOTES);
			$classHead = preg_match('/(?:(`?)' . preg_quote($f['table']) . '\1\.)?(`?)' . preg_quote($f['name']) . '\2/', $detailView['GroupBy']) ? ' class="active"' : '';
			$classField = $f['Key'] ? " class='key'" : "";
			$lstHead[$k] = "<div id='{$f['id']}'$classHead onclick='{$id}.clickFltHead(this)' onmouseover='{$id}.overFltHead(this)' onmouseout='{$id}.outFltHead(this)'>{$f['Label']}</div>";
			$lstField[$k] = "<div id='{$f['id']}'$classField><input id='{$f['id']}' value='$value' onkeypress='{$id}.keypressFltField(this,event)' onkeyup='{$id}.keyupFltField(this,event)' onfocus='{$id}.focusFltField(this)' onblur='{$id}.blurFltField(this)' onmouseover='{$id}.overFltField(this)' onmouseout='{$id}.outFltField(this)' /></div>";
		}
		$this->session['style'] = implode("", $style);
		if (!$lstHead)
			return '';
		$box = new Box("\n\t<div id='DataList_FltHead'>\n\t\t" . implode("\n\t\t", $lstHead) . "\n\t</div>\n\t<div id='DataList_FltField'>\n\t\t" . implode("\n\t\t", $lstField) . "\n\t</div>\n");
		return $box->__tostring();
	}

	function getStyleField($id, $detail)
	{
		$out = "\t\tdiv#$id { width: {$detail['ViewWidth']}em; }\n";
		$out .= "\t\t#DataList_DtdBody div#$id { text-align: {$detail['Align']}; ";
		if (!($detail['DegradeCut'] & 4))
			$out .= "white-space: nowrap; ";
		if ($detail['DegradeCut'] & 1)
			$out .= "text-overflow : ellipsis;\n\toverflow: hidden; ";
		if ($detail['DegradeCut'] & 2)
			$out .= "filter: progid:DXImageTransform.Microsoft.Alpha( style=1,opacity=100,finishOpacity=10,startX=50,startY=50,finishX=100,finishY=100); ";
		$out .= "}\n";
		return $out;
	}

	function inicializeDataList()
	{
		$view = $this->view;
		// if($view==$this->viewOld) return;
		$id = $this->id;
		$ev = new EasyView();
		$ev->conn = $conn = $this->conn;
		if ($this->dsn)
			$ev->prepare($this->dsn);
		$ev->view = $view;
		$res = $conn->query($ev->spfull);

		$this->session['viewOld'] = $view;
		$this->session['sp'] = $ev->sp;
		$this->session['spfull'] = $ev->spfull;
		// $this->session['details']=$details=$ev->details;
		$details = $ev->details;
		$this->session['alias'] = $alias = $ev->alias;

		$t = $res->fetch_fields;
		$allFields = $detailView = $orderby = $groupby = $oFld = array();
		if (!($oFld = $this->oFld))
			$oFld = array();
		$keyAliasFirst = '';
		$details['']['view'] = array(
			'idDataView' => 0,
			'idGroup' => 0,
			'Schema' => '',
			'Name' => '',
			'Label' => '',
			'ViewType' => 'V',
			'ShowTable' => 1,
			'OrderBy' => '',
			'GroupBy' => '',
			'LinesView' => 30,
			'ShowFilter' => 1,
			'ShowRecordCount' => 1,
			'ShowNavBar' => 1,
			'Refresh' => 0,
			'HelpId' => 0
		);

		foreach ($t as $k => $v) {
			$idFld = "{$id}_fld$k";
			if ($v->table) {
				$keyAlias = "`{$alias[$v->table]['db']}`.`{$alias[$v->table]['tbl']}`";
				$fieldData = $details[$keyAlias]['fields'][$v->name];
				if (!$keyAliasFirst)
					$keyAliasFirst = $keyAlias;
			} else {
				$keyAlias = '';
				$oId = Id::singleton();
				$i = $oId->id;
				$fieldData = $details['']['fields'][$v->name] = array(
					'idField' => $i,
					'idDataView' => 0,
					'FieldName' => $v->name,
					'Label' => $v->name,
					'Type' => 'varchar',
					'Element' => '',
					'Key' => 0,
					'Align' => 'left',
					'DisplayFormat' => '',
					'Title' => $v->name,
					'TabIndex' => 0,
					'ViewShow' => 1,
					'ViewHidden' => 0,
					'ViewFilter' => 1,
					'ViewFunction' => 1,
					'ViewWidth' => 0,
					'DegradeCut' => 0,
					'ValueFilter' => '',
					'Value' => '',
					'Source' => '',
					'Min' => '',
					'Max' => '',
					'EditWidth' => 0,
					'ValidateField' => '',
					'ValidateKey' => '',
					'Disabled' => 1,
					'Form_Hidden' => 0,
					'Requeried' => 0,
					'Unsigned' => 0,
					'ZeroFill' => 0,
					'Auto_increment' => 0
				);
			}
			$allFields[$k] = array(
				'id' => $idFld,
				'alias' => $keyAlias,
				'name' => $v->name,
				'orgname' => $v->orgname,
				'table' => $v->table,
				'orgtable' => $v->orgtable,
				'idField' => $fieldData['idField'],
				'idDataView' => (int) $fieldData['idDataView'],
				'FieldName' => $fieldData['FieldName'],
				'Label' => $fieldData['Label'],
				'Type' => $fieldData['Type'],
				'Element' => $fieldData['Element'],
				'Key' => (int) $fieldData['Key'] ? true : false,
				'Align' => $fieldData['Align'],
				'DisplayFormat' => $fieldData['DisplayFormat'],
				'Title' => $fieldData['Title'],
				'ViewShow' => (int) $fieldData['ViewShow'] ? true : false,
				'ViewHidden' => (int) $fieldData['ViewHidden'] ? true : false,
				'ViewFilter' => (int) $fieldData['ViewFilter'] ? true : false,
				'ViewFunction' => $fieldData['ViewFunction'],
				'ViewWidth' => ($w = (int) $fieldData['ViewWidth']) ? $w : 10,
				'DegradeCut' => (int) $fieldData['DegradeCut'],
				'ValueFilter' => $fieldData['ValueFilter'] ? $fieldData['ValueFilter'] : "*"
			);
		}
		$orderby[$keyAliasFirst] = $details[$keyAliasFirst]['view']['OrderBy'];
		$groupby[$keyAliasFirst] = $details[$keyAliasFirst]['view']['GroupBy'];
		$detailView = $details[$keyAliasFirst]['view'];

		$detailView['OrderBy'] = implode(',', preg_grep("/^.*$/", $orderby));
		$detailView['GroupBy'] = implode(',', preg_grep("/^.*$/", $groupby));
		$detailView['hashWhere'] = '';
		$detailView['pg'] = 1;
		$detailView['pgTotal'] = 1;
		$detailView['recCount'] = 0;
		$this->session['fields'] = implode(",", array_keys($allFields));
		$this->session['allFields'] = $allFields;
		$this->session['detailView'] = $detailView;
	}

	function sqlCalc(&$detailView, $view)
	{
		/*
         * $this->session['viewOld']=$view;
         * $this->session['sp']=$ev->sp;
         * $this->session['spfull']=$ev->spfull;
         * $this->session['details']=$details=$ev->details;
         * $this->session['alias']=$alias=$ev->alias;
         * $this->protect['allFields']=$allFields;
         */
		$view = $viewCore = preg_replace('/\s*;\s*$/', '', $view);
		$LinesView = $detailView['ShowNavBar'] ? max(0, (int) $detailView['LinesView']) : 0;
		$pg = $detailView['pg'] = $LinesView ? max(1, (int) $detailView['pg']) : 1;
		$recIni = $detailView['recIni'] = ($pg - 1) * $LinesView;
		if ($LinesView) {
			if (preg_match('/((\)|\s)limit\s+)|((\)|\s)union(\s|\())/i', $view))
				$view = "SELECT SQL_CALC_FOUND_ROWS * ($view) tmp";
			else
				$view = preg_replace('/^((?:.|\s)*?select\s+)/i', '\1SQL_CALC_FOUND_ROWS ', $view);
			$view .= " LIMIT $recIni,$LinesView";
		}
		$res = $this->conn->query($view);
		$resFound = $this->conn->query("SELECT FOUND_ROWS()");
		$lFound = $resFound->fetch_row();
		// $recCount=$detailView['recCount']=$res->num_rows;
		$recCount = $detailView['recCount'] = $lFound[0];
		$resFound->close();
		$pgTotal = $detailView['pgTotal'] = ceil($recCount / ($LinesView ? $LinesView : $recCount));
		if ($pg > $pgTotal) {
			$res->close();
			$detailView['pg'] = $pgTotal;
			return $this->sqlCalc($detailView, $viewCore);
		}
		$this->session['detailView'] = $detailView;
		return $res;

		// Filtra OrderBy
		// Filtra GroupyBy
		// Separa pedaços da query
		// inclue o where, orderby e o whereGroupBy
		/*
         * $aOrder=array();
         * $aOrder[]="`{$f['table']}`.`{$f['name']}` $orderClass";
         * $detailView['OrderBy']=implode(",",$aOrder);
         * $this->session['detailView']=$detailView;
         */
		/*
         * $view=$temp=" ".preg_replace("/\s*;\s*$/","",$view);
         * $aV=array("LIMIT"=>0,"ORDER BY"=>0,"GROUP BY"=>0,"WHERE"=>0,"FROM"=>1,"SELECT"=>1);
         * $nV=str_replace(" ","\\s+"," ".implode(" | ",array_keys($aV))." ");
         * foreach($aV as $c=>$v){
         * $e=str_replace(" ","\\s+"," $c ");
         * $aV[$c]='';
         * if ($v) {
         * if (preg_match("/^(?:.|\s)*?$e((?:.|\s)+)$/i",$temp,$ret)){
         * $aV[$c]=trim($ret[1]);
         * $temp=preg_replace("/^((?:.|\s)*?)$e((?:.|\s)+)$/i","\\1",$temp);
         * }
         * }elseif (preg_match("/(?:.|\s)*$e((?:.|\s)+?)$/i",$temp,$ret) && !preg_match("/$nV/i",$ret[1])){
         * $aV[$c]=trim($ret[1]);
         * $temp=preg_replace("/((?:.|\s)*)$e((?:.|\s)+?)$/i","\\1",$temp);
         * }
         * }
         * $aV['']=trim($temp);
         * $aV=array_reverse($aV);
         * if ($where=$this->where()){
         * if ($aV['WHERE']) $aV['WHERE']="({$aV['WHERE']}) AND $where";
         * else $aV['WHERE']=$where;
         * }
         * $this->mk_order_group($aV['ORDER BY'],$this->OrderBy);
         * return $aV;
         */
	}

	function grpCalc()
	{
		return;
		$group = $this->GroupBy;
		if (!$group)
			return;
		//$aV = $this->sqlCalc();
		$aV['SELECT'] = $group;
		$this->mk_order_group($aV['GROUP BY'], $group, false);
		return $aV;
	}

	function mk_sql($aV)
	{
		$sql = array();
		foreach ($aV as $k => $v)
			if ($v)
				$sql[] = "$k $v";
		$sql = trim(implode(" \n", $sql));
		return $sql;
	}

	function mk_order_group(&$cmd, $value, $order = true)
	{
		if (!$value)
			return;
		$value = preg_split('/\s*,\s*/', $value . ($cmd ? ",$cmd" : ''));
		$aO = array();
		$fim = array();
		foreach ($value as $v) {
			$o = $order ? preg_replace('/^(`?)(\w*?)\1\s+(asc|desc)$/i', '\2', $v) : $v;
			if (!in_array($o, $aO)) {
				$aO[] = $o;
				$fim[] = $v;
			}
		}
		$cmd = implode(", ", $fim);
	}

	function where()
	{
		$lstWhere = array();
		$allFields = $this->allFields;
		foreach ($allFields as $k => $v) {
			// $v['ValueFilter']="=1>4|2";
			if (preg_match('/^[\*\?]*$/', $v['ValueFilter']))
				continue;
			$value = addcslashes($v['ValueFilter'], "\0..\31\"'");
			$fld = "`{$v['table']}`.`{$v['name']}`";
			preg_match_all('/([\&\|]?)([^\&\|]+)/', $value, $values, PREG_SET_ORDER);
			if ($values) {
				$itemWhere = array();
				$firstJoin = '';
				foreach ($values as $where) {
					$join = $where[1] == '|' ? "OR" : "AND";
					if (!$firstJoin)
						$firstJoin = $join;
					if ($itemWhere)
						$itemWhere[] = $join;
					$itemWhere[] = $fld . $this->whereItem($where[2], $v, $fld);
				}
				if ($lstWhere)
					$lstWhere[] = $firstJoin;
				$lstWhere[] = count($itemWhere) == 1 ? implode(' ', $itemWhere) : "(" . implode(' ', $itemWhere) . ")";
			}
		}
		return implode(" \n", $lstWhere);
	}

	function whereItem($value, $info, $fld)
	{
		$conn = $this->conn;
		$valueCompl = '';
		$er = '(<>|!=|>=|<=|=|<|>)((?:.|\s)*)';
		if (preg_match('/^\(([!n]?)ereg\)(.*)/i', $value, $r)) { // eReg
			$lCmd = ($r[1] ? " NOT" : '') . " REGEXP ";
			$value = $r[2];
		} elseif (preg_match("/^$er/", $value, $r)) {
			$lCmd = str_replace("!=", "<>", $r[1]);
			$value = $r[2];
			if (preg_match("/$er$/", $value, $r)) {
				$valueCompl = " AND $fld{$this->whereItem($r[1] .$r[2],$info,$fld)}";
				$value = preg_match("/$er$/", "", $value);
			}
			if (preg_match('/date|timestamp/i', $info['Type'])) {
				$value = preg_replace("/[^-: 0-9]/", "0", $this->reconfigData($value));
				if (preg_match('/^date$/i', $info['Type']))
					$value = preg_replace(' .{2}-.{2}-.{2}', '', $value);
			} elseif (preg_match('/^time$/i', $info['Type']))
				$value = preg_replace('/[^-: 0-9]/', '0', $this->reconfigTime($value));
		} else { // like
			$not = "";
			if (preg_match("/^!/", $value)) {
				$not = " NOT";
				$value = substr($value, 1);
			}
			$lCmd = "$not LIKE ";
			$d2 = '([\d\\*\\?]{1,2})';
			$d4 = '([\d\\*\\?]{1,4})';
			$ds = "([\\/\\-])";
			if (preg_match('/date|timestamp/i', $info['Type'])) {
				$value = $this->reconfigData($value);
				if (preg_match('/^date$/i', $info['Type']))
					$value = preg_replace(' .{2}-.{2}-.{2}', '', $value);
			} elseif (preg_match('/^time$/i', $info['Type']))
				$value = $this->reconfigTime($value);
			$value = preg_replace(array(
				'/([()_%])/',
				'/\*+/',
				'/\?/'
			), array(
				"\\\\1",
				'%',
				'_'
			), $value);
		}
		return "$lCmd'$value'$valueCompl";
	}

	function reconfigData($value)
	{
		$d2 = '([\d\\*\\?]{1,2})';
		$d4 = '([\d\\*\\?]{1,4})';
		$ds = "([\\/\\-])";
		preg_match("/^$d2(?:$ds$d2(?:\\2$d4)?)?(?:\s+$d2(?:-$d2(?:-$d2)?)?)?/", $value, $tD);
		for ($i = 1; $i <= 7; $i++)
			$tD[$i] = isset($tD[$i]) ? $tD[$i] : '';
		return "{$this->trD($tD[4], 4)}-{$this->trD($tD[3], 2)}-{$this->trD($tD[1], 2)} {$this->trD($tD[5], 2)}:{$this->trD($tD[6], 2)}:{$this->trD($tD[7], 2)}";
	}
	function trD($x,$y){}

	function reconfigTime($value)
	{
		$d2 = '([\d\\*\\?]{1,2})';
		preg_match("/^$d2(?:-$d2(?:-$d2)?)?/", $value, $tD);
		for ($i = 1; $i <= 3; $i++)
			$tD[$i] = isset($tD[$i]) ? $tD[$i] : '';
		return "{$this->trDt($tD[1], 2)}:{$this->trDt($tD[2], 2)}:{$this->trDt($tD[3], 2)}";
	}

	function trDt($v, $n)
	{
		$v = $v == '' ? str_repeat('?', $n) : str_replace('*', '?', $v);
		$c = strlen($v) - 1;
		if ($v{
		0} == '?' && $v{
		$c} == '?')
			$v = str_repeat('?', $n);
		else if ($v{
		0} == '?')
			$v = str_pad($v, $n, "?", STR_PAD_LEFT);
		else if ($v{
		$c} == '?')
			$v = str_pad($v, $n, "?");
		else
			$v = str_pad($v, $n, "0", STR_PAD_LEFT);
		return $v;
	}

	function mk_ElementFilter($numField, $fld)
	{
		if ($fld['ViewHidden'] || !$fld['ViewShow'])
			return '';
		$id = $this->id;
		$name = "{$id}[fld][{$numField}][ValueFilter]";
		$value = htmlspecialchars($fld['ValueFilter'], ENT_QUOTES);
		$value = $value == '' ? $value : '*';
		$disabled = $fld['ViewFilter'] ? "" : " DISABLED";
		/*
         * if (preg_match("/datetime|timestamp/i",$fld['Type'])) $title="Formato: dd/mm/aaaa HH:MM:SS\n";
         * elseif (preg_match("/date/i",$fld['Type'])) $title="Formato: dd/mm/aaaa\n";
         * elseif (preg_match("/time/i",$fld['Type'])) $title="Formato: HH:MM:SS\n";
         */
		$out = "<div id='{$id}_$numField' class='DataList_FltFieldGrp'>"; // dlFlt_n=DataList Filter_num
		$out .= "<div id='DataList_FltLabel'>{$fld['Label']}</div>"; // dlLbl=DataList Label
		$out .= "<div id='DataList_FltField'>";
		$out .= "<input id='DataList_FldInput' type='text' name='$name' value='$value'$disabled />";
		$out .= "<div id='DataList_FldButton' onclick='{$this->id}.assistent(this)'></div>";
		$out .= "</div>\n"; // dlFld=DataList Field
		$out .= "</div>\n";
		return $out;
	}

	function group()
	{
		$group = $this->GroupBy;
		if (!$group)
			return;
		$sql = $this->mk_sql($this->grpCalc());
		return $sql;
	}

	function navigator()
	{
		if (!$this->showTable || !$this->showNavBar)
			return;
	}
}
/*
function parseView()
{
	if ($this->easyView->sp)
		$this->protect['view'] = $this->easyView->sp;
	if (!$this->easyView->details || isset($this->session['parseView']))
		return;
	$this->session['parseView'] = true;
	$details = $this->easyView->details;
	if (count($this->easyView->details) == 1) {
		$tbl = key($details);
		$details = current($details);
		$flds = array_values($details['fields']);
		foreach ($flds as $k => $v)
			$flds[$k]['tbl'] = $tbl;
		$view = $details['view'];
	} else {
		$view = array(
			'idDataView' => 0,
			'idGroup' => 0,
			'Schema' => '',
			'Name' => '',
			'Label' => '',
			'ViewType' => '',
			'ShowTable' => 0,
			'OrderBy' => '',
			'GroupBy' => '',
			'LinesView' => 50,
			'ShowFilter' => 1,
			'ShowRecordCount' => 1,
			'ShowNavBar' => 1,
			'Refresh' => false,
			'HelpId' => false
		);
		$res = $this->easyView->exec();
		$f = $res->fetch_fields();
		$keysTbl = array();
		foreach ($details as $k => $v) {
			$nK = preg_replace(array(
				'/^`\w+`\.`/',
				'/`$/'
			), '', $k);
			$keysTbl[$nK] = $k;
		}
		$flds = array();
		foreach ($f as $k => $v) {
			$key = $keysTbl[$v->orgtable];
			if (!isset($details[$key]['fields'][$v->orgname])) {
				$this->easyView->consolida();
				session_unset();
				session_destroy();
				die("Execução interrompida pela atualização de campos do Banco de Dados. Execute Novamente");
			}
			$flds[$k] = $details[$key]['fields'][$v->orgname];
			$flds[$k]['tbl'] = $key;
		}
	}
	$this->session['fld'] = $flds;
	foreach ($view as $k => $v) {
		$k = preg_replace('/^(\w)/e', "strtolower('\\1')", $k);
		if ($v !== false && (!isset($this->session[$k]) || $this->session[$k] === false))
			$this->session[$k] = $v;
	}
}
*/