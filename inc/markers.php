<?php

/**
 * Retorna array com marcadores para a pesquisa no mapa
 * @param codigo imovel
 * @return dados do imovel
 */

chdir('../');

include('setup.php');

$busca 		= fbz_sql_busca();
$sql_where 	= $busca[0];
$markers 	= [];

if( isset($_GET['swLat']) && $_GET['neLat'] ){
	$swLat = $_GET['swLat'];
	$neLat = $_GET['neLat'];
	$swLng = $_GET['swLng'];
	$neLng = $_GET['neLng'];
	$sql_geo_where = '';
	$sql_geo_where .= " AND latitude BETWEEN {$swLat} AND {$neLat} ";
	$sql_geo_where .= " AND longitude BETWEEN {$swLng} AND {$neLng} ";
}

// echo $sql_geo_where;

$imoveis = fbz_sql_select( $fbz_sql_query_marcadores,
	array(
		'where' => $sql_where . $sql_geo_where,
		'limit' => '50',
		'orderby' => $orderby,
		// 'debug' => 1
	)
);

// exit;

if( sizeof($imoveis)>0 ) {
	foreach ($imoveis as $idxImovel => $Imovel) {
		$imovel_foto = fbz_sql_select(
			$fbz_sql_query_fotos,
			array(
				'where' => 'registro='.$Imovel['id'],
				'limit' => 1,
				// 'debug' => 1
			)
		);
		$markers[$idxImovel]['foto'] 	= $imovel_foto[0]['foto_sm'];
		$markers[$idxImovel]['lat'] 	= $Imovel['latitude'];
		$markers[$idxImovel]['lng'] 	= $Imovel['longitude'];
		$markers[$idxImovel]['codigo'] 	= $Imovel['id'];
		$markers[$idxImovel]['label'] 	= fbz_format_valor(fbz_imovel_valor($Imovel));
		$markers[$idxImovel]['ico'] 	= "img/marker/marker.png";
	}
}

// echo "<pre>"; var_export($markers); exit;

header('Content-Type: application/json');

echo json_encode($markers);