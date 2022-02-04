<?php
/**
 * Define e monta um XML
 * @author Helbert Fernandes <helbert.fernandes@inteligtelecom.com.br>
 * @version 1.0
 * @package EasyData
 * @example
	$r=new Xml('root');
	$r->att=array('id'=>'exemplo');
	for($i=0;$i<20;$i++) {
		$r->child[]=$a=new Xml('branch');
		$a->att=array('num'=>$i);
	}
	$r->child[]="Texto";
	$r->child[]="<!--coment�rio-->";
	print $r;
 */
class Xml {
	private $head=true;
	protected $dad;
	/**
	 * Identificador do Nó <Obrigatório>
	 * @var string
	 */
	private $nodeName;
	/**
	 * Atributos do Tag ('nome_atributo'=>'valor_atributo','nome_atributo'=>'valor_atributo',...)
	 * @var array
	 */
	public $att=array();
	/**
	 * Filhos do Tag ('associativo'=>'texto ou Objeto Xml',0=>'texto ou Objeto Xml',...)
	 * @var array
	 */
	public $child=array();
	/**
	 * Quebra de linha [\n]
	 * @var string
	 */
	public $ln="\n";
	/**
	 * Mapa de caracters [default ISO-8859-1]
	 * @var string
	 */
	public $charset='ISO-8859-1';
	/**
	 * Define a vers�o do XML [default 1.0]
	 * @var string
	 */
	private $version='1.0';
	/**
	 * Define um nome de arquivo para download, se n�o definido, apenas abre
	 * @var string
	 */
	private $file='';
	/**
	 * Define um comentário para o item
	 * @var string
	 */
	private $comment='';
	/**
	 * Define um código ou texto normal
	 * @var boolean
	 */
	private $isCode=false;
	/**
	 * Instancia um N� XML
	 * @param string $nodeName
	 */
	function __construct($nodeName='root'){ $this->nodeName=$nodeName; }
	public function __set($nm,$val){
		$fn='set'.ucfirst($nm);
		if(method_exists($this,$fn)) $this->$fn($val);
		elseif(isset($this->protect[$nm])) $this->protect[$nm]=$val;
	}
	public function __get($nm){
		$fn='get'.ucfirst($nm);
		if(method_exists($this,$fn)) return $this->$fn();
		elseif(isset($this->protect[$nm])) return $this->protect[$nm];
	}
	public function setHead($val) { $this->head=(bool)$val; }
	public function setNodeName($val) { 
		$val=preg_replace('/\W/','',$val);
		if($val) $this->nodeName=(bool)$val; 
	}
	public function setIsCode($val) { $this->isCode=(bool)$val; }
	public function setComment($val) { $this->comment=$val; }
	public function setFile($val) { $this->file=preg_replace('/\.\w+$/','',$val).'.xml'; }
	public function setVersion($val) { 
		$val=(float)$val;
		if($val) $this->version=$val; 
	}
	public function makeHead() {
		if ($this->head) {
			@header ("expires: Mon, 26 Jul 1990 05:00:00 GMT");
			@header ("last-modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			@header ("cache-control: private");
			@header ("pragma: no-cache");
			@header ("Content-Type: application/xml;charset={$this->charset}");
			if ($this->file) @header("Content-Disposition: attachment; filename=\"{$this->file}\"");
			return "<?xml version=\"{$this->version}\" encoding=\"{$this->charset}\"?>{$this->ln}";
		}
		return '';
	}
	/**
	 * Retorna o XML formatado com seus filhos e atributos
	 *
	 * @param boolean $root se true instancia o header XML
	 * @return string
	 */
	public function __tostring(){
		$out=$this->makeHead();
		if($this->comment) $out.=$this->comment($this->comment);
		$out.="<{$this->nodeName}";
		foreach($this->att as $k=>$v) $out.=" $k=\"{$this->text($v)}\"";
		if ($this->child) {
			$out.=">{$this->ln}";
			$this->child=(array)$this->child;
			foreach($this->child as $v) {
				if (is_object($v)) {
					if(preg_match('/^Xml(Sql)?$/',get_class($v))) {
						$v->head=false;
						$out.=$v->__tostring();
					} else $out.=$this->code($v->__tostring());
				}else $out.=$this->isCode?$this->code($v):$this->text($v);
			}
			$out.="</{$this->nodeName}>{$this->ln}";
		} else $out.=" />{$this->ln}";
		return $out;
	}
	/**
	 * Retorna um coment�rio formatado
	 * @param string $text
	 * @return string
	 */
	public function comment($text) { return "<!--{$this->text($text)}-->{$this->ln}";}
	/**
	 * Retorna um texto codificado para XML
	 * @param string $text
	 * @return string
	 */
	public function text($text){ return htmlspecialchars($text,ENT_QUOTES); }
	/**
	 * Retorna um c�digo formatado para XML
	 * @param string $text
	 * @return string
	 */
	public function code($text){ return "<![CDATA[$text]]>"; }
}
