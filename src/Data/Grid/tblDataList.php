<?php
namespace EstaleiroWeb\ED\Data\Grid;

use EstaleiroWeb\ED\Data\Form\Form;
use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\DB\Tools\EasyView;
use EstaleiroWeb\ED\Ext\Bootstrap;
use EstaleiroWeb\ED\Ext\Ed;
use EstaleiroWeb\ED\Ext\JQuery_Cookie;
use EstaleiroWeb\ED\IO\MimeType;
use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\ED\Secure\Secure;

/**
* @author Helbert Fernandes <helbertfernandes@gmail.com>
* @description Conjunto de classes do tblData para manipulação de conjunto de dados
**/
//Retorna ou imprime uma View na tela
class tblDataList extends tblData {
	protected $baseId='DataList';
	protected $oConn, $sql, $dbCalc, $listFields, $result, $lstOrder, $actWidth,$done=false;
	protected $outTable, $outBody, $outFilter, $outHead, $outRecord, $outQuantLines, $outNavBar;
	protected $outHeadTable, $outBodyFilter, $outColumns, $outHidden, $outPagination;
	protected $outButtonFilter, $outButtonNew, $outButtonSort, $outButtonShowHide, $outButtonMenu, $outButtonMenuLi;
	
	public $row,$rownum,$cellClass; //atributos usados durante a geração
	
	public $elementFilter=array(); //Tipo diferente de elemento para o filtro
	public $getCells=''; //Depreciado - verificar no FORM
	public $attrLine=''; //Atributos de tr dentro de tbody
	
	public $tabs='';
	public $help='';
	protected $csv=['sep'=>"\t",'lf'=>"\n",'str'=>'"','null'=>''];
	
	private $trXML=array(
		'decimal'  =>array('type'=>'Number',  'style'=>'Decimal'),
		'tinyint'  =>array('type'=>'Number',  'style'=>'Number'),
		'smallint' =>array('type'=>'Number',  'style'=>'Number'),
		'int'      =>array('type'=>'Number',  'style'=>'Number'),
		'float'    =>array('type'=>'Number',  'style'=>'Decimal'),
		'double'   =>array('type'=>'Number',  'style'=>'Decimal'),
		'bigint'   =>array('type'=>'Number',  'style'=>'Number'),
		'mediumint'=>array('type'=>'Number',  'style'=>'Number'),
		'year'     =>array('type'=>'Number',  'style'=>'Number'),
		'timestamp'=>array('type'=>'DateTime','style'=>'DateTime'),
		'date'     =>array('type'=>'DateTime','style'=>'Date'),
		'datetime' =>array('type'=>'DateTime','style'=>'DateTime'),
		'time'     =>array('type'=>'DateTime','style'=>'Time'),
		'text'     =>array('type'=>'String',  'style'=>'String'),
		'varchar'  =>array('type'=>'String',  'style'=>'String'),
		'char'     =>array('type'=>'String',  'style'=>'String'),
	);
	
	//Execução automática
	public function __construct($id=null){
		parent::__construct($id);
		$outHtml=OutHtml::singleton();
		$this->outFormat=strtolower(@$_REQUEST[$this->baseId][$this->id]['outFormat']);
		if ($this->outFormat) {
			set_time_limit(0);
			$this->saveSession=false;
			//$classes=array_flip(get_declared_classes());
			$outHtml->isPrintScript=false;
			$outHtml->contentType=$this->outFormat;
			//$outHtml->organize=false;
		}

		//Verifica se é para resetar 
		if (@$_REQUEST[$this->baseId][$this->id]['reset']) {
			$this->oSess->destroy_id();
			$_REQUEST=$_POST=$_GET=$this->varSess=array();
		}
	}
	public function __toString(){
		//if($GLOBALS['doido']) show([@$this->showLabel , @$this->forceShowLabel]);
		if(@$_REQUEST['__rebuildVars__']) return $this->rebuildVars();
		if(!self::$toDo || $this->done || !$this->check_startVars()) return ''; 
		$this->done=true;

		$o=OutHtml::singleton();
		$this->result=$this->buildResult();//Executa a view
		if(!$this->result) return '';

		$this->outHead=$this->outTable=$this->outQuantLines=$this->outRecord=$this->outNavBar='';
		$this->outHidden=$this->outButtonNew=$this->outButtonFilter=$this->outButtonSort=$this->outButtonShowHide='';
		$this->outButtonMenu=$this->outPagination=$this->outBody='';
		$this->outButtonMenuLi=$this->actWidth=array();
		$this->outHeadTable=$this->outBodyFilter=$this->outColumns='';

		$this->buildFields();
		$this->verifyBasicActive();

		$fn='buildShow_return_'.($this->outFormat?$this->outFormat:'web');
		return $this->$fn();
	}
	
	protected function rebuildVars(){
		$r=$_REQUEST[$this->baseId][$this->id];
		foreach($r as $k=>$v) $this->$k=$v;
		$this->saveSession();
		return '';
	}
	protected function buildShow_return_web(){
		OutHtml::singleton()->html5();
		$this->buildShow_body_web();                                  //Conteuto
		$this->buildShow_filter_web();                                //Caixa de Filtros
		$this->buildShow_header_web();                                //Cabeçalho
		$this->buildShow_table_web();                                 //Tabela
		$this->buildShow_end_web();                                   //Finalizando o objeto

		$oldTab=$this->tabs;
		$this->tabs.="\t\t";
		$class='class="dataList'.($this->container===false?'':' container').'"';
		$out="{$this->tabs}<form role='form' method='POST'><div {$class} id='{$this->idObj}_data' ed-class='DataList'>\n";
		$out.=$this->outHead.$this->outHidden;
		$out.=$this->trBoxText($this->language['topBar'],$this->label);
		$out.=$this->outTable;
		$out.=$this->trBoxText($this->language['bottomBar'],$this->label);
		$out.="{$this->tabs}</div></form>\n";
		if($this->help) $out.="<div {$class} id='{$this->idObj}_help'>$this->help</div>";
		$this->tabs=$oldTab;
		return $out;
	}
	protected function buildShow_return_xls(){
		self::$toDo=false;
		OutHtml::singleton()->clearAll()->organize=false;

		$this->buildShow_body_xls();                              //Conteuto
		$this->buildShow_filter_xls();                            //Caixa de Filtros
		$this->buildShow_header_xls();                            //Cabeçalho
		$this->buildShow_table_xls();                             //Tabela

		print "{$this->outHead}<style>.x_title{background:#969696;}.x_par { background:silver; }.x_impar { background:silver; mso-pattern:white gray-50; }</style>{$this->outTable}";
		exit;
	}
	protected function buildShow_return_xml(){
		self::$toDo=false;
		OutHtml::singleton()->clearAll()->organize=false;

		$out=$this->buildShow_header_xml();                            //Cabeçalho
		$out.=$this->buildShow_table_xml();                            //Tabela
		$out.="</Workbook>\n";
		print $out;
		exit;
	}
	protected function buildShow_return_csv(){
		$this->outFormat='csv';
		self::$toDo=false;
		OutHtml::singleton()->clearAll()->organize=false;

		$this->buildShow_header_csv();                      //Cabeçalho
		$this->buildShow_table_csv();                       //Tabela
		exit;
	}
	protected function buildShow_return_csv_comma(){
		$this->csv['sep']=',';
		return $this->buildShow_return_csv();
	}
	protected function buildShow_return_csv_semicomma(){
		$this->csv['sep']=';';
		return $this->buildShow_return_csv();
	}

	protected function check_startVars(){
		$frm=$this->findForm();
		if($frm && is_object($frm)) {
			if(!$this->conn && $frm->conn) $this->conn="$frm->conn";
			if(!$this->view && $frm->tbl) $this->view=$frm->tbl;
			//if(Secure::$idUser==2) show($frm->key);
			if(!$this->key && $frm->key) $this->key=array_keys($frm->key);
		}
		if($this->key && !$this->url && !$this->urlEdit) $this->url='?';
		$tmp=$this->values;
		if (!$this->view) return false;
		return true;
	}
	protected function buildFields(){
		if(!$this->result) return array();
		$fields=$this->fields;
		$flds=@$this->result->fetch_fields();
		{$mysql_type=array(
			0=>'decimal',
			1=>'tinyint',
			2=>'smallint',
			3=>'int',
			4=>'float',
			5=>'double',
			7=>'timestamp',
			8=>'bigint',
			9=>'mediumint',
			10=>'date',
			11=>'time',
			12=>'datetime',
			13=>'year',
			246=>'decimal',
			252=>'text',
			253=>'varchar',
			254=>'char'
		);}
		//show($this->typeFields);
		
		$width=$this->width;
		if(!$width) $width=[];
		$typeFields=$this->typeFields;
		$typeNumFields=$this->typeNumFields;
		$style=$this->style;
		if(!$style || !is_array($style)) $style=array();
		foreach($flds as $oField){
			$key=$oField->name;
			//if(!array_key_exists($key,$this->style)) $this->style[$key]=array();
			//elseif(!is_array($this->style[$key])) 
			$style[$key]=$this->trString2ArrayStyle(@$style[$key]);
			if(!$this->isShowField($key)) $style[$key]['display']='none';
			if(!@$width[$key]) $width[$key]=($v=$this->unit2em(@$style[$key]['width']))?$v:'auto';
			//if(!@$width[$key] && ($v=$this->unit2em(@$style[$key]['width']))) $width[$key]=$v;
			if(!array_key_exists($key,$fields)) $fields[$key]=$this->buildNameById($key);
			$values=$this->values;
			if(!array_key_exists($key,$values)) $this->active['values'][$key]='*';
			$typeFields=$this->typeFields;
			if(!array_key_exists($key,$typeFields)) {
				$this->active['typeFields'][$key]=array_key_exists($oField->type,$mysql_type)?$mysql_type[$oField->type]:'';
				$this->active['typeNumFields'][$key]=$oField->type;
			}
			if(!array_key_exists($key,$this->format)) {
				//setlocale(LC_ALL, 'pt_BR.utf-8');
				$dataFormat='%x';
				if ($this->active['typeFields'][$key]=='date') $this->active['format'][$key]=$dataFormat;
				elseif ($this->active['typeFields'][$key]=='datetime' || $this->active['typeFields'][$key]=='timestamp') $this->active['format'][$key]=$dataFormat.' %T';
				else $this->active['format'][$key]='<xmp>%s</xmp>';
			}
		}
		$this->style=$style;
		$this->width=$width;
		//show($this->style);
		$this->active['fields']=$fields;
		if(!$this->lstFields) $this->active['lstFields']=array_keys($fields);
		//show($this->lstFields);
	}
	protected function addStyle($field,$style,$value){
		$styles=$this->style;
		$styles[$field][$style]=$value;
		$this->style=$styles;
	}
	protected function buildShow_body_web(){
		if (!$this->showTable || !$this->dbCalc['reccount']) return '';
		$oldTab=$this->tabs;
		$this->tabs.="\t\t";
		$body='';
		$events=array();
		foreach ($this->events as $e=>$cmds) $events[]=$e.'="'.implode(';',$cmds).'"';
		$events=implode(' ',$events);
		$noBrIni=$noBrFim='';
		if($this->noBrBody) {
			$noBrIni='<nobr>';
			$noBrFim='</nobr>';
		}
		
		if ($this->dbCalc['reccount']) {
			$body.="{$this->tabs}<tbody>\n";
			for ($this->rownum=0;$this->rownum<$this->dbCalc['lines'];$this->rownum++) {
				if(!($this->row=$this->result->fetch_assoc())) break;
				$hash=md5(implode('',$this->row));
				$attrLine=$this->handleLine?call_user_func($this->handleLine,$this):'';

				$body.="{$this->tabs}\t<tr hash='$hash'$events $attrLine>\n"; //.$getCells
				$fields=$this->active['fields'];
				foreach ($fields as $field=>$fieldName) {
					$attrLine=@$this->handleCell[$field]?call_user_func($this->handleCell[$field],$this):'';
					
					$this->cellClass=$this->buildClassByType($field);
					$printing=$this->fieldValue($field);
					$len=$this->autowidth($field,$printing);
					if($len>$this->actWidth[$field]) $this->cellClass.=' dataList-field-cuted';
					//if(!$this->isShowField($field)) $this->addStyle($field,'display','none');
					$style=$this->build_styleCell($field);
					$style=$style?' style="'.$style.'"':'';
					//if($field=='OC') show($style);

					//show($styles);
					//$printing=preg_replace('/(\r\n|\n\r|\n|\r)/','<br>',$printing==''?'&nbsp;':$printing);
					$body.="{$this->tabs}\t\t<td class='dataList-field {$this->cellClass}' ed-column='{$field}'$style $attrLine>";
					$body.='<div data-type="DataList-cell">'.$noBrIni.$printing.$noBrFim.'</div>';
					$body.='<input type="hidden" field="'.htmlspecialchars($field).'" value="'.htmlspecialchars(@$this->row[$field],ENT_QUOTES).'" >';
					$body.="</td>\n";
				}
				$body.="{$this->tabs}\t</tr>\n";
			}
			$body.="{$this->tabs}</tbody>\n";
		}
		$this->tabs=$oldTab;
		$this->outBody=$body;
	}
	protected function buildShow_body_xls(){
		if (!$this->showTable || !$this->dbCalc['reccount']) return '';
		$oldTab=$this->tabs;
		$this->tabs.="\t\t";
		$body='';
		
		if ($this->dbCalc['reccount']) {
			$body.="{$this->tabs}<tbody>\n";
			for ($this->rownum=0;$this->rownum<$this->dbCalc['lines'];$this->rownum++) {
				if(!($this->row=$this->result->fetch_assoc())) break;
				$attrLine=$this->handleLine?call_user_func($this->handleLine,$this):'';

				$colorLine=($this->rownum & 1)?'x_par':'x_impar';
				$body.="{$this->tabs}\t<tr class=$colorLine {$attrLine}>\n";
				$fields=$this->fields;
				foreach ($fields as $field=>$fieldName) {
					if(!$this->isShowFieldFilter($field)) continue;
					$printing=$this->fieldValue($field);
					$body.="{$this->tabs}\t\t<td>$printing</td>\n";
					//$this->autowidth($field,$printing);
				}
				$body.="{$this->tabs}\t</tr>\n";
				
			}
			$body.="{$this->tabs}</tbody>\n";
		}
		$this->tabs=$oldTab;
		$this->outBody=$body;
	}
	protected function buildShow_filter_web(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t\t\t\t";
		
		$noBrIni=$noBrFim='';
		if($this->noBrHead) {
			$noBrIni='<nobr>';
			$noBrFim='</nobr>';
		}
		//Caixa de Filtros
		$values=$this->values;
		$aFormat=$this->format;
		//show($this->lstFields);
		$fields=$this->fields;
		foreach ($fields as $field=>$fieldName) {
			if(!$this->isShowFieldFilter($field)) continue;
			$this->autowidth($field,$fieldName);
			$style=$this->build_styleCell($field,false);
			if(!($show=(int)$this->isShowField($field))) $style.='display:none;';
			if($style) $style=" style='{$style}'";
			//show(array($field=>$show));
			$name=htmlentities($fieldName);
			$format=htmlentities(@$aFormat[$field]);
			//$fieldName=$this->noBrHead?str_replace(' ','&nbsp;',$name):$name;
			//$fieldName=$name;
			//Cabecalho
			$classOrder=($ord=@$this->lstOrder[$field])?$this->buildIcon($ord):'';
			$disabled=in_array($field,$this->lock)?' DISABLED':'';
			$v=(isset($values[$field]))?htmlspecialchars($values[$field],ENT_QUOTES):'*';
			$this->outHeadTable.="{$this->tabs}\t<th$style>{$noBrIni}{$classOrder}{$fieldName}{$noBrFim}</th>\n";
			$nameFilter=$this->baseId."[{$this->idHtml}][values][".htmlspecialchars($field,ENT_QUOTES)."]";
			$this->outBodyFilter.="{$this->tabs}\t<td$style>";
			if (isset($this->elementFilter[$field]) && is_object($this->elementFilter[$field])) {
				$this->elementFilter[$field]->edit=true;
				$this->elementFilter[$field]->name=$nameFilter;
				$this->elementFilter[$field]->disable=$disabled;
				$this->elementFilter[$field]->value=$v;
				$this->outBodyFilter.=$this->elementFilter[$field]->__tostring();
			} else {
				//$this->outBodyFilter.="<input name=\"$nameFilter\" value='$v' type='text' onfocus='{$this->idObj}.activeElement(this,true)' onblur='{$this->idObj}.activeElement(this,false)' onkeypress='{$this->idObj}.filterKeyPress(this,event);'$disabled >";
				$this->outBodyFilter.='<input name="'.$nameFilter.'" value="'.$v.'" type="text"'.$disabled.' />';
			}
			$this->outBodyFilter.="</td>\n";
			$typeFields=$this->typeFields;
			$typeNumFields=$this->typeNumFields;
			$type=@$typeFields[$field];
			$typeNum=@$typeNumFields[$field];
			$this->outColumns.="{$this->tabs}<col idName='$nameFilter' field='$field' fieldName='$name' type='{$type}' typeNum='{$typeNum}' format='$format' show='$show' />\n";
		}
		$this->tabs=$oldTab;
	}
	protected function buildShow_filter_xls(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t\t\t\t";
		
		//Caixa de Filtros
		$fields=$this->fields;
		foreach ($fields as $field=>$fieldName) {
			if(!$this->isShowFieldFilter($field)) continue;
			$name=htmlentities($fieldName);
			$fieldName=str_replace(' ','&nbsp;',$name);
			$this->outHeadTable.="{$this->tabs}\t<th>{$fieldName}</th>\n";
		}
		$this->tabs=$oldTab;
	}
	protected function buildShow_table_web(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t";
		//$this->outTable="<table tblId='{$this->idHtml}' id='{$this->idObj}_table' class='tblData_table' border='0' cellspacing='0' x:str><thead><tr>{$this->outHeadTable}</tr></thead>";
		$this->outTable="{$this->tabs}<div style='overflow-x: auto;'>\n"; // class='table-responsive'

		$attrs= ' id="'.$this->idObj.'_table"';
		//$attrs.=' tblId="'.$this->idHtml.'"';
		$attrs.=' label="'.htmlentities($this->label).'"';
		$attrs.=' key="'.$this->key.'"';
		$urls=array('url'=>'R','urlNew'=>'C','urlEdit'=>'U','urlNav'=>'R','urlDel'=>'D','urlClone'=>'C',);
		//show(array('url'=>$this->url,'urlNew'=>$this->urlNew));
		foreach($urls as $k=>$p) if($this->checkUrl($url=$this->$k,$p)) $attrs.=' '.$k.'="'.htmlentities($url).'"';
		$this->outTable.="{$this->tabs}\t<table$attrs class='table table-striped table-hover table-condensed table-bordered' border='0' cellspacing='0'>\n";
		// style='width:50px' 
		
		$this->outTable.="{$this->tabs}\t\t<colgroup>\n";
		$this->outTable.=$this->outColumns;
		$this->outTable.="{$this->tabs}\t\t</colgroup>\n";
		
		$this->outTable.="{$this->tabs}\t\t<thead>\n";
		if($this->showHead) $this->outTable.="{$this->tabs}\t\t\t<tr class='dataList-fieldsNorder'>\n{$this->outHeadTable}{$this->tabs}\t\t\t</tr>\n";
		$filterStyle=$this->showFilter?'':' style="display:none;"';
		$this->outTable.="{$this->tabs}\t\t\t<tr class='dataList-filter' $filterStyle>\n{$this->outBodyFilter}{$this->tabs}\t\t\t</tr>\n";
		$this->outTable.="{$this->tabs}\t\t</thead>\n";
		$this->outTable.=$this->outBody;
		
		//Foot
		
		$this->outTable.="{$this->tabs}\t</table>\n";
		$this->outTable.="{$this->tabs}</div>\n";
		//$this->outTable="{$this->outTable}";
		$this->tabs=$oldTab;
	}
	protected function buildShow_table_xls(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t";

		$this->outTable="{$this->tabs}\t<table class='table table-striped table-hover table-condensed table-bordered' border='0' cellspacing='0'>\n";
		
		//$this->outTable.="{$this->tabs}\t\t<colgroup>\n";
		//$this->outTable.=$this->outColumns;
		//$this->outTable.="{$this->tabs}\t\t</colgroup>\n";
		
		$this->outTable.="{$this->tabs}\t\t<thead>\n";
		$this->outTable.="{$this->tabs}\t\t\t<tr class=x_title>\n{$this->outHeadTable}{$this->tabs}\t\t\t</tr>\n";
		$this->outTable.="{$this->tabs}\t\t</thead>\n";
		$this->outTable.=$this->outBody;
		
		//Foot
		
		$this->outTable.="{$this->tabs}\t</table>\n";
		//$this->outTable="{$this->outTable}";
		$this->tabs=$oldTab;
	}
	protected function buildShow_table_xml(){
		{//begin
		$oldTab=$this->tabs;
		$tab="\t";
		$lf="\n";
		$this->tabs.=$tab;
		$body='';
		$tabTable=$this->tabs.$tab;
		$tabRow=$tabTable.$tab;
		$tabCell=$tabRow.$tab;
		$heightLine=15;
		}
		{//body
		if ($this->showTable && $this->dbCalc['reccount']) {
			for ($this->rownum=0;$this->rownum<$this->dbCalc['lines'];$this->rownum++) {
				if(!($this->row=$this->result->fetch_assoc())) break;

				$row='';
				$len=0;
				$fields=$this->fields;
				$typeFields=$this->typeFields;
				foreach ($fields as $field=>$fieldName) {
					if(!$this->isShowFieldFilter($field)) continue;
					$printing=$this->fieldValue($field);
					if($printing==='') $row.=$tabCell.'<Cell/>'.$lf;
					else {
						$len=max($len,$this->autowidth($field,$printing));
						$tp=$this->trXML[$typeFields[$field]]['type'];
						if($tp=='Number' and preg_match('/\D/',$printing)) $tp='String';
						$printing=htmlentities(strip_tags($printing), ENT_QUOTES);
						$row.=$tabCell.'<Cell><Data ss:Type="'.$tp.'">'.$printing.'</Data></Cell>'.$lf;
					}
				}
				
				$attrLine=$this->handleLine?call_user_func($this->handleLine,$this):'';
				$styleLine=($this->rownum & 1)?'Par':'Impar';
				$height=min($this->maxHeightLine,max($heightLine,round($len/$this->widthField)*$heightLine));
				$body.=$tabRow.'<Row ss:Height="'.$height.'" ss:StyleID="'.$styleLine.'" '.$attrLine.'>'.$lf.$row.$tabRow.'</Row>'.$lf;
			}
		} else $this->rownum=0;
		}
		{//header + column
		$column='';
		$row=$tabRow.'<Row ss:Height="15" ss:StyleID="Header">'.$lf;
		$addSplit=true;
		$splitVertical=1;
		$fields=$this->fields;
		$typeFields=$this->typeFields;
		foreach ($fields as $field=>$fieldName) {
			if(!$this->isShowFieldFilter($field)) continue;
			$this->autowidth($field,$fieldName);
			$row.=$tabCell.'<Cell><Data ss:Type="String">'.htmlentities($fieldName, ENT_QUOTES).'</Data></Cell>'.$lf;
			$width=min($this->widthField,$this->actWidth[$field])+0;
			$width=$width?' ss:Width="'.($width*6.5).'"':'';
			if(!$this->isShowField($field)) $width.=' ss:Hidden="1"';
			else $addSplit=false;
			$column.=$tabRow.'<Column'.$width.' ss:StyleID="'.$this->trXML[$typeFields[$field]]['style'].'" />'.$lf;
			if($addSplit) $splitVertical++;
		}
		$row.=$tabRow.'</Row>'.$lf;
		}
		{//Worksheet
		$this->rownum++;
		$nCol=count($fields);
		
		$out =$this->tabs.'<Worksheet ss:Name="'.htmlentities($this->label, ENT_QUOTES).'">'.$lf;
		$out.=$tabTable.'<Table ss:ExpandedColumnCount="'.$nCol.'" ss:ExpandedRowCount="'.$this->rownum.'" x:FullColumns="1" x:FullRows="1" ss:DefaultColumnWidth="0" ss:DefaultRowHeight="0">'.$lf;
			$out.=$column;
			$out.=$row;
			$out.=$body;
		$out.=$tabTable.'</Table>'.$lf;
		$out.=$tabTable.'<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">'.$lf;
			$out.=$tabRow.'<ZeroHeight/>'.$lf;
			$out.=$tabRow.'<Selected/>'.$lf;
			$out.=$tabRow.'<DoNotDisplayGridlines/>'.$lf;
			$out.=$tabRow.'<FreezePanes/>'.$lf;
			$out.=$tabRow.'<FrozenNoSplit/>'.$lf;
			$out.=$tabRow.'<SplitHorizontal>1</SplitHorizontal>'.$lf;
			$out.=$tabRow.'<TopRowBottomPane>1</TopRowBottomPane>'.$lf;
			$out.=$tabRow.'<SplitVertical>'.$splitVertical.'</SplitVertical>'.$lf;
			$out.=$tabRow.'<LeftColumnRightPane>'.$splitVertical.'</LeftColumnRightPane>'.$lf;
			$out.=$tabRow.'<ActivePane>0</ActivePane>'.$lf;
			$out.=$tabRow.'<ProtectObjects>False</ProtectObjects>'.$lf;
			$out.=$tabRow.'<ProtectScenarios>False</ProtectScenarios>'.$lf;
		$out.=$tabTable.'</WorksheetOptions>'.$lf;
		$out.=$tabTable.'<AutoFilter x:Range="R1C1:R'.$this->rownum.'C'.$nCol.'" xmlns="urn:schemas-microsoft-com:office:excel"></AutoFilter>'.$lf;
		$out.=$this->tabs.'</Worksheet>'.$lf;
		}
		$this->tabs=$oldTab;
		return $out;
	}
	protected function buildShow_table_csv_line($line){
		foreach($line as $k=>$v) {
			if(is_null($v)) $v=$this->csv['null'];
			elseif(!is_numeric($v)) $v=$this->csv['str'].preg_replace('/\s/',' ',str_replace(array('\\',$this->csv['str'],"\n","\r","\t"),array('\\\\','\\'.$this->csv['str'],'\n','\r','\t'),$v)).$this->csv['str'];
			$line[$k]=$v;
		}
		return implode($this->csv['sep'],$line).$this->csv['lf'];
	}
	protected function buildShow_table_csv(){
		$row=[];
		$fields=$this->fields;
		foreach ($fields as $field=>$fieldName) if($this->isShowFieldFilter($field)) $row[]=$fieldName;
		print $this->buildShow_table_csv_line($row);

		if ($this->dbCalc['reccount']) {
			for ($this->rownum=0;$this->rownum<$this->dbCalc['lines'];$this->rownum++) {
				if(!($this->row=$this->result->fetch_assoc())) break;
				$row=[];
				foreach ($fields as $field=>$fieldName) if($this->isShowFieldFilter($field)) $row[]=@$this->row[$field];
				print $this->buildShow_table_csv_line($row);
			}
		}
	}
	protected function buildShow_header_web(){
		$this->outHead.=$this->htmlHead();
		$outHtml=OutHtml::singleton();
		//$host=$outHtml->config['__autoload']['host'];
		/*$edFN=$outHtml->config['ed']['fn'];
		$outHtml->headScript['copyUrl']="window.copyUrlPath='{$edFN}/urlRedir.php'";*/
		new JQuery_Cookie();
		new Bootstrap();
		new Ed();
		$outHtml->style('tblDataList','ed');
		$outHtml->script('tblDataList','ed');
		
		$this->buildShow_records();            //Numero de Registros
		$this->buildShow_quantLines();         //Quantidade de Registros a mostrar
		$this->buildShow_buttonNew();          //Botão Novo Registro
		$this->buildShow_buttonFilter();       //Botão Filtro
		$this->buildShow_buttonSort();
		$this->buildShow_buttonShowHide();     //Botão ShowHide
		$this->buildShow_buttonMenu();         //Botão Menu
		$this->buildShow_navBar();             //Barra de Navegação e linhas
		$this->buildShow_pagination();         //Barra de Navegação Númerica e linhas
		$this->buildShow_hiddenFileds();       //Campos Hidden
		if($this->getCells) OutHtml::singleton()->headScript['onkeypress']="document.onkeypress=function(){ if(event.keyCode==27) window.close() }";
	}
	protected function buildShow_header_xls(){
		$this->outHead.="<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">";
		//$this->outHead.="<html xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns='http://www.w3.org/TR/REC-html40'>";
		if(!$this->outObj('header')) return false;
		//$this->header();
		$o=OutHtml::singleton();
		$o->attachment($this->buildFileName($this->label).'.'.$this->outFormat);
		$o->contentType();
	}
	protected function header(){
		$o=OutHtml::singleton();
		
		$filename=$this->buildFileName($this->label).'.'.$this->outFormat;
		$type=strtolower(trim($this->outFormat));
		if(isset(MimeType::$content_types[$this->outFormat])) $type=MimeType::$content_types[$type];
		elseif(strpos($type,'/')===false) $type="application/{$this->outFormat}";
		
		@header('Cache-Control: must-revalidate');
		@header('Pragma: must-revalidate');
		@header("content-type: $type; charset={$o->charset}");
		@header("Content-Disposition: attachment; filename={$filename}");
	}
	protected function buildShow_header_xml(){
		$s=Secure::$obj;
		$user=$s && $s::$idUser?$s->user->User:'UNKNOWN';
		$out="<?xml version='1.0'?>\n";
		$out.="<?mso-application progid='Excel.Sheet'?>\n";
		$out.="<Workbook xmlns='urn:schemas-microsoft-com:office:spreadsheet' xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:x='urn:schemas-microsoft-com:office:excel' xmlns:ss='urn:schemas-microsoft-com:office:spreadsheet' xmlns:html='http://www.w3.org/TR/REC-html40'>\n";
		$out.="	<DocumentProperties xmlns='urn:schemas-microsoft-com:office:office'>\n";
		$out.="		<Author>$user</Author>\n";
		$out.='		<Created>'.strftime('%FT%TZ')."</Created>\n";
		$out.="		<Version>14.00</Version>\n";
		$out.="	</DocumentProperties>\n";
		$out.="	<Styles>\n";
		$out.="		<Style ss:ID='Default' ss:Name='Normal'>\n";
		$out.="			<Alignment ss:Vertical='Center' ss:WrapText='1'/>\n";
		$out.="			<Borders/>\n";
		$out.="			<Font ss:FontName='Calibri' x:Family='Swiss' ss:Size='11' ss:Color='#000000'/>\n";
		$out.="			<Interior/>\n";
		$out.="			<NumberFormat/>\n";
		$out.="			<Protection/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Header'>\n";
		$out.="			<Alignment ss:Horizontal='Center' ss:Vertical='Center' ss:WrapText='1'/>\n";
		$out.="			<Font ss:FontName='Calibri' x:Family='Swiss' ss:Color='#000000' ss:Bold='1'/>\n";
		$out.="			<Interior ss:Color='#969696' ss:Pattern='Solid'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Impar'>\n";
		$out.="			<Interior ss:Color='#C0C0C0' ss:Pattern='Gray50' ss:PatternColor='#FFFFFF'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Par'>\n";
		$out.="			<Interior ss:Color='#C0C0C0' ss:Pattern='Solid'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='String'>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Number'>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Decimal'>\n";
		$out.="			<NumberFormat ss:Format='Fixed'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Precision'>\n";
		$out.="			<NumberFormat ss:Format='0.000'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Currency'>\n";
		$out.="			<NumberFormat ss:Format='&quot;R$&quot;\ #,##0.00'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Date'>\n";
		$out.="			<Alignment ss:Vertical='Center' ss:WrapText='0'/>\n";
		$out.="			<NumberFormat ss:Format='Short Date'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='DateTime'>\n";
		$out.="			<Alignment ss:Vertical='Center' ss:WrapText='0'/>\n";
		$out.="			<NumberFormat ss:Format='General Date'/>\n";
		$out.="		</Style>\n";
		$out.="		<Style ss:ID='Time'>\n";
		$out.="			<Alignment ss:Vertical='Center' ss:WrapText='0'/>\n";
		$out.="			<NumberFormat ss:Format='[$-F400]h:mm:ss\ AM/PM'/>\n";
		$out.="		</Style>\n";
		$out.="	</Styles>\n";

		//<html xmlns:o=\"urn:schemas-microsoft-com:office:office\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns=\"http://www.w3.org/TR/REC-html40\">";
		if($this->outObj('header')) OutHtml::singleton()->attachment($this->buildFileName($this->label).'.'.$this->outFormat);
		return $out;
	}
	protected function buildShow_header_csv(){
		if($this->outObj('header')) OutHtml::singleton()->attachment($this->buildFileName($this->label).'.'.$this->outFormat);
	}
	protected function buildShow_end_web(){
		unset($this->elementFilter);
		$this->saveSession();
	}
	
	protected function buildShow_records(){
		$this->outRecord=$this->trBoxText($this->language['records'],$this->recordCount==1?'':'s');
	}
	protected function buildShow_quantLines(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t\t\t";
		$this->outQuantLines.="{$this->tabs}<div class='btn-group dataList-quantLines'>\n";
		$this->outQuantLines.="{$this->tabs}\t<input type='hidden' name='{$this->baseId}[{$this->idHtml}][lines]' id='{$this->idObj}_lines' value='{$this->lines}'>\n";
		if($this->showQuantLines && ($this->dbCalc['reccount']>1 || $this->lines<$this->dbCalc['reccount'])) {
			$this->outQuantLines.="{$this->tabs}\t<button type='button' class='{$this->classIcons['btn']} btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' title='Mostrar linhas' >{$this->trBoxText($this->language['quantLinesButton'],$this->lines==0?$this->language['quantLinesAll']:$this->lines)}</button>\n";
			$this->outQuantLines.="{$this->tabs}\t<ul class='dropdown-menu'>\n";
			foreach($this->quantLinesOptions as $v) {
				if(!is_numeric($v)) $this->outQuantLines.="{$this->tabs}\t\t<li role='separator' class='divider'></li>\n{$this->tabs}\t\t\n<li><a href='#' id='_'>{$this->trBoxText($this->language['quantLinesConf'],$v)}</a></li>\n";
				elseif($v==0) $this->outQuantLines.="{$this->tabs}\t\t<li><a href='#' id='$v'>{$this->trBoxText($this->language['quantLinesAll'],$v)}</a></li>\n";
				else $this->outQuantLines.="{$this->tabs}\t\t<li><a href='#' id='$v'>{$this->trBoxText($this->language['quantLinesDef'],$v)}</a></li>\n";
			}
			$this->outQuantLines.="{$this->tabs}\t</ul>\n";
		}
		$this->outQuantLines.="{$this->tabs}</div>\n";
		$this->tabs=$oldTab;
	}
	protected function buildShow_buttonNew(){
		($url=$this->urlNew) || ($url=$this->url);
		if ($this->checkUrl($url,'C')) {
			$i=$this->buildIcon('plus');
			$this->outButtonNew="{$this->tabs}\t\t\t\t\t<button class='{$this->classIcons['btn']} btn-default dataList-btn-new' type='button' title='Novo Registro em {$this->label}'>{$i}</button>";
			$this->outButtonMenuLi[]="<li><a href='#' action='plus'       >{$i}New...</a></li>";
		}
	}
	protected function buildShow_buttonFilter(){
		$this->outButtonFilter="{$this->tabs}\t\t\t\t\t<button class='{$this->classIcons['btn']} btn-default dataList-btn-filter' type='button' title='Filtro' style='display:none;'>{$this->buildIcon('filter')}</button>";
	}
	protected function buildShow_buttonSort(){
		$this->outButtonSort="{$this->tabs}\t\t\t\t\t<button class='{$this->classIcons['btn']} btn-default dataList-btn-sort' type='button' title='Sort'>{$this->buildIcon('sort')}</button>";
	}
	protected function buildShow_buttonShowHide(){
		$this->outButtonShowHide="{$this->tabs}\t\t\t\t\t<button class='{$this->classIcons['btn']} btn-default dataList-btn-showHide' type='button' title='ShowHide'>{$this->buildIcon('showHide')}</button>";
	}
	protected function buildShow_buttonMenu(){
		$copyTitle="Click: CSV (TAB separator)\n";
		$copyTitle.="Ctrl+Click:CSV (; separator)\n";
		$copyTitle.="Shift+Click:JSON\n";
		$copyTitle.="Ctrl+Shift+Click:Planilha XML 2003";

		$exportCsvTitle="Click: CSV (; separator)\n";
		$exportCsvTitle.="Ctrl+Click:CSV (TAB separator)\n";
		$exportCsvTitle.="Shift+Click:CSV (, separator)";
		$addLI=implode("\n",$this->outButtonMenuLi);
		$this->outButtonMenu="
			<div class='btn-group dataList-btn-menu'>
				<button type='button' class='{$this->classIcons['btn']} btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' title='Menu de Opções'>
					{$this->buildIcon('menu')}
				</button>
				<ul class='dropdown-menu' role='menu'>
					$addLI
					<li><a href='#' action='filter'        >{$this->buildIcon('filter')}Filter</a></li>
					<li><a href='#' action='sort'          >{$this->buildIcon('sort')}Sort</a></li>
					<li><a href='#' action='showHide'      >{$this->buildIcon('showHide')}Show/Hide Columns</a></li>
					<li><a href='#' action='showHideFilter'>{$this->buildIcon('showHideFilter')}Show/Hide Filter Line</a></li>
					<li role='separator' class='divider'></li>
					<li><a href='#' action='copyTable' title='{$copyTitle}'>{$this->buildIcon('copyTable')}Copy table</a></li>
					<li><a href='#' action='copyFieldsTable' title='{$copyTitle}'>{$this->buildIcon('copyFieldsTable')}Copy field names table</a></li>
					<li><a href='#' action='copyFields'    >{$this->buildIcon('copyFields')}Copy field names</a></li>
					<li><a href='#' action='copyURL' title='Com Ctrl não codifica URL'>{$this->buildIcon('copyURL')}Copy URL <kbd title='Shift'>Shift</kbd>+<kbd title='Alt'>Alt</kbd>+<kbd title='L'>L</kbd></a></li>
					<li role='separator' class='divider'></li>
					<li><a href='#' action='exportExcelAll'>{$this->buildIcon('exportExcelAll')}Export to Excel</a></li>
					<li><a href='#' action='exportCSV' title='{$exportCsvTitle}'>{$this->buildIcon('exportCSV')}Export to CSV</a></li>
					<li role='separator' class='divider'></li>
					<li><a href='#' action='reset'         >{$this->buildIcon('reset')}Reset</a></li>
					<li><a href='#' action='help'         >{$this->buildIcon('help')}Help</a></li>
				</ul>
			</div>\n";
					//<li><a href='#' action='exportExcel'   >{$this->buildIcon('exportExcel')}Export to Excel</a></li>
	}
	protected function buildShow_navBar(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t\t\t";
		if ($this->showNavBars && $this->dbCalc['pagecount']>1) {//Navegação
			$this->outNavBar= "{$this->tabs}<div class='{$this->classIcons['input-group']} dataList-navBar'>\n";
			$this->outNavBar.="{$this->tabs}\t<span class='{$this->classIcons['input-group-btn']}'>\n";
			$this->outNavBar.="{$this->tabs}\t\t<button class='{$this->classIcons['btn']} btn-default' type='button' title='Go to the first record'   >{$this->buildIcon('first')}</button>\n";
			$this->outNavBar.="{$this->tabs}\t\t<button class='{$this->classIcons['btn']} btn-default' type='button' title='Go to the previous record'>{$this->buildIcon('previous')}</button>\n";
			$this->outNavBar.="{$this->tabs}\t</span>\n";
			$this->outNavBar.="{$this->tabs}\t<input type='text' class='form-control' id='{$this->idObj}_txtPg' title='Pg/Total' value='{$this->trBoxText($this->language['pageTextBox'])}'>\n";
			$this->outNavBar.="{$this->tabs}\t<span class='{$this->classIcons['input-group-btn']}'>\n";
			$this->outNavBar.="{$this->tabs}\t\t<button class='{$this->classIcons['btn']} btn-default' type='button' title='Go to the next record'    >{$this->buildIcon('next')}</button>\n";
			$this->outNavBar.="{$this->tabs}\t\t<button class='{$this->classIcons['btn']} btn-default' type='button' title='Go to the last record'    >{$this->buildIcon('last')}</button>\n";
			$this->outNavBar.="{$this->tabs}\t</span>\n";
			$this->outNavBar.="{$this->tabs}</div>\n";
		} else $this->outNavBar='';
		$this->tabs=$oldTab;
	}
	protected function buildShow_pagination(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t\t\t";
		if ($this->showNavBarsNum && $this->dbCalc['pagecount']>1) {//Navegação
			$ini=max(1,$this->dbCalc['page']-ceil($this->quantNavBarsNum/2));
			$fim=min($this->dbCalc['pagecount'],$ini+$this->quantNavBarsNum-1);
			$this->outPagination= "{$this->tabs}<nav class=' dataList-pagination'>\n";
			$this->outPagination.="{$this->tabs}\t<ul class='pagination'>\n";
			$c=$ini==$this->dbCalc['page']?' class="disabled"':'';
			$this->outPagination.="{$this->tabs}\t\t<li$c><a href='#' aria-label='Previous'><span aria-hidden='true'>&laquo;</span></a></li>\n";
			for($i=$ini;$i<=$fim;$i++) {
				$c=$i==$this->dbCalc['page']?' active':'';
				$this->outPagination.="{$this->tabs}\t\t<li class='dataList-pagination-num $c' page='$i'><a href='#'>$i</a></li>\n";
			}
			$c=$fim==$this->dbCalc['page']?' class="disabled"':'';
			$this->outPagination.="{$this->tabs}\t\t<li$c><a href='#' aria-label='Next'    ><span aria-hidden='true'>&raquo;</span></a></li>\n";
			$this->outPagination.="{$this->tabs}\t</ul>\n";
			$this->outPagination.="{$this->tabs}</nav>\n";
		}
		$this->tabs=$oldTab;
	}
	protected function buildShow_hiddenFileds(){
		$oldTab=$this->tabs;
		$this->tabs.="\t\t\t";
		$this->outHidden="{$this->tabs}<input type='hidden' class='dataList-input-reset'        name='{$this->baseId}[{$this->idHtml}][reset]'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-outFormat'     name='{$this->baseId}[{$this->idHtml}][outFormat]'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-default'       name='{$this->baseId}[{$this->idHtml}][default]'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-showFilter'    name='{$this->baseId}[{$this->idHtml}][showFilter]'   value='{$this->showFilter}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-showRecCount'  name='{$this->baseId}[{$this->idHtml}][showRecCount]' value='{$this->showRecCount}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-showNavBars'   name='{$this->baseId}[{$this->idHtml}][showNavBars]'  value='{$this->showNavBars}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-showTable'     name='{$this->baseId}[{$this->idHtml}][showTable]'    value='{$this->showTable}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-page'          name='{$this->baseId}[{$this->idHtml}][page]'         value='{$this->dbCalc['page']}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-pages'         name='{$this->baseId}[{$this->idHtml}][pages]'        value='{$this->dbCalc['pagecount']}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-order'         name='{$this->baseId}[{$this->idHtml}][order]'        value='".htmlspecialchars($this->order,ENT_QUOTES)."'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-group'         name='{$this->baseId}[{$this->idHtml}][group]'        value='".htmlspecialchars($this->group,ENT_QUOTES)."'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-lstFields'     name='{$this->baseId}[{$this->idHtml}][lstFields]'    value='".htmlspecialchars(implode(',',$this->lstFields),ENT_QUOTES)."'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-widthField'    name='{$this->baseId}[{$this->idHtml}][widthField]'   value='{$this->widthField}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-width'         name='{$this->baseId}[{$this->idHtml}][width]'        value='".htmlspecialchars(json_encode($this->width),ENT_QUOTES)."'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-fields'        name='{$this->baseId}[{$this->idHtml}][fields]'       value='".htmlspecialchars(json_encode($this->fields),ENT_QUOTES)."'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-id'     value='{$this->id}'>\n";
		$this->outHidden.="{$this->tabs}<input type='hidden' class='dataList-input-idFile' value='{$this->oSess->idFile()}'>\n";
		$this->outHidden.="{$this->tabs}<input type='submit' class='dataList-input-submit_button' name='{$this->baseId}[{$this->idHtml}][submit]'       value='filtrar' style='display:none;width:1px;height:1px;'>\n";
		$this->tabs=$oldTab;
		//show($this->lstFields);
		//show($this->fields);
	}

	protected function connect(){
		if(!$this->oConn) {
			if(!$this->conn) $this->oConn=Conn::dsn();
			elseif(is_string($this->conn)) $this->oConn=Conn::dsn($this->conn);
			else $this->oConn=$this->conn;
		}
		if($this->db) $this->oConn->select_db($this->db);
	}
	protected function isShowField($field){
		$lst=$this->lstFields;
		//show($this->hiddenFields);
		if($this->isShowFieldFilter($field)) return !$lst || in_array($field,$lst);
		return false;
	}
	protected function isShowFieldFilter($field){
		$hid=$this->hiddenFields;
		if($hid && in_array($field,$hid)) return false;
		return true;
	}
	protected function trBoxText($txt,$num=0){
		if(!$txt) return '';
		return str_replace(
			array(
				'<num>',
				'<reccount>',
				'<startRecord>',
				'<endRecord>',
				'<lines>',
				'<page>',
				'<pagecount>',
				'<records>',
				'<quantLines>',
				'<navBar>',
				'<pagination>',
				'<buttonNew>',
				'<buttonFilter>',
				'<buttonSort>',
				'<buttonShowHide>',
				'<buttonMenu>',
				'<idHtml>',
				'<idObj>',
				'<status>',
			),
			array(
				$num,
				$this->dbCalc['reccount'],
				$this->dbCalc['begin'],
				$this->dbCalc['end'],
				$this->lines,
				$this->dbCalc['page'],
				$this->dbCalc['pagecount'],
				"\t\t\t\t\t<div class='dataList-records'>{$this->outRecord}</div>",
				$this->outQuantLines,
				$this->outNavBar,
				$this->outPagination,
				$this->outButtonNew,
				$this->outButtonFilter,
				$this->outButtonSort,
				$this->outButtonShowHide,
				$this->outButtonMenu,
				$this->idHtml,
				$this->idObj,
				"\t\t\t\t\t<div class='text-info dataList-status'></div>",
			),
			$txt
		);
	}
	protected function buildClassByType($field){
		static $classes=array();
		if(!@$classes[$field]) {
			$typeFields=$this->typeFields;
			$type=@$typeFields[$field];
			//$width=array_key_exists($field,$this->width)?$this->width[$field]:$this->widthField;
			//$noCut=array_key_exists($field,$this->noCut);
			
			if(preg_match('/^(decimal|float|double)$/',$type)) $classes[$field]='dataList-field-dec';
			elseif(preg_match('/^((big|medium|small|tiny)?int|integer|year)$/',$type)) $classes[$field]='dataList-field-int';
			elseif(preg_match('/^(date(time)?|time(stamp)?)$/',$type)) $classes[$field]='dataList-field-date';
			elseif($type=='text') $classes[$field]='dataList-field-text';
			else $classes[$field]='dataList-field-char';
		}
		return $classes[$field];
	}
	protected function buildResult(){
		$this->connect();
		//show($this->oConn);
		//Captura View
		//$sqlDet=$this->oConn->details($this->view);
		$ev=new EasyView($this->oConn,$this->view);
		//show($ev);
		$sql=$ev->sp;
		$sql2=$ev->spfull;
		$error=$ev->error;
		$values=$this->values;
		//Monta sub-query
		$this->dbCalc=['page'=>$this->dbCalc['page'],'begin'=>1,'end'=>0,'reccount'=>0,'lines'=>$this->lines,'pagecount'=>1];
		if ($this->showTable){
			//Monta o ORDER BY
			$order=$this->order;
			$this->lstOrder=$this->buildOrderField($order);
			//Monta o WHERE e adciona o order no fim
			$where=$this->mountWhereExpression($values);
			//showme($where);
			//Monta a Query
			if ($where) {
				$sql=preg_match("/^select/i",$sql)?"SELECT * FROM ($sql) t $where":"$sql2 $where";
			} else $sql=$ev->spfull;
			if ($this->group) {
				$sql.=" GROUP BY {$this->group}";
				if($this->groupFields) $sql=preg_replace('/^(select\s+)\*/i',"\\1{$this->groupFields}",$sql);
				$sqlCount="SELECT COUNT(1) FROM ($sql) t";
			}else {
				if(preg_match($er='/^(select\s+)\*(\s+from\s)/i',$sql)) $sqlCount=preg_replace($er,'\1COUNT(1)\2',$sql);
				else $sqlCount="SELECT COUNT(1) FROM ($sql) t";
			}
			//showme($sqlCount);
			$this->dbCalc['reccount']=$this->recordCount=$this->oConn->fastValue($sqlCount);
			$sql.=$order;

			$this->dbCalc['lines']=($this->lines && $this->showNavBars)?$this->lines:$this->dbCalc['reccount'];
			if($this->dbCalc['lines']){
				$this->dbCalc['pagecount']=max(1,ceil($this->recordCount/$this->dbCalc['lines']));
				$this->dbCalc['page']=min($this->page,$this->dbCalc['pagecount']);
				$offset=($this->dbCalc['page']-1)*$this->dbCalc['lines'];
				$sql.=" LIMIT $offset,{$this->dbCalc['lines']}";
				//showme([$this->dbCalc,$this->dbCalc['page'],$sql]);
				$this->dbCalc['begin']=$offset+1;
				//show($sql);
			} elseif($this->limit) $sql.=' LIMIT '.$this->limit;
		} else $sql="$sql2 LIMIT 0";
		$this->dbCalc['end']=$this->dbCalc['begin']+$this->dbCalc['lines']-1;

		//Executa a view
		$this->sql=$sql;
		$res=$this->oConn->query($sql);
		if (!$res->res) die("Erro ao fazer Fetch na view: <pre>$sql</pre>");

		return $res;
	}
	protected function buildOrderField(&$orderExpression){
		$order=array();
		if ($orderExpression) {
			$lst=explode(',',$orderExpression);
			$orderExpression=" ORDER BY $orderExpression";
			foreach ($lst as $value) {
				preg_match('/ *(`?)(.*?)\1(?: (desc|asc))? *$/i',$value,$ret);
				$order[$ret[2]]=strtolower(@$ret[3])=='desc'?'desc':'asc';
			}
		}
		return $order;
	}
	protected function findForm(){
		$bt=debug_backtrace();
		foreach($bt as $line) if(array_key_exists('object',$line) && $line['object'] instanceof Form) return $line;
		if(array_key_exists('frm',$GLOBALS) && is_object($GLOBALS['frm']) && $GLOBALS['frm'] instanceof Form) return $GLOBALS['frm'];
		foreach($GLOBALS as $v) if(is_object($v) && $line['object'] instanceof Form) return $v;
		return false;
	}
	protected function autowidth($field,&$value){
		$style=$this->style;
		$width=$this->width;
		$widthField=$this->widthField;
		$vStriped=strip_tags($value);
		$typeFields=$this->typeFields;
		if($vStriped==$value) $value=htmlspecialchars($value,ENT_QUOTES);
		if(@$typeFields[$field]=='date') $len=10;
		elseif(@$typeFields[$field]=='time') $len=8;
		else $len=strlen($vStriped);
		
		if(array_key_exists($field,$width)) $w=$width[$field];
		elseif($len>=$widthField)           $w=$widthField;
		else                                $w=max(@$this->actWidth[$field],$len);
		$this->actWidth[$field]=$w;
		$this->addStyle($field,'width',$w.($w==($w+0)?'em':''));
		return $len;
	}
	protected function build_styleCell($field,$removeWidth=true){
		$style=$this->style;
		$style=$style[$field];
		if($removeWidth && @$style['width']) unset($style['width']);
		/*
		if(!@$style['width'] && @$this->actWidth[$field]) {
			$style['width']=$this->actWidth[$field];
			if($style['width']>$this->widthField && !array_key_exists($field,$this->noCut)) $style['width']=$this->widthField.'em';
		}
		*/
		return $this->trArray2StringStyle($style);
	}
	protected function fieldValue($field){
		if(!array_key_exists($field,$this->row)) return '';
		$value=$this->row[$field];
		$typeFields=$this->typeFields;
		$fn=$this->fn;
		//show(array($field=>$this->fn[$field]));
		if(array_key_exists($field,$this->fn)) return call_user_func($fn[$field],$value,$field,$this->row,$this);
		elseif(is_null($value)) return '';
		elseif($this->outFormat=='xml') {
			switch ($typeFields[$field]){
				case 'date': 
				case 'datetime': 
				case 'timestamp': 
					$d=strtotime($value);
					return $d>=0?strftime('%FT%TZ',$d):null;
			}
		}
		elseif(!$this->outFormat) {
			$format=$this->format;
			$format=@$format[$field];
			switch ($typeFields[$field]){
				case 'date': 
				case 'datetime': 
				case 'timestamp': return strftime($format,strtotime($value));
				default:          return sprintf($format,$value);
			}
		}
		return $value;
	}
	
	//Monta o WHERE
	public function mountWhereExpression($array){
		$where=array();
		if($array) foreach ($array as $key=>$value) {
			if ($value=='*') continue;
			
			$empty="(`$key` IS NULL OR (`$key`!='0' AND `$key`=''))";

			if ($value==='' || $value==='=') $where[]=$empty;
			elseif (preg_match("/^(<>|![=*]?)$/",$value)) $where[]="NOT$empty";
			elseif($cond=$this->mountWhereExpressionDetail($key,$value)) $where[]=$cond;
		}
		//showme($where);
		$where=$where?" WHERE ".implode(" AND ",$where):"";
		//print "MANUTENÇÃO: $where<br>";
		//show($array);
		return $where;
	}
	public function mountWhereExpressionDetail($key,$value){
		//show(array($key=>$value));
		//$value=htmlConvert($value);
		//Testa se é ereg
		if (preg_match("/^(!?\([!n]?)(?:ereg|regexp)\)(.*)/i",$value,$return)) {
			$not=$return[1]=='('?'':'NOT ';
			return "`$key` {$not}REGEXP '".str_replace("'","\\'",$return[2])."'";
		}

		//Testa se é um LIKE
		$ret=preg_split("/(?:([|&])(!=?|<[>=]?|>=?|==?)?)|(!=?|<[>=]?|>=?|==?)/", $value, -1, PREG_SPLIT_OFFSET_CAPTURE);
		//print '<pre>'.print_r(array($key=>$value,$ret),true).'</pre>';
		$where='';
		$ini=0;
		$itens=-1;
		foreach ($ret as &$v) {
			if($v[0]==='' && !$v[1]) continue;
			$tam=$v[1]-$ini;
			$d=substr($value,$ini,$tam);
			$tam+=strlen($v[0]);
			preg_match("/^([|&]?)(.*)$/",$d,$r);
			//print "MANUTENÇÃO: <pre>".print_r($v,true)."</pre>";
			//print "d: $d | tam: $tam | $ini<br>";
			$v[1]=$s=$r[2];
			/*
			$v[0]=preg_replace(
				array("/\xAB/"	,"/\xA3/"	,"/\xA1/"	,"/\xA6/"	,"/\xBB/"	,"/\xA4/"),
				array('<'		,'&'		,'!'		,'|'		,'>'		,'='),
				$v[0]
			);
			*/
			//show(array($v));
			$dateTime=array();
			/*
			if(preg_match('/^[0-9\?\* \/-]+$/',$v[0])){
				if(preg_match("/([\d*?]{1,2})([-\/])([\d*?]{1,2})\\2([\d*?]{2,4})/",$v[0],$retDT)) {
					$this->trDateTime($retDT);
					$retDT[4]=preg_match("/\?/",$retDT[4])?str_pad($retDT[4],4,"?",STR_PAD_LEFT):substr(strftime("%Y"),0,4-strlen($retDT[4])).$retDT[4];
					$retDT="{$retDT[4]}-{$retDT[3]}-{$retDT[1]}";
					if(preg_match("/\?/",$retDT) || strtotime($retDT)) $dateTime[]=$retDT;
				}
				if(preg_match("/([\d*?]{1,2}):([\d*?]{1,2}):([\d*?]{1,2})/",$v[0],$retDT)) {
					$this->trDateTime($retDT);
					$retDT="{$retDT[1]}:{$retDT[2]}:{$retDT[3]}";
					if(preg_match("/\?/",$retDT) || strtotime($retDT)) $dateTime[]=$retDT;
				}
			}
			if ($dateTime) $v[0]=implode(" ",$dateTime);
			*/
	//print "MANUTENÇÃO: $s{$v[0]}<br>";
			if ($s=='' || $s=='!'){
				if (preg_match("/[*?]/",$v[0])) {
					if ($dateTime) $v[0]="*{$v[0]}*";
					$s=($s==''?'':' NOT').' LIKE ';
					$v[0]=str_replace(array('*','?'),array('%','_'),$v[0]);
				}else $s.='=';
			} elseif($s=='==') $s='=';
			$sign=$ini?$sign=$r[1]=='|'?' OR ':' AND ':'';
			//$where.='(';
			$where.="$sign(`$key`$s'".addslashes($v[0])."'";
			$itens++;
			if ($v[0]=='' && preg_match("/=|!|<>/",$s)) {
				$n=preg_match("/!|<>/",$s)?" NOT":"";
				$where.=" OR `$key` IS$n NULL";
				$itens++;
			}
			$where.=')';
			$ini+=$tam;
		}
		if ($where) $where=$itens?"($where)":$where;
	//print "MANUTENÇÃO: {$where}<br>";
		return htmlConvert($where);
	}
	public function trDateTime(&$d){
		foreach ($d as &$v){
			$v=str_replace("*","?",$v);
			$v=str_pad($v,2,preg_match("/\?/",$v)?"?":"0",STR_PAD_LEFT);
		}
	}
}