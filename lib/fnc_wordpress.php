<?php

function fbz_get_rss_wordpress( $options=array() ){

	$limit = isset($options['limit']) ? $options['limit'] : 4;
	$filter = isset($options['filter']) ? $options['filter'] : null;
	$return = array();

	// URL do feed RSS do WordPress
	$rss_feed_url = 'https://exclusivesul.com.br/blog/feed/';

	// Carrega o feed RSS
	$rss = simplexml_load_file($rss_feed_url);

	if ($rss === false) {
		echo "Erro ao carregar o feed RSS.";
		exit;
	}

	foreach ($rss->channel->item as $item) {

		$date = (string) $item->pubDate;
		$link = (string) $item->link;
		$title = (string) $item->title;
		$description = (string) $item->description;
		// $date = new DateTime($date);
		// $date = $date->format('d/m/Y');

		// Extrai a URL da imagem usando expressão regular
		preg_match('/<img.*?src=["\'](.*?)["\']/', $description, $matches);
		$image_url = $matches[1];

		// Remove a imagem da descrição
		$description_without_image = preg_replace('/<img.*?>/', '', $description);

		// Remove a linha específica da descrição
		$description_without_footer = preg_replace('/<p>O post.*?<\/p>/', '', $description_without_image);

        // Filtragem
        $add_to_return = true;
        if ($filter) {
            $add_to_return = false;
            foreach ($filter as $filter_item) {
                if (stripos($title, $filter_item) !== false || stripos($description, $filter_item) !== false) {
                    $add_to_return = true;
                    break;
                }
            }
        }

        if ($add_to_return) {
            $return[] = array(
                "url" => $link,
                "title" => strip_tags($title),
                "date" => $date,
                "image" => $image_url,
                "text" => strip_tags($description_without_footer),
            );
        }

        // Limite de itens retornados
        if (count($return) >= $limit) {
            break;
        }

	}

	return $return;

}