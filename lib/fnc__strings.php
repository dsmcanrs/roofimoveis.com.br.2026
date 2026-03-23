<?php

function fbz_strtolower($str) {
	if (function_exists('mb_strtolower')) {
		$str = mb_strtolower($str, 'UTF-8');
	} else {
		$str = strtolower($str);
	}
	return $str;
}

function fbz_strtoupper($str) {
	if (function_exists('mb_strtoupper')) {
		$str = mb_strtoupper($str, 'UTF-8');
	} else {
		$str = strtoupper($str);
	}
	return $str;
}

function fbz_substr($str,$start=0,$length=null) {
	if (function_exists('mb_substr')) {
		$str = mb_substr($str,$start,$length, 'UTF-8');
	} else {
		$str = substr($str,$start,$length);
	}
	return $str;
}

/**
 * Extrai o primeiro parágrafo de uma string.
 * Se for HTML, retorna o primeiro <p>...</p>.
 * Se for texto puro, retorna até a primeira quebra dupla de linha.
 * @param string $str
 * @return string
 */
function fbz_first_paragraph($str) {
    // Verifica se contém tag <p>
    if (preg_match('/<p\b[^>]*>(.*?)<\/p>/is', $str, $matches)) {
        return $matches[0]; // HTML: retorna o primeiro <p>
    }
    // Texto puro: normaliza quebras de linha
    $text = str_replace(["\r\n", "\r"], "\n", $str);
    $parts = explode("\n\n", trim($text));
    return isset($parts[0]) ? trim($parts[0]) : '';
}

/**
 * Retorna String com Primeira Letras em Maiuculo
 * @param  string  	$str
 * @return string   string alterada
 */

function fbz_ucfirst($str) {
    $palavras = explode(' ', $str);
    foreach ($palavras as $i => $palavra) {
        if (strlen($palavra) > 2) {
			// Mantém palavras de exceção em minúsculas
			if (!preg_match('/^(para|dos?|das?|iii)$/i', $palavra)) {
				$palavras[$i] = mb_convert_case($palavra, MB_CASE_TITLE, "UTF-8");
			} else {
				$palavras[$i] = mb_strtolower($palavra);
			}
        }else if( preg_match('/(para|de|da|do|e)/i',$palavra) ){
        	$palavras[$i] = mb_strtolower($palavra);
		}
    }
    $newStr = implode(' ', $palavras);
    return $newStr;
}

/**
 * Remove caracteres especiais e espaços
 * @param  string  	$str
 * @return string   string sem especiais
 *
 * http://rubular.com/r/Eh6sNab55u
 *
 */

function remove_acentos($str){
	$str = preg_replace("/[áàâãªä]/u","a",$str);
	$str = preg_replace("/[ÁÀÂÃÄ]/u","A",$str);
	$str = preg_replace("/[íìîï]/u","i",$str);
	$str = preg_replace("/[ÍÌÎÏ]/u","I",$str);
	$str = preg_replace("/[éèêë]/u","e",$str);
	$str = preg_replace("/[ÉÈÊË]/u","E",$str);
	$str = preg_replace("/[óòôõö]/u","o",$str);
	$str = preg_replace("/[ÓÒÔÕÖ]/u","O",$str);
	$str = preg_replace("/[úùûü]/u","u",$str);
	$str = preg_replace("/[ÚÙÛÜ]/u","u",$str);
	$str = preg_replace("/ç/","c",$str);
	$str = preg_replace("/Ç/","C",$str);
	$str = preg_replace("/ñ/","n",$str);
	$str = preg_replace("/Ñ/","N",$str);
	$str = preg_replace("/&/","e",$str);
	return trim($str);
}

function remove_especiais($str){
	$str = remove_acentos($str);
	$str = preg_replace('/[^-\d\s\/a-z]/i', "-", $str);
	$str = preg_replace("/(\s)+/u"," ",$str);
	return trim($str);
}

function file_name_format($str){
	$str = trim($str);
	$str = remove_especiais($str);
	$str = preg_replace('/[ \/]/i',"-",$str);
	$str = preg_replace('/-+/', "-", $str);
	$str = (function_exists('mb_strtolower')) ? mb_strtolower($str) : strtolower($str);
	return utf8_encode($str);
}

/**
 * Retorna categorias no plural
 * @param  string $tipo Nome da Categoria
 * @return string       Nome da Categoria no Plural
 */

function fbz_tipos_plural($tipo) {
	$tipo = fbz_strtolower($tipo);
	$tipo = preg_replace("/pavilh(a|ã)o/i",'pavilhões',$tipo);
	$tipo = preg_replace("/barrac(a|ã)o/i",'barracões',$tipo);
	$tipo = preg_replace("/galp(a|ã)o/i",'galpões',$tipo);
	$tipo = preg_replace("/duplex/i",'duplexes',$tipo);
	$tipo = preg_replace("/casa de vila/i",'casas de vila',$tipo);
	$tipo = preg_replace("/casa comercial/i",'casas comerciais',$tipo);
	$tipo = preg_replace("/casa em condom(í|i)nio/i",'casas em condomínio',$tipo);
	$tipo = preg_replace("/sobrado em condom(í|i)nio/i",'sobrados em condomínios',$tipo);
	$tipo = preg_replace("/pr(é|e)dio comercial/i",'prédios comerciais',$tipo);
	$tipo = preg_replace("/sala comercial/i",'salas comerciais',$tipo);
	$tipo = preg_replace("/terreno comercial/i",'terrenos comerciais',$tipo);
	if( substr($tipo,-1)!='s' ){
		$tipo .= 's';
	}
	return $tipo;
}