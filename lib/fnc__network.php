<?php

// Redirecionamento de Páginas (PHP ou javascript)

function redir($redirpage,$jsmsg="") {
	if ($redirpage=='js') {
		echo '<script>';
		echo 'alert(\''.$jsmsg.'\');';
		echo 'history.go(-1);';
		echo '</script>';
		echo '<html>Aguarde, redirecionando...</html>';
		exit;
	} else {
		header("location: $redirpage");
	}
	exit;
}

// Retorna URI

function fbz_uri() {
	if (isset($_SERVER['REQUEST_URI'])) {
		$return = $_SERVER['REQUEST_URI'];
	} else {
		$return = "";
		$return .= $_SERVER['SCRIPT_NAME']."?".$_SERVER['QUERY_STRING'];
	}
	return $return;
}

// Url completa

function fbz_url() {
	$http = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$return = '';
	$return .= $http . $_SERVER['HTTP_HOST'];
	$return .= $_SERVER['REQUEST_URI'];
	return $return;
}

// Retornar o nome da pagina atual

function current_page() {
  $path = $_SERVER['SCRIPT_NAME'];
  $pagina = explode("/",$path);
  return $pagina[count($pagina)-1];
}
