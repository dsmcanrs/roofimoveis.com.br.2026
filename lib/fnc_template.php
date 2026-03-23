<?php

function template_path($file){

	global $fbz_frontend;

	$TemplatePath = $fbz_frontend . str_replace('.php', '.latte', current_page());

	return $TemplatePath;

}

function template_replace_path($output){

	global $fbz_url_site, $fbz_frontend;

	$output = preg_replace(
		'/(href="|src=")(plugins|css|js)\/(.*?)(")/i',
		'$1' . $fbz_url_site . $fbz_frontend . '$2/$3$4$5',
		$output
	);

	return $output;

}