<?php
class Conn_mysqli_details_field extends Conn_mysqli_details_main {
	public $indexTypes=array('INDEX'=>1,'KEY'=>1,'UNIQUE'=>2,'PRIMARY'=>4,'FOREIGN'=>8,'FULLTEXT'=>16,'SPATIAL'=>32,);
	protected function getFullPath(){ return self::$path."/{$this->readonly['dsn']}/{$this->readonly['db']}/table/{$this->readonly['table']}/fields"; }
	protected function getFullName(){ return "`{$this->readonly['db']}`.`{$this->readonly['table']}`.`{$this->readonly['name']}`"; }
	protected function getStatus(){ return true; }
	protected function rebuildObj(){
		$this->log();
		$length=$precision=$listArg=null;
		if(preg_match('/^(set|enum)/i',$this->protect['force']['type'],$rTy)) $listArg=$this->getSourceSetEnum($this->protect['force']['type'],$length);
		elseif(preg_match('/^(\w+)\((\d+)(?:,(\d+))?\)/i',$this->protect['force']['type'],$rTy)) {
			$length=$rTy[2];
			$precision=@$rTy[3];
		}
		else $rTy=array(1=>$this->protect['force']['type']);
		$type=$rTy[1];
		$name=$this->protect['force']['field'];
		$this->readonly['type']              =$type;
		$this->readonly['length']            =$length;
		$this->readonly['precision']         =$precision;
		$this->readonly['listArg']           =$listArg;
		$this->readonly['unsigned']          =(bool)@$this->protect['force']['unsigned'];
		$this->readonly['zerofill']          =(bool)@$this->protect['force']['zerofill'];
		$this->readonly['not_null']          =strtolower(@$this->protect['force']['not_null'])=='not null';
		$this->readonly['auto_increment']    =(bool)@$this->protect['force']['auto_increment'];
		$this->readonly['default']           =$this->decodeComment(preg_replace('/\'(.*)\'/','\1',@$this->protect['force']['default']));
		$this->readonly['defaultRaw']        =@$this->protect['force']['default'];
		$this->readonly['on_update']         =@$this->protect['force']['on_update'];
		$this->readonly['comment']           =$this->decodeComment(@$this->protect['force']['comment']);
		$this->readonly['charset']           =@$this->protect['force']['charset'];
		$this->readonly['collate']           =@$this->protect['force']['collate'];
		$this->readonly['column_format']     =@$this->protect['force']['column_format'];
		$this->readonly['storage']           =@$this->protect['force']['storage'];
		$this->readonly['key']=0;
		$this->readonly['line']              =$this->protect['force'][0];
		$this->setKey(@$this->protect['force']['key']);
		return true;
	}
	function setKey($keyValue){ /*bits 1=INDEX(+1), 2=UNIQUE(+2), 3=PRIMARY(+4), 4=FOREIGN(+8).. see $this->indexTypes*/
		$this->readonly['key']|=(int)$keyValue; 
	}
	protected function splitFullName($fullName,$field=false){ return parent::splitFullName($fullName,true); }
}