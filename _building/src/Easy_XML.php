<?php
class Easy_XML {
	public $pre='@';
	public $showAttributes=true;
	public $showNamespaces=false;
	public $showPath=false;
	public $showXml=false;
	public $addNamespaceInName=true;
	
	/**
	 * Variaveis resgataveis
	 * @var array
	 */
	protected $protect=array(
		'sxe'=>null,
		'out'=>null,
	);
	/**
	 * Retorna o valor da variavel sobrecarregada
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function __get($nm){
		$fn='get'.ucfirst($nm);
		if(method_exists($this,$fn)) return $this->$fn();
		elseif(isset($this->protect[$nm])) return $this->protect[$nm];
	}
	/**
	 * Sobrecarrega as variaveis
	 *
	 * @param string $nm Nome da Variavel
	 * @param mixed $val Valor
	 */
	public function __set($nm,$val){
		$fn='set'.ucfirst($nm);
		if(method_exists($this,$fn)) $this->$fn($val);
		elseif(isset($this->protect[$nm])) $this->protect[$nm]=$val;
	}
	function getProtect() {
		return $this->protect;
	}
	function load_string($xmlstr) {
		$this->protect['sxe']=simplexml_load_string($xmlstr);
		return $this->parser($this->protect['sxe']);
	}
	function load_file($file) {
		$this->protect['sxe']=simplexml_load_file($file);
		return $this->parser($this->protect['sxe']);
	}
	function import_dom($node,$class_name='SimpleXMLElement') {
		$this->protect['sxe']=simplexml_import_dom($node,$class_name);
		return $this->parser($this->protect['sxe']);
	}
	function parser($sxe,$xPath=''){
		$fn=__FUNCTION__;
		if(!is_object($sxe)) return null;
		$obj=new StdClass;
		$name=$this->getXmlName($sxe);
		if($xPath=='') $obj->{$name}=$this->$fn($sxe,'/');
		else {
			$nsp=$sxe->getNamespaces(true);
			if($this->showPath) $obj->{$this->pre.'path'}=$xPath;
			if($this->showNamespaces && $nsp) $obj->{$this->pre.'nameSpaces'}=$nsp;
			if($this->showAttributes && ($att=$sxe->attributes())) $obj->{$this->pre.'attributes'}=$att;
			if($this->showXml) $obj->{$this->pre.'xml'}=$sxe->asXML();
			$path="$xPath{$this->getXmlName($sxe,true)}/";
			if(!$nsp) $nsp=array(''=>'');
			$NhasChild=true;
			$aChildren=array();
			foreach($nsp as $namespace=>$url) {
				$children=$sxe->children($namespace,true);
				if($children) foreach($children as $nodeName=>$node) {
					$NhasChild=false;
					$aChildren[$this->getXmlName($node)][]=$this->rebuildObject($this->$fn($node,$path));
				}
			}
			if($NhasChild) {
				if(($children=$sxe->xpath($path.'*'))) {
					foreach($children as $id=>$node) {
						$NhasChild=false;
						$aChildren[$this->getXmlName($node)][]=$this->rebuildObject($this->$fn($node,$path));
					}
				} else $aChildren=get_object_vars($sxe);
			}
			if($aChildren) foreach($aChildren as $nodeName=>$nodes) {
				if(count($nodes)==1) $nodes=$nodes[0];
				$obj->{$nodeName}=$nodes;
			}
			if($NhasChild) return trim($sxe->__toString());
		}
		return $obj;
	}
	private function getXmlName($sxe,$force=false){
		$name=$sxe->getName();
		if(($force || $this->addNamespaceInName) && ($nsp=$sxe->getNamespaces()) && ($pre=key($nsp))) $name=$pre.':'.$name;
		return $name;
	}
	private function rebuildObject($obj){
		if(is_object($obj)) {
			$obj=(object)(array)$obj;
			if(($vars=get_object_vars($obj))) foreach($vars as $childName=>$nodes) $obj->{$childName}=$this->rebuildObject($nodes);
		}elseif(is_array($obj)) foreach($obj as $k=>$v) $obj[$k]=$this->rebuildObject($v);
		return $obj;
	}
}
