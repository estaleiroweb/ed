<?php
class XmlSql extends Xml {
	/**
	 * Variaveis com controle de conteudo
	 * conn: @var string|array|ConnObj conexão com o banco
	 * db: @var string banco diferente da conn
	 * sql: @var string sql que poderá gerar o XML
	 */
	protected $protect=array(
		'db'=>'',
		'sql'=>'',
		'function'=>'',
		'recordNodeName'=>'record',
		'recordAtt'=>array(),
		'showFields'=>array(),
		'line'=>array(),
		'details'=>array(
			'serverInfo'=>'',
			'clientInfo'=>'',
			'charset'=>'',
			'numRows'=>0,
			'names'=>array(),
			'fields'=>array(),
		),
		'conn'=>null, 
	);
	private $res;
	function setConn($val) {
		if (is_string($val)) $val=Conn::dsn($val);
		elseif (is_array($val)) $val=Conn::singleton($val);
		$this->protect['conn']=$val;
	}
	function setDataBase($val) {
		$this->protect['conn']->select_db($val);
		$this->protect['db']=$val;
	}
	function setSql($val=false) {
		if($val) $this->protect['sql']=$val;
		if($this->protect['sql'] && $this->protect['conn']) {
			$this->res=$this->protect['conn']->query($this->protect['sql']);
			$fields=$this->res->fetch_fields();
			$tmp=$names=array();
			foreach($fields as $k=>$obj) $tmp[$k]=$obj->name;
			while($tmp) {
				$value=reset($tmp);
				$key=key($tmp);
				$ret=preg_grep('/^'.preg_quote($value).'$/',$tmp);
				$i=0;
				if(count($ret)>1) foreach($ret as $k=>&$v) {
					while(preg_grep('/^'.preg_quote($value).(++$i).'$/',$tmp));
					$tmp[$k].=$i;
				}
				$names[$key]=$tmp[$key];
				$fields[$key]->key=$key;
				$fields[$key]->nodeName=$tmp[$key];
				$fields[$key]->fn='';
				$fields[$key]->att=array();
				unset($tmp[$key]);
			}
			$names=array_flip($names);
			$this->protect['details']=array(
				'serverInfo'=>$this->protect['conn']->get_server_info(),
				'clientInfo'=>$this->protect['conn']->get_client_info(),
				'charset'=>$this->protect['conn']->get_charset(),
				'numRows'=>$this->res->num_rows(),
				'names'=>$names,
				'fields'=>$fields,
			);

		}
	}
	function setRecordAtt($val) {
		if(!is_array($val)) $val=preg_split('/\s*[,;]\s*/',(string)$val);
		$this->protect['recordAtt']=$val;
	}
	function setShowFields($val) {
		if(!$this->res) return;
		if(!is_array($val)) $val=preg_split('/\s*[,;]\s*/',(string)$val);
		$this->protect['showFields']=array();
		foreach($val as $v) if(($v=$this->getDetail($v))) $this->protect['showFields'][$v->key]=$v->nodeName;
	}
	function setDetailField($field,$item,$value) {
		$field=$this->getDetail($field);
		if(!$field || !preg_match('/^(nodeName|function|att)$/',$item)) return;
		$this->protect['details']['fields'][$field->key]->$item=$value;
	}
	function getDetail($field){
		if(isset($this->protect['details']['names'][$field])) $field=$this->protect['details']['names'][$field];
		if(isset($this->protect['details']['fields'][$field])) return $this->protect['details']['fields'][$field];
	}
	function makeLine() {
		$line=(array)$this->protect['line'];
		if(!$line) return;
		
		$this->child[]=$o=new Xml($this->protect['recordNodeName']);
		foreach($this->protect['recordAtt'] as $v) {
			$v=$this->getDetail($v);
			if($v) $o->att[$v->nodeName]=$line[$v->key];
			//$this->protect['showFields']
		}
	
		foreach($line as $field=>$value) if(!$this->protect['showFields'] || isset($this->protect['showFields'][$field])) $o->child[]=$this->makeField($field,$value);
		
		return $o;
	}
	function makeField($field,$value=null) {
		$field=$this->getDetail($field);
		if(!$field) return;
		if($field->fn) $value=eval('return '.$field->fn.'($value,$field,$this);');		
		$o=new Xml($field->nodeName);
		$o->att=$field->att;
		$o->child[]=$value;
		return $o;
	}
	function __toString(){
		if($this->res) {
			$ev=($this->protect['function']?$this->protect['function'].'($this);':'$this->makeLine();');
			while($this->protect['line']=$this->res->fetch_row()) eval($ev);
		}
		return parent::__toString();
	}
}