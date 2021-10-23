<?php
class MyMenu {
	public $eletric,$context,$id,$inicialization=true;
	private $path,$config,$logon,$site,$dsn,$isAdmin,$sqlGrpUser,$grpUser;
	protected  $readonly=array(
		'id'=>'',
		'name'=>'',
		'conn'=>'',
	);
	protected  $att=array(
		'electric'=>false,
		'context'=>false,
		'blink'=>false,
		'timeout'=>false,
	);
		
	function __construct($id=1,$context=false,$eletric=false){
		$this->readonly['id']=$id;
		$this->att['context']=$context;
		$this->att['electric']=$eletric;
		
		$OutHtml=OutHtml::singleton();
		$this->config=$OutHtml->config;
		$this->path=$OutHtml->config->hmenu;
		$this->site=$OutHtml->config->site;
		$this->dsn=$OutHtml->config->secury['dsn'];

		$OutHtml->style('skin-xp',$this->path);
		$OutHtml->addHead("<script type='text/javascript'>_dynarch_menu_url='{$this->path}'</script>");
		$OutHtml->script('hmenu',$this->path);
	}
	function __get($nm){
		if (isset($this->readonly[$nm])) return $this->readonly[$nm];
		if (isset($this->att[$nm])) return $this->att[$nm];
	}
	function __set($nm,$val){
		if (isset($this->att[$nm])) return $this->att[$nm]=$val;
	}
	function connect(){
		if (!$this->readonly['conn']) {
			$dsn=Dsn::singleton();
			$d=$this->dsn;
			$this->readonly['conn']=Conn::singleton($dsn->$d);
		}
		return $this->readonly['conn'];
	}
	function __tostring(){
		$OutHtml=OutHtml::singleton();
		$conn=$this->connect();
		
		$id='';
		if(is_numeric($this->readonly['id'])) {
			$res=$conn->query("SELECT * FROM tb_Menu WHERE idMenu={$this->readonly['id']}");
			if ($l=$res->fetch_assoc()) $id=$this->readonly['id'];
			$res->close();
		}
		if (!$id) {
			$res=$conn->query("SELECT * FROM tb_Menu WHERE Menu='{$conn->escape_string($this->readonly['id'])}' AND idMenuPai=0");
			if ($l=$res->fetch_assoc()) $id=$l['idMenu'];
			$res->close();
		}
		if (!$id) {
			print "<div>ERRO: Não existe o Id de Menu '$id'</div>";
			return '';
		}
		$secury=Secure::singleton();
		$this->sqlGrpUser=$secury->sqlGrpUser;
		$this->grpUser=implode(',',array_keys($secury->grpUser));
		$this->isAdmin=$secury->isAdmin;
		
		$this->readonly['id']=$id;
		$this->readonly['name']="hmenu_{$this->readonly['id']}";
		$this->logon=$_SESSION['logon'];

		if ($this->inicialization) $OutHtml->script[]="DynarchMenu.setup('{$this->readonly['name']}',{$this->getValueJS($this->att)})";
		$att=array(
			"id='{$this->readonly['name']}'",
			"style='display:none;'", 
		);
		return $this->getMenu($this->readonly['id'],$att);
	}
	function getMenu($idPai,$att=array(),$tab=""){
		$conn=$this->connect();
		$itens=array();
		$disable=$this->isAdmin?"0":"if(f.idFile,abs(p.R-1),0)";
		$adm=(int)$this->isAdmin;
		$idUser=$this->logon['idUser'];
		$sql="
			SELECT 
				m.*, f.Nome, min($disable) as `disabled`
			FROM tb_Menu m
			LEFT JOIN tb_Files f ON m.idUrl=f.idFile
			LEFT JOIN tb_Permition p ON p.idFile=f.idFile
			WHERE idMenuPai=$idPai AND ($adm OR f.idFile IS NULL OR p.idGrpUsr in ({$this->grpUser}))
			GROUP BY idMenu
			ORDER BY `Order`
		";
		$res=$conn->query($sql);
		$ok=false;
		//print "<pre>$sql</pre>";
		while ($l=$res->fetch_assoc()){
		$sql=print_r($l,true);
		//if (!$l['disabled']) print "{$l['Nome']}<br>";
			$attBox=array();
			if($l['ClassBox']) $attBox[]=" class='{$l['ClassBox']}'";
			$child=$this->getMenu($l['idMenu'],$attBox,"$tab\t");
			//if(!$child) continue;
			if($child) $child="\n$child";
			$url=$child?'':(trim($l['Nome']?$l['Nome']:$l['Url']));
			if ($l['disabled']) {
				continue;
				$l['Class'].=($l['Class']?" ":"")."disabled";
				$url='';
			} 
			if ($url) {
				if(!$l['disabled']) $ok=true;
				if (!$l['Title']) $l['Title']=$url;
				if ($l['Get']) $url.="?{$l['Get']}";
			}elseif($l['Img'].$l['Menu'].$l['Html'] && !$child) continue;
			$class=$l['Class']?" class='{$l['Class']}'":'';
			$title=$titleLi='';
			if ($l['Title']) {
				$title=" title='{$l['Title']}'";
				if (!$url) $titleLi=$title;
			}
			$item="<li$class$titleLi>\n$tab";
			$onclick='';
			if(preg_match("/^javascript\:(.*)$/",$url,$ret)) {
				$onclick=" style='cursor:pointer;' onclick='{$ret[1]}'";
				$url='';
			}
			if ($l['Img']){
				$alt=$l['AltImg']?" alt='{$l['AltImg']}'":'';
				$item.="<img src='{$this->strEval($l['Img'])}'$alt$onclick />";
			}
			$menu=trim($l['Menu']).trim(@eval("return \"{$l['Html']}\";")).$child;
			if ($url){
				$url="{$this->site}/$url";
				$target=$l['Target']?" target='{$l['Target']}'":'';
				$item.="<a href='$url'$target$title>$menu</a>";
			} else {
				if($onclick) $menu="<span$onclick>$menu</span>";
				$item.="$tab\t\t$menu";
			}
			$item.="\n$tab\t</li>";
			$itens[]=$item;
		}
		$res->close();
		if (!$itens) return '';
		$att=$att?" ".implode(" ",$att):'';
		if ($ok) {
			$er="\<li\>\s*\<\/li\>";
			$out=implode("\n$tab\t",$itens);
			$out=preg_replace(array("/$er\s*$er/i","/^\s*$er/i","/er\s*$/i"),'',$out);
			return $out?"$tab<ul$att>\n$tab\t$out\n$tab</ul>\n":"";
		} else return '';
	}
	function getNameMenu($idMenu){
		if (!$idMenu) return'';
		$conn=$this->connect();
		$sql="SELECT * FROM tb_Menu m WHERE idMenuPai=$idMenu ";
		$res=$conn->query($sql);
		if ($l=$res->fetch_assoc()) return $l['Menu'];
	}
	function getValueJS($value){
		if (is_bool($value)) return $value?"true":"false";
		if (is_numeric($value)) return $value;
		if (is_array($value)) {
			$tmpO=$tmpA=array();
			$n=true;
			foreach ($value as $k=>$v) {
				if ($n && !is_numeric($k)) $n=false;
				$v=$this->getValueJS($v);
				$tmpO[]="$k:$v";
				$tmpA[]=$v;
			}
			if ($n) return "[".implode(",",$tmpA)."]";
			return "{".implode(",",$tmpO)."}";
		}
		return "'".addslashes($value)."'";
	}
	function strEval($value){
		($out=@eval("return \"$value\";")) || $out=$value;
		return $out;
	}
	function p($text){
		print "<pre>".print_r($text,true)."</pre>";
	}
}
