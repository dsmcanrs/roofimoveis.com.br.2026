<?php

$secoes = mn_secoes();

$tpl['menu'] = [];

foreach ($secoes as $secao) {

    $total = 0;
    $paginas = mn_paginas($secao);
    $menuItens = [];  // Reinicializa para cada seção

    foreach ($paginas as $pagina) {

        $total++;

        $tpl['menu'][$secao]['total'] = $total;

        $url = strstr($pagina["url"], 'http')
             ? $pagina["url"]
             : $fbz_url_site . '/' . $pagina["url"];

        $menuItens[] = [
            'grupo' => $pagina["grupo"],
            'titulo' => $pagina["titulo"],
            'target' => $pagina["target"],
            'url' => $url
        ];

    }

    // var_export($menuItens); exit;

    $tpl['menu'][$secao]['paginas'] = $menuItens;

}
