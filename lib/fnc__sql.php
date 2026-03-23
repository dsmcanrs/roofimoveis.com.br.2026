<?php

function query1($sql) {
	global $conn;
	$result = mysqli_query($conn, $sql);
	if (!$result) {
		$error = mysqli_error($conn);
		$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'URL não disponível';
		$currentPage = $_SERVER['REQUEST_URI'];
		$dateTime = date('Y-m-d H:i:s'); // Formato: Ano-Mês-Dia Hora:Minuto:Segundo
		$logEntry = "[$dateTime]\nErro: $error\nSQL: $sql\nPágina: $currentPage\nOrigem: $url\n---\n";
		file_put_contents("log/query.log", $logEntry, FILE_APPEND);
	}
	return $result;
}

function query($sql) {
    global $conn;
    try {
        $result = mysqli_query($conn, "$sql");
        if (!$result) {
            throw new Exception(mysqli_error($conn));
        }
        return $result;
    } catch (Exception $e) {
        $dateTime = date('Y-m-d H:i:s');
        $currentPage = $_SERVER['REQUEST_URI'];
        $logEntry = "====================[SQL ERROR]====================\n";
        $logEntry .= "[$dateTime]\n";
        $logEntry .= ">>> PÁGINA: $currentPage\n";
        $logEntry .= ">>> ERRO: {$e->getMessage()}\n";
        $logEntry .= ">>> SQL: $sql\n";
        file_put_contents("log/query.log", $logEntry, FILE_APPEND);
        return false;
    }
}

function query_num($query) {
	$return = mysqli_num_rows($query);
	return $return;
}

function fetch($query) {
	$return = mysqli_fetch_array($query);
	return $return;
}

function fetch_all($query) {
    $rows = [];
    while($row = mysqli_fetch_assoc($query)) {
        $rows[] = $row;
    }
    return $rows;
}

// function fetch_all($query) {
// 	while($rows[] = mysqli_fetch_assoc($query));
// 	array_pop($rows);
// 	return $rows;
// }

function get_total($str_sql) {
	global $conn;
	$sql = stristr($str_sql,"from");
	$sql = 'select count(*) '.$sql;
	$ct  = query($sql) or die( mysqli_error($conn) );
	$rs  = fetch($ct);
	return $rs[0];
}

function get_value($str_sql) {
	global $conn;
	$sql = query($str_sql) or die( mysqli_error($conn) );
	$rs  = fetch($sql);
	return $rs[0];
}

function test_table($tbl) {
	$qry = query("SHOW TABLES LIKE '".$tbl."'");
	$tables = fetch($qry);
	return ($tables) ? true : false;
}

function sql_replace($str){
	$str = addslashes($str);
	$str = str_replace("'", "''", ''.$str);
	$str = str_replace("\\", "\\\\", ''.$str);
	return $str;
}

// function sql_replace($v){
// 	$v = str_replace("'", "`", $v);
// 	return $v;
// }

/**
 * Consulta Imóveis
 *
 * @param  string  	$sql     			Template do comando SQL
 * @param  string  	$options			Array de opcoes de replace do comando
 * @param  string  	$options[fields]
 * @param  string  	$options[where]
 * @param  string  	$options[orderby]
 * @param  string  	$options[limit]
 * @param  string  	$options[return]	array, query, json, count
 * @param  int  	$options[cache]		tempo de cache em segundos
 * @param  bool  	$options[debug] 	print sql
 *
 * Tipos de retorno:
 * @return array	vetor php
 * @return query	resource mysql
 * @return json		vetor em json
 * @return count	total
 *
 */

function fbz_sql_select( $sql, $options=array() ){

	if( empty($sql) ) exit('fbz_sql_imoveis: definir sql');

	$debug = isset($options['debug']) ? true : false;
	$debug = isset($options['debug']) ? true : false;
	$fields = isset($options['fields']) ? $options['fields'] : null;
	$where = isset($options['where']) ? trim($options['where']) : null;
	$limit = isset($options['limit']) ? $options['limit'] : null;
	$orderby = isset($options['orderby']) ? $options['orderby'] : null;
	$retorno = isset($options['return']) ? trim($options['return']) : 'array';
	$cacheTime = isset($options['cache']) ? intval($options['cache']) : 0;

	if( !empty($where) && stripos($where, 'and') !== 0 )
		$where = " AND $where ";

	if( !empty($limit) && stripos($limit, 'limit') !== 0 )
		$limit = " LIMIT $limit ";

	if( !empty($fields) && substr($fields,0,1)!="," )
		$fields = ", $fields ";

	if( !empty($orderby) && stripos($orderby, 'order by') !== 0 )
		$orderby = " ORDER BY $orderby ";

	$sql = str_replace("/*FIELDS*/", $fields, $sql);
	$sql = str_replace("/*WHERE*/", $where, $sql);
	$sql = str_replace("/*ORDERBY*/", $orderby, $sql);
	$sql = str_replace("/*LIMIT*/", $limit, $sql);

	// echo "\n<!--";
	// echo "\n[SQL]: $sql";
	// echo '\n[OPTIONS]: '. var_export($options,true);
	// echo "\n-->";

	// Imprime o comando SQL
	if ($debug) {
		echo "\n$sql\n";
	}

	// Verifica se tem cache desta consulta
	if( $cacheTime>0 ){
		$cacheFile = 'cache/query_' . md5($sql) . '.json';
		// Verifique se o arquivo de cache existe e se o tempo de cache não expirou
    	if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
			// echo "\n<!--[DO CACHE]-->\n";
	        $jsonData = file_get_contents($cacheFile);
    	    $return = json_decode($jsonData, true);
			return $return;
		}
	}

	// echo "\n<!--[DO MYSQL]-->\n";

	if ($retorno=='count') {
		$sql = stristr($sql,"from");
		$sql = "SELECT COUNT(*) as n ".$sql;
		$query = query($sql);
		$rows = fetch_all($query);
		$return = $rows[0]['n'];
	}

	if ($retorno=='query') {
		$rows = query($sql);
		$return = $rows;
	}

	if ($retorno=='array') {
		$query = query($sql);
		$return = fetch_all($query);
	}

	if ($retorno=='json') {
		$query = query($sql);
		$return = fetch_all($query);
		$return = json_encode($return);
	}

	// Salva resultado em cache
	if( $cacheTime>0 ){
		$jsonData = json_encode($return);
        file_put_contents($cacheFile, $jsonData);
	}

	return $return;
}

/**
 * Monta comandos INSERT e UPDATE
 *
 * @param  string 	$command   	insert/update
 * @param  string 	$table     	nome tabela
 * @param  array 	$fields 	vetor com nomes dos campos e valores
 * @param  string 	$where    	condicao para update
 *
 * @return string	           	comando sql
 */

function query_build($command,$table,$fields,$where=''){

	global $conn;

	$er_number = '(^(-?)(\d+)(\.?)(\d+)?$)';
	$bulk = '';
	$campos = '';
	$valores = '';
	$sql = '';

	if ( $command=='update' ) {

		if ( $where=='' ) return 'SQL Update precisa de Where';

		$sql = 'UPDATE {TABLE} SET {FIELDS} WHERE {WHERE}';

		foreach ($fields as $key => $value) {
            if( !preg_match($er_number,$value) ){
                // $value = trim($value)=='' ? 'NULL' : "'". str_replace("'","\'",$value) ."'";
                $value = trim($value)=='' ? 'NULL' : "'". mysqli_real_escape_string($conn,$value) ."'";
            }
			$campos .= $key.' = '.$value.', ';
		}

		$campos = rtrim($campos,', ');
		$sql = str_replace('{TABLE}',$table,$sql);
		$sql = str_replace('{FIELDS}',$campos,$sql);
		$sql = str_replace('{WHERE}',$where,$sql);

	}

	if ( $command=='insert' ) {

        // Se for array é builk insertion
        // if ( isset($fields[0]) && is_array($fields[0]) ) {
        if ( is_array(current($fields)) ) {
            $fields = $fields;
        }else{
            $fields = array($fields);
        }

		$sql = 'INSERT INTO {TABLE} {FIELDS} VALUES {VALUES}';

		foreach ($fields as $key => $value) {
			$campos = '';
			$valores = '';
			foreach ($value as $key2 => $value2) {
				if( !preg_match($er_number, $value2) ){
					// $value2 = trim($value2)=='' ? 'NULL' : "'". str_replace("'","\'",$value2) ."'";
					$value2 = trim($value2)=='' ? 'NULL' : "'". mysqli_real_escape_string($conn,$value2) ."'";
				}
				$campos .= $key2.', ';
				$valores .= $value2.', ';
			}
			$campos = rtrim($campos,', ');
			$valores = rtrim($valores,', ');
			$bulk .= '('.$valores.'),';
		}

		$bulk = rtrim($bulk,', ');
		$sql = str_replace('{FIELDS}','('.$campos.')',$sql);
		$sql = str_replace('{TABLE}',$table,$sql);
		$sql = str_replace('{VALUES}',$bulk,$sql);

	}

	return $sql;

}