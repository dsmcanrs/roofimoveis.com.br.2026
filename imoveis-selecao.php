<?php

include("inc/setup.php");

// Template Start
// ----------------------------------------------------------------------------

$tpl = [];
$latte = new Latte\Engine;
$TemplatePath = template_path(current_page());
$template = $latte->createTemplate($TemplatePath);
$latte->setTempDirectory('cache');

include("inc/header.php");

// Busca
// ----------------------------------------------------------------------------

// var_export($_GET['selecao']); exit;

$selecao = preg_split("/[,\-_]/",$_GET['selecao']);

$sql_where = " publicado = 1 AND ";

foreach( $selecao as $codigo ){
	$codigo = sql_replace($codigo);
	$sql_where .= " codigo='$codigo' OR ";
}

$sql_where = rtrim($sql_where,' OR ');

// echo $sql_where;

$imoveis = fbz_sql_select(
	$fbz_sql_query_imoveis, array(
		'where' => $sql_where,
		// 'debug' => 1
	)
);

// die($imoveis);

$total = fbz_sql_select(
	$fbz_sql_query_imoveis, array(
		'where' => $sql_where,
		'return' => 'count'
	)
);

// Imóveis Selecionados
// ----------------------------------------------------------------------------

$titulo = 'Imóveis Selecionados';

// HTML Head
$tpl['site']['title'] = $titulo.', Página '.$pg.' - '.$site['geral']['title'];
$tpl['site']['desc']  = $titulo .' em '.  $site['geral']['nome'];

$tpl['titulo'] = $titulo;
$tpl['total_imoveis'] = $total;

if( sizeof($imoveis)>0 ) {
	foreach ($imoveis as $idxImovel => $cardImovel) {
		include("inc/imovel.php");
		$tpl['imoveis'][$idxImovel] = $cardImovel;
	}
	// echo "<pre>"; var_export($tpl['imoveis']); exit;
}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);