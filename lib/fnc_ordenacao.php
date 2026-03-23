<?php

/**
 * [description]Select de Ordenação da Busca
 * @param  	Array 	$array 	opcoes de ordenamento]
 * @return	HTML	Bloco ORDENACAO da BUSCA
 */

function fbz_orderby($fbz_order_array){

	global $tpl;

	// var_export($array);

	$i=0;

	$orderby_agrupado = array();

	foreach ($fbz_order_array as $item) {
		$orderby_agrupado[$item['grupo']][] = $item;
	}

	// echo "<pre>";
	// var_export($orderby_agrupado); exit;

	foreach ($orderby_agrupado as $grupo => $item) {
		foreach ($item as $index => $option) {
			$tpl['orderby'][$grupo][] = [
				'value' => $i,
				'label' => $option['label'],
				'selected' => $_GET['ordem']==$i ? 'active selected' : '',
			];
			$i++;
		}
	}

}