<?php

/**
 * Coleta dispositivo de HTTP_USER_AGENT
 * @param 	HTTP_USER_AGENT
 * @return 	sql
 */

function fbz_estatisca_device() {

	$CURDATE = date("Y-m-d",time());
	$agent 	= filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');

	// die($agent);

	$device = 'Computador';
	if( stripos($agent,"Android") )		{ $device = 'Android'; }
	if( stripos($agent,"iPhone") ) 		{ $device = 'iPhone'; }
	if( stripos($agent,"iPad") ) 		{ $device = 'iPad'; }
	if( stripos($agent,"iPod") ) 		{ $device = 'iPod'; }
	if( stripos($agent,"webOS") ) 		{ $device = 'webOS'; }
	if( stripos($agent,"BlackBerry") ) 	{ $device = 'BlackBerry'; }

	$sql = "SELECT id
			FROM estatisticas
			WHERE
				tipo = 'dispositivo'
			AND valor = '".$device."'
			AND data = '$CURDATE'";

	// die($sql);

	$existe = get_value($sql);

	if($existe){
		$sql = "UPDATE estatisticas
				SET total=total+1 WHERE data='$CURDATE' AND id=$existe";
	}else{
		$sql = "INSERT INTO estatisticas
				(tipo, valor, total, data)
				VALUES
				('dispositivo','".trim($device)."',1,'$CURDATE')";
	}

	// echo $sql; exit;

	query($sql);

}

/**
 * Pega querystring de busca e salva nas estatisticas de pesquisa
 *
 * @param  string 	filter_input_array(INPUT_GET,  FILTER_SANITIZE_MAGIC_QUOTES)
 *
 * @return insert 	DB
 */

function fbz_estatisca_busca($get='') {

	// var_export($get);

	$CURDATE = date("Y-m-d",time());
	$campos_aceitos = array('tipo','bairro','valor','id');

	if( is_array($get) ){

		$get = array_map("strtolower", $get);

		foreach ($campos_aceitos as $campo) {

			if( array_key_exists($campo, $get) && $get[$campo]!='' ) {
				$var = explode(',', $get[$campo]);

				foreach ($var as $valor) {

					$sql = "SELECT id FROM estatisticas
							WHERE
							    tipo like '".$campo."'
							AND valor like '".$valor."'
							AND data = '$CURDATE'";

					// echo $sql; exit;

					$existe = get_value($sql);

					if($existe){
						$sql = "UPDATE estatisticas
								SET total=total+1 WHERE data='$CURDATE' AND id=".$existe."";
					}else{
						$sql = "INSERT INTO estatisticas
								(tipo, valor, total, data)
								VALUES
								('".trim($campo)."', '".fbz_ucfirst($valor)."', 1, '$CURDATE')";
					}

					// echo $sql; exit;

					query($sql);

				}

			}

		}

	}

	fbz_estatisca_device();

	// Historico máximo
	// $sqlx = query("DELETE FROM estatisticas WHERE data < DATE_SUB('$CURDATE',INTERVAL 120 DAY)");

}
