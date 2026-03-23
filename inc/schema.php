<?php

$realstate = [];
$offers = [];

if( isset($site) ) {

    // var_export($site); exit;

    $telephone = $site["contato"]["whatsapp"] != ''
                ? $site["contato"]["whatsapp"]
                : $site["contato"]["telefone"];

    $website[] = [
        "@type" => "WebSite",
        "name" => $site["geral"]["nome"],
        "url" => $fbz_url_site,
        "description" => $site["geral"]["description"],
    ];

    $organization[] = [
        "@type" => "Organization",
        "name" => $site["geral"]["nome"],
        "url" => $fbz_url_site,
        "logo" => $fbz_url_site . $site["design"]["logo"],
        "description" => $site["geral"]["description"],
        "telephone" => $telephone,
        "contactPoint" => [
            "@type" => "ContactPoint",
            "telephone" => $telephone,
            "areaServed" => "BR",
            "availableLanguage" => ["Portuguese"],
            "contactType" => "customer service"
        ]
    ];

    $realstate[] = [
        "@type" => "RealEstateAgent",
        "name" => $site["geral"]["nome"],
        "url" => $fbz_url_site,
        "image" => $fbz_url_site . $site["design"]["logo"],
        "telephone" => $telephone,
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "{$site["contato"]["endereco"]}",
            "addressCountry" => "BR"
        ]
    ];

}

// Detalhes
if( isset($imovel) ) {
    $imoveis[] = $imovel;
}

// echo count($destaques); exit;
// var_export($destaques); exit;

// Index
if (isset($destaques) && is_array($destaques)) {
    $imoveis = [];
    foreach ($destaques as $secao) {
        // var_export($secao); exit;
        if (isset($secao['imoveis']) && is_array($secao['imoveis'])) {
            foreach ($secao['imoveis'] as $imovel) {
                $imovel['status'] = $secao['finalidade'];
                $imoveis[] = $imovel;
            }
        }
    }
}

// echo count($imoveis); exit;
// var_export($imoveis); exit;

// Imoveis
if( isset($imoveis) && sizeof($imoveis)>0 ) {

    // var_export($imoveis); exit;

    $schema_types = [
        'Casa' => 'House',
        'Apartamento' => 'Apartment'
    ];

    $disponiveis = [
        "id",
        "status",
        "codigo",
        "titulo",
        "categoria",
        "bairro",
        "cidade",
        "uf",
        "valor_venda",
        "valor_aluguel"
    ];


    // Filtra os campos de cada imóvel para manter apenas os disponíveis
    foreach ($imoveis as &$imovel) {
        $imovel = array_intersect_key($imovel, array_flip($disponiveis));
    }

    // echo count($imoveis); exit;
    // var_export($imoveis); exit;

    foreach ($imoveis as $imovel) {

        $_GET['status'] = $imovel['status'];

        $imovel["titulo"] = empty($imovel['titulo'])
                        ? "{$imovel['categoria']} {$imovel['bairro']} {$imovel['uf']}"
                        : $imovel['titulo'];
        $imovel["url"] = $fbz_url_site . fbz_imovel_url($imovel);

        $imovel["valor"] = fbz_imovel_valor($imovel);

        $fotos = fbz_sql_select( $fbz_sql_query_fotos,
            array(
                'where' => 'registro = '.$imovel['id'],
                'limit' => 1,
                'cache' => 86400,
                // 'debug' => 1,
            )
        );

        $imovel["foto"] = $fotos[0]['foto'];

        // Busca tipo Schema.org por substring
        $schema_type = 'Place';
        foreach ($schema_types as $chave => $tipo) {
            if (stripos($imovel["categoria"], $chave) !== false) {
                $schema_type = $tipo;
                break;
            }
        }

        // if( $imovel["valor"]>0 ){
            $offer = [
                "@context" => "https://schema.org",
                "@type" => ["Product", $schema_type],
                "name" => $imovel["titulo"],
                "sku" => $imovel["codigo"],
                "offers" => [
                    "@type" => "Offer",
                    "@id" => $imovel["url"]."#offer",
                    "priceCurrency" => "BRL",
                    "price" => $imovel["valor"],
                    "url" => $imovel["url"],
                    "availability" => "https://schema.org/InStock"
                ],
                "address" => [
                    "@type" => "PostalAddress",
                    "addressLocality" => "{$imovel["bairro"]}, {$imovel["cidade"]}",
                    "addressRegion" => $imovel["uf"],
                    "addressCountry" => "BR"
                ]
            ];
            if (!empty($imovel["foto"])) {
                $offer["image"] = $imovel["foto"];
            }
            $offers[] = $offer;
        // }

    }
}

$graph = array_merge($website, $organization);

if (!empty($offers)) {
    $graph = array_merge($graph, $offers);
}

$schema = [
    "@context" => "https://schema.org",
    "@graph" => $graph
];

// echo (json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$tpl['schema'] = json_encode($schema, JSON_UNESCAPED_UNICODE);