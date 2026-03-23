<?php

chdir('../');

require_once("inc/setup.php");

// Adiciona ao Mailing
// ----------------------------------------------------------------------------

// var_export($_POST); exit;

if( !is_array(post("email")) ) die( 'Post email' );

$nome 	= post("nome");
$email 	= post("email");
$fone 	= post("fone");

if( !is_email($email) ) {
	$msg = "Informe um e-mail válido.";
	exit;
}

$msg = fbz_news_add( $email, $nome, $fone );

setmsg($msg);
redir("./");
exit;