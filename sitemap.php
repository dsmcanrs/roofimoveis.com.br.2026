<?php

include("inc/setup.php");

header("Content-Type:text/xml");

$sql_imoveis = "SELECT * FROM imoveis WHERE $fbz_sql_imoveis_where";

// die($sql_imoveis);

$sql_blog = "SELECT * FROM noticias WHERE ativa='S' ORDER BY data DESC";

// die($sql_blog);

$query_imoveis = query($sql_imoveis);
$query_blog = query($sql_blog);

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo "\n";

?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">
<?php
// Páginas
// Somente páginas no domínio
foreach (mn_paginas() as $index => $pagina) {
	if( strstr($pagina['url'], $fbz_url_site) ){
	?>
	<url>
		<loc><?php echo str_replace('&','%2',$pagina['url']); ?></loc>
		<changefreq>weekly</changefreq>
	</url>
	<?php
	}
}
// Imóveis
while($imovel = fetch($query_imoveis)) {
	$url = fbz_imovel_url($imovel, $fbz_formato_url_imovel);
	$url = $fbz_url_site . $url;
	?>
	<url>
		<loc><?php echo $url; ?></loc>
		<changefreq>weekly</changefreq>
	</url>
	<?php
}
// Blog
while($blog = fetch($query_blog)) {
	$url = 'blog/'. file_name_format($blog["titulo"]) .'/'.$blog["id"];
	$url = $fbz_url_site . $url;
	?>
	<url>
		<loc><?php echo $url; ?></loc>
		<changefreq>weekly</changefreq>
	</url>
	<?php
}
?>
</urlset>