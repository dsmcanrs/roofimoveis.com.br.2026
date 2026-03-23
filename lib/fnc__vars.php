<?php

function post($str,$default='') {
  if (isset($_POST[$str])) {
    return $_POST[$str];
  } else {
    return $default;
  }
}

function get($str,$default='') {
  if (isset($_GET[$str])) {
    return $_GET[$str];
  } else {
    return $default;
  }
}

function var_to_array($input, $delimiter = ",") {

    $result = [];

    if (empty($input)) {
        return $result;
    }

    if (is_array($input)) {
        return $input;
    }

    if (empty($delimiter)) {
        return $result;
    }

    $input = rtrim($input, $delimiter);
    $result = explode($delimiter, $input);

		// Remove elementos vazios do array
    return array_filter($result, 'strlen'); 

}

/**
 * Array Search Multidimensional
 * @param needle 	string para procura
 * @param haystack 	array
 * @return bool
 */

function fbz_array_search($needle, $haystack) {
	foreach($haystack as $key => $value) {
		// var_export($value); exit;
		$current_key = $key+1;
		if( !is_array($value) && strstr($value,$needle) ) {
			return $current_key;
		}
		if( is_array($value) && fbz_array_search($needle,$value) ) {
			return $current_key;
		}
	}
	return 0;
}

/**
 * Filtra valores duplicados em array
 * Usado para filtrar Categorias, Cidades daquela merda do Vista
 */
function array_unique_values(array $array, string $chave): array {

    $valoresVistos = [];
    $arrayUnico = [];

    foreach ($array as $item) {
        // Verifica se o valor da chave não está vazio e se ainda não foi visto
        if (!empty($item[$chave]) && !in_array($item[$chave], $valoresVistos)) {
            $valoresVistos[] = $item[$chave];
            $arrayUnico[] = $item;
        }
    }

    return $arrayUnico;

}