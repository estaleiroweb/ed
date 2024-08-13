<?php
// http://datatables.net/reference/option/columns
// http://legacy.datatables.net/usage/columns
class DataTable_Columns extends Geters_Seters {
	private $dad;
	private $server=array();
	private $client=array();
	private $filter=array(
		'function'       =>array('target'=>'server','name'=>'function',      'setFn'=>null,            'getFn'=>null),
		'orgname'        =>array('target'=>'server','name'=>'orgname',       'setFn'=>'orgname',       'getFn'=>null),
		'orgtable'       =>array('target'=>'server','name'=>'orgtable',      'setFn'=>null,            'getFn'=>null),
		'db'             =>array('target'=>'server','name'=>'db',            'setFn'=>null,            'getFn'=>null),
		'vartype'        =>array('target'=>'server','name'=>'vartype',       'setFn'=>'vartype',       'getFn'=>null),
		'value'          =>array('target'=>'server','name'=>'value',         'setFn'=>null,            'getFn'=>null),
		'cellType'       =>array('target'=>'client','name'=>'cellType',      'setFn'=>null,            'getFn'=>null),
		'sCellType'      =>array('target'=>'client','name'=>'cellType',      'setFn'=>null,            'getFn'=>null),
		'className'      =>array('target'=>'client','name'=>'className',     'setFn'=>null,            'getFn'=>null),
		'class'          =>array('target'=>'client','name'=>'className',     'setFn'=>null,            'getFn'=>null),
		'sClass'         =>array('target'=>'client','name'=>'className',     'setFn'=>null,            'getFn'=>null),
		'contentPadding' =>array('target'=>'client','name'=>'contentPadding','setFn'=>null,            'getFn'=>null),
		'sContentPadding'=>array('target'=>'client','name'=>'contentPadding','setFn'=>null,            'getFn'=>null),
		'createdCell'    =>array('target'=>'client','name'=>'createdCell',   'setFn'=>'toSetFunction', 'getFn'=>'toGetFunction'),
		'fnCreatedCell'  =>array('target'=>'client','name'=>'fnCreatedCell', 'setFn'=>'toSetFunction', 'getFn'=>'toGetFunction'),
		'data'           =>array('target'=>'client','name'=>'data',          'setFn'=>null,            'getFn'=>null),
		'mData'          =>array('target'=>'client','name'=>'mData',         'setFn'=>'toSetFunction', 'getFn'=>'toGetFunction'),
		'fnData'         =>array('target'=>'client','name'=>'mData',         'setFn'=>'toSetFunction', 'getFn'=>'toGetFunction'),
		'defaultContent' =>array('target'=>'client','name'=>'defaultContent','setFn'=>null,            'getFn'=>null),
		'sDefaultContent'=>array('target'=>'client','name'=>'defaultContent','setFn'=>null,            'getFn'=>null),
		'name'           =>array('target'=>'client','name'=>'name',          'setFn'=>null,            'getFn'=>null),
		'sName'          =>array('target'=>'client','name'=>'name',          'setFn'=>null,            'getFn'=>null),
		'orderable'      =>array('target'=>'client','name'=>'orderable',     'setFn'=>'toBool',        'getFn'=>null),
		'sortable'       =>array('target'=>'client','name'=>'orderable',     'setFn'=>'toBool',        'getFn'=>null),
		'bSortable'      =>array('target'=>'client','name'=>'orderable',     'setFn'=>'toBool',        'getFn'=>null),
		'orderData'      =>array('target'=>'client','name'=>'orderData',     'setFn'=>'field_split',   'getFn'=>'trFields'),
		'dataSort'       =>array('target'=>'client','name'=>'orderData',     'setFn'=>'field_split',   'getFn'=>'trFields'),
		'aDataSort'      =>array('target'=>'client','name'=>'orderData',     'setFn'=>'field_split',   'getFn'=>'trFields'),
		'orderDataType'  =>array('target'=>'client','name'=>'orderDataType', 'setFn'=>null,            'getFn'=>null),
		'render'         =>array('target'=>'client','name'=>'render',        'setFn'=>null,            'getFn'=>null),
		'fnRender'       =>array('target'=>'client','name'=>'mRender',       'setFn'=>'toSetFunction', 'getFn'=>'toGetFunction'),
		'mRender'        =>array('target'=>'client','name'=>'mRender',       'setFn'=>'toSetFunction', 'getFn'=>'toGetFunction'),
		'searchable'     =>array('target'=>'client','name'=>'searchable',    'setFn'=>'toBool',        'getFn'=>null),
		'bSearchable'    =>array('target'=>'client','name'=>'searchable',    'setFn'=>'toBool',        'getFn'=>null),
		'title'          =>array('target'=>'client','name'=>'title',         'setFn'=>null,            'getFn'=>null),
		'sTitle'         =>array('target'=>'client','name'=>'title',         'setFn'=>null,            'getFn'=>null),
		'label'          =>array('target'=>'client','name'=>'title',         'setFn'=>null,            'getFn'=>null),
		'type'           =>array('target'=>'client','name'=>'type',          'setFn'=>null,            'getFn'=>null),
		'sType'          =>array('target'=>'client','name'=>'type',          'setFn'=>null,            'getFn'=>null),
		'visible'        =>array('target'=>'client','name'=>'visible',       'setFn'=>'toBool',        'getFn'=>null),
		'bVisible'       =>array('target'=>'client','name'=>'visible',       'setFn'=>'toBool',        'getFn'=>null),
		'length'         =>array('target'=>'client','name'=>'width',         'setFn'=>'length',        'getFn'=>null),
		'width'          =>array('target'=>'client','name'=>'width',         'setFn'=>null,            'getFn'=>null),
		'sWidth'         =>array('target'=>'client','name'=>'width',         'setFn'=>null,            'getFn'=>null),
		'sorting'        =>array('target'=>'client','name'=>'asSorting',     'setFn'=>'sortWords',     'getFn'=>null),
		'asSorting'      =>array('target'=>'client','name'=>'asSorting',     'setFn'=>'sortWords',     'getFn'=>null),
	);
	
	function __construct($dad){ $this->dad=$dad; }
	public function __get($nm){
		if(($f=@$this->filter[$nm])) {
			$out=$this->{$f['target']}[$f['name']];
			$fn=$f['getFn'];
			return $fn?$this->$fn($f['name'],$out):$out;
		}
		$fn='get'.ucfirst($nm);
		if(method_exists($this,$fn)) return $this->$fn();
	}
	public function __set($nm,$val){
		if(($f=@$this->filter[$nm])) {
			if(($fn=$f['setFn'])) $val=$this->$fn($nm,$val);
			if(!array_key_exists($f['name'],$this->{$f['target']})) $this->{$f['target']}[$f['name']]=$val;
		} 
		else {
			$fn='set'.ucfirst($nm);
			if(method_exists($this,$fn)) $this->$fn($val);
		}
	}

	private function trFields($nm,$aFields){ return $this->dad->FiledsName2Num($aFields); }
	function outFormat($out) { 
		foreach($out as $k=>$v) $out[$k]=$this->__get($k);
		return $out;
	}
	function getServer(){ return $this->server; }
	function getClient(){ return $this->client; }
	function getAll(){ return array('server'=>$this->getServer(),'client'=>$this->getClient()); }
	
	function toBool($nm,$val) { return toBool($val); }
	function field_split($nm,$val) { return field_split($val); }
	function sortWords($nm,$val) { return array_values(preg_grep('/^(asc|desc)$/',field_split(strtolower($val)))); }
	function vartype($nm,$val) {
		if(preg_match('/^(date(?:time)?|timestamp)$/i',$val)) $this->__set('type','date');
		elseif(preg_match('/^((?:big|small|tiny)?int(?:eger)?|decimal|float|double)$/i',$val)) $this->__set('type','numeric');
		else $this->__set('type','html');
		return $val;
	}
	function orgname($nm,$val) {
		$this->client['name']=$val;
		return $val;
	}
	function length($nm,$val) {
		static $max=30;
		if($val>$max) $val=$max;
		return ceil($val/1.8).'em';
	}
}