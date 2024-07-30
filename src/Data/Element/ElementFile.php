<?php

namespace EstaleiroWeb\ED\Data\Element;

class ElementFile extends Element {
	protected $typeList = array('File');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'File';
		$this->displayAttr['pathTarget'] = null;
		$this->displayAttr['onMoveRename'] = null; //true|1=rename to name file, 2=rename to tmp file
		$this->displayAttr['prefix_file'] = null;  //if onMoveRename = 2
		//$this->displayAttr['ext']=null;
		//$this->displayAttr['extTypeCheck']='match';
		$this->inputAttr['accept'] = null;
		$this->inputAttr['multiple'] = null;
		$this->inputAttr['capture'] = null;
		//$this->protected['extTypesCheck']=array('in','regexp','glob','smart');
		parent::__construct($name, $value, $id);
		//$this->OutHtml->script('validateform','ed');
	}
	function makeContent() {
		//show($this->value);
		$val = $this->value;
		if (is_array($val)) {
			if (array_key_exists('name', $val)) $val = $val['name'];
			else {
				$out = array();
				foreach ($val as $v) if (array_key_exists('name', $v)) $out[] = $v['name'];
				$val = implode('; ', $out);
			}
		}
		return $this->htmlLabel() . "<span{$this->makeHtmlAttrId()}{$this->buildStyles()}{$this->makeAttrib()}>" . $val . '</span>';
		//return '';
		//if($this->toPrint==2) return '';
	}
	function setAccept($type) {
		if (is_array($type)) $type = implode(',', $type);
		$this->set('accept', $type);
		//file_extension (audio|video|image|application)/*
		//image/png, image/jpeg
		//accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
	}
	function makeControl($moreEvents = array(), $moreAttr = array(), $tp = 'text') {
		$this->script();
		$out = $this->htmlLabel();
		$out .= '<input type="file"';
		$out .= $this->makeHtmlAttrId();
		$out .= $this->makeHtmlAttrName($this->multiple ? '[]' : '');
		$out .= $this->makeAttrib($moreAttr);
		$out .= $this->makeAttribInput();
		$out .= $this->buildStyles();
		$out .= $this->makeEvents($moreEvents);
		//$out.=' accept="image/png, image/jpeg"'; 
		//accept, capture, files, multiple

		//$out.=' accept="application/msword"';
		//$v=$this->value;
		//if(is_array())
		//$value=htmlspecialchars($this->inputformat?$this->format($this->inputformat,$v):$v,ENT_QUOTES);
		$out .= ' />';
		return $this->outControl($out);
	}
	function testFile($osFile) {
		($type = `file --mime --brief $osFile`) || ($type = 'application/octet-stream');
	}
	public function update($data = null) {
		if (!array_key_exists($this->name, $_FILES)) return;
		$pathTarget = $this->pathTarget;
		$onMoveRename = (int)$this->onMoveRename;
		$prefix_file = $this->prefix_file;
		if ($onMoveRename && !$pathTarget) $pathTarget = ini_get('upload_tmp_dir'); //$pathTarget=sys_get_temp_dir();
		$pathTarget = preg_replace('|/{2,}|', '/', $pathTarget . '/');
		$files = (array)$_FILES[$this->name]['name'];
		foreach ($files as $k => $file) {
			$file = array('name' => $file);
			$arr = array('type', 'tmp_name', 'error', 'size',);
			foreach ($arr as $i) {
				$v = (array)$_FILES[$this->name][$i];
				$file[$i] = $v[$k];
			}
			if (!$file['error']) {
				if ($pathTarget) {
					if (!is_dir($pathTarget)) mkdir($pathTarget, '0777', true);
					if (!$onMoveRename) $toFile = $pathTarget . basename($file['tmp_name']);
					elseif ($onMoveRename === 2) $toFile = tempnam($pathTarget, $prefix_file);
					else $toFile = $pathTarget . $file['name'];
					if ($file['tmp_name'] != $toFile) {
						if (is_file($toFile)) unlink($toFile);
						if (rename($file['tmp_name'], $toFile)) $file['tmp_name'] = $toFile;
					}
				}
				//Save data in TargetTable
			}
			$files[$k] = $file;
		}
		$this->value = $files;
		return $this;
	}
	public function detect_mime() {
		$files = $this->value;
		//show($files['tmp_name']);
		foreach ($files as $k => $item) if ((($f = @$item['tmp_name']) && is_file($f)) || (($f = @$item['name']) && is_file($f))) {
			preg_match('/(\S+);\s*charset=(\S+)/', `file -bi {$f}`, $ret);
			$files[$k]['mime'] = ['type' => @$ret[1], 'charset' => strtoupper(@$ret[2])];
			$t1 = explode('/', $files[$k]['type']);
			$t2 = explode('/', $files[$k]['mime']['type']);
			if ($t1[0] != $t1[1] && $t1[0] == 'text') $files[$k]['mime']['charset'] = mb_detect_encoding(file_get_contents($f));
		}
		$this->value = $files;
		return $this;
	}
	public function clear() {
		$files = (array)$this->value;
		if (array_key_exists('tmp_name', $files)) {
			if (is_file($files['tmp_name'])) unlink($files['tmp_name']);
		} else foreach ($files as $file) if (is_file($file['tmp_name'])) unlink($file['tmp_name']);
		$this->value = null;
		return $this;
	}
	public function save() {

		$path = $this->pathTarget;
		$files = $_FILES[$this->name];
		foreach ($files as $k => $v) $files[$k] = (array)$v;
		/*$files=array(//Exemplo 
			'name' => array('TIM_logo.png','Banco de Hosras.xls','boafoto_carro.pdf',),
			'type' => array('image/png','application/vnd.ms-excel','application/pdf',),
			'tmp_name' => array('/tmp/phpKLYMxK','/tmp/phpPuootS','/tmp/phpH27joA',),
			'error' => array(0,0,0,),
			'size' => array(18997,42496,533671,),
		)*/
	}
	function download2($osFile) {
		if (is_file($osFile)) {
			($type = `file --mime --brief $osFile`) || ($type = 'application/octet-stream');
			$filename = basename($osFile);
			header('Content-Type: ' . $type);
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			readfile($osFile);
		} else {
			header('Content-Type: text/plain');
			print 'Arquivo não encontrado';
		}
		exit;
	}
}
