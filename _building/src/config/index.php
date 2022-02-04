<?php
# Desenvolvido por Helbert Fernandes 2010-07-28
{#Configurações
	{#MAIN
	define('BASE_DIR',dirname(__FILE__));
	define('HIDDEN','index.php;icons');//index.php;autoindex;testar/pub;/testar/novo
	define('INDEX_ICONS','icons');
	define('DEFAULT_STYLE','winvista');
	define('DATE_FORMAT','%d/%m/%Y %T');
	define('DIRECTORY_REAL_SIZE',false);
	define('SIZE_TYPE','h'); //b: bytes, k: Kbytes, m: MBytes, g: GBytes, t: TBytes, h: Human type
	define('FIRSTDIR',1); //0: junto com os arquivos, 1: diretórios primeiro, -1: diretórios por último
	define('CHAR_checked','<font face="wingdings" color="green">ü</font>'); //'<font face="webdings" color="green">a</font>'
	define('CHAR_unchecked','<font face="wingdings" color="red">û</font>'); //'<font face="webdings" color="red">r</font>'
	define('SEP_FOOT',' - ');
	}
	{#SHOW...
	define('SHOW_ORDER_DIR',true);
	define('SHOW_SERACH',true);
	define('SHOW_PATH_LINK',true);
	define('SHOW_TABLE_HEADER',true);
	define('SHOW_TABLE_FOOT',true);
	define('SHOW_COUNT_FILES',true);
	define('SHOW_COUNT_DIRECTORIES',true);
	define('SHOW_COUNT_TOTAL',true);
	define('SHOW_COUNT_SIZE',true);

	define('SHOW_Item',true);
	define('SHOW_Icon',true);
	define('SHOW_Size',true);
	define('SHOW_Type',true);
	define('SHOW_AccessTime',false);
	define('SHOW_UpNodeTime',false);
	define('SHOW_UpdateTime',true);
	define('SHOW_Group',false);
	define('SHOW_iNode',false);
	define('SHOW_Owner',false);
	define('SHOW_Perms',true);
	define('SHOW_X',true);
	define('SHOW_W',true);
	define('SHOW_Link',true);
	}
	{#STYLE
	define('STYLE',"
	<style>
		body {
			padding: 5px;
		}
		form {
			margin:0;
			padding: 0;
		}
		a {
		}
		a:hover {
			background-color: #aa55FF;
		}
		.orderDir,
		.search,
		.IndexOf {
			display: inline;
		}
		.orderDir a,
		.orderDir a:visited,
		.orderDir a:active {
			background-color: #777777;
			color: #FFFFFF;
			margin-right: 3px;
			border: 1px solid #000000;
			text-decoration: none;
			padding-left: 4px;
			padding-right: 4px;
		}
		.orderDir a:hover {
			border: 1px solid #AAAAAA;
			background-color: #999999;
		}
		.orderDir img {
			/*margin-right: 3px;*/
		}
		.IndexOf span {
		}
		.IndexOf a,
		.IndexOf a:visited,
		.IndexOf a:active {
			text-decoration: none;
			color: #999999;
			font-weight: bold;
		}
		.IndexOf a:hover {
			background-color: transparent;
			text-decoration: underline;
			color: #0000FF;
		}
		.search img{
			cursor: pointer;
			margin-right: 5px;
		}
		table {
			border-top: 1px solid #000000;
			border-left: 1px solid #000000;
		}
		table.Files {
			margin-top: 3px;
		}
		table.retSearch {
			margin-top: 10px;
		}
		tbody td,
		thead th {
			font-family: courier new;
			padding-left: 4px;
			padding-right: 4px;
			border-left: 1px solid #EEEEEE;
			border-right: 1px solid #999999;
			white-space:nowrap;
		}
		thead th {
			background-color: #999999;
			color: #FFFFFF;
			cursor: pointer;
		}
		thead th.Over {
			background-color: #888888;
			color: #eeeeee;
		}
		thead img.sort {
			margin-right: 4px;
		}
		
		tbody tr {
			cursor: pointer;
		}
		tbody img {
			margin-right: 3px;
		}
		tbody tr.oddLine {
			background-color: #EEEEEE;
			color: #444444;
		}
		tbody tr.evenLine {
			background-color: #DDDDDD;
			color: #333333;
		}
		tbody tr.oddLineOver {
			background-color: #CCCCCC;
			color: #222222;
		}
		tbody tr.evenLineOver {
			background-color: #BBBBBB;
			color: #111111;
		}

		tbody td.Size, 
		tbody td.Perms, 
		tbody td.Group, 
		tbody td.iNode, 
		tbody td.Owner  {
			font-family: courier new;
			text-align: right;
		}
		tbody td.Type {
			text-align: center;
		}
		tbody abbr.users {
			color: black;
		}
		tbody abbr.group {
			color: orange;
		}
		tbody abbr.onwer, 
		tbody abbr.wrongLink {
			color: red;
		}
		tbody abbr.special {
			color: blue;
		}
		tfoot tr {
			background-color: #000000;
			color: #FFFFFF;
		}
		tfoot td {
			padding-left: 4px;
			padding-right: 4px;
		}
		tfoot span.Directories,
		tfoot span.Files,
		tfoot span.Total,
		tfoot span.TotalSize {
		}
	</style>
	");
	}
}
print new AutoIndex;
class AutoIndex {
	private $root,$httpRoot,$base,$path,$style,$sort,$firstDir,$pathIcons,$urlIcons,$search;
	private $macros=array(
		'Size'=>'getSizeTxt',
		'X'=>'showItemChecked',
		'W'=>'showItemChecked',
		'Perms'=>'showItemAttr',
		'Link'=>'showItemLink',
	);
	private $icons=array();
	function __construct(){
		@header ("expires: Mon, 26 Jul 1990 05:00:00 GMT");
		@header ("last-modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		@header ("cache-control: private");
		@header ("pragma: no-cache");
		define('SHOW_Name',true);
		@date_default_timezone_set('America/Sao_Paulo');
		$this->root=$_SERVER['DOCUMENT_ROOT'];
		$this->httpRoot=dirname($_SERVER['PHP_SELF']);
		$this->path=preg_replace('/^\/+/','',@$_GET['dir']);
		if($this->path=='.') $this->path=='';
		($this->style=@$_GET['style']) || ($this->style=DEFAULT_STYLE);
		$this->sort=(array)@$_GET['sort'];
		$this->firstDir=isset($_GET['firstDir'])?$_GET['firstDir']:FIRSTDIR; 
		$this->urlIcons=$this->httpRoot.'/'.INDEX_ICONS.'/'.$this->style;
		$this->search=@$_POST['search'];
	}
	function __toString(){
	//glob("*.txt") pesquisa
		$out='<html><head><title>AutoIndex '.$this->path.'</title>'.STYLE.'</head></body><form method="post">';
		if(SHOW_ORDER_DIR) $out.=$this->showOrderDir();
		if(SHOW_SERACH) $out.=$this->showSearch();
		if(SHOW_PATH_LINK) $out.='<div class="IndexOf">Index of: '.$this->getPathLink().'<div>';
		$dir=$this->getDir();
		#sort
		$sort=array();
		if($this->firstDir==1) $sort[]='$dir[\'isDir\'],SORT_DESC';
		elseif($this->firstDir==-1) $sort[]='$dir[\'isDir\'],SORT_ASC';
		foreach($this->sort as $k=>$v) $sort[]='$dir[\''.$k.'\'],'.$v;
		foreach($dir as $k=>$lines) if($k!='isDir' && !isset($this->sort[$k])) $sort[]='$dir[\''.$k.'\']';
		eval ('array_multisort('.implode(',',$sort).');');
		unset($dir['isDir']);

		//return '<pre>'.print_r($dir,true).'</pre>';
		$out.='<table class="Files" cellspacing=0 cellpadding=0 border=0>';
		$keys=array_keys($dir);
		$contItens=0;
		if(SHOW_Item) $contItens++;
		foreach($keys as $v) if($this->isShowItem($v)) $contItens++;
		if(SHOW_TABLE_HEADER) {
			$out.='<thead><tr>';
			if(SHOW_Item) $out.='<th class="item">#</th>';
			foreach($keys as $v) $out.=$this->showHeader($v);
			$out.='</tr></thead>';
		}
		$countDirs=$countFiles=$countSize=0;
		$out.='<tbody>';
		if($this->path) {
			$d=dirname($this->path);
			if($d=='.')$d='';
			$src='location="'.$this->rebuildGet(array('dir'=>$d)).'"';
			$out.='<tr class="oddLine" fixclass="oddLine" onmouseover=\'this.className=this.fixclass+"Over"\' onmouseout=\'this.className=this.fixclass\' onclick=\''.$src.'\'>';
			if(SHOW_Item) $out.='<td class="item">&nbsp;</td>';
			foreach($keys as $item) $out.=$this->showItem($item,$item=='Name'?((SHOW_Icon?'<img src="'.$this->urlIcons.'/back.png" border=0>':'').'Back'):'');
			$out.='</tr>';
		}
		foreach($dir['Name'] as $k=>$v) {
			if($dir['Type'][$k]=='dir') {
				$countDirs++;
				$src='location="'.$this->rebuildGet(array('dir'=>$this->path.'/'.$dir['Name'][$k])).'"';
			} else {
				$countFiles++;
				$src='window.open("'.$this->httpRoot.($this->path?'/'.$this->path:'').'/'.$dir['Name'][$k].'")';
			}
			$countSize+=$dir['Size'][$k];
			$class=($k & 1)?'oddLine':'evenLine';
			$out.='<tr class="'.$class.'" fixclass="'.$class.'" onmouseover=\'this.className=this.fixclass+"Over"\' onmouseout=\'this.className=this.fixclass\' onclick=\''.$src.'\'>';
			if(SHOW_Item) $out.='<td class="item">'.($k+1).'</td>';
			$dir['Name'][$k]=(SHOW_Icon?'<img src="'.$this->urlIcons.'/'.$dir['Type'][$k].'.png" border=0>':'').htmlspecialchars($dir['Name'][$k]);
			foreach($dir as $item=>$lines) $out.=$this->showItem($item,$lines[$k]);
			$out.='</tr>';
		}
		$out.='</tbody>';
		if(SHOW_TABLE_FOOT) {
			$o=array();
			if(SHOW_COUNT_DIRECTORIES) $o[]='<span class="Directories">'.$countDirs.' Directories</span>';
			if(SHOW_COUNT_FILES) $o[]='<span class="Files">'.$countFiles.' Files</span>';
			if(SHOW_COUNT_TOTAL) $o[]='<span class="Total">'.($countDirs+$countFiles).' Itens no Total</span>';
			if(SHOW_COUNT_SIZE) $o[]='<span class="TotalSize">('.$this->getSizeTxt($countSize).')</span>';
			$out.='<tfoot><tr><td colspan="'.$contItens.'">'.implode(SEP_FOOT,$o).'</td></tr></tfoot>';
		}
		$out.='<table>';
		if($this->search) $out.=$this->getSearch();
		$out.='</form></body></html>';
		return $out;
	}
	private function rebuildGet($newGet=array()){
		$get=$_GET;
		foreach($newGet as $k=>$v) $get[$k]=$v;
		return '?'.http_build_query($get);
	}
	private function getFileTypeExt($file,&$ext){
		$type='';
		if(preg_match('/\.(pdf|js|ps|java|dll|mov|key|login)$/i',$file,$ret)) $type=strtolower($ret[1]);
		elseif(preg_match('/\.(x?html?|x?aspx?|x[sm]l)$/i',$file,$ret)) $type='web';
		elseif(preg_match('/\.(php?[345]?|inc)$/i',$file,$ret)) $type='php';
		elseif(preg_match('/\.(img|iso|nrg)$/i',$file,$ret)) $type='cd';
		elseif(preg_match('/\.(tar(?:\.bz2))$/i',$file,$ret)) $type='tar';
		elseif(preg_match('/\.(do[ct][xm]?|rtf)$/i',$file,$ret)) $type='doc';
		elseif(preg_match('/\.(xl[st]][xmb]?|xlam)$/i',$file,$ret)) $type='xls';
		elseif(preg_match('/\.((?:pp[ts]|pot|sld)[xm]?|ppam|thmx)$/i',$file,$ret)) $type='ppt';
		elseif(preg_match('/\.(txt)$/i',$file,$ret)) $type='text';
		elseif(preg_match('/\.(ini|conf|bat|sh|c|css)$/i',$file,$ret)) $type='comp';
		elseif(preg_match('/\.(h)$/i',$file,$ret)) $type='binhex';
		elseif(preg_match('/\.(jpe?g|gif|png|bmp)$/i',$file,$ret)) $type='image';
		elseif(preg_match('/\.(mp[23]|wav|mid)$/i',$file,$ret)) $type='sound';
		elseif(preg_match('/\.(swf|mpe?g|wmv?|asf|mp[ave]|avi)$/i',$file,$ret)) $type='movie';
		
		//elseif(preg_match('/\.(???????)$/i',$file,$ret)) $type='uu';
		//elseif(preg_match('/\.(???????)$/i',$file,$ret)) $type='new';
		else {
			$f=`file "$file"`;
			if(strpos($f,'image')!==false) $type='image';
			elseif(strpos($f,'audio')!==false) $type='sound';
			elseif(strpos($f,'movie')!==false) $type='movie';
			elseif(strpos($f,'compressed')!==false) $type='compressed';
			elseif(strpos($f,'text')!==false) $type='generic';
			elseif(strpos($f,'executable')!==false) $type='binary';
			else $type='unknown';
			
		}
		if(!($ext=strtolower(@$ret[1])) && preg_match('/\.([^\/]*)$/i',$file,$ret)) $ext=strtolower($ret[1]);

		return $type;
		/*
		back.png voltar
		search.png pesquisa
		*/
	}
	private function getDir(){
		$pathIcons=realpath(BASE_DIR.'/'.INDEX_ICONS.'/'.$this->style);
		$path=BASE_DIR.($this->path?'/'.$this->path:'');
		if(!is_dir($path)) {
			$path=BASE_DIR;
			$this->path='';
		}
		$erHidden='/^(\.|\.\.';
		if(HIDDEN) $erHidden.='|'.str_replace(';','|',preg_quote(preg_replace(array('/([^;]+)/','/\/+/'),array('/\1','/'),HIDDEN),'/'));
		$erHidden.=')$/';
		
		$dir=preg_grep('/^\.{1,2}$/',scandir($path),PREG_GREP_INVERT);
		if(HIDDEN) {
			$erHidden='/^('.str_replace(';','|',preg_quote(preg_replace(array('/([^;]+)/','/\/+/'),array('/\1','/'),HIDDEN),'/')).')$/';
			foreach($dir as $k=>$v) if(preg_match($erHidden,$this->path.'/'.$v)) unset($dir[$k]);
		}
		$dir=array_values($dir);
		$out=array();
		foreach($dir as $k=>$v) {
			$name=$path.'/'.$v;
			$out['Name'][$k]=$v;
			$sizeType=$ext=$out['Type'][$k]=$out['Size'][$k]='';
			if(is_dir($name)) {
				$out['isDir'][$k]=1;
				$out['Type'][$k]='dir';
				if(DIRECTORY_REAL_SIZE) {
					$cmd="IFS=$'\n';SIZE=0;for i in `find $name/*`;do if [ -f \"\$i\" ]; then SIZE=$((\$SIZE+`ls -bl \"\$i\" | awk '{print $5}'`)); fi; done; echo \$SIZE";
					$out['Size'][$k]=(double)`$cmd`;
				} else {
					$out['Size'][$k]=(double)`du -s "$name/"`;
					//$out['Size'][$k]=(double)disk_total_space($name);
				}

			} else {
				$out['isDir'][$k]=0;
				$out['Type'][$k]=$this->getFileTypeExt($name,$ext);
				$out['Size'][$k]=(double)@filesize($name);
			}
			$out['Perms'][$k]=@fileperms($name);
			$out['X'][$k]=(int)is_executable($name);
			$out['W'][$k]=(int)is_writable($name);
			$out['Link'][$k]=is_link($name)?readlink($name):'';
			$out['AccessTime'][$k]=($dt=@fileatime($name))?strftime(DATE_FORMAT,$dt):'';
			$out['UpNodeTime'][$k]=($dt=@filectime($name))?strftime(DATE_FORMAT,$dt):'';
			$out['UpdateTime'][$k]=($dt=@filemtime($name))?strftime(DATE_FORMAT,$dt):'';
			$out['Group'][$k]=@filegroup($name);
			$out['iNode'][$k]=@fileinode($name);
			$out['Owner'][$k]=@fileowner($name);
		}
		return $out;
	}
	private function getSizeTxt($size){
		$fn='getSize'.strtoupper(SIZE_TYPE);
		$sizeTxt=$this->$fn($size,$sizeType);
		return $sizeTxt.$sizeType;
	}
	private function getSizeB($sizeBytes,&$type) {
		$type='';
		return $sizeBytes;
	}
	private function getSizeK($sizeBytes,&$type) {
		$type='K';
		return $sizeBytes/1024;
	}
	private function getSizeM($sizeBytes,&$type) {
		$type='M';
		return $sizeBytes/1048576; //1024^2
	}
	private function getSizeG($sizeBytes,&$type) {
		$type='G';
		return $sizeBytes/1073741824; //1024^3
	}
	private function getSizeT($sizeBytes,&$type) {
		$type='T';
		return $sizeBytes/1099511627776; //1024^4
	}
	private function getSizeH($sizeBytes,&$type) {
		$sizeTypes=array('getSizeT','getSizeG','getSizeM','getSizeK','getSizeB');
		foreach($sizeTypes as $fn) {
			$size=round($this->$fn($sizeBytes,$type),1);
			if($size) break;
		}
		return $size;
	}
	private function getPathLink(){
		$out='<a href="?">...</a>';
		$parts=explode('/',str_replace('\\','/',$this->path));
		$full='';
		foreach($parts as $d) if($d) {
			$full.='/'.$d;
			$out.='<span>/</span><a href="'.$this->rebuildGet(array('dir'=>$full)).'">'.$d.'</a>';
		}
		return $out;
	}
	private function getSearch(){
		$path=BASE_DIR.($this->path?'/'.$this->path:'');
		$cmd="find '$path' -name '{$this->search}'";
		$dir=preg_split('/\n/',trim(`$cmd`));
		$er='/^'.preg_quote(BASE_DIR,'/').'/';
		$out='<table class="retSearch" cellspacing=0 cellpadding=0 border=0><thead><tr><th>';
		$out.=count($dir).' Itens encontrados';
		$out.='</th></tr></thead><tbody>';
		foreach($dir as $k=>$v) {
			$ext='';
			$http=preg_replace($er,'',$v);
			if(is_dir($v)) {
				$type='dir';
				$src='location="'.$this->rebuildGet(array('dir'=>$v)).'"';
			}else {
				$type=$this->getFileTypeExt($v,$ext);
				$src='window.open("'.$this->httpRoot.'/'.$http.'")';
			}
			$class=($k & 1)?'oddLine':'evenLine';
			$out.='<tr class="'.$class.'" fixclass="'.$class.'" onmouseover=\'this.className=this.fixclass+"Over"\' onmouseout=\'this.className=this.fixclass\' onclick=\''.$src.'\'><td>';
			$out.=(SHOW_Icon?'<img src="'.$this->urlIcons.'/'.$type.'.png" border=0>':'').htmlspecialchars($http);
			$out.='</td></tr>';
		}
		$out.='</tbody></table>';
		return $out;
	}
	private function isShowItem($item){
		return eval('return SHOW_'.$item.';');
	}
	private function showHeader($item){
		$o=(int)@$this->sort[$item];
		$ord=$o==4?3:($o==3?0:4);
		$sort=array('sort'=>array_merge(array($item=>$ord),$this->sort));
		$sort['sort'][$item]=$ord;
		if($o==0) {
			$img=$pos='';
			if(isset($this->sort[$item])) unset($this->sort[$item]);
		}elseif($o==4) {
			$img='<img src="'.$this->urlIcons.'/down.gif" border="0" class="sort">';
			$pos='asc';
		} else {
			$img='<img src="'.$this->urlIcons.'/up.gif" border="0" class="sort">';
			$pos='desc';
		}
		if($this->isShowItem($item)) return '<th class="'.$item.'" onmouseover=\'this.className="Over"\' onmouseout=\'this.className=""\' onclick=\'location="'.$this->rebuildGet($sort).'"\'><span class="'.$pos.'">'.$img.$item.'</span></th>';
	}
	private function showOrderDir(){
		if($this->firstDir==0) {
			$img=$pos='';
			$ord=1;
		}elseif($this->firstDir==1) {
			$img='<img src="'.$this->urlIcons.'/down.gif" border="0">';
			$pos='asc';
			$ord=-1;
		} else {
			$img='<img src="'.$this->urlIcons.'/up.gif" border="0">';
			$pos='desc';
			$ord=0;
		}
		return '<div class="orderDir"><a href="'.$this->rebuildGet(array('firstDir'=>$ord)).'">'.$img.'Dir</a></div>';
	}
	private function showItem($item,$value){
		if(!$this->isShowItem($item)) return '';
		if(!$value && $value!==0) $value='&nbsp;';
		elseif(($fn=@$this->macros[$item])) $value=$this->$fn($value);
		
		return '<td class="'.$item.'">'.$value.'</td>';
	}
	private function showSearch(){
		return '<div class="search"><input name="search" value="'.htmlspecialchars($this->search).'"/><img src="'.$this->urlIcons.'/search.png" border="0" onclick="document.getElementsByTagName(\'form\')[0].submit()"></div>';
	}
	private function showItemChecked($value){ 
		return $value?CHAR_checked:CHAR_unchecked;
	}
	private function showItemAttr($attr){
		$g=array('users','group','onwer');
		$a=array('x','w','r');
		$bit=1;
		$out='';
		foreach($g as $group) {
			$gOut='';
			foreach($a as $k) {
				$gOut=($attr&$bit?$k:'-').$gOut;
				$bit*=2;
			}
			$out='<abbr title="'.$group.'" class="'.$group.'">'.$gOut.'</abbr>'.$out;
		}
		if (($attr & 0xC000) == 0xC000) $gOut = 's';// Socket
		elseif (($attr & 0xA000) == 0xA000) $gOut = 'l';// Link simbólico
		elseif (($attr & 0x8000) == 0x8000) $gOut = '-';// Regular
		elseif (($attr & 0x6000) == 0x6000) $gOut = 'b';// Bloco especial
		elseif (($attr & 0x4000) == 0x4000) $gOut = 'd';// Diretório
		elseif (($attr & 0x2000) == 0x2000) $gOut = 'c';// Caractere especial
		elseif (($attr & 0x1000) == 0x1000) $gOut = 'p';// FIFO pipe
		else $gOut = 'u';// Desconhecido
		return '<abbr title="special" class="special">'.$gOut.'</abbr>'.$out;
	}
	private function showItemLink($link){
		$path=BASE_DIR.($this->path?'/'.$this->path:'').'/'.$link;
		if(realpath($path)) $tag='<abbr tilte="Link simbolico" class="link">';
		else $tag='<abbr title="Caminho inválido" class="wrongLink">';
		return $tag.$link.'<abbr>';
	}
}