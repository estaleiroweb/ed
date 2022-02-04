<?php
require_once 'common.php';
title('Data List: Resumo');
?>
<div class="container">
	<div class="row">
		<div class="col-xs-12  col-sm-12 col-md-4 col-lg-6">
			<p>A ferramenta <b>Data List</b> é utilizado para exibir relatórios de informações do sistema.</p>
			<p>
				As facilidades encontradas neste componete permite:
					<ul>
						<li>Ordernar</li>
						<li>Filtrar</li>
						<li>Mostrar/Ocultar campos</li>
						<li>Detalhar registros</li>
						<li>Navegar nas páginas</li>
						<li>Copiar informações da célula</li>
						<li>Copiar informações dos campos</li>
						<li>Copiar informações da tabela</li>
						<li>Exportar para Excel</li>
					</ul>
			</p>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-8 col-lg-6">
			<div class="thumbnail" ><img class="img-dataList dl-resume" style="zoom: 60%;" /></div>
		</div>
	</div>
	<h2>Componentes:</h2>
	
	<dl class="dl-horizontal">
		<dt>Botões</dt><dd>
			É a primeira barra de botões no canto superior esquedo trazendo agilidade para determinadas funções. <a href="dataList_menu.php">Veja mais...</a>
			<div class="img-dataList dl-btn dl-btnbar"></div>
		</dd>
		<hr />
		<dt>Quantidades</dt><dd>
			Mostra a quantidade de registros da consulta e início e fim desta página.
			<div class="img-dataList dl-btn dl-btn-records"></div>
		</dd>
		<hr />
		<dt>Limite</dt><dd>
			Define quantos registros serão mostrados por página. 
			<div class="img-dataList dl-btn dl-btn-limit"></div>
		</dd>
		<hr />
		<dt>Navegação</dt><dd>
			Permite mudar de página que está sendo mostrada. 
			<div class="img-dataList dl-nav dl-navbar"></div>
			
			Barra de paginação é um 2º modo de navegar nas páginas vizinhas. <a href="dataList_nav.php">Veja mais...</a>
			<div class="img-dataList dl-grd dl-pgbar"></div>
		</dd>
		<hr />
		<dt>Ordenação</dt><dd>
			Existem duas formas de ordernar os campos: <a href="dataList_sort.php">Veja mais...</a>
			<ol>
				<li>
					<div class="img-dataList dl-btn dl-item dl-btn-order"></div> Botão de ordenação que irá abrir uma caixa para expecificar as ordens
				</li>
				<li>
					Lista de campos que permite a ordenação destes com <span class="label label-default">Click</span> para ordernar um campo <span class="label label-info">Ctrl</span><span class="label label-default">Click</span> ou <span class="label label-info">Shift</span><span class="label label-default">Click</span> para ordernar mais de um campo.
					<div class="img-dataList dl-grd dl-ordbar"></div>
				</li>
			</ol>
		</dd>
		<hr />
		<dt>Filtro</dt><dd>
			Existem duas formas de filtrar os campos: <a href="dataList_filter.php">Veja mais...</a>
			<ol>
				<li>
					<div class="img-dataList dl-btn dl-item dl-btn-filter"></div> Botão de filtro que irá abrir uma caixa para expecificar os filtros
				</li>
				<li>
					Permite filtrar qualquer registro que tenha uma caixa de texto. <a href="dataList_filter.php">Veja mais...</a>
					<div class="img-dataList dl-grd dl-fltbar"></div>
				</li>
			</ol>
		</dd>
		<hr />
		<dt>Registro</dt><dd>
			Conteúdo de um registro. <a href="dataList_rec.php">Veja mais...</a>
			<div class="img-dataList dl-grd dl-linebar"></div>
		</dd>
	</dl>
</div>
