<?php

namespace EstaleiroWeb\ED\Data\Grid;
/*
	URL?CounterId=1057000200&period=D&vw[where]=FIND_IN_SET('SBC', FNs)&refresh=10
	'SQL FIELD @idGraph,@idKPI,@CounterId,@idDevice,@Interface,@total AND SQL WHERE',
	'SQL WHERE CounterId,FnSetId,FnSubSetId,idUnit,idSenderType,idDevice,idInterfDev,idInterf,CounterName,Counter,FnSetName,FnSubSetName,CounterUnit,SenderType,FNs,Interface,Duration',

	&idGraph=
	&idKPI=
	&CounterId=
	&idDevice=
	&idInterfDev=
	&path=

	&period=
	&refresh=
	&filter=
	&group=

	&dtStart=
	&dtEnd=
	&conf=

	&type=
	&SQL=


	&vm[title]=
	&vm[subtitles]=
	&vm[legendText]=
	&vm[label]=
	&vm[type]=
	&vm[axisYType]=
	&vm[axisY]=
	&vm[axisY2]=
	&vm[x]=
	&vm[y]=
	&vm[link]=
	&vm[Aggr]=
	&vm[where]=
*/

use Conn;
use EstaleiroWeb\Cache\Config;
use EstaleiroWeb\ED\Data\Element\ElementCalendar;
use EstaleiroWeb\ED\Data\Element\ElementCheck;
use EstaleiroWeb\ED\Ext\Ed;
use EstaleiroWeb\ED\IO\SessControl;
use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\ED\Secure\Secure;
use EstaleiroWeb\ED\Tools\Id;
use EstaleiroWeb\Traits\GetterAndSetter;

class DataGraph extends DataGraph_common {
	use GetterAndSetter;

	static public $verbose = false;
	private $exit = false;
	private $axis = [], $args = [], $sets = [], $line = []; //,$lines=[];
	public $chart, $pathId = [], $out = [];

	protected $oSess;
	protected $tbBase;
	public $db = 'db_IMS_Huawei';
	public $dbHst = 'db_IMS_Huawei_Hst';
	protected $arrHst = array(
		'D' => 'tb_Results_Day',
		'M' => 'tb_Results_Month',
		'Y' => 'tb_Results_Year',
	);
	protected $afield = array(
		'D' => array('MIN' => '`Value`', 'MAX' => '`Value`', 'SUM' => '`Value`', 'COUNT' => '`Value`', 'AVG' => '`Value`',),
		'M' => array('MIN' => '`MIN`', 'MAX' => '`MAX`', 'SUM' => '`SUM`', 'COUNT' => '`COUNT`', 'AVG' => '`SUM`/`COUNT`',),
		'Y' => array('MIN' => '`MIN`', 'MAX' => '`MAX`', 'SUM' => '`SUM`', 'COUNT' => '`COUNT`', 'AVG' => '`SUM`/`COUNT`',),
	);
	protected $axisQuant = 0;
	protected $quants = array(
		'FNs' => 0,
		'FnSetId' => 0,
		'FnSubSetId' => 0,
		'CounterId' => 0,
		'idSenderType' => 0,
		'idDevice' => 0,
		'idInterfDev' => 0,
		'idInterf' => 0,
		'Duration' => 0,
	);
	/**
	 *  @brief Inicia objeto de um gráfico
	 *  
	 *  @param [in] $id Identificador do gráfico 
	 *  @param [in] $idFile Identificador do arquivo
	 *  
	 *  @details More details
	 */
	public function __construct($id = null, $idFile = null) {
		$this->tbBase = 'tmp_base_' . Secure::$idUser;
		if (is_null($id)) {
			$oId = Id::singleton();
			$id = $oId->id;
		}
		$this->oSess = SessControl::singleton($id, $idFile, true); {
			$this->readonly = array(
				'conn' => null,
				'id' => $id,
				'idObj' => $id,
				'idFile' => $this->oSess->idFile(),
				'rebuildArray' => array('data' => 'data'),
				'tbHst' => '',
				'field' => [],
				'view' => [],
				'zone' => substr_replace(strftime('%z'), ':', 3, 0), // CONVERT_TZ(h.DtUpdate,'+00:00','$zone') 

			);
		} {
			$this->protect = array(
				'refresh' => null,
				'period' => null,

				'total' => 0,
				'joinRed' => 0,
				'filter' => null,
				'field' => null,
				'group' => null,
				'FNs' => null,
				'groupPattern' => null,
				'dtStart' => null,
				'dtEnd' => null,
				'weeks' => null,
				'Duration' => null,
				'type' => null,
				'yMax' => null,
				'conf' => [],
				'attr' => [],
				'vw' => [],

				'idInterf' => null,
				'idInterfDev' => null,
				'idDevice' => null,

				'path' => null,
				'idGraph' => null,
				'idKPI' => null,
				'SQL' => null,
				'CounterId' => null,
			);
		}
	}
	public function __destruct() {
	}
	/**
	 *  @return string Retorna a string principal chamadora dos dados do gráfico
	 *  
	 *  @details More details
	 */
	public function __toString() {
		if ($this->exit) return '';
		$this->exit = true;

		$this->period = 'D';
		foreach ($_REQUEST as $field => $val) $this->$field = $val;
		//$this->choiceTable();
		foreach ($this->sets as $do) call_user_func_array(array($this, $do['fn']), $do['args']); { //Head
			$ed = new Ed();

			//$ed->outHtml->script('canvasjs/canvasjs.min','easyData');$o->script('canvasjs/jquery.canvasjs.min','easyData');
			$ed->outHtml->script('canvasjs/source/canvasjs', 'easyData');
			//$ed->outHtml->script('canvasjs/source/jquery.canvasjs','easyData');
			$ed->outHtml->script('canvasjs/source/locale/pt-br', 'easyData');

			//$ed->outHtml->script(__CLASS__,'easyData');
			//$ed->outHtml->style(__CLASS__,'easyData');

			//$o->script('Ed','easyData');
			$ed->outHtml->script(__CLASS__, 'easyData');
		} { //HTML
			$arr = $this->protect['attr'];
			$arr['ed-element'] = __CLASS__;
			$arr['id'] = $this->id;
			$arr['idFile'] = $this->idFile;
			$arr['path'] = $this->path;
			$arr['refresh'] = $this->protect['refresh'];

			$tag = '<div';
			foreach ($arr as $k => $v) if (!is_null($v)) $tag .= ' ' . $k . '="' . htmlentities($v, ENT_QUOTES) . '"';
			$tag .= '></div>';
		}
		$this->conf();
		$this->saveSession();
		return $tag;
	}
	/**
	 *  @brief Gera dados do gráfico
	 *  
	 *  @return DataGraph THIS Object
	 *  
	 *  @details More details
	 */
	public function __invoke($obj = false) {
		$arr = $this->loadSession();

		if ($this->protect['type']) DataGraph_Chart_data::$default['type'] = $this->protect['type'];
		if (@$_REQUEST['gdet'] == 2) DataGraph_Chart_data::$default['showInLegend'] = false;

		$this->chart = new DataGraph_Chart($this);
		$this->out = array_merge($this->out, $this->protect['conf']);

		//$this->show($this->out);
		$this->build_Data();
		//$this->chart->__invoke();
		//$this->protect['build_Data']=true;
		return $obj ? $this->chart->__invoke() : $this->out;
		//return $obj?$this->chart->__invoke():"$this->chart";
	}

	protected function set_period($val) {
		$val = strtoupper($val);
		if (!array_key_exists($val, $this->arrHst)) return;
		$this->protect['period'] = $val;
		$this->readonly['tbHst'] = $this->dbHst . '.' . $this->arrHst[$val];
		$this->readonly['field'] = $this->afield[$val];
	}
	protected function set_idGraph($val) {
		$this->sets[] = array('fn' => 'conf' . substr(__FUNCTION__, 3), 'args' => func_get_args());
	}
	protected function set_idKPI($val) {
		$this->sets[] = array('fn' => 'conf' . substr(__FUNCTION__, 3), 'args' => func_get_args());
	}
	protected function set_CounterId($val) {
		$this->sets[] = array('fn' => 'conf' . substr(__FUNCTION__, 3), 'args' => func_get_args());
	}
	protected function set_SQL($sql) {
		$this->sets[] = array('fn' => 'conf' . substr(__FUNCTION__, 3), 'args' => func_get_args());
	}
	protected function set_Formula($val) {
		$this->sets[] = array('fn' => 'conf' . substr(__FUNCTION__, 3), 'args' => func_get_args());
	}
	protected function set_idDevice($val) {
		$this->rebuild_id('idDevice', $val);
	}
	protected function set_idInterfDev($val) {
		$this->rebuild_id('idInterfDev', $val);
	}
	protected function set_idInterf($val) {
		$this->rebuild_id('idInterf', $val);
	}
	protected function set_conf($val) {
		$this->protect['conf'] = array_merge($this->protect['conf'], (array)json_decode($val));
	}
	protected function set_height($val) {
		if ($val) $this->protect['conf']['height'] = $val;
	}
	protected function set_groupAggr($val) {
		$this->group = $val;
	}
	protected function set_group($val) {
		if (preg_match('/^(MIN|MAX|AVG|COUNT|SUM)$/', $val)) $this->protect['group'] = $val;
	}
	protected function set_field($val) {
		if (preg_match('/^(MIN|MAX|AVG|COUNT|SUM)$/', $val)) $this->protect['field'] = $val;
	}
	protected function set_Start($val) {
		$this->protect['dtStart'] = $val;
	}
	protected function set_End($val) {
		$this->protect['dtEnd'] = $val;
	}
	protected function set_Filter($val) {
		$this->protect['filter'] = $val;
	}
	protected function get_height() {
		return @$this->protect['conf']['height'];
	}
	protected function get_Start() {
		return @$this->protect['dtStart'];
	}
	protected function get_End() {
		return @$this->protect['dtEnd'];
	}
	protected function get_Filter() {
		return @$this->protect['filter'];
	}

	protected function conf() {
		//show($this->quants);
		$sql = "SET @qAxis={$this->axisQuant}; SET @ifIni=null; SET @ifEnd=null;\n";

		/*foreach($this->quants as $k=>$lines) {
			$q=count($lines);
			$v=implode(',',$lines);
			$sql.="SET @q{$k}={$q}; SET @all_{$k}='{$v}';\n";
		}
		*/
		$arr = array(
			'Interface' => null,
			'dtStart' => null, 'dtEnd' => null,

			'filter' => null,
			//'labelReplace'=>null,
			'period' => null,
			'conf' => null,
			'joinRed' => null,
			'group' => null,
			'groupPattern' => null,

			'total' => 0,
		);
		foreach ($arr as $k => $v) {
			// $v=is_null($v)?'NULL':"$v";
			$sql .= "SET @{$k}={$this->val($this->$k,$v)};\n";
		}

		array_unshift($this->readonly['view'], array('sql' => $sql, 'eval' => false));
		//$this->show($this->readonly['view']); 
		return $sql;
	}
	protected function conf_idGraph($val) {
		$this->rebuild_id('idGraph', $val);
		$this->conf_byidGraph();
	}
	protected function conf_idKPI($val) {
		//$this->show($val);
		$this->rebuild_id('idKPI', $val);
		$conn = $this->connect();
		$res = $conn->query("SELECT * FROM db_MainPerf.tb_Graphs_KPI k WHERE k.idKPI IN ({$this->protect['idKPI']})");
		$tps = array(
			'SQL' =>    array('fn' => 'conf_SQL',       'field' => 'SQL',),
			'Counter' => array('fn' => 'conf_CounterId', 'field' => 'CounterId',),
			'Formula' => array('fn' => 'conf_Formula',   'field' => 'Formula',),
		);
		while ($this->args = $res->fetch_assoc()) {
			$tp = $tps[$this->args['KPIType']];
			//show($tp);show($this->args);
			$this->{$tp['fn']}($this->args[$tp['field']]);
		}
		/*
			y (SUM(C478154828)/SUM(C478154827))*100

			where ???
			join by Device|Interf
			Unit %
			KPI ATS MO Session Completion Rate
			Big Value Good		
			
			r.idResult,
			r.CounterId,c.CounterName, IFNULL(a.Alias,c.CounterName) Counter, 
			c.FnSetId, f.FnSetName, 
			t.idSenderType, t.SenderType,
			c.FnSubSetId, s.FnSetName FnSubSetName, 
			i.idDevice, d.Device, e.FNs,
			r.idInterfDev, i.idInterf, ii.Interface,
			c.idUnit, u.CounterUnit,
			900 Duration, null Aggr
		*/
		/*
			-- Value:	MAX(C1907466045-C1907466046)[,C1907466045][,C1907466046]
						MAX(Value)
			-- Label: fn_grpLabel('Number of XXX',Device,Interface)
			-- WHERE: Device,FnSubSetId,Interface,CnlGrp,Pop,FNs
			-- FIND_IN_SET(d.idDevice,'11,2,1') 
			-- IFNULL(FIND_IN_SET(d.idDevice,null),true); 
			-- FIND_IN_SET("SBC",FNs)

			SET @idDevice=8081;
			SET @Interface="ABCF";
			SET @Invert_Interface=0;
			SET @TOTAL=0;

			-- SET @idDevice=NULL;
			-- SET @Interface=NULL;



			SELECT 
				MAX(C1907466045-C1907466046) Value, -- Definição Primária
				h.*
			FROM (
				SELECT 
					fn_grpLabel("Number of XXX",Device,REGEXP_REPLACE(Interface,"^(\\d+),([^_-]+)\\D+(\\d+)\\D*(\\d+),0","\\2-\\1-\\3-\\4")) Label,
					null Link,
					H1907466045.DtUpdate,
					H1907466045.Value C1907466045,
					H1907466046.Value C1907466046,
					b2.*
				FROM (
					SELECT 
						i.idDevice,
						Device,FnSubSetId,Interface,
						CnlGrp,Pop,FNs,
						b1.*
					FROM (
						SELECT 
							T1907466045.idInterf,
							T1907466045.idResult R1907466045,
							T1907466046.idResult R1907466046
						FROM db_IMS_Huawei.tb_Results T1907466045
						JOIN db_IMS_Huawei.tb_Results T1907466046 ON T1907466046.CounterId=1907466046 -- AND T1907466045.idInterf=T1907466046.idInterf
						WHERE T1907466045.CounterId=1907466045
						
					) b1
					JOIN db_IMS_Huawei.tb_Interfaces i ON b1.idInterf=i.idInterf 
					JOIN db_MainResource.tb_Devices d ON i.idDevice=d.idDevice AND d.`Enable`
					JOIN db_IMS_Huawei.tb_Elements e ON e.idDevice=d.idDevice
					WHERE IF(@idDevice,i.idDevice=@idDevice,TRUE) 
						AND IF(@Interface IS NOT NULL,IF(@Invert_Interface,i.Interface NOT REGEXP @Interface,i.Interface REGEXP @Interface),TRUE) -- Depreciado WHERE
						-- AND FIND_IN_SET("SBC",FNs)
				) b2
				JOIN db_IMS_Huawei_Hst.tb_Results H1907466045 ON H1907466045.idResult=R1907466045
				JOIN db_IMS_Huawei_Hst.tb_Results H1907466046 ON H1907466046.idResult=R1907466046 AND H1907466045.DtUpdate=H1907466046.DtUpdate
			) h
			GROUP BY IF(@TOTAL,1,DtUpdate),Label -- IF(C_idDevice,idDevice,1),IF(C_Interface,idInterf,1)
				limit 10
		*/
	}
	protected function conf_Formula($val) {
	}
	protected function conf_CounterId($val) {
		$this->rebuild_id('CounterId', $val);

		//$CounterIds=implode(',',$this->protect['CounterId']);
		//$this->show($CounterIds);
		($counterId = @$this->args['CounterId']) || ($counterId = @$this->protect['CounterId']);
		//$this->show($counterId);
		if (!$counterId) return;
		$conn = $this->connect();
		//$where=(($w=@$this->args['where']) || ($w=@$this->protect['vw']['where']))?'WHERE '.$w:'';
		$tmpBase = $this->make_SQL_create_tmpBase($counterId);
		foreach ($tmpBase as $sql) {
			$this->readonly['view'][] = array('sql' => $sql, 'eval' => false);
			$conn->query($sql);
		}
		$sqlBase = $this->make_SQL_base($counterId); {
			$sql = "
			SELECT 
				COUNT(DISTINCT CounterId)   qCounterId,
				COUNT(DISTINCT idDevice)    qDevice,
				COUNT(DISTINCT idInterfDev) qIntefDev,
				COUNT(DISTINCT idInterf)    qInterf,
				
				idUnit,
				GROUP_CONCAT(DISTINCT REPLACE(FNs,',','-'))        FNs,
				GROUP_CONCAT(DISTINCT FnSetId)                     FnSetId,
				GROUP_CONCAT(DISTINCT FnSubSetId)                  FnSubSetId,
				GROUP_CONCAT(DISTINCT CounterId)                   CounterId,
				GROUP_CONCAT(DISTINCT idSenderType)                idSenderType,
				GROUP_CONCAT(DISTINCT idDevice)                    idDevice,
				GROUP_CONCAT(DISTINCT idInterfDev)                 idInterfDev,
				GROUP_CONCAT(DISTINCT idInterf)                    idInterf,
				
				CounterUnit,
				GROUP_CONCAT(DISTINCT Device)                      Device,
				GROUP_CONCAT(DISTINCT FnSetName SEPARATOR '\t')    FnSetName,
				GROUP_CONCAT(DISTINCT FnSubSetName SEPARATOR '\t') FnSubSetName,
				GROUP_CONCAT(DISTINCT CounterName SEPARATOR '\t')  CounterName,
				GROUP_CONCAT(DISTINCT Counter SEPARATOR '\t')      Counter,
				GROUP_CONCAT(DISTINCT SenderType SEPARATOR '\t')   SenderType,
				GROUP_CONCAT(DISTINCT Interface SEPARATOR '\t')    Interface,
				GROUP_CONCAT(DISTINCT Duration SEPARATOR ',')      Duration,
				Aggr
			FROM {$this->tbBase} t
			GROUP BY idUnit
		";
			$lines = $conn->query_all($sql);
			//$this->show($units);
		}
		//$this->show($sql); 
		//Conn::$fit_maxLength=0;$this->showTable($lines); 

		//$this->lines=$lines;
		$this->add_Quants($lines);
		while ($lines) {
			$line = array_shift($lines);
			$this->line = $line;
			$this->sumarize($line['Interface']);
			$axisY = $this->configure_axis($line['CounterUnit']);
			if (!$axisY) break;

			($aggr = @$this->args['group']) || ($aggr = @$this->protect['group']) || ($aggr = @$line['Aggr']) || ($aggr = 'MAX');
			$this->line['aggr'] = $this->protect['group'] = $aggr;
			($field = $this->field) || ($field = $aggr);
			$field = $this->protect['period'] == 'D' ? '`Value`' : ($field == 'AVG' ? '`SUM`/`COUNT`' : "`$field`"); //todo value period ????
			//$field=$this->protect['period']='D'?'Value':$aggr;//todo value period ????
			//$this->show($line);
			$this->line['field'] = $field;
			$fld = $fieldsBase = []; {
				$fieldsBaseJoinRed = array(
					'idDevice' => 'idDevice',
					'Device' => 'Device',
					'Redundance' => 'Redundance',
					'Counter' => 'Counter',
					'CounterName' => 'CounterName',
					'FNs' => 'FNs',
					'Interface' => 'Interface',
				);
			} { //Campos Não Configuráveis
				$arr = array('idGraph', 'idKPI',);
				foreach ($arr as $k) if (($v = @$this->args[$k]) || ($v = @$this->protect[$k])) $fld[$k] = "'{$v}' `$k`";
			} { //Fields Configuráveis Base
				{
					$arr = array(
						'type' => '{$this->auto_type()}',
						'legendText' => '{$this->auto_legendText()}',
						'label' => '{$this->auto_label()}',
						'title' => '{$this->auto_title()}',
						'subtitles' => '{$this->auto_subtitles()}',
						'link' => '{$this->auto_link()}',
						'axisY' => '{$this->auto_axisY()}',

						'xValueType' => '"dateTime"',
						'axisYType' => $axisY['axisYType'],
						'AggrField' => 'Aggr',
					);
				}
				foreach ($arr as $k => $v) {
					if (($val = @$this->args[$k]) || ($val = @$this->protect['vw'][$k])) $v = $val;
					$v = $this->do_eval($v);
					$fieldsBase[$k] = $v . ' `' . $k . '`';
					$fieldsBaseJoinRed[$k] = "`$k`";
				}
				if ($axisY['axisY'] != 'axisY') {
					$fieldsBase['axisY2'] = str_replace('axisY', 'axisY2', $fieldsBase['axisY']);
					$fieldsBaseJoinRed['axisY2'] = str_replace('axisY', 'axisY2', $fieldsBaseJoinRed['axisY']);
					unset($fieldsBase['axisY']);
					unset($fieldsBaseJoinRed['axisY']);
				}
			}
			$sqlBase = $this->make_SQL_base($line['CounterId'], $fieldsBase); { //Campos Configuráveis
				$dur = $this->protect['period'] == 'D' && $this->quants['Duration'] != 1 ? max(explode(',', $line['Duration'])) : 'null';
				$arr = array(
					'x' => "db_IMS_Huawei.fn_UTC(h.DtUpdate,'{$this->zone}',{$dur})",
					'y' => "{$aggr}({$field})",
				);
				foreach ($arr as $k => $v) {
					if (($val = @$this->args[$k]) || ($val = @$this->protect['vw'][$k])) $v = $this->do_eval($val);
					//else if(is_array($v)) $v='CONCAT_WS(\'-\','.implode(',',$v).')';
					$fld[$k] = ($v == $k ? '' : $v . ' ') . ('`' . $k . '`');
					$fieldsBaseJoinRed[$k] = "`$k`";
				}
			}

			$fields = implode(",\n\t", $fld); {
				$sql = "
				SELECT  
					t.*,
					{$fields} 
				FROM ($sqlBase) t
				JOIN {$this->tbHst} h ON h.idResult=t.idResult{$this->sqlAndWhere_date()}
				GROUP BY `legendText`,`x`;\n";
			}
			if ($this->protect['joinRed']) {
				$tblTmp = 'tmp_graph_joinRed_idUsr' . Secure::$idUser;
				$this->readonly['view'][] = array(
					'sql' => "DROP TABLE IF EXISTS {$tblTmp};\nCREATE TEMPORARY TABLE IF NOT EXISTS {$tblTmp} {$sql}",
					'eval' => false,
				);
				$leg_1 = $this->auto_legendText(false);
				$fieldsBaseJoinRed['legendText'] = $leg_1 . ' `legendText`';
				$fieldsBaseJoinRed['y'] = 'IF(isSum,SUM(y),MAX(y)) `y`';
				//$fieldsBaseJoinRed['y']='SUM(y) `y`';
				$fields = implode(",\n\t", $fieldsBaseJoinRed);
				$sql = "SELECT\n\t{$fields}\nFROM {$tblTmp} j\nGROUP BY {$leg_1},`x`;\n";

				$fieldsBaseJoinRed['y'] = "{$aggr}(`y`) `y`";
				$leg_2 = $this->auto_legendText(true);
				if ($leg_1 != $leg_2) {
					$tblTmp2 = 'tmp_graph_joinRed2_idUsr' . Secure::$idUser;
					$this->readonly['view'][] = array(
						'sql' => "DROP TABLE IF EXISTS {$tblTmp2};\nCREATE TEMPORARY TABLE IF NOT EXISTS {$tblTmp2} {$sql}",
						'eval' => false,
					);
					$fieldsBaseJoinRed['legendText'] = $leg_2 . ' `legendText`';
					$fields = implode(",\n\t", $fieldsBaseJoinRed);
					$sql = "SELECT\n\t{$fields}\nFROM {$tblTmp2} j\nGROUP BY {$leg_2},`x`;\n";
					//$sql=preg_replace('/;\s*$/','',$sql);
					//$grp=str_replace('legendText','Redundance',$grp);
					//$sql="SELECT\n\t{$fields}\nFROM (\n{$sql}\n) j\nGROUP BY {$grp};\n";
					//$sql="DROP TABLE IF EXISTS {$tblTmp};\n";
				}
			}
			($total = @$this->args['total']) || ($total = @$this->protect['total']);
			if ($total == 2) {
				$tblTmpTotal = 'tmp_graph_total_idUsr' . Secure::$idUser;
				$this->readonly['view'][] = array(
					'sql' => "DROP TABLE IF EXISTS {$tblTmpTotal};\nCREATE TEMPORARY TABLE IF NOT EXISTS {$tblTmpTotal} {$sql}",
					'eval' => false,
				);
				$fieldsBaseJoinRed['y'] = "{$aggr}(`y`) `y`";
				$fieldsBaseJoinRed['type'] = '"column" `type`';
				$fields = implode(",\n\t", $fieldsBaseJoinRed);
				$sql = "SELECT\n\t{$fields}\nFROM {$tblTmpTotal} j\nGROUP BY `legendText`;\n";
			}

			$this->readonly['view'][] = array('sql' => $sql, 'eval' => false);
			//$this->show($sql);  //Drop it
			//$this->show($line); //Drop it
			//$this->show($fld);  //Drop it
		}
		//$this->show($this->readonly['view']);  //Drop it
		//$this->show($this->quants);  //Drop it
		//exit;
	}

	protected function auto_arr($arr, $sep = '-') {
		if (!$arr) return 'NULL';
		if (count($arr) == 1) return reset($arr);
		return 'CONCAT_WS("' . $sep . '",' . implode(',', $arr) . ')';
	}
	protected function auto_type() {
		($total = @$this->args['total']) || ($total = @$this->protect['total']);
		if ($total == 2) return '"column"';
		($type = @$this->args['type']) || ($type = @$this->protect['type']) || ($type = 'line');
		return "'$type'";
	}
	protected function auto_label() {
		if ($this->protect['total']) return 'IFNULL(b.Counter,b.CounterName)';
		return 'NULL';
	}
	protected function auto_legendText($noRed = null) {
		$arr = [];
		$device = $noRed === false || $noRed === true ? 'Redundance' : 'Device';
		if ($this->joinRed && !$noRed) $arr['Device'] = $device;
		if ($this->total) return $arr ? $this->auto_arr($arr) : 'IFNULL(Counter,CounterName)';
		if ($this->quants['CounterId'] > 1) $arr[] = 'IFNULL(Counter,CounterName)';
		else {
			if ($this->quants['idDevice'] > 1) {
				if ($this->quants['FNs'] > 1) $arr[] = 'FNs';
				else $arr['Device'] = $device;
				//$arr[]=$this->joinRed?'IFNULL(Red,Device)':$device;
			}

			if (!$arr && $this->quants['idInterf'] > 1) {
				$fld = 'Interface';
				//if($this->line['ifEnd']) $fld="substr($fld,1,LENGTH($fld)-{$this->line['ifEnd']})";
				//if($this->line['ifIni']) $fld="substr($fld,{$this->line['ifIni']})";
				$groupPattern = $this->groupPattern;
				if ($groupPattern) {
					$replace = '\\1';
					// /^([^_]+).*(VDU(?:\d+|_[a-z]+)).*$/\1-\2/
					if (preg_match('~^s?/(.*?)(?:(?<!\\\)/(.*))?/$~', $groupPattern, $ret)) {
						$groupPattern = $ret[1];
						if (array_key_exists(2, $ret)) $replace = $ret[2];
						//$this->show($ret);
					}
					$fld = "REGEXP_REPLACE({$fld},{$this->val($groupPattern)},{$this->val($replace)})";
				}
				$arr[] = $fld;
			}
		}
		return $this->auto_arr($arr);
	}
	protected function auto_title() {
		$arr = [];
		if ($this->quants['idDevice'] == 1) $arr[] = 'b.Device';
		elseif ($this->quants['FNs'] == 1) $arr[] = 'b.FNs';
		if ($this->quants['CounterId'] == 1) $arr[] = 'b.CounterName';
		if ($this->quants['idInterf'] == 1) $arr[] = 'IF(b.Interface,b.Interface,NULL)';
		return $this->auto_arr($arr);
	}
	protected function auto_title_old() {
		$arr = [];
		if ($this->quants['CounterId'] == 1) {
			if ($this->quants['idDevice'] == 1) $arr[] = 'b.Device';
			elseif ($this->quants['FNs'] == 1) $arr[] = 'b.FNs';
			$arr[] = 'b.CounterName';
			if ($this->quants['idInterf'] == 1) $arr[] = 'b.Interface';
		} else {
			if ($this->quants['FNs'] > 1) $arr[] = 'b.FNs';
			elseif ($this->quants['idDevice'] > 1) $arr[] = "'{$this->line['Device']}'";
		}
		return $this->auto_arr($arr);
	}
	protected function auto_subtitles() {
		$arr = [];
		if ($this->quants['FnSetId'] == 1) $arr[] = 'b.FnSetName';
		if ($this->quants['FnSubSetId'] == 1) $arr[] = 'b.FnSubSetName';
		$filter = @$this->filter;
		if ($filter != '') $arr[] = '"' . escapeString($filter) . '"';
		return $this->auto_arr($arr);
	}
	protected function auto_link() {
		if ($this->protect['total'])      return '"js:{total:' . ($this->protect['total'] - 1) . '}"';
		if ($this->quants['CounterId'] > 1) return 'CONCAT("js:{CounterId:\'",b.CounterId,"\'}")';
		if ($this->quants['idDevice'] > 1) {
			if ($this->quants['FNs'] > 1) return 'CONCAT("js:{FNs:\'",b.FNs,"\'}")';
			if ($this->protect['joinRed']) return 'CONCAT("js:{joinRed:0,idDevice:\'",b.idRed,"\'}")';
			else return 'CONCAT("js:{idDevice:\'",b.idDevice,"\'}")';
		}
		$CounterId = @$this->line['CounterId'] + 0;
		if ($this->quants['CounterId'] == 1 && $CounterId <= 2000 && $CounterId >= 1000) {
			$conn = $this->connect();
			$newCounterId = $conn->fastValue("
				SELECT GROUP_CONCAT(CounterId) CounterId
				FROM db_IMS_Huawei.tb_Counters_SpcLstRel c 
				WHERE c.CounterIdTo=$CounterId
			");
			//$this->show(array($CounterId=>$newCounterId));
			if ($newCounterId) return 'CONCAT("js:{filter:\'",b.Interface,"\',CounterId:\'' . $newCounterId . '\'}")';
		}
		if ($this->quants['idInterf'] > 1)  return 'CONCAT("js:{idInterf:\'",b.idInterf,"\'}")';
		return 'NULL';
	}
	protected function auto_axisY() {
		$unit = "({$this->line['CounterUnit']})";

		$f = str_replace('`', '', $this->line['field']);
		if ($f != 'Value' && $f != $this->line['aggr']) $unit = "({$f} {$unit})";
		return "'{$this->line['aggr']} {$unit}'";
	}

	protected function conf_SQL($sql) {
		$this->axisQuant = max($this->axisQuant, 1);
		$sql = array('sql' => preg_replace('/(\s*;\s*)+$/', '', $sql) . ';', 'eval' => true);
		$this->readonly['view'][] = $sql;
		//$this->show($sql);
	}
	protected function build_Data() {
		//if(!$this->protect['build_Data']) return $this;
		//$this->protect['build_Data']=false;
		if (!$this->readonly['view']) return;

		$arrSQL = [];
		$arrSQLParserd = [];
		$conn = $this->connect();
		foreach ($this->readonly['view'] as $vw) {
			$arrSQL[] = $vw['sql'];

			$sql = $this->build_view($vw);
			$arrSQLParserd[] = $sql;
			//$sql=$this->readonly['view'];
			//$this->show($sql);
			$res = $conn->query($sql);
			//$this->chart->sql=$_REQUEST;
			//$res=$conn->query($this->protect['SQL']);
			while ($res) {
				$this->readonly['fnParser'] = 'parser_HeaderLine';
				//$lines=[];
				$i = 0;
				//$this->show($this->chart->data->idQuery);
				while ($line = $res->fetch_assoc()) {
					//file_put_contents('/tmp/test',strftime('%F %T: '). __LINE__ .' '.($i++)."\n",FILE_APPEND);
					//$lines[]=$line;
					$this->{$this->readonly['fnParser']}($line);
				}
				//$this->showTable($lines);
				$res = $conn->next_result();
				$this->chart->data->idQuery++;
			}
		}
		if (($yMax = $this->yMax) && $yMax > $this->chart->max_axisX_y) {
			$this->out['axisY']['maximum'] = $yMax;
			//$this->show($this->out);
			//$axisY->maximum=$yMax;
			//->maximum=100;
		}
		//file_put_contents('/tmp/test',strftime('%F %T: '). __LINE__ ."\n",FILE_APPEND);
		$this->chart->SQL = implode($b = "\n-- -------- bordeaux ---------- --\n", $arrSQL);
		$this->chart->SQLParserd = implode($b, $arrSQLParserd);
		return $this;
	}

	public function sqlAndWhere_base($w = null) {
		$where = [];
		if ($w) $where[] = $w;
		if (($w = $this->sqlAndWhere_in('idInterfDev')))        $where[] = $w;
		if (($w = $this->sqlAndWhere_in('idInterf')))           $where[] = $w;
		if (($w = $this->sqlAndWhere_in('idDevice')))           $where[] = $w;
		if (($w = $this->sqlAndWhere_er('FNs')))                $where[] = $w;
		if (($w = $this->sqlAndWhere_er('Interface', 'filter'))) $where[] = $w;
		return implode(' AND ', $where);
	}
	public function sqlAndWhere_field_in_set($field, $rawField = null, $init = '') {
		$rawField = $this->sqlAndWhere_rawField($field, $rawField);
		($v = @$this->args[$rawField]) || ($v = @$this->protect[$rawField]);
		return $v ? "{$init}FIND_IN_SET('{$v}',{$field})" : '';
	}
	public function sqlAndWhere_in($field, $rawField = null, $init = '') {
		$rawField = $this->sqlAndWhere_rawField($field, $rawField);
		($v = @$this->args[$rawField]) || ($v = @$this->protect[$rawField]);
		return $v ? "{$init}{$field} IN ({$v})" : '';
	}
	public function sqlAndWhere_er($field, $rawField = null, $init = '') {
		$rawField = $this->sqlAndWhere_rawField($field, $rawField);
		($v = @$this->args[$rawField]) || ($v = @$this->protect[$rawField]);
		return $v ? "{$init}{$field} REGEXP '" . str_replace('\'', '\\\'', $v) . '\'' : '';
	}
	public function sqlAndWhere_rawField($field, $rawField) {
		if ($rawField) return $rawField;
		$aField = explode('.', $field);
		//($rawField=@$aField[1]) || ($rawField=@$aField[0]);
		return array_pop($aField);
	}
	public function sqlAndWhere_date() {
		$whereHst = '';
		$year = strftime('%Y');
		if ($this->protect['dtStart']) {
			if (preg_match('/^\d{4}/', $this->protect['dtStart'], $ret)) $year = $ret[0];
			$whereHst .= " AND db_IMS_Huawei.fn_UTC(h.DtUpdate,'{$this->zone}',null)>={$this->ck_dtEval($this->protect['dtStart'])}";
		}
		if ($this->protect['dtEnd']) {
			if (preg_match('/^\d{4}/', $this->protect['dtEnd'], $ret)) $year = $ret[0];
			$whereHst .= " AND db_IMS_Huawei.fn_UTC(h.DtUpdate,'{$this->zone}',null)<={$this->ck_dtEval($this->protect['dtEnd'], '23:59:59')}";
		}
		if ($this->protect['weeks'] != '') {
			$ret = preg_split('/[^0-9-]+/', $this->protect['weeks']);
			$out = [];
			foreach ($ret as $k => $v) if (is_numeric($v)) {
				if ($v <= 0) {
					$d = explode('-', strftime('%Y-%V', strtotime("$v WEEK")));
					$year = $d[0];
					$week = $d[1];
					$out[$year][] = $week;
				} else $out[$year][] = $v + 0;
			}
			//$this->show($out);
			if ($out) {
				foreach ($out as $year => $week) {
					$week = implode(',', $week);
					$out[$year] = "(WEEK(fn_UTC(h.DtUpdate,'{$this->zone}',null),1) IN ($week) AND YEAR(h.DtUpdate)=$year)";
				}
				$whereHst .= ' AND (' . implode(' OR ', $out) . ')';
			}
		}
		return $whereHst;
	}

	protected function make_SQL_create_tmpBase($counterId) {
		$d = $this->Duration + 0;
		$devices = '';
		// AND n.idDevice=n.idDevice

		$Duration = $d ? $d : 'n.Duration';
		$sql = [];
		$sql[] = "DROP TABLE IF EXISTS {$this->tbBase};\n"; {
			$sql[] = "CREATE TEMPORARY TABLE {$this->tbBase} (
			`idSenderType` SMALLINT(6) UNSIGNED NULL DEFAULT NULL,
			`FnSetId` INT(11) NULL DEFAULT NULL,
			`FnSubSetId` INT(11) NULL DEFAULT NULL,
			`CounterId` INT(11) UNSIGNED NULL DEFAULT NULL,
			`CounterName` VARCHAR(200) NULL DEFAULT NULL,
			`idUnit` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
			`Aggr` ENUM('AVG','MAX','MIN','SUM','COUNT') NULL DEFAULT NULL,
			`Counter` VARCHAR(200) NULL DEFAULT NULL,
			`idCountDev` INT(11) UNSIGNED NULL DEFAULT NULL,
			`Duration` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
			`NumInterfaces` SMALLINT(5) UNSIGNED NULL DEFAULT NULL,
			`idDevice` INT(11) UNSIGNED NULL DEFAULT NULL,
			`Device` VARCHAR(64) NULL DEFAULT NULL,
			`idRed` VARCHAR(55) NULL DEFAULT NULL,
			`Red` VARCHAR(150) NULL DEFAULT NULL,
			`FNs` VARCHAR(15) NULL DEFAULT NULL,
			`idResult` INT(11) UNSIGNED NOT NULL DEFAULT '0',
			`idInterfDev` INT(11) UNSIGNED NULL DEFAULT NULL,
			`idInterf` INT(11) UNSIGNED NULL DEFAULT NULL,
			`Interface` VARCHAR(255) NULL DEFAULT NULL,
			`FnSetName` VARCHAR(100) NULL DEFAULT NULL,
			`FnSubSetName` VARCHAR(100) NULL DEFAULT NULL,
			
			`SenderType` VARCHAR(10) NULL DEFAULT NULL,
			`CounterUnit` VARCHAR(30) NULL DEFAULT NULL,
			PRIMARY KEY (`idResult`),
			INDEX `CounterId` (`CounterId`),
			INDEX `CounterName` (`CounterName`),
			INDEX `idDevice` (`idDevice`),
			INDEX `Device` (`Device`),
			INDEX `idRed` (`idRed`),
			INDEX `Red` (`Red`),
			INDEX `FNs` (`FNs`),
			INDEX `FnSetId` (`FnSetId`),
			INDEX `FnSubSetId` (`FnSubSetId`),
			INDEX `idCountDev` (`idCountDev`),
			INDEX `idInterf` (`idInterf`),
			INDEX `idInterfDev` (`idInterfDev`),
			INDEX `idSenderType` (`idSenderType`),
			INDEX `idUnit` (`idUnit`),
			INDEX `Interface` (`Interface`),
			INDEX `NumInterfaces` (`NumInterfaces`),
			INDEX `SenderType` (`SenderType`)
		);\n";
		} {
			$select = "SELECT
				c.idSenderType,c.FnSetId,c.FnSubSetId,c.CounterId,c.CounterName,c.idUnit,
				a.Aggr,IFNULL(a.Alias,c.CounterName) Counter,
				n.idCountDev,r.Duration,n.NumInterfaces,
				d.idDevice,d.Device,
				IFNULL(db_IMS_Huawei.fn_get_idRed(d.idDevice),d.idDevice) idRed,IFNULL(db_IMS_Huawei.fn_get_Red(d.idDevice),d.Device) Red,e.FNs,
				r.idResult,v.idInterfDev,v.idInterf,i.Interface,
				f.FnSetName,sf.FnSetName FnSubSetName,
				s.SenderType,u.CounterUnit
			FROM {$this->db}.tb_Counters c
			JOIN {$this->db}.tb_Counters_Aggr a          ON c.CounterId=a.CounterId 
			JOIN {$this->db}.tb_Counters_Devices n       ON c.CounterId=n.CounterId{$devices}{$this->sqlAndWhere_in('n.idDevice', null, ' AND ')}
			JOIN {$this->db}.tb_Elements e               ON n.idDevice=e.idDevice{$this->sqlAndWhere_er('e.FNs', null, ' AND ')}
			JOIN db_MainResource.tb_Devices d            ON n.idDevice=d.idDevice AND d.Enable
			JOIN {$this->db}.tb_Interfaces_Device v      ON v.idDevice=e.idDevice AND c.FnSubSetId=v.FnSubSetId{$this->sqlAndWhere_in('v.idInterfDev', null, ' AND ')}{$this->sqlAndWhere_in('v.idInterf', null, ' AND ')}
			JOIN {$this->db}.tb_Results r                ON c.CounterId=r.CounterId AND v.idInterfDev=r.idInterfDev AND r.Duration={$Duration}
			JOIN {$this->db}.tb_Interf i                 ON v.idInterf=i.idInterf{$this->sqlAndWhere_er('i.Interface', 'filter', ' AND ')}
			LEFT JOIN {$this->db}.tb_FunctionSets f      ON c.FnSetId=f.FnSetId 
			LEFT JOIN {$this->db}.tb_FunctionSets sf     ON c.FnSubSetId=sf.FnSetId 
			LEFT JOIN {$this->db}.tb_SenderesType s      ON c.idSenderType=s.idSenderType 
			LEFT JOIN {$this->db}.tb_Units u             ON c.idUnit=u.idUnit 
			WHERE c.CounterId IN ({$counterId})";
		}
		if (($w = @$this->args['where']) || ($w = @$this->protect['vw']['where'])) $select = "SELECT * FROM (\n$select\n) t WHERE {$w}";
		$sql[] = "INSERT IGNORE {$this->tbBase} \n{$select};\n";
		return $sql;
	}
	protected function make_SQL_base($counterId, $fields = []) {
		$fields = $fields ? ",\n\t\t" . implode(",\n\t\t", $fields) : '';
		$base = "
			SELECT 
				b.*{$fields}, 
				IFNULL(b.Red,b.Device) Redundance,
				IF(b.Red IS NULL, 0, INSTR(b.Red,'/')) isSum
			FROM {$this->tbBase} b
			WHERE b.CounterId IN ({$counterId})\n";
		//$this->show($base);
		return $base;
	}
	protected function ck_dtEval($value, $t = '00:00:00') {
		$v = strtotime($value);
		return $v ? '\'' . strftime('%F ' . $t, $v) . '\'' : $this->do_eval($value);
	}
	protected function rebuild_id($name, $val) {
		$val = preg_replace(array('/[^0-9,;]+/', '/[,;]+/', '/^,/', '/,$/'), array('', ',', '', ''), $val);
		$this->protect[$name] = $val ? $val : null;
	}
	protected function choiceTable() {
		if (!$this->protect['dtStart'] && !$this->protect['dtEnd']) return;
		$p = $this->protect['period'];
		$arrFn = array('D' => 'fn_tbl_day', 'M' => 'fn_tbl_month', 'Y' => 'fn_tbl_year',);
		$startDef = array('D' => '- 3 DAY', 'M' => '- 1 MONTH', 'Y' => '- 5 YEAR',);
		$aP = array('D' => array('w', 1), 'M' => array('d', 2), 'Y' => array('y', 2),);

		($dtStart = $this->protect['dtStart']) || ($dtStart = strftime('%F', strtotime('now ' . $startDef[$this->protect['period']])));
		($dtEnd = $this->protect['dtEnd'])     || ($dtEnd = strftime('%F'));

		$conn = $this->connect();
		$s = $conn->fastValue("SELECT {$this->dbHst}.{$arrFn[$p]}('$dtStart') v");
		$e = $conn->fastValue("SELECT {$this->dbHst}.{$arrFn[$p]}('$dtEnd')   v");
		if ($s == $e) $this->readonly['tbHst'] .= ' PARTITION (' . $aP[$p][0] . str_pad($s, $aP[$p][1], 0, STR_PAD_LEFT) . ')';
	}
	protected function conf_byidGraph() {
	}
	protected function configure_axis($axis) {
		if (array_key_exists($axis, $this->axis)) return $this->axis[$axis];
		$t = count($this->axis);
		$this->axisQuant = max($this->axisQuant, $t + 1);
		if ($t == 0) return $this->axis[$axis] = array('axisY' => 'axisY', 'axisYType' => 'null',);
		if ($t == 1) return $this->axis[$axis] = array('axisY' => 'axisY2', 'axisYType' => '\'secondary\'',);
		return false;
	}
	protected function add_Quants($lines) {
		if (!$lines) return;
		foreach ($this->quants as $k => $lns) if (array_key_exists($k, $lines[0])) {
			$itens = [];
			foreach ($lines as $l) $itens = array_merge($itens, explode(',', $l[$k]));
			$itens = array_unique($itens);

			$this->quants[$k] = count($itens);
			$this->line[$k] = $itens;
			//foreach($itens as $v) $this->quants[$k][$v]=$v;
		}
	}
	protected function sumarize($value, $separator = "\t") {
		$value = explode($separator, $value);
		$total = count($value);
		$val = $value[0];
		$er = '';
		$erEnd = '';
		$ifIni = null;
		$ifEnd = null;
		while (preg_match('/.*([,;_-].*?)$/', $val, $ret)) {
			$r = preg_quote($ret[1], '/');
			$rr = '/' . $r . '$/i';
			if (count(preg_grep($rr, $value)) == $total) {
				$erEnd = $r . $erEnd;
				$ifEnd += strlen($ret[1]);
				$value = preg_replace($rr, '', $value);
				$val = $value[0];
			} else break;
		}

		while (preg_match('/^(.*?[,;_-]).*/', $val, $ret)) {
			$r = preg_quote($ret[1], '/');
			$rr = '/^' . $r . '/i';
			if (count(preg_grep($rr, $value)) == $total) {
				$er .= $r;
				$ifIni += strlen($ret[1]);
				$value = preg_replace($rr, '', $value);
				$val = $value[0];
			} else break;
		}
		$r = preg_quote($val, '/');
		$rr = '/^' . $r . '/i';
		if (count(preg_grep($rr, $value)) == $total) return null;
		$tam = count(preg_grep('/./', $value));
		if ($tam)
			$er = $tam ? '^' . $er . '(.+)' . $erEnd . '$' : null;
		if ($ifIni) $ifIni++;
		$this->line['ifIni'] = $ifIni;
		$this->line['ifEnd'] = $ifIni;
		$this->line['erIf'] = $er;

		$this->readonly['view'][] = array(
			'sql' =>
			"SET @erIf={$this->val($er)};\n" .
				"SET @ifIni={$this->val($ifIni)}; SET @ifEnd={$this->val($ifEnd)};\n",
			'eval' => false
		);
		//$this->readonly['view'][]=array('sql'=>"SET @ifIni={$this->val($ifIni)}; SET @ifEnd={$this->val($ifEnd)};\n",'eval'=>false);
		return $er;
	}
	protected function connect() {
		static $conn = false;
		$conn = $conn ? $conn : ($conn = Conn::dsn($this->readonly['conn']));
		$conn->select_db($this->db);
		return $conn;
	}

	protected function build_view($vw) {
		return $vw['eval'] ? $this->do_eval($vw['sql']) : $vw['sql'];
	}
	protected function do_eval($text) {
		if (!preg_match_all('/\{(\$[^\}]+?)\}/', $text, $ret, PREG_SET_ORDER + PREG_OFFSET_CAPTURE)) return $text;
		$GLOBALS['this'] = &$this;
		while ($ret) {
			$item = array_pop($ret);
			$replacement = @eval('return ' . $item[1][0] . ';');
			$start = $item[0][1];
			$length = strlen($item[0][0]);
			$text = substr_replace($text, $replacement, $start, $length);
		}
		return $text;
	}
	protected function parser_HeaderLine($line) {
		//foreach($line as $k=>$v) $this->chart->$k=$v;
		//show($line);
		$this->chart->set($line);
		$this->readonly['fnParser'] = 'parser_DataLine';
		return $this->parser_DataLine($line);
	}
	protected function parser_DataLine($line) {
		if (array_key_exists('json', $line) && ($json = (array)json_decode($line['json']))) {
			$i = reset($json);
			if (is_array($i) || is_object($i)) {
				foreach ($json as $l) $this->chart->data->add(array_merge($line, (array)$l));
			} else foreach ($json as $legendText => $y) {
				if (!is_numeric($legendText)) $line['legendText'] = $legendText;
				$line['y'] = $y;
				$this->chart->data->add($line);
			}
		} else $this->chart->data->add($line);
		return $this;
	}
	protected function val($val, $default = null) {
		if (is_null($val)) $val = $default;
		if (is_null($val)) return 'NULL';
		if (is_numeric($val)) return $val;
		if (is_array($val)) $val = implode(',', $val);

		$conn = $this->connect();
		return $conn->addQuote($val);
	}
	/**
	 *  @brief Brief description
	 *  
	 *  @param [in] $vars Variáveis que serão carregadas automaticamente no objeto
	 *  
	 *  @return Return description
	 *  
	 *  @details More details
	 */
	protected function in($val) {
		return strpos($val, ',') ? ' in (' . $val . ')' : '=' . $val;
	}

	protected function loadSession($vars = array('readonly', 'protect')) {
		$arr = $this->oSess->get();
		foreach ($vars as $k) if (array_key_exists($k, $arr)) {
			$this->$k = $arr[$k];
			unset($arr[$k]);
		}
		return $arr;
	}
	/**
	 *  @brief Salva variaveis da SESSION by SessControl
	 *  
	 *  @param [in] $vars Variáveis do objeto que serão salvas automaticamente
	 *  
	 *  @return DataGraph THIS Object
	 *  
	 *  @details More details
	 */
	protected function saveSession($arr = [], $vars = array('readonly', 'protect')) {
		if ($arr) $this->oSess->set($arr);
		foreach ($vars as $k) $this->oSess->$k = $this->$k;
		return $this;
	}
	public function find_in_set($key, $arr) {
		return array_key_exists($key, $arr) ? $arr[$key] : reset($arr);
	}
	public function request($key, $default = null) {
		$val = array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
		if (is_null($val)) return 'NULL';
		if (is_numeric($val)) return $val;
		return '"' . str_replace(array('\\', '"'), array('\\\\', '\\"'), $val) . '"';
	}
	public function request_raw($key, $default = null) {
		return array_key_exists($key, $_REQUEST) ? $_REQUEST[$key] : $default;
	}
	public function call_user_fn($fn_back, $param = []) {
		return call_user_func_array($fn_back, $param);
	}

	public function show_options($return = false) {
		$html = $this->__toString();
		$out = '';
		$out .= $this->element_scripts(); {
			$out .= '<div class="form-group line-1">' .
				$this->element_types() .
				$this->element_filter() .
				$this->element_groups() .
				$this->element_groupPattern() .
				'</div>';
		} {
			$out .= '<div class="form-group line-2">' .
				$this->element_periods() .
				$this->element_weeks() .
				$this->element_dateMinMax() .
				$this->element_height() .
				'</div>';
		} {
			$out .= '<div class="form-group line-3">' .
				$this->element_eqtos() .
				$this->element_FNs() .
				$this->element_total() .
				$this->element_joinRed() .
				$this->element_CounterId() .
				'</div>';
		}
		$out .= $this->element_buttons(); {
			$out = "<!-- line-container -->
		<div class='container'>
			{$html}
			<br>
			<form id='frmGraph' class='form-horizontal'>
			$out
			</form>
			{$this->element_message()}
		</div>
		";
		}
		if ($return) return $out;
		else print $out;
	}
	public function element_types() { {
			$aTypes = array(
				'line' => 'Line',
				'spline' => 'Spline',
				'column' => 'Column',
				'bar' => 'Bar',
				'stepLine' => 'StepLine',
				'area' => 'Area',
				'stepArea' => 'StepArea',
				'splineArea' => 'SplineArea',
				'stackedArea' => 'StackedArea',
				'stackedColumn' => 'StackedColumn',
				'stackedBar' => 'StackedBar',
				'stackedArea100' => 'StackedArea100',
				'stackedColumn100' => 'StackedColumn100',
				'stackedBar100' => 'StackedBar100',
				//'scatter'=>'Scatter','pie'=>'Pie','doughnut'=>'Doughnut','funnel'=>'Funnel','pyramid'=>'Pyramid','waterfall'=>'Waterfall',
			);
		}
		($type = $this->type) || ($type = key($aTypes)); {
			$label = '
		<label 
			for="type" 
			class="col-sm-1 control-label" 
			title="Tipo de construção do gráfico"
		>Types: </label>';
		}
		$input = FN_Main::createSelect('type', $type, $aTypes, array(
			'onchange' => '$(this)[0].form.submit()',
			'data-live-search' => 'true',
		));

		return "$label<div class='col-sm-2'>$input</div>";
	}
	public function element_filter() { {
			$label = '
		<label 
			for="Filter" 
			class="col-sm-1 control-label"
		>Filter: </label>';
		} {
			$input = '
		<input 
			name="Filter" 
			id="Filter" 
			type="text" 
			class="form-control" 
			value="' . htmlentities($this->filter) . '" 
			placeholder="RegExp"
			title="Expressão regular que delimitará quais Interfaces a serem mostradas no gráfico." 
		/>';
		}
		return "$label<div class='col-sm-2'>$input</div>";
	}
	public function element_groups() { {
			$label = '
		<label 
			for="group" 
			class="col-sm-1 control-label" 
			title="Agregador de dados:
		- None: Sem agregação (desligado)
		- AVG: Média Aritimética
		- MAX: Valor máximo
		- MIN: Valor mínimo
		- SUM: Somatória
		- COUNT: Contagem"
		>Group: </label>';
		}
		$aGroup = array('' => 'None', 'AVG' => 'AVG', 'MAX' => 'MAX', 'MIN' => 'MIN', 'SUM' => 'SUM', 'COUNT' => 'COUNT',);
		($group = $this->group) || ($group = key($aGroup));
		$input = FN_Main::createSelect('group', $group, $aGroup, array(
			'onchange' => '$(this)[0].form.submit()',
		));
		return "$label<div class='col-sm-2'>$input</div>";
	}
	public function element_groupPattern() {
		$value = htmlentities($this->groupPattern); {
			$input = '
		<input name="groupPattern" 
			id="groupPattern" 
			type="text" 
			class="form-control" 
			placeholder="RegExp Pattern Auto"
			value="' . $value . '" 
			title="Expressão Regular, 
	combinada com o agregador AVG/MAX/MIN/SUM/COUNT,
	que agrupará apenas as interfaces que 
	fizerem o Match com o padrão considerando:
		- Todo o padrão se não conter grupos
		- Apenas os grupos quando existir
		Valor padrão: .*(?=[,\/_-])" />';
		}
		return "<div class='col-sm-3'>$input</div>";
	}
	public function element_periods() { {
			$label = '
		<label 
			for="period" 
			class="col-sm-1 control-label" 
			title="Perído/Periodicidade do Gráfico:
		- Dia: Periodicidade do gráfico com detalhamento máximo dos últimos 1 à 2 dias
		- Semana: Periodicidade de hora em hora da última semana
		- Ano: Periodicidade de dia em dia do últimos 5 anos(*sujeito a alteração)"
		>Period: </label>';
		}
		$aPeriods = array('D' => 'Dia', 'M' => 'Mês', 'Y' => 'Ano');
		($period = $this->period) || ($period = key($aPeriods));
		$input = FN_Main::createSelect('period', $period, $aPeriods, array(
			'onchange' => '$(this)[0].form.submit()',
		));
		return "$label<div class='col-sm-1'>$input</div>";
	}
	public function element_weeks() { {
			$label = '
		<label 
			for="weeks" 
			class="col-sm-1 control-label"
		>Weeks: </label>';
		} {
			$input = '
		<input 
			name="weeks" 
			id="weeks" 
			class="form-control" 
			value="' . htmlentities($this->weeks) . '" 
			title="Semanas ex: 33,34" 
		/>';
		}
		return "$label<div class='col-sm-1'>$input</div>";
	}
	public function element_dateMinMax() {
		//<input name="Start" id="Start" type="datetime-local" class="form-control" value="'.htmlentities($g->Start).'" title="Data de início do gráfico" />
		//<input name="End" id="End" type="datetime-local" class="form-control" value="'.htmlentities($g->End).'" title="Data de fim do gráfico" />
		$oDtStart = new ElementCalendar('Start', $this->Start);
		$oDtEnd = new ElementCalendar('End', $this->End);
		$oDtEnd->edit = $oDtStart->edit = true;
		$oDtEnd->label = $oDtStart->label = null;
		$oDtEnd->showLabel = $oDtStart->showLabel = false;
		$oDtEnd->type = $oDtStart->type = 'date';
		$oDtEnd->max = $oDtStart->max = strftime('%F');
		return '
				<label for="' . $oDtStart->buildIdDisplay() . '" class="col-sm-1 control-label">Start: </label>
				<div class="col-sm-2">' . $oDtStart . '</div>
				<label for="' . $oDtEnd->buildIdDisplay() . '" class="col-sm-1 control-label">End: </label>
				<div class="col-sm-2">' . $oDtEnd . '</div>
		';
	}
	public function element_height() {
		if (!$this->height) $this->height = 600; {
			$label = '
		<label 
			for="height" 
			class="col-sm-1 control-label"
		>Height: </label>';
		} {
			$input = '
		<input 
			name="height" 
			id="height" 
			type="number" 
			class="form-control" 
			value="' . $this->height . '" 
			min="200"  
			max="2000" 
			title="Altura do gráfico (200~2000)" 
		/>';
		}
		return "$label<div class='col-sm-1'>$input</div>";
	}
	public function element_eqtos() {
		$aEqtos = $this->options_Devices($this->CounterId, $this->FNs); {
			$label = '
		<label 
			for="D" 
			class="col-sm-1 control-label"
		>Device: </label>';
		} {
			$input = FN_Main::createSelect(
				'D',
				$this->idDevice,
				$aEqtos['opts'],
				array(
					//'onchange'=>'$(this)[0].form.submit()',
					'multiple' => 'true',
					'data-size' => 10,
					//'data-width'=>'fit',
					'data-actions-box' => 'true',
					'data-live-search' => 'true',
					'onchange' => 'changeSelect(\'D\',\'idDevice\')',
				),
				$aEqtos['attr']
			);
		} {
			$input2 = '
		<input 
			name="idDevice" 
			id="idDevice" 
			type="hidden" 
			value="' . $this->idDevice . '" 
		/>';
		}
		return "$label<div class='col-sm-2'>$input$input2</div>";
	}
	public function element_FNs() { {
			$input = FN_Main::createSelect('E', $this->FNs, $this->options_FNs(), array(
				'multiple' => 'true',
				//'data-size'=>5,
				//'data-width'=>'fit',
				//'data-actions-box'=>'true',
				//'data-live-search'=>'true',
				//'onchange'=>'changeFn(this);',
				//'onchange'=>'$(this)[0].form.submit()',
				'onchange' => 'changeSelect(\'E\',\'FNs\')',
			));
		} {
			$input2 = '
		<input 
			name="FNs" 
			id="FNs" 
			type="hidden" 
			value="' . $this->FNs . '" 
		/>';
		}
		return "<div class='col-sm-2'>$input$input2</div>";
	}
	public function element_total() { {
			$input = FN_Main::createSelect('total', $this->total, array(0 => 'Det', 1 => 'Total', 2 => 'Ger'), array(
				//'onchange'=>'$(this)[0].form.submit()',
				//'data-size'=>5,
				//'data-width'=>'fit',
				//'data-actions-box'=>'true',
				//'data-live-search'=>'true',
				'onchange' => '$(this)[0].form.submit()',
			));
		}
		return "<div class='col-sm-1'>$input</div>";
	}
	public function element_joinRed() {
		$jr = new ElementCheck('joinRed', $this->joinRed);
		$jr->edit = true;
		$jr->title = 'Junta os 2 equipamentos redundantes caso estiver na lista de seleção';
		return "<div class='col-sm-1'>$jr</div>";
	}
	public function element_CounterId() { {
			$label = '
		<label 
			for="CounterId" 
			class="col-sm-1 control-label"
		>CounterId: </label>';
		} {
			$input = '
		<input 
			name="CounterId" 
			id="CounterId" 
			class="form-control" 
			value="' . $this->CounterId . '" 
		/>';
		}
		return "$label<div class='col-sm-4'>$input</div>";
	}
	public function element_buttons() {
		return '
			<div class="text-right buttons">
				<button type="submit" class="btn btn-success">Submit</button>
				<button type="button" class="btn btn-danger" onclick="xReset()">Reset</button>
				<button type="button" class="btn btn-primary" onclick="window.location=\'ims.php\'">Hide Graph</button>
			</div>
		';
	}
	public function element_scripts() {
		$OutHtml = OutHtml::singleton();
		//$g=new Graph_IMS();	$html=new CanvasJS('data_graph_ims.php?'.http_build_query($_GET),600);
		$OutHtml->jQueryScript['idDevice_select'] = '$("#D").on("hidden.bs.select", function (e, clickedIndex, isSelected, previousValue) {
			var aURI=$.uri2array(location.search.substr(1));
			var o=$("#idDevice")
			if(o.val()!=aURI["idDevice"]) o[0].form.submit();
		});
		';
		return '
		<script>
			function xReset(){
				var $form=$("#frmGraph"); //.children().val("");
				$form.find(":input").not(":button, :submit, :reset, :hidden, :checkbox, :radio").val("");
				$form.find(":checkbox, :radio").prop("checked", false);
				$form.find("select").prop("selectedIndex", 0); //.selectpicker("render");
			}
			function changeSelect(idSelect,idInput){
				var v=$("#"+idSelect).val() || [];
				$("#"+idInput).val(v.join(\',\'));
			}
		</script>';
	}
	public function element_message() {
		return '
		<div>
			Leitura Recomendada:
			<ul>
				<li>Expressões Regulares: <a href="http://aurelio.net/regex/guia/" target="_BLANK">http://aurelio.net/regex/guia/</a></li>
			</ul>
		</div>
		';
	}

	public function options_FNs() {
		$conn = $this->connect();
		$res = $conn->query($sql = "
			SELECT KeyEFn
			FROM tb_Keys_EFn
			ORDER BY `Ord`,KeyEFn
		");
		$opts = [];
		while ($line = $res->fetch_assoc()) $opts[$line['KeyEFn']] = $line['KeyEFn'];
		$res->close();
		return $opts;
	}
	public function options_Devices($CounterId, $fns) {
		$conn = $this->connect();
		$w = [];
		$w[] = 'IFNULL(e.FNs,"")!=""';
		if ($fns) $w[] = "e.FNs REGEXP '\\\b{$fns}\\\b'";
		if ($CounterId) $w[] = "d.idDevice IN (SELECT c.idDevice FROM tb_Counters_Devices c WHERE c.CounterId IN ($CounterId))";
		$w = implode(' AND ', $w);
		$res = $conn->query($sql = "
			SELECT 
				d.idDevice,d.Device,
				e.FNs, 
				fn_get_idRed(d.idDevice) idRed, fn_get_Red(d.idDevice) Red
			FROM tb_Elements e
			JOIN db_MainResource.tb_Devices d ON e.idDevice=d.idDevice AND d.Enable
			WHERE  $w
			GROUP BY d.idDevice
			ORDER BY e.FNs,d.Device
		");
		$opts = [];
		$attr = [];
		//show($sql);
		while ($line = $res->fetch_assoc()) {
			$k = $line['idDevice'];
			$opts[$line['FNs']][$k] = $line['Device'];
			if ($line['Red']) $attr[$k] = array('data-subtext' => $line['Red']);
		}
		return array('opts' => $opts, 'attr' => $attr);
	}
}
class DataGraph_common {
	public function show($value) {
		if (DataGraph::$verbose && Secure::$idUser == 2) print '<pre style="background-color:#ddd;">' . $this->whoCallerHtml() . print_r($value, true) . '</pre>';
		return $this;
	}
	public function showTable($value) {
		if (DataGraph::$verbose && Secure::$idUser == 2) {
			print '<pre style="background-color:#ddd;">' . $this->whoCallerHtml();
			showTable($value);
			print '</pre>';
		}
		return $this;
	}
	public function putFile($value) {
		//if(DataGraph::$verbose) 
		file_put_contents('/tmp/test', "{$this->whoCaller()}\n$value\n", FILE_APPEND);
		return $this;
	}
	public function whoCaller($id = 1) {
		$bt = debug_backtrace();
		$btF = $bt[$id + 1];
		$bt = $bt[$id];
		return "CALLER: {$bt['file']}[{$bt['line']}]:{$btF['class']}->{$btF['function']}()";
	}
	public function whoCallerHtml() {
		return "<div style='background-color:#000;color:#fff;'><b>{$this->whoCaller(2)}</b></div>";
	}
}
class DataGraph_Chart_Base extends DataGraph_common {
	public static $default = [];
	public $firstKey, $nick;
	public $pathId = [];
	protected $readonly = [], $protect = [];
	public $defaultDataSet = [];
	//protected $update=true;
	public $dataGraph, $dad, $hierarchy = [];

	public function __construct(&$dad, $nick = null) {
		$this->dad = &$dad;
		if (@$dad->dataGraph) $this->dataGraph = &$dad->dataGraph;
		else $this->dataGraph = &$dad;

		$this->nick = $nick;
		$this->pathId = $this->dad->pathId;
		if ($nick) $this->pathId[] = $nick;

		//show(get_class($this).' : '.implode(',',$this->pathId));
		$this->initProtect();
		$this->firstKey = key($this->protect);
		$hierarchy = [];
		foreach ($this::$default as $k => $v) {
			if (array_key_exists($k, $this->protect)) $this->protect[$k] = $v;
			elseif (array_key_exists($k, $this->hierarchy)) $hierarchy[] = $k;
		}

		if ($this->hierarchy) $this->startObj(array_keys($this->hierarchy));

		$this->defaultDataSet();
		$this->resetDefault();
		foreach ($hierarchy as $k) {
			if (is_array($this::$default[$k])) $this->hierarchy[$k]->set($this::$default[$k]);
			else $this->hierarchy[$k]->{$this->hierarchy[$k]->firstKey} = $this::$default[$k];
		}
	}
	public function __invoke() {
		$out = $this::$default;
		foreach ($this->hierarchy as $fld => &$obj) $out[$fld] = $obj->__invoke();
		return $out;
	}
	//public function __toString(){ $out=$this->__invoke();return json_encode($out);}
	//public function __sleep(){ return array('protect'); }
	//public function __wakeup(){ }
	//public static function __set_state($obj) { return $this->protect; }
	//public function __debugInfo() { return $this->protect; }
	public function __get($name) {
		if (($l = @$this->defaultDataSet[$name])) return $l['obj']->{$l[__FUNCTION__]}($l['key']);
	}
	public function __set($name, $value) {
		if (($l = @$this->defaultDataSet[$name])) {
			$l['obj']->{$l[__FUNCTION__]}($l['key'], $value);
			return true;
		}
	}
	public function getObj($name) {
		return @$this->hierarchy[$name];
	}
	protected function _get_item($name) {
		return @$this->dataGraph->out[$name];
	}
	protected function _set_item($name, $value) {
		if (is_null($value)) {
			if (array_key_exists($name, $this->dataGraph->out)) unset($this->dataGraph->out[$name]);
		} else {
			//$this->show("[$name] = $value");
			$this->dataGraph->out[$name] = $value;
		}
	}
	/*
	protected function _get_item($name){ $this->_get_ref($this->dataGraph->out,$this->_get_path($name)); }
	protected function _set_item($name,$value){
		$path=$this->_get_path($name);
		if(is_null($value)) $this->_del_ref($this->dataGraph->out,$path);
		else $this->_set_ref($this->dataGraph->out,$path,$value);
	}
	protected function _get_ref(&$arr,$path){
		if($path) {
			$k=array_shift($path);
			return array_key_exists($k,$arr)?$this->_get_ref($arr[$k],$path):null;
		}
		return $arr;
	}
	protected function _set_ref(&$arr,$path,$value){
		if($path) {
			$k=array_shift($path);
			if(!array_key_exists($k,$arr)) $arr[$k]=[];
			return $this->_set_ref($arr[$k],$path,$value);
		}
		$arr=$value;
	}
	protected function _del_ref(&$arr,$path){
		if(!$path) return;
		$k=array_shift($path);
		if(array_key_exists($k,$arr)) {
			if($path) return $this->_del_ref($arr[$k],$path);
			unset($arr[$k]);
		}
	}
	protected function _get_path($name){
		$path=$this->pathId;
		//show(get_class($this).':'.implode(',',$path));
		$path[]=$name;
		return $path;
	}
	*/
	public function resetDefault() {
		//$this->putFile(get_class($this));
		foreach ($this->protect as $k => $v) $this->$k = array_key_exists($k, $this::$default) ? $this::$default[$k] : $v;
		//foreach($this->hierarchy as $fld=>&$obj) $obj->resetDefault();
	}
	public function get_protect() {
		return $this->protect;
	}

	protected function initProtect() {
		$this->protect = [];
	}
	protected function startObj($nick) {
		if (is_array($nick)) {
			foreach ($nick as $v) $this->startObj($v);
		} else {
			$class = 'DataGraph_Chart_' . $nick;
			if (array_key_exists($nick, $this->hierarchy)) $this->hierarchy[$nick] = new $class($this, $nick);
			else $this->$nick = new $class($this, $nick);
		}
		return $this;
	}
	protected function defaultDataSet() {
		$this->defaultDataSet_hierarchy($this);
		//show(get_class($this));
		//show($this->defaultDataSet);
	}
	protected function defaultDataSet_hierarchy(&$obj, $nick = '') {
		$this->defaultDataSet_arr($obj, array_keys($this->protect), $nick);
		foreach ($this->hierarchy as $sub => &$o) {
			//$o->defaultDataSet_hierarchy($obj,$nick.$sub.'.');
			$o->defaultDataSet_item($obj, $o->firstKey, $nick . $sub);
			foreach ($o->defaultDataSet as $fld => &$line) {
				$key = $nick . $sub . '.' . $fld;
				if (!array_key_exists($key, $obj->defaultDataSet)) $obj->defaultDataSet[$key] = &$line;
			}
		}
	}
	protected function defaultDataSet_arr(&$obj, $arr, $nick = '', $defaultGetFn = '_get_item', $defaultSetFn = '_set_item') {
		foreach ($arr as $k) $this->defaultDataSet_item($obj, $k, $nick . $k, $defaultGetFn, $defaultSetFn);
	}
	protected function defaultDataSet_item(&$obj, $k, $key, $defaultGetFn = '_get_item', $defaultSetFn = '_set_item') {
		if (array_key_exists($key, $obj->defaultDataSet)) return;
		$obj->defaultDataSet[$key] = array(
			'obj' => &$this, //get_class($this),
			'key' => $k,
			'__get' => method_exists($this, $fn = 'get_' . $k) ? $fn : $defaultGetFn,
			'__set' => method_exists($this, $fn = 'set_' . $k) ? $fn : $defaultSetFn,
		);
	}
	/*
	public function resetProtect(){ 
		if($this->update) {
			array_walk($this->protect,function($v){$v=null;});
			foreach($this::$default as $k=>$v) $this->$k=$v;
			$this->id=null;
			$this->update=false;
		}
		foreach($this->hierarchy as $fld=>&$obj) $obj->resetProtect();
	}
	public function __invoke(){
		if($this->update) $this->apply($this->dataGraph->out,$this->pathId);
		$this->update=false;
		foreach($this->hierarchy as $fld=>&$obj) $obj->__invoke();
	}
	public function load(){
		if($this->update) $this->load_item($this->dataGraph->out,$this->pathId);
		$this->update=false;
		foreach($this->hierarchy as $fld=>&$obj) $obj->load();
	}
	protected function apply(&$arr,$path){
		if($path) {
			$k=array_shift($path);
			if(!array_key_exists($k,$arr)) $arr[$k]=[];
			return $this->apply($arr[$k],$path);
		}
		foreach($this->protect as $k=>$v) if(!is_null($v)) $arr[$k]=$v;
	}
	protected function load_item(&$arr,$path){
		if($path) {
			$k=array_shift($path);
			if(array_key_exists($k,$arr)) return $this->load_item($arr[$k],$path);
			return $this->resetProtect();
		}
		foreach($this->protect as $k=>$v) $this->protect[$k]=array_key_exists($k,$arr)?$arr[$k]:(array_key_exists($k,$this->defaultDataSet)?$this->defaultDataSet[$k]:null);
	}
	*/
	public function set(array &$val) {
		foreach ($val as $k => $v) if ($this->__set($k, $v)) unset($val[$k]);
	}
}
class DataGraph_Chart_L2 extends DataGraph_Chart_Base {
	protected function _get_item($name) {
		return @$this->dataGraph->out[$this->nick][$name];
	}
	protected function _set_item($name, $value) {
		if (is_null($value)) {
			if (!array_key_exists($this->nick, $this->dataGraph->out)) return;
			if (array_key_exists($name, $this->dataGraph->out[$this->nick])) unset($this->dataGraph->out[$this->nick][$name]);
		} else {
			//$this->show("[{$this->nick}][$name]=$value");
			//if(!array_key_exists($this->nick,$this->dataGraph->out)) $this->dataGraph->out[$this->nick]=[];
			$this->dataGraph->out[$this->nick][$name] = $value;
		}
	}
}
class DataGraph_Chart_L3 extends DataGraph_Chart_Base {
	protected function _get_item($name) {
		return @$this->dataGraph->out[$this->dad->nick][$this->nick][$name];
	}
	protected function _set_item($name, $value) {
		if (is_null($value)) {
			if (!array_key_exists($this->dad->nick, $this->dataGraph->out)) return;
			if (!array_key_exists($this->nick, $this->dataGraph->out[$this->dad->nick])) return;
			if (array_key_exists($name, $this->dataGraph->out[$this->dad->nick][$this->nick])) unset($this->dataGraph->out[$this->dad->nick][$this->nick][$name]);
		} else {
			//$this->show("[{$this->dad->nick}][{$this->nick}][$name]=$value");
			//if(!array_key_exists($this->dad->nick,$this->dataGraph->out)) $this->dataGraph->out[$this->dad->nick]=[];
			//if(!array_key_exists($this->nick,$this->dataGraph->out[$this->dad->nick])) $this->dataGraph->out[$this->dad->nick][$this->nick]=[];
			$this->dataGraph->out[$this->dad->nick][$this->nick][$name] = $value;
		}
	}
}
class DataGraph_Chart_L2_Array extends DataGraph_Chart_Base {
	protected $id = 0, $nameId = null, $arrId = [];

	protected function _get_item($name) {
		return @$this->dataGraph->out[$this->nick][$this->id][$name];
	}
	protected function _set_item($name, $value) {
		if (is_null($value)) {
			if (!array_key_exists($this->nick, $this->dataGraph->out)) return;
			if (!array_key_exists($this->id, $this->dataGraph->out[$this->nick])) return;
			if (array_key_exists($name, $this->dataGraph->out[$this->nick][$this->id])) unset($this->dataGraph->out[$this->nick][$this->id][$name]);
		} else {
			//$this->show("[{$this->nick}][{$this->id}][$name]=$value");
			//if(!array_key_exists($this->nick,$this->dataGraph->out)) $this->dataGraph->out[$this->nick]=[];
			//if(!array_key_exists($this->id,$this->dataGraph->out[$this->nick])) $this->dataGraph->out[$this->nick][$this->id]=[];
			$this->dataGraph->out[$this->nick][$this->id][$name] = $value;
		}
	}

	/*
	public function __invoke(){
		$path=$this->pathId;
		$path[]=is_null($this->id)?0:@$this->arrId[$this->id]['id']+0;
		$this->apply($this->dataGraph->out,$path);
		foreach($this->hierarchy as $fld=>&$obj) $obj->__invoke();
	}
	*/
	protected function _get_path($name) {
		$path = $this->pathId;
		$path[] = $this->id;
		$path[] = $name;
		return $path;
	}
	/*
	public function load(){
		$path=$this->pathId;
		$path[]=is_null($this->id)?0:@$this->arrId[$this->id]['id']+0;
		$this->load_item($this->dataGraph->out,$path);
		foreach($this->hierarchy as $fld=>&$obj) $obj->load();
	}
	*/
	public function makeId($id) {
		$this->nameId = $id;
		if (is_null($id)) $this->id = 0;
		else {
			if (array_key_exists($id, $this->arrId)) $this->id = $this->arrId[$id]['id'];
			else {
				$this->id = count($this->arrId);
				$this->arrId[$id] = array('id' => $this->id);
				foreach ($this->protect as $k => $v) if (!is_null($v)) $this->$k = $v;
				//$this->show($this->protect);
				return true;
			}
		}
		return false;
	}
}

class DataGraph_Chart extends DataGraph_Chart_Base {
	public static $default = [
		'exportEnabled' => true,
		'exportFileName' => 'Graph',
		'zoomEnabled' => true,
		'zoomType' => 'xy', //'x','x,y,xy'
		'creditText' => 'EstaleiroWeb',
		'creditHref' => 'http://estaleiroweb.com.br',
		'culture' => 'pt-br',
	];
	//public $title,$subtitles,$axisX,$axisY,$axisX2,$axisY2,$legend,$toolTip;
	public $hierarchy = [];
	public $data;

	public function __construct($dad, $nick = null) {
		$dummy=new DataGraph_Chart_Base($this);
		$this->hierarchy = [
			'title' => $dummy, 'subtitles' => $dummy,
			'axisX' => $dummy, 'axisY' => $dummy, 'axisX2' => $dummy, 'axisY2' => $dummy, 
			'legend' => $dummy, 'toolTip' => $dummy,
		];
		$config = Config::singleton();
		$this->default = array_merge($this->default, $config->dataGraph);
		parent::__construct($dad, $nick);
		$this->startObj('data');
	}
	protected function initProtect() {
		$this->protect = array(
			'height' => null, //$this->height,
			'interactivityEnabled' => null, //true,'false,true'
			'animationEnabled' => null, //false,'false,true'
			'animationDuration' => null, //1200,'1000,500,...
			'exportEnabled' => null,
			'exportFileName' => null, //.$this->head['Device'], 
			'zoomEnabled' => null,
			'zoomType' => null, //'x','x,y,xy'
			'theme' => null,    // light1,light2,dark1,dark2
			'backgroundColor' => null, //'white','yellow,#F5DEB3,...
			'culture' => null, //en
			'creditText' => null,
			'creditHref' => null,
			'colorSet' => null, //colorSet1,colorSet2,colorSet3
			'rangeChanging' => null, //function(e){alert( "Event Type : " + e.type );}
			'rangeChanged' => null, //function(e){alert( "Event Type : " + e.type );}
			'width' => null, //500,'380, 500, 720,...
			'dataPointMaxWidth' => null, //'auto','10, 20, 30,...'
			'dataPointMinWidth' => null, //1,'2, 10, 25,...
			'dataPointWidth' => null, //'auto','10, 20, 30,...'
			'SQL' => null,
			'SQLParserd' => null,

			'min_axisX_x' => null,
			'max_axisX_x' => null,
			'min_axisX_y' => null,
			'max_axisX_y' => null,
			'min_axisX_z' => null,
			'max_axisX_z' => null,

			'min_axisX2_x' => null,
			'max_axisX2_x' => null,
			'min_axisX2_y' => null,
			'max_axisX2_y' => null,
			'min_axisX2_z' => null,
			'max_axisX2_z' => null,
		);
	}
	public function defaultDataSet() {
		//foreach($this->hierarchy as $k=>$o) show($k.':'.get_class($o));

		$this->hierarchy[$k = 'title']->defaultDataSet_arr($this, array(
			'dockInsidePlotArea', 'fontColor', 'fontFamily', 'fontSize', 'fontStyle', 'fontWeight', 'margin',
			'maxWidth', 'padding', 'text', 'wrap', 'horizontalAlign', 'verticalAlign',
		));
		$this->hierarchy[$k = 'legend']->defaultDataSet_arr($this, array(
			'itemMaxWidth', 'itemTextFormatter', 'itemWidth', 'itemWrap', 'itemclick', 'itemmousemove', 'itemmouseout', 'itemmouseover', 'maxHeight', 'reversed',
		));
		$this->hierarchy[$k = 'toolTip']->defaultDataSet_arr($this, array(
			'borderColor', 'borderThickness', 'content', 'contentFormatter', 'cornerRadius', 'enabled', 'shared',
		));
		$this->hierarchy[$k = 'axisX']->defaultDataSet_arr($this, array(
			'labelAngle', 'labelAutoFit', 'interval', 'intervalType',
		));
		$this->hierarchy[$k = 'axisY']->defaultDataSet_arr($this, array(
			'interlacedColor', 'valueFormatString', 'includeZero',
			'logarithmBase', 'logarithmic', 'maximum', 'minimum', 'suffix', 'prefix', 'titleFontColor', 'titleFontFamily',
			'titleFontSize', 'titleFontStyle', 'titleFontWeight', 'titleMaxWidth', 'titleWrap', 'labelBackgroundColor',
			'labelFontColor', 'labelFontFamily', 'labelFontSize', 'labelFontStyle', 'labelFontWeight', 'labelFormatter',
			'labelMaxWidth', 'labelWrap', 'gridColor', 'gridDashType', 'gridThickness', 'tickColor', 'tickLength', 'tickThickness',
		));
		parent::defaultDataSet();
	}
}
class DataGraph_Chart_title extends DataGraph_Chart_L2 {
	public static $default = array(
		'fontSize' => 18, //'function(){return Auto.Calculated;}',
	);
	//public function __invoke(){ return $this->protect['text']?parent::__invoke():[]; }
	protected function initProtect() {
		$this->protect = array(
			'text' => null,
			'fontSize' => null, //'function(){return Auto.Calculated;}',
			'fontColor' => null, //'#dddddd',
			'backgroundColor' => null, //'red',
			'borderColor' => null,
			'borderThickness' => null,
			'cornerRadius' => null,
			'fontFamily' => null, //'Calibri, Optima, Candara, Verdana, Geneva, sans-serif',
			'fontStyle' => null,
			'fontWeight' => null,
			'horizontalAlign' => null,
			'margin' => null,
			'padding' => null,
			'verticalAlign' => null,
			'wrap' => null,
			'maxWidth' => null,
			'dockInsidePlotArea' => null,
		);
	}
}
class DataGraph_Chart_subtitles extends DataGraph_Chart_L2_Array {
	public static $default = array(
		'fontSize' => 16, //Number	Auto. Calculated	25, 30 ..
		'fontColor' => '#999999', //String	“#3A3A3A”	“red”, “yellow” ,”#FF0000″ ..
		'fontFamily' => 'Calibri, Optima, Candara, Verdana, Geneva, sans-serif', //String	“Calibri, Optima, Candara, Verdana, Geneva, sans-serif”	“arial” , “tahoma”, “verdana” ..
	);
	//public function __invoke(){ return $this->protect['text']?parent::__invoke():[]; }
	protected function initProtect() {
		$this->protect = array(
			'text' => null, //	String	null	“Chart Title”
			'fontSize' => null, //Number	Auto. Calculated	25, 30 ..
			'fontColor' => null, //String	“#3A3A3A”	“red”, “yellow” ,”#FF0000″ ..
			'fontFamily' => null, //String	“Calibri, Optima, Candara, Verdana, Geneva, sans-serif”	“arial” , “tahoma”, “verdana” ..
			'backgroundColor' => null, //	String	null	“red”, “yellow” , “#FF0000” ..
			'borderColor' => null, //	String	“black”	“red”, “yellow” ,”#FF0000″ ..
			'borderThickness' => null, //	Number	0	2,6 ..
			'cornerRadius' => null, //	Number	0	5,8, ..
			'fontStyle' => null, //	String	““normal””	“normal”,“italic”, “oblique”
			'fontWeight' => null, //	String	“bold”	“lighter”, “normal, “bold”, “bolder”
			'horizontalAlign' => null, //	String	““center””	“left”, “center”, “right”
			'margin' => null, //	Number	10	4, 12 ..
			'padding' => null, //	Number	0	5, 8 ..
			'verticalAlign' => null, //	String	“top”	“top”, “center”, “bottom”
			'wrap' => null, //	Boolean	true	true, false
			'maxWidth' => null, //	Number	Automatically calculated based on the chart size.	200, 400 etc.
			'dockInsidePlotArea' => null, //	Boolean	false	true, false
		);
	}
	public function set_text($name, $value = null) {
		if ($this->nameId != $value) $this->makeId($value);
		$this->_set_item($name, $value);
	}
}
class DataGraph_Chart_axis extends DataGraph_Chart_L2 {
	public $stripLines, $crosshair, $scaleBreaks;
	public $hierarchy = array('stripLines' => null, 'crosshair' => null, 'scaleBreaks' => null,);
	protected function initProtect() {
		$this->protect = array(
			//Attribute	Type	Default	Options/Examples	Remarks
			'title' => null, //	String	null	“Axis Y Title”	–
			'titleWrap' => null, //	Boolean	true	true, false	–
			'titleMaxWidth' => null, //	Number	Automatically Calculated based on Chart Size	150, 200	–
			'titleFontColor' => null, //	String	“#666666”	“red”, “#006400” ..	–
			'titleFontSize' => null, //	Number	Auto. Calculated	25, 30 ..	–
			'titleFontFamily' => null, //	String	“Calibri, Optima, Candara, Verdana, Geneva, sans-serif”	“arial” , “tahoma”, “verdana” ..	–
			'titleFontWeight' => null, //	String	“normal”	“lighter”, “normal, “bold”, “bolder”	–
			'titleFontStyle' => null, //	String	“normal”	“normal”,“italic”, “oblique”	–
			'margin' => null, //	Number	2	10, 12 ..	–
			'labelBackgroundColor' => null, //	String	“transparent”	“red”, “#fabd76”	–
			'labelMaxWidth' => null, //	Number	Automatically calculated based on the length of label	45,150, 60 ..	–
			'labelWrap' => null, //	Boolean	true	true, false	–
			'labelAutoFit' => null, //	Boolean	true	true, false	–
			'labelAngle' => null, //	Number	0	45,-45, 60 ..	–
			'labelFontFamily' => null, //	String	“Calibri, Optima, Candara, Verdana, Geneva, sans-serif”	“tahoma”, “verdana” ..	–
			'labelFontColor' => null, //	String	“grey”	“red”, “#006400” ..	–
			'labelFontSize' => null, //	Number	Auto. Calculated	25, 30 ..	–
			'labelFontWeight' => null, //	String	“normal”	“lighter”, “normal, “bold”, “bolder”	–
			'labelFontStyle' => null, //	String	“normal”	“normal”,“italic”, “oblique”	–
			'prefix' => null, //	String	null	“$”, “cat”..	–
			'suffix' => null, //	String	null	“USD”, “cat”..	–
			'valueFormatString' => null, //	String	null	“#,##0.##”	Auto Calculated
			'minimum' => null, //	Number	null	-100, 350	Auto Calculated
			'maximum' => null, //	Number	null	100, 350	Auto Calculated
			'interval' => null, //	Number	null	25, 40	Auto Calculated
			'intervalType' => null, //	String	null	25, 40	used with interval
			'reversed' => null, //	Boolean	false	true, false	–
			'logarithmic' => null, //	Boolean	false	true, false	–
			'logarithmBase' => null, //	Number	10	2,10 …	–
			'tickLength' => null, //	Number	5	15, 20	–
			'tickColor' => null, //	String	“#BBBBBB”	“red”, “#006400” ..	–
			'tickThickness' => null, //	Number	2	5, 8..	–
			'lineColor' => null, //	String	“#BBBBBB”	“red”, “#006400” ..	–
			'lineThickness' => null, //	Number	2	5, 8..	–
			'interlacedColor' => null, //	String	null	“#F8F1E4”, “#FEFDDF” ..	–
			'gridThickness' => null, //	Number	2	5, 8..	Inc. to see grid
			'gridColor' => null, //	String	“#BBBBBB”	“red”, “#006400” ..	–
			'includeZero' => null, //	Boolean	true	false, true	–
			'gridDashType' => null, //	String	“solid”	“dot”, “dash” etc.	–
			'lineDashType' => null, //	String	“solid”	“dot”, “dash” etc.	–
			'labelFormatter' => null, //	Function	–	–	–
		);
	}
}
class DataGraph_Chart_stripLines extends DataGraph_Chart_L3 {
	protected function initProtect() {
		$this->protect = array(
			//Attribute	Type	Default	Options/Examples
			'value' => null, //	Number	null	12
			'startValue' => null, //	Number	null	20
			'endValue' => null, //	Number	null	30
			'thickness' => null, //	Number	2	5,10,20
			'color' => null, //	String	“orange”	“green”,”#23EA23″
			'label' => null, //	String	“” (empty string)	“Threshold”,”Target”
			'labelPlacement' => null, //	String	“inside”	“inside”,”outside”
			'labelAlign' => null, //	String	“far”	“far”,”center”,”near”
			'labelWrap' => null, //	Boolean	true	true,false
			'labelMaxWidth' => null, //	Number	Automatically Calculated based on label length	100, 200…
			'labelBackgroundColor' => null, //	String	“#eeeeee”	“red”,”#fabd76″
			'labelFontFamily' => null, //	String	“arial”	“Arial, Trebuchet MS, Tahoma, sans-serif”
			'labelFontColor' => null, //	String	“orange”	“blue”,”#4135e9″
			'labelFontSize' => null, //	Number	12	18,19,20,22
			'labelFontWeight' => null, //	String	“normal”	“lighter”,”normal”,”bold”,”bolder”
			'labelFontStyle' => null, //	String	“normal”	“normal”,”oblique”,”italic”
			'showOnTop' => null, //	Boolean	false	true, false
			'lineDashType' => null, //	String	“solid”	“dot”, “dash” etc.
			'opacity' => null, //	Number	null	.1, .2, .5 etc.
			'labelFormatter' => null, //	Function	–
		);
	}
}
class DataGraph_Chart_crosshair extends DataGraph_Chart_L3 {
	protected function initProtect() {
		$this->protect = array(
			///Attribute	Type	Default	Options/Examples
			'enabled' => null, //	Boolean	true	false, true
			'snapToDataPoint' => null, //	Boolean	false	true, false
			'color' => null, //	String	“black”	“red”, “#FFF046″…
			'opacity' => null, //	Number	1	0.5, 0.8,…
			'thickness' => null, //	Number	1	5,10,20
			'lineDashType' => null, //	String	dash	“dot”, “dash”, “dashedDot”,…
			'valueFormatString' => null, //	String	Automatically calculated	“#,###.##”, “####.00”
			'label' => null, //	String	“” (Empty String)	“Custom Label”, “Crosshair Label”,…
			'labelWrap' => null, //	Boolean	true	true,false
			'labelMaxWidth' => null, //	Number	Automatically Calculated based on label length	100, 200…
			'labelBackgroundColor' => null, //	String	“grey”	“black”, “#E8E8E8”,…
			'labelFontFamily' => null, //	String	“arial”	“Arial, Trebuchet MS, Tahoma, sans-serif”
			'labelFontColor' => null, //	String	“white”	“blue”,”#4135e9″
			'labelFontSize' => null, //	Number	12	18,19,20,22
			'labelFontWeight' => null, //	String	“normal”	“lighter”,”normal”,”bold”,”bolder”
			'labelFontStyle' => null, //	String	“normal”	“normal”,”oblique”,”italic”
			'labelFormatter' => null, //	Function	–	–
		);
	}
}
class DataGraph_Chart_scaleBreaks extends DataGraph_Chart_L3 {
	protected function initProtect() {
		$this->protect = array(
			//Attribute	Type	Default	Options/Examples
			'autoCalculate' => null, //	Boolean	false	true, false
			'collapsibleThreshold' => null, //	String	“25%”	“40%”…	–
			'maxNumberOfAutoBreaks' => null, //	Number	2	0,1,2,3,4
			'spacing' => null, //	String/Number	Automatically Calculated	“2%”, 10
			'type' => null, //	String	“straight”	“straight”, “wavy”, “zigzag”
			'color' => null, //	String	“#FFFFFF”	“green”,”#23EA23″…
			'fillOpacity' => null, //	Number	.9	.2, .5
			'lineThickness' => null, //	Number	2	0,1,2..
			'lineColor' => null, //	String	“#E16E6E”	“red”, “#A45A23”
			'lineDashType' => null, //	String	“solid”	“dot”,”dash” etc.
		);
	}
}
class DataGraph_Chart_axisX extends DataGraph_Chart_axis {
	public static $default = array(
		//'title'=>'Tempo',
		'titleFontSize' => 14,
		'labelFontSize' => 12,
		//'labelAngle'=>-20,
		//'valueFormatString'=>'DDD DD/MM/YY HH:mm',
		//'valueFormatString'=>'DDD DD/MMM/YY HH:mm',
		'crosshair' => array('enabled' => true, 'opacity' => .4),
		//'tickColor'=>'DarkSlateBlue','tickLength'=>15,'tickThickness'=>5,
		//'gridColor'=>'#efefef','gridThickness'=>1,'gridDashType'=>'solid', //dot, dash
		//'interval'=>2, 'intervalType'=>'hour', 
		//'interlacedColor'=>'#eeeeee',
	);
}
class DataGraph_Chart_axisY extends DataGraph_Chart_axis {
	public static $default = array(
		//'title'=>$aggr.'('.$this->head['CounterUnit'].')',
		'titleFontSize' => 14,
		'labelFontSize' => 12,
		//'crosshair'=>array('enabled'=>null,'opacity'=>null),
		//'viewportMaximum'=>100,
		//'tickColor'=>'DarkSlateBlue','tickLength'=>15,'tickThickness'=>5,
		//'gridColor'=>'#efefef','gridThickness'=>1,'gridDashType'=>'solid', //dot, dash
		//'interlacedColor'=>'#efefff',
	);
}
class DataGraph_Chart_axisX2 extends DataGraph_Chart_axisX {
}
class DataGraph_Chart_axisY2 extends DataGraph_Chart_axisY {
}
class DataGraph_Chart_legend extends DataGraph_Chart_L2 {
	public static $default = array(
		'fontSize' => 12, //Number	Auto. Calculated	25, 30 ..
		'cursor' => 'pointer', //String	“default”	“pointer”, “crosshair”, …
		'itemclick' => 'js:click_legend', //Function	null	function(e) { },
		'itemmouseover' => 'js:mouseover_dataSeries', //Function	null	function(e) { },
		'itemmouseout' => 'js:mouseout_dataSeries', //Function	null	function(e) { },
	);
	protected function initProtect() {
		$this->protect = array(
			//Attribute	Type	Default	Options/Examples
			'fontSize' => null, //Number	Auto. Calculated	25, 30 ..
			'cursor' => null, //String	“default”	“pointer”, “crosshair”, …
			'itemclick' => null, //Function	null	function(e) { },
			'itemmouseover' => null, //Function	null	function(e) { },
			'itemmouseout' => null, //Function	null	function(e) { },
			'fontFamily' => null, //	String	“monospace, sans-serif,arial black”	“arial” , “tahoma”, “verdana” ..
			'fontColor' => null, //	String	“black”	“red”, “yellow” ,”#FF0000″ ..
			'fontWeight' => null, //	String	“normal”	“lighter”, “normal, “bold”, “bolder”
			'fontStyle' => null, //	String	“normal”	“normal”,“italic”, “oblique”
			'verticalAlign' => null, //	String	“bottom”	“top”, “center”, “bottom”
			'horizontalAlign' => null, //	String	“right”	“left”, “center”, “right”
			'itemmousemove' => null, //	Function	null	function(e) { },
			'reversed' => null, //	Boolean	false	true, false
			'maxWidth' => null, //	Number	Automatically calculated based on the chart size.	200, 300, etc.
			'maxHeight' => null, //	Number	Automatically calculated based on the chart size.	200, 300, etc.
			'itemMaxWidth' => null, //	Number	Automatically calculated based on the chart size.	200, 300, etc.
			'itemWidth' => null, //	Number	Automatically calculated based on the chart size.	200, 300, etc.
			'itemWrap' => null, //	Boolean	true	true, false
			'itemTextFormatter' => null, //	Function	null	function(e) {}
			'dockInsidePlotArea' => null, //	Boolean	false	true, false
		);
	}
}
class DataGraph_Chart_toolTip extends DataGraph_Chart_L2 {
	public static $default = array(
		//'content'=>'<b style=\'"\'color: {color};\'"\'>{legendText}</b><br><b>Data</b>: {x}<br><b>Valor</b>: {y} ',
		'contentFormatter' => 'js:toolTip',
		//'shared'=>true,
	);
	protected function initProtect() {
		$this->protect = array(
			//Attribute	Type	Default	Options/Examples
			'content' => null, //	Function, String	auto	function (e){….. }
			'enabled' => null, //	Boolean	true	true, false
			'shared' => null, //	Boolean	false	true, false
			'animationEnabled' => null, //	Boolean	true	true, false
			'borderColor' => null, //	String	dataPoint/dataSeries Color	“green”, “#FF0312”..
			'fontColor' => null, //	String	“black”	“green”, “#FF0312”..
			'fontStyle' => null, //	String	“italic”	“normal”, “italic”,”oblique”
			'fontSize' => null, //	Number	14	16, 12, etc
			'fontFamily' => null, //	String	“Calibri, Arial, Georgia, serif”	“arial” , “tahoma”, “verdana” ..
			'fontWeight' => null, //	String	“normal”	“lighter”, “normal”, “bold” , “bolder”
			'borderThickness' => null, //	Number	2	1, 3 etc
			'cornerRadius' => null, //	Number	5	1, 3 etc
			'reversed' => null, //	Boolean	false	true, false
			'contentFormatter' => null, //	Function	null	function(e) { }
			'backgroundColor' => null, //	String	“white”	“black”, “#FFFFFF” etc
		);
	}
}
class DataGraph_Chart_data extends DataGraph_Chart_L2_Array {
	public static $default = array(
		//'name'=>"<b>{$this->head['Label']}</b>: $Interface",
		//'legendText'=>$Interface,
		//'type'=>$this->type,
		'showInLegend' => true,
		//'xValueType'=>'dateTime',
		//'xValueFormatString'=>'DDDD DD/MMM/YYYY HH:mm:ss K',

		//'axisXType'=>'primary', //secondary
		//'visible'=>true,
		//'indexLabel'=>'{x}, {y}', //indexLabelPlacement: "outside",indexLabelOrientation: "horizontal",indexLabelMaxWidth,indexLabelWrap,indexLabelBackgroundColor 
		//'markerSize'=>7,'markerColor'=>'#FFFFFF','markerBorderThickness'=>1,'markerBorderColor'=>'#000000','markerType'=>'circle', //none, circle, square, cross, triangle, line
		//'cursor'=>'pointer',
		'click' => 'js:click_link',
		'mouseover' => 'js:mouseover_dataSeries',
		'mouseout' => 'js:mouseout_dataSeries',

		//'link'=>$link,//'target'=>'_blank',
	);
	public $allTypes = array(
		'line', 'spline', 'column', 'bar', 'area', 'splineArea',
		'stackedArea', 'stackedColumn', 'stackedBar',
		'stackedArea100', 'stackedColumn100', 'stackedBar100',
		'rangeArea', 'rangeBar', 'rangeColumn', 'rangeSplineArea',
		'stepLine', 'stepArea',
		'pie', 'doughnut', 'funnel', 'pyramid', 'bubble', 'scatter',
		'waterfall', 'ohlc', 'candlestick', 'error', 'boxAndWhisker',
	);
	public $idQuery = 0, $tp = 'column';
	public $hierarchy = array('dataPoints' => null,);
	private $__add;

	protected function initProtect() {
		$this->protect = array(
			//Attribute	Type	Default	Options/Examples	Remarks
			'type' => null, //	String	“column”	“line”,”pie”,”area”..etc.	–
			'name' => null, //	String	auto. named	“series1”, “data1”..	–
			'legendText' => null, //	String	“dataSeries1”, “dataSeries2” ..	“apples”, “oranges” ..	-auto when not set-
			'showInLegend' => null, //	Boolean	false	true,false	-auto when not set-
			'legendMarkerType' => null, //	String	“circle”	“circle”, “square”, “cross”, “triangle”	Applies to line, area, bubble, scatter charts
			'legendMarkerColor' => null, //	String	marker Color	“red”,”#1E90FF”..	Applies to line, area, bubble, scatter charts
			'axisXType' => null, //	String	“primary”	“primary”,”secondary”	–
			'axisYType' => null, //	String	“primary”	“primary”,”secondary”	–
			'axisXIndex' => null, //	Number	0	1,2,3…	–
			'axisYIndex' => null, //	Number	0	1,2,3…	–
			'xValueType' => null, //	String	auto	“number”,”dateTime”	–
			'bevelEnabled' => null, //	Boolean	false	true,false	Applies to all Bar & Column charts
			'color' => null, //	String	From theme	“red”,”#1E90FF”..	–
			'lineColor' => null, //	String	From color	“red”,”#1E90FF”..	–
			'lineDashType' => null, //	String	“solid”	“dot”, “dash”	Sets the Line Dash Type for all Line and Area Charts.
			'lineThickness' => null, //	Number	2	3,4	Applies to pie & doughtnut charts
			'connectNullData' => null, //	Boolean	false	true, false	–
			'nullDataLineDashType' => null, //	String	“dash”	“solid”,”dot”..	–
			'visible' => null, //	Boolean	true	true, false	–
			'fillOpacity' => null, //	Number	.7 for Area Charts and 1 for all other chart types	1, .5 etc	–
			'xValueFormatString' => null, //	String	null	“##.0#”, “DD-MMM-YYYY” etc	–
			'yValueFormatString' => null, //	String	null	“##.0#” etc	–
			'zValueFormatString' => null, //	String	null	“##.0#” etc	z value is used only in Bubble chart
			'percentFormatString' => null, //	String	null	“##.0#” etc	–
			'startAngle' => null, //	Number	0	25,-45,180..	Applies to onle Pie & Doughnut charts, in Degrees
			'indexLabel' => null, //	String	null	“{label}”, “Win”, “x: {x}, y: {y} ”	Supports Keyword
			'indexLabelMaxWidth' => null, //	Number	Automatically calculated based on the length of indexLabel	2, 10, 40 etc	–
			'indexLabelWrap' => null, //	Boolean	true	true, false	–
			'indexLabelPlacement' => null, //	String	“outside”	“inside”,”outside”	Applies to Bar, Column, Pie And Doughnut
			'indexLabelOrientation' => null, //	String	“horizontal”	“horizontal”,”vertical”	Applies to all chart types except for pie and doughnut
			'indexLabelBackgroundColor' => null, //	String	null	“red”,”#1E90FF”..	–
			'indexLabelFontStyle' => null, //	String	“normal”	“normal”, “italic”, “oblique”	–
			'indexLabelFontColor' => null, //	String	“grey”	“red”,”#1E90FF”..	–
			'indexLabelFontSize' => null, //	Number	18	16,20,24..	–
			'indexLabelFontFamily' => null, //	String	“Calibri, Optima, Candara, Verdana, Geneva, sans-serif”	“arial”, “calibri”, “tahoma”..	–
			'indexLabelFontWeight' => null, //	String	“normal”	“lighter”, “normal” ,”bold” , “bolder”	–
			'indexLabelLineColor' => null, //	String	“lightgrey”	“red”, “#1E90FF”..	–
			'indexlabelLineThickness' => null, //	Number	2	2,4	–
			'indexLabelLineDashType' => null, //	String	“solid”	“dot”, “dash”	Sets the Dash Type for indexLabel’s line.
			'indexLabelFormatter' => null, //	Function	null	function(e) { }	–
			'toolTipContent' => null, //	String	auto.	“{y} units”	Supports Keywords
			'markerType' => null, //	String	“circle”	“circle”, “square”, “cross”, “triangle”, “line”	Applies to line, area, bubble, scatter charts
			'markerColor' => null, //	String	auto. takes dataSeries/dataPoint color	“red”, “#1E90FF”..	Applies to line, area, bubble, scatter charts
			'markerSize' => null, //	Number	auto. Zero for area chart	5, 10..	Applies to line, area, bubble, scatter charts
			'markerBorderColor' => null, //	String	marker Color	“red”, “#1E90FF”..	Applies to line, area, bubble, scatter charts
			'markerBorderThickness' => null, //	Number	1	4,6..	Applies to line, area, bubble, scatter charts
			'explodeOnClick' => null, //	Boolean	true	true, false	Applies to Pie And Doughnut
			'legendMarkerBorderColor' => null, //	String	dataSeries Color	“red”,”#1E90FF”..	–
			'legendMarkerBorderThickness' => null, //	Number	0	2, 4 etc	–
			'risingColor' => null, //	String	“white”	“red”, “#DD7E86” etc	risingColor property can only be used with candle stick chart
			'click' => null, //	Function	null	function(e) { },	–
			'mouseover' => null, //	Function	null	function(e) { },	–
			'mouseout' => null, //	Function	null	function(e) { },	–
			'mousemove' => null, //	Function	null	function(e) { },	–
			'cursor' => null, //	String	“default”	“pointer”, “crosshair”, etc	Sets cursor type for the dataSeries
			'highlightEnabled' => null, //	Boolean	true	false, true	–

			'idGraph' => null,
			'idKPI' => null,
			'CounterId' => null,
			'CounterName' => null,
			'Counter' => null,
			'idDevice' => null,
			'Device' => null,
			'Red' => null,
			'idInterfDev' => null,
			'idInterf' => null,
			'Interface' => null,
			'path' => null,
			'filter' => null,
			'labelReplace' => null,
			'period' => null,
			'dtStart' => null,
			'dtEnd' => null,
			'group' => null,
			'conf' => null,
			'link' => null,
			'total' => null,
			'FnSetId' => null,
			'FnSetName' => null,
			'FnSubSetId' => null,
			'FnSubSetName' => null,
			'CounterUnit' => null,
			'Aggr' => null,

			'y' => null,
			'x' => null,
			'z' => null,
			'label' => null,
			'exploded' => null,
		);
	}
	protected function defaultDataSet() {
		$this->dataGraph->out[$this->nick] = [];

		foreach ($this->allTypes as $tp) $this->defaultDataSet[$tp] = [];

		$arrTypes = array('column', 'bar', 'stackedColumn', 'stackedBar', 'stackedColumn100', 'stackedBar100', 'waterfall',);
		$arr = array('indexLabelPlacement', 'indexLabelOrientation',);
		$this->defaultDataSet_byTypeDp($arrTypes, $arr);
		$this->defaultDataSet_byTypeDp('waterfall', array('isIntermediateSum', 'isCumulativeSum',));

		$arrTypes = array('line', 'area', 'spline', 'splineArea', 'stepLine', 'scatter', 'stackedArea', 'stackedArea100', 'rangeArea', 'rangeSplineArea', 'bubble',);
		$arr = array('markerSize', 'markerType', 'markerColor', 'markerBorderColor', 'markerBorderThickness',);
		$this->defaultDataSet_byTypeDp($arrTypes, $arr);

		$arrTypes = array('scatter', 'bubble', 'pie', 'doughnut', 'funnel', 'pyramid',);
		$arr = array(
			'name', 'legendText', 'indexLabel', 'color', 'lineColor', 'lineDashType', 'showInLegend',
			'legendMarkerType', 'legendMarkerColor', 'legendMarkerBorderColor', 'legendMarkerBorderThickness',
			'markerType', 'markerColor', 'markerSize', 'markerBorderColor', 'markerBorderThickness',
			'indexLabelLineDashType', 'indexLabelFormatter',
		);
		$this->defaultDataSet_byTypeDp($arrTypes, $arr);

		$arrTypes = array('pie', 'doughnut', 'funnel', 'pyramid',);
		$arr = array('explodeOnClick', 'startAngle',);
		$this->defaultDataSet_byTypeDp($arrTypes, $arr);

		$arr2 = array('y', 'x', 'z', 'label', 'exploded',);

		foreach ($this->allTypes as $this->tp) {
			$this->defaultDataSet_byTypeDp($this->tp, $arr2);
			$this->defaultDataSet_hierarchy($this);
		}
		$this->tp = key($this->allTypes);

		//show(get_class($this));
		//$this->show(get_class($this->defaultDataSet['line']['xValueType']['obj']));
	}
	protected function defaultDataSet_hierarchy(&$obj, $nick = '') {
		$this->defaultDataSet_arr($obj, array_keys($this->protect));
		$this->defaultDataSet_arr($obj, array_keys($this->protect), 'data.');
		foreach ($this->hierarchy as $sub => &$o) {
			foreach ($o->defaultDataSet as $fld => &$line) {
				$key = $nick . $sub . '.' . $fld;
				if (!array_key_exists($key, $obj->defaultDataSet[$this->tp])) $obj->defaultDataSet[$this->tp][$key] = &$line;
			}
		}
	}
	protected function defaultDataSet_item(&$obj, $k, $key, $defaultGetFn = '_get_item', $defaultSetFn = '_set_item') {
		if (array_key_exists($key, $obj->defaultDataSet[$this->tp])) return;
		$obj->defaultDataSet[$this->tp][$key] = array(
			'obj' => &$this, //get_class($this),
			'key' => $k,
			'__get' => method_exists($this, $fn = 'get_' . $k) ? $fn : '_get_item',
			'__set' => method_exists($this, $fn = 'set_' . $k) ? $fn : '_set_item',
		);
	}

	public function __get($name) {
		if (($l = @$this->defaultDataSet[$this->tp][$name])) return $l['obj']->{$l[__FUNCTION__]}($l['key']);
		if (array_key_exists($name, $this->hierarchy)) return $this->hierarchy[$name];
	}
	public function __set($name, $value) {
		if (($l = @$this->defaultDataSet[$this->tp][$name])) {
			$l['obj']->{$l[__FUNCTION__]}($l['key'], $value);
			return true;
		}
	}
	protected function _set_item($name, $value) {
		if (is_null($value)) return;
		//$this->_set_value($name,$value);
		$this->dataGraph->out[$this->nick][$this->id][$name] = $value;
	}
	protected function defaultDataSet_byTypeDp($type, $arr) {
		if (is_array($type)) {
			foreach ($type as $t) $this->defaultDataSet_byTypeDp($t, $arr);
			return;
		}
		foreach ($arr as $fld) {
			$this->defaultDataSet[$type][$fld] = &$this->hierarchy['dataPoints']->defaultDataSet[$fld];
			$this->defaultDataSet[$type][$fld]['dataPoints'] = true;
		}
	}

	public function set_legendText($name, $value) {
		$this->_set_item($name, $value);
		if (is_null($value) || $value == '') $this->_set_item('showInLegend', false);
	}
	/*public function set_showInLegend($name,$value){
		$this->_set_item($name,$value);
		$this->_set_item('showInLegend',!(is_null($value) || $value==''));
	}*/
	public function set_type($name, $value) {
		$this->tp = $value;
		$this->_set_item($name, $value);
	}
	public function set_link($name, $value) {
		$this->_set_item('cursor', $value ? 'pointer' : null);
		$this->_set_item($name, $value);
	}

	protected function makeyType($line, $k) {
		if ($this->tp != $line[$k]) $this->prepare($line);
	}
	protected function makeyType_none($line, $k) {
		$this->tp = 'column';
	}
	protected function makeId_name($line, $k) {
		$this->makeId($line[$k]);
	}
	protected function makeId_legendText($line, $k) {
		$this->makeId($this->idQuery . '#' . $line[$k]);
	}
	protected function makeId_none($line, $k) {
		$this->makeId($this->idQuery);
	}

	protected function prepare($line) {
		$this->__add = [];

		if (($this->tp = $line[$tpKey = 'type']) || ($this->tp = $line[$tpKey = 'data.type'])) {
			$this->__add['type'] = array('fn' => 'makeyType', 'key' => $tpKey);
		} else {
			$this->tp = 'column';
			$this->__add['type'] = array('fn' => 'makeyType_none', 'key' => null);
		}

		$this->prepare_item($line, $k = 'data.type') || $this->prepare_item($line, $k = 'type');
		if ($this->prepare_item($line, $k = 'data.name') || $this->prepare_item($line, $k = 'name')) $this->__add['id'] = array('fn' => 'makeId_name', 'key' => $k);
		elseif ($this->prepare_item($line, $k = 'data.legendText') || $this->prepare_item($line, $k = 'legendText')) $this->__add['id'] = array('fn' => 'makeId_legendText', 'key' => $k);
		else $this->__add['id'] = array('fn' => 'makeId_none', 'key' => null);

		foreach ($line as $k => $v) $this->prepare_item($line, $k);
		//$this->show(array_keys($this->__add['fields']));
	}
	protected function prepare_item(&$line, $k, $ord = 3) {
		if (
			!array_key_exists($k, $line) ||
			!array_key_exists($k, $this->defaultDataSet[$this->tp])
		) return false;
		unset($line[$k]);
		$this->__add['fields'][$k] = &$this->defaultDataSet[$this->tp][$k];
		return true;
	}
	public function add($line) {
		if (!$this->__add) $this->prepare($line);

		//$this->show($line);
		//$this->show($this->nameId);
		$o = $this->__add['type'];
		$this->{$o['fn']}($line, $o['key']);
		$o = $this->__add['id'];
		$this->{$o['fn']}($line, $o['key']);

		//$this->show("[{$this->nick}][{$this->id}]={$this->nameId};");
		$this->_set_item('idQuery', $this->idQuery);

		foreach ($this->__add['fields'] as $k => &$o) {
			//$this->show(get_class($o['obj']).'->'.$o['__set'].'('.$o['key'].','.$line[$k].')');
			$o['obj']->{$o['__set']}($o['key'], $line[$k]);
		}
		//$this->hierarchy['dataPoints']->add();
		//exit;
	}
}
class DataGraph_Chart_dataPoints extends DataGraph_Chart_L2_Array {
	protected function initProtect() {
		$this->protect = array(
			//Attribute	Type	Default	Options/Examples	Remarks
			'y' => null, //	number	null	34, 26, 28..	–
			'x' => null, //	number	null	10,20,30 .. | new Date(2012, 12, 15)	-auto-
			'z' => null, //	number	null	240, 300, 400	Applies only to Bubble Charts
			'name' => null, //	string	auto.	“apple”, “mango”	–
			'label' => null, //	string	null	“label1”,”label2″…	–
			'indexLabel' => null, //	String	null	“{label}”, “Win”, “x: {x}, y: {y} ”	Supports Keyword
			'indexLabelWrap' => null, //	Boolean	true	true, false	–
			'indexLabelMaxWidth' => null, //	Number	Automatically calculated based on the length of indexLabel	2, 10, 40 etc	–
			'indexLabelPlacement' => null, //	string	“outside”	“inside”,”outside”	–
			'indexLabelOrientation' => null, //	string	“horizontal”	“horizontal”,”vertical”	Doesn’t apply in pie/doughnut chart
			'indexLabelBackgroundColor' => null, //	string	null	“red”,”#1E90FF”..	–
			'indexLabelFontColor' => null, //	string	“grey”	“red”,”#1E90FF”..	–
			'indexLabelFontSize' => null, //	number	18	16,20,24..	–
			'indexLabelFontStyle' => null, //	string	“normal”	“normal”, “italic”, “oblique”	–
			'indexLabelFontFamily' => null, //	string	“Calibri, Optima, Candara, Verdana, Geneva, sans-serif”	“arial”, “calibri”, “tahoma”..	–
			'indexLabelFontWeight' => null, //	string	“normal”	“lighter”, “normal” ,”bold” , “bolder”	–
			'indexLabelLineColor' => null, //	string	“lightgrey”	“red”, “#1E90FF”..	–
			'indexLabelLineThickness' => null, //	number	2	4,6..	Applies to pie & doughtnut charts
			'indexLabelLineDashType' => null, //	String	“solid”	“dot”, “dash”…	–
			'indexLabelFormatter' => null, //	Function	null	function(e) { }	–
			'toolTipContent' => null, //	string	auto.	“{y} units”	Supports Keywords
			'exploded' => null, //	boolean	false	true, false	Applies to pie & doughtnut charts
			'color' => null, //	string	from theme	“red”,”#1E90FF”..	–
			'lineColor' => null, //	string	dataSeries lineColor	“red”,”#1E90FF”..	–
			'lineDashType' => null, //	string	dataSeries lineDashType	“dash”, “dot”..	–
			'legendText' => null, //	string	“dataPoint1”, “dataPoint2” ..	“apples”, “oranges” ..	-auto when not set-
			'legendMarkerType' => null, //	string	“circle”	“circle”, “square”, “cross”, “triangle”	–
			'legendMarkerColor' => null, //	string	marker Color	“red”,”#1E90FF”..	–
			'legendMarkerBorderColor' => null, //	string	dataSeries marker Color	“red”,”#1E90FF”..	–
			'legendMarkerBorderThickness' => null, //	Number	0	2, 4 etc	–
			'markerType' => null, //	string	“circle”	“circle”, “square”, “cross”, “triangle”	Applies to line, area, bubble, scatter charts
			'markerColor' => null, //	string	dataSeries Color	“red”,”#1E90FF”..	Applies to line, area, bubble, scatter charts
			'markerSize' => null, //	number	auto. Zero for area chart	5,10..	Applies to line, area, bubble, scatter charts
			'markerBorderColor' => null, //	string	dataSeries color.	“red”,”#1E90FF”..	Applies to line, area, bubble, scatter charts
			'markerBorderThickness' => null, //	number	1	4,6..	Applies to line, area, bubble, scatter charts
			'click' => null, //	function	null	function(e) { },	–
			'mouseover' => null, //	function	null	function(e) { },	–
			'mouseout' => null, //	function	null	function(e) { },	–
			'mousemove' => null, //	function	null	function(e) { },	–
			'cursor' => null, //	String	“default”	“pointer”, “crosshair”, etc	Sets cursor type for the dataPoint
			'highLightEnabled' => null, //	Boolean	true	false, true	Enables or Disables highlighting of dataPoint on mouse hover.
			'showInLegend' => null, //	Boolean	false	true, false	–
			'isIntermediateSum' => null,
			'isCumulativeSum' => null,
			'explodeOnClick' => null,
			'startAngle' => null,
		);
	}

	public function makeId($fld) {
		//$this->show("[{$this->dad->nick}][{$this->dad->id}][{$this->nick}][{$this->id}]=($fld){$this->nameId};");
		$idDad = $this->dad->id;
		$this->nameId = $idDad;
		if (array_key_exists($idDad, $this->arrId)) {
			if ($this->arrId[$idDad]['fld'] == $fld) {
				$this->id = ++$this->arrId[$idDad]['id'];
				return false;
			}
			$out = !@$this->arrId[$idDad]['flds'][$fld];
			$this->arrId[$idDad]['flds'][$fld] = true;
			return $out;
		}
		$this->arrId[$idDad] = array(
			'id' => $this->id = 0,
			'fld' => $fld,
			'axis' => ($this->dad->axisXType && $this->dad->axisXType == 'secondary') ? 'axisX2' : 'axisX',
			'flds' => [],
		);
		//$this->show($fld);
		return true;
	}
	protected function _get_item($name) {
		return @$this->dataGraph->out[$this->dad->nick][$this->dad->id][$this->nick][$this->id][$name];
	}
	protected function _set_item($name, $value) {
		if (is_null($value)) return;
		$this->makeId($name);
		$this->_set_value($name, $value);
	}
	protected function _set_value($name, $value) {
		$this->dataGraph->out[$this->dad->nick][$this->dad->id][$this->nick][$this->id][$name] = $value;
	}
	public function set_x($name, $value) {
		if (is_null($value)) return;
		if ($this->makeId($name)) {
			$xValueType = preg_match('/(\d{2}|\d{4})-(\d{1,2})-(\d{1,2})(?:[T ](\d{1,2})(?::(\d{1,2})(?::(\d{1,2}))?)?)?(?:\.(\d+))?/i', $value) ? 'dateTime' : 'number';
			$this->arrId[$this->nameId]['fnX'] = 'parserX_' . $xValueType;

			if (($x = $this->dad->xValueType))  $xValueType = $x;
			else $this->dad->xValueType = $xValueType;
			if ($xValueType == 'dateTime') {
				$this->setAxis('labelAngle', -20);
				$this->setAxis('valueFormatString', 'DD/MM/YY HH:mm'); //DDD DD/MM/YY HH:mm
				if (!$this->dad->xValueFormatString) $this->dad->xValueFormatString = 'DDD DD/MMM/YYYY HH:mm:ss K';
				//if(!$this->dad->toolTipContent) $this->dad->toolTipContent='<b style=\'"\'color: {color};\'"\'>{legendText}</b><br><b>Data</b>: {x}<br><b>Valor</b>: {y}{CounterUnit}';
				//$toolTip=&$this->dataGraph->chart->getObj('toolTip');
				//if(!$toolTip->content) $toolTip->content='<b style=\'"\'color: {color};\'"\'>{legendText}</b><br><b>Data</b>: {x}<br><b>Valor</b>: {y}{CounterUnit}';
			}
		}
		$this->_set_value($name, $this->{$this->arrId[$this->nameId]['fnX']}($value));
		$this->minMax($value, $name)->minMax($value, $name, 'max');
	}
	public function set_y($name, $value) {
		if (is_null($value)) return;
		if ($this->makeId($name)) $this->arrId[$this->nameId]['fnY'] = strpos($value, ',') === false ? 'parserY_number' : 'parserY_array';

		return $this->{$this->arrId[$this->nameId]['fnY']}($value);
	}
	public function set_z($name, $value) {
		if (is_null($value)) return;
		$this->makeId($name);
		$value += 0;
		$this->_set_value($name, $value);
		$this->minMax($value, $name)->minMax($value, $name, 'max');
	}
	public function set_legendText($name, $value) {
		if (is_null($value)) return;
		$this->makeId($name);
		$this->_set_value($name, $value);
		$this->_set_value('showInLegend', true);
	}

	protected function setAxis($name, $value) {
		$axis = &$this->dataGraph->chart->getObj($this->arrId[$this->nameId]['axis']);
		//$this->show(get_class($axis));
		if (!$axis->$name) $axis->$name = $value;
	}
	protected function minMax($val, $field, $fn = 'min') {
		$k = $fn . '_' . $this->arrId[$this->nameId]['axis'] . '_' . $field;
		$vOld = $this->dataGraph->chart->$k;
		$this->dataGraph->chart->$k = is_null($vOld) ? $val : $fn($vOld, $val);
		return $this;
	}
	protected function parserX_dateTime($value) {
		return strtotime($value) * 1000;
	}
	protected function parserX_number($value) {
		return $value + 0;
	}
	protected function parserY_number($value) {
		$value += 0;
		$this->minMax($value, $name = 'y')->minMax($value, $name, 'max');
		$this->_set_value($name, $value);
		return $value;
	}
	protected function parserY_array($value) {
		$this->_set_value('y', "js:[$value]");
	}
	public function add() {
	}
}
/*Types:
	[x: num|date, ]y: value, label: "Venezuela"
		Column Chart
		Area Chart
		Bar Chart
	
		Line Chart
		Spline Chart
		Spline Area Chart
		Scatter (Point) Chart
		Stacked Area Chart
		Stacked Area 100% Chart
		Stacked Bar Chart
		Stacked Bar 100% Chart
		Stacked Column Chart
		Stacked Column 100% Chart
		Step Line Chart
		Step Area Chart
		Range Bar Chart
		Range Column Chart
		Range Area Chart
		Range Spline Area Chart
		Candlestick Chart
		OHLC (Stock) Chart
		Error Chart
		Box and Whisker Chart
		Waterfall chart
		
	y: value, label: "Venezuela"
		Pyramid Chart
		Funnel Chart
		Doughnut Chart
		Pie Chart
	x: num|date, y: value, z: value, label: "Venezuela"
		Bubble Chart

*/
