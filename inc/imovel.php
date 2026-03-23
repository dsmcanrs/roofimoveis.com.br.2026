<?php

// echo "<pre>"; var_export($cardImovel); exit;

// Fotos do Imóvel
// ----------------------------------------------------------------------------

$fotos = fbz_sql_select(
	$fbz_sql_query_fotos,
	array(
		'where' => 'registro = '.$cardImovel['id'],
		'limit' => 5,
	)
);

// var_export($fotos);

$cardImovel['foto'] = $fotos[0]['foto_sm'];

foreach ($fotos as $foto) {
	$foto['alt'] = fbz_imovel_alt_title($cardImovel);
	$cardImovel['fotos'][] = $foto;
}

// Dados do Imóvel
// ----------------------------------------------------------------------------

$imovel_url = fbz_imovel_url($cardImovel,$fbz_tipo_url);
$imovel_valor = fbz_imovel_valor($cardImovel);

$imovel_titulo = !empty($cardImovel['titulo']) ? $cardImovel['titulo'] : $cardImovel['categoria'];
$imovel_titulo = fbz_ucfirst($imovel_titulo);

$imovel_descricao = strip_tags($cardImovel['descricao']);
$imovel_descricao = preg_split("/\r\n|\n|\r/", $imovel_descricao, 2)[0];
$imovel_descricao = strlen($imovel_descricao)>250 ? mb_substr($imovel_descricao, 0, 250) .'...' : $imovel_descricao;

$moeda = 'R$ ';
if( stristr($cardImovel['pais'],'unidos') ) $moeda = 'US$ ';
if( stristr($cardImovel['pais'],'portugal') ) $moeda = 'Є ';

if( $cardImovel["empreendimento"]!='' && fbz_strtolower($cardImovel["categoria"])=='condomínio fechado'){
	$cardImovel['mostrar_valores'] = false;
	$cardImovel['mostrar_ficha'] = false;
	$imovel_tipo = $cardImovel["empreendimento"];
	$imovel_bairro = $cardImovel["bairro"];
}else if( $cardImovel["empreendimento"]!='' && fbz_strtolower($cardImovel["categoria"])!='condomínio fechado'){
	$cardImovel['mostrar_valores'] = true;
	$cardImovel['mostrar_ficha'] = true;
	$imovel_tipo = $cardImovel["categoria"];
	$imovel_bairro = $cardImovel["empreendimento"];
}else {
	$cardImovel['mostrar_valores'] = true;
	$cardImovel['mostrar_ficha'] = true;
	$imovel_tipo = $cardImovel["categoria"];
	$imovel_bairro = $cardImovel["bairro"];
}

$cardImovel['url'] = $imovel_url;
$cardImovel['tipo'] = $imovel_tipo;
$cardImovel['titulo'] = $imovel_titulo;
$cardImovel['status'] = fbz_ucfirst($cardImovel['status']);
$cardImovel['categoria'] = fbz_ucfirst($imovel_tipo);
$cardImovel['bairro'] = fbz_ucfirst($imovel_bairro);
$cardImovel['bairro'] = fbz_ucfirst($cardImovel['bairro']);
$cardImovel['cidade'] = fbz_ucfirst($cardImovel['cidade']);
$cardImovel['descricao'] = $imovel_descricao;
$cardImovel['video'] = $cardImovel['youtube']!='' || json_decode($cardImovel['videos']) ? true : false;
$cardImovel['moeda'] = $moeda;
$cardImovel['valor'] = $imovel_valor>0 ? $moeda . fbz_format_valor($imovel_valor) : 'Consulte';
$cardImovel['valor_iptu'] = $cardImovel['valor_iptu']>0 ? $moeda . fbz_format_valor($cardImovel['valor_iptu']) : 0;
$cardImovel['valor_condominio'] = $cardImovel['valor_condominio']>0 ? $moeda . fbz_format_valor($cardImovel['valor_condominio']) : 0;

// echo "<pre>"; var_export($imovel); exit;