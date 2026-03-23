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

// Blog
// ----------------------------------------------------------------------------

// var_export($_GET); exit;

$title 	= $site['geral']['title'];
$uri 	= '//'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

// Busca
if (get('qb')!="") {

	$qb = urldecode(get('qb'));

	$tpl['qb'] = $qb;

	$sql_blog = "SELECT * FROM noticias
			  	 WHERE ativa='S'
				 AND (titulo like '%".$qb."%' or texto like '%".$qb."%')
				 ORDER by data desc";
}
// Categoria
else if (get('secao')!="") {

	$secao = urldecode(get('secao'));
	$secao = str_replace("-", ' ', get('secao'));

	$sql_blog = "SELECT * FROM noticias
				 WHERE ativa=1 AND grupo like '$secao'
				 ORDER BY data desc";

	// die($sql_blog);

}
// Tudo
else{
	$sql_blog = "SELECT * from noticias
				 where ativa=1
				 order by data desc";
}

// echo $exibir;
// echo $sql_blog;
// exit;

// Post Destaque
// ----------------------------------------------------------------------------

if( $template->hasBlock('destaque')
	&& (get('qb')=='' && get('secao')=='' && !get('pg')) ) {

	$sql_destaque = "SELECT * FROM noticias
					WHERE
						ativa=1
					AND destaque=1
					ORDER BY RAND()
					LIMIT 1";

	$query = query($sql_destaque);

	$post = fetch($query);

	if( $post ){

		$media = blog_media($post);

		$tpl['destaque']['url'] 	= $fbz_url_site .'blog/'. file_name_format($post["titulo"]).'/'.$post["id"];
		$tpl['destaque']['titulo'] 	= $post["titulo"];
		$tpl['destaque']['chamada'] = $post["chamada"];
		$tpl['destaque']['texto'] 	= blog_media_path($post["texto"]);

		if( trim($media)!='' ){
			$tpl['destaque']['img'] = blog_media_path($media);
		}

	}

}

// Posts
// ----------------------------------------------------------------------------

if( $template->hasBlock('posts') ) {

	$total_reg  = get_total($sql_blog);
	$regs_pp    = 10;
	$pg         = !isset($_GET["pg"]) ? 1 : $_GET["pg"];
	$comeca_em  = ($pg-1) * $regs_pp;
	$sql_limit  = " limit " . $comeca_em . "," . $regs_pp;

	$query_posts = query($sql_blog . $sql_limit);

	$posts = fetch_all($query_posts);

	$secao = get_value("SELECT grupo as val
				        from noticias
						where grupo like '$secao' limit 1");

	$secao = trim($secao)=='' ? 'Blog' : $secao;

	// HTML Head
	$tpl['site']['title'] = $secao." - ".$title;
	$tpl['site']['desc'] = $secao;
	$tpl['site']['canonical'] = $uri;

	$tpl['blog']['secao'] = $secao;

	foreach($posts as $post) {

		$media = blog_media($post);

		$dia = date('d', strtotime($post["data"]));
		$mes = ucfirst(monthname(date('m', strtotime($post["data"]))));
		$ano = date('Y', strtotime($post["data"]));

		$tpl['posts'][] = [
			'data' => "$dia de  $mes de $ano",
			'mes' => mes_abr(substr($post["data"],5,2)),
			'url' => $fbz_url_site .'blog/'. file_name_format($post["titulo"]).'/'.$post["id"],
			'grupo' => $post["grupo"],
			'titulo' => $post["titulo"],
			'chamada' => $post["chamada"]!='' ? $post["chamada"] : substr(strip_tags($post["texto"]), 0, 150),
			'img' => trim($media)!='' ? blog_media_path($media) : '',
		];

	}

}

// Categorias
// ----------------------------------------------------------------------------

if( $template->hasBlock('blog_menu') ) {

	$sql_grupos = "SELECT distinct grupo
				  FROM noticias
				  WHERE ativa=1 order by grupo";

	$query = query($sql_grupos);

	if(query_num($query)>0){
		while($grupos = fetch($query)) {
			$tpl['blog_menu'][] = [
				'url' => $fbz_url_site .'blog/'. file_name_format($grupos["grupo"]),
				'titulo' => $grupos["grupo"],
				'active' => file_name_format($grupos["grupo"])==get("secao") ? 'active' : '',
			];
		}
	}
}

// Paginacao
// ----------------------------------------------------------------------------

if($total_reg>$regs_pp) {
	fbz_paginacao(
		$pg,
		$regs_pp,
		$total_reg
	);
}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);