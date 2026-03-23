<?php

function fbz_corretor($imovel) {

	$return = array();

	if( empty($imovel) ) return null;

	$sql = "SELECT
			(SELECT arquivo FROM arquivos
			 WHERE registro=usuarios.id AND modulo='usuarios' LIMIT 1) AS foto,
			 usuarios.*
			FROM usuarios, imoveis
			WHERE
				usuarios.ativo=1 AND usuarios.exibir=1
			AND usuarios.id = imoveis.idu
			AND imoveis.id = $imovel
			ORDER BY nome
			";

	// echo($sql);

	$query = query($sql);

	if( query_num($query)>0 ) {
		while( $rs = fetch($query) ){
			$return[] = $rs;
		}
	}

	if( count($return)>0 ) $return = $return[0];

	// var_export($return); exit;

	return $return;

}