<?php

// Vista Setup
// ----------------------------------------------------------------------------

if (is_dir('importacao/vista') ) {
    require("importacao/vista/class.vista42.php");

    $jsonVista = file_get_contents('importacao/vista/vista.json');
    $dataVista = json_decode($jsonVista, true);

    define('VISTA',$dataVista);
}else{

    define('VISTA',null);

}

// var_export(VISTA); exit;

// ----------------------------------------------------------------------------

function vista_map_array($originalArray) {

    if (!defined('VISTA')) exit('Definir constante VISTA.');

    $newArray = array();

    foreach ($originalArray as $outerKey => $innerArray) {
        // Verificar se o valor é um array e mapeá-lo
        if (is_array($innerArray)) {
            $mappedInnerArray = array();
            foreach ($innerArray as $key => $value) {
                // Mapear as chaves internas
                if (isset(VISTA['campos'][$key])) {
                    $mappedInnerArray[VISTA['campos'][$key]] = $value;
                } else {
                    // Manter as chaves que não estão no mapeamento
                    $mappedInnerArray[$key] = $value;
                }
            }
            // Armazenar o array mapeado no novo array
            $newArray[$outerKey] = $mappedInnerArray;
        } else {
            // Caso não seja um array, simplesmente manter o valor
            $newArray[$outerKey] = $innerArray;
        }
    }

    return $newArray;
}

function fbz_busca_vista() {

    $querystring = "";
    $querystring .= get("busca") ? "&busca=".get("busca") : '';
    $querystring .= get("ordem") ? "&ordem=".get("ordem") : '';

    $finalidade = get("finalidade")!='' ? get("finalidade") : get("status");

    $busca  = array();

    // Campo Código

    if (get("codigo")!="") {
        $codigo = get("codigo");
        $busca['Codigo'][] = $codigo;
        $querystring .= "&codigo=$codigo&";
        return array($busca,$querystring);
    }

    // Campo Valor

    if( $finalidade=='aluguel' ){
        $campo_vlr = "ValorLocacao";
    }else if( $finalidade=='aluguel-temporada' ){
        $campo_vlr = "ValorDiaria";
    }else{
        $campo_vlr = "ValorVenda";
    }

    // Filtro Finalidade

    if( $finalidade!='' ){
        if ( $finalidade=="aluguel-temporada" ){
            $busca['Status'] = array('like', 'temporada');
        }
        else if ( $finalidade=="aluguel" ){
            $busca['Status'] = array('like', 'aluguel');
        }
        else{
            $busca['Status'] = array('like', 'venda');
        }
        $querystring .= "&finalidade=".$finalidade."&";
    }

    // Strings (categoria, bairro, cidade)
    // querystring => campo_db

    $campos_string = array(
        "codigo" => "Codigo",
        "tipo" => "Categoria",
        "empreendimento" => "Empreendimento",
        "cidade" => "Cidade",
        "bairro" => "Bairro"
    );

    foreach( $campos_string as $qry_field => $vista_field ){
        if( get($qry_field)!="" ){
            $array = var_to_array( get($qry_field) );
            $array = array_filter($array);
            // var_export($array); exit;
            if ( sizeof($array)>0 ) {
                foreach($array as $value) {
                    $value = file_name_format($value);
                    $value = str_replace('-', '+', $value);
                    // array_push($busca[$vista_field],$value);
                    $busca[$vista_field][] = $value;
                }
                $querystring .= "&$qry_field=".get($qry_field);
            }
        }
    }

    // echo $querystring;
    // var_export($busca);
    // exit;

    // Inteiros (dorm, suites, vagas)
    // querystring => campo_db

    $campos_numericos = array(
        "dorm" => "Dormitorios",
        "suite" => "Suites",
        "vagas" => "Vagas"
    );

    foreach( $campos_numericos as $qry_field => $vista_field ){
        if( get($qry_field)!="" ){
            $array = var_to_array(get($qry_field));
            $array = array_filter($array);
            if( sizeof($array)>0 ){
                foreach($array as $value) {
                    $sinal = "=";
                    if (!is_numeric($value)) {
                        if (substr($value,-1)==" ") {
                            $sinal = ">=";
                            $value = str_replace("+","",$value);
                        }
                    }
                    // $busca[$vista_field] = [$sinal,$value];
                    $busca[$vista_field] = ['>=',$value];
                }
                $querystring .= "&$qry_field=".get($qry_field);
            }
        }
    }

    // echo $querystring;
    // var_export($busca);
    // exit;

    // Valores em input

    if( get("valor_de")!="" ){
        $de = mysql_moeda(get("valor_de"));
        $querystring .= "&valor_de=". get("valor_de");
    }

    if( get("valor_ate")!="" ){
        $ate = mysql_moeda(get("valor_ate"));
        $querystring .= "&valor_ate=". get("valor_ate");
    }

    if( $de>0 && $ate>0 ){
        $busca[$campo_vlr] = [$de,$ate];
    }else if( $de==0 && $ate>0 ){
        $busca[$campo_vlr] = [0,$ate];
    }else if( $de>=0 && $ate==0 ){
        $busca[$campo_vlr] = [$de,1000000000];
    }

    // Busca no Mapa

    if( isset($_GET['swLat']) && $_GET['neLat'] ){

        $swLat = $_GET['swLat'];
        $neLat = $_GET['neLat'];
        $swLng = $_GET['swLng'];
        $neLng = $_GET['neLng'];

        $busca['Latitude'] = [$swLat, $neLat];
        $busca['Longitude'] = [$swLng, $neLng];

    }

    // ---

    $querystring = preg_replace('/&+/',"&",$querystring);

    // echo json_encode($busca); exit;
    // var_export($busca); exit;

    return array($busca,$querystring);

}

function fbz_semelhantes_vista($imovel) {

    // var_export($imovel); exit;

    $where = [];
    $percentual = 20;

    if( $percentual>0 ){
        if( strstr('aluguel', strtolower($imovel['status'])) ){
            $valor_vista = 'ValorLocacao';
            $valor_site = 'valor_aluguel';
        }else{
            $valor_vista = 'ValorVenda';
            $valor_site = 'valor_venda';
        }
        $calc = ($imovel[$valor_site]/100) * $percentual;
        $mais  = $imovel[$valor_site] + $calc;
        $menos = $imovel[$valor_site] - $calc;
        $where[$valor_vista] = [$menos, $mais];
    }

    $where['Cidade'] = str_replace(' ','+',$imovel['cidade']);
    $where['Categoria'] = str_replace(' ','+',$imovel['categoria']);
    $where['Codigo'] = ['!=',$imovel['codigo']];

    $options['registros'] = 12;
    $options['campos'] = array_keys(VISTA['campos']);
    $options['filtro'] = $where;

    // var_export($options); exit;

    $vista = new Vista();
    $result = $vista->apiGetImoveis( $options );
    $imoveis = vista_map_array($result['imoveis']);

    // var_export($result); exit;

    return $imoveis;
}

function fbz_condominio_vista($imovel) {

    // var_export($imovel); exit;

    $condominio = str_replace(' ','%',$imovel["empreendimento"]);
    $categoria = str_replace(' ','%',$imovel["categoria"]);
    $percentual = 30;

    if( strstr('aluguel', strtolower($imovel['status'])) ){
        $valor_vista = 'ValorLocacao';
        $valor_site = 'valor_aluguel';
    }else{
        $valor_vista = 'ValorVenda';
        $valor_site = 'valor_venda';
    }

    $calc = ($imovel[$valor_site]/100) * $percentual;
    $mais  = $imovel[$valor_site] + $calc;
    $menos = $imovel[$valor_site] - $calc;

    $where = [];
    $where['Codigo'] = ['!=',$imovel['codigo']];
    $where['And']['Status'] = ['like',$imovel['status']];
    $where['And']['Categoria'] = ['like',$categoria];
    $where['And']['Empreendimento'] = ['like',$condominio];
    $where['And'][$valor_vista] = [$menos, $mais];

    // echo json_encode($where); exit;

    $options['registros'] = 15;
    $options['campos'] = array_keys(VISTA['campos']);
    $options['advFilter '] = $where;

    // var_export($options); exit;

    $vista = new Vista();
    $result = $vista->apiGetImoveis( $options );
    $imoveis = vista_map_array($result['imoveis']);

    // var_export($result); exit;

    return $imoveis;
}

function fbz_title_vista() {

    $return     = "";

    $bairro     = get("bairro")!='' ? var_to_array( get("bairro") ) : null ;
    $cidade     = get("cidade")!='' ? var_to_array( get("cidade") ) : null ;
    $categoria  = get("tipo")!='' ? var_to_array( get("tipo") ) : null ;
    $finalidade = get("finalidade")!='' ? get("finalidade") : get("busca");

    // var_export($categoria); exit;

    // ---

    // Inclui Status pra gerar um cache diferente do cache da busca
    $options['campos'] = array('Status','Bairro','Cidade','Categoria');

    // var_export($options); exit;

    $vista = new Vista();

    $result = $vista->apiGetCache($options, 1800);

    // var_export($result);

    if ($result === null) {
        $result = $vista->apiGetConteudo( $options );
        $vista->apiSetCache($options, 43200, $result); //12 horas
    }

    foreach($result['Categoria'] as $index => $value) {
        // $vcategorias[]['categoria'] = $value;
        $key = file_name_format($value);
        $categorias[$key] = $value;
    }
    foreach($result['Bairro'] as $index => $value) {
        // $vbairros[]['bairro'] = $value;
        $key = file_name_format($value);
        $bairros[$key] = $value;
    }
    foreach($result['Cidade'] as $index => $value) {
        // $vcidades[]['cidade'] = $value;
        $key = file_name_format($value);
        $cidades[$key] = $value;
    }


    $categorias = array_unique($categorias);
    $bairros = array_unique($bairros);
    $cidades = array_unique($cidades);

    asort($categorias);
    asort($bairros);
    asort($cidades);

    // echo "<pre>"; var_export($bairros); exit;

    // ---

    if (is_array($categoria) && sizeof($categoria) == 1) {
        $categoria = $categorias[$categoria[0]];
        $return .= fbz_tipos_plural($categoria);
    } else {
        $return .= 'Imóveis';
    }

    if($finalidade=='aluguel'){
        $return .= " para alugar";
    }else{
        $return .= " à venda";
    }

    if( get("bairro")!="" && count($bairro)==1 ){
        $bairro = $bairros[$bairro[0]];
        $return .= " em $bairro";
    }

    if( get("cidade")!="" && count($cidade)==1 ){
        $cidade = $cidades[$cidade[0]];
        if ( get("cidade")!="" && get("bairro")!="" ) {
            $return .= ', '.$cidade;
        } else if ( get("cidade")!="" ) {
            $return .= ' em '.$cidade;
        }

    }

    if ($return=="") {
        $return = "Imóveis";
    }else{
        $return = $return;
    }

    return $return;

}