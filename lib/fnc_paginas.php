<?

function mn_secoes(){

	$sql = "SELECT distinct IF(grupo='' OR ISNULL(grupo), titulo, grupo) AS titulo, ordem
			FROM paginas p
			WHERE
				ativa='S'
			AND exibir='S'
			ORDER BY ordem
			";

	$sql = "SELECT titulo, MIN(ordem) AS ordem
			FROM (
			    SELECT IF(grupo='' OR ISNULL(grupo), titulo, grupo) AS titulo, ordem
			    FROM paginas p
			    WHERE ativa=1
			    AND exibir=1
			) AS subquery
			GROUP BY titulo
			ORDER BY ordem";


	$query = query($sql);

	$return = array();

	while( $rst=fetch($query) ){
		$return[] = $rst["titulo"]; $i++;
	}

	return $return;
}

function mn_paginas($grupo=null) {

	global $fbz_tipo_url, $fbz_url_site;

	if( $grupo!='' ){
		$where = " AND (grupo='$grupo' OR titulo='$grupo') ";
	}

	$sql = "SELECT *
			FROM paginas p
			WHERE
				ativa=1
			AND exibir=1
			$where
			ORDER BY ordem";

	// die($sql);

	$query = query($sql);

	$paginas = array();
	$i = 0;

	while ( $rst = fetch($query) ) {

		$paginas[$i]['titulo'] 			= $rst['titulo'];
		$paginas[$i]['titulo_amigavel'] = file_name_format($rst['titulo']);
		$paginas[$i]['grupo'] 			= $rst['grupo'];
		$paginas[$i]['grupo_amigavel'] 	= file_name_format($rst['grupo']);
		$paginas[$i]['target'] 			= $rst['target'];
		$paginas[$i]['submenu'] 		= $rst['submenu'];

		$url = trim($rst['url']);

		// url = titulo da página
		if( $url=='' ){
			$paginas[$i]['url'] = $fbz_url_site . $paginas[$i]['titulo_amigavel'];
		// url = url interna
		}else if( !preg_match('/^(http|https):\/\//', $url) ) {
			$paginas[$i]['url'] = $fbz_url_site . $url;
		}else{
			$paginas[$i]['url'] = $url;
		}

		$i++;

	}

	return $paginas;
}
