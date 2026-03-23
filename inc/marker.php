<?php

/**
 * Retorna imagem de marcadores do Mapa de acordo com status e tipo
 * @param codigo imovel
 * @return dados do imovel
 */

if( empty($_GET['codigo']) ) exit;

chdir('../');

include('setup.php');

$codigo = get("codigo");

$imovelbox = fbz_sql_select(
	$fbz_sql_query_imoveis, array( 'where' => "id = $codigo" )
);

$imovelbox = $imovelbox[0];

// var_export($imovelbox); exit;

$Imovel = array();

// ---

$imovel_foto = fbz_sql_select(
	$fbz_sql_query_fotos,
	array(
		'where' => 'registro='.$imovelbox['id'],
		'limit' => 1,
		// 'debug' => 1
	)
);

// var_export($imovel_foto); exit;

$altl = fbz_imovel_alt_title($imovelbox);

$Imovel['foto'] = $imovel_foto[0]['foto_sm'];

// ---

$area = '';

if ($imovelbox['area_privativa']>0) $area = $imovelbox['area_privativa']."m²";
else if ($imovelbox['area_total']>0) $area = $imovelbox['area_total']."m²";
else if ($imovelbox['area_construida']>0) $area = $imovelbox['area_construida']."m²";

$Imovel['area'] = $area;

// ---

$price = fbz_imovel_valor($imovelbox);

$Imovel['valor'] = $price==0 ? 'Consulte' : fbz_format_valor($price);

// ---

$Imovel['url'] 			= fbz_imovel_url($imovelbox,$fbz_tipo_url);
$Imovel['codigo'] 		= $imovelbox['codigo'];
$Imovel['categoria'] 	= $imovelbox["categoria"];
$Imovel['banheiros'] 	= $imovelbox['banheiro']>0 ? $imovelbox['banheiros'] : 'hidden';
$Imovel['dormitorios'] 	= $imovelbox['dormitorios']>0 ? $imovelbox['dormitorios'] : 'hidden';
$Imovel['suites'] 		= $imovelbox['suites']>0 ? $imovelbox['suites'] : 'hidden';
$Imovel['vagas'] 		= $imovelbox['vagas']>0 ? $imovelbox['vagas'] : 'hidden';

$return = json_encode($Imovel);

echo $return;