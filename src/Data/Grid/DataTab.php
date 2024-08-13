<?php
 /**
 * Page-Level DocBlock DataTab
 * @package Easy
 * @subpackage Screen
 * @category Tools
 * @author Helbert Fernandes <helbertfernandes@yahoo.com.br>
 * @version 1.1 em 03/10/2006 15:00 - Helbert Fernandes
 */
 
 /**
 * Exibe guias apresentando os determinados objetos nela contido
 *
 * @see new Data
 * @example
 * <code>
 * <?php 
 * $dt=new DataTab(); 
 * $dt->add('view','table',$DataObj,...);
 $ $dt->conn=$conn;
 * print $dt;
 * ?>
 * </code>
 */
class DataTab extends Data {
	public $tabs=array();
	//protected $varMeditated=array('active');
	
	function __construct($id=false,$label=false){
		parent::__construct($id,$label);
		$this->active=0;
	}
	function __tostring(){
		$label=array();
//$label=array('Eu que bem','Tu bão','Ele vai','Nós tmabém','Eu que bem','Tu bão','Ele vai','Nós tmabém','Eu que bem','Tu bão','Ele vai','Nós tmabém','Eu que bem','Tu bão','Ele vai','Nós tmabém','Eu que bem','Tu bão','Ele vai','Nós tmabém');
		if(!$this->tabs) return '';
		$i=0;
		$act=$this->active;
		$obj=false;
		foreach ($this->tabs as $v) {
			if ($i==$act) {
				if (($obj=$this->getObject($v))===false) continue;
				$l=$obj->label;
			} elseif (($l=$this->getLabel($v))===false) continue;
			$label[$i]=$l;
			$i++;
		}
		if (!$label) return '';
		//$this->startClass();
		foreach($label as $k=>$l) $label[$k]=$this->getBox($l,$k,$act);
		$out="<div id='DataTab'><div id='DataTab_bar'>".implode('',$label)."</div>";
		$out.="<div id='DataTab_body'>".($obj?@$obj->__tostring():'')."</div>";
		$out.="</div>";
		//$this->endClass();
		return $out;
	}
/*
	function __get($nm){
		return parent::__get($nm);
	}
*/
	function getBox($label,$id,$active){
		$a=$id==$active?"Active":"";
		return "<div id='DataTab_Button$a' onclick='{$this->id}.click(this,$id)' onmouseover='{$this->id}.over(this)' onmouseout='{$this->id}.out(this)'><div id='DataTab_leftButton'></div><div id='DataTab_middleButton'>$label</div><div id='DataTab_rightButton'></div></div>";
	}
	function getLabel($var) {
		if (is_string($var)) {
			//busca no banco o label da view
			return $var;
		}elseif (is_object($var)) return @$var->label;
		return false;
	}
	function getObject($var) {
		if (is_string($var)) {
			//constroe um DataList
			$r=new DataHtml(false,$var);
			$r->html='test';
			return $r;
		}elseif (is_object($var)) return $var;
		return false;
	}

	function add(){//$obj,'view'

	}
}
