<?php
require_once 'common.php';
title('Data List: Ordernar');
?>
<div class="container">
	<div>Existem duas formas para se ordernar sua lista: Forma Direta ou Completa</div>
	
	<h2>Forma Direta</h2>
	
	<div class="img-dataList dl-grd dl-ordbar"></div>
	Na primeira linha onde observamos a lista de campos, cada coluna permite:
	<dl class="dl-horizontal">
		<dt><span class="label label-default">Click</span></dt><dd>Para ordernar apenas um campo</dd>
		<dt><span class="label label-info">Ctrl</span> <span class="label label-default">Click</span></dt><dd>Para ordernar mais de um campo</dd>
		<dt><span class="label label-info">Shift</span> <span class="label label-default">Click</span></dt><dd>Também para ordernar mais de um campo</dd>
	</dl>

	A quantidade de vezes que é feita a ordenação irá mudar o sentido da mesma:
	<dl class="dl-horizontal">
		<dt><div class="img-dataList dl-grdItem dl-ord2"></div></dt><dd>Ordem Crescente [1º click]</dd>
		<dt><div class="img-dataList dl-grdItem dl-ord3"></div></dt><dd>Ordem Decrescente [2º click]</dd>
		<dt><div class="img-dataList dl-grdItem dl-ord1"></div></dt><dd>Sem Ordenação [3º click/nenhum]</dd>
	</dl>

	<h2>Forma Completa</h2>
	<div>
		Clicar no botão Sort <div class="img-dataList dl-btn dl-item dl-btn-order"></div> 
		ou dentro do <div class="img-dataList dl-btn dl-item dl-btn-menu"></div> Menu de Opções em Sort. 
		Isto irá abrir um assistênte que lhe ajudará a ordernar sua lista.
	</div>
	<div>Após isso, irá abrir uma caixa abaixo da barra de Menu de Opções do Data List para escolher na parte esquerda, através do botão [AZ], quais campos serão ordenados.</div>
	<div>Na parte direita, no(s) botão(ões) azul(is), se define se é Crescente/Decrescente a ordem.</div>
	<div>As setas define a prioridade de ordem.</div>
	<div>O Botão [X] Vermelho retira da ordenação o campo.</div>
	<div>Basta confirmar [Order] ou [Cancelar].</div>
	
	<div><img src="img/order.jpg" /></div>
	
</div>
