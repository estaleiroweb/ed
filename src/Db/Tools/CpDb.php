<?php
class CpDb {
	public $connFrom,$connTo;
	public $DtUpdate='DtUpdate'; //Pode ser DtUpdate,DtGer,DtOutro

	function __construct($connFrom=null,$connTo=null){
		$this->connFrom=Conn::dsn($connFrom);
		$this->connTo=Conn::dsn($connTo);
	}
	function __destruct(){
		$this->connFrom->close();
		$this->connTo->close();
	}
	
	public function copyTable($query,$toTable=null,$fields=null){
		$this->save($query,$toTable,'',$fields,true);
	}
	public function syncTable($query,$toTable=null,$fields=null,$DtUpdate=null){
		if(is_null($toTable))  $toTable=$this->tableName($query);
		if(is_null($DtUpdate)) $DtUpdate=$this->DtUpdate;
		if(!$this->existsTable($toTable)) return $this->copyTable($query,$toTable,$fields);
		
		$dt=$this->connTo->fastValue("SELECT MAX({$DtUpdate}) dt FROM {$toTable} GROUP BY 1");
		if(!$dt) return $this->copyTable($query,$toTable,$fields);
		$this->fields=$fields;
		
		$w=preg_match('/\bWHERE\b/i',$query)?'AND':'WHERE';
		$this->save($query,$toTable," $w {$this->connFrom->fieldDelimiter($DtUpdate)}>{$this->connFrom->addQuote($dt)} ORDER BY {$DtUpdate}",$fields);
	}
	protected function existsTable($tbl){
		static $arr=array();
		if(!array_key_exists($tbl,$arr)) $arr[$tbl]=$this->connTo->existsTable($tbl);
		return $arr[$tbl];
	}
	protected function save($query,$toTable,$where,$fields,$createTbl=false){
		if(is_null($toTable))  $toTable=$this->tableName($query);
		if(!preg_match('/\SELECT\b/i',$query)) $query="SELECT * FROM $query";

		$dsn="{$this->connTo}";
		$db=$this->connTo->db;
		$this->connTo->close();
		$sql=$query.$where;
		//show($sql);
		$res=$this->connFrom->query($sql);
		$this->connTo=Conn::dsn($dsn);
		$this->connTo->select_db($db);
		
		$newTbl=$toTable;
		$isTbl=false;
		if($createTbl){
			$isTbl=$this->existsTable($toTable);
			if($isTbl) {
				$newTbl=$this->createTableLike($toTable);
				if(!$fields) $fields=$this->connTo->getFields($toTable);
			}
			else {
				// createTable($toTable);
			}
		}
		
		while($line=$res->fetch_assoc()){
			$this->connTo->save($newTbl,$line,null,true);
		}
		$this->connTo->save($newTbl);
		//if($createTbl && $isTbl) $this->connTo->rename($newTbl,$toTable);
	}
	protected function createTableLike($tbl){
		$tmpTable="tmp_$tbl";
		//$this->createLike($tbl,$tmpTable);
		return $tmpTable;
	}
	protected function tableName($query){
		if(preg_match('/\bfrom\b(?:\s\()*(\S+)/i',$query,$ret)) $query=$ret[1];
		elseif(preg_match('/`([^`]+)`$/i',$query,$ret))     $query=$ret[1];
		elseif(preg_match('/"([^"]+)"$/i',$query,$ret))     $query=$ret[1];
		elseif(preg_match('/\[([^\[\]]+)]$/i',$query,$ret)) $query=$ret[1];
		elseif(preg_match('/\.(\w+)$/i',$query,$ret))       $query=$ret[1];
		return $query;
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
	function pr($text){
		print $text;
	}
}
