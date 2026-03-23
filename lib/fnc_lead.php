<?php

function lead_save($post) {

	global $fbz_broker_id;

	// fbz_broker_id é definido no inc/db.php
	if( !isset($fbz_broker_id) ) 	die('Definir broker ID');
	if( !isset($post['nome']) ) 	die('nome vazio');
	if( !isset($post['email']) ) 	die('email vazio');
	if( !isset($post['telefone']) ) die('telefone vazio');

	// var_export($post); exit;

	$url = "http://host.docker.internal:8000/webhook/{$fbz_broker_id}/lead";
	$url = "https://webhook.fullbroker.com.br/{$fbz_broker_id}/lead";

	// exit($url);

	$headers[] = 'Content-Type: application/json';

	$body['origem'] 		= 'Site';
	$body['codigo'] 		= $post['codigo'];
	$body['nome'] 			= $post['nome'];
	$body['telefone'] 		= $post['telefone'];
	$body['email'] 			= $post['email'];
	$body['mensagem'] 		= strip_tags($post['mensagem'],'<br><br/>');
	$body['mensagem'] 		= nl2br($body['mensagem']);

	// var_export($body); exit();

	$post = json_encode($body);

	// var_export($post); exit();

	$response = fbz_curl($url, array(
		'method' => 'POST',
		'data' => $post,
		'headers' => $headers,
	));

	chdir(dirname(__FILE__));

	$date = date("Y-m-d H:m:i",time());

	$log = '';
	$log .= "$date\n";
	$log .= "post: $post\n";
	$log .= "response: ";
	$log .= var_export($response, true) . "\n";
	$log .= "-----------------------\n";

	file_put_contents( '../log/leads_fullbroker.log', "$log\n", FILE_APPEND);

	return $response;

}
