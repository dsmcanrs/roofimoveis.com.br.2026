<?php

/**
 * Retorna valor considerando o STATUS
 * @param  Array 	$imovel 	array de dados do imovel
 * @return String
 */

function fbz_imovel_valor($imovel) {

	$valor = 0;

	if( get("status")!="" ){

		if (get("status")=="venda") $valor = $imovel['valor_venda'];
		if (get("status")=="aluguel") $valor = $imovel['valor_aluguel'];
		if (get("status")=="aluguel-temporada") $valor = $imovel['valor_diaria'];

	} else {

		$valor = stristr($imovel['status'],'aluguel')
			   ? $imovel['valor_aluguel']
			   : $imovel['valor_venda'];

	}

	return $valor;

}

/**
 * Monta URL Amigavel
 * @param  Array 	$imovel 	Objeto do imovel
 * @param  Array 	$formato 	Formato da url
 * @return String
 */

function fbz_imovel_url($imovel,$formato='') {

 	if( array_key_exists('fbz_formato_url_imovel', $GLOBALS) ){
 		global $fbz_formato_url_imovel;
 	}else{
		$fbz_formato_url_imovel = array("categoria","bairro","cidade");
	}

	$campos = $fbz_formato_url_imovel;

	$palavras = "";

 	foreach ($campos as $value) {
		 $palavra = '';
		//  String valor
		 if( !is_array($value) ){
			 if( $value=='categoria' && strtolower($imovel['categoria'])=='empreendimento' ){
				$palavra = '';
			 }else{
				 $palavra = !empty($imovel[$value]) ? $imovel[$value] : '';
			 }
		// Array [0]Valor / [1]Legenda
		}else{
			if( !empty($imovel[$value[0]]) ) {
				if( $imovel[$value[0]]==1 ){
					$palavra = $imovel[$value[0]].'-'.rtrim($value[1],'s');
				}else{
					$palavra = $imovel[$value[0]].'-'.$value[1];
				}
			}
		}
 		$palavras .= ($palavra!='') ? $palavra.'-' : '';
 	}

	$palavras = strtolower(file_name_format($palavras));
 	$palavras = rtrim($palavras,"-");

	$url = "imovel/$palavras/".$imovel['id'];

	return $url;

}
/**
 * Monta Título para Resultado da Busca
 * Avalia campos recebidos por GET
 * @return String
 */

function fbz_imoveis_title() {

    $status    = $_GET['status']    ?? '';
    $categoria = $_GET['categoria'] ?? '';
    $cidade    = $_GET['cidade']    ?? '';
    $bairro    = $_GET['bairro']    ?? '';

    $get_nomes = function($campo, $valor) {

		if (!$valor) return [];

		$itens = explode(',', $valor);
        $nomes = [];

        foreach ($itens as $slug) {

			$busca = str_replace('-', ' ', $slug);

			$sql = "SELECT DISTINCT $campo
					FROM imoveis WHERE
					$campo LIKE '%$busca%' LIMIT 1";

			// echo "$sql<br>";

            $query = query($sql);

            if ($row = fetch($query)) {
                $nomes[] = $row[$campo];
            }

        }

        return $nomes;

    };

	$formatar_lista = function($array) {
		$total = count($array);
		if ($total === 0) return '';
		if ($total === 1) return $array[0];
		return implode(', ', array_slice($array, 0, -1)) . ' e ' . end($array);
	};

    $categorias = $get_nomes('categoria', $categoria);
    $bairros    = $get_nomes('bairro', $bairro);
    $cidades    = $get_nomes('cidade', $cidade);

    $titulo = $categorias ? $formatar_lista($categorias) : 'Imóveis';

    // if ($status == 'venda') {
    //     $titulo .= ' à venda';
    // } elseif ($status == 'aluguel') {
    //     $titulo .= ' para alugar';
    // }

    if ( count($bairros)>0 ) {
        $titulo .= ' no ' . $formatar_lista($bairros);
    }

    if ($cidades) {
        $titulo .= count($bairros)>0 ? ' de ' : ' em ';
        $titulo .= $formatar_lista($cidades);
    }

	if ($situacao=='lancamento') {
        $titulo = 'Lançamentos';
    }

    if ($status == 'venda') {
        $titulo .= ' à venda';
    } elseif ($status == 'aluguel') {
        $titulo .= ' para alugar';
    }

	// exit($titulo);

    return $titulo;
}


/**
 * Montagem padrão de ALT para Imóveis
 *
 * @param  Array 	$imovel
 * @param  String 	$campos definir quais campos usar
 *
 * @return String
 */

function fbz_imovel_alt_title($imovel, $campos="") {
	$return = "";
	$campos = $campos!='' ? $campos : array("categoria","bairro","cidade");
	foreach ($campos as $index => $value) {
		$valor = fbz_ucfirst($imovel[$campos[$index]]);
		$return .= "$valor ";
	}
	return $return;
}