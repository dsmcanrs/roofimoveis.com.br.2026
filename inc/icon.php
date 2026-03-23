<?php

/**
 * Retorna imagem de marcadores do Mapa de acordo com status e tipo
 * @param array imovel
 * @return string imagem
 */

$imovelbox = isset($imovelbox) ? $imovelbox : $imovel ;

$aluguel = strstr($imovelbox['STATUS'],'ALUGUEL') ? true : false;
$cat = $imovelbox['CODIGO_CATEGORIA'];

if (preg_match("/terreno/i", $cat)) {
	$marker = ($aluguel) ? "marker_aluguel_crop.png" : "marker_venda_crop.png" ;
}

else if (preg_match("/comercial|sala|loja/i", $cat)) {
	$marker = ($aluguel) ? "marker_aluguel_mala.png" : "marker_venda_mala.png" ;
}

else if (preg_match("/apartamento|jk|loft/i", $cat)) {
	$marker = ($aluguel) ? "marker_aluguel_predio.png" : "marker_venda_predio.png" ;
}

else if (preg_match("/pavilhao/i", $cat)) {
	$marker = ($aluguel) ? "marker_aluguel_fabrica.png" : "marker_venda_fabrica.png" ;
}

else {
	$marker = ($aluguel) ? "marker_aluguel_casa.png" : "marker_venda_casa.png" ;
}

$marker = "marker.png";

if($tpl->exists("BOX_MARKER")) $tpl->BOX_MARKER = $marker;