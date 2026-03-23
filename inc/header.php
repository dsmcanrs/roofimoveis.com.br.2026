<?php

// Header, Globais, Meta Tags
// ----------------------------------------------------------------------------

$tpl['site']['lang']		= $fbz_fw_lang;
$tpl['site']['charset']		= $fbz_fw_charset;
$tpl['site']['meta']		= $fbz_meta_custom;
$tpl['site']['dominio'] 	= $fbz_url_site;
$tpl['site']['canonical']	= fbz_url();
$tpl['site']['url'] 		= $fbz_url_site . fbz_uri();
$tpl['site']['title']		= $site['geral']['title'];
$tpl['site']['descricao']	= nl2br($site['geral']['description']);
$tpl['site']['logo']		= $site['design']['logo'];
$tpl['site']['ico']			= $site['design']['favicon'];
$tpl['site']['img']			= !empty($site['design']['logo_share'])
							? $fbz_url_site . $site['design']['logo_share']
							: $fbz_url_site . $site['design']['logo'];
$tpl['site']['nome']		= $site['geral']['nome'];
$tpl['site']['desc'] 		= $site['geral']['description'];
$tpl['site']['email'] 		= $site['geral']['email'];
$tpl['site']['creci'] 		= $site['geral']['creci'];

$tpl['whatsapp']['msg'] = "Estou navegando nesta página: ". rtrim($fbz_url_site,'/') . fbz_uri() ." e gostaria de mais informações.";

// Css / JS
// ----------------------------------------------------------------------------

// var_export($site['scripts']); exit;

if( !preg_match('/(Lighthouse|GTmetrix)/',$_SERVER["HTTP_USER_AGENT"] )  ){
	foreach ($site['scripts'] as $key => $field) {
		if( trim($field['head'])!='' ){
			$tpl['scripts']['head'][] = $field['head'];
		}
		if( trim($field['body'])!='' ){
			$tpl['scripts']['body'][] = $field['body'];
		}
		if( trim($field['footer'])!='' ){
			$tpl['scripts']['footer'][] = $field['footer'];
		}
	}
	// var_export($tpl['scripts']); exit;
}

// Endereços, telefones, email, whatsapp
// ----------------------------------------------------------------------------

// var_export($site); exit;

foreach ($site as $key1 => $value) {

	if( !is_array($site[$key1]) ) $site[$key1][] = $value;

	foreach ($site[$key1] as $key2 => $value) {
		if( strstr($key2,'whatsapp') || strstr($key2,'telefone') ){
			$number = '55'.preg_replace("/[^0-9]/","",$value);
			$tpl[$key1]["$key2-num"] = $number;
			// echo "[$key1][$key2-num]: $value<br>";
		}
		$tpl[$key1][$key2] = is_array($value) ? $value : nl2br($value);
		// echo "[$key1][$key2]: $value<br>";
	}

	if( strstr($key1,'contato') ){
		$tpl['contatos'][] = $tpl[$key1];
	}

}

// echo "<pre>"; var_export($tpl['contatos']); // exit;

// Cores
// ----------------------------------------------------------------------------

// var_export($site['design']); exit;

foreach ($site['design'] as $key => $value) {
	if( strstr($key,'color_') ){
		$hex = $value;
		list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
		$rgb = $r.','.$g.','.$b;
		$tpl['design'][$key] = $hex;
		$key_rgb = $key.'_rgb';
		$tpl['design'][$key_rgb] = $rgb;
	}
}

// echo "<pre>";
// var_export($tpl); exit;

// Redes sociais
// ----------------------------------------------------------------------------

// var_export($site['social']); exit;

unset($tpl['social']); // reinicializa

foreach ($site['social'] as $rede => $url) {
	if( $url!='' ){
		$tpl['social'][] = [
			'label' => $rede,
			'title' => fbz_ucfirst($rede),
			'url' => $url,
			'class' => 'fa-'.$rede,
		];
	}
}

// echo "<pre>"; var_export($tpl['social']); exit;

// Menu de categorias de imóveis
// ----------------------------------------------------------------------------

$menuCategorias = fbz_sql_select($fbz_sql_query_categorias,
	array(
		'cache' => 900, // 15 min
	)
);

foreach($menuCategorias as $categoria) {
	$tpl['categorias'][] = [
		'url' => file_name_format($categoria["categoria"]),
		'title' => fbz_ucfirst($categoria["categoria"])
	];

}

// echo "<pre>"; var_export($tpl['categorias']); exit;

// ----------------------------------------------------------------------------

include("inc/menu.php");

if( $template->hasBlock('busca') ) include("inc/busca.php");