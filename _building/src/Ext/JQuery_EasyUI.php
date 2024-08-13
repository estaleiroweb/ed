<?php
class JQuery_EasyUI extends ExternalPlugins{
	protected $context='jquery-easyui';
	protected $filesType=array(
		'css'=>'style', 
		'theme'=>'style', 
		'js'=>'script',
		'jsLocation'=>'script',
	);
	/*
	protected $pluings=array(
		'easyui'=>array('js'=>array(),'css'=>array()),
		'accordion',
		'calendar',
		'combo',
		'combobox',
		'combogrid',
		'datagrid',
		'datalist',
		'datebox',
		'dialog',
		'filebox',
		'layout',
		'linkbutton',
		'menubutton',
		'menu',
		'messager',
		'numberbox',
		'pagination',
		'panel',
		'progressbar',
		'propertygrid',
		'searchbox',
		'slider',
		'spinner',
		'splitbutton',
		'switchbutton',
		'tabs',
		'textbox',
		'tooltip',
		'tree',
		'validatebox',
		'window',
jquery.datetimebox.js
jquery.datetimespinner.js
jquery.draggable.js
jquery.droppable.js
jquery.form.js
jquery.mobile.js
jquery.numberspinner.js
jquery.parser.js
jquery.resizable.js
jquery.timespinner.js
jquery.treegrid.js
	);
	*/
	public function __construct(){
		new JQuery_UI;
		parent::__construct();
	}
	public function plugin($plugin){
		$this->style($plugin,'theme');
		$this->script('jquery.'.$plugin,'plugin');
	}
}
