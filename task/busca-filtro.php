<?php

chdir('../');

require_once("inc/setup.php");

/**
 * retorna dados em json para a busca
 * @param  string campo 	campo que eu quero do banco
 * @param  string filtro  	string json com parametros para where
 * @return json
 */

// var_export($_GET); exit;
// var_export($_POST); exit;

$campo 	= $_GET["campo"];
$filtro = $_GET["filtro"];
$filtro = stripslashes($filtro);
$filtro = html_entity_decode($filtro);
$filtro = json_decode( $filtro, true );
$where 	= '';

// var_export($filtro); exit;

foreach( $filtro as $field => $value ){

	// Converte valores separados por virgula em array
	if( gettype($value)==='string' && strstr($value,',') ){
		$value = var_to_array($value);
		if( gettype($value)=='array' ){
			$value = array_filter($value, 'strlen');
		}
	}

	// var_export($value);

	if( gettype($value)=='array' && count($value)>0 ){
		$where .= " AND ( ";
		foreach($value as $item){
			if( trim($item)!='' ){
				// echo "$field = $item<br>";
				$item = str_replace('-','%',$item);
				$where .= " $field like '$item' OR";
			}
		}
		$where = rtrim($where,' OR');
		$where .= " ) ";
		$where = str_replace('AND ( )','',$where);
	}

	if( gettype($value)=='string' && trim($value)!='' ){
		$value = str_replace('-','%',$value);
		// echo "$field = $value<br>";
		if( $field=='status' ){
			$where .= " AND $field like '%$value%' ";
		}else{
			$where .= " AND $field like '$value' ";
		}
	}

}

$sql = "SELECT distinct($campo) as value
		FROM imoveis
		WHERE
		$campo<>''
		AND $fbz_sql_imoveis_where
		$where
		ORDER BY $campo";

// echo $sql; exit;

// $query = query($sql);
// $fetch = fetch_all($query);

$fetch = fbz_sql_select( $sql,
	array(
		// 'debug' => 1
		// 'cache' => 900, // 15 min
	)
);

foreach($fetch as $i => $array) {
	$fetch[$i] = fbz_ucfirst($array['value']);
}

echo json_encode( $fetch, true );

exit;