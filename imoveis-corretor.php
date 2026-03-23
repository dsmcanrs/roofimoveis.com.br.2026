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

// Corretor
// ----------------------------------------------------------------------------

// var_export($_GET); exit;

$nome = str_replace('-','%',$_GET["nome"]);

$corretor = fbz_sql_select(
	"SELECT * FROM usuarios WHERE exibir = 1 AND nome like '$nome%'",
	array(
		// 'debug' => 1
	)
);

$corretor = $corretor[0];

// var_export($corretor); exit;

// fullbiz
$where = " idu = '{$corretor['id']}' ";

// Busca
// ----------------------------------------------------------------------------

// Paginação
$regs_pp 	= 12;
$pg			= is_numeric(get("pg")) ? get("pg") : 1;
$comeca_em	= ($pg-1) * $regs_pp;

// Ordem
$_GET['ordem'] = isset($_GET['ordem']) ? $_GET['ordem'] : 0;
$orderby = $fbz_order_array[$_GET['ordem']]['order'];

$imoveis = fbz_sql_select(
	$fbz_sql_query_imoveis,
	array(
		'where' => $where,
		'limit' => $comeca_em.",".$regs_pp,
		'orderby' => $ordem,
		// 'debug' => 1
	)
);

$total = fbz_sql_select(
	$fbz_sql_query_imoveis,
	array(
		'where' => $where,
		'return' => 'count'
	)
);

// var_export($imoveis);

// Paginação
// ----------------------------------------------------------------------------

if($total>$regs_pp) {
	fbz_paginacao( $pg, $regs_pp, $total );
	// echo "<pre>"; var_export($tpl['paginacao']); exit;
}

// Order By
// ----------------------------------------------------------------------------

parse_str($_SERVER['QUERY_STRING'],$query_parts);
unset($query_parts['ordem']);
$new_query = http_build_query($query_parts);

$tpl['order']['query'] = $new_query;
$tpl['order']['page'] = current_page();
$tpl['order']['selected'] = $fbz_order_array[$_GET['ordem']]['label'];

fbz_orderby($fbz_order_array);

// echo "<pre>"; var_export($tpl['orderby']); exit;

// ----------------------------------------------------------------------------

$titulo = 'Imóveis do Corretor: ' . fbz_ucfirst($corretor['nome']);

// HTML Head
$tpl['site']['title'] = $titulo.', Página '.$pg.' - '.$site['geral']['title'];
$tpl['site']['desc']  = $titulo .' em '.  $site['geral']['nome'];

$tpl['titulo'] = $titulo;
$tpl['pagina'] = $pg;
$tpl['total_pagina'] = ceil($total/$regs_pp);
$tpl['total_imoveis'] = $total;

// Imóveis
// ----------------------------------------------------------------------------

if( sizeof($imoveis)>0 ) {
	foreach ($imoveis as $idxImovel => $cardImovel) {
		include("inc/imovel.php");
		$tpl['imoveis'][$idxImovel] = $cardImovel;
	}
	// echo "<pre>"; var_export($tpl['imoveis']); exit;
}

// Marcadores no Mapa
// ----------------------------------------------------------------------------

if( $template->hasBlock('mapa') ) {

	$marker = array();

	// select para mapa
	$marcadores	= fbz_sql_select(
		$fbz_sql_query_marcadores,
		array(
			'where' => $sql_where,
			'limit' => 1000,
			'cache' => 1800, // 30 minutos
		)
	);

	if( sizeof($marcadores)>0 ) {

		$i=0;
		foreach ($marcadores as $marcador) {
			$marker[$i]['lat'] 		= $marcador['latitude'];
			$marker[$i]['lng'] 		= $marcador['longitude'];
			$marker[$i]['codigo'] 	= $marcador['id'];
			$marker[$i]['label'] 	= fbz_format_valor(fbz_imovel_valor($marcador));
			$marker[$i]['ico'] 		= "img/marker/marker.png";
			$i++;
		}

	}

	// var_export($marker);

	$tpl['mapa']['marcadores'] = json_encode($marker);

}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);