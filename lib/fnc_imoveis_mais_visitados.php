<?php

/**
 * Retorna query sql especifica para os mais visitados
 * @param  integer $limite [description]]
 * @return query sql       [description]
 */

function fbz_sql_imoveis_mais_visitados($limite=10){

	if( !test_table('fbz_maisvistos') ) return null;

	$sql = query("SELECT v.codigo, v.total
				from fbz_maisvistos v, fbz_imoveis i
				where v.codigo = i.CODIGO
				order by v.total desc
				limit 0,".($limite+10));

	$str_where = " ( ";
	$str_order = "";

	if(query_num($sql)>0) {

		while ($rs = mfa($sql)) {
			$str_where .= " CODIGO = ".$rs['codigo']." or ";
			$str_order .= " CODIGO=".$rs['codigo']." DESC /*".$rs['total']."*/, ";
		}

		$str_where = rtrim($str_where,"or ")." ) ";
		$str_order = rtrim($str_order,", ");

		return fbz_sql_select(
			$sql,
			array(
				'return' => 'query',
				'where' => $str_where,
				'orderby' => $str_order,
				'limit' => $limite,
			)
		);

	}else{

		return null;

	}

}

/**
 * Adiciona imovel a base de mais visitados do site
 * @param imovel Código do Imóvel
 * @return void
 */

function fbz_add_imoveis_mais_visitados($imovel){

	if( !test_table('fbz_maisvistos') ) return null;

	if (empty($_SESSION['maisvistos'])) {
		$_SESSION['maisvistos'] = test_table('fbz_maisvistos') ? 'sim' : 'nao' ;
	}

	if ( $_SESSION['maisvistos']=='sim' ) {

		if ($imovel!="" && is_numeric($imovel)) {

			$querytest = query("SELECT * from fbz_maisvistos where codigo = ".$imovel);

			if (query_num($querytest)>0) {
				query("UPDATE fbz_maisvistos set total=total+1 where codigo = ".$imovel);
			} else {
				query("INSERT into fbz_maisvistos (codigo,total) values (".$imovel.",1)");
			}

		}

	}

}
