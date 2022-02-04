<?php
class ListFiles extends OverLoadElements {
	protected $protect=array(
		'er_pattern'=>'/.*/',
		'base_dir'=>'.',
		'show_sub_dir'=>true,
		'path'=>'',
	);
	protected $allParameters=array('er_pattern','base_dir');
	
	public function __toString(){
		$base_dir=$this->base_dir;
		if($base_dir[0]!='/') $base_dir=preg_replace(array('/\/\.(?=\/)/','/\/\.$/'),'',dirname($_SERVER['SCRIPT_FILENAME']).'/'.$base_dir);
		if($get=@$_REQUEST['get']) {
			$outHtml=OutHtml::singleton();
			$outHtml->clearAll()->stopBuffer()->organize=false;
			
			$file=$base_dir.'/'.$get;
			($type=`file --mime --brief $file`) || ($type='application/octet-stream');
			
			header('Content-Type: '.$type);
			header('Content-Disposition: attachment; filename="'.basename($get).'"');
			readfile($file);
			return '';
		} 
		$out='';
		$show_sub_dir=$this->show_sub_dir;
		if($show_sub_dir && (($path=@$_REQUEST['path']) || ($path=$this->path))) {
			$path=preg_replace(array('/^\/+/','/\/+$/'),'',$path);
			if($path) {
				$base_dir.='/'.$path;
				$path.='/';
				$out=$this->link_dir(dirname($path),'parent','level-up');
			}
		} else $path=$out='';
		$er_pattern=$this->er_pattern;
		$files=array();
		$sub_dir=array();
		$dir=scandir($base_dir);
		foreach($dir as $item) {
			if($item=='.' || $item=='..' || $item=='index.php') continue;
			if($er_pattern && !preg_match($er_pattern,$item)) continue;
			$os_file="$base_dir/$item";
			$web_file="$path$item";
			if(is_file($os_file)) $files[]=$this->link_file($os_file,$web_file,$item);
			elseif($show_sub_dir) $sub_dir[]=$this->link_dir($web_file,$item);
		}
		//show($dir);
		$out.=implode('', $sub_dir);
		$out.=implode('', $files);
		return $out;
	}
	private function link_file($os_file,$href,$item){
		$mine=`file --mime-type -b $os_file`;
		$m=$mine?explode('/',$mine):array('unknown','unknown');
		switch($m[0]){
			case 'image':        $gly='picture'; break; //camera
			case 'drawing': 
			case 'xgl':          $gly='blackboard'; break;
			case 'audio':        $gly='headphones'; break;
			case 'music': 
			case 'x-music':      $gly='music'; break; //music 
			case 'video':        $gly='film'; break; //film facetime-video
			case 'multipart':    $gly='duplicate'; break;
			case 'www':          $gly='globe'; break;
			case 'x-conference': $gly='earphone'; break;
			case 'chemical':     $gly='glass'; break;
			
			case 'model':        $gly='picture'; break;
			case 'message':      $gly='envelope'; break;
			case 'text': 
			case 'x-world': 
			case 'i-world':      $gly='book'; break;
			case 'application':  
				if($m[1]=='octet-stream') {
					if(substr($os_file,-4)=='.zip') $gly='compressed';
					else $gly='open-file';
				}
				elseif(preg_match('/^(x-)?(iso|nrg|ccd)/',$m[1])) $gly='cd';
				elseif(preg_match('/^(x-)?(rar|tar|bzip2|gzip)/',$m[1])) $gly='compressed';
				else $gly='open-file'; 
				break;
			default: $gly='file';
		}
		$img='<span class="glyphicon glyphicon-'.$gly.'" aria-hidden="true"></span> ';
		$size=$this->human_size(filesize($os_file));
		$time=strftime('%x %X',filemtime($os_file));
		$out='<a href="?get='.htmlentities($href,ENT_QUOTES).'" title="'.$mine.'">'.$img.$item.'</a>';
		return $this->item($out,$size,$time);
	}
	private function link_dir($href,$item,$gly='folder-close'){
		$img='<span class="glyphicon glyphicon-'.$gly.'" aria-hidden="true"></span> ';
		$out='<a href="?path='.htmlentities($href,ENT_QUOTES).'">'.$img.$item.'</a>';
		return $this->item($out);
	}
	private function item($html,$size='',$time=''){
		return "<div class='row'><div class='col-md-6'>$html</div><div class='col-md-3 text-right'>$size</div><div class='col-md-3 text-right'>$time</div></div>\n";
	}
	private function human_size($bytes, $decimals=2){
		$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}
}