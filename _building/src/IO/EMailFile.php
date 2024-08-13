<?php
class EMailFile extends EMailArray {
	public function __construct($filename=null,$data=null) {
		$this->offsetSet($filename,$data); //or $this->offsetSet(null,$filename);
	}
	public function __toString(){
		$message=$this->__invoke();
		$count=count($message);
		if($count==0) return '';
		if($count==1) return $message[0];
		$boundary=md5(date('r', time())).'-mix';
		$separator='--'.$boundary.self::$lf;
		$out='Content-Type: multipart/mixed; boundary="'.$boundary.'"'.self::$lf.self::$lf;
		$out.=$separator;
		$out.=implode($separator,$message);
		$out.='--'.$boundary.'--'.self::$lf;
		return $out;
	}
	public function __invoke(){
		$message=array();
		foreach($this->args as $item) {
			if(is_array($item)) {
				$header='Content-Type: '.$item['type'].'; name="'.$item['filename'].'"'.self::$lf;
				$header.='Content-Transfer-Encoding: base64'.self::$lf;
				$header.='Content-Disposition: attachment'.self::$lf;
				$message[]=$header.self::$lf.chunk_split(base64_encode($item['data'])).self::$lf;
			} else $message[]=$item;
		}
		return $message;
	}
	public function offsetSet($index, $value){                                      // implements by ArrayAccess
		$this->debug();
		if(!$value) return;
		if($index=='raw') return $this->args[]=$value;
		if(is_object($value)) $value=(array)$value;
		if(is_array($value)) {
			foreach($value as $k=>$v) $this->offsetSet($k, $v);
			return ;
		}
		if(!is_string($value)) $value=(string)$value;
		
		if(is_file($value)) {
			$filename=basename($value);
			$value=file_get_contents($value);
		}
		elseif(is_file($index)) {
			$filename=basename($index);
			$value=file_get_contents($index);
		}
		elseif(!$index || is_numeric($index)) return;
		else $filename=basename($index);
		
		$ext=preg_match('/\.([^\.]+)$/',$filename,$ret)?$ret[1]:'txt';
		$type=array_key_exists($ext,MimeType::$content_types)?MimeType::$content_types[$ext]:"application/$ext";
		$this->args[]=array('filename'=>$filename,'type'=>$type,'data'=>$value);
	}
}
