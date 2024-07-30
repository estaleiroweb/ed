<?php
//$_SESSION=array();
class DataTable extends OverLoadElements {
	private $tr1=array(
		'\\'=>"\x01",
		'!' =>"\x02",
		'&' =>"\x04",
		'|' =>"\x05",
		'=' =>"\x06",
		'<' =>"\x07",
		'>' =>"\x08",
		'*' =>"\x0B",
		'?' =>"\x0C",
	);
	private $tr2=array(
		'%' =>"\x0E",
		'_' =>"\x0F",
	);
	private $oldDb=null;
	private $config,$data;
	protected $protect=array(
		'idFile'=>null,
		'idView'=>null,
		'CRUDS'=>31,
		'conn'=>null,
		'sql'=>null,
		'db'=>null,
		'label'=>null,
		'showLabel'=>null,
		'tagLabel'=>array('start'=>'<h2>','end'=>'</h2>'),
		'method'=>'POST',
		'error'=>'',
		'class'=>'table table-striped table-bordered table-hover table-condensed dt-responsive',/*
			default:
					 - hover + order-column + row-border
				cell-border - Cells with a border
				 - Increase the data density by reducing the cell padding
				hover - Highlight a row when hovered over
				order-column - Highlight the cells in the column currently being ordering upon
				row-border - Rows with a border
				stripe - Zebra striped rows
			bootstrap:
				table table-striped table-bordered
		*/
		'order'=>array(),
		'colVis'=>null,
		'scrollY'=>'200px', //Default 200px
		'scrollCollapse'=>true,  //Default true
		'scrollX'=> true, //Default true
		'ajaxAddData'=>null,
		'fields'=>array(),
		'fieldsName'=>array(),
		'queryUpdate'=>'', //FIXME automatizar processo
		'key'=>null,
	);
	public function __construct($label=null){
		//$this->logFile(null,true);
		//$this->logFile($GLOBALS['__autoload']);
		$this->protect['idFile']='eDT'.md5($GLOBALS['__autoload']->thisFile.$GLOBALS['__autoload']->fullUrl);
		if(is_array($label)) parent::__construct($label);
		$this->label=$label;
		$this->protect['colVis']=new DataTable_ColVis;
	}
	public function __toString(){
		{//Carregar configuracoes e checar
			$s=Secure::singleton(true);
			if(($access=$s->access)) $this->protect['CRUDS']=@$access['CRUDS'];
			$this->connect();
			$this->config=self::loadConfig($this->idFile,$this->idView);
			if(!$this->config) $this->config=$this->buildConfig();
			$this->config=$this->buildConfig();//FIXME retirar
			$this->config['client']['CRUDS']=$this->protect['CRUDS'];
			if(!Secure::can_R($this->config['client']['CRUDS'])) return '';
		}
		//$this->do_where_column(0,'=$\\=as\'&*df=3423!asdf%');
		{//Iniciando
			$lf="\n";
			$tab="\t";
			$ini=$tab;
			$numFileds=count($this->config['client']['columns']);
			new Bootstrap;
			new ExternalPlugins('dataTables');
			new ExternalPlugins('dataTables-ColReorder');
			new ExternalPlugins('dataTables-ColVis');
			new ExternalPlugins('dataTables-defaults');
		}
		{//Monta Script
			$OutHtml=OutHtml::singleton();
			$OutHtml->jQueryScript['dataTable.'.$this->idView]='$("#'.$this->idView.'").dataTable('.json_encode2($this->config['client']).');';
		}
		{//Monta Html
			$out=$this->buildLabel().$lf;
			$out.=$ini.'<table id="'.$this->idView.'" class="'.$this->class.'" cellspacing="0" width="100%">'.$lf;
			$out.=$ini.$tab.'<thead>'.$lf;
			$out.=$ini.$tab.$tab.'<tr>'.$lf;
			//$this->config['server']['fields']
			foreach($this->config['server']['fields'] as $v) {
				$out.=$ini.$tab.$tab.$tab.'<td><input type="text" style="width:100%" value="'.htmlentities($v['value']).'" /></td>'.$lf;
			}
			$out.=$ini.$tab.$tab.'</tr>'.$lf;
			$out.=$ini.$tab.$tab.'<tr>'.$lf;
			$out.=str_repeat($ini.$tab.$tab.$tab.'<th>&nbsp;</th>'.$lf,$numFileds);
			$out.=$ini.$tab.$tab.'</tr>'.$lf;
			$out.=$ini.$tab.'</thead>'.$lf;
			$out.=$ini.'</table>'.$lf;
		}
		{//Finalizando
			self::saveConfig($this->idFile,$this->idView,$this->config);
			$this->leaveConnection();
			return $out;
		}
	}
	protected function logFile($text,$new=false){
		file_put_contents('/var/tmp/DataTable.txt',print_r($text,true),$new?null:FILE_APPEND);
	}
	
	public function setConn($conn){ $this->protect['conn']=$conn; }
	public function setDsn($conn){ $this->setConn($conn); }
	public function setSql($sql){
		$this->protect['sql']=$sql;
		$this->protect['idView']='eDT'.md5($sql); //1f3870be274f6c49b3e31a0c6728957f
	}
	public function setView($sql){ $this->setSql($sql); }
	public function setQuery($sql){ $this->setSql($sql); }
	public function setDataBase($db){ $this->db=$db; }
	public function setColVis($empty){ }
	public function setFields($empty){ }
	public function setMassUpdate($fields=null,$options=null,$formClass=null,$buttonClass=null,$showLabels=false){ 
		if(is_null($fields)) {
			if(isset($this->protect['massUpdate'])) unset($this->protect['massUpdate']);
			return;
		}
		$this->protect['massUpdate']=array(
			'fields'=>is_string($fields)?$fields:(bool)$fields,
			'options'=>$options?$options:'SPFA', //Selected, Filtered, All
			'formClass'=>is_null($formClass)?'':$formClass, // '' | form-inline | form-horizontal 
			'buttonClass'=>$buttonClass?$buttonClass:'btn-primary', // btn-default | btn-primary | btn-success | btn-info | btn-warning | btn-danger
			'showLabels'=>(bool)$showLabels,
		);
	}
	public function getFields($field=null){
		return $field?@$this->protect['fields'][$field]:$this->protect['fields'];
	}
	public function cfgField($field,$property,$value=null){
		if(!array_key_exists($field,$this->protect['fields'])) $this->protect['fields'][$field]=new DataTable_Columns($this);
		if(is_string($property)) $property=array($property=>$value);
		else $property=(array)$property;
		foreach($property as $p=>$v)  $this->protect['fields'][$field]->$p=$v;
	}
	public function cfgFields($fields,$property,$value=null){
		$string=false;
		if(is_string($fields)) {
			$string=true;
			$fields=field_split($fields);
		}
		if($string) foreach($fields as $k) $this->cfgField($k,$property,$value);
		else foreach($fields as $k=>$v) $this->cfgField($k,$property,$v);
	}
	public function loadData(){
		{//Connect
			$this->config=self::loadConfig($this->idFile,$this->idView);
			if(!$this->config) $this->fatal('Without config'); //Config not found
			if($_SERVER['REQUEST_METHOD']!=$this->config['client']['ajax']['type']) $this->fatal('Wrong config'); //Diferent Method
			//$this->logFile($this->config['client']);
			if(!Secure::can_R($this->config['client']['CRUDS'])) $this->fatal('Permition denied: READ');
			$this->data=$GLOBALS['_'.$_SERVER['REQUEST_METHOD']];
			
			$this->conn=$this->config['server']['dsn'];
			if(@$this->config['server']['db']) $this->db=$this->config['server']['db'];
			$this->connect();
			$out=array(
				'draw'            => (int)@$this->data['draw'],
				'recordsTotal'    => 0,
				'recordsFiltered' => 0,
				'data'            => array(),
			);
		}
		//$this->logFile(array('data'=>$this->data,'config'=>$this->config));
		$where=$this->do_where();
		$order=$this->do_order();
		$limit=$this->do_limit();
		if(($action=@$this->data['action'])) { //Action CUD
			$type=$action['type'][0];
			if($type=='C') $fn='createData';
			elseif($type=='U') $fn='updateData';
			elseif($type=='D') $fn='deleteData';
			else $fn='';
			if($fn) $this->$fn($action,$where,$order,$limit);
		}
		if($out['draw'] && @$this->data['columns']) {//draw
			$sql=$this->config['server']['sql'].$where.$order.$limit;
			//$this->logFile(array('sql'=>$sql));
			$res=$this->protect['conn']->query($sql,false);
			$err=$this->protect['conn']->error();
			if($err) $this->fatal($err);
			$out['data']=$res->fetch_row_all(); //fetch_assoc_all | fetch_row_all
			$line=$this->protect['conn']->fastLine('SELECT FOUND_ROWS() r');
			$out['recordsFiltered']=$line['r'];
			$line=$this->protect['conn']->fastLine($this->config['server']['sqlCount']);
			$out['recordsTotal']=$line['r'];
			$res->close();
		}
		//$this->logFile(array('out'=>$out));
		{//Finaliza
			//self::saveConfig($this->idFile,$this->idView,$this->config);
			$this->leaveConnection();
			print json_encode($out);
			exit(0);
		}
	}
	public function fatal($msq) {
		$out=json_encode(array('error'=>$msq));
		//$this->logFile($out);
		print $out;
		exit(0);
	}

	static private function loadConfig($idFile,$idView){
		return @$_SESSION[__CLASS__][$idFile][$idView];
	}
	static private function saveConfig($idFile,$idView,$config){
		$_SESSION[__CLASS__][$idFile][$idView]=$config;
	}
	
	private function buildLabel(){
		$label=$this->label;
		$showLabel=$this->showLabel;
		if(!$label || (!is_null($showLabel) && !$showLabel)) return '';
		$tagLabel=$this->tagLabel;
		if(!@$tagLabel['start'] || !@$tagLabel['end']) $tagLabel=array('start'=>'<h2>','end'=>'</h2>');
		return $tagLabel['start'].$label.$tagLabel['end'];
	}
	private function buildConfig(){
		$config=loadFileDefault('config.ini');
		
		$ajaxAddData=$this->ajaxAddData;
		$ajaxAddData="function (d) {
			d=dataTable_dataRefresh('{$this->idView}',d);
			
			/*ajaxAddData content*/$ajaxAddData
		}";
		$server=$this->buildServer();
		$out=array(
			'server'=>$server,
			'client'=>array(
				'idFile'=>$this->idFile,
				'idView'=>$this->idView,
				'__autoload'=>$GLOBALS['__autoload']->thisFile,
				'colVis'=>$this->buildColVis(),
				'scrollY'=>$this->buildVariable('scrollY','200px'),
				'scrollCollapse'=>$this->buildVariable('scrollCollapse',true),
				'scrollX'=>$this->buildVariable('scrollX',true),
				'ajax'=>array(
					'url'=>$config['easyData']['fn'].'/dataTable.php',
					//'url'=>'scripts/server_processing.php',
					'type'=>$this->protect['method'],
					'data'=>json_stripSlashes($ajaxAddData),
				),
			),
		);
		$fields=$this->buildFields($server['sql']);
		$out['server']['fields']=$fields['server'];
		$out['server']['fieldsName']=$this->protect['fieldsName'];
		$out['server']['regional']['datetime_format']=$this->sumary_date_format($config['regional']['datetime_format']);
		$out['server']['regional']['date_format']=$this->sumary_date_format($config['regional']['date_format']);
		$out['server']['regional']['time_format']=$this->sumary_date_format($config['regional']['time_format']);
		if($this->protect['key']) $out['client']['key']=$this->FiledName2Num($this->protect['key']);
		$out['client']['columns']=$fields['client'];

		$mu=@$this->protect['massUpdate'];
		if(@$mu && Secure::can_U($this->protect['CRUDS'])) {
			if($mu['fields']===true) $mu['fields']=array_values($this->protect['fieldsName']);
			else $mu['fields']=$this->FiledsName2Num($mu['fields']);
			$out['client']['massUpdate']=$mu;
			$out['server']['queryUpdate']=$this->protect['queryUpdate'];
		}
		
		if(($order=$this->buildOrder())) $out['client']['order']=$order;
		//show($out);
		return $out;
	}
	private function buildServer(){
		$out=array('dsn'=>$this->protect['conn']->dsn);
		
		$sql=trim($this->protect['sql']);
		$whereWord='WHERE ';
		if(preg_match('/\bSELECT\b/i',$sql)) {
			if(preg_match('/\b(UNION|LIMIT|GROUP|ORDER)\b/i',$sql)) {
				$sqlCount='SELECT COUNT(1) r FROM ('.$sql.') t';
				$sql='SELECT SQL_CALC_FOUND_ROWS * FROM ('.$sql.') t';
			}
			else {
				$sqlCount=preg_replace('/^((?:\(|\s)*SELECT\b)(?:.|\s)*\bFROM\b\s*/i','\1 COUNT(1) r FROM ',$sql);
				$sql=preg_replace('/^((?:\(|\s)*SELECT\b)/i','\1 SQL_CALC_FOUND_ROWS ',$sql);
				if(preg_match('/\bWHERE\b/i',$sql)) {
					$sql=preg_replace('/(\bWHERE\b)\s*((?:\s|.)*)/i','\1 (\2)',$sql);
					$whereWord='AND ';
				}
			}
		}
		else {
			$sqlCount='SELECT COUNT(1) r FROM '.$sql;
			$sql='SELECT SQL_CALC_FOUND_ROWS * FROM '.$sql;
		}
		
		$out['sql']=$sql;
		$out['sqlCount']=$sqlCount;
		$out['whereWord']=$whereWord;
		return $out;
	}
	private function buildFields($sql){
		//Captura informações no servidor
		$res=$this->protect['conn']->query($sql.' LIMIT 0');
		$viewFields=$res->fetch_fields();
		$this->protect['fieldsName']=array_flip($res->fields());
		$res->close();
		
		//Trabalha informações
		$fields=$this->fields;
		$out=array();
		foreach($viewFields as $k=>$v) {
			unset($v->name);
			unset($v->table);
			unset($v->type);
			
			$this->cfgField($v->orgname,'title',$v->orgname);
			$this->cfgField($v->orgname,$v);
			$this->cfgField($v->orgname,'value','*');
			$out['client'][$k]=$this->protect['fields'][$v->orgname]->client;
			$out['server'][$k]=$this->protect['fields'][$v->orgname]->server;
		}
		//show($out);
		return $out;
	}
	private function buildOrder(){//FIXME "order":  [[ 5, "asc" ],[ 2, "asc" ],[ 3, "asc" ],[ 6, "asc" ],[ 4, "desc" ],[ 7, "desc" ]],
		$out=array();
		$order=$this->order;
		$order=is_array($order)?$this->order:field_split($order);
		foreach($order as $k=>$v) {
			$v=preg_split('/ +/',$v);
			if(count($v)==1) $v[1]='asc';
			$v[0]=$this->FiledName2Num($v[0]);
			if($v[0]) $out[]=$v;
		}
		return $out;
	}
	private function buildColVis(){
		$colVis=$this->colVis->protect;
		if(array_key_exists('exclude',$colVis)) {
			if($colVis['exclude']) $colVis['exclude']=$this->FiledsName2Num($colVis['exclude']);
			if(!$colVis['exclude']) unset($colVis['exclude']);
		}
		if(array_key_exists('groups',$colVis)) {
			if($colVis['groups']) {
				$group=array();
				foreach($colVis['groups'] as $gk=>$gv) {
					$gv['columns']=$this->FiledsName2Num($gv['columns']);
					if($gv['columns']) $group[]=$gv;
				}
				$colVis['groups']=$group;
			}
			if(!$colVis['groups']) unset($colVis['groups']);
		}
		return $colVis;
	}
	private function buildVariable($var,$default=null){
		$out=$this->$var;
		return is_null($out)?$default:$out;
	}
	
	private function connect(){
		$this->protect['conn']=is_object($this->protect['conn'])?$this->protect['conn']:Conn::dsn($this->protect['conn']);
		if(@$this->protect['db']) {
			$this->oldDb=$this->protect['conn']->get_database();
			$this->protect['conn']->select_db($this->protect['db']);
		}
		return $this->protect['conn'];
	}
	private function leaveConnection(){
		if($this->oldDb) {
			$this->protect['conn']->select_db($this->oldDb);
			$this->oldDb=null;
		}
	}
	private function createData($action,$where,$order,$limit){
		if(!Secure::can_C($this->config['client']['CRUDS'])) $this->fatal('Permition denied: CREATE');
		//FIXME incluir instruçoes de update
	}
	private function updateData($action,$where,$order,$limit){
		if(!Secure::can_U($this->config['client']['CRUDS'])) $this->fatal('Permition denied: UPDATE');
		if(!$action['values']) $this->fatal('Error: UPDATE VALUES');
		$sql=$this->config['server']['queryUpdate'];
		
		$type=$action['type'];
		$set=array();
		//$this->logFile(array('fields'=>$this->config['server']['fields']));
		foreach($action['values'] as $k=>$v) $set[]=$this->do_field($k).'="'.$this->protect['conn']->escape_string($v).'"';
		$sql.=" SET \n\t".implode(", \n\t",$set)." \n";
		
		if($type=='US'){ //Update Selecionados
			$field=$this->do_field($this->config['client']['key']);
			if(!$field) $this->fatal('Error: UPDATE SELECTED FIELD');
			foreach($action['selected'] as $k=>$v) $action['selected'][$k]='"'.$this->protect['conn']->escape_string($v).'"';
			$sql.=$this->config['server']['whereWord'].$field.' IN ('.implode(', ',$action['selected']).')';
		}
		elseif($type=='UP'){ //Update esta página
			$sql.=$where.$order.$limit;
		}
		elseif($type=='UF'){ //Update Filtrados
			$sql.=$where;
		}
		elseif($type!='UA') $this->fatal('Error: UPDATE TYPE'); //Update Todos
		//$this->logFile(array('UPDATE sql'=>$sql));
		$this->protect['conn']->query($sql,false);
		$er=$this->protect['conn']->error();
		if($er) $this->fatal('Error: UPDATE SQL => '.$er); 
	}
	private function deleteData($action,$where,$order,$limit){
		if(!Secure::can_D($this->config['client']['CRUDS'])) $this->fatal('Permition denied: DELETE');
		//FIXME incluir instruçoes de delete
	}

	private function FiledsName2Num($fields){
		$out=array();
		$fields=field_split($fields);
		foreach($fields as $k=>$v) if(is_numeric($fields[$k]=$this->FiledName2Num($v))) $out[]=$fields[$k];
		return $out;
	}
	private function FiledName2Num($field){
		return $this->protect['fieldsName']?@$this->protect['fieldsName'][$field]:null;
	}
	private function sumary_date_format($format){
		return preg_replace(
			array('/%%/','/%[bBh]/','/%c/'    ,'/%D/'    ,'/%e/','/%[IrR]/','/%y/','/%T/'    ,'/[^dmYHMS% :\/-]/','/%[^dmYHMS]/','/ +/','/ ([:\/-])/'),
			array(''    ,'%m'      ,'%x','%m/%d/%y','%d'  ,'%H'      ,'%Y'  ,'%H:%M:%S',''                 ,''            ,' '   ,'\1'),
			$format
		);
	}
	private function trDateTime($d){
		$d=str_replace('*','?',$d);
		foreach ($d as &$v) $v=str_pad($v,2,strpos($v,'?')===false?0:'?',STR_PAD_LEFT);
		return $d;
	}
	private function N2H($value){//Escape Chars
		foreach($this->tr1 as $k=>$v) $value=str_replace('\\'.$k,$v,$value);
		foreach($this->tr2 as $k=>$v) $value=str_replace($k,$v,$value);
		return $value;
	}
	private function H2N($value){//Unescape Chars
		$value=str_replace(array('*','?'),array('%','_'),$value);
		foreach($this->tr1 as $k=>$v) $value=str_replace($v,$k,$value);
		$value=addslashes($value);
		foreach($this->tr2 as $k=>$v) $value=str_replace($v,'\\'.$k,$value);
		return $value;
	}
	
	private function do_field($num){
		$fld=@$this->config['server']['fields'][$num];
		if($fld) return '`'.$fld['orgtable'].'`.`'.$fld['orgname'].'`';
	}
	private function do_limit(){
		if (isset($this->data['start']) && $this->data['length']!=-1) return " \nLIMIT ".intval($this->data['start']).', '.intval($this->data['length']);
		else return '';
	}
	private function do_order(){
		if(!@$this->data['order']) return ;
		$order=array();
		foreach($this->data['order'] as $l) if(toBool($this->data['columns'][$l['column']]['orderable'])) {
			$order[]=$this->do_field($l['column']).($l['dir']==='asc'?'':' DESC');
		}
		return " \nORDER BY ".implode(', ',$order);
	}
	private function do_where(){
		{//Iniciando
			if(!@$this->config['server']['fields']) return ;
			$columns=$this->data['columns'];
			
			$out=array();
		}
		{//Global Search
			$search=(string)@$this->data['search']['value'];
			if($search!=='') {
				if(toBool(@$this->data['search']['regex'])) {
					$value=' REGEXP "'.$this->protect['conn']->escape_string($search).'"';
				}
				else {
					$value=' LIKE "%'.preg_replace(array('/%/','/ +/'),array('\\%','%'),$this->protect['conn']->escape_string(trim($search))).'%"';
				}
				foreach($columns as $num=>$l) if(toBool($l['searchable'])) $out[]=$this->do_field($num).$value;
				$out=array('('.implode(' OR ',$out).')');
			}
		}
		{// Column Search
			foreach($columns as $num=>$l) if(toBool($l['searchable'])) {
				$search=$l['search']['value'];
				if($search=='') $out[]=$this->do_where_column_null($num);
				elseif($search!='*') {
					if(toBool($l['search']['regex'])) {
						if($search[0]=='!') {
							$not=' NOT';
							$search=substr($search,1);
						} else $not='';
						$out[]=$this->do_field($num).$not.' REGEXP "'.$this->protect['conn']->escape_string($search).'"';
					}
					elseif(($wh=$this->do_where_column($num,$search))) $out[]=$wh;
				}
			}
		}
		{//Finalizando
			if(!$out) return ;
			$c=count($out);
			$out=implode(" \nAND ",$out);
			if($c>1) $out='('.$out.')';
			return " \n".$this->config['server']['whereWord'].$out;
		}
	}
	private function do_where_column_null($num) {
		return 'IFNULL('.$this->do_field($num).',"")=""';
	}
	private function do_where_column($num,$search){
		{//Starting variables
			$search=$this->N2H($search);
			$ret=preg_split("/(?:([|&])(!=?|<[>=]?|>=?|==?)?)|(!=?|<[>=]?|>=?|==?)/", $search, -1, PREG_SPLIT_OFFSET_CAPTURE);
			$key=$this->do_field($num);
			$type=$this->config['server']['fields'][$num]['vartype'];
			if($type=='BOOLEAN') $fn='do_where_type_BOOLEAN';
			elseif($type=='DATE') $fn='do_where_type_DATE';
			elseif($type=='TIME') $fn='do_where_type_TIME';
			elseif($type=='DATETIME' || $type=='TIMESTAMP') $fn='do_where_type_DATETIME';
			else $fn='do_where_type_VARCHAR';
			$where='';
			$ini=0;
			$itens=-1;
		}
		foreach ($ret as $k=>$v) {
			$finder=$v[0];
			$posFinder=$v[1];
			if($finder==='' && !$posFinder) continue;
			$tam=$posFinder-$ini;
			$and_or_sign=substr($search,$ini,$tam);
			preg_match("/^([|&]?)(.*)$/",$and_or_sign,$res_and_or_sign);
			$finderTam=strlen($finder);
			$sign=$res_and_or_sign[2];
			$cond=$this->$fn($sign,$finder,$finderTam,$key);

			if($cond) {
				$and_or=$ini?($res_and_or_sign[1]=='|'?' OR ':' AND '):''; //join AND/OR
				$where.=$and_or.$cond;
				$itens++;
			}
			$ini+=$tam+$finderTam;
		}
		if($itens && $where) $where='('.$where.')';
		return $where;
	}
	private function do_where_type(&$sign,&$finder,$finderTam,$key,$nullValue='',$like=null){
		if(!is_null($like)) return $like;
		if($finderTam && ($sign=='' || $sign=='!') && preg_match('/[*?]/',$finder)) {
			if($finder=='*') return;
			$like=true;
			$sign=($sign==''?'':' NOT').' LIKE ';
			$finder=str_replace(array('*','?'),array('%','_'),$finder);
		}
		else {
			$like=false;
			if($finder=='*' && ($sign=='' || $sign=='!')) return;
			if($sign=='==' || $sign=='') $sign='=';
			elseif($sign=='<>' || $sign=='!') $sign='!=';
			if(!$finderTam) return 'IFNULL('.$key.',"'.$nullValue.'")'.$sign.'"'.$nullValue.'"';
		}
		//$this->logFile(array('like'=>$like,'sign'=>$sign,'finder'=>$finder,'finderTam'=>$finderTam,'key'=>$key));
		return $like;
	}
	private function do_where_type_VARCHAR($sign,$finder,$finderTam,$key){
		$like=$this->do_where_type($sign,$finder,$finderTam,$key);
		if(!is_bool($like)) return $like;
		return 'IFNULL('.$key.',"")'.$sign.'"'.$this->H2N($finder).'"';
	}
	private function do_where_type_DATE_aux1($finder){
		static $tr=array(
			'jan'=>'01','fev'=>'02','mar'=>'03','abr'=>'04','mai'=>'05','jun'=>'06',
			'jul'=>'07','ago'=>'08','set'=>'09','out'=>'10','nov'=>'11','dez'=>'12',

			'janeiro'=>'01','fevereiro'=>'02','março'=>'03','abril'=>'04','maio'=>'05','junho'=>'06',
			'julho'=>'07','agosto'=>'08','setembro'=>'09','outubro'=>'10','novembro'=>'11','dezembro'=>'12',

			'feb'=>'02','apr'=>'04','may'=>'05','aug'=>'08','sep'=>'09','oct'=>'10','dec'=>'12',

			'january'=>'01','february'=>'02','march'=>'03','april'=>'04','may'=>'05','june'=>'06',
			'july'=>'07','august'=>'08','september'=>'09','october'=>'10','november'=>'11','december'=>'12',
		);
		if(preg_match('/[a-z]/',$finder)) {
			$finder=@$tr[$finder];
			if($finder) return $finder;
		}
	}
	private function do_where_type_DATE_aux2($finder,$tam=2){
		if(preg_match('/^(%+|%?_{2,}%?|[%_]+\d+|\d+[%_]+|\d{2,})$/',$finder)) return $finder;
		if($tam==2) return substr('0'.$finder,-2);
		$finder=substr($finder,-4);
		$y=substr(strftime('%Y'),0,4-strlen($finder));
		return $y.$finder;
	}
	private function do_where_type_DATE($sign,&$finder,$finderTam,$key,$force=null){
		static $usDate=null;
		if(is_null($usDate)) $usDate=preg_match('/%m[\/-]%d[\/-]%y/i',$this->config['server']['regional']['date_format']);

		$finder=strtolower($finder);
		if(preg_match('/[^0-9a-z-\/\*\?]/',$finder)) return;
		
		$like=$this->do_where_type($sign,$finder,$finderTam,$key,'0000-00-00',$force);
		if(!is_bool($like) && !is_null($force)) return $like;
		
		$format=array();
		if(preg_match('/^\d{4}-\d{2}-\d{2}$/',$finder)) $format=array('%Y-%m-%d');
		elseif(preg_match('/^(\d{2})-(\d{2})-(\d{4})$/',$finder,$ret)) {
			$format=('%Y-%m-%d');
			if($usDate) $finder=$ret[3].'-'.$ret[1].'-'.$ret[2];
			return $finder=$ret[3].'-'.$ret[2].'-'.$ret[1];
		}
		if($format) return !is_null($force)?$format:$key.$sign.'"'.$finder.'"';

		$sp=preg_split('/[\/-]/',$finder);
		$tam=count($sp);
		if($tam>3) return 'FALSE';
		$num=$o=array();
		$ok=false;
		foreach($sp as $k=>$v) {
			$o[$k]=$this->do_where_type_DATE_aux1($v);
			if($o[$k]) { //Verifica se há 2 ou + meses literais na data
				if($ok) return 'FALSE';
				$ok=true;
				$num[$k]=0;
				$sp[$k]=$o[$k];
			} 
			else $num[$k]=strlen($v)>2;
			//$sp[$k]=$sign.'"'.$v.'"';
		}
		//$sign.'"'.$sp[0].'"'
		$isTimeStamp=strpos($finder,'/')==false?true:false;
		if($tam==1) { //YYYY | MM | DD | MMM | MMMM
			$format=array('%Y','%m','%d');
		}
		elseif($tam==2) { //YYYY-MM | MM-DD | MM/YYYY | DD/YYYY
			if($o[0]) { // MMM-DD | MMM/YYYY | MMM/DD
				$format=array($isTimeStamp || $usDate?'%m-%d':'%m-%Y');
			}
			elseif($o[1]) { // YYYY-MMM | DD/MMM
				$format=array($isTimeStamp?'%Y-%m':'%d-%m');
			}
			elseif($isTimeStamp) { // YYYY-MM | MM-DD
				$format=array('%Y-%m');
				if(!$num[0]) $format[]='%m-%d';
			}
			elseif($usDate) { // DD/YYYY | MM/DD
				$format=array('%d-%Y');
				if(!$num[1]) $format[]='%m-%d';
			}
			else { // MM/YYYY | DD/MM
				$format=array('%m-%Y');
				if(!$num[1]) $format[]='%d-%m';
			}
		}
		elseif($isTimeStamp) { // YYYY-MM-DD
			$format=array('%Y-%m-%d');
		}
		elseif($usDate) { // MM/DD/YYYY
			$format=array('%m-%d-%Y');
		}
		else {
			$format=array('%d-%m-%Y');
		}

		$finder=array();
		foreach($format as $i=>$frm) {
			$f=explode('-',$frm);
			foreach($f as $k=>$v) {
				if($v=='%m' && $o[$k]) $f[$k]=$o[$k];
				else $f[$k]=$this->do_where_type_DATE_aux2($sp[$k],$v=='%Y'?4:2);
			}
			$finder[]=!is_null($force)?implode('-',$f):'DATE_FORMAT('.$key.',"'.$frm.'"'.$sign.'"'.implode('-',$f).'"';
		}
		if(!is_null($force)) return $format;
		return count($finder)?'('.implode(' OR ',$finder).')':$finder[0];
	}
	private function do_where_type_TIME($sign,&$finder,$finderTam,$key,$force=null){
		if(preg_match('/[^0-9:\*\?]/',$finder)) return;
		
		$like=$this->do_where_type($sign,$finder,$finderTam,$key,'00:00:00',$force);
		if(!is_bool($like) && !is_null($force)) return $like;
		
		if(preg_match('/^\d{2}:\d{2}:\d{2}$/',$finder)) return !is_null($force)?'%H:%M:%S':$key.$sign.'"'.$finder.'"';
		
		$f=array('%H','%M','%S');
		$sp=explode(':',$finder);
		$val=$frm=array();
		foreach($sp as $k=>$v) {
			$frm[]=$f[$k];
			$val[]=$this->do_where_type_DATE_aux2($sp[$k]);
			if($k>=3)break;
		}
		$finder=implode(':',$val);
		$format=implode('-',$frm);
		return !is_null($force)?$format:'DATE_FORMAT('.$key.',"'.$format.'"'.$sign.'"'.$finder.'"';
	}
	private function do_where_type_DATETIME($sign,$finder,$finderTam,$key){
		$like=$this->do_where_type($sign,$finder,$finderTam,$key,'0000-00-00 00:00:00');
		if(!is_bool($like)) return $like;

		$finder=preg_split('/ +/',$finder);
		$tam=count($finder);
		if($tam>2) return 'FALSE';
		$and_or=$tam==2?' AND ':' OR ';
		$dF=$finder[0];
		$tF=$finder[$tam-1];
		$format_date=$this->do_where_type_DATE($sign,$dF,strlen($dF),$key,$like);
		$format_time=$this->do_where_type_TIME($sign,$tF,strlen($tF),$key,$like);
		
		if($format_date) {
			$out=array();
			foreach($format_date as $k=>$v) {
				if($format_time) {
					$frm=$v.' '.$format_time;
					if($frm=='%Y-%m-%d %H:%M:%S') $out[]=$key.$sign.'"'.$dF[$k].' '.$tF.'"';
					else $out[]='DATE_FORMAT('.$key.',"'.$frm.'"'.$sign.'"'.$dF[$k].' '.$tF.'"';
				}
				else $out[]='DATE_FORMAT('.$key.',"'.$v.'"'.$sign.'"'.$dF[$k].'"';
			}
			return count($out)?'('.implode(' OR ',$out).')':$out[0];
		}
		if($format_time) return 'DATE_FORMAT('.$key.',"'.$format_time.'"'.$sign.'"'.$finder[$tam-1].'"';
		else return 'FALSE';
	}
	private function do_where_type_BOOLEAN($sign,$finder,$finderTam,$key){
		$finder=$finder?(int)toBool($finder):'';
		return $this->do_where_type_VARCHAR($sign,$finder,$finderTam,$key); 
	}
}
