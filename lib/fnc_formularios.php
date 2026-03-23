<?php

/**
 * Composição do JSON
 *
 * @param json_form[label]			tag Label
 * @param json_form[placeholder]	placeholder do input
 * @param json_form[type]			tipo do campo: text, email, select, textarea, checkbox, radio
 * @param json_form[class]			classes: mask-fone, validar, etc
 * @param json_form[cols]			cols: número de colunas 0-12
 * @param json_form[value]			value: string ou array opções para select/check/radio
 * @param json_form[rows]			rows: para textarea
 *
 * Variável global que define formato de nomes de campos
 * @param formulario_set_names {bool}
 *
 * true 	label do campos sem caracteres especiais
 * false 	automatico ... campo_1, campos_2, etc
 *
 * Montagem do FORM na página
 * @param json_form[]	objeto json do formulário
 *
 */

function formulario_html($json_form) {

	// var_export($json_form); exit;

	if( !isset($json_form['fields']) || !is_array($json_form['fields']) ){
		die('formularios: form fields not defined.');
	}

	// nomes dos campos
	// se true, define nomes para os campos, senao serão "campo_{i}"
	$set_name = isset($GLOBALS['formulario_set_names']) ? $GLOBALS['formulario_set_names'] : true;

	// template
	$path = dirname(dirname(__FILE__));
	$frontend = rtrim($GLOBALS['fbz_frontend'],'/');
	$TemplatePath = "$path/$frontend/forms.latte";
	$TemplatePath = "$frontend/forms.latte";

	// exit($TemplatePath);

	$latte = new Latte\Engine;
	$template = $latte->createTemplate($TemplatePath);
	// $latte->setTempDirectory('cache');

	$i = 0;
	$tpl = [];

	// echo "<pre>"; var_export($json_form['fields']); exit;

	foreach ($json_form['fields'] as $index => $field) {

		$i++;

		if( array_key_exists('text', $field)  ){
			$tpl['fields'][$index]['text'] = nl2br($field['text']);
		}

		else if( array_key_exists('group', $field) ){
			$tpl['fields'][$index]['group'] = $field['group'];
		}

		else if( array_key_exists('type', $field) ){

			$name = formulario_campo_name($field['label']);

			if( $field['type']=='hidden' ){
				$name = $name;
			}else{
				$name = $set_name ? $name : "campo_$index";
			}

			$cols = isset($field['cols']) ? $field['cols'] : 12;

			// $tpl['fields'][$index]['col-sm'] = $cols<4 ? 6 : 12;
			$tpl['fields'][$index]['col-md'] = $cols;
			$tpl['fields'][$index]['name'] = $name;
			$tpl['fields'][$index]['label'] = $field['label'];
			$tpl['fields'][$index]['value'] = $field['value'];
			$tpl['fields'][$index]['type'] = $field['type'];
			$tpl['fields'][$index]['class'] = $field['class'];
			$tpl['fields'][$index]['placeholder'] = $field['placeholder'];
			$tpl['fields'][$index]['rows'] = $field['rows'];

			if( $field['required'] ){
				$tpl['fields'][$index]['class'] .= ' valida ';
				$tpl['fields'][$index]['label'] .= ' *';
			}

			if ( strstr($field['type'],"file") ) {
				$name = strstr($field['type'],"multiple") ? $name.'[]' : $name;
				$multiple = strstr($field['type'],"multiple") ? true : false;
				$tpl['fields'][$index]['name'] = $name;
				$tpl['fields'][$index]['multiple'] = $multiple;
			}

		}

	}

	// echo "<pre>"; var_export($tpl); exit;

	$return = $latte->renderToString($TemplatePath, $tpl);

	// die($return);

	return $return;

}

/**
 * Recebe o json do form + post dos campos e monta html do email
 * @param  	json_form
 * @param  	template  		template do email
 * @return 	[string]		corpo do email
 */

function formulario_email($json_form, $template) {

	// var_export($json_form); exit;
	// var_export($_POST); exit;

	$i = 0;
	$return = "";
	$post_form = $_POST;
	$set_name = isset($GLOBALS['formulario_set_names']) ? $GLOBALS['formulario_set_names'] : true;

	// HTML template
	$html = file_get_contents($template) ? $template : '';
	$tpl_mail = new Template($html);

	foreach ($json_form['fields'] as $index => $campo) {

		$i++;

		if( isset($campo['group']) ){
			$tpl_mail->VALOR = $campo['group'];
			$tpl_mail->block('GROUP');
		}

		if( isset($campo['text']) ){
			$tpl_mail->VALOR = nl2br($campo['text']);
			$tpl_mail->block('TEXT');
		}

		if( isset($campo['type']) && $campo['type']!='hidden' && !stristr($campo['type'],'file') ){

			$name = formulario_campo_name($campo['label']);
			$name = $set_name ? $name : "campo_$index";

			$valor = $post_form[$name];
			$valor = $campo['type']=='textarea' ? nl2br($valor) : "$valor";

			if( !empty($campo['cols'] && $campo['cols']<12) ){
				// $width = (100/12)*$campo['cols'];
				// $tpl_mail->STYLE = "width: {$width}%; float: left";
				$tpl_mail->STYLE = "width: 50%; float: left";
			}else{
				$tpl_mail->STYLE = "width: 100%; float: left";
			}

			$tpl_mail->COLS = !empty($campo['cols']) ? $campo['cols'] : 12;
			$tpl_mail->LABEL = $campo['label'];
			$tpl_mail->VALOR = $valor;

			$tpl_mail->block('CAMPO');

		}

		$tpl_mail->block('ITEM');

	}

	$return = $tpl_mail->parse();

	return $return;

}

function formulario_salvar($contato) {

	$nome 		= !empty($contato['nome']) ? $contato['nome'] : '';
	$email 		= !empty($contato['email']) ? $contato['email'] : '';
	$telefone 	= !empty($contato['telefone']) ? $contato['telefone'] : '';
	$mensagem 	= !empty($contato['mensagem']) ? $contato['mensagem'] : '';
	$origem 	= !empty($contato['origem']) ? $contato['origem'] : 'Site';

	$tags = '';
	$tags .= '<h1><h2><h3><h4>';
	$tags .= '<a><b><i><u>';
	$tags .= '<ul><li><ol>';

	$mensagem = html_entity_decode($mensagem);
	$mensagem = preg_replace("/<style\\b[^>]*>(.*?)<\\/style>/s", "", $mensagem);
	$mensagem = preg_replace("/<\/div>/im","\n",$mensagem);
	$mensagem = preg_replace("/<\/p>/im","\n",$mensagem);
	$mensagem = preg_replace("/<br\s?\/?>/im","\n",$mensagem);
	$mensagem = preg_replace("/<hr\s?\/?>/im","\n",$mensagem);
	$mensagem = strip_tags($mensagem, $tags);
	$mensagem = preg_replace('/^ /im', "", $mensagem);
	$mensagem = preg_replace('/ {2,}/im', " ", $mensagem);
	$mensagem = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/im", "\n", $mensagem);

	// die($mensagem);

	$sql = "INSERT INTO fbz_mensagens
			(data, nome, email, fone, mensagem, origem)
			values
			(NOW(),'$nome', '$email', '$telefone', '$mensagem', '$origem')";

	query($sql);

	return true;

}

// Pra não preciar evolver outras libs

function formulario_campo_name($campo){
	$campo = str_replace('-','',$campo);
	$campo = file_name_format($campo);
	$campo = str_replace('-','_',$campo);
	$campo = strtolower($campo);
	return $campo;
}