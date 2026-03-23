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

// HTML Head
$tpl['site']['title'] = $site['geral']['title'];
$tpl['site']['desc']  = $site['geral']['description'];

// Full Banner
// ----------------------------------------------------------------------------

if( $template->hasBlock('fullbanner') ) {

	$tpl['fullbanner'] = [];

	$sql_fb = query("SELECT * FROM banners
				     WHERE ativo=1 and posicao = 0
				     ORDER BY ordem");

	if( query_num($sql_fb)>0 ) {

		while ($fullbanner = fetch($sql_fb)) {

			$fullbanner["titulo"] 	= nl2br($fullbanner["titulo"]);
			$fullbanner["texto"] 	= nl2br($fullbanner["texto"]);
			$fullbanner["lg"] 		= str_replace('../','',$fullbanner["lg"]);;
			$fullbanner["video"] 	= youtube_video($fullbanner["video"]);

			if($fullbanner["target"]=='') 	$fullbanner["target"] = "_parent";
			if($fullbanner["link"]=='') 	$fullbanner["link"] = '#';

			$tpl['fullbanner'][] = $fullbanner;

		}

    }

}

// Banners
// ----------------------------------------------------------------------------

if( $template->hasBlock('banner') ) {

	$tpl['banner'] = [];

	$sql_fb = query("SELECT * FROM banners
				     WHERE ativo=1 and posicao = 1
				     ORDER BY ordem");

	if( query_num($sql_fb)>0 ) {

		while ($banner = fetch($sql_fb)) {

			$banner["titulo"] 	= nl2br($banner["titulo"]);
			$banner["texto"] 	= nl2br($banner["texto"]);
			$banner["img"] 		= trim(str_replace('../','',$banner["lg"]));

			if($banner["target"]=='') 	$banner["target"] = "_parent";
			if($banner["link"]=='') 	$banner["link"] = '#';

			$tpl['banner'][] = $banner;

		}

    }

}

// Pop-up
// ----------------------------------------------------------------------------

if( $template->hasBlock('popup') ) {

	$tpl['popup'] = [];

	$query_pop = query("SELECT * FROM banners
			 	   WHERE ativo=1 and posicao = 2
				   ORDER BY RAND() LIMIT 1");

	if( query_num($query_pop)>0 ) {

		while ($popup = fetch($query_pop)) {

			$popup["titulo"] 	= nl2br($popup["titulo"]);
			$popup["texto"] 	= nl2br($popup["texto"]);
			$popup["img"] 		= str_replace('../','',$popup["lg"]);;

			if($popup["target"]=='') 	$popup["target"] = "_parent";
			if($popup["link"]=='') 	$popup["link"] = '#';

			// var_export($popup); exit;

			$tpl['popup'][] = $popup;

		}

    }

}

// Destaques
// ----------------------------------------------------------------------------

if( $template->hasBlock('destaques') ) {

	$tpl['destaques'] = [];

	$num_destaques = 9;

	$destaques[] = array(
		"finalidade" => "venda",
		"titulo" => "Oportunidades de Venda",
		"chamada" => "Imóveis selecionados com preços imperdíveis para comprar agora.",
		"imoveis" => fbz_sql_select(
			$fbz_sql_query_imoveis,
			array(
				'where' => " AND status like 'venda' AND categoria not like 'empreendimento%' ",
				'limit' => $num_destaques,
				'orderby' => "destaque_web=1 desc",
				// 'cache' => 900, // 15 min
				// 'debug' => true,
			)
		)
	);

	$destaques[] = array(
		"finalidade" => "aluguel",
		"titulo" => "Oportunidades de Locação",
		"chamada" => "Encontre o aluguel ideal em Jacareí e região, com as melhores ofertas do mercado.",
		"imoveis" => fbz_sql_select(
			$fbz_sql_query_imoveis,
			array(
				'where' => " AND status like 'aluguel' AND categoria not like 'empreendimento%' ",
				'limit' => $num_destaques,
				'orderby' => "destaque_web=1 desc",
				// 'cache' => 900, // 15 min
				// 'debug' => true,
			)
		)
	);

	// var_export($destaques); exit;

	foreach ($destaques as $idxSecao => $secao) {

		$_GET['status'] = $secao['finalidade'];

		$tpl['destaques'][$idxSecao] = $secao;

		shuffle($secao['imoveis']);

		if( count($secao['imoveis'])>0 ){
			foreach ($secao['imoveis'] as $idxImovel => $cardImovel) {
				include("inc/imovel.php");
				$tpl['destaques'][$idxSecao]['imoveis'][$idxImovel] = $cardImovel;
			}
		}

	}

	// echo "<pre>"; var_export($tpl['destaques']); exit;

}

// Blog
// ----------------------------------------------------------------------------

if( $template->hasBlock('blog') && $site['layout']['blog'] ) {

	$tpl['blog'] = [];

	$sqlb = "SELECT * FROM noticias
			 WHERE ativa=1
			 ORDER BY data DESC LIMIT 9";

	$qryb = query($sqlb);

	if (query_num($qryb)>0) {
		while($post = fetch($qryb)) {

			$dia = date('d', strtotime($post["data"]));
			$mes = ucfirst(monthname(date('m', strtotime($post["data"]))));
			$ano = date('Y', strtotime($post["data"]));

			$media = trim(blog_media($post));

			$post['data']		= "$dia de  $mes de $ano";
			$post['url'] 		= "blog/". file_name_format($post["titulo"]) .'/'.$post["id"];
			$post['titulo'] 	= $post["titulo"];
			$post['chamada'] 	= $post["chamada"];
			$post['tipo']		= $post["tipo"];
			$post['img']		= $media!='' ? blog_media_path($media) : '';

			$tpl['blog']['posts'][] = $post;

		}
	}
}

// Depoimentos
// ----------------------------------------------------------------------------

if( $template->hasBlock('depoimentos') && $site['layout']['depoimentos'] ) {

	$tpl['depoimentos'] = [];

	$tpl['depoimentos']['cta'] = "Mostrar todos os depoimentos";
	$tpl['depoimentos']['chamada'] = "Descubra o que nossos clientes têm a dizer sobre a experiência imobiliária conosco!";;

	$sqld = "SELECT * FROM depoimentos
			 WHERE ativo=1
			 ORDER BY data DESC
			 LIMIT 6";

	$qryd = query($sqld);

	if (query_num($qryd)>0) {

		$tpl['depoimentos']['cta'] = "Mostrar todos os depoimentos";
		$tpl['depoimentos']['chamada'] = "Descubra o que nossos clientes têm a dizer sobre a experiência imobiliária conosco!";;

		while($depoimento = fetch($qryd)) {

			$dia = date('d', strtotime($depoimento["data"]));
			$mes = ucfirst(monthname(date('m', strtotime($depoimento["data"]))));
			$ano = date('Y', strtotime($depoimento["data"]));
			$estrelas = '';

			for ($i=0; $i<5; $i++) {
				if( $depoimento["nota"]>$i ){
					$estrelas .= "<i class='bx bxs-star'></i>";
				}else{
					$estrelas .= "<i class='bx bx-star'></i>";
				}
			}

			$foto = $depoimento["imagem"]!=''
					? blog_media_path($depoimento["imagem"])
					: "{$fbz_url_site}img/user-happy.png";

			$depoimento['data']		= "$mes de $ano";
			$depoimento['nome'] 	= $depoimento["nome"];
			$depoimento['texto'] 	= $depoimento["texto"];
			$depoimento['estrelas'] = $estrelas;
			$depoimento['foto'] 	= $foto;

			$tpl['depoimentos']['depoimentos'][] = $depoimento;

		}

	}

}

// Nuvem
// ----------------------------------------------------------------------------

if( $template->hasBlock('nuvem') ) {

	$tpl['nuvem'] = [];

	$sqln = "SELECT categoria, status
					FROM imoveis
					WHERE
						publicado=1
					AND (INSTR(status,'VENDA') OR INSTR(status,'ALUGUEL'))
					AND CATEGORIA IS NOT NULL
					GROUP BY CATEGORIA
					ORDER BY RAND()
					LIMIT 20
					";

	$nuvem = fbz_sql_select($sqln);

	foreach($nuvem as $link) {
		if(stristr($link["status"],'venda')) {
			$status = 'venda';
			$label  = ' à Venda';
		}
		if(stristr($link["status"],'aluguel')) {
			$status = 'aluguel';
			$label  = ' para Alugar';
		}
		$link['url'] 	= 'imoveis?busca='.$status.'&tipo='. file_name_format($link["categoria"]);
		$link['label'] 	= fbz_ucfirst(($link["categoria"])) . $label;

		$tpl['nuvem'][] = $link;

	}

}

// Shorts
// ----------------------------------------------------------------------------

if( $template->hasBlock('shorts') ) {

	$tpl['shorts'] = [];

	$shorts = getYoutubeShorts();

	foreach ($shorts as $short) {
		$tpl['shorts'][] = $short;
	}

}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);