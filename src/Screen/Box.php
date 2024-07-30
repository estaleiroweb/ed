<?php
	/**
 * Gera uma caixa html de acordo com o padr�o skin configurado no .ini
 * 
 * @EXAMPLE print new Box("html")
 */
class Box {
	/**
	 * Nome da classe CSS que ir� ter o box
	 * @var string
	 */
	public $class='';
	/**
	 * Local da fun��o de esconder o box, caso preenchido. Valores poss�veis {r,c,l,t,m,b}
	 * @var string
	 */
	public $fnHidden='';
	/**
	 * Array associativo com o Id de cada parte do box {tl,tc,tr,ml,mc,mr,bl,bc,br}=>id
	 * @var array
	 */
	public $id=array();
	/**
	 * Array associativo com o Id de cada parte do box {tl,tc,tr,ml,mc,mr,bl,bc,br}=>html
	 * @var array
	 */
	public $html=array();
	/**
	 * Array associativo com o estilo de cada parte do box {tl,tc,tr,ml,mc,mr,bl,bc,br}=>style
	 *
	 * @var array
	 */
	public $style=array();
	/**
	 * local onde nunca poder� ficar escondido, caso preenchido. Valores poss�veis {r,c,l,t,m,b}
	 * @var string
	 */
	public $neverHidden='';
	/**
	 * Configura se o box apresenta escondido ou n�o
	 * @var boolean
	 */
	public $hidden=false;
	/**
	 * Configura se box tem sombra [ainda n�o implementado pelos CSS]
	 * @var boolean
	 */
	public $shadow=true;
	/**
	 * Oculta ou n�o o Topo
	 * @var boolean
	 */
	public $t=true;
	/**
	 * Oculta ou n�o o Meio Vertical
	 * @var boolean
	 */
	public $m=true;
	/**
	 * Oculta ou n�o em Baixo
	 * @var boolean
	 */
	public $b=true;
	/**
	 * Oculta ou n�o o Esquerda
	 * @var boolean
	 */
	public $l=true;
	/**
	 * Oculta ou n�o o Centro Horizontal
	 * @var boolean
	 */
	public $c=true;
	/**
	 * Oculta ou n�o a Direita
	 * @var boolean
	 */
	public $r=true;
	public $OutHtml;

	/**
	 * Parametros s�o para o eixo mc, ou seja, central do box
	 *
	 * @param string $html conteudo html do eixo mc
	 * @param string $attrib atributos gerais do table gerado
	 * @param string $class classe CSS do table geral
	 * @param string $id id do eixo mc
	 */
	function __construct($html='',$attrib='',$class='',$id=''){
		if ($id) $this->id['mc']=$id;
		if ($html) $this->html['mc']=$html;
		$this->class=$class;
		$this->attrib=$attrib;
		$this->OutHtml=OutHtml::singleton();
	}
	/**
	 * Retorna o Box formatado
	 *
	 * @return string
	 */
	function __toString() {
		$this->OutHtml->style(__CLASS__,'easyData');
		$outFormat=isset($_REQUEST['outFormat'])?$_REQUEST['outFormat']:'';;
		//inicializa variaveis
		$cr="\n";
		$class=$this->class?$this->class:'Box';
		$boxMatrix=array(array('t','m','b'),array('l','c','r'));

		//faz o box
		$ret='';
		$ret.="$cr<table border='0' cellspacing='0' class='$class' $this->attrib>\r\n";
		foreach ($boxMatrix[0] as $row) if ($this->$row){
			$ret.="$cr<tr>";
			foreach ($boxMatrix[1] as $cell) if ($this->$cell){
				$id=$fn=$nh=$style=$cl='';
				$html='<div>&nbsp;</div>';
				$item=$row.$cell;
				if (isset($this->html[$item])) $html=print_r($this->html[$item], true);
				if ($this->hidden) $style='display:nome';
				if (isset($this->style[$item])) $style.=($style?";":'').$this->style[$item];
				if ($style) $style=" style='$style'";

				if (preg_match("/[$item]/",$this->neverHidden) || preg_match("/[$item]/",$this->fnHidden)) $nh=' Box_neverHidden';
				if (isset($this->id[$item])) $id=' id=\''.htmlspecialchars ( $this->id[$item], ENT_QUOTES ).'\'';
				if (preg_match("/[$item]/",$this->fnHidden)) {
					$this->OutHtml->script('Box','easyData');
					$nh.=' Box_HandHidden';
					$fn=' onclick=\'BoxShowHidden(this)\'';
				}
				$ret.=$outFormat?"<td$fn$style>$html</td>\r\n":"<td class='Box_$item$nh'$id$fn$style>$html</td>\r\n";
			}
			$ret.="</tr>\r\n";
		}
		$ret.="$cr</table>\r\n";
		return $ret;
	}
}
