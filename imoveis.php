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

// Estatíscas da Busca
// ----------------------------------------------------------------------------

$GET = $_GET;
unset($GET['codigo']);
fbz_estatisca_busca($GET);
// var_export($GET); exit;

// Busca
// ----------------------------------------------------------------------------

// Paginação
$regs_pp 	= 12;
$pg			= is_numeric(get("pg")) ? get("pg") : 1;
$comeca_em	= ($pg>=1) ? ($pg-1)*$regs_pp : 1;

// Ordem
$_GET['ordem'] = isset($_GET['ordem']) ? $_GET['ordem'] : 0;
$orderby = $fbz_order_array[$_GET['ordem']]['order'];

// var_export($fbz_order_array); exit;

// Monta SQL da Busca
$busca 		 = fbz_sql_busca();
$querystring = $busca[1];
$sql_where   = $busca[0];

// Query para string 'codigo' ou 'q' > Código ou Empreendimento
if( !empty($_GET['q']) || !empty($_GET['codigo']) ){
	$busca = isset($_GET['q']) ? trim($_GET['q']) : trim($_GET['codigo']);
	if( is_numeric($busca) ){
		$sql_where = " (codigo like '$busca' OR id = '$busca')";
	} else {
		$sql_where = " (codigo like '$busca' OR empreendimento like '%$busca%')";
	}
}

// Alto padrão
if( !empty($_GET['altopadrao']) ){
	$sql_where = " AND alto_padrao='S' and status = 'venda' ";
	$titulo = 'Imóveis de Alto Padrão';
}

// echo $sql_where;

$imoveis = fbz_sql_select( $fbz_sql_query_imoveis,
	array(
		'where' => $sql_where,
		'limit' => $comeca_em.",".$regs_pp,
		'orderby' => $orderby,
		// 'debug' => 1
	)
);

// Redireciona busca código
if( (!empty($_GET['codigo']) || !empty($_GET['q']) ) && count($imoveis)==1 ){
	$url = fbz_imovel_url($imoveis[0]);
	$url = $fbz_url_site . $url;
	redir($url);
}

// var_export($imoveis); exit;

$total = fbz_sql_select(
	"SELECT count(*) from imoveis WHERE $fbz_sql_imoveis_where /*WHERE*/",
	array(
		'where' => $sql_where,
		'return' => 'count',
		// 'debug' => 1
	)
);

// var_export($total); exit;

// Paginação
// ----------------------------------------------------------------------------

if($total>$regs_pp) {
	fbz_paginacao( $pg, $regs_pp, $total );
	// echo "<pre>"; var_export($tpl['paginacao']); exit;
}

// Order By
// ----------------------------------------------------------------------------

parse_str($_SERVER['QUERY_STRING'],$query_parts);
unset($query_parts['ordem']);
$new_query = http_build_query($query_parts);

$tpl['order']['query'] = $new_query;
$tpl['order']['page'] = current_page();
$tpl['order']['selected'] = $fbz_order_array[$_GET['ordem']]['label'];

fbz_orderby($fbz_order_array);

// echo "<pre>"; var_export($tpl['orderby']); exit;

// ----------------------------------------------------------------------------

// Titulo
if( empty($titulo) ){
	if(get("q")!=""){
		$titulo = 'Busca por "'.get("q").'"';
	}else{
		$titulo = fbz_ucfirst(fbz_imoveis_title());
	}
}

// HTML Head
$tpl['site']['title'] = $titulo.', Página '.$pg.' - '.$site['geral']['title'];
$tpl['site']['desc']  = "Na {$site['geral']['nome']} você encontra mais de {$total} $titulo." ;

$tpl['titulo'] = $titulo;
$tpl['pagina'] = $pg;
$tpl['total_pagina'] = ceil($total/$regs_pp);
$tpl['total_imoveis'] = $total;

// Imóveis
// ----------------------------------------------------------------------------

if( sizeof($imoveis)>0 ) {
	foreach ($imoveis as $idxImovel => $cardImovel) {
		include("inc/imovel.php");
		$tpl['imoveis'][$idxImovel] = $cardImovel;
	}
	// echo "<pre>"; var_export($tpl['imoveis']); exit;
}

// Exibir Mapa
// ----------------------------------------------------------------------------

$tpl['mapa']['show'] = $site['layout']['mapa'] ? true : false;

// *** DEPRECIADA ***
// leaflet.custer.js + markers.php

if( $template->hasBlock('xmapa') ) {

	$marker = array();

	// select para mapa
	$marcadores	= fbz_sql_select(
		$fbz_sql_query_marcadores,
		array(
			'where' => $sql_where,
			'limit' => 1000,
			'cache' => 1800, // 30 minutos
		)
	);

	if( sizeof($marcadores)>0 ) {

		$i=0;
		foreach ($marcadores as $marcador) {
			$marker[$i]['lat'] 		= $marcador['latitude'];
			$marker[$i]['lng'] 		= $marcador['longitude'];
			$marker[$i]['codigo'] 	= $marcador['id'];
			$marker[$i]['label'] 	= fbz_format_valor(fbz_imovel_valor($marcador));
			$marker[$i]['ico'] 		= "img/marker/marker.png";
			$i++;
		}

	}

	// var_export($marker);

	$tpl['mapa']['marcadores'] = json_encode($marker);

}

// Filtros Selecionados
// ----------------------------------------------------------------------------

if( $template->hasBlock('filtros') ) {

	$actual_link = "https://$_SERVER[HTTP_HOST]?$_SERVER[QUERY_STRING]";

	parse_str(parse_url($actual_link, PHP_URL_QUERY), $params);

	unset($params['pg']);
	unset($params['ordem']);
	unset($params['valor_de']);
	unset($params['valor_ate']);

	$params = array_filter($params, function($valor) {
		return $valor !== '' && $valor !== null;
	});

	// var_export($actual_link);

	foreach ($params as $key => $value) {

		$value = explode(',',$value);
		$value = array_filter($value);

		foreach($value as $value){

			$filtro_url = http_build_query($params);
			$filtro_url = urldecode($filtro_url);
			$filtro_url = str_replace($value,'',$filtro_url);

			if($key=='dorm') $value = "$value dormitorio(s)";
			if($key=='suites') $value = "$value suíte(s)";
			if($key=='vagas') $value = "$value vaga(s)";

			$tpl['filtros'][] = array(
				'label' => ucfirst(str_replace('-',' ',$value)),
				'url' => "imoveis?$filtro_url"
			);

		}

	}

	// echo "<pre>"; var_export($tpl['filtros']); exit;

}

// Mais Links
// ----------------------------------------------------------------------------

if( $template->hasBlock('links') ) {

	// Tipos com dormitórios e vagas

    $options = array(
        'dorm' => array(1, 2, 3),
        'vagas' => array(1, 2, 3),
    );

    $status = isset($_GET['status']) ? str_replace('-', ' ',$_GET['status']): '';
    $cidade = isset($_GET['cidade']) ? str_replace('-', ' ',$_GET['cidade']): '';
    $tipo = isset($_GET['tipo']) ? str_replace('-',' ',$_GET['tipo']) : '';
	$tipos = [
		"",
		"apartamento",
		"cobertura",
		"casa",
		"casa em condominio",
		"sobrado",
		"sobrado em condomio"
	];

	$title_links_1 = 'Mais imóveis';
	$title_links_1 .= $cidade!='' ? ' em '. fbz_ucfirst($cidade) : '';

	$tpl['links1']['title'] = $title_links_1;

	$tipo = explode(',',$tipo);

	foreach ($tipo as $tipo) {
		if (in_array($tipo, $tipos)) {
			foreach ($options as $key => $values) {
				foreach ($values as $key2 => $value) {

					$s = ($value > 1) ? 's' : '';
					$s = '';

					$link_label = ($tipo != '') ? $tipo : 'Imóveis';
					$link_label .= " com $value $key$s";
					$link_label .= ($cidade != '') ? " em $cidade" : '';

					$link_data = array();
					if (!empty($_GET['status'])) $link_data['status'] = $_GET['status'];
					if (!empty($_GET['tipo'])) $link_data['tipo'] = $tipo;
					if (!empty($_GET['cidade'])) $link_data['cidade'] = $_GET['cidade'];
					$link_data[$key] = $value;

					$link_url = 'imoveis?' . http_build_query($link_data);

					$tpl['links1']['links'][] = [
						'label' => fbz_ucfirst($link_label),
						'url' => $link_url
					];

				}
			}
		}
	}

	// echo "<pre>"; var_export($tpl['links1']); exit;

	// Principais bairros da cidade

	$title_links_2 = 'Principais bairros';
	$title_links_2 .= $cidade!='' ? ' em '. fbz_ucfirst($cidade) : '';

	$tpl['links2']['title'] = $title_links_2;

	$bairros_where = '';
	if($status!='') $bairros_where .= " AND status like '%" . str_replace(' ','%',$status) ."%'";
	if($tipo!='') $bairros_where .= " AND categoria like '%" . str_replace(' ','%',$tipo) ."%'";
	if($cidade!='') $bairros_where .= " AND cidade like '%" . str_replace(' ','%',$cidade) ."%'";

	$sql_bairros =" SELECT bairro, COUNT(*) AS total_imoveis
					FROM imoveis
					WHERE
					$fbz_sql_imoveis_where
					$bairros_where
					GROUP BY bairro
					ORDER BY total_imoveis DESC
					LIMIT 6";

	// echo $sql_bairros;

	$bairros = fbz_sql_select($sql_bairros);

	if( count($bairros)>0 ){
		foreach ($bairros as $bairro) {

			// echo $bairro['bairro'] . " > " . $bairro['total_imoveis'];

			$link_label = ($tipo != '') ? $tipo : ' imóveis';
			$link_label .= " em {$bairro['bairro']}";
			$link_label .= ($cidade != '') ? ", $cidade" : '';
			$link_label .= " ({$bairro['total_imoveis']})";

			$link_data = array();
			if (!empty($_GET['status'])) $link_data['status'] = $_GET['status'];
			if (!empty($_GET['tipo'])) $link_data['tipo'] = $_GET['tipo'];
			if (!empty($_GET['cidade'])) $link_data['cidade'] = $_GET['cidade'];
			$link_data['bairro'] = str_replace(' ', '-',mb_strtolower($bairro['bairro']));

			$link_url = 'imoveis?' . http_build_query($link_data);

			$tpl['links2']['links'][] = [
				'label' => fbz_ucfirst($link_label),
				'url' => $link_url
			];

		}
	}

	// echo "<pre>"; var_export($tpl['links2']); exit;

}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);