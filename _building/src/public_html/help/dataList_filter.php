<?php
require_once 'common.php';
title('Data List: Filtrar');
?>
<div class="container">
	<div class="img-dataList dl-grd dl-fltbar"></div>
	<div>Existem duas formas de fazer um filtro, mas primeiro é necessário escolher em qual campo você deseja filtrar selecionando a caixa de filtro correspodente:</div>
	<div><div class="img-dataList dl-grdItem dl-flt"></div></div>
	<ol>
		<li>Pelo Assistênte
			<ul>
				<li>
					Clicar no botão de Filtro <div class="img-dataList dl-btn dl-item dl-btn-filter"></div> 
					ou dentro do <div class="img-dataList dl-btn dl-item dl-btn-menu"></div> Menu de Opções em Filter. 
					Isto irá abrir um assistênte que lhe ajudará a criar filtros para sua pesquisa.
				</li>
				<li>
					Na caixa de Filtro [EM CONSTRUÇÃO], escolher o formato/expressão de como ser filtrada
				</li>
			</ul>
		</li>
		<li>Diretamente nas caixas de Filtro utilizando o padrões e separadores detalhados abaixo. Veja os exemplos.</li>
	</ol>
	
	<h3>Padrões:</h3>
	<dl class="dl-horizontal">
		<dt>*  </dt><dd>Coringa qualquer caracter de qualquer tamanho (0-n)</dd>
		<dt>?  </dt><dd>Coringa qualquer caracter de tamanho = 1</dd>
		<dt>=  </dt><dd>Igualdade [Opcional]</dd>
		<dt>== </dt><dd>Igualdade [Opcional]</dd>
		<dt>!= </dt><dd>Não Igualdade</dd>
		<dt>!  </dt><dd>Negação</dd>
		<dt>&gt;  </dt><dd>Maior que</dd>
		<dt>&lt;  </dt><dd>Menor que</dd>
		<dt>&gt;= </dt><dd>Maior igual que</dd>
		<dt>&lt;= </dt><dd>Menor igual que</dd>
		<dt>(ereg)</dt><dd>Expressão Regular</dd>
		<dt>(regexp)</dt><dd>Expressão Regular</dd>
		<dt>(!ereg)</dt><dd>Negando Expressão Regular</dd>
		<dt>(!regexp)</dt><dd>Negando Expressão Regular</dd>
		<dt>(nereg)</dt><dd>Negando Expressão Regular</dd>
		<dt>(nregexp)</dt><dd>Negando Expressão Regular</dd>
	</dl>

	<h3>Separadores:</h3>
	<dl class="dl-horizontal">
		<dt>| </dt><dd>Ou</dd>
		<dt>& </dt><dd>E</dd>
	</dl>

	<h3>Ex:</h3>
	<dl class="dl-horizontal">
		<dt>*    </dt><dd>Qualquer coisa</dd>
		<dt>	 </dt><dd>Vazio [sem caracter]</dd>
		<dt>!    </dt><dd>Não Vazio</dd>
		<dt>a*   </dt><dd>Tudo que começa com "a"</dd>
		<dt>*a   </dt><dd>Tudo que termina com "a"</dd>
		<dt>!a*  </dt><dd>Tudo que não começa com "a"</dd>
		<dt>!*a  </dt><dd>Tudo que não termina com "a"</dd>
		<dt>*a*  </dt><dd>Tudo que contém "a"</dd>
		<dt>!*a* </dt><dd>Tudo que não contém "a"</dd>
		<dt>a*|*a</dt><dd>Tudo que começa ou termina com "a"</dd>
		<dt>a*&*a</dt><dd>Tudo que começa e termina com "a"</dd>
		<dt>(regexp)^a.*\d$</dt><dd>Tudo que começa com "a" e termina com numero</dd>
	</dl>
</div>
