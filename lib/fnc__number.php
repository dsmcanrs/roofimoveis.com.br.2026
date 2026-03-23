<?php

/**
 * Formatação de Moeda
 * @param 	float 	$valor 	valor
 * @return 	int 	$casas 	quantas casas decimais depois da virgula
 * @return 	string 	$prefix prefixo da moeda
 * @return 	string 	$sufix  sei la
 */

function fbz_format_valor($valor, $casas=2) {
	if ($casas!=2){
		$return = number_format($valor,$casas,',','.');
	}else{
		$return = number_format($valor,2,',','.');
	}
	return $return;
}

/**
 * Retorna valor no padrão mysql
 * @param float $valor - valor moeda
 * @return float valor
 */

function mysql_moeda($valor){
  $valor = str_replace(".","",$valor);
  $valor = str_replace(",",".",$valor);
  return trim($valor);
}
