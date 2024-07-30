<?php
class CpOracle2MySQL extends Parameters {
	public $connFrom,$connTo;
	public $dtUpdateDefault='DtUpdate'; //Pode ser DtUpdate,DtGer,DtOutro
	public $maxRows=500;
	public $dropTmpTable=true;
	public $truncateTable=false;
	private $tblFrom,$resFrom,$tblTo,$tmpTo,$mssRes,$showTable,$isInsert,$fieldsCLOB;
	private $key='';
	private $set='';
	private $cl="\r                                                                                                           \r";

	function __construct($dsnFron,$dsnTo,$dbTo=''){
		$this->parameters['[t]']=array('tip'=>'Apaga as Tabelas','cmd'=>'$this->truncateTable=true;');
		parent::__construct();
		$this->openFromConnection($dsnFron);
		$this->openToConnection($dsnTo);
		if($dbTo) $this->select_db($dbTo);
	}
	function __destruct(){
		$this->close();
	}
	
	function getTime(){ return '['.trim(`date "+%F %T"`).']'; }
	protected function openFromConnection($dsn){
		$this->connFrom=is_object($dsn)?$dsn:Conn::dsn($dsn);
		if(!($this->connFrom instanceof Conn_oracle)) die("Parametro '--from=$dsn' nao e Oracle\n");
	}
	protected function openToConnection($dsn){
		$this->connTo=is_object($dsn)?$dsn:Conn::dsn($dsn);
		if(!$this->connTo instanceof Conn_mysqli) die("Parametro '--to=$dsn' nao e MySQL\n");
	}	
	protected function select_db($db){
		$this->connTo->select_db($db);
	}
	protected function checkToTable($tbl){
		$res=null;
		$where=(preg_match('/(.*?)\.(.*?)/',$tbl,$res))?" FROM '{$res[1]}' LIKE '{$res[2]}'":" LIKE '{$tbl}'";
		$line=$this->connTo->fastLine('SHOW TABLES'.$where);
		if(!$line || count($line)!=1) return false;
		$line=$this->connTo->fastLine("SHOW CREATE TABLE $tbl");
		return @$line['Create Table'];
	}
	protected function mountCreateTableTo($tbl){
		if(!$tbl) return;
		$fields=array();
		$max=$this->resFrom->num_fields();
		for($i=1;$i<=$max;$i++ ) {
			$size=max(1,$this->resFrom->field_size($i));
			$typeField=$this->resFrom->field_type($i);
			if(preg_match('/^(NUMBER|(BINARY_)?FLOAT|BINARY_DOUBLE|LONG|LONG_?RAW)$/i',$typeField)){
				$precision=$this->resFrom->field_precision($i);
				$scale=$this->resFrom->field_scale($i);
				if(preg_match('/FLOAT/i',$typeField) || ($precision!=0 && $scale!=-127)) {
					$s=$scale>=0?",$scale":'';
					if($precision==0) $type='FLOAT(9,3)';
					if($precision<=9) $type="FLOAT($precision$s)";
					else $type="DOUBLE($precision$s)";
				}else {
					if($size<=4) $type="TINYINT($size)";
					elseif($size<=6) $type="SMALLINT($size)";
					elseif($size<=9) $type="MEDIUMINT($size)";
					elseif($size<=11) $type="INTEGER($size)";
					else $type="BIGINT($size)";
				}
			} elseif($typeField=='DATE'){
				#if($size<=7) $type='DATE'; else $type='DATETIME';
				$type='DATETIME';
			} else {
				if($typeField=='BLOB') $t1=$t2='BLOB';
				else{
					$t2='TEXT';
					if($typeField=='RAW') $t1='BIT';
					elseif($typeField=='CHAR') $t1='CHAR';
					else $t1='VARCHAR';
				}
				if($size<=255) $type="$t1($size)";
				elseif($size<=65535) $type=$t2;
				elseif($size<=16777215) $type='MEDIUM'.$t2;
				else $type='LONG'.$t2;
			}
			$fields[$i]="\t`{$this->resFrom->field_name($i)}` $type".($this->resFrom->field_is_null($i)?'':' NOT NULL');
		}
		return $fields?"CREATE TABLE IF NOT EXISTS $tbl ( \n".implode(", \n",$fields)." \n)":false;
	}
	function buildTablesName($tblFrom,$tblTo){
		if(is_numeric($tblFrom)) $tblFrom=$tblTo;
		if(!$tblTo) $tblTo=$tblFrom;
		$this->tblFrom=$tblFrom;
		$this->tblTo=preg_replace(array('/@.*$/','/`/','/^.+\./'),'',$tblTo);
		$nm='tmpByOraSync_';
		if(preg_match('/(.*?)\.(.*?)/',$this->tblTo,$res)){
			$this->tmpTo=preg_replace('/(\.)/','\1'.$nm,$this->tblTo,1);
		} else {
			$this->tmpTo=$nm.$this->tblTo;
		}
		$this->showTable=$this->checkToTable($this->tblTo);
	}
	function copyTablelAll($tables){
		$tables=(array)$tables;
		foreach($tables as $tblFrom=>$tblTo) $this->copyTable($tblFrom,$tblTo);
	}
	function queryFrom($sql){
		$this->resFrom=$this->connFrom->query($sql);
		$f=$this->resFrom->fetch_fields();
		$this->fieldsCLOB=array();
		foreach($f as $k=>$v) if($v->vartype=='CLOB' || $v->vartype=='BLOB') $this->fieldsCLOB[$v->orgname]=$v->vartype;
	}
	function copyTable($tblFrom,$tblTo='',$fn=''){ 
		$this->buildTablesName($tblFrom,$tblTo);
		$this->isInsert=true;

		$this->mssRes="{$this->getTime()}Copping table {$this->tblTo}: ";
		$this->pr($this->mssRes.'Selecting...');
		//print __LINE__.":".print_r($this->showTable,true)."\n";

		$recnum=0;
		$this->queryFrom("SELECT * FROM {$this->tblFrom}");

		while($line=$this->resFrom->fetch_assoc()) $recnum=$this->insertLine($line);
		$this->insertLine();

		if($this->showTable) {
			if($this->checkToTable($this->tmpTo)) {
				$this->pr(' Finishing');
				if($this->dropTmpTable) $this->connTo->query("DROP TABLE IF EXISTS {$this->tblTo}");
				else $this->connTo->query("ALTER TABLE {$this->tblTo} RENAME TO REN_{$this->tmpTo}");
				$this->connTo->query("ALTER TABLE {$this->tmpTo} RENAME TO {$this->tblTo}");
				if(!$this->dropTmpTable) $this->connTo->query("ALTER TABLE REN_{$this->tmpTo} RENAME TO {$this->tmpTo}");
			} elseif($recnum>0) $this->pr(' ERROR');
		} elseif(($createTable=$this->mountCreateTableTo($this->tblTo))) {
			$this->connTo->query($createTable);
			$this->pr(' TABLE CREATED');
		}
		$this->resFrom->close();
		if($fn){
			$res=$this->connTo->query("SELECT * FROM{$this->tblTo}");
			while($line=$res->fetch_assoc()) $fn(null,$line);
			$res->close();
		}
		$this->pr("\n");
	}
	function syncTable($tblFrom,$idField,$tblTo='',$dtField='',$fn=''){ //idField=>Id1,Id2,Id3.... - dtField=veja $this->dtUpdateDefault, $fn(de,para) é uma funcao macro por registro antes a execução dele
		print "Sync $tblFrom -> $tblTo\n";
		$this->buildTablesName($tblFrom,$tblTo);
		if(!$this->showTable) return $this->copyTable($tblFrom,$tblTo);
		if(!$dtField) $dtField=$this->dtUpdateDefault;
		$this->isInsert=false;
		
		$dtFieldMysql=$this->rebuildDate_MySql($dtField);
		$dtFieldOracle=$this->rebuildDate_Oracle($dtField);
		$maxDt=$this->getMaxDateTo($dtFieldMysql);
		
		if($maxDt && !$this->truncateTable) {
			$whereDt=preg_match('/^000[01]-0[01]-0[01]( 00:00:00)?$/',$maxDt)?'':" WHERE $dtFieldOracle>'$maxDt'";
			$txtDt="/{$maxDt}";
		} else $txtDt=$whereDt='';

		$this->mssRes="{$this->getTime()}Syncing table {$this->tblTo}$txtDt: ";
		$this->pr($this->mssRes.'Selecting...');

		$recnum=0;
		//print_r(array("SELECT * FROM $tblFrom$whereDt ORDER BY $dtFieldOracle",$this->rebuildDate_MySql($dtField),$this->rebuildDate_Oracle($dtField),));
		$this->queryFrom("SELECT * FROM $tblFrom$whereDt ORDER BY $dtFieldOracle");
		while($line=$this->resFrom->fetch_assoc()) {
			$recnum=$this->insertLine($line);
		}
		$this->insertLine();

		$this->resFrom->close();

		//update
		if($this->checkToTable($this->tmpTo) && $recnum) {
			if($this->truncateTable) {
				$this->pr(' Truncate');
				$this->connTo->query("TRUNCATE TABLE {$this->tblTo}");
			}
			if($fn) $this->macroFunction($fn,$idField);
			$this->pr(' Update');
			$this->connTo->query("UPDATE {$this->tmpTo} s \nJOIN {$this->tblTo} t USING ($idField)\n SET {$this->set}");
			$this->pr('['.$this->connTo->affected_rows().']');
			$this->pr(' Insert');
			$this->connTo->query("INSERT IGNORE {$this->tblTo} ({$this->key}) \nSELECT {$this->key} FROM {$this->tmpTo}");
			$this->pr('['.$this->connTo->affected_rows().']');
			if($this->dropTmpTable) $this->connTo->query("DROP TABLE IF EXISTS {$this->tmpTo}");
		} elseif($recnum>0) $this->pr(' ERROR');
		$this->pr("\n");
	}
	function syncTableDel($tblFrom,$idField,$tblTo='',$fn=''){ //veja syncTable
		$this->buildTablesName($tblFrom,$tblTo);
		if(!$this->showTable) return $this->copyTable($tblFrom,$tblTo);
		$this->isInsert=false;

		$this->mssRes="{$this->getTime()}Rebuild table {$this->tblTo}: ";
		$this->pr($this->mssRes.'Selecting...');

		$recnum=0;
		$this->queryFrom("SELECT * FROM $tblFrom");
		while($line=$this->resFrom->fetch_assoc()) $recnum=$this->insertLine($line);
		$this->insertLine();

		$this->resFrom->close();

		//update
		if($this->checkToTable($this->tmpTo) && $recnum) {
			if($this->truncateTable) $this->connTo->query("TRUNCATE TABLE {$this->tblTo}");
			if($fn) $this->macroFunction($fn,$idField,true);
			$oneField=preg_replace('/,.*/','',$idField);
			$this->pr(' Delete');
			$this->connTo->query("DELETE t.* FROM {$this->tblTo} t LEFT JOIN {$this->tmpTo} s USING ($idField) WHERE s.$oneField IS NULL");
			$this->pr('['.$this->connTo->affected_rows().']');
			$this->pr(' Update');
			$this->connTo->query("UPDATE {$this->tmpTo} s JOIN {$this->tblTo} t USING ($idField)\n SET {$this->set}");
			$this->pr('['.$this->connTo->affected_rows().']');
			$this->pr(' Insert');
			$this->connTo->query("INSERT IGNORE {$this->tblTo} ({$this->key}) \nSELECT {$this->key} FROM {$this->tmpTo}");
			$this->pr('['.$this->connTo->affected_rows().']');
			if($this->dropTmpTable) $this->connTo->query("DROP TABLE IF EXISTS {$this->tmpTo}");
		} elseif($recnum>0) $this->pr(' ERROR');
		$this->pr("\n");
	}
	function macroFunction($fn,$idField,$del=false){
		//To Update
		$this->pr(' Macro=Update');
		$sql=" FROM {$this->tmpTo} s JOIN {$this->tblTo} t USING ($idField)";
		$resFrom=$this->connTo->query('SELECT s.* '.$sql);
		$resTo=$this->connTo->query('SELECT t.* '.$sql);
		while($lineFrom=$resFrom->fetch_assoc()) $fn($lineFrom,$resTo->fetch_assoc());
		$resFrom->close();
		$resTo->close();
		
		//To Insert
		$this->pr('/Insert');
		$oneField=preg_replace('/,.*/','',$idField);
		$resTo=$this->connTo->query("SELECT s.* FROM {$this->tmpTo} s LEFT JOIN {$this->tblTo} t USING ($idField) WHERE t.$oneField IS NULL");
		while($lineTo=$resTo->fetch_assoc()) $fn(null,$lineTo);
		$resTo->close();
		
		//To Delete
		if($del){
			$this->pr('/Delete');
			$resFrom=$this->connTo->query("SELECT t.* FROM {$this->tblTo} t LEFT JOIN {$this->tmpTo} s USING ($idField) WHERE s.$oneField IS NULL");
			while($lineTo=$lineFrom->fetch_assoc()) $fn($lineFrom,null);
			$resFrom->close();
		}
	}
	function insertLine($line=array()) {
		static $sql=array();
		static $recnum=0;
		static $createTable='';
		static $tbl='';
		static $key='';

		if($line) {
			$recnum++;
			if(!$key) {
				$key=$this->key='`'.implode('`,`',array_keys($line)).'`';
				$this->set=array();
				foreach($line as $k=>$v) $this->set[]="\tt.$k=s.$k";
				$this->set=implode(",\n",$this->set);
			}
			foreach($line as $k=>$v) {
				if(is_null($v)) $line[$k]='NULL';
				elseif(@$this->fieldsCLOB[$k]) $line[$k]="'".$this->connTo->escape_string((string)$v->load())."'";
				else $line[$k]="'{$this->connTo->escape_string($v)}'";
			}
			$sql[]='('.implode(',',$line).')';
		}  
		$this->pr("{$this->cl}{$this->mssRes}".number_format($recnum,0));
		if($sql && (!$line || count($sql)>=$this->maxRows)) {
			$this->pr(' Save');
			$this->connTo->ping();
			if($createTable==='') {
				if($this->showTable) {
					$tbl=$this->tmpTo;
					$createTable=preg_replace('/(CREATE TABLE) .* (\()/i',"\\1 IF NOT EXISTS {$this->tmpTo} \\2",$this->showTable);
				} else {
					$tbl=$this->tblTo;
					$createTable=$this->mountCreateTableTo($tbl);
				}
				if($createTable) $this->connTo->query($createTable);
				$this->connTo->query("TRUNCATE TABLE {$tbl}");
				//print_r($createTable);
			}
			if($createTable) $query="INSERT IGNORE {$tbl} ({$key}) VALUES \n".implode(", \n",$sql)."\n";
			//print "\n$query\n";
			$this->connTo->query($query);
			$sql=array();
		}
		if(!$line){
			$recnum=0;
			$tbl=$key='';
			$createTable='';
		}
		return $recnum;
	}	
	function getMaxDateTo($dtField){
		$line=$this->connTo->fastLine("SELECT MAX($dtField) as DT FROM {$this->tblTo}");
		return @$line['DT'];
	}
	function rebuildDate_Oracle($dtField){
		$aDt=explode(',',$dtField);
		foreach($aDt as $k=>$v) $aDt[$k]='NVL('.$v.',\'0001-01-01 00:00:00\')';
		return count($aDt)==1?$aDt[0]:'GREATEST('.implode(',',$aDt).')';
	}
	function rebuildDate_MySql($dtField){
		$aDt=explode(',',$dtField);
		foreach($aDt as $k=>$v) $aDt[$k]='IFNULL('.$v.',\'\')';
		return count($aDt)==1?$aDt[0]:'GREATEST('.implode(',',$aDt).')';
	}
	function close(){
		$this->pr($this->getTime()."Fim\n");
		@$this->connFrom->close();
		@$this->connTo->close();
	}
}
