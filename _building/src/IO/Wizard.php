<?php
/**
 *  @file Wizard.php
 *  @version 1.0
 *  @brief Uma classe que permite uma implementação fácil de um Wizard 
 *  
 *  @details More details
 *  @example shows the code of a specified example file or, optionally, just a portion of it.
 *      //Exemplo Implementações
 *      public function start(){//opcional
 *          $this->help(); //Tudo que pode ser usado
 *          $this->fields=array(
 *              'field_name1'=>'json ElementX',
 *              'field_nameN'=>ElementX,
 *          );
 *      }
 *      //Cada Passo Sequencial do Wizard
 *      public function step_0();
 *      public function step_1();
 *      public function step_N();
 *  
 *      //Exemplo de implementação de um step
 *      public function step_0(){
 *          $this->help(); //Tudo que pode ser usado
 *          return false; //Caso erro;
 *      }
 *      public function __invoke(){//Quando concluir
 *          $this->help(); //Tudo que pode ser usado
 *          return false; //Caso erro;
 *      }
 *  @see indicates a reference from the associated element to a website or other elements.
 *  
 *  @author Helbert Fernandes<helbertfernandes@gmail.com>
 *  @filesource OutHtml.php Secure.php Bootstrap.php Element*.php
 *  @package Main
 */
abstract class Wizard {
	use \Traits\OO\GetterAndSetter;
	
	private $id,$permition=true,$html='';
	protected $oSess,$secure,$minStep,$numSteps,$title,$subTitle;
	protected $fields=array();
	protected $values=array();
	protected $height='300px',$message='';
	protected $showBar=true,$showPg=true,$reset=false;
	protected $btn=array();
	protected $messClass=null;
	protected $messTop=true;
	
	final public function __construct(){
		if( //Secure
			preg_grep('/^Secure$/',get_declared_classes()) && 
			($this->secure=Secure::$obj) &&
			$this->secure->access &&
			$this->secure->access['Nivel'] &&
			!($this->secure->C || $this->secure->U || $this->secure->D)
		) return $this->permition=false;
		{//Start Variables
			new Ed();
			new Bootstrap();
			$oId=Id::singleton();
			$this->id=$id=$oId->id;
			$this->oSess=SessControl::singleton($id);

			if(array_key_exists($id,$_POST) && @$_POST[$id]['step']==='') {
				$this->destroy();
				$v=array();
			} else $v=(array)$this->oSess->get();
			$POST=$v && array_key_exists($id,$_POST)?$_POST:array();
			$this->numSteps=count(preg_grep('/^step_/',get_class_methods($this)));
			$this->readonly=array(
				'exec'=>(bool)@$POST[$id]['exec'],
			);
			if(array_key_exists('values',$v)) {
				$this->values=(array)$v['values'];
				unset($v['values']);
			}
			if(array_key_exists('fields',$v)) {
				$startFields=$v['fields'];
				unset($v['fields']);
			} else $startFields=false;
			$this->protect=$v;
			$oldStep=@$this->protect['oldStep']+0;
			if(!array_key_exists('step',$this->protect)) {$this->protect=array(
				'step'=>0,
				'oldStep'=>null,
				'ok'=>null,
			);}
			//show(array($this->values,$this->protect));
			if(array_key_exists($id,$POST)){
				$this->protect['step']=$step=(int)@$POST[$id]['step']+$this->exec;
				unset($POST[$id]);
				if($oldStep<$step) $this->values[$oldStep]=array_merge($POST,$_FILES);
			}
			$this->minStep=$this->numSteps;
			//show(array($v,$id,$oldStep,$POST));
		}
		{ //Btns
			//btns
			$this->btn[$nm='backward']=new ElementButton('<span class="glyphicon glyphicon-backward" aria-hidden="true"></span> Voltar',null,$nm);
			$this->btn[$nm='forward']=new ElementButton('Avançar <span class="glyphicon glyphicon-forward" aria-hidden="true"></span>',null,$nm);
			$this->btn[$nm='commit']=new ElementButton('<span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Concluir',null,$nm);
			$this->btn[$nm='cancel']=new ElementButton('<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancelar',null,$nm);
			$this->btn[$nm='start']=new ElementButton('<span class="glyphicon glyphicon-repeat" aria-hidden="true"></span> Início',null,$nm);
			$this->btn['commit']->addClass('btn btn-success');
			$this->btn['cancel']->addClass('btn btn-danger');
			foreach($this->btn as $o) {
				$o->addClass('Wizard-Control');
				$o->preIdDisplay=$this->id.'_';
			}

			//$this->btn['backward']=new ElementButton();
			//return "<button id='{$this->id}_$type'$disabled></button>";
			/*
			'backward'=>'default',
			'forward' =>'default',
			'commit'  =>'success',
			*/
		}
		$this->start();
		{//finaliza configuração
			if($this->title){
				$o=OutHtml::singleton();
				if(!$o->title) $o->title($this->title);
			}
			$this->minStep=max(0,min($this->minStep,$this->numSteps));
			
			//rebuild $this->fields
			$setFields=preg_grep($er='/^setField_/',get_class_methods($this));
			$setFields=array_combine(preg_replace($er,'',$setFields),$setFields);
			foreach($this->fields as $name=>$obj) {
				$this->fields[$name]=Element::parser($name,$obj);
				$this->fields[$name]->edit=true;
				$this->fields[$name]->required=true;
				if(array_key_exists($name,$setFields)) {
					$this->fields[$name]=call_user_func(array($this,$setFields[$name]),$this->fields[$name]);
					unset($setFields[$name]);
				}
			}
			foreach($setFields as $name=>$fn) if(method_exists($this,$fn)) $this->fields[$name]=call_user_func(array($this,$fn),@$this->fields[$name]);
			if($startFields) {
				foreach($startFields as $k=>$v) $this->fields[$k]->value=$v;
			}
			$this->values2Fields();
		}
		$this->doStep();
	}
	final public function __destruct(){
		if(!$this->permition) return '';
		$this->protect['oldStep']=$step=$this->protect['step'];
		for($i=0;$i<=$step; $i++) if(array_key_exists($i,$this->values)){
			foreach($this->values[$i] as $name=>$v) if(array_key_exists($name,$this->fields)) {
				$this->values[$i][$name]=$this->fields[$name]->value;
			}
		}
		$this->protect['values']=$this->values;
		//$this->protect['fields']=serialize($this->fields);
		$this->protect['fields']=[];
		foreach($this->fields as $k=>$o) $this->protect['fields'][$k]=$o->value;

		if($this->reset || ($this->exec && $this->ok)) $this->destroy();
		//$this->protect=array();
		$this->oSess->set($this->protect);
	}
	final public function __toString(){
		if(!$this->permition) return '';
		OutHtml::singleton()->script(__CLASS__,'easyData');
		$step=$this->step;
		$numSteps=$this->numSteps;
		$pg=$step+1;
		$footer=true;

		$out="<form id='{$this->id}' method='POST' enctype='multipart/form-data' ed-class='".__CLASS__."'><div class='container'>";
		$out.="<input id='{$this->id}_step'     name='{$this->id}[step]'     class='Wizard-Control' type='hidden' value='{$step}' \>";
		$out.="<input id='{$this->id}_numSteps' name='{$this->id}[numSteps]' class='Wizard-Control' type='hidden' value='{$numSteps}' \>";
		$out.="<input id='{$this->id}_exec'     name='{$this->id}[exec]'     class='Wizard-Control' type='hidden' value='0' \>";
		
		{//Header
			$title=$this->title;
			if(($subTitle=$this->subTitle)) $title.=" <small>$subTitle</small>";
			if($title) $out.="<header class='page-header'><h1>$title</h1></header>";
		}
		$nm='start';
		if($this->exec){
			if($this->ok){
				if(!$this->message) $this->message('Concluído');
				$this->btn=[$nm=>$this->btn[$nm]];
			}
			elseif(!$this->message)  $this->error('Erro',true);
		}else unset($this->btn[$nm]);
		{//Body
			$out.="<div class='container-fluid' style='height: {$this->height}; overflow: auto;'>";
			if($this->messTop) $out.=$this->message.$this->html;
			else $out.=$this->html.$this->message;
			$out.='</div>';
		}
		
		$btns=array(
			'backward' =>!$step,
			'forward'  =>!($numSteps && $pg<$numSteps),
			'commit'   =>$pg<$this->minStep,
		);
		foreach($btns as $k=>$v) if(array_key_exists($k,$this->btn)) $this->btn[$k]->disabled=$v;
		$buttons='<nobr class="pull-right"><div class="btn-group" role="group">'.implode('',$this->btn).'</div></nobr>';
		
		if($this->showPg && !$this->exec){
			$pages="
				<div class='col-md-1  col-xs-2 '><span class='badge'>$pg/{$this->numSteps}</span></div>
				<div class='col-md-11 col-xs-10'>";
		} else $pages="\n					<div class='col-md-12'>";
		if($this->showBar && $numSteps && !$this->exec){
			$barPerc=min(100,round($pg/max(1,$numSteps)*100));
			$progressBar="
			<div class='row'>
				<div class='col-md-12'>
					<div class='progress'>
						<div class='progress-bar' role='progressbar' aria-valuenow='$barPerc' aria-valuemin='0' aria-valuemax='100' style='width: $barPerc%;'>
							$barPerc%
						</div>
					</div>
				</div>
			</div>
			";
		} else $progressBar='';
		{$out.="
		<footer class='page-footer'>
			<div class='row'>$pages
					$buttons
				</div>
			</div>$progressBar
		</footer>";}
		$out.='</div></form>';
		
		return $out;
	}
	final protected function values2Fields(){
		//get values to fields
		$step=$this->step+$this->exec;
		for($i=0;$i<=$step; $i++) if(array_key_exists($i,$this->values)){
			foreach($this->values[$i] as $name=>$v) if(array_key_exists($name,$this->fields)) {
				//$this->show("<div><b>step $i</b>:$name=$v</div>");
				$this->fields[$name]->value=$v;
			}
		}
	}
	final protected function step(){//chama o step corrente
		if(!method_exists($this,$fn='step_'.$this->protect['step'])) return true;
		if(call_user_func(array($this,$fn))===false) return false;
		$this->end();
		return true;
	}
	final public function go($step=0){
		$this->protect['step']=$step+0;
		$this->start();
		$this->doStep();
		return $this;
	}
	final private function doStep(){
		if($this->exec) {//Execução 
			$this->protect['ok']=$this()===false?false:true;
			if(!$this->protect['ok']) $this->step();
		}
		elseif(!($this->protect['ok']=$this->step()) && $this->protect['step']) $this->protect['step']--;
	}
	final protected function show($html){//insere HTML no corpo do wizard
		if(is_array($html)) $html='<pre>'.print_r($html,true).'</pre>';
		$this->html.=$html;
		return $this;
	}
	protected function destroy(){
		$this->oSess->destroy();
		$this->protect=array();
		return $this;
	}
	/**
	 * @param mixed $objs=null
	 * 
	 * @return void
	 */
	final protected function resume($objs=null){//null=all,this,values,0,1,2..n
		$pr=function($label,$ln,$val=null,$class=''){
			if(!is_null($val)){
				$v=var_export($val,true);
				if(is_array($val)) $val='<code class="value_compacted" title="'.htmlspecialchars($v,ENT_QUOTES).'">array</code>';
				elseif(is_object($val)) {
					$v2=var_export(@$val->value,true);
					$val='<code class="value_compacted" title="'.htmlspecialchars($v,ENT_QUOTES).'">&lt;'.get_class($val).'&gt;</code>'.$v2;
				}
				else $val=$v;
			}
			if(@$ln['readonly']) {
				$class.=' bg-warning';
				$cl=' title="readonly"';
			}
			elseif(@$ln['main']) {
				$class.=' bg-info';
				$cl="style='cursor: pointer;' onclick='$(this).parent().find(\".{$ln['main']}\").toggle(\"slow\")'";
			}
			else $cl='';
			$col='';
			$help='';
			if(@$ln['help']) $help="\n				<td>{$ln['help']}</span></td>";
			else $col=' colspan=2';
			return "
			<tr class='$class'$cl>
				<th><nobr>$label</nobr></th>
				<td$col>$val</span></td>$help
			</tr>";
		};

		$out='<table class="table table-hover table-sm table-responsive"><tr><th>Arg</th><th>Value</th><th>Descr</th></tr>';
		if(is_null($objs) || preg_match('/\bt(his)?\b/i',$objs)) {//this
			{$arr=array(
				'step'        =>array('help'=>'Modifica o step para o passo 0',),
				'oldStep'     =>array('help'=>'qual foi o step anterior',),
				'numSteps'    =>array('help'=>'Quantidade de steps','readonly'=>true,),
				'minStep'     =>array('help'=>'default $this->numSteps, A partir do step 1 pode ser concluido',),
				'ok'          =>array('help'=>'houve sucesso/erro step anterior $this->oldStep',),
				'message'     =>array('help'=>'Info/Erro/Sucess Message; Sucess somente quando concluido __invoke',),
				'exec'        =>array('help'=>'Foi concluido (tentativa)','readonly'=>true,),
				'id'          =>array('help'=>'Id do Wizard e palavra reservada não podendo ser nome de campo','readonly'=>true,),
				'title'       =>array('help'=>'Define um título para o Wizard',),
				'subTitle'    =>array('help'=>'Define um subtítulo para o Wizard',),
				'height'      =>array('help'=>'Altura do Wizard',),
				'showBar'     =>array('help'=>'Show|Hide progress bar',),
				'showPg'      =>array('help'=>'Show|Hide páginas',),
				'reset'       =>array('help'=>'Limpa todo o cache para o próximo passo',),
				'secure'      =>array('help'=>'Objeto Secure|herdado. Caso tenha sido instanciado, retorna todas as opções de segurança. Ex: CRUDS, etc',),
				'btn'         =>array('help'=>'Array associativo de Objetos de todos os botões do rodapé do Wizard ('.implode(',',array_keys($this->btn)).')',),
			);}
			$out.=$pr('$this-&gt;<span class="badge">arg</span>',array('help'=>"Argumentos do this",'main'=>$grp='main'));
			foreach($arr as $label=>$ln) $out.=$pr($label,$ln,$this->$label,$grp);
		}
		//$out.="\n			<tr style='background-color: #000;'><th></th><td></td><td></td></tr>";
		$values=$this->values;           // Busca todos os valores'
		foreach($values as $step=>$oStep) if(is_null($objs) || preg_match('/\b(v(alues?)?|'.$step.')\b/i',$objs)) {
			$out.=$pr('$this-&gt;values['.$step.'][<span class="badge">field</span>]',array('help'=>"Array de valores do step $step",'main'=>$grp='val'.$step));
			if($oStep) foreach($oStep as $field=>$v) $out.=$pr($field,$ln,$v,$grp);
			else $out.=$pr('<span class="text-muted">vazio</span>',array(),null,$grp);
		}
		
		if(is_null($objs) || preg_match('/\bf(ields?)?\b/i',$objs)) {
			$lf="\n";
			$help='Array de valores dos campos do formulário. ';
			$help.='<code onmouseover="$(this).next().show(\'slow\')" onmouseout="$(this).next().hide(\'slow\')">Ver Implementação</code>';
			$help.='<pre style="display:none;">';
			$help.='class XXXX extends Wizard{'.$lf;
			$help.='	protected $fields=array('.$lf;
			$help.='		"field1"=>0,             //new ElementNumber("field1",0);'.$lf;
			$help.='		"field2"=>"ElementText", //new ElementText("field2");'.$lf;
			$help.='		"field3"=>true,          //new ElementChek("field3",true);'.$lf;
			$help.='		//(new ElementString("field4"))->label="Ex";'.$lf;
			$help.='		"field4"=>array("label"=>"Ex"),'.$lf;
			$help.='		//(new ElementText("field5"))->label="Ex";'.$lf;
			$help.='		"field5"=>array("Element"=>"ElementText","label"=>"Ex"),'.$lf;
			$help.='	);'.$lf;
			$help.='	protected function start(){'.$lf;
			$help.='		$this->fields["field0"]=new ElementCpf("nome_qq");'.$lf;
			$help.='	}'.$lf;
			$help.='}</pre>';
			$out.=$pr('$this-&gt;fields[<span class="badge">field</span>]',array('help'=>$help,'main'=>$grp='fields'));
			if($this->fields) foreach($this->fields as $name=>$field) {
				$out.=$pr($name,array(),$field,$grp);
			}
			else $out.=$pr('<span class="text-muted">vazio</span>',array(),null,$grp);
		}
		
		if(is_null($objs) || preg_match('/\bm(ethods?)?\b/i',$objs)) {//methods
			$arr=array();
			{$arr['show']=array('help'=>'
				Imprime no corpo do Wizard um HTML.
				<pre>$this-&gt;show(html);</pre>
			',);}
			{$arr['resume']=array('help'=>'
				Mostra o resumo de todos os argumentos com valores e methods, tudo, com suas respectivas ajudas. 
				<pre>$this-&gt;resume([<kbd class="text-muted">null</kbd>|"<kbd class="text-muted">&lt;num&gt;</kbd><kbd>this</kbd>,<kbd>values</kbd>,<kbd>fields</kbd>,<kbd>methods</kbd>"]);</pre>
				<dl class="dl-horizontal">
					<dt class="text-muted">null</dt><dd>Mostra tudo</dd>
					<dt class="text-muted">&lt;num&gt;</dt><dd>Mostra valores do step <num> começando por 0</dd>
					<dt>t|this</dt><dd>Mostra todos os argumentos</dd>
					<dt>v|alues</dt><dd>Mostra todos os valoes</dd>
					<dt>f|fields</dt><dd>Mostra todos os campos $this->fields</dd>
					<dt>m|methods</dt><dd>Mostra todos os métodos</dd>
				</dl>
			',);}
			{$arr['help']=array('help'=>'
				Método que pode ser implementado substituindo o default que é chamar <code>$this-&gt;resume();</code>
				<pre>$this-&gt;help();</pre>
			',);}
			{$arr['values2Fields']=array('help'=>'
				Copia todos os valores de <code>$this-$gt;values</code> para <code>$this-$gt;fields</code>
				<pre>$this-&gt;values2Fields();</pre>
			',);}
			{$arr['start']=array('help'=>'
				Método que deve ser implementado caso deseje inicializar valores padrões no sistema.
				<pre>$this-&gt;start();</pre>
			',);}
			{$arr['step_<span class="text-meted">&lt;step_num&gt;</span>']=array('help'=>'
				Método que deve ser implementado caso deseje adicionar steps no Wizard.
				<pre>$this-&gt;step();</pre>
			',);}
			{$arr['__invoke']=array('help'=>'
				Obrigatório a implementação.<br>
				Método que é executado ao Concluir o Wizard.<br>
				Caso o retorno seja <code>FALSE</code> será considerado erro. Uma mensagem de erro ou sucesso poderá ser adicionada em <code>$this->message</code>
				<pre>$this-&gt;__invoke();</pre>
			',);}
			{$arr['destroy']=array('help'=>'
				Método que é executado ao Cancelar o Wizard.<br>
				<pre>$this-&gt;destroy();</pre>
			',);}
			$out.=$pr('$this-&gt;<span class="badge">method(parm,...)</span>',array('help'=>"Métodos do this",'main'=>$grp='methods'));
			foreach($arr as $label=>$ln) $out.=$pr($label,$ln,null,$grp);
		}
		$out.='</table>';
		$this->show($out);
	}
	protected function help($objs=null){ return $this->resume(); }
	
	abstract protected function __invoke();
	protected function start(){ return $this; }
	protected function end(){ return $this; }
	protected function error($mess,$top=false){
		//$this->messTop=false;
		$this->message($mess,'danger',$top);
	}
	protected function message($mess,$class='success',$top=false){
		//$this->messTop=false;
		$m="<div class='col-sm-12 alert alert-$class'>$mess</div>";
		if($top) $this->message=$m.$this->message;
		else $this->message.=$m;
	}
	public function showValues(){
		$out='';
		foreach($this->values as $k=>$o) {
			$out.="<div><b>$k</b>: ";
			$i=[];
			foreach($o as $name=>$obj) {
				$t=gettype($obj);
				if($t=='array') $t.='('.count($obj).')';
				$i[]=$name.'['.$t.']';
			}
			$out.=implode(',',$i).'</div>';
		}
		$this->show($out);
	}
}
