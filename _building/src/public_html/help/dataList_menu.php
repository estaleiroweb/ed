<?php
require_once 'common.php';
title('Data List: Barra de Menu');
?>
<div class="container">
	<p class="text-warning">(**) Copia para área de transferência utilizando
		<span class="label label-default">Click</span> CSV, 
		<span class="label label-info">Ctrl</span> <span class="label label-default">Click</span> CSV (TAB), 
		<span class="label label-info">Shift</span> <span class="label label-default">Click</span> JSON, 
		<span class="label label-info">Ctrl</span> <span class="label label-info">Shift</span> <span class="label label-default">Click</span> Planilha XML2003
	</p>
	
	<div class="img-dataList dl-btn dl-btnbar"></div>
	É a primeira barra de botões no canto superior esquedo trazendo agilidade para determinadas funções.

	<h2><div class="img-dataList dl-btn dl-item dl-btn-menu"></div> Menu de Opções</h2>
	<div class="row">
		<div class="col-xs-12  col-sm-6 col-md-4 col-lg-3">
			<img src="img/menu.jpg" />
		</div>
		<div class="col-xs-12 col-sm-6 col-md-8 col-lg-9">
			<p>É um resumo de algumas as funções da ferramenta Data List:</p>
			<dl class="dl-horizontal">
				<dt>New</dt><dd>Adiciona um novo registro*</dd>
				<dt>Filter</dt><dd>Acessa a caixa de Filtro. <a href="dataList_filter.php">Veja mais...</a></dd>
				<dt>Sort</dt><dd>Acessa a caixa de Ordenação. <a href="dataList_sort.php">Veja mais...</a></dd>
				<dt>Show/Hide Columns</dt><dd>Acessa a caixa de Mostrar/Ocultar Registros. <a href="dataList_showHide.php">Veja mais...</a></dd>
				<dt>Show/Hide Filter Line</dt><dd>Mostrar/Ocultar A linha de Filtros</dd>
				<dt>Copy Table</dt><dd>Copia toda a tabela **</dd>
				<dt>Copy field names table</dt><dd>Copia campos da tabela e metadados **</dd>
				<dt>Copy field names</dt><dd>Copia nome dos campos **</dd>
				<dt>Copy URL</dt><dd>Coipa URL <span class="label label-info">Ctrl</span> <span class="label label-info">Shift</span> <span class="label label-info">L</span></dd>
				<dt>Export to Excel</dt><dd>Exporta a página para Excel</dd>
				<dt>Export All to Excel</dt><dd>Exporta toadas as páginas para Excel</dd>
				<dt>Reset</dt><dd>Recompoe a Lista ao Início</dd>
			</dl>
		</div>
	</div>
	
	<h2><div class="img-dataList dl-btn dl-item dl-btn-add"></div> Adicionar</h2>
	<p>Adicionar um registro* na consulta em amostra</p>
	
	<h2><div class="img-dataList dl-btn dl-item dl-btn-order"></div> Ordenar Campos</h2>
	<p>Acessa a caixa de Ordenação. <a href="dataList_sort.php">Veja mais...</a></p>
	
	<h2><div class="img-dataList dl-btn dl-item dl-btn-fields"></div> Mostrar/Ocultar Campos</h2>
	<p>Acessa a caixa de Mostrar/Ocultar Registros. <a href="dataList_showHide.php">Veja mais...</a> </p>
	
	<h2><div class="img-dataList dl-btn dl-item dl-btn-filter"></div> Filtrar</h2>
	<p>Acessa a caixa de Filtro. <a href="dataList_filter.php">Veja mais...</a></p>
</div>
