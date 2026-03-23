<?php

chdir('../');

require_once("inc/setup.php");

// Contato Imóvel
// ----------------------------------------------------------------------------

// var_export($_POST); exit;

if( count($_POST)==0 ) die('Post fields');

if( $GLOBALS['fbz_captcha'] || $site['recaptcha']['active'] ) fbz_captcha('validar');

// UTM
$post['utm_source'] 	= post("utm_source");
$post['utm_campaign'] 	= post("utm_campaign");
$post['utm_term'] 		= post("utm_term");
$post['utm_id'] 		= post("utm_id");
$post['utm_content'] 	= post("utm_content");
// dados do form
$post['status'] 	= post("status");
$post['codigo'] 	= post("codigo");
// padrao
$post['nome'] 		= post("nome");
$post['email'] 		= post("email");
$post['telefone'] 	= post("telefone");
$post['mensagem'] 	= post("mensagem");
// agendamento
$post['dia'] 		= post("dia");
$post['horario'] 	= post("horario");

// url do imovel
$previous_url = $_SERVER['HTTP_REFERER'];

// var_export($post); exit;

if( !is_email($post['email']) ) {
	redir('js','Informe um e-mail válido.');
	exit;
}

$titulo = '';
$mensagem = '';

$titulo = ($post['dia']!='')
	? "Agendar visita para o imóvel: ".$post['codigo']
	: "Contato para o imóvel: ".$post['codigo']."";

// header
// $mensagem .= "<h2>$titulo</h2><hr>";

// body
foreach($post as $index => $value){
	if( $value!='' ){
		$mensagem .= "<strong>". ucfirst($index) ."</strong>: $value<br>";
	}
}

// footer
$mensagem .= "<hr><a href=\"$previous_url\" target=\"_blank\">$previous_url</a>";

// echo $mensagem; exit;

$contato = [
    'origem'   => 'Site',
    'codigo'   => $post['codigo'],
    'nome'     => $post['nome'],
    'email'    => $post['email'],
    'telefone' => $post['telefone'],
    'mensagem' => $mensagem
];

if( function_exists('lead_save') ){
	lead_save($contato);
}

mail_send( $post['email'], $site['geral']['email'], $titulo, $mensagem );
mail_send( $post['email'], 'dsmcanrs@gmail.com', $titulo, $mensagem );

redir('../obrigado?nome='.$post['nome']);