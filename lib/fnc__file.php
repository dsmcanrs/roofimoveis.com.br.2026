<?php

// leitura de arquivo

function read_file($arquivo){
   $fd = fopen($arquivo, 'r') or die("arquivo não encontrado.");
   $file_content = fread($fd, filesize($arquivo));
   fclose($fd);
   return $file_content;
}

/**
 * Grava arquivo de log
 * @param string $arquivo - caminho do arquivo
 * @param string $conteudo - conteudo do arquivo
 */

function log_file($arquivo, $conteudo) {

	$dir_base = dirname(dirname(__FILE__));
	$data = date("Y-m-d",time());
	$wday = date("w",time());

	$log  = "";
	$log .= "$data<br />";
	$log .= "$wday <br />";
	$log .= "<hr />";
	$log .= $conteudo;

	$f = fopen($arquivo, 'w') or die("Não conseguiu gerar arquivo de log");
	fwrite($f, $log);
	fclose($f);

}

