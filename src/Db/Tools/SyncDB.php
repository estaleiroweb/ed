<?php
class SyncDB extends OverLoadElements {
	protected $readonly=array('conn'=>null,'connTarget'=>null,'startTime'=>null,'dsnTarget'=>null);
	protected $protect=array('debug'=>null);
	private $tableName=null;

	function __construct($conn=null,$connTarget=null) {
		parent::__construct();
		if(in_array("-d",$GLOBALS['argv'])) $this->debug=true;
		$this->conn=$conn;
		$this->connTarget=$connTarget;
		$this->readonly['startTime']=microtime(true);
	}
	function __destruct(){
		$this->tableName='';
		$tg=microtime(true)-$this->readonly['startTime'];
		$this->pr("End $tg\n");
	}
	private function connect($dsn){
		if (!$dsn) return;
		elseif (is_string($dsn)) return Conn::dsn($dsn);
		elseif (is_array($dsn))  return Conn::singleton($dsn);
	}
	function setConn($value){ return $this->readonly['conn']=$this->connect($value); }
	function setConnTarget($value){ return $this->readonly['connTarget']=$this->connect($this->readonly['dsnTarget']=$value); }
	function sync($tableName,$keys,$DateFields='DtUpdate',$force=false){
		{//Declarations
			if(!$this->readonly['conn'] || !$this->readonly['connTarget']) die("Sem string de conexão\n");
			$this->tableName=$tableName;
			$this->pr("Start\n");
			
			$time=microtime(true);
			$tmpTbl="tmp_sync_$tableName";
		}
		{//Cria tabela temporária
			$this->readonly['connTarget']->query("DROP TABLE IF EXISTS $tmpTbl");
			$ddlSource=@$this->readonly['conn']->fastLine("SHOW CREATE TABLE $tableName",false);
			$ddlSource=@$ddlSource['Create Table'];
			if(!$ddlSource) return $this->pr("Não existe a tabela $tableName\n");
			$ddlTmpTable=preg_replace("/^(CREATE\s+TABLE\s*)(`.*`)/i","\\1$tmpTbl",$ddlSource);
			$this->readonly['connTarget']->query($ddlTmpTable);
		}
		{//Captura a maior data
			$this->pr('Getting Max Date: ');
			$maxDtSource=$force?'':@$this->readonly['connTarget']->fastLine("SELECT MAX({$DateFields}) as dt FROM $tableName",false);
			$maxDtSource=@$maxDtSource['dt'];
			$where=$maxDtSource?" WHERE {$DateFields}>'{$maxDtSource}'":'';
			parent::pr(($maxDtSource?$maxDtSource:($force?'Forced':'Empty'))."\n");
			$db=$this->readonly['connTarget']->db;
			$this->readonly['connTarget']->close();
		}
		{//Parsing
			$this->pr('Parsing ');
			$res=$this->readonly['conn']->query("SELECT * FROM $tableName$where ORDER BY $DateFields",false);
			$this->readonly['connTarget']=$this->connect($this->readonly['dsnTarget']);
			$this->readonly['connTarget']->select_db($db);
			parent::pr('[');
			$lineOld=null;
			while ($line=$res->fetch_assoc()) if($this->readonly['connTarget']->insertLine($tmpTbl,$lineOld=$line)) parent::pr('.');
			$res->close();
			if($this->readonly['connTarget']->insertLine($tmpTbl)) parent::pr('.');
			parent::pr("]\n");
		}
		{//Ending - Verifica Tabela Existente
			$this->pr("Ending...\n");
			$ddlTarget=$this->readonly['connTarget']->fastLine("SHOW CREATE TABLE $tableName",false);
			$ddlTarget=@$ddlTarget['Create Table'];
			if(!$maxDtSource || !$ddlTarget || md5($this->stripDDL($ddlSource))!=md5($this->stripDDL($ddlTarget))) {
				$this->readonly['connTarget']->query("DROP TABLE IF EXISTS $tableName");
				$this->readonly['connTarget']->query("ALTER TABLE $tmpTbl RENAME TO $tableName",false);
			}
			else {
				if($lineOld) {
					$setFields=$this->readonly['connTarget']->mountComparation(array_keys($lineOld),null,null,',');
					$keys=$this->readonly['connTarget']->mountComparation($keys);
					$this->readonly['connTarget']->query("UPDATE $tableName as t JOIN $tmpTbl as s ON $keys SET {$setFields}",false);
					$this->readonly['connTarget']->query("INSERT IGNORE $tableName SELECT * FROM $tmpTbl",false);
				}
				$this->readonly['connTarget']->query("DROP TABLE IF EXISTS $tmpTbl");
			}
			$tg=microtime(true)-$time;
			$this->pr("End $tg\n");
		}
	}
	function stripDDL($ddl){
		return preg_replace(
			array('/\)\s+ENGINE.*/','/[\n\r]\s+CONSTRAINT\s+[^\n\r]*/','/[\s\(\),`]/',),
			'',
			strtoupper($ddl)
		);
	}
	public function pr($text,$force=false){ parent::pr(strftime('[%F %T] ').$this->tableName.': '.$text); }
}
