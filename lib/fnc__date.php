<?php

// Verifica se data é padrão: dd/mm/aaaa
function is_date($data){
	return (preg_match("/[0-9]{2}\/[0-9]{2}\/[0-9]{4}/", $data)) ? true : false;
}

// Verifica se data é padrão: aaaa-mm-dd
function is_date_mysql($data){
	return (preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", $data)) ? true : false;
}

/**
 * Calcula a diferença entre duas datas e retorna o resultado na unidade especificada.
 *
 * @param string $data1 A primeira data no formato de string (ex: "2023-12-03 12:30:00").
 * @param string $data2 A segunda data no formato de string (ex: "2023-12-04 15:45:00").
 * @param string $unidade A unidade desejada para o resultado ('d' para dias, 'h' para horas, 'm' para minutos).
 *
 * @return int|bool A diferença calculada na unidade especificada ou false se a unidade não for reconhecida.
 */
function fbz_date_diff($data1, $data2, $unidade = 'm'){

    $data1 = new DateTime($data1);
    $data2 = new DateTime($data2);

    $diferenca = $data1->diff($data2);

    switch ($unidade) {
        case 'd': // Diferença em dias totais
            return $diferenca->days;
        case 'h': // Diferença em horas totais
            return ($diferenca->days * 24) + $diferenca->h;
        case 'm': // Diferença em minutos totais
            return (($diferenca->days * 24 + $diferenca->h) * 60) + $diferenca->i;
        default:
            return false;
    }

}

/**
 * Conversão de formatos de datas
 * @param  data 	data
 * @param  para 	mysql, brasil, usa
 * @return        	data convertida
 */

function converte_data($datahora, $para) {

	if( !$datahora ) return null;

	$data = $datahora;
	$hora = '';

	if( strlen($datahora)>10 ){
		$datahora = explode(" ",$datahora);
		$data = $datahora[0];
		$hora = $datahora[1];
	}

	// die($data .' > '. $hora);

	if( $para=='mysql'){
		if( !is_date($data) )
			exit("MYSQL: forneça data no formato dd/mm/aaaa ({$data})");
		$nova = explode('/',$data);
		$data = $nova[2]."-".$nova[1]."-".$nova[0];
	}

	if( $para=='brasil' ){
		if( !is_date_mysql($data) )
			exit("BR: forneça data no formato yyyy-mm-aa ({$data})");
		$nova = explode('-',$data);
		$data = $nova[2]."/".$nova[1]."/".$nova[0];
	}

	if( $para=='usa' ){
		if( !is_date_mysql($data) )
			exit("USA: forneça data no formato yyyy-mm-aa ({$data})");
		$nova = explode('-',$data);
		$data = $nova[1]."/".$nova[2]."/".$nova[0];
	}

	return $data .' '. $hora;

}

/**
 * Adiciona uma quantidade de dias, meses, anos
 * @param 	string 	$d	string: y-m-d
 * @param 	sring  	$n 	number:
 * @param 	int 	$i 	int quantos dias, meses ou anos a adicionar
 * @return 	string 	y-m-d
 */

function fbz_date_add($d, $n, $i){
  $cd = strtotime($d);
  if($n=='y'){ $newd = date('Y-m-d', mktime(0,0,0,date('m',$cd),date('d',$cd),date('Y',$cd)+$i)); }
  if($n=='m'){ $newd = date('Y-m-d', mktime(0,0,0,date('m',$cd)+$i,date('d',$cd),date('Y',$cd))); }
  if($n=='d'){ $newd = date('Y-m-d', mktime(0,0,0,date('m',$cd)+$n,date('d',$cd)+$i,date('Y',$cd))); }
  return $newd;
}

function weekdayname($d){
  $dia = "dia inválido";
  if ($d==1){ $dia="domingo"; }
  if ($d==2){ $dia="segunda"; }
  if ($d==3){ $dia="terça"; }
  if ($d==4){ $dia="quarta"; }
  if ($d==5){ $dia="quinta"; }
  if ($d==6){ $dia="sexta"; }
  if ($d==7){ $dia="sábado"; }
  return $dia;
}

function monthname($m){
  $mes = "mes inválido";
  if ($m==1){ $mes="janeiro"; }
  if ($m==2){ $mes="fevereiro"; }
  if ($m==3){ $mes="março"; }
  if ($m==4){ $mes="abril"; }
  if ($m==5){ $mes="maio"; }
  if ($m==6){ $mes="junho"; }
  if ($m==7){ $mes="julho"; }
  if ($m==8){ $mes="agosto"; }
  if ($m==9){ $mes="setembro"; }
  if ($m==10){ $mes="outubro"; }
  if ($m==11){ $mes="novembro"; }
  if ($m==12){ $mes="dezembro"; }
  return $mes;
}

function mes_abr($m) {
	$m = (int) $m;
	if($m==1){ return 'JAN'; }
	if($m==2){ return 'FEV'; }
	if($m==3){ return 'MAR'; }
	if($m==4){ return 'ABR'; }
	if($m==5){ return 'MAI'; }
	if($m==6){ return 'JUN'; }
	if($m==7){ return 'JUL'; }
	if($m==8){ return 'AGO'; }
	if($m==9){ return 'SET'; }
	if($m==10){ return 'OUT'; }
	if($m==11){ return 'NOV'; }
	if($m==12){ return 'DEZ'; }
}