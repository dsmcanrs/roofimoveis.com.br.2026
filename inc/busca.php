<?php

// Status Inicial
if (empty($_GET['status'])) $_GET['status'] = 'venda';

// Menu e Select finalidade
// ----------------------------------------------------------------------------

$fbz_buscas['venda']    = array(
    "nome"      => "Comprar",
    "titulo"    => "Os melhores imóveis à Venda",
    "filtro"    => $fbz_sql_imoveis_where_venda,
);

$fbz_buscas['aluguel']  = array(
    "nome"      => "Alugar",
    "titulo"    => "As melhores oportunidade para Locação",
    "filtro"    => $fbz_sql_imoveis_where_aluguel,
);

// $fbz_buscas['lancamentos']  = array(
//     "nome"      => "Lançamentos",
//     "titulo"    => "Lançamentos e imóveis em Construção",
//     "filtro"    => $fbz_sql_imoveis_where_lancamentos,
// );

foreach( $fbz_buscas as $index => $buscas ){
    $sqlb = "SELECT id FROM imoveis
             WHERE $fbz_sql_imoveis_where AND {$buscas['filtro']} LIMIT 1";
    $test = get_value($sqlb);
    if( !$test ) unset($fbz_buscas[$index]);
}

// echo "<pre>";
// var_export($fbz_buscas); exit;

// Status
// ------------------------------------------------------------------------

foreach($fbz_buscas as $index => $busca) {
    $tpl['busca']['form']['status'][$index]['id'] = $index;
    $tpl['busca']['form']['status'][$index]['url'] = $busca["url"];
    $tpl['busca']['form']['status'][$index]['nome'] = $busca['nome'];
    if($_GET['status']==$index){
        $tpl['busca']['form']['status'][$index]['active'] = 'active';
        $tpl['busca']['form']['status'][$index]['selected'] = 'selected';
    }
}

// echo "<pre>";
// var_export($tpl['busca']['form']['status']); exit;

// Selects de busca
// ------------------------------------------------------------------------

$selects = [
    // 'pais' => $fbz_sql_query_paises,
    'categoria' => $fbz_sql_query_categorias,
    'cidade' => $fbz_sql_query_cidades,
    'bairro' => $fbz_sql_query_bairros,
    'empreendimento' => $fbz_sql_query_empreendimentos,
];

// var_export($selects); exit;

$where = "";

if (!empty($_GET['status'])) {
    $replaced = str_replace('-', '%', $_GET['status']);
    $where = " AND status LIKE '%{$replaced}%'";
}

foreach($selects as $campo => $query) {

    // Filtros acumulados
    $acum_where = $where;

    if (!empty($_GET[$campo])) {

        $valores = var_to_array($_GET[$campo]);
        $likes = [];

        foreach ($valores as $valor) {
            $replaced = str_replace('-', '%', $valor);
            $likes[] = "{$campo} LIKE '%{$replaced}%'";
        }

        if ($likes) {
            $where .= " AND (" . implode(' OR ', $likes) . ") ";
        }

    }

    // echo "$campo: $where<br>";

    $options = fbz_sql_select(
        $query,
        array(
            'where' => $acum_where,
            'cache' => 900, // 15 min
            // 'debug' => true
        )
    );

    // if( $campo=='empreendimento' ){
    //     var_export($options);
    // }

    foreach($options as $option) {

        $option["label"] = fbz_ucfirst($option[$campo]);
        $option["value"] = file_name_format($option[$campo]);

        $selected = in_array( $option["value"], var_to_array($_GET[$campo]) );

        $option["selected"] = $selected ? true : false ;
        $option["checked"] = $selected ? true : false ;

        $tpl['busca']['form'][$campo][] = $option;

    }

}

// var_export( $tpl['busca']['form']); exit;
// var_export( $tpl['busca']['form']['situacao']); exit;

// Grupo de Opções: Select carac
// ------------------------------------------------------------------------

$inputs = [
    'dorm' => $fbz_busca_tags['dormitorios'],
    'suites' => $fbz_busca_tags['suites'],
    'vagas' => $fbz_busca_tags['vagas'],
    'tags' => $fbz_busca_tags['tags'],
];

foreach($inputs as $campo => $array) {

    foreach($array as $index => $item) {

        // var_export( $item ); exit;

        $value = $item[0];
        $label = $item[1];

        $option = null;
        $selected = false;

        if( $_GET['carac'] ) {
            if( strstr($_GET['carac'],urldecode($value)) ) $selected = true;
        }

        // dorm=1
        if( strstr($_GET[$campo],intval($value)) ) $selected = true;

        $option["selected"] = $selected ? true : false ;
        $option["checked"] = $selected ? true : false ;
        $option["active"] = $selected ? 'active' : '' ;

        $option["value"] = (string)$value;
        $option["label"] = $label;

        // var_export($option);

        $tpl['busca']['form'][$campo][] = $option;

    }

}

// var_export( $tpl['busca']['form']['dorm']); exit;

// Valores Datalist
// ------------------------------------------------------------------------

$option = null;

foreach($fbz_busca_valor_venda_list as $value) {
    $option['class'] = 'venda';
    $option['value'] = $value;
    $tpl['busca']['form']['valores'][] = $option;
}

foreach($fbz_busca_valor_aluguel_list as $value) {
    $option['class'] = 'aluguel';
    $option['value'] = $value;
    $tpl['busca']['form']['valores'][] = $option;
}

// echo "<pre>"; var_export($tpl['busca']['form']['valores']); exit;

$tpl['busca']['form']['valor_de'] = get('valor_de');
$tpl['busca']['form']['valor_ate'] = get('valor_ate');

// Área
// ------------------------------------------------------------------------

$tpl['busca']['form']['area_de'] = get('area_de');
$tpl['busca']['form']['area_ate'] = get('area_ate');

// Query ou Código
// ------------------------------------------------------------------------

$tpl['busca']['form']['q'] = get('q');
$tpl['busca']['form']['codigo'] = get('cpdigo');

// echo "<pre>"; var_export($tpl['buscas']); exit;