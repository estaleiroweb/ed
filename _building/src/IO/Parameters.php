<?php
class Parameters {
	public $debug=false;
	public $superDebug=false;
	public $help='';
	public $parameters=array(
		'[...]'=>array('tip'=>'Restantes dos parametros','cmd'=>'$this->example($cmd,$attr)',),
		'[-h|--help]'=>array('tip'=>'Mostra essa Ajuda','cmd'=>'$this->help()',),
		'[-d|--debub=[0|1|true|false|on|off|verdadeiro|falso|ligado|desligado]]'=>array('tip'=>'Ativa/Desativa o debug','cmd'=>'$this->setDebug($attr)',),
		'[-sd|--superDebug=[0|1|true|false|on|off|verdadeiro|falso|ligado|desligado]]'=>array('tip'=>'Ativa/Desativa o Super Debug','cmd'=>'$this->setSuperDebug($attr)',),
		/*
		$cmd=Nome do Atributo
		$attr=valor do Atributo
		'<-e|--example=<valor>>'=>array('tip'=>'Exemplo com paramtetro','cmd'=>'$this->example($attr)',),
		'[-xx|--xxxxx=[0|1|true|false|on|off|verdadeiro|falso|ligado|desligado]]'=>array('tip'=>'Ativa/Desativa xxxx','cmd'=>'$this->setOnOff("x",$attr)',),
		'example1'=>array('tip'=>'Exemplo1','cmd'=>'$this->example($cmd,$attr)',),
		'<example2>'=>array('tip'=>'Exemplo1','cmd'=>'$this->example($cmd,$attr)',),
		'[example3]'=>array('tip'=>'Exemplo1','cmd'=>'$this->example()',),
		*/
	);
	
	function __construct($parametros=null,$noRest=false){
		if($noRest) {
			if(is_string($noRest)) $noRest=preg_split('/\s*[,;]\s*/',$noRest);
			if(!is_array($noRest)) $noRest=array('[...]');
			foreach($noRest as $k) if(isset($this->parameters[$k])) unset($this->parameters[$k]);
		}
		if($parametros && is_array($parametros)) $this->parameters=array_merge($this->parameters,$parametros);
		$erTot=array();
		$er=array();
		foreach($this->parameters as $k=>&$v) {
			if(!isset($v['tip'])) $v['tip']='';
			if(!isset($v['cmd'])) $v['cmd']='';
			$v['required']=!preg_match('/^\[.*\]$/',$k);
			$v['eReg']=preg_replace(array('/^\[(.*)]/','/^\<(.*)>/',),'\1',$k);
			$v['attr']=(bool)preg_match('/\$attr\b/',$v['cmd']);
			$v['attrValue']='';
			$v['attrRequired']=false;
			$v['attrEReg']='.+';
			$eregAtt='=((\[|<)?([^\]>]+)(\]|>)?)';
			if(preg_match("/$eregAtt/",$v['eReg'],$ret)) {
				$v['attrValue']=$ret[1];
				if(preg_match('/\|/',$ret[3])) $v['attrEReg']=$ret[3];
				if($ret[2]!='[') $v['attrRequired']=true;
				else $v['attrEReg']="{$v['attrEReg']}";
			}elseif($v['attr']) $v['attrValue']='[valor]';
			$v['eReg']=preg_replace(
				"/^(.+?)($eregAtt)?$/",
				'(\1'.($v['attrValue']?($v['attrRequired']?"=({$v['attrEReg']}))":")(?:=({$v['attrEReg']}))?"):')'),
				$v['eReg']
			);
			if(!preg_match('/-/',$v['eReg'])) $v['eReg']='(.+)';
			$v['values']=array();
			if($v['eReg']!='(.+)')  $erTot[]=$v['eReg'];
		}
		$erTot=implode('|',$erTot);
		if(isset($this->parameters['[...]'])) {
			$temp=$this->parameters['[...]'];
			unset($this->parameters['[...]']);
			$this->parameters['[...]']=$temp;
		}
		if (@$GLOBALS['argv']) {
			$erro=array();
			$atts=array_slice(@$GLOBALS['argv'],1);
			foreach($this->parameters as $k=>&$v) {
				$er="/^{$v['eReg']}$/";
				while(($ret=preg_grep($er,$atts))) {
					reset($ret);
					$key=key($ret);
					preg_match($er,$atts[$key],$ret);
					if(isset($ret[2])) $ret[1]=str_replace("={$ret[2]}",'',$ret[1]);
					$cmd=$ret[1];
					$attr='';
					unset($atts[$key]);
					if($k=='[...]') {
						$attr=$cmd;
						$cmd='...';
					} else {
						if(isset($ret[2])) $attr=$ret[2];
						elseif($v['attrValue']) {
							$key++;
							//if(isset($atts[$key]) && preg_match("/^({$v['attrEReg']})$/i",$atts[$key]) && $atts[$key]{0}!='-') {
							if(isset($atts[$key]) && preg_match("/^({$v['attrEReg']})$/i",$atts[$key]) && !preg_match("/^{$erTot}$/",$atts[$key])) {
								$attr=$atts[$key];
								unset($atts[$key]);
							} elseif($v['attrRequired']) $erro[]="Atributo {$k} requerido";
						}
						if($v['eReg']=='(.+)') $er='/^\n$/';
					}
					$v['values'][$attr]=$cmd;
					//$atts=array_values($atts);
				}
				if($v['required'] && !$v['values']) $erro[]="Parametro {$k} requerido";
			}
					
			if($erro) {
				print "ERROS:\n".implode("\n",$erro)."\n\n";
				$this->help();
			}
			foreach($this->parameters as $k=>&$v) if(@$v['cmd']) {
				foreach($v['values'] as $attr=>$cmd) $v['values'][$attr]=$this->__eval($v['cmd'],$cmd,$attr);
			}
		} else {
			$ret=$_REQUEST;
		}
	}
	private function __eval($eval,$cmd,$attr){ 
		return eval("return $eval;"); 
	}
	public function setDebug($debug=false){
		return $this->setOnOff('debug',$debug);
	}
	public function setSuperDebug($debug=false){
		return $this->setOnOff('superDebug',$debug);
	}
	public function setOnOff($var,$value){
		return ($this->$var=$value==='' || preg_match('/^(on|true|verdadeiro|ligado)$/i',$value)?true:(bool)$value);
	}
	function pr($text){ if ($this->debug) print_r($text); }
	function prs($text){ if ($this->superDebug) print_r($text); }
	public function help($space=20){
		$sintaxe=@$GLOBALS['argv']?$GLOBALS['argv'][0]:'';
		$tips=array();
		foreach($this->parameters as $k=>$v) {
			$sintaxe.=" $k";
			$intro="$k: ";
			$intro=strlen($intro)>$space?"$intro\n".str_repeat(' ',$space):str_pad($intro,$space);
			$tips[]=$intro.$v['tip'].($v['required']?'':' [OPCIONAL]');
		}
		$help=$this->help?"{$this->help}\n":'';
		die("{$help}Sintaxe:\n$sintaxe\n".implode("\n",$tips)."\n");
	}
	protected function example($cmd=null,$attr=null){ return array('Retorno do metodo example','cmd'=>$cmd,'attr'=>$attr); }
}