<?php

/**
 * Retorna um array com WHERE e Querystring da Busca de Imóveis
 * @param  _GET		variáveis da pesquisa
 * @return array
 * return[0] 		SQL Where
 * return[1] 		url Querystring
 */

function fbz_sql_busca() {

	global
	$fbz_sql_query_imoveis,
	$fbz_sql_imoveis_where_venda,
	$fbz_sql_imoveis_where_aluguel,
	$fbz_sql_imoveis_where_lancamentos,
	$fbz_url_site;

	$livre 			= get("q");
	$status 		= get("status");

	$sql_busca 		= "";
	$querystring 	= "";

	$querystring 	.= get("busca") ? "&busca=".get("busca") : '';
	$querystring 	.= get("ordem") ? "&ordem=".get("ordem") : '';


	// ---

	// Campo Valor

	if( $status=='aluguel' ){
		$campo_vlr = "valor_aluguel";
	}else if( $status=='aluguel-temporada' ){
		$campo_vlr = "valor_diaria";
	}else{
		$campo_vlr = "valor_venda";
	}

	// ---

	// Filtro Finalidade

	if( !empty($status) ){
		if ( $status=="aluguel-temporada" ){
			$sql_busca .= $fbz_sql_imoveis_where_temporada;
		}
		else if ( $status=="aluguel" || $status=="locacao" ){
			$sql_busca .= " $fbz_sql_imoveis_where_aluguel ";
		}
		else if ( $status=="lancamentos" ){
			$sql_busca .= " $fbz_sql_imoveis_where_lancamentos ";
		}
		else if ( $status=="venda" ){
			$sql_busca .= " $fbz_sql_imoveis_where_venda ";
		}
		$querystring .= "&finalidade=".$status."&";
	}

	// echo $querystring;
	// echo $sql_busca;
	// exit;

	// ---

	// Boolean ou Sim/Não

	$campos_bool = array(
		"exclusivos" => "exclusivo",
		"corporativos" => "corporativo",
	);

	foreach( $campos_bool as $qry_field => $sql_field ){
		if( isset($_GET[$qry_field]) ){
			$sql_busca .= " AND {$sql_field}='S' ";
			$querystring .= "&$qry_field=".get($qry_field);
		}
	}

	// ---

	// Strings (categoria, bairro, cidade)
	// querystring => campo_db

	$campos_string = array(
		"codigo" => "codigo",
		"categoria" => "categoria",
		"empreendimento" => "empreendimento",
		"cidade" => "cidade",
		"bairro" => "bairro"
	);

	foreach( $campos_string as $qry_field => $sql_field ){
		if( get($qry_field)!="" ){
			$array = var_to_array( get($qry_field) );
			$array = array_filter($array);
			if ( sizeof($array)>0 ) {
				$sql_busca .= " and (";
				foreach($array as $value) {
					$value = file_name_format($value);
					$value = str_replace('-', ' ', $value);
					$sql_busca .= " $sql_field LIKE '$value' OR ";
				}
				$querystring .= "&$qry_field=".get($qry_field);
				$sql_busca = rtrim($sql_busca,"OR ");
				$sql_busca .= " ) ";
			}
		}
	}

	// echo $querystring;
	// echo $sql_busca;
	// exit;

	// ---

	// Inteiros (dorm, suites, vagas)
	// querystring => campo_db

	$campos_numericos = array(
		"dorm" => "dormitorios",
		"suites" => "suites",
		"vagas" => "vagas"
	);

	foreach( $campos_numericos as $qry_field => $sql_field ){
		if( get($qry_field)!="" ){
			$array = var_to_array(get($qry_field));
			$array = array_filter($array);
			if( sizeof($array)>0 ){
				$sql_busca .= " and (";
				// var_export($array); exit;
				foreach($array as $value) {
					$value = str_replace(' ', 'm', $value);
					$sinal = "=";
					if (!is_numeric($value)) {
							$sinal = ">=";
							$value = intval($value);
					}
					$sql_busca .= " $sql_field $sinal $value OR ";
				}
				$querystring .= "&$qry_field=".get($qry_field);
				$sql_busca = rtrim($sql_busca,"OR ");
				$sql_busca .= " ) ";
			}
		}
	}

	// echo $querystring;
	// echo $sql_busca;
	// exit;

	// ---

	// Range de Áreas
	// min-max
	// querystring => campo_db

	$campos_double = array(
		"areap" => "area_privativa",
		"areac" => "area_construida",
		"areat" => "area_total"
	);

	foreach( $campos_double as $qry_field => $sql_field ){
		if( get($qry_field)!="" ){
			$array = var_to_array(get($qry_field));
			$array = array_filter($array);
			if( sizeof($array)>0 ){
				$sql_busca .= " AND ".$sql_field.">0 AND (";
				foreach ($array as $value) {
					$range = explode("-",$value);
					if ( sizeof($range)==2 && is_numeric($range[0]) && is_numeric($range[1]) ) {
						if ($range[1]==0) $range[1] = 99999999;
						$sql_busca .= " ( $sql_field >= $range[0] and $sql_field <= $range[1] ) OR ";
						$querystring .= "&$qry_field=$value,";
					}
				}
				$querystring = rtrim($querystring,",");
				$sql_busca = rtrim($sql_busca,"OR ");
				$sql_busca .= " ) ";
			}
		}
	}

	// ---

	// Caracteristicas no mesmo select

	if( get("carac")!="" ){

		global $fbz_busca_tags;

		// Separa as opções da querystring
		$carac_opcoes = explode(',', get("carac"));
		$sql_conditions = array();
		$field_groups = array();

		// Para cada opção, busca o SQL correspondente no fbz_busca_tags
		foreach( $carac_opcoes as $opcao ){
			$opcao = trim(urlencode($opcao));
			// Percorre todos os grupos do fbz_busca_tags
			foreach( $fbz_busca_tags as $grupo => $tags ){
				foreach( $tags as $tag ){
					if( $tag[0] == $opcao ){
						$sql_condition = trim($tag[2]);
						$sql_conditions[] = $sql_condition;

						// Extrai o nome do campo (primeira palavra antes do operador)
						$field_name = preg_replace('/\s+(=|>=|<=|>|<|LIKE).*/', '', $sql_condition);
						$field_name = trim($field_name);
						$field_groups[$field_name][] = $sql_condition;

						break 2; // Sai dos dois loops quando encontrar
					}
				}
			}
		}

		// Monta as condições SQL agrupadas por campo
		if( !empty($field_groups) ){
			$final_conditions = array();

			foreach( $field_groups as $field => $conditions ){
				if( count($conditions) > 1 ){
					// Se há múltiplas condições para o mesmo campo, usa OR
					$final_conditions[] = '(' . implode(' OR ', $conditions) . ')';
				} else {
					// Se há apenas uma condição, usa diretamente
					$final_conditions[] = $conditions[0];
				}
			}

			// Une todas as condições com AND
			$sql_busca .= ' AND (' . implode(' AND ', $final_conditions) . ')';
		}

		$querystring .= '&carac='.get("carac");

	}

	// echo $querystring;
	// echo $sql_busca;
	// exit;

	if (get("carac-xxx") != "") {

		global $fbz_busca_carac;

		$array = explode(',', get("carac"));
		$sqlFiltros = [];

		foreach ($array as $string) {
			if (preg_match('/^(\d+)m-(\w+)$/', $string, $m)) {
				// Ex: 5m-dorm
				if (!isset($fbz_busca_carac[$m[2]])) {
					exit("<b>Campo não definido em fbz_busca_carac:</b> {$m[2]}<br>");
				}
				$campo = $fbz_busca_carac[$m[2]];
				$sqlFiltros[] = "$campo >= {$m[1]}";
			} elseif (preg_match('/^(\d+)-(\w+)$/', $string, $m)) {
				// Ex: 1-dorm
				if (!isset($fbz_busca_carac[$m[2]])) {
					exit("<b>Campo não definido em fbz_busca_carac:</b> {$m[2]}<br>");
				}
				$campo = $fbz_busca_carac[$m[2]];
				$sqlFiltros[] = "$campo = {$m[1]}";
			} else {
				if (!isset($fbz_busca_carac[$string])) {
					exit("<b>Campo não definido em fbz_busca_carac:</b> {$string}<br>");
				}
				$campo = $fbz_busca_carac[$string];
				$sqlFiltros[] = "$campo LIKE '%$string%'";
			}
		}

		if ($sqlFiltros) {
			$sql_busca .= ' AND (' . implode(' OR ', $sqlFiltros) . ')';
			$querystring .= '&carac=' . get("carac");
		}
	}

	// ---

	// Range de Valores
	// min-max

	if( get("valor")!="" ){
		if (preg_match('/[0-9]-[0-9]/', get("valor"))){
			$v_valor = var_to_array( get("valor") );
			$sql_busca .= " and ( ";
			$querystring .= "&valor=";
			foreach($v_valor as $valor) {
				$v = explode("-",$valor);
				if ( sizeof($v)==2 && is_numeric($v[0]) && is_numeric($v[1]) ) {
					if ($v[1]==0) $v[1] = 99999999;
					$sql_busca .= " ( $campo_vlr >= $v[0] AND $campo_vlr <= $v[1] ) OR ";
					$querystring .= $v[0]."-".$v[1].",";
				}
			}
			$querystring = rtrim($querystring,",");
			$sql_busca = rtrim($sql_busca,"OR ");
			$sql_busca .= " ) ";
		}
	}

	// ---

	// Valores em input

	if( get("valor_de")!="" ){
		$de = mysql_moeda(get("valor_de"));
		$sql_busca .= " AND $campo_vlr >= $de ";
		$querystring .= "&valor_de=". get("valor_de");
	}

	if( get("valor_ate")!="" ){
		$ate = mysql_moeda(get("valor_ate"));
		$sql_busca .= " AND $campo_vlr <= $ate ";
		$querystring .= "&valor_ate=". get("valor_ate");
	}

	// Áreas input

	if( get("area_de")!="" ){
		$de = mysql_moeda(get("area_de"));
		$sql_busca .= " AND IF(area_privativa>0, area_privativa, area_total)  >= $de ";
		$querystring .= "&area_de=". get("area_de");
	}

	if( get("area_ate")!="" ){
		$ate = mysql_moeda(get("area_ate"));
		$sql_busca .= " AND IF(area_privativa>0, area_privativa, area_total) <= $ate ";
		$querystring .= "&area_ate=". get("area_ate");
	}

	// ---

	if( get('corretor')!="" && is_numeric(get('corretor')) ){
		$corretor = get("corretor");
		// se for cms
		$sql_busca .= " AND (corretor = $corretor) ";
		// se for vista
		// $email = get_value("SELECT email from fbz_usuarios where id=$corretor");
		// $sql_busca .= " AND (corretor like '%$email%') ";
		$querystring .= "&corretor=$corretor";
	}

	// ---

	if( isset($_GET['favoritos']) ){
		$sqlfav = " AND imo.id=0 ";
		if( isset($_COOKIE['favoritos']) && trim($_COOKIE['favoritos'])!='' ){
			$cods = $_COOKIE['favoritos'];
			$cods = preg_split("/[\s,;]/",$cods);
			// var_export($cods); exit;
			if( trim($cods[0])!='' ){
				$sqlfav = " and (";
				foreach ($cods as $cod) {
					if( is_numeric($cod) ){
						$cod = intval($cod);
						$sqlfav .= " id=$cod";
						$sqlfav .= (count($cods)>0) ? " OR " : '';
					}
				}
				$sqlfav = rtrim($sqlfav, "OR ");
				$sqlfav .=" )";
			}
		}
		$sqlfav = str_replace('and ( )', '', $sqlfav);
		$sql_busca .= $sqlfav;
		$querystring .= "&favoritos=true";
	}

	//

	$querystring = preg_replace('/&+/',"&",$querystring);

	// echo $sql_busca;
	// echo $querystring;
	// exit;

	return array($sql_busca,$querystring);

}
