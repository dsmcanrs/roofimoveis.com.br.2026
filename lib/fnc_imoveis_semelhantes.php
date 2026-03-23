<?php

/**
 * Retorna a query sql especifica para os semelhantes
 *
 * @param  array  	$imovel 		resource mysql
 * @param  string 	$where
 * @param  integer 	$limite
 * @param  integer 	$percentual 	percentual do valor acima e abaixo do imovel atual
 *
 * @return array             		array de imoveis
 */

function fbz_sql_imoveis_semelhantes($imovel,$where='',$percentual=20,$limite=9) {

	global $fbz_sql_query_imoveis;

	if( $where=='' ){
		$where .= "
			AND imo.cidade = '".$imovel['cidade']."'
			AND imo.categoria = '".$imovel['categoria']."'
		";
	}

	$where .= " AND imo.id <> ".$imovel['id'];

	// die($where);

	$campo_valor = stristr($imovel['status'],'aluguel') ? 'valor_aluguel' : 'valor_venda';

	if( $imovel[$campo_valor]>0 ){
		$calc = ($imovel[$campo_valor]/100) * $percentual;
		$mais  = $imovel[$campo_valor] + $calc;
		$mais = str_replace(",",".",$mais);
		$menos = $imovel[$campo_valor] - $calc;
		$menos = str_replace(",",".",$menos);
		$where .= " AND imo.".$campo_valor." BETWEEN $menos and $mais";
	}

	// die($where);

	$ordem = '';

	if( $imovel['bairro']!='' )
		$ordem .= " imo.bairro like '". sql_replace($imovel['bairro']) ."' DESC, ";
	if( $imovel['empreendimento']!='' )
		$ordem .= " imo.empreendimento='". sql_replace($imovel['empreendimento']) ."' desc,";
	if( $imovel['dormitorios']!='' )
		$ordem .= " imo.dormitorios>={$imovel['dormitorios']} desc,";
	if( $imovel['vagas']!='' )
		$ordem .= " imo.vagas>={$imovel['vagas']} desc,";

	$ordem = rtrim($ordem,',');

	// echo $ordem;

	$return = fbz_sql_select(
		$fbz_sql_query_imoveis,
		array(
			'where' => $where,
			'limit' => $limite,
			// 'orderby' => sql_replace($ordem),
			// 'debug' => 1
		)
	);

	return $return;

}