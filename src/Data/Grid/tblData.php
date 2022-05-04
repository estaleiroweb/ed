<?php

namespace EstaleiroWeb\ED\Data\Grid;

use EstaleiroWeb\ED\Data\Form\Form;
use EstaleiroWeb\ED\IO\SessControl;
use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\ED\Secure\Secure;
use EstaleiroWeb\ED\Tools\Id;
use Evoice\Tools\Refresh;

/**
* @author Helbert Fernandes <helbertfernandes@gmail.com>
* @description Conjunto de classes do tblData para manipulação de conjunto de dados
**/
//$_SESSION=[];
class tblData implements DataInterface {
	static private $labled=false;
	static protected $toDo=true;
	static protected $outObj=[];
	
	const LF="\n";
	const TAB="\t";

	public $oSess;
	protected $baseId='Data';
	protected $varSess=[];
	
	protected $outFormat;
	public $oDad;
	public $active=[];
	protected $readonly=[
		'id'                => null,   //Id do Objeto [obrigatório]
		'idHtml'            => null,   //Id do Objeto em html [obrigatório]
		'idObj'             => null,   //Id automático do Objeto
		'label'             => null,   //Label do objeto
		'recordCount'       => null,   //Número de registros
	];
	protected $protect=[
		'conn'              => null,   //Conexão com o banco de dados
		'style'             => [],//array('field'=>$call_back_function)
		'container'         => null,
	];
	protected $trackerFields=[  
		//Formato String
		'view'              => null,   //SQL ou nome da View [obrigatório]
		'db'                => null,   //Base de dados source
		'key'               => null,   //Chave simples ou composta (separada por vírgula) de target da urls [opcional]
		'groupFields'       => null,   //lista de campos para amostra o grupo com , count, max, miv, avg, etc SELECT <value>
		'group'             => null,   //lista de campos para o grupo GROUP BY <value>
		'url'               => null,
		'urlNew'            => null,
		'urlEdit'           => null,
		'urlNav'            => null,
		'urlDel'            => null,
		'urlClone'          => null,
		'tabActived'        => null,
		'handleLine'        => null,   //string|array $atributos_tr=call_back_function($this);
		'handleCell'        => null,   //array('field'=>$call_back_function)
		'order'             => null,   //string lista de campos do order by
		
		//Formato Boleano
		'noBrHead'          => true,   //bollean adiciona <nobr> no cabeçalho
		'noBrBody'          => true,   //bollean adiciona <nobr> na célula
		'showLabel'         => true,   //mostra ou não o H1 na página
		'showTable'         => true,   //Mostra ou não a tabela
		'showHead'          => true,   //Mostra ou não o cabeçalho de Filtros
		'showFilter'        => true,   //Exibe ou não a tarja de campos com filtro
		'showRecCount'      => true,   //Exibe ou não o número total de registros
		'showNavBars'       => true,   //Exibe ou não o número de linhas e a barra de navegação a serem mostradas (caso false exibirá todas as linhas)
		'showNavBarsNum'    => true,   //Exibe ou não o número de linhas e a barra de navegação numéricas a serem mostradas (caso false exibirá todas as linhas)
		'showQuantLines'    => true,   //Exibe ou não o box seleção quantidade de linhas por página
		'forceShowLabel'    => false,  //Força mostrar o label quando sub-call
		'refresh'           => false,  //Habilita o refresh se true. Faz um refresh automático se numérico>0.
		'saveSession'       => true,   //Habilita o cache de sessão
		
		//Formato Numérico
		'page'              => 1,      //number Numero da página ativa
		'lines'             => 10,     //number Numero de linhas a serem mostradas
		'quantNavBarsNum'   => 5,      //number Quantidade de números na barra de navegação
		'widthField'        => 32,     //number Largura default dos campos
		'maxHeightLine'     => 45,     //number Altura máxima dos campos
		'limit'             => false,  //bollean|number LIMIT da consulta. false não há limit

		'quantLinesOptions' => [10,20,50,100,null], //array combobox de opções de quantidade de linhas. 0=All, null=Conf
		'values'            => [],  //array Valores default dos filtros array('campo1'=>'value','campo2'=>'value','campo3'=>'value')
		'hiddenFields'      => [],  //array Campos a serem ocultos
		'lock'              => [],  //array bloqueia filtros dos campos com funções
		'noCut'             => [],  //array Não usar elipisis nos campos descritos
		'format'            => [],  //array Formato dos campos array('campo1'=>'value','campo2'=>'value','campo3'=>'value')
		'width'             => [],  //array Largura dos campos array('campo1'=>'value','campo2'=>'value','campo3'=>'value')
		'fn'                => [],  //array Executa a função call_user_func_array para os campos descritos; array('campo1'=>'call_back_function','campo2'=>'call_back_function','campo3'=>'call_back_function'); $value=call_back_function($value,$field,$this->row,$this);
		'events'            => [],  //array Eventos do <TR> onde array('onclick'=>'function()','onHover'=>'function()',...)
		'typeFields'        => [],  //array Tipos dos campos array('campo1'=>'value','campo2'=>'value','campo3'=>'value')
		'typeNumFields'     => [],  //array Tipos dos campos Numerico array('campo1'=>'value','campo2'=>'value','campo3'=>'value')
		'fields'            => [],  //array Nomes dos campos array('campo1'=>'value','campo2'=>'value','campo3'=>'value')
		'lstFields'         => null,     //array|string Lista de campos a serem mostrados array('campo1'=>'value','campo2'=>'value','campo3'=>'value')
		
	];
	public $classIcons=[
		'btn'=>            'btn',
		'input-group'=>    'input-group',
		'input-group-btn'=>'input-group-btn',
		'asc'=>            'glyphicon glyphicon-arrow-down',  //-chevron-down -download -circle-arrow-down -sort-by-alphabet
		'desc'=>           'glyphicon glyphicon-arrow-up',    //-chevron-up   -upload   -circle-arrow-up   -sort-by-alphabet-alt
		'plus'=>           'glyphicon glyphicon-plus',
		'next'=>           'glyphicon glyphicon-forward',
		'previous'=>       'glyphicon glyphicon-backward',
		'first'=>          'glyphicon glyphicon-fast-backward',
		'last'=>           'glyphicon glyphicon-fast-forward',
		'filter'=>         'glyphicon glyphicon-filter',
		'sort'=>           'glyphicon glyphicon-sort-by-alphabet',
		'showHide'=>       'glyphicon glyphicon-eye-open',
		'showHideFilter'=> 'glyphicon glyphicon-eye-open',
		'menu'=>           'glyphicon glyphicon-align-justify',    //-asterisk -align-justify -tasks
		'copyTable'=>      'glyphicon glyphicon-th',
		'copyFieldsTable'=>'glyphicon glyphicon-th-list',
		'copyFields'=>     'glyphicon glyphicon-tag',
		'copyURL'=>        'glyphicon glyphicon-star',
		'exportExcel'=>    'glyphicon glyphicon-export',
		'exportExcelAll'=> 'glyphicon glyphicon-export',
		'exportCSV'=>      'glyphicon glyphicon-export',
		'reset'=>          'glyphicon glyphicon-refresh',
		'help'=>           'glyphicon glyphicon-question-sign',
	];
	public $language=[
		'pageTextBox'=>'<page>/<pagecount>',
		'quantLinesAll'=>'All',
		'quantLinesConf'=>'Specify',
		'quantLinesDef'=>'<num> Records',
		'quantLinesButton'=>'Shows <num> <span class="caret"></span>',
		'records'=>'<div style="height: 35px; padding-top:6px;" title="<startRecord>~<endRecord>"><reccount> record<num></div>',
		'status'=>'<div style="height: 35px; padding-top:6px;" class="text-info"></div>',
		//'records'=>'<span class="label label-default"><reccount> record<num></span> <span class="badge"><startRecord>~<endRecord></span>',
		'topBar'=>"
			<div class='row'>
				<div class='col-xs-8 col-sm-3 col-md-5 col-lg-6'>
				<div class='btn-group' role='group' aria-label='buttons'>
					<buttonMenu><buttonNew><buttonSort><buttonShowHide><buttonFilter>
				</div>
				</div>
				<div class='col-xs-4 col-sm-3 col-md-3 col-lg-2 text-right'>
					<records>
				</div>
				<div class='col-xs-4 col-sm-2 col-md-2 col-lg-1'>
					<quantLines>
				</div>
				<div class='col-xs-8 col-sm-4 col-md-4 col-lg-3'>
					<navBar>
				</div>
			</div>",
		'bottomBar'=>"
			<div class='row'>
				<div class='col-xs-4  col-sm-4 col-md-6 col-lg-6'>
					<status>
				</div>
				<div class='col-xs-8  col-sm-8 col-md-6 col-lg-6 text-right'>
					<pagination>
				</div>
			</div>",
	];

	//Execução automática
	public function __construct($id=null){
		if(!self::$toDo) return;
		$oId=Id::singleton();
		$this->idObj=$oId->id;
		if (is_null($id) || $id=='') $id=$this->idObj;
		$this->id=$id;
		$this->label=preg_replace('/\s*#.*$/','',$id);
		$this->readonly['idHtml']=$this->buildIdHtml($id);
		
		//MediatorPHPJS::singleton(); /*Retirado 2019-07-09*/
		//Captura a sessao do objeto
		$this->oSess=SessControl::singleton(get_class($this).':'.$this->id);
		$this->varSess=(array)$this->oSess->get();
		//if(@$GLOBALS['doido']) show($this->varSess);
		//if(@$GLOBALS['doido']) show([get_class($this)=>$this->varSess]);
		//$this->varSess=(array)$this->oSess->active;
		//show($this->varSess);
	}
	/**
	 * Retorna o valor da variavel sobrecarregada
	 *
	 * @param string $nm Nome da Variavel
	 * @return mixed
	 */
	public function __get($nm){
		/*if(@$GLOBALS['doido'] && $nm=='values') show([get_class($this)=>[
			'active'=>$this->isVal($nm,$this->active),
			'readonly'=>$this->isVal($nm,$this->readonly),
			'protect'=>$this->isVal($nm,$this->protect),
			'method'=>method_exists($this,$fn='get'.ucfirst($nm)),
			'trackerFields'=>array_key_exists($nm,$this->trackerFields),
		]]);*/
		//if(Secure::$idUser==2);
		if($nm=='') return;
		if($this->isVal($nm,$this->active))                 return $this->active[$nm];
		if($this->isVal($nm,$this->readonly))               return $this->readonly[$nm];
		if($this->isVal($nm,$this->protect))                return $this->protect[$nm];
		if(method_exists($this,$fn='get'.ucfirst($nm)))     {
			$value=$this->$fn();
			if(array_key_exists($nm,$this->trackerFields)) return $this->active[$nm]=$value;
			if(array_key_exists($nm,$this->protect))       return $this->protect[$nm]=$value;
			return $this->readonly[$nm]=$value;
		}
		if(!array_key_exists($nm,$this->trackerFields)) return;
		$value=$this->buildTrack($nm,$this->trackerFields[$nm]);
		if(is_null($value)) return;
		return $this->active[$nm]=method_exists($this,$fn='set'.ucfirst($nm))?$this->$fn($value):$value;
	}
	/**
	 * Sobrecarrega as variaveis
	 *
	 * @param string $nm Nome da Variavel
	 * @param mixed $val Valor
	 */
	public function __set($nm,$value){
		if($nm=='') return;
		$method=method_exists($this,$fn='set'.ucfirst($nm));

		if(array_key_exists($nm,$this->readonly)) {
			if($this->isInternalParentCalled()) return $this->readonly[$nm]=$method?$this->$fn($value):$value;
			die("Argumento privado: $nm");
		}

		if(array_key_exists($nm,$this->protect)) {
			$value=$this->protect[$nm]=$method?$this->$fn($value):$value;
			if(array_key_exists($nm,$this->trackerFields)) $this->active[$nm]=$value;
			return $value;
		}
		if(!(array_key_exists($nm,$this->trackerFields) || $method)) return $this->protect[$nm]=$value;
		$value=$this->buildTrackRequest($nm,$value);
		return $this->active[$nm]=$this->protect[$nm]=$method?$this->$fn($value):$value;
	}
	public function __set_old($nm,$value){
		if($nm=='') return;
		$method=method_exists($this,$fn='set'.ucfirst($nm));

		if(array_key_exists($nm,$this->readonly)) {
			if($this->isInternalParentCalled()) return $this->readonly[$nm]=$method?$this->$fn($value):$value;
			die("Argumento privado: $nm");
		}
		if(array_key_exists($nm,$this->protect)) return $this->protect[$nm]=$method?$this->$fn($value):$value;
		if(!(array_key_exists($nm,$this->trackerFields) || $method)) return $this->protect[$nm]=$value;
		$value=$this->buildTrackRequest($nm,$value);
		return $this->active[$nm]=$method?$this->$fn($value):$value;
	}
	public function __invoke(array $args){
		foreach($args as $k=>$v) $this->$k=$v;
		return $this;
	}
	
	protected function setKey($value)          { return $this->trArray2String($value); }
	protected function setGroupFields($value)  { return $this->trArray2String($value); }
	protected function setGroup($value)        { return $this->trArray2String($value); }
	protected function setOrder($value)        { return $this->trArray2String($value); }
	protected function setLstFields($value)    { return $this->trString2Array($value); }
	protected function setHiddenFields($value) { return $this->trString2Array($value); }
	protected function setLock($value)         { return $this->trString2Array($value); }
	protected function setNoCut($value)        { return $this->trString2Array($value); }
	protected function setFields($value)       { return $this->trJSON2Array($value); }
	protected function setWidth($value)        { return $this->trJSON2Array($value); }
	protected function setFunction($value)     { return $this->fn=$value; }
	protected function setDataBase($value)     { return $this->db=$value; }
	
	protected function getFunction()           { return $this->fn; }
	protected function getDataBase()           { return $this->db; }
	protected function getLstFields()          { return ($value=$this->buildTrack('lstFields'))?$this->setLstFields($value):array_keys($this->fields); }
	protected function getConn()               { return $this->buildTrack('conn'); }
	
	protected function verifyBasicActive(){
		foreach ($this->trackerFields as $item=>$sub) if(!array_key_exists($item,$this->active)) $tmp=$this->$item;
	}
	protected function isVal($nm,$array){
		$out=array_key_exists($nm,$array) && !is_null($array[$nm]);
		//show([$nm,$array,array_key_exists($nm,$array)]);
		return $out;
	}
	protected function trJSON2Array($value){ return is_array($value)?$value:(array)json_decode($value); }
	protected function trString2Array($value){ return is_array($value)?$value:preg_split('/\s*[,;]\s*/',$value); }
	protected function trString2ArrayStyle($style){
		if(is_array($style)) return $style;
		$out=[];
		if(is_string($style)) {
			$style=preg_split('/\s*;\s*/',$style);
			foreach($style as $v)if(preg_match('/^([^:]+?)\s*:\s*(\s+?|.+)$/',trim($v),$ret)) $out[$ret[1]]=$ret[2];
		}
		return $out; 
	}
	protected function trArray2String($value){
		if(is_string($value)) return $value;
		if(is_object($value)) $value=(array)$value;
		if(is_array($value)) return '`'.implode('`,`',$value).'`';
		return (string)$value;
	}
	protected function trArray2StringStyle($style){
		if(!$style || !is_array($style)) return null;
		foreach($style as $k=>$v) $style[$k]=$k.':'.$v.';';
		return implode(' ',$style); 
	}
	protected function isInternalParentCalled(){
		$bt=debug_backtrace();
		$bt=@$bt[1];
		//show([is_object(@$bt['object']),$bt]);
		return is_object(@$bt['object']) && $bt['object'] instanceof DataInterface;
	}
	protected function requestData($nm,$value=null){
		if(@$_REQUEST[$this->baseId] && @$_REQUEST[$this->baseId][$this->id] && array_key_exists($nm,$_REQUEST[$this->baseId][$this->id])) {
			return $_REQUEST[$this->baseId][$this->id][$nm];
		}
		return $value;
	}
	protected function buildTrack($nm,$default=null){
		//if(@$GLOBALS['doido'] && $nm=='values') show(get_class($this));
		if(!is_null($val=$this->buildTrackRequest($nm))) return $val;
		//if(@$GLOBALS['doido'] && $nm=='values') show(get_class($this));
		if(!is_null($val=$this->buildTrackDad($nm))) return $val;
		//if(@$GLOBALS['doido'] && $nm=='values') show(get_class($this));
		return $this->buildTrackHistory($nm,$default);
	}
	protected function buildTrackRequest($nm,$default=null){
		if(!is_null($val=$this->requestData($nm))) return $val;

		if(array_key_exists($nm,$this->varSess)) return $this->varSess[$nm];
		return $default;
	}
	protected function buildTrackHistory($nm,$default=null){
		/*$class=$this->buildHistory();

		foreach($class as $obj) if(!is_null($val=@$obj->$nm) && @$obj->idObj!=$this->idObj) {
			//if(@$GLOBALS['doido'] && $nm=='showLabel') var_dump($obj->$nm);
			//if($nm=='values') show(get_class($obj));
			//show(array($this->idObj,$obj->idObj));
			return $val;
		}
		*/
		if(array_key_exists($nm,$GLOBALS)) return $GLOBALS[$nm];
		return $default;
	}
	protected function buildTrackDad($nm,$default=null){
		if(!is_null($val=@$this->oDad->$nm)) return $val;
		return $default;
	}
	protected function buildHistory(){
		$class=[];
		$bt=debug_backtrace();
		foreach($bt as $line) if(
			array_key_exists('class',$line)    && $line['class']  &&
			array_key_exists('object',$line)   && $line['object'] && $line['object']!==$this && $line['object'] instanceof DataInterface
		) $class[$line['class']]=$line['object'];
		return $class;
	}
	protected function addForm(){
		OutHtml::singleton()->addFormTag();
	}
	protected function saveSession(){
		//if($this->saveSession) $this->oSess->active=$this->active;
		if($this->saveSession) $this->oSess->set($this->active);
	}
	protected function htmlHead(){
		$outHtml=OutHtml::singleton();
		if ($this->refresh)	{
			$rfr=Refresh::singleton();
			$outHtml->script['refresh']="fRfr(".($this->refresh===true?0:(int)$this->refresh).")";
		}

		if($this->outObj('label')) {
			$outHtml->title($this->label,$this->showLabel);
		}elseif($this->forceShowLabel) return "<h2>{$this->label}</h2>\n";
		return '';
	}
	protected function outObj($id){
		if(@self::$outObj[$id]) return false;
		return self::$outObj[$id]=true;
	}
	protected function buildIdHtml($id){
		return htmlspecialchars($id,ENT_QUOTES);
	}
	protected function buildNameById($id){
		$id=preg_replace(
			array('/([a-z0-9])([A-Z])/','/([a-z])(\d)/','/[ _]+/',),
			array('\1 \2',              '\1 \2',        ' ',      ),
			$id
		);
		return trim($id);
	}
	protected function buildFileName($nome){
		return strtr(
			strtr(
				$nome,
				'ÀÁÂÃÄÅÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜàáâãäåèéêëìíîïðòóôõöùúûüÇÐÑ×ØÝÞßçñ÷øýþÿ!"#$%&\'+,/:;<=>?@\^`|~ ¡¢£¤¥¦§¨©ª«¬­®¯°±²³´µ¶·¸¹º»¿ ','AAAAAAEEEEIIIIOOOOOUUUUaaaaaaeeeeiiiioooooouuuuCDNX0YDBcn-0ydy*************-*********icLoY*S*Ca***R_o*23*u***1o***'
			),
			array('Æ'=>'AE','æ'=>'ae','¼'=>'1_4','½'=>'1_2','¾'=>'3_4','*'=>'')
		);
	}
	protected function buildIcon($id){
		return "<span class='{$this->classIcons[$id]}' aria-hidden='true'></span>";
	}
	protected function checkUrl($url,$permition='R'){
		if($url=='') return false;
		$s=Secure::$obj;
		return $s?call_user_func('Secure::can_'.$permition,$s->permitionFile($url)):true;
	}
	public function unit2em($value){
		if(!preg_match('/\s*(\d+(?:\.\d+)?)\s*(%|p[xtc]]|[cm]m|in|r?em|ex|ch|v[hw]|vmin|vmax)?/i',$value,$ret)) return null;
		$width=$ret[1]+0;
		$un=strtolower(@$ret[2]);
		if(!$un || $un=='em' || $un=='rem') return $width;
		if(preg_match('/(%|p[tc]]|ex|ch|v[hw]|vmin|vmax)/',$un)) return null;
		
		if($un=='pt') $width*=0.1;          /*point*/
		elseif($un=='mm') $width*=0.284527; /*millimeter*/
		elseif($un=='cm') $width*=2.84527;  /*centimeter*/
		elseif($un=='ex') $width*=0.43055;  /*ex*/
		elseif($un=='pc') $width*=1.2;      /*picas*/
		elseif($un=='in') $width*=7.22699;  /*inches*/
		elseif($un=='bp') $width*=0.10037;  /*bp*/
		elseif($un=='dd') $width*=0.107;    /*dd*/
		return $width;
	}
	public function frm($preStartMethods=null,$preStartAttrs=null,$force=false){
		return Form::byData($this,$preStartMethods,$preStartAttrs,$force);
	}
}