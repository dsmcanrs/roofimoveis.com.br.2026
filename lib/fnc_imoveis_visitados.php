<?php

/**
 * Manipulação de sessão de imóveis visitados do usuário
 * @param  integer $limite
 * @return array 	imoveis
 */

function fbz_sql_imoveis_visitados($limite=5) {

	global $fbz_sql_query_imoveis;

	$return = false;

	if ( !empty($_SESSION['visitados']) ) {
		$return = fbz_sql_select(
			$fbz_sql_query_imoveis,
			array(
				'where' => " id IN ($session) ",
				'limit' => $limite,
				'orderby' => 'RAND()',
			)
		);
	}

	return $return;

}

/**
 * Adiciona imovel a sessão do usuário
 * @param int 	$codigo
 */

function fbz_add_imoveis_visitados($codigo) {

	global $fbz_prefix;

	$str = $_SESSION["visitados"];

	if ( $codigo!="" && is_numeric($codigo) ) {
		$str .= ','.$codigo;
		$_SESSION['visitados'] = $str;
	}

	return true;

}
