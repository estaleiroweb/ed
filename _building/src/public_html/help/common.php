<?php
require_once '../admin/common.php';
function title($title){
	OutHtml::singleton()->style('dataList')->title($title,true);
	$links=array(
		'dataList.php'=>'Resumo',
		'dataList_menu.php'=>'Barra de Menu',
		'dataList_filter.php'=>'Filtros',
		'dataList_sort.php'=>'Ordernar',
		'dataList_showHide.php'=>'Mostra|Ocultar Campos',
		'dataList_nav.php'=>'Navegação',
		'dataList_rec.php'=>'Registros',
	);
	$file=basename($_SERVER['SCRIPT_NAME']);
	print '<div class="container"><ol class="breadcrumb">Data List: ';
	foreach($links as $k=>$v) print '<li>'.($file==$k?$v:"<a href='$k'>$v</a>").'</li>';
	print '</ol>';
	print '<p class="text-warning">(*) Componentes que são acessados apenas se tiver permissão</p>';
	print '</div>';
}
