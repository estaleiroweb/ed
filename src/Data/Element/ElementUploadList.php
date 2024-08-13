<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\Ext\BootstrapDateTimePicker;
use EstaleiroWeb\ED\Screen\OutHtml;
use EstaleiroWeb\ED\Secure\Secure;

class ElementUploadList extends Element {
	protected $typeList = array('File');
	public function __construct($name = '', $value = null, $id = null) {
		$this->protect['pathTarget'] = null;
		$this->protect['pathTargetBackup'] = null;
		$this->protect['request'] = array('uploaded' => [], 'removed' => []);
		$this->protect['files_done'] = array('uploaded' => [], 'removed' => []);
		$this->protect['action_bar'] = null;
		$this->events['accept'] = null;
		$this->displayAttr['file_pattern'] = null;
		$this->displayAttr['type_pattern'] = null;
		$this->displayAttr['file_pattern_checkBy'] = 'glob';
		$this->displayAttr['type_pattern_checkBy'] = 'regexp';
		parent::__construct($name, $value, $id);
		$this->updatable = true;
		//$this->OutHtml->script('validateform','easyData');
	}
	public function __toString() {
		$this->style();
		$attr = $this->makeAttrib() . $this->makeAttribInput();
		$out = '<form role="form" method="post" enctype="multipart/form-data">';
		$out .= '<div class="container"' . $attr . $this->buildStyles() . '>';
		if ($this->form) $out .= $this->action_bar();
		else {
			OutHtml::singleton()->addFormTag();
			$this->update();
			$html = ($this->isEdit() || $this->isDelete()) ? $this->button_submit() : '';
			$out .= $this->action_bar($html);
		}
		$out .= parent::__toString();
		if (($error = $this->error)) $out .= '<p class="bg-danger">' . implode('<br/>', $error) . '</p>';
		if ($this->protect['files_done']['uploaded']) {
			$files = [];
			$arr = (array)$this->protect['files_done']['uploaded'];
			foreach ($arr as $line) $files[] = $line['name'];
			$out .= '<p class="bg-success">Upload feito: ' . implode(', ', $files) . '</p>';
		}
		$files = (array)$this->protect['files_done']['removed'];
		if ($files) {
			$out .= '<p class="bg-warning">Arquivos removidos: ' . implode(', ', $files) . '</p>';
		}
		$out .= $this->list_file();
		$out .= '</div>';
		$out .= '</form>';
		return $out;
	}

	public function action_bar($html = '') {
		if ($this->protect['action_bar']) $html .= $this->protect['action_bar'];
		if ($html) return '<div class="text-right">' . $html . '</div>';
	}
	public function button_submit() {
		return '<button type="submit" class="btn btn-info"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> Submit</button>';
	}
	public function isEdit() {
		//return true;
		if (parent::isEdit()) {
			$s = Secure::$obj;
			if (!$s || ($s->C && $s->U)) return true;
		}
		return false;
	}
	public function isDelete() {
		//return true;
		if (parent::isEdit()) {
			$s = Secure::$obj;
			if (!$s || $s->D) return true;
		}
		return false;
	}
	public function makeContent() {
		return $this->htmlLabel();
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		$this->script();
		$id = $this->makeHtmlAttrId();
		$name = $this->makeHtmlAttrName();
		return $this->htmlLabel() . '
			<div ed-item="uploadList-file-box">
				<div class="input-group">
					<input' . $id . $name . ' type="file" class="form-control"' . $this->makeEvents() . ' />
					<span class="input-group-btn">
						<button type="button" class="btn btn-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
					</span>
				</div>
			</div>';
	}
	public function verifyFile_ext($fileName) {
		$ext = $this->ext;
		if (is_null($ext)) return true;
		preg_match('/\.(^[\.]+)$/', $fileName, $ret);
		$fn = 'check_' . $this->ext_checkBy;
		return $this->$fn(@$ret[1], $ext);
	}
	public function verifyFile_typeFile($file) {
		$ext = $this->ext;
		if (is_null($ext)) return true;
		preg_match('/\.(^[\.]+)$/', $file, $ret);
		$fn = 'check_' . $this->ext_checkBy;
		return $this->$fn(@$ret[1], $ext);
	}

	public function download_file($osFile) {
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
	protected function upload_File($file, $pathTarget) {
		if (!$file['name'] || !$file['tmp_name']) return false;
		if (!$pathTarget) return $file['tmp_name'];
		if ($file['error'] == 0) {
			if (!$this->verifyFile_ext($file['name'])) return $this->setError("Arquivo {$file['name']} não extensão correta");
			if (!$this->verifyFile_typeFile($file['tmp_name'])) return $this->setError("Arquivo {$file['name']} não é do tipo correto");
			$fileTarget = $pathTarget . '/' . $file['name'];
			$this->backup_file($fileTarget);
			if (!@move_uploaded_file($file['tmp_name'], $fileTarget)) return $this->setError("Arquivo {$file['name']} não foi possível para a pasta correta");
		} elseif ($file['error'] == UPLOAD_ERR_INI_SIZE)   return $this->setError("Arquivo {$file['name']} excede o limite definido na diretiva upload_max_filesize do php.ini");
		elseif ($file['error'] == UPLOAD_ERR_FORM_SIZE)  return $this->setError("Arquivo {$file['name']} excede o limite definido em MAX_FILE_SIZE no formulário HTML");
		elseif ($file['error'] == UPLOAD_ERR_PARTIAL)    return $this->setError("Arquivo {$file['name']} foi feito parcialmente o upload");
		elseif ($file['error'] == UPLOAD_ERR_NO_FILE)    return $this->setError("Nenhum arquivo foi enviado");
		elseif ($file['error'] == UPLOAD_ERR_NO_TMP_DIR) return $this->setError("Arquivo {$file['name']}: Pasta temporária ausênte");
		elseif ($file['error'] == UPLOAD_ERR_CANT_WRITE) return $this->setError("Arquivo {$file['name']} Falha em escrever o arquivo em disco");
		elseif ($file['error'] == UPLOAD_ERR_EXTENSION)  return $this->setError("Arquivo {$file['name']} Uma extensão do PHP interrompeu o upload do arquivo. O PHP não fornece uma maneira de determinar qual extensão causou a interrupção");
		else return $this->setError("Arquivo {$file['name']} Ocorreu erro inesperado");

		return $pathTarget . '/' . $file['name'];
	}
	protected function delete_file($file, $pathTarget) {
		if (!$pathTarget) return $file;
		$fullName = $pathTarget . '/' . $file;
		if (!file_exists($fullName)) return;
		$bkp = $this->backup_file($fullName);
		if ($bkp === false) return false;
		if ($bkp || unlink($fullName)) return $file;
		return $this->setError("Não foi possível apagar arquivo $file");
	}
	protected function backup_file($fullName) {
		$pathTargetBackup = $this->pathTargetBackup;
		if (!$pathTargetBackup || !is_file($fullName)) return 0;
		if (!is_dir($pathTargetBackup)) return $this->setError("Diretório de backup '$pathTargetBackup' inválido");

		$newFileName = $pathTargetBackup . '/' . preg_replace('/(\.\w+)$/', '_' . strftime('%Y%m%d%H%M%S') . '\1', basename($fullName));
		if (rename($fullName, $newFileName)) return true;
		return $this->setError("Erro ao tentar fazer backup do arquivo $fullName");
	}
	protected function list_file() {
		if (($path = $this->pathTarget) && is_dir($path)) {
			$out = '';
			$dir = scandir($path);
			$isEdit = $this->isEdit();
			if ($this->isDelete()) {
				$delAll = '<span class="input-group-addon"><input type="checkbox" ed-item="uploadList-select-all"> <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></span>';
				$fn = 'list_file_withDel';
				$name = $this->makeHtmlAttrName('[remove][]');
			} else {
				$delAll = '';
				$fn = 'list_file_withoutDel';
			}
			//$finfo=finfo_open(FILEINFO_MIME_TYPE);
			$source = [];
			foreach ($dir as $file) if (is_file($fullFile = $path . '/' . $file)) {
				$size = filesize($fullFile);
				$source[] = $file;
				//'type'=>htmlentities(finfo_file($finfo, $fullFile),ENT_QUOTES),
				$file = htmlentities($file, ENT_QUOTES);
				$out .= '
				<div class="col-md-4" size="' . $size . '" timestamp="' . filemtime($fullFile) . '" ed-item="uploadList-remove-grp">
					' . $this->$fn($path, $file, $name) . '
				</div>';
			}
			//finfo_close($finfo);
			if ($out) {
				$out .= '<input type="hidden"' . $this->makeHtmlAttrName('[source]') . ' value="' . htmlentities(json_encode($source), ENT_QUOTES) . '" />';
				new BootstrapDateTimePicker();
				return ($this->isEdit() ? '<hr>' : '') . '
				<div class="input-group" ed-item="uploadList-filter-box">
					' . $delAll . '
					<span class="input-group-addon"><input type="checkbox" ed-item="uploadList-show-datetime"> <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
					<span class="input-group-addon"><input type="checkbox" ed-item="uploadList-show-size"> <span class="glyphicon glyphicon-compressed" aria-hidden="true"></span></span>
					<input type="text" placeholder="Filtrar" class="form-control"  ed-item="uploadList-filter" />
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" ed-item="uploadList-clear-filter">
							<span class="glyphicon glyphicon-remove" aria-hidden="true">
						</button>
					</span>
				</div>
				<div>&nbsp;</div>
				<div class="row" ed-item="uploadList-remove-files-box">' . $out . '
				</div>';
			}
		}
		return '';
	}
	protected function list_file_withDel($path, $file, $name) {
		return '<div class="input-group">
				<div class="form-control" ed-item="uploadList-remove-file">' . $file . '</div>
				<span class="input-group-addon">
					<input type="checkbox"' . $name . ' value="' . $file . '" ed-item="uploadList-remove-check" /> <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
				</span>
			</div>';
	}
	protected function list_file_withoutDel($path, $file, $name) {
		return '<div class="form-control" ed-item="uploadList-remove-file">' . $file . '</div>';
	}
	protected function bulkUpload($pathTarget = null, $files = []) {
		if (!$files) return [];

		if (!is_array($files['name'])) foreach ($files as $k => $v) $files[$k] = array($v);

		$uploaded = [];
		foreach ($files['name'] as $k => $v) if (($val = $files['name'][$k]) != '') {
			$item = array(
				'name' => $val,
				'type' => $files['type'][$k],
				'tmp_name' => $files['tmp_name'][$k],
				'error' => $files['error'][$k],
				'size' => $files['size'][$k],
				'fullFile' => $files['tmp_name'][$k],
				'content' => file_get_contents($files['tmp_name'][$k]),
			);
			$up = $this->upload_File($item, $pathTarget);
			if ($up) {
				$item['fullFile'] = $up;
				$uploaded[] = $item;
			}
		}
		/*[file] => Array(
			[name] => TIM_logo.png           or []
			[type] => image/png              or []
			[tmp_name] => /tmp/phpKLYMxK     or []
			[error] => 0                     or []
			[size] => 18997                  or []
		)*/
		return $uploaded;
	}
	protected function bulkDelete($pathTarget = null, $files = []) {
		if (!$files) return [];
		$removed = [];
		foreach ($files as $file) {
			$del = $this->delete_file($file, $pathTarget);
			if ($del) $removed[] = $del;
		}
		return $removed;
	}
	public function update($data = null) {
		($saveTo = $this->get('saveTo')) || ($saveTo = []);

		//Get form properties
		if (($form = $this->form)) {
			$updated = $form->updated;
			$inserted = $form->inserted;
			$deleted = $form->deleted;
		} else {
			$updated = true;
			$deleted = $inserted = false;
		}
		$this->conn = $conn = array_key_exists('conn', $saveTo) ? Conn::dsn($saveTo['conn']) : $this->conn;

		$pathTarget = $this->pathTarget;
		if ($deleted) {
			$this->protect['request']['removed'] = json_decode($this->buildValueByName($this->name . '[source]', $_REQUEST));
		} else {
			$this->protect['request']['removed'] = $this->buildValueByName($this->name . '[remove]', $_REQUEST);
			$this->protect['request']['uploaded'] = $this->buildValueByName($this->name, $_FILES);
		}
		if (!$form) {
			if ($this->protect['request']['removed'] && $this->startEvent('onbeforedelete')) {
				$this->protect['files_done']['removed'] = $this->bulkDelete($pathTarget, $this->protect['request']['removed']);
			}
			if ($this->protect['request']['uploaded'] && $this->startEvent('onbeforeupdate') && $this->startEvent('onbeforeinsert')) {
				$this->protect['files_done']['uploaded'] = $this->bulkUpload($pathTarget, $this->protect['request']['uploaded']);
			}
		}
		$removed = (array)$this->protect['files_done']['removed'];
		$uploaded = (array)$this->protect['files_done']['uploaded'];

		if (!$saveTo || !@$saveTo['table']) {
			if (!$form) {
				if ($removed) $this->startEvent('onafterdelete');
				if ($uploaded) {
					$this->startEvent('onafterupdate');
					$this->startEvent('onafterinsert');
				}
			}
			return true;
		}
		if (!@$saveTo['value']) $saveTo['value'] = 'name';

		if ($removed) {
			foreach ($removed as $k => $file) $removed[$k] = $conn->addQuote($file);
			$sql = 'DELETE FROM ' . $saveTo['table'] . ' WHERE ' . $conn->fieldDelimiter($saveTo['value']) . ' IN (' . implode(', ', $removed) . ')';
			//show($sql);
			$conn->query($sql);
			$removed = null;
			if (!$form) $this->startEvent('onafterdelete');
		}
		if ($deleted || !$uploaded) return true;

		//Buld arrays $key,$tmpInitTable,$whereUpd 
		$whereUpd = $key = $tmpInitTable = $tmpTable = [];
		if (array_key_exists('key', $saveTo)) foreach ($saveTo['key'] as $field => $v) {
			$this->update_list_addPrepare($conn, $field, $v, $key, $tmpInitTable, $whereUpd);
		}
		//Buld arrays $key,$tmpTable,$whereUpd 
		$this->update_list_addPrepareRaw($conn, $saveTo['value'], '$value', $key, $tmpTable, $whereUpd);
		if (array_key_exists('order', $saveTo)) $this->update_list_addPrepareRaw($conn, $saveTo['order'], '$seq', $key, $tmpTable, $whereUpd);

		if (@$saveTo['fields']) foreach ($saveTo['fields'] as $field => $v) if (!array_key_exists($field, $key)) {
			$this->update_list_addPrepareRaw($conn, $field, $v, $key, $tmpTable);
		}

		//Create tmpTable
		$tmp = [];
		foreach ($uploaded as $seq => $source) {
			$value = $source['name'];
			$line = $tmpInitTable;
			foreach ($tmpTable as $field => $fieldMap) {
				$ret = $conn->addQuote($this->update_list_eval($fieldMap, $source, $uploaded, $seq, $value));
				$line[] = $ret . ' as ' . $field;
			}
			$tmp[] = 'SELECT ' . implode(', ', $line);
		}
		//show([$tmpTable,$tmpInitTable]);
		$tmpName = 'tmp_' . __CLASS__ . '_' . time();
		$sql = 'CREATE TEMPORARY TABLE ' . $tmpName . " \n" . implode(" UNION ALL \n", $tmp);
		//show($sql);
		$conn->query($sql);

		//show($source);return true;
		//if($values===false) $values=$this->value;
		//$source=json_decode($this->buildValueByName($this->name.'[source]',$_REQUEST)); //FIXME
		//$values=$source?array_keys($source):[]; //FIXME

		if ($updated || $inserted) {
			$sql = 'INSERT IGNORE ' . $saveTo['table'] . ' (' . implode(',', $key) . ') SELECT * FROM ' . $tmpName;
			//show($sql);
			$conn->query($sql);
		}
		$sql = 'DROP TABLE IF EXISTS ' . $tmpName;
		//show($sql);
		$conn->query($sql);
		if (!$form) {
			$this->startEvent('onafterupdate');
			$this->startEvent('onafterinsert');
		}
		return true;
		/*'saveTo'=>array(
			'table'=>'[db.]table',
			'value'=>'field_seq',
			['conn'=>'connection',]
			['key'=>array(
				'this=([db.]table) field'=>fixed_value,
			),]
			['order'=>'field_seq',]
			['fields'=>array(
				'this=([db.]table) field'=>'"fixed_value ".$value." ".$html." ".$field['name']." ".$seq." ".$source[value][field]',
			),]
		)*/
	}
}
