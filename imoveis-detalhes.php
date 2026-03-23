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

// ----------------------------------------------------------------------------

if( !isset($_GET['id']) || $_GET['id']=='' ) redir($fbz_url_site . '404');

$id = get("id");

$detalhes = fbz_sql_select(
	$fbz_sql_query_imoveis, array(
		'where' => "id = $id"
	)
);

$imovel = $detalhes[0];

// var_export($imovel); exit;

if( !$imovel['publicado'] ) redir($fbz_url_site . '404');

$fotos = fbz_sql_select(
	$fbz_sql_query_fotos,
	array(
		'where' => 'registro='.$imovel['id'],
	)
);

// Estatíscas da Busca: Códigos mais acessados
// ----------------------------------------------------------------------------

fbz_estatisca_busca($_GET);

// Título do Imóvel
// ----------------------------------------------------------------------------

$imovel_titulo = '';
$imovel_url = $fbz_url_site . fbz_imovel_url($imovel, $fbz_tipo_url);

// Verifica se é um empreendimento
$imovel_empreend = strtolower($imovel['categoria']) === 'empreendimento' && !empty($imovel['empreendimento']);

// Define o título baseado na categoria do imóvel
$imovel_titulo .= $imovel_empreend
    ? "{$imovel['empreendimento']} - "
    : "{$imovel['categoria']} para {$imovel['status']} em";

// Adiciona bairro, cidade e estado (se existirem)
$imovel_titulo .= !empty($imovel['bairro']) ? " {$imovel['bairro']}" : '';
$imovel_titulo .= !empty($imovel['cidade']) ? ", {$imovel['cidade']}" : '';
$imovel_titulo .= !empty($imovel['uf']) ? ", {$imovel['uf']}" : '';

// Formata e limpa o título
$imovel_titulo = fbz_ucfirst(trim($imovel_titulo));

// Usa o título personalizado se existir, caso contrário mantém o gerado
$imovel_titulo = !empty($imovel['titulo']) ? $imovel['titulo'] : $imovel_titulo;

// HTML Head
$tpl['site']['title'] = "{$imovel_titulo}, Código: {$imovel['codigo']} - {$site['geral']['title']}";
$tpl['site']['desc'] = $imovel['status'] .' > '. $imovel['categoria'] .' > '. $imovel['cidade'] .' > '. $imovel['bairro'] .' > '. $imovel['empreendimento'] .' > Código: '. $imovel['codigo'];
$tpl['site']['canonical'] = $imovel_url;
$tpl['site']['img'] = $fotos[0]['foto_sm'];

// Dados do Imóvel
// ----------------------------------------------------------------------------

$tpl['imovel'] = $imovel;
$tpl['imovel']['titulo'] = $imovel_titulo;

$tpl['imovel']['empreendimento'] = fbz_ucfirst($imovel['empreendimento']);
$tpl['imovel']['categoria'] = fbz_ucfirst($imovel['categoria']);
$tpl['imovel']['bairro'] = fbz_ucfirst($imovel['bairro']);
$tpl['imovel']['cidade'] = fbz_ucfirst($imovel['cidade']);

$moeda = 'R$ ';
if( stristr($imovel['pais'],'unidos') ) $moeda = 'US$ ';
if( stristr($imovel['pais'],'portugal') ) $moeda = 'Є ';

if( stristr($imovel['status'],'aluguel')
	&& ($imovel['valor_iptu']>0 || $imovel['valor_condominio']>0) ){
	$aluguel_total = $imovel['valor_aluguel']+$imovel['valor_iptu']+$imovel['valor_condominio'];
}else{
	$aluguel_total = 0;
}

$tpl['imovel']['moeda'] = $moeda;
$tpl['imovel']['valor_antigo'] = fbz_format_valor($imovel['valor_antigo']);
$tpl['imovel']['valor_venda'] = fbz_format_valor($imovel['valor_venda']);
$tpl['imovel']['valor_aluguel'] = fbz_format_valor($imovel['valor_aluguel']);
$tpl['imovel']['valor_diaria'] = fbz_format_valor($imovel['valor_diaria']);
$tpl['imovel']['valor_aluguel_total'] = fbz_format_valor($aluguel_total);
$tpl['imovel']['valor_iptu'] = fbz_format_valor($imovel['valor_iptu']);
$tpl['imovel']['valor_condominio'] = fbz_format_valor($imovel['valor_condominio']);

if( $imovel["empreendimento"]!='' && fbz_strtolower($imovel["categoria"])=='empreendimento'){
	$tpl['imovel']['mostrar_valores'] = false;
	$tpl['imovel']['mostrar_ficha'] = false;
}else{
	$tpl['imovel']['mostrar_valores'] = true;
	$tpl['imovel']['mostrar_ficha'] = true;
}

if( !empty($imovel['descricao']) ){
	$descricao = $imovel['descricao'];
	$desc_empr = preg_replace('/<p>(\s|&nbsp;)*<\/p>/i', '', $desc_empr);
	$tpl['imovel']['descricao'] = $descricao;
}

if( !empty($imovel['descricao_empreendimento']) ){
	$desc_empr = $imovel['descricao_empreendimento'];
	$desc_empr = preg_replace('/<p>(\s|&nbsp;)*<\/p>/i', '', $desc_empr);
	$desc_empr = preg_replace('/<p>(\s|&nbsp;)*<br\s*\/?>\s*<\/p>/i', '', $desc_empr);
	$tpl['imovel']['descricao_empreendimento'] = $desc_empr;
}

// echo "<pre>"; var_export($tpl['imovel']); exit;

// Características Sim/Não
// ----------------------------------------------------------------------------

$imovel_infra['Características do Imóvel'] = explode(";",$imovel['infra_imovel']);
$imovel_infra["Características do Condomínio"] = explode(";",$imovel['infra_comum']);

// var_export($imovel_infra); exit;

foreach( $imovel_infra as $index => $infra ){
	foreach( $infra as $item ){
		if( trim($item)!='' ){
			$tpl['imovel']['infra'][$index][] = $item;
		}
	}
}

// echo "<pre>"; var_export($tpl['imovel']['infra']); exit;

// Mapa
// ----------------------------------------------------------------------------

if ( $site['layout']['mapa'] && $imovel['longitude']!='' && $imovel['latitude']!='' ) {

	$markers[] = array(
		"lat" => $imovel['latitude'],
		"lng" => $imovel['longitude'],
		"ico" => 'img/marker.png',
		"type" => "marker",
		// "info" => 'Oi',
	);

	$tpl['imovel']['mapa'] = json_encode($markers,true);

}

// Vídeo / Youtube
// ----------------------------------------------------------------------------

$video = $imovel['youtube'];
$videos = json_decode($imovel['videos'], true);

if( is_array($videos) ){
	foreach($videos as $array){
		if( $array['Video']!='' &&  $array['ExibirNoSite']=='Sim' ){
			$video = $array['Video'];
			break;
		}
	}
}

if( $video!='') {
	$video = youtube_video($video);
	$tpl['imovel']['video'] = $video;
}

// Tour Virtual
// ----------------------------------------------------------------------------

$tour = trim($imovel['tour360']);

if( $tour!='') {
	$tpl['imovel']['tour'] = $tour;
}

// Galeria Fotos
// ----------------------------------------------------------------------------

if (sizeof($fotos)>0) {
	foreach ($fotos as $foto) {
		$tpl['imovel']['fotos'][] = [
			'desc' => $foto['descricao'],
			'g' => $foto['foto_lg'],
			'p' => $foto['foto_sm'],
		];
	}
}

// Unidades no Condomínio
// ----------------------------------------------------------------------------

if( $template->hasBlock('no_condominio') ) {

	// -- AND valor_venda > 0
	// -- AND valor_venda >= 0.8 * {$imovel["valor_venda"]}
	// -- AND valor_venda <= 1.2 * {$imovel["valor_venda"]}

	$condominio = sql_replace($imovel["empreendimento"]);
	$where = " id<>{$imovel["id"]}
				AND status = '{$imovel["status"]}'
				AND IF('{$imovel["categoria"]}'<>'Empreendimento',categoria='{$imovel["categoria"]}',1=1)
				AND categoria<>'Empreendimento'
				AND empreendimento like '{$condominio}'";

	$semelhantes = fbz_sql_select(
		$fbz_sql_query_imoveis,
		array(
			'where' => $where,
			'orderby' => 'valor_venda asc',
			// 'debug' => 1
		)
	);

	if ( is_array($semelhantes) && sizeof($semelhantes)>0) {
		foreach ($semelhantes as $idxImovel => $cardImovel) {
			include("inc/imovel.php");
			$tpl['no_condominio'][$idxImovel] = $cardImovel;
		}
	}

}

// Semelhantes
// ----------------------------------------------------------------------------

if( $template->hasBlock('semelhantes') && $imovel["categoria"]!='Empreendimento' ) {

	$semelhantes = fbz_sql_imoveis_semelhantes($imovel);

	if ( is_array($semelhantes) && sizeof($semelhantes)>0) {
		foreach ($semelhantes as $idxImovel => $cardImovel) {
			include("inc/imovel.php");
			$tpl['semelhantes'][$idxImovel] = $cardImovel;
		}
	}

	// echo "<pre>"; var_export($tpl['semelhantes']); exit;

}

// Sobre o Empreendimento
// ----------------------------------------------------------------------------

if( $template->hasBlock('o_empreendimento') ) {

	$condominio = sql_replace($imovel["empreendimento"]);
	$where = " id<>{$imovel["id"]}
			   AND categoria = 'empreendimento'
			   AND empreendimento like '{$condominio}'";

	$emprendimento = fbz_sql_select(
		$fbz_sql_query_imoveis,
		array(
			'where' => $where,
			// 'cache' => 900, // 15 min
			// 'debug' => 1
		)
	);

	// var_export($emprendimento); exit;

	if (sizeof($emprendimento)>0) {

		$emprendimento = $emprendimento[0];
		$emprendimento_url = fbz_imovel_url($emprendimento,$fbz_tipo_url);

		$tpl['o_empreendimento']['id'] = $emprendimento['id'];
		$tpl['o_empreendimento']['url'] = $emprendimento_url;
		$tpl['o_empreendimento']['nome'] = $emprendimento['empreendimento'];
		$tpl['o_empreendimento']['descricao'] = fbz_first_paragraph($emprendimento['descricao']);

		$fotos = fbz_sql_select(
			$fbz_sql_query_fotos,
			array(
				'where' => 'imovel='.$emprendimento['id'],
				// 'cache' => 1800, // 15 min
			)
		);

		if (sizeof($fotos)>0) {
			foreach ($fotos as $foto) {
				$fotoe['descricao']	= $foto['descricao'];
				$fotoe['foto'] = $foto['foto_lg'];
				$fotoe['fotop'] = $foto['foto_sm'];
				$tpl['o_empreendimento']['fotos'][] = $fotoe;
			}
		}

	}

	// echo "<pre>"; var_export($tpl['o_empreendimento']); exit;

}

// Corretores
// ----------------------------------------------------------------------------

$corretor = fbz_corretor($imovel['id']);

// var_export($corretor);

if( !empty($corretor) && count($corretor)>0 ){

	if( !empty($corretor["fone_celular"]) ){
		$fone_celular_num = '55'.preg_replace("/[^0-9]/","",$corretor["fone_celular"]);
	}

	$corretor_foto 	= $corretor["foto"];
	$corretor_url 	= $fbz_url_site . "corretor/" . file_name_format($corretor["nome"]);

	$tpl['corretores'][] = [
		'url' => $corretor_url,
		'creci' => $corretor["creci"],
		'nome' => fbz_ucfirst($corretor["nome"]),
		'email' => $corretor["email"],
		'fone' => $corretor["fone_celular"],
		'fone_num' => $fone_celular_num,
		'foto' => $corretor_foto,
	];

}

// Breadcrumb
// ----------------------------------------------------------------------------

if( $template->hasBlock('breadcrumb') ) {

    $options = array(
        'finalidade' => stristr('venda',$imovel['status']) ? 'venda' : 'aluguel' ,
        'tipo' => $imovel['categoria'],
        'cidade' => $imovel['cidade'],
        'bairro' => $imovel['bairro'],
    );

	$querystring = '';

	foreach ($options as $key => $value) {

		$value = mb_strtolower($value);
		$querystring .= "$key=$value&";

		$tpl['breadcrumb'][] = [
			'label' => fbz_ucfirst($value),
			'url' => $fbz_url_site . 'imoveis?' . str_replace(' ','+',$querystring)
		];

	}

	// echo "<pre>"; var_export($tpl['breadcrumb']); exit;

}

// Form Contato
// ----------------------------------------------------------------------------

if( $template->hasBlock('form_contato') ) {

	$placa = $imovel["codigo"]!='' ? $imovel["codigo"] : $imovel["id"];

	$mensagem = "";
	$mensagem .= "Olá, gostaria de mais informações sobre o imóvel: ";
	$mensagem .= fbz_ucfirst($imovel["categoria"]).' ';
	$mensagem .= fbz_ucfirst($imovel["bairro"]).' ';
	$mensagem .= fbz_ucfirst($imovel["cidade"]).', Código: ';
	$mensagem .= $placa.' ';

	$contato_form = array();

	$contato_form['fields'][] = array(
		"label" => "enviar",
		"type" => "hidden",
		"class" => "valida",
		"value" => 1
	);
	$contato_form['fields'][] = array(
		"label" => "url_retorno",
		"type" => "hidden",
		"class" => "valida",
		"value" => $_SERVER['REQUEST_URI']
	);
	$contato_form['fields'][] = array(
		"label" => "codigo",
		"type" => "hidden",
		"class" => "valida",
		"value" => $imovel["codigo"]
	);
	$contato_form['fields'][] = array(
		"label" => "status",
		"type" => "hidden",
		"class" => "valida",
		"value" => $imovel["status"]
	);
	$contato_form['fields'][] = array(
		"label" => "Nome",
		"placeholder" => "Nome",
		"type" => "text","class" => "valida",
		"cols" => 12
	);
	$contato_form['fields'][] = array(
		"label" => "E-mail",
		"placeholder" => "E-mail",
		"type" => "text",
		"class" => "valida valida-email",
		"cols" => 6
	);
	$contato_form['fields'][] = array(
		"label" => "Telefone",
		"placeholder" => "Telefone",
		"type" => "text",
		"class" => "valida mask-fone",
		"cols" => 6
	);
	$contato_form['fields'][] = array(
		"label" => "Mensagem",
		"placeholder" => "Mensagem",
		"type" => "textarea",
		"class" => "valida",
		"cols" => 12,
		"value" => $mensagem,
		"rows" => 2
	);

	$tpl['form_contato']['form'] = formulario_html($contato_form);
	$tpl['form_contato']['action'] = $fbz_url_site . 'task/lead-imovel';
	$tpl['form_contato']['captcha'] = fbz_captcha();

}

// Form Agendar
// ----------------------------------------------------------------------------

if( $template->hasBlock('form_agendar') ) {

	$placa = $imovel["codigo"]!='' ? $imovel["codigo"] : $imovel["id"];

	$mensagem = "";
	$mensagem .= "Olá, gostaria de agendar uma visita no imóvel: ";
	$mensagem .= fbz_ucfirst($imovel["categoria"]).' ';
	$mensagem .= fbz_ucfirst($imovel["bairro"]).' ';
	$mensagem .= fbz_ucfirst($imovel["cidade"]).', Código: ';
	$mensagem .= $placa.' ';

	$contato_form = array();

	$contato_form['fields'][] = array(
		"label" => "enviar",
		"type" => "hidden",
		"class" => "valida",
		"value" => 1
	);
	$contato_form['fields'][] = array(
		"label" => "url_retorno",
		"type" => "hidden",
		"class" => "valida",
		"value" => $_SERVER['REQUEST_URI']
	);
	$contato_form['fields'][] = array(
		"label" => "codigo",
		"type" => "hidden",
		"class" => "valida",
		"value" => $imovel["codigo"]
	);
	$contato_form['fields'][] = array(
		"label" => "status",
		"type" => "hidden",
		"class" => "valida",
		"value" => $imovel["status"]
	);
	$contato_form['fields'][] = array(
		"label" => "Nome",
		"placeholder" => "Nome",
		"type" => "text","class" => "valida",
		"cols" => 12
	);
	$contato_form['fields'][] = array(
		"label" => "E-mail",
		"placeholder" => "E-mail",
		"type" => "text",
		"class" => "valida valida-email",
		"cols" => 6
	);
	$contato_form['fields'][] = array(
		"label" => "Telefone",
		"placeholder" => "Telefone",
		"type" => "text",
		"class" => "valida mask-fone",
		"cols" => 6
	);
	$contato_form['fields'][] = array(
		"label" => "Mensagem",
		"placeholder" => "Mensagem",
		"type" => "textarea",
		"class" => "valida",
		"cols" => 12,
		"value" => $mensagem,
		"rows" => 2
	);

	$tpl['form_agendar']['form'] = formulario_html($contato_form);
	$tpl['form_agendar']['action'] = $fbz_url_site . 'task/lead-imovel';
	$tpl['form_agendar']['captcha'] = fbz_captcha();

}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);