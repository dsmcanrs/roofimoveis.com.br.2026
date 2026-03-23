<?php

include("inc/setup.php");

$tpl = new Template( "tpl/imprimir.html" );

// ---

$tpl->HTML_TITLE  = $site['geral']['title'];
$tpl->EMPRESA     = $site['geral']['nome'];
$tpl->TELEFONE    = $site['contato']['telefone'];
$tpl->DOMINIO     = ;

// ---

$id = get("id");
$imovel = fbz_imovel_detalhes($id);

if( !isset($imovel['ver_web']) || $imovel['ver_web']=='N' ) redir("404.php");

if($tpl->exists('CODIGO'))
  $tpl->CODIGO = $imovel['codigo'];

if($tpl->exists('CATEGORIA'))
  $tpl->CATEGORIA = fbz_ucfirst($imovel["categoria"]);

if($tpl->exists('CIDADE'))
  $tpl->CIDADE = fbz_ucfirst($imovel['cidade']);

if($tpl->exists('BAIRRO'))
  $tpl->BAIRRO = fbz_ucfirst($imovel['bairro']);

if($tpl->exists('DESCRICAO'))
  $tpl->DESCRICAO = nl2br($imovel['descricao']);

if($tpl->exists('DORMITORIOS'))
  $tpl->DORMITORIOS = ($imovel['dormitorios']>0) ? $imovel['dormitorios'].' <strong>dormitório(s)</strong>' : '';

if($tpl->exists('VAGAS'))
  $tpl->VAGAS = ($imovel['vagas']>0) ? $imovel['vagas'].' <strong>vaga(s)</strong>' : '';

if($tpl->exists('AREA_PRIVATIVA'))
  $tpl->AREA_PRIVATIVA  = ($imovel['area_privativa']>0) ? $imovel['area_privativa'].'m² <strong>privativo</strong>' : '';

// mapa ou foto
if ( $imovel['longitude']!='' && $imovel['latitude']!='' ) {
  $tpl->MAP_LAT   = $imovel['latitude'];
  $tpl->MAP_LNG   = $imovel['longitude'];
  $tpl->block("MOSTRA_MAPA");
}else{
  $foto_grande  = fbz_imovel_foto($imovel['id'],'g');
  $tpl->FOTOG   = $foto_grande;
  $tpl->block("MOSTRA_FOTO");
}

// grids de detalhes
$grid = fbz_imovel_detalhes_grid(
          $imovel,
          array(
            'DORM|Dormitório(s)|num',
            'SUITE|Suíte(s)|num',
            'VAGA|Vaga(s)|num',
            'AREAP|Privativo|area',
            'AREAT|Total|area',
            'AREAP|Útil|area',
            'AREAC|Construido|area'
          )
        );

foreach ($grid as $key => $value) {
  $tpl->GRID_VAL = $key;
  $tpl->GRID_LABEL = $value;
  $tpl->block("IMOVEL_GRID");
}

// valores
$gridv = fbz_imovel_detalhes_grid(
          $imovel,
          array(
            'valor_venda|Venda|valor',
            'valor_aluguel|Aluguel|valor',
            'valor_condominio|Condomínio|valor',
            'valor_iptu|IPTU|valor'
          )
        );

foreach ($gridv as $key => $value) {
  $tpl->GRIDV_VAL = $value;
  $tpl->GRIDV_LABEL = $key;
  $tpl->block("IMOVEL_VALORES");
}

// fotos
$vfotos = fbz_imovel_fotos($imovel['id'],6);

// var_export($vfotos); exit;

if (sizeof($vfotos)>0) {
  foreach ($vfotos as $foto) {
    $tpl->IMOVEL_FOTO_P = $foto['fotop'];
    $tpl->block("IMOVEL_FOTO");
  }
  $tpl->block("IMOVEL_FOTOS");
}

// ---

$return = $tpl->parse();
include("inc/paths.php");
echo $return;