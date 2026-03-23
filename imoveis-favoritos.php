<?php

include("inc/setup.php");

// Template Start
// ----------------------------------------------------------------------------

$tpl = [];
$latte = new Latte\Engine;
$TemplatePath = template_path(current_page());
$template = $latte->createTemplate($TemplatePath);
$latte->setTempDirectory('cache');

include("inc/header.php");

// Busca
// ----------------------------------------------------------------------------

$_GET['favoritos'] = true;

// Busca
$busca 		 = fbz_sql_busca();
$querystring = $busca[1];
$sql_where   = $busca[0];

// echo $sql_where; exit;

$imoveis = fbz_sql_select(
	$fbz_sql_query_imoveis, array(
		'where' => $sql_where,
		'orderby' => "IF(status LIKE '%aluguel%', valor_aluguel, 0) ASC,
    				  IF(status LIKE '%venda%', valor_venda, 0) ASC",
		// 'debug' => true
	)
);

$total = fbz_sql_select(
	$fbz_sql_query_imoveis, array(
		'where' => $sql_where,
		'return' => 'count'
	)
);

// var_export($imoveis);

// Imóveis Favoritos
// ----------------------------------------------------------------------------

$titulo = 'Meus Imóveis Favoritos';

// HTML Head
$tpl['site']['title'] = $titulo.', Página '.$pg.' - '.$site['geral']['title'];
$tpl['site']['desc']  = $titulo .' em '.  $site['geral']['nome'];

$tpl['titulo'] = $titulo;
$tpl['total_imoveis'] = $total;

if( sizeof($imoveis)>0 ) {
	foreach ($imoveis as $idxImovel => $cardImovel) {
		include("inc/imovel.php");
		$tpl['imoveis'][$idxImovel] = $cardImovel;
	}
	// echo "<pre>"; var_export($tpl['imoveis']); exit;
}

// Enviar Favoritos
// ----------------------------------------------------------------------------

if( $template->hasBlock('form_favoritos') ) {

	$_GET['favoritos'] = "true";

	$str_codigos = "";
	$busca = fbz_sql_busca();
	$sql_where = $busca[0];

	$imoveis = fbz_sql_select(
		$fbz_sql_query_imoveis,
		array( 'where' => $sql_where )
	);

	foreach ($imoveis as $imovel) {
		$str_codigos .= $imovel['id'].',';
	}

	$str_codigos = rtrim($str_codigos,',');

	$fav_form['fields'][] = array(
		"label" => "url_retorno",
		"type" => "hidden",
		"class" => "valida",
		"value" => $_SERVER['REQUEST_URI']
	);
	$fav_form['fields'][] = array(
		"label" => "enviar",
		"type" => "hidden",
		"class" => "valida",
		"value" => 1
	);
	$fav_form['fields'][] = array(
		"label" => "codigos",
		"type" => "hidden",
		"class" => "valida",
		"value" => $str_codigos
	);
	$fav_form['fields'][] = array(
		"label" => "Nome",
		"placeholder" => "Nome",
		"type" => "text",
		"class" => "valida",
		"cols" => 6
	);
	$fav_form['fields'][] = array(
		"label" => "E-mail",
		"placeholder" => "E-mail",
		"type" => "text",
		"class" => "valida valida-email",
		"cols" => 6
	);
	$fav_form['fields'][] = array(
		"label" => "Nome Destinatário",
		"placeholder" => "Nome Destinatário",
		"type" => "text",
		"class" => "valida",
		"cols" => 6
	);
	$fav_form['fields'][] = array(
		"label" => "E-mail Destinatário",
		"placeholder" => "E-mail Destinatário",
		"type" => "text",
		"class" => "valida valida-email",
		"cols" => 6
	);
	$fav_form['fields'][] = array(
		"label" => "Mensagem",
		"placeholder" => "Sua mensagem",
		"type" => "textarea",
		"class" => "valida",
		"cols" => 12
	);

	$tpl['form_favoritos']['form'] = formulario_html($fav_form);
	$tpl['form_favoritos']['action'] = 'task/favoritos';
	$tpl['form_favoritos']['captcha'] = fbz_captcha();

}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);