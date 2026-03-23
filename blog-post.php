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

// Post
// ----------------------------------------------------------------------------

// var_export($_GET); exit;

$uri = $fbz_url_site . $_SERVER['REQUEST_URI'];
$id = urldecode($_GET['id']);

if(!$id) header("location:${$fbz_url_site}404");

$sql_post = "SELECT * FROM noticias
			 WHERE ativa=1 AND id=$id";

$query = query($sql_post);

if(query_num($query)==0) header("location:404");

$post = fetch($query);

$media = blog_media($post);

// meta

$tpl['site']['canonical'] = $uri;
$tpl['site']['title'] = $post["titulo"]." - ".$titulo;
$tpl['site']['desc'] = $post["chamada"];
$tpl['site']['img'] = blog_media_path($media);


// post

$dia = date('d', strtotime($post["data"]));
$mes = ucfirst(monthname(date('m', strtotime($post["data"]))));
$ano = date('Y', strtotime($post["data"]));

$tpl['post']['data'] = "$dia de $mes de $ano";
$tpl['post']['url'] = $fbz_url_site .'blog/'. file_name_format($post["titulo"]) .'/'. $post["id"];
$tpl['post']['grupo'] = fbz_ucfirst($post["grupo"]);
$tpl['post']['titulo'] = $post["titulo"];
$tpl['post']['chamada'] = $post["chamada"];
$tpl['post']['texto'] = blog_media_path($post["texto"]);
$tpl['post']['img'] = $media!='' ? blog_media_path($media) : '';

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

// Mais Posts
// ----------------------------------------------------------------------------

if( $template->hasBlock('posts') ) {

	$sqln = query("SELECT * FROM noticias
				   WHERE ativa = 1
				   ORDER BY RAND() LIMIT 6");

	if (query_num($sqln)>0) {
		while($post = fetch($sqln)) {

			$dia = date('d', strtotime($post["data"]));
			$mes = ucfirst(monthname(date('m', strtotime($post["data"]))));
			$ano = date('Y', strtotime($post["data"]));

			$media = blog_media($post);

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
}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);