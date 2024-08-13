<?php

namespace EstaleiroWeb\ED\Data\Element;

use EstaleiroWeb\ED\Db\Conn\Conn;
use EstaleiroWeb\ED\Ext\Bootstrap;

class ElementList2List extends Element {
	public function __construct($name = '', $value = null, $id = null) {
		$this->displayAttr['ed-element'] = 'list2list'; {
			$this->displayAttr = array_merge($this->displayAttr, array(
				'data-element' => null,
				'swapBox' => null,
				'sortBox' => null,
				'sortBoxFrom' => null,
				'moveOnSelect' => null,
				'preserveSelectionOnMove' => null,
				'showFilter' => null,
				'showButtons' => null,
				'filterOn' => null,
				'filterTarget' => null,
				'filterSource' => null,
				'filterOn' => null,
				'filterTarget' => null,
				'filterSource' => null,
				'tipText_move' => null,
				'tipText_moveAll' => null,
				'tipText_remove' => null,
				'tipText_removeAll' => null,
				'tipText_orderUp' => null,
				'tipText_orderDown' => null,
				'tipText_filter' => null,
				'tipText_clearFilter' => null,
				'placeHolder_filter' => null,
				'infoText_all' => null,
				'infoText_filtered' => null,
				'infoText_empty' => null,
				'htmlButton_move' => null,
				'htmlButton_moveAll' => null,
				'htmlButton_remove' => null,
				'htmlButton_removeAll' => null,
				'htmlButton_orderUp' => null,
				'htmlButton_orderDown' => null,
				'htmlButton_clearFilter' => null,
			));
		}
		parent::__construct($name, $value, $id);
		//$this->isDelete=true;
		//$this->updated=true;
		$this->updatable = true;
	}
	public function makeControl($moreEvents = [], $moreAttr = [], $tp = 'text') {
		new Bootstrap();
		$this->script();

		$this->displayAttr['data-element'] = 'list2list';
		$this->attr('class', null, true);

		$options = array();
		$maxWidth = 0;
		$source = $this->makeSourceList($targetValue);
		$value = $this->buildValues($targetValue);
		if ($this->preserveSelectionOnMove) while ($value) {
			$key = array_shift($value);
			$options[] = '<option value="' . $key . '" selected>' . $this->buildOpitonHtml($source, $key, $maxWidth) . '</option>';
		}
		else foreach ($value as $key) {
			$options[] = '<option value="' . $key . '" selected>' . $this->buildOpitonHtml($source, $key, $maxWidth) . '</option>';
			if (array_key_exists($key, $source)) unset($source[$key]);
		}
		foreach ($source as $key => $v) $options[] = '<option value="' . $key . '">' . $this->buildOpitonHtml($source, $key, $maxWidth) . '</option>';
		$this->makeAutoWidth($maxWidth);

		$att = $this->makeHtmlAttrName() . $this->makeHtmlAttrId() . $this->makeEvents() . $this->buildStyles() . $this->makeAttrib() . $this->makeAttribInput();
		return $this->htmlLabel() . "<select multiple$att>\n\t" . implode("\n\t", $options) . "</select>"; //{$this->buildLinks()}
	}
	public function makeContent() {
		$options = array();
		$maxWidth = 0;
		$source = $this->makeSourceList($targetValue);
		$value = $this->buildValues($targetValue);
		//show($value);
		$htmlValue = ' value="' . htmlspecialchars(json_encode($value), ENT_QUOTES) . '"';
		if ($value) {
			if (!is_array($value)) $value = preg_split('/\s*[,;]\s*/', $value);
			while ($value) $options[] = '<div>' . $this->buildOpitonHtml($source, array_shift($value), $maxWidth) . '</div>';
		} else {
			$options[] = '<div>' . $this->buildOpitonHtml(array('&nbsp;'), 0, $maxWidth) . '</div>';
		}
		$this->makeAutoWidth($maxWidth);
		$this->style['overflow'] = 'auto';
		if (!$this->height && !@$this->style['height']) {
			$this->style['max-height'] = '82px';
			$this->style['height'] = 'auto';
		}

		$att = $this->makeHtmlAttrId() . $this->buildStyles() . $this->makeAttrib() . $htmlValue;
		return $this->htmlLabel() . "<div$att>\n\t" . implode("\n\t", $options) . "</div>"; //{$this->buildLinks()}
	}
	public function getValue() {
		$value = parent::getValue();
		if (is_null($value) || $value == '' || @$this->form->isActionInsert()) $value = array();
		elseif (!is_array($value)) $value = preg_split('/\s*[,;]\s*/', $value);
		return $value;
		//$value=$this->get('value');
		//return is_array($value)?implode(',',$value):$value;
	}

	protected function buildValues($targetValue = null) {
		$value = $this->value;
		$target = $this->get('saveTo');
		if (($form = $this->form)) {
			if ($form->isActionInsert()) return $value;
			$conn = array_key_exists('conn', $target) ? Conn::dsn($target['conn']) : $form->conn;
		} else {
			$conn = array_key_exists('conn', $target) ? Conn::dsn($target['conn']) : $this->conn;
		}
		//if(is_array($value)) return $value;
		if (!$value && $target && @$target['table']) {
			if (!@$target['value']) $target['value'] = $targetValue;
			if ($target['value']) {
				$value = array();
				$where = array();
				if (array_key_exists('key', $target)) foreach ($target['key'] as $field => $v) $where[] = $conn->fieldCompareValue($field, $v);
				$where = $where ? ' WHERE ' . implode(' AND ', $where) : '';
				$order = @$target['order'] ? ' ORDER BY ' . $conn->fieldDelimiter($target['order']) : '';
				$sql = 'SELECT ' . $conn->fieldDelimiter($target['value']) . ' FROM ' . $target['table'] . $where . $order;
				$res = $conn->query($sql);
				while ($line = $res->fetch_row()) $value[] = $line[0];
				$res->close();
				/*'target'=>array(
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
				$this->set('value', $value);
			}
		}
		//elseif(!is_array($value)) $value=preg_split('/\s*[,;]\s*/',$value);
		return $value;
	}
	public function update($values = false) {
		return $this->update_list($values);
	}
}
