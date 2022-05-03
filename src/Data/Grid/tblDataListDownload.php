<?php
namespace Evoice\Data\Grid;

use EstaleiroWeb\ED\Db\Conn\Conn;

ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('memory_limit','1G');
class tblDataListDownload {
	private $path='/download';
	private $conn,$res,$root,$file,$manifest,$context;
	function __construct($file=false){
		$this->context=stream_context_create(array('http' => array('header' => 'Accept-Charset: UTF-8, *;q=0')));
		$this->root='/var/www/html';
		($this->file=$file) || ($this->file=$this->createFile());
	}
	function __toString(){ /*ordem=campo&sentido=asc|desc*/
		$dir=$this->root.$this->path.'/*.status';
		if($ordem=strtolower(@$_GET['ordem'])) ($sentido=strtolower(@$_GET['sentido'])) || ($sentido='asc');
		else {
			$ordem='datetime';
			$sentido='asc';
		}
		if($ordem=='pid' || $ordem=='status' || $ordem=='rows' || $ordem=='size') $sort_flags=SORT_NUMERIC;
		else $sort_flags=SORT_STRING;
		
		//print '<pre>'.`ls -1 $dir`.'</pre>';exit;
		$dir=explode("\n",trim(`ls -1 $dir`));
		
		
		$c=array();
		$orderby=array();
		$cont=0;
		foreach($dir as $file) if($file) {
			$p=pathinfo($file);
			$this->file=$p['filename'];
			$this->manifest=$this->loadManifest();
			$c[$cont]=array_merge(array('file'=>$file),$this->manifest,$this->loadStatus());
			$c[$cont]['rows']="{$c[$cont]['rownum']}/{$c[$cont]['recCount']}";
			$orderby[$cont]=$c[$cont][$ordem];
			$cont++;
		}
		if(!$c) return "Arquivos Inexistentes\n";
		
		if($sentido=='desc') arsort($orderby,$sort_flags);
		else asort($orderby,$sort_flags);
		
		$out="<input type='hidden' id='vOrdem' value='$ordem' /><input type='hidden' id='vSentido' value='$sentido' /><table id='showDownload' cellpadding=0 cellspacing=0 border=0>\n"; 
		$out.="<tr><th>Datetime</th><th>Title</th><th>User</th><th>PID</th><th>Status</th><th>Rows</th><th>Size</th><th>Link</th></tr>\n";
		foreach($orderby as $k=>$v) {
			$out.='<tr>';
			$out.="<td>{$c[$k]['datetime']}</td>";
			$out.="<td>{$c[$k]['title']}</td>";
			$out.="<td>{$c[$k]['user']}</td>";
			$out.="<td>{$c[$k]['pid']}</td>";
			$out.="<td>{$c[$k]['status']}</td>";
			$out.="<td>{$c[$k]['rows']}</td>";
			$out.="<td>{$c[$k]['size']}</td>";

			$lnk=array();
			if(@$c[$k]['refer']) $lnk[]="[<a href='{$c[$k]['refer']}' target='_blank'>Lnk</a>]";
			if($c[$k]['status']=='Completed' && @$c[$k]['link'] && is_file($this->root.$c[$k]['link'])) $lnk[]="[<a href='{$c[$k]['link']}'>{$c[$k]['ext']}</a>]";
			$lnk=implode('&nbsp;',$lnk);
			$out.="<td>$lnk</td>";
			$out.="</tr>\n";
		}
		$out.="</table>\n";
		$out.='<script>
		$("#showDownload th").click(function() { 
			var o=String($(this).text()).toLowerCase();
			var s=(o==$("#vOrdem").val() && $("#vSentido").val()!="desc")?"desc":"asc";
			location="?ordem="+$(this).text()+"&sentido="+s
		});
		$("#showDownload th").each(function() {
			var o=String($(this).text()).toLowerCase();
			if(o==$("#vOrdem").val()) $(this).addClass($("#vSentido").val())
		});
		</script>';
		return $out;
	}
	function start($dsn='',$sql='',$user='',$ext='',$title='',$refer='') {
		if(!$dsn) $dsn='spo11';
		if(!$ext) $ext='xml';
		if(!$sql) $sql='select null Vazio';
		if(!$user) $user='anonymous';
		if(!$title) $title='Sem Título';
		$this->putStatus();
		$this->manifest="dsn=\"$dsn\"\nuser=\"$user\"\nlink=\"{$this->path}/{$this->file}.$ext\"\next=\"$ext\"\ntitle=\"$title\"\nrefer=\"$refer\"\ndatetime=".`date "+%F %T.%N"`;
		$this->putFile($sql,'.sql');
		$this->putFile($this->manifest);
		$fileRun='/var/www/html/bkps/scripts/.exeText_'.trim(`hostname`).'.txt';
		$lineExe="php -r \"require '/var/www/html/shared/easyData/autoload.php'; \\\$d=new tblDataListDownload('{$this->file}'); \\\$d->download();\" &\n";
		//$this->putFile($lineExe,'.sql');
		file_put_contents($fileRun,$lineExe,FILE_APPEND);
		return $this->file;
	}
	function download() {
		set_time_limit(0);
		$this->putStatus('Selecting');
		
		$this->manifest=$this->loadManifest();
		$this->manifest['sql']=$this->getFile('.sql');
		$this->manifest['outputFile']=$this->file.'.'.$this->manifest['ext'];
		
		$this->conn=Conn::dsn($this->manifest['dsn']);
		$this->res=$this->conn->query($this->manifest['sql']);
		$this->manifest['recCount']=$this->res->num_rows();
		$this->manifest['fields']=$this->res->fields();
		
		$this->putStatus('Preparing',$this->manifest['recCount']);
		$fn=__FUNCTION__.'_'.$this->manifest['ext'];
		$this->$fn();
		$this->putStatus('Completed',$this->manifest['recCount'],$this->manifest['recCount']);
		
		$this->res->close();
		$this->conn->close();

		exit;
	}
	function download_xls() { $this->export_Excel('xls'); }
	function download_xlsx() {$this->export_Excel(); }
	function kill() {
		$status=$this->loadStatus();
		if(!@$status['pid']) return;
		`kill -9 {$status['pid']}`;
		$this->putStatus('Canceled',@$status['recCount'],@$status['rownum']);
	}
	
	function putStatus($status='Starting',$recCount=0,$rownum=0){
		$this->putFile(json_encode(array(
		'pid'=>$status?getmypid():0,
		'status'=>$status,
		'recCount'=>$recCount,
		'rownum'=>$rownum,
		'size'=>$this->loadSize(),
		)),'.status');
	}
	function loadManifest() { return parse_ini_string($this->getFile()); }
	function loadStatus() { return (array)json_decode($this->getFile('.status')); }
	function loadSize() {
		if(!@$this->manifest['ext']) return null;
		$file=$this->root.$this->path.'/'.$this->file.'.'.$this->manifest['ext'];
		if(!is_file($file)) return 'no file:'.$file;
		return `ls -hl "$file" | awk '{print $5 "B"}'`;
	}
	function export($content){ $this->putFile($content,'.'.$this->manifest['ext'],FILE_APPEND); }
	function createFile(){ return basename(tempnam($this->root.$this->path,'exp_')); }
	function putFile($content,$function='',$flag=FILE_USE_INCLUDE_PATH){ 
		static $f='';
		$file=$this->root.$this->path.'/'.$this->file.$function;
		file_put_contents($file,$content,$flag,$this->context); 
		if($f!=$file) {
			$f=$file;
			`chmod +r $file`;
		}
	}
	function getFile($function=''){ return file_get_contents($this->root.$this->path.'/'.$this->file.$function,FILE_TEXT,$this->context); }
	function encodingLatim1($content){ return mb_convert_encoding($content,'ISO-8859-1',mb_detect_encoding($content)); }
	function encodingUTF8($content){ return mb_convert_encoding($content,'UTF-8','auto'); }
	function stripSings($text){ return preg_replace('/[^a-zA-Z0-9_-]/', '', strtr($text, 'áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ', 'aaaaeeiooouucAAAAEEIOOOUUC')); }
	function export_Excel($ext='xlsx'){
		$objPHPExcel = new PHPExcel();
		
		//Locale
		if (!PHPExcel_Settings::setLocale('pt_br')) exit;

		$title=utf8_encode($this->manifest['title']); //,ENT_QUOTES,'UTF-8');
		//$title=$this->manifest['title'];
		$objPHPExcel->getProperties()->setCreator('BdConfig')
									 ->setLastModifiedBy('BdConfig')
									 ->setTitle($title)
									 ->setSubject('BdConfig Export')
									 ->setDescription('BdConfig Export in '.$this->manifest['datetime'].': '.$title)
									 ->setKeywords('')
									 ->setCategory('BdConfig Export');

		// Rename worksheet
		$objPHPExcel->getActiveSheet(0)->setTitle($title);

		//Hide grid lines
		$objPHPExcel->getActiveSheet()->setShowGridLines(false);

		//Put Header
		$objPHPExcel->getActiveSheet()->fromArray(array_map('utf8_encode',$this->manifest['fields']), NULL, 'A1');

		// Freeze panes
		$objPHPExcel->getActiveSheet()->freezePane('A2');

		//Put Values
		$row=0;
		while($line=$this->res->fetch_row()) {
			$this->putStatus('Loading',$this->manifest['recCount'],++$row);
			$objPHPExcel->getActiveSheet()->fromArray(array_map('utf8_encode',$line), NULL, 'A'.($row+1));
		}
		$row++;
		
		// Set autofilter
		// Always include the complete filter range!
		// Excel does support setting only the caption
		// row, but that's not a best practise...
		$this->putStatus('Processing AutoFilter',$this->manifest['recCount'],$this->manifest['recCount']);
		$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());

		//Formatar Colunas
		$lastCol='';
		$this->putStatus('Processing Format Cols',$this->manifest['recCount'],$this->manifest['recCount']);
		foreach($this->manifest['fields'] as $v) {
			if(!$lastCol) $lastCol='A';
			else $lastCol++;
			
			//Formatar Largura 
			$objPHPExcel->getActiveSheet()->getColumnDimension($lastCol)->setAutoSize(true);
			//Fixme: Formatar tipo campos 
			//Fixme: Formatar estilo campos 
		}
		
		//Formatar estilo do header
		$this->putStatus('Processing Format Header',$this->manifest['recCount'],$this->manifest['recCount']);
		$objPHPExcel->getActiveSheet()->getStyle('A1:'.$lastCol.'1')->applyFromArray(array(
			'font'    => array(
				'bold'      => true
			),
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FFAAAAAA'),
				),
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			),
			'fill' => array(
	 			'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
	  			'rotation'   => 90,
	 			'startcolor' => array(
	 				'argb' => 'FFA0A0A0'
	 			),
	 			'endcolor'   => array(
	 				'argb' => 'FFFFFFFF'
	 			)
	 		)
		));
		
		//Formatar estilo do content
		$this->putStatus('Processing Format Rows',$this->manifest['recCount'],$this->manifest['recCount']);
		$objPHPExcel->getActiveSheet()->getStyle('A2:'.$lastCol.$row)->applyFromArray(array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FFAAAAAA'),
				),
			),
		));

		// Hide columns IU | XFD
		$this->putStatus('Processing Hidding Cols',$this->manifest['recCount'],$this->manifest['recCount']);
		$maxCol=$ext=='xls'?'IU':'XFD';
		do {
			$objPHPExcel->getActiveSheet()->getColumnDimension(++$lastCol)->setVisible(false);
		} while($lastCol!=$maxCol);

		//Save
		$this->putStatus('Saving',$this->manifest['recCount'],$this->manifest['recCount']);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, ($ext=='xls'?'Excel5':'Excel2007'));
		$objWriter->save($this->root.$this->path.'/'.$this->file.'.'.$ext);
		
		//Close Files
		$objPHPExcel->disconnectWorksheets();
	}
	function download_xml(){
		$title=$this->export_Xml_String($this->manifest['title']);
		$user=$this->export_Xml_String($this->manifest['user']);
		$href=$this->export_Xml_String($this->manifest['href']);
		$dtNow=date('c');
		$lineType=array('Par','Impar');
		
		{//Definição de campos e seus estilos
			$fields=$this->res->fetch_fields();
			$styles=array(
				'sPar'  =>'<Style ss:ID="sImpar" ss:Parent="sBorders"><Interior ss:Color="#F2F2F2" ss:Pattern="Solid"/></Style>',
				'sImpar'=>'<Style ss:ID="sPar" ss:Parent="sBorders"><Interior ss:Color="#D9D9D9" ss:Pattern="Solid"/></Style>',
			);
			$columns='';
			foreach($fields as $k=>$v) {
				$v->style='';
				$v->xmlType='String';
				$v->fn='export_Xml_String';
				$v->fieldName=$v->orgname?$v->orgname:$v->name;
				$w=min(200,max($v->length,strlen($v->fieldName))*5+14);
				if($v->vartype=='DATE') {
					$v->style=$s='sDate';
					$v->xmlType='DateTime'; 
					$v->fn='export_Xml_Datetime';
					foreach($lineType as $lt) $styles[$s.'_'.$lt]="<Style ss:ID=\"{$s}_{$lt}\" ss:Parent=\"s{$lt}\"><NumberFormat ss:Format=\"Short Date\"/></Style>";
				}
				elseif($v->vartype=='DATETIME' || $v->vartype=='TIMESTAMP') {
					$v->style=$s='sDateTime';
					$v->xmlType='DateTime';
					$v->fn='export_Xml_Datetime';
					foreach($lineType as $lt) $styles[$s.'_'.$lt]="<Style ss:ID=\"{$s}_{$lt}\" ss:Parent=\"s{$lt}\"><NumberFormat ss:Format=\"General Date\"/></Style>";
				}
				elseif($v->vartype=='TIME') {
					$v->style=$s='sTime';
					$v->xmlType='DateTime';
					$v->fn='export_Xml_Datetime';
					foreach($lineType as $lt) $styles[$s.'_'.$lt]="<Style ss:ID=\"{$s}_{$lt}\" ss:Parent=\"s{$lt}\"><NumberFormat ss:Format=\"hh:mm:ss\"/></Style>";
				}
				elseif($v->vartype=='DECIMAL') {
					$v->style=$s='sDecimal'.$v->decimals;
					$v->xmlType='Number';
					$v->fn='export_Xml_Number';
					$z=$v->decimals?'.'.str_repeat(0,$v->decimals):'';
					foreach($lineType as $lt) $styles[$s.'_'.$lt]="<Style ss:ID=\"{$s}_{$lt}\" ss:Parent=\"s{$lt}\"><NumberFormat ss:Format=\"_-* #,##0{$z}_-;\-* #,##0{$z}_-;_-* &quot;-&quot;??_-;_-@_-\"/></Style>";
				}
				elseif($v->vartype=='BIT') {
					$v->xmlType='Boolean';
					$v->fn='export_Xml_Boolean';
				}
				elseif(preg_match('/(INT(EGER)?|FLOAT|DOUBLE|YEAR)$/',$v->vartype)) {
					$v->xmlType='Number';
					$v->fn='export_Xml_Number';
				}
				$v->styles=$v->style?array($v->style.'_Par',$v->style.'_Impar'):array('sPar','sImpar');
				$fields[$k]=$v;
				$nCol=$k+1;
				$columns.="\t\t\t<Column ss:Index=\"{$nCol}\" ss:AutoFitWidth=\"1\" ss:Width=\"{$w}\" ss:Hidden=\"0\" /><!--{$v->fieldName}:{$v->vartype}-->\n";
			}
			$styles=implode("\n\t\t",$styles);
			
			$nCols=count($fields);
			$nRows=$this->manifest['recCount']+1;
			$range="R1C1:R{$nRows}C{$nCols}";
		}
		{//Grava Configurações
			$this->export('<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
xmlns:html="http://www.w3.org/TR/REC-html40">
	<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
'."		<Title>$title</Title>
		<Subject>BdConfig Exportação: $title</Subject>
		<Author>Helbert Braga Fernandes</Author>
		<Keywords>BdConfig Exportação $title</Keywords>
		<Description>BdConfig Exportação $title às $dtNow</Description>
		<LastAuthor>$user</LastAuthor>
		<Created>$dtNow</Created>
		<LastSaved>$dtNow</LastSaved>
		<Category>BdConfig Exportação</Category>
		<Manager>BdConfig</Manager>
		<Company>Tim</Company>
		<HyperlinkBase>$href</HyperlinkBase>
		<Lines>Concluido</Lines>
		<Version>14.00</Version>
".'	</DocumentProperties>
	<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">
		<AllowPNG/>
	</OfficeDocumentSettings>
	<Styles>
		<Style ss:ID="Default" ss:Name="Normal">
			<Alignment ss:Vertical="Bottom"/>
			<Borders/>
			<Font ss:Size="11" ss:Color="#000000" />
			<!--<Font ss:FontName="Calibri" x:Family="Swiss" ss:Size="11" ss:Color="#000000"/>-->
			<Interior/>
			<NumberFormat/>
			<Protection/>
		</Style>
		<Style ss:ID="sBorders">
			<Borders>
				<Border ss:Position="Top"    ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A6A6A6"/>
				<Border ss:Position="Right"  ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A6A6A6"/>
				<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A6A6A6"/>
				<Border ss:Position="Left"   ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A6A6A6"/>
			</Borders>
		</Style>
		<Style ss:ID="sHeader" ss:Parent="sBorders">
			<Font ss:Bold="1" />
			<Interior ss:Color="#B8CCE4" ss:Pattern="Solid"/>
		</Style>
		'.$styles.'
	</Styles>
	<Worksheet ss:Name="'.$title.'">
		<Names>
			<NamedRange ss:Name="_FilterDatabase" ss:RefersTo="='."'{$title}'!{$range}".'" ss:Hidden="1"/>
		</Names>
		<Table x:FullColumns="1" x:FullRows="1" ss:DefaultColumnWidth="0" ss:DefaultRowHeight="15">
'.$columns);
		}
		{//Save Header
			$linha="\t\t\t<Row ss:AutoFitHeight=\"0\">\n";
			foreach($this->manifest['fields'] as $k=>$v) $linha.="\t\t\t\t<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">{$this->export_Xml_String($v)}</Data></Cell>\n";
			$linha.="\t\t\t</Row>\n";
			$this->export($linha);
		}
		{//Grava Linhas
			$row=0;
			while($line=$this->res->fetch_row()) {
				$this->putStatus('Saving',$this->manifest['recCount'],++$row);
				$linha="\t\t\t<Row ss:AutoFitHeight=\"0\">\n";
				foreach($line as $k=>$v) {
					$fld=$fields[$k];
					$st=$fld->styles[$row&1];
					$fn=$fld->fn;
					$v=$this->$fn($v,$fld);
					$linha.="\t\t\t\t<Cell ss:StyleID=\"{$st}\"><Data ss:Type=\"{$fld->xmlType}\">{$v}</Data></Cell><!--{$fld->orgname}:{$fld->vartype}-->\n";
				}
				$linha.="\t\t\t</Row>\n";
				$this->export($linha);
			}
		}
		{//Grava Fechamento
			$this->export('		</Table>
		<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
			<DoNotDisplayGridlines/>
			<FreezePanes/>
			<FrozenNoSplit/>
			<SplitHorizontal>1</SplitHorizontal>
			<TopRowBottomPane>1</TopRowBottomPane>
			<ActivePane>2</ActivePane>
			<ProtectObjects>False</ProtectObjects>
			<ProtectScenarios>False</ProtectScenarios>
		</WorksheetOptions>
		<AutoFilter x:Range="'.$range.'" xmlns="urn:schemas-microsoft-com:office:excel" />
	</Worksheet>
</Workbook>
');
		}
	}
	function export_Xml_String($v,$fld=false){
		return htmlentities($v, ENT_QUOTES);
	}
	function export_Xml_Datetime($v,$fld=false){
		return strftime('%FT%T',strtotime($v));
	}
	function export_Xml_Boolean($v,$fld=false){
		return $v&1?1:0;
	}
	function export_Xml_Number($v,$fld=false){
		return $v;
	}
}
