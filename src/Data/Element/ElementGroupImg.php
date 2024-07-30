<?php 

/*#Tabela Exemplo
CREATE TABLE `tb_Exemplo` (
	`idImg` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da imagem que é relacionada a um caminho',
	`IdTest` int(11) unsigned NOT NULL COMMENT 'Id ou grupo de Ids da tabela de origem',
	`File` varchar(255) DEFAULT NULL COMMENT 'Nome físico do arquivo relativo ao caminho do objeto',
	`Nome` varchar(255) DEFAULT NULL COMMENT 'Nome demonstrativo',
	`Descr` text COMMENT 'Descrição',
	`DtUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Opcional. Data de Atualização do registro',
	`DtGer` timestamp NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Opcional. Data de Criação do registro',
	# Pode inserir quantos campos quiser aqui
	PRIMARY KEY (`idImg`),
	KEY `IdTest` (`IdTest`),
	KEY `File` (`File`),
	KEY `Nome` (`Nome`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

CREATE DEFINER = 'admin'@'%' TRIGGER `tr_tb_Exemplo_before_ins` BEFORE INSERT ON `tb_Exemplo` FOR EACH ROW
BEGIN
	SET NEW.DtGer=NOW();
END;
*/

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Data\Form\FormAlternative4ElementSearch;

class ElementGroupImg extends Element {
	protected $typeList = array('groupimg');
	function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'group_img';
		parent::__construct($name, $value, $id);
		$this->max = 0;
		$this->numRows = 0;
		$this->path = $this->OutHtml->config->imgPro;
		//$this->conn='';
		//$this->db='';
		$this->x = false; //Largura do Thumb
		$this->y = false; //Altura do Thumb
		$this->showNames = true; //Altura do Thumb
		$this->tbl = ''; //tabela onde ficará as imagens
		$this->trKey = array(); //Campo(s) onde guardarão as chaves do form;
		$this->thumb = $this->OutHtml->config->php . '/thumb.php';
		$this->labelDelete = 'X';
		$this->attrDelete = '';
		$this->labelDescr = 'D';
		$this->attrDescr = '';
		$this->attrName = '';
		$this->imgOnClick = $this->id . '.click(this)';
		//$this->imgOnClick="alert({$this->id}.buildSubId(this))";
		$this->error = '';
		$this->style();
		$this->script();
	}
	function __toString() {
		$ret = "<span{$this->makeHtmlAttrId($this->preIdMain)} class='Element {$this->type}{$this->makeClass()}'>";
		if ($this->isEdit()) $ret .= $this->makeControl();
		else $ret .= $this->makeContent();
		$ret .= "</span>";
		$error = $this->error ? '<div>' . $this->error . '</div>' : '';
		return $ret . $error;
	}
	function buildFullPath($createDir = false) {
		$path = "{$this->OutHtml->config->root}/{$this->path}";
		if ($createDir && !is_dir($path)) {
			`mkdir -p "$path"; chmod 777 "$path"`;
			if (!is_dir($path)) $this->error = "Não foi possível criar o diretório '$path'. Contate o administrador.";
		}
		return realpath($path);
	}
	function makeContent() {
		$mss = ($mss = $this->mss) ? "<div class='ElementGroupImg_Mss'>$mss</div>" : '';
		($out = $this->buildHTMLValues()) || ($out = 'sem conteúdo');
		return "{$this->htmlLabel()}<span{$this->buildStyles()}>{$out}$mss</span>";
	}
	function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		//if ($this->action&2) return $this->makeContent(); //Helbert 28/10/2007
		$values = $this->buildHTMLValues(true);
		$id = $this->id;
		$name = $this->OutHtml->htmlSlashes($this->name);
		$dis = !($max = $this->max) || $this->numRows < $max ? '' : ' disabled';
		$mss = ($mss = $this->mss) ? "<div id='{$id}_mss' class='ElementGroupImg_Mss'>$mss</div>" : '';
		return "{$this->htmlLabel()}<div id='{$id}_control' class='ElementGroupImg_Control'{$this->buildStyles()}>\n\t<div id='{$id}_fileBar' class='ElementGroupImg_fileBar'><input name='ElementGroupImg[$id][]' onchange='$id.rebuild()' type='file'$dis /></div>$values\n$mss</div>\n";
	}
	function buildHTMLValues($edit = false) {
		$conn = null;
		$this->getConn($conn);
		if ($conn && $this->db) $conn->select_db($this->db);
		$out = array();
		$path = $this->buildFullPath();
		if ($conn && ($sql = $this->buildSQL())) {
			$res = $this->query($sql);
			$id = $this->id;
			$onClick = ' onclick="' . htmlentities($this->imgOnClick) . '"';
			$onMouse = " onmouseover='$id.over(this)' onmouseout='$id.out(this)'";
			$name = $this->OutHtml->htmlSlashes($this->name);
			$a = array('x', 'y');
			$p = array();
			foreach ($a as $v) if ($value = $this->$v) $p[] = "$v=$value";
			$p[] = 'root=' . $this->OutHtml->config->root;
			$p[] = preg_replace('/\/+/', '/', 'img=/' . $this->path);
			$thumb = $this->thumb . '?' . implode("&", $p);
			$this->numRows = $res->num_rows();
			while ($line = $res->fetch_assoc()) {

				$title = @$line['Descr'] ? " title='{$line['Descr']}'" : '';
				if ($edit) {
					$edit = "\n\t<td id='ElementGroupImg_Edit'>\n";
					$edit .= "\t\t<button class='ElementGroupImg_ButtonDelete' type='button' onclick='$id.remove(this)'      {$this->attrDelete}>{$this->labelDelete}</button><br>\n";
					$edit .= "\t\t<button class='ElementGroupImg_ButtonDescr'  type='button' onclick='$id.changeDescr(this)' {$this->attrDescr} >{$this->labelDescr}</button>\n";
					$edit .= "\t\t<input id='ElementGroupImg_Descr' name='{$name}[Descr][]' type='hidden' value='{$line['Descr']}' />\n\t</td>";
					$textImg = "<input class='ElementGroupImg_Nome'  name='{$name}[Nome][]' type='text' width='100%' value='{$line['Nome']}' {$this->attrName} />";
				} else {
					$edit = '';
					$textImg = $this->showNames ? "<span {$this->attrName}>{$line['Nome']}</span>" : '';
				}
				$textImg .= "<input id='ElementGroupImg_Chk'   name='{$name}[check][]' type='hidden' value='{$line['idImg']}' />";
				$out[$line['idImg']] = "
<table id='ElementGroupImg_Table' border='0' cellspacing='0'><tr>$edit
	<td><div id='ElementGroupImg_Foto'$title$onMouse>\n\t<img id='ElementGroupImg_Img' src='$thumb/{$line['File']}'$onClick /><div>$textImg</div></div></td>
</tr></table>\n";
			}
			$res->close();
		}
		return implode("\n", $out);
	}
	function buildSQL($where = null) {
		if (!$this->tbl || !$this->key || $this->action & 2) return '';
		$where = $this->buildWhere($where);
		$where = $where ? ' WHERE ' . $where : '';
		return "SELECT * FROM {$this->tbl}$where ORDER BY Nome, idImg";
	}
	function buildWhere($where = null, $separador = ' AND ') {
		$where = (array)$where;
		if ($key = $this->buildKey()) {
			$trKey = $this->trKey;
			foreach ($key as $k => $v) $where[] = '`' . (@$trKey[$k] ? $trKey[$k] : $k) . '`="' . $v . '"';
		}
		return implode($separador, $where);
	}
	function buildKey() {
		if (!is_array($this->key)) {
			$key = explode(",", $this->key);
			$tmp = array();
			foreach ($key as $v) if (@$this->form->fields[$v]) $tmp[$v] = $this->form->fields[$v]->value;
			else return array();
			$this->key = $tmp;
		}
		return $this->key;
	}
	public function update($data = null) {
		if (!$this->form) $this->form = new FormAlternative4ElementSearch;
		if (!$this->edit) return false;
		$action = $this->action;
		$conn = null;
		$this->getConn($conn);
		if (!$conn || !($key = $this->buildKey())) return false;
		if ($this->db) $conn->select_db($this->db);
		foreach ($key as $v) if (!$v) return false;

		$out = false;
		//print '<pre>'.print_r($_POST,true).'</pre>';
		if ($action & 11) { //Updating
			if (isset($data['check'])) {
				foreach ($data['check'] as $k => $v) $this->query("UPDATE {$this->tbl} SET Nome='{$conn->escape_string($data['Nome'][$k])}', Descr='{$conn->escape_string($data['Descr'][$k])}' WHERE idImg=$v");
				$whereDel = 'idImg NOT IN ("' . implode('","', $data['check']) . '")';
			} else $whereDel = null;
			if ($this->delImg($whereDel)) $out = true;
			$out = $this->uploadImg();
		} elseif ($action & 4 && $this->delImg()) { //Deleting
			$out = true;
		}
		return $out;
	}
	function delImg($where = null) { //$conn,$path,$where=''
		$this->getConn($conn);
		$sql = $this->buildSQL($where);
		$path = $this->buildFullPath(true);
		if (!$conn || !$sql || !$path) return false;
		$res = $this->query($sql);
		$ids = array();
		while ($line = $res->fetch_assoc()) {
			$ids[] = $line['idImg'];
			@unlink("$path/{$line['File']}");
		}
		$res->close();
		if ($ids) {
			$ids = implode("','", $ids);
			$this->query("DELETE FROM {$this->tbl} WHERE idImg IN ('$ids')");
			$this->query("ALTER TABLE {$this->tbl} AUTO_INCREMENT=1");
		}
		return $conn->affected_rows() > 0;
	}
	function uploadImg($data = array()) {
		if (!$_FILES) return true;
		$this->getConn($conn);
		$path = $this->buildFullPath(true);
		$keys = $this->buildWhere(null, ',');
		$keys = $keys ? ',' . $keys : '';
		$ids = array();
		$mss = array();
		$idObj = $this->id;
		foreach ($_FILES as $key => $value) {
			$value['error'][$idObj] = (array)$value['error'][$idObj];
			$value['name'][$idObj] = (array)$value['name'][$idObj];
			$value['tmp_name'][$idObj] = (array)$value['tmp_name'][$idObj];
			$value['size'][$idObj] = (array)$value['size'][$idObj];
			$value['type'][$idObj] = (array)$value['type'][$idObj];
			foreach ($value['error'][$idObj] as $k => $v) {

				if ($value['error'][$idObj][$k] != 0) {
					if ($value['name'][$idObj][$k]) $mss[] = "Upload Erro de '{$value['name'][$idObj][$k]}'";
					continue;
				}

				$this->query("INSERT {$this->tbl} (`File`) VALUES (NOW())");
				$id = $conn->insert_id();
				if ($id) {
					$parts_path = pathinfo($value['name'][$idObj][$k]);
					$ext = isset($parts_path["extension"]) ? ".{$parts_path["extension"]}" : '';
					$file = $id . $ext;
					if ($ret = @move_uploaded_file($value['tmp_name'][$idObj][$k], $path . '/' . $file)) {
						$this->query("UPDATE {$this->tbl} SET `File`='{$conn->escape_string($file)}',Nome='{$conn->escape_string($value['name'][$idObj][$k])}',Descr=CONCAT('Tamanho: {$value['size'][$idObj][$k]}\\nData: ',NOW())$keys WHERE idImg=$id");
						$ids[] = $id;
					} else {
						$this->query("DELETE FROM {$this->tbl} WHERE idImg=$id");
						$mss[] = "Erro ao copiar '{$value['name'][$idObj][$k]}' para a pasta '$path'";
						$id = 0;
					}
				} else $mss[] = "Insert Erro de '{$value['name'][$idObj][$k]}'";
				if (!$id) $this->query("ALTER TABLE {$this->tbl} AUTO_INCREMENT=1");
			}
		}
		$this->mss .= implode("<br>", $mss);
		return !$mss;
	}
}
