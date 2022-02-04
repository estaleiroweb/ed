<?php
require_once 'checkReferer.php';
require_once 'get_autoload.php';

class exe{
	private $ret=array();
	public $dataType='json';
	public $method='_POST';
	public $ajax;
	
	function __construct(){
		if(array_key_exists('ajax',$_REQUEST)) {
			$this->ajax=$_REQUEST['ajax'];
			unset($_REQUEST['ajax']);
			if(array_key_exists('dataType',$this->ajax) && array_key_exists($dataType=$this->ajax['dataType'],MimeType::$content_types)) $this->dataType=$dataType;
			//if(array_key_exists('type',$this->ajax)) $this->method='_'.$this->ajax['type'];
		}
		header('Content-Type: '.MimeType::$content_types[$this->dataType]);
		if(array_key_exists($k='cmd',$_REQUEST)){
			$cmd='cmd_'.$_REQUEST[$k];
			unset($_REQUEST[$k]);
			if(array_key_exists($k,$_POST)) unset($_POST[$k]);
			if(array_key_exists($k,$_GET)) unset($_GET[$k]);
			if(!method_exists($this,$cmd)) $cmd='cmd_session';
		} else $cmd='cmd_session';
		call_user_func(array($this,$cmd));
	}
	function __toString(){
		$cmd='show_'.$this->dataType;
		if(!method_exists($this,$cmd)) $cmd='show_text';
		return call_user_func(array($this,$cmd));
	}
	
	private function cmd_session(){
		foreach($_REQUEST as $k=>$v) $_SESSION[$k]=$v;
		$this->ret=$_REQUEST;
	}
	private function cmd_eval(){
		if(array_key_exists($k='__eval',$_REQUEST)) {
			$__eval=$_REQUEST[$k]; unset($_REQUEST[$k]);
			if(array_key_exists($k,$_POST)) unset($_POST[$k]);
			if(array_key_exists($k,$_GET)) unset($_GET[$k]);
			
			if($data=@$_REQUEST['data']) {
				unset($_REQUEST['data']);
				$_REQUEST=array_merge($data,$_REQUEST);
				$GLOBALS[$this->method]=array_merge($data,$GLOBALS[$this->method]);
			}
			
			if(is_array($__eval)) foreach($__eval as $v) $this->ret[]=@eval($v);
			else $this->ret=@eval($__eval);
			//if(is_array($__eval)) foreach($__eval as $v) $this->ret[]=$this->fnEval($v);
			//else $this->ret=$this->fnEval($__eval);
		}
		/*
		$code = 'var_dump(isset($this));';

		$evUl = function()use($code){ eval($code); };
		$evUl = $evUl->bindTo(null);
		$evUl();
		*/
	}
	private function fnEval($val){
		$GLOBALS['this']=&$this;
		$val=preg_replace('/\$this/','$GLOBALS[\'this\']',$val);
		return @eval($val);
	}
	private function cmd_makeLink(){
		if(!array_key_exists('url',$_REQUEST)) return;
		$conn=Conn::dsn();
		$url=$conn->addQuote($_REQUEST['url']);
		$data=$conn->addQuote(@$_REQUEST['data']);
		$this->ret=$conn->fastValue("CALL db_Secure.pc_URL_create($url,$data,{$this->get_idUser()})");
	}
	private function cmd_SessControl(){
		if(!array_key_exists('id',$_REQUEST) || !array_key_exists('idFile',$_REQUEST)) return;
		
		$oSess=SessControl::singleton($_REQUEST['id'],$_REQUEST['idFile']);
		$oSess->set((array)@$_REQUEST['data']);
		$this->ret=$oSess->get();
		//session_start(); $this->ret=session_id();
		/*$this->ret=[
			'id'=>$_REQUEST['id'],
			'idFile'=>$_REQUEST['idFile'],
			'get'=>$oSess->get(),
			'sess'=>$_SESSION,
		];*/
	}
	private function show_json(){ return json_encode($this->ret); }
	private function show_text(){ return is_string($this->ret)?$this->ret:$this->show_json(); } // implode("\n",$this->ret)
	private function show_html(){ return is_string($this->ret)?$this->ret:$this->show_json(); } // implode("\n",$this->ret)
	private function show_xml(){
		/*{$books = array(
			'@attributes' => array(
				'@type' => 'fiction',
				'@value' => '$18.00',
			),
			'@cust' => '$18.00',
			'book' => array(
				array(
					'@attributes' => array(
						'author' => 'George Orwell'
					),
					'title' => '1984'
				),
				array(
					'@attributes' => array(
						'author' => 'Isaac Asimov'
					),
					'title' => 'Foundation',
					'price' => '$15.61'
				),
				array(
					'@attributes' => array(
						'author' => 'Robert A Heinlein'
					),
					'title' => 'Stranger in a Strange Land',
					'price' => array(
						'@attributes' => array(
							'discount' => '10%'
						),
						'@value' => '$18.00'
					)
				)
			)
		);}*/
		$xml=$this->array2XML($this->ret);
		return $xml->asXML();
	}
	private function array2XML($data, &$xml=null) {
		/*{$books = array(
			'@attributes' => array(
				'@type' => 'fiction',
				'@value' => '$18.00',
			),
			'@cust' => '$18.00',
			'book' => array(
				array(
					'@attributes' => array(
						'author' => 'George Orwell'
					),
					'title' => '1984'
				),
				array(
					'@attributes' => array(
						'author' => 'Isaac Asimov'
					),
					'title' => 'Foundation',
					'price' => '$15.61'
				),
				array(
					'@attributes' => array(
						'author' => 'Robert A Heinlein'
					),
					'title' => 'Stranger in a Strange Land',
					'price' => array(
						'@attributes' => array(
							'discount' => '10%'
						),
						'@value' => '$18.00'
					)
				)
			)
		);}*/
		if(is_null($xml)) $xml=new SimpleXMLElement('<root/>');
		if(is_string($data)) $xml->addChild(null,$data);
		else foreach($data as $tag=>$context) {
			if($tag==='@attributes' && is_array($context)){
				$i=0;
				foreach($context as $attr=>$value){
					if("$attr"==="$i"){ $attr=$value;$value=null;$i++; }
					$xml->addAttribute($attr,(string)$value);
				}
			} 
			elseif($tag[0]==='@') $xml->addAttribute(substr($tag,1),(string)$context);
			else {
				$v=is_array($context)?null:htmlspecialchars("$context");
				if(is_numeric($tag)) $subnode=$xml->addChild('item',$v)->addAttribute('id',$tag);
				else $subnode=$xml->addChild($tag,$v);
				if(is_array($context)) $this->array2XML($context, $subnode);
			}
		}
		return $xml;
	}
	private function get_idUser(){
		$oSess=SessControl::singleton('Secure','main');
		return $oSess->idUser+0;
	}
}
print new exe;
