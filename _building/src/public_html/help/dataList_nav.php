<?php
require_once 'common.php';
title('Data List: Navegação');
?>
<div class="container">
	<div class="img-dataList dl-nav dl-navbar"></div>
	Permite mudar de página que está sendo mostrada. <a href="dataList_nav.php">Veja mais...</a>
	<dl class="dl-horizontal">
		<dt><div class="img-dataList dl-nav dl-item dl-nav-navFisrt"></div></dt><dd>Vai para primeira página</dd>
		<dt><div class="img-dataList dl-nav dl-item dl-nav-navPrev"></div> </dt><dd>Vai para página anterior</dd>
		<dt><div class="img-dataList dl-nav dl-item dl-nav-navGo"></div>   </dt><dd>Vai para página que escrever</dd>
		<dt><div class="img-dataList dl-nav dl-item dl-nav-navNext"></div> </dt><dd>Vai para página seguinte</dd>
		<dt><div class="img-dataList dl-nav dl-item dl-nav-navLast"></div> </dt><dd>Vai para última página</dd>
	</dl>
	<hr />
	<div class="img-dataList dl-grd dl-pgbar"></div>
	Barra de paginação é um 2º modo de navegar nas páginas vizinhas.
	<dl class="dl-horizontal">
		<dt><div class="img-dataList dl-pgItem dl-pgPrev"></div></dt><dd>Vai para página anterior</dd>
		<dt><div class="img-dataList dl-pgItem dl-pg1"></div>   </dt><dd>Página corrente</dd>
		<dt><div class="img-dataList dl-pgItem dl-pgN"></div>   </dt><dd>Vai para página N</dd>
		<dt><div class="img-dataList dl-pgItem dl-pgNext"></div></dt><dd>Vai para página seguinte</dd>
	</dl>

</div>
