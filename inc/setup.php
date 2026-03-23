<?php

// PHP Settings
// ----------------------------------------------------------------------------

ini_set('date.timezone','America/Montevideo');
ini_set('error_log', dirname(__DIR__) . '/log/php-errors.log');
ini_set('log_errors', '1');

error_reporting( E_ERROR );

setlocale(LC_ALL, NULL);
setlocale(LC_ALL, 'pt_BR');

header("Content-Type: text/html; charset=utf-8",true);
header("Content-Security-Policy: img-src * 'self' data: https:");
header("Cache-Control: no-cache, must-revalidate, max-age=0");

session_start();

require_once('lib/autoload.php');
require_once("vendor/autoload.php");

// MYSQL Setup
// ----------------------------------------------------------------------------

require_once("db.php");

$conn = mysqli_connect(
	$fbz_db['host'],
	$fbz_db['user'],
	$fbz_db['pass'],
	$fbz_db['db'],
	$fbz_db['port']
)

or die('Não foi possível conectar ao banco de dados.');

mysqli_query($conn, "SET NAMES 'utf8mb4'");
// mysqli_query($conn, "SET time_zone = '-03:00'");

// Variáveis Globais
// ----------------------------------------------------------------------------

// URL
$http = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
$fbz_url_site = $http . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$fbz_url_site .= substr($fbz_url_site,-1)!='/' ? '/' : '';

// die($fbz_url_site);

// Sem imagem
$fbz_url_sem_foto_p	= $fbz_url_site . "img/sem_foto.png";
$fbz_url_sem_foto_g	= $fbz_url_site . "img/sem_foto.png";

// Cadastro automático
$fbz_cad_news_auto	= false;

// Captcha
$fbz_captcha = false;

// Template
// ----------------------------------------------------------------------------

$fbz_dir  = dirname(__DIR__);
$fbz_tpl  = "$fbz_dir/inc/template.json";
$fbz_view = file_exists($fbz_tpl) ? json_decode(file_get_contents($fbz_tpl),true) : null;

if ( getenv("SERVER_NAME")=='localhost' && file_exists($fbz_tpl) ) {
	$fbz_frontend = $fbz_view['template'].'/';
}else{
	$fbz_frontend = 'view/';
}

// die($fbz_frontend);

// Meta tag para domínios *.siteexpresso.com.br
// ----------------------------------------------------------------------------

$dominios_noindex = [
    // 'localhost',
    'siteexpresso.com.br',
    'fullbiz.net.br',
];

foreach ($dominios_noindex as $dominio) {
    if (str_ends_with($_SERVER['HTTP_HOST'], $dominio)) {
        $fbz_meta_custom = '<meta name="robots" content="noindex, nofollow">';
    }
}

// Url amigável do imovel
// ----------------------------------------------------------------------------
// Quando array conatena valor do campo com string
// Ex: array('DORMITORIO','dormitorios') = ...-2-dormitios-...

$fbz_formato_url_imovel = array(
	"status",
	"categoria",
	array('dormitorios','dormitorios'),
	"empreendimento",
	"bairro",
	"cidade"
);

// Configurações e Integrações do Site
// ----------------------------------------------------------------------------

$loader = new ConfigLoader($conn);
$site = $loader->loadSiteConfigs();

// var_export($site); exit;

$site['scripts'] = $loader->loadSiteIntegrations();

// var_export($site); exit;

// Forms em json/form-*.json
$forms = glob('json/form-*.json');

if (is_array($forms)) {
	foreach ($forms as $json) {
		$file = file_get_contents($json);
		$array = json_decode($file, true);
		$grupo = preg_replace('/json\/|\.json/', '', basename($json));
		$site['forms'][$grupo] = $array;
	}
}

// var_export($site['forms']);

// Ordenamento
// ----------------------------------------------------------------------------

$campo_valor = get('finalidade')=='aluguel'
			 ? 'valor_aluguel'
			 : 'valor_venda';

$fbz_order_array[] = array(
	'grupo' => 'Publicação', 'label' => 'Mais Relevantes', 'order' => "destaque_web='S'"
);
$fbz_order_array[] = array(
	'grupo' => 'Publicação', 'label' => 'Mais Recentes', 'order' => 'id DESC'
);
$fbz_order_array[] = array(
	'grupo' => 'Preço', 'label' => 'Menor Preço', 'order' => "$campo_valor ASC"
);
$fbz_order_array[] = array(
	'grupo' => 'Preço', 'label' => 'Maior Preço', 'order' => "$campo_valor DESC"
);
$fbz_order_array[] = array(
	'grupo' => 'Dormitórios', 'label' => 'Menos Dormitórios', 'order' => 'dormitorios ASC'
);
$fbz_order_array[] = array(
	'grupo' => 'Dormitórios', 'label' => 'Mais Dormitórios', 'order' => 'dormitorios DESC'
);
$fbz_order_array[] = array(
	'grupo' => 'Vagas', 'label' => 'Menos Vagas', 'order' => 'vagas ASC'
);
$fbz_order_array[] = array(
	'grupo' => 'Vagas', 'label' => 'Mais Vagas', 'order' => 'vagas DESC'
);
$fbz_order_array[] = array(
	'grupo' => 'Área', 'label' => 'Menor Área', 'order' => 'area_privativa ASC'
);
$fbz_order_array[] = array(
	'grupo' => 'Área', 'label' => 'Maior Área', 'order' => 'area_privativa DESC'
);

// var_export($fbz_ord_array); exit;

// Busca: Filtros
// ----------------------------------------------------------------------------

// $fbz_busca_carac = array(
// 	"dorm" => "dormitorios",
// 	"vagas" => "vagas",
// 	"suites" => "suites",
// 	"lancamento" => "situacao",
// 	"construcao" => "situacao",
// 	"mobiliado" => "infra_imovel",
// 	"piscina" => "infra_comum",
// 	"condominio-fechado" => "descricao",
// 	"suite" => "descricao",
// );

// $fbz_busca_tags = [
// 	"condominio-fechado" => 'Condomínio Fechado',
// 	"mobiliado" => 'Mobiliado',
// ];

$fbz_busca_tags['dormitorios'] = array(
	array("1-dormitorio",'1 dormitório', " dormitorios = 1 "),
	array("2-dormitorios",'2 dormitórios', " dormitorios = 2 "),
	array("3-dormitorios",'3 dormitórios', " dormitorios = 3 "),
	array("4+dormitorios",'4+ dormitórios', " dormitorios >= 4 "),
);

$fbz_busca_tags['suites'] = array(
	array("1-suite",'1 suite', " suites = 1 "),
	array("2-suites",'2 suites', " suites = 2 "),
	array("3-suites",'3 suites', " suites = 3 "),
	array("4+suites",'4+ suites', " suites >= 4 "),
);

$fbz_busca_tags['vagas'] = array(
	array("1-vagas",'1 vaga', " vagas = 1 "),
	array("2-vaga",'2 vagas', " vagas = 2 "),
	array("3-vagas",'3 vagas', " vagas = 3 "),
	array("4+vagas",'4+ vagas', " vagas >= 4 "),
);

$fbz_busca_tags['tags'] = array(
	array("mobiliado",'Mobiliado', " infra_imovel like '%mobiliado%' "),
	array("academia",'Academia', " infra_comum like '%academia%' "),
	array("elevador",'Elevador', " infra_comum like '%elevador%' "),
	array("aceita-pet",'Aceita Pet', " infra_imovel like '%aceita pet%' "),
	array("piscina",'Piscina', " infra_comum like '%piscina%'  "),
	array("ofertas",'Ofertas', " destaque_web='S'"),
);

// $fbz_busca_dorm = [
// 	"1" => '1 dormitório',
// 	"2" => '2 dormitórios',
// 	"3" => '3 dormitórios',
// 	"4+" => '4+ dormitórios',
// ];

// $fbz_busca_suites = [
// 	"1" => '1 suíte',
// 	"2" => '2 suítes',
// 	"3" => '3 suítes',
// 	"4+" => '4+ suítes',
// ];

// $fbz_busca_vagas = [
// 	"1" => '1 vaga',
// 	"2" => '2 vagas',
// 	"3" => '3 vagas',
// 	"4+" => '4+ vagas',
// ];

$fbz_busca_valor_venda_list = [
	'200.000',
	'400.000',
	'600.000',
	'800.000',
	'1.000.000',
	'1.500.000',
	'2.000.000',
	'3.000.000',
	'4.000.000',
	'5.000.000'
];

$fbz_busca_valor_aluguel_list = [
	'1.000',
	'1.500',
	'2.000',
	'3.000',
	'4.000',
	'5.000',
	'6.000',
	'7.000',
	'8.000',
	'9.000',
	'10.000',
];

// Templates de SQL para Imóveis
// ----------------------------------------------------------------------------

$fbz_sql_imoveis_where	= "deleted=0
							AND publicado=1
							AND (
								status like '%venda%'
								OR status like '%aluguel%'
								OR status like '%temporada%'
							)";

$fbz_sql_imoveis_where_venda = " status like '%venda%' ";
$fbz_sql_imoveis_where_aluguel = " (status like '%aluguel%' OR status like '%temporada%') ";
$fbz_sql_imoveis_where_lancamentos = " (status like '%venda%' AND (situacao like 'lancamento' OR situacao like 'construcao')) ";

// SQL Querys Globais
// ----------------------------------------------------------------------------

$fbz_sql_query_paises	= "SELECT DISTINCT pais
							FROM imoveis
						   	WHERE $fbz_sql_imoveis_where
						   	AND pais<>''
						   	/*WHERE*/ /*GROUPBY*/
						   	ORDER BY pais ASC";

$fbz_sql_query_cidades	= "SELECT DISTINCT cidade
							FROM imoveis
						   	WHERE $fbz_sql_imoveis_where
						   	AND cidade<>''
						   	/*WHERE*/
						   	/*GROUPBY*/
						   	ORDER BY cidade ASC";

$fbz_sql_query_uf		= "SELECT DISTINCT uf
							FROM imoveis
						    WHERE $fbz_sql_imoveis_where
						    AND uf<>''
						    /*WHERE*/
						    /*GROUPBY*/
						    ORDER BY uf ASC";

$fbz_sql_query_categorias	= "SELECT DISTINCT categoria
							FROM imoveis
							WHERE $fbz_sql_imoveis_where
							AND categoria<>''
							/*WHERE*/
							/*GROUPBY*/
							ORDER BY categoria";

$fbz_sql_query_bairros	= "SELECT DISTINCT bairro
							FROM imoveis
							WHERE $fbz_sql_imoveis_where
							AND	bairro<>''
							/*WHERE*/
							/*GROUPBY*/
							ORDER BY bairro ASC";

$fbz_sql_query_empreendimentos	= "SELECT DISTINCT empreendimento
									FROM imoveis
									WHERE $fbz_sql_imoveis_where
									AND	empreendimento<>''
									/*WHERE*/
									/*GROUPBY*/
									ORDER BY empreendimento ASC";

$fbz_sql_query_fotos	= "SELECT * FROM fotos
							WHERE 1=1
							/*WHERE*/
							ORDER BY ordem ASC
							/*LIMIT*/";

$fbz_sql_query_imoveis = "SELECT *,
							IF(codigo<>'', codigo, id) AS codigo,
							dormitorios as DORM,
							vagas as VAGA,
							area_privativa as AREAP,
							area_total as AREAT
							FROM imoveis imo
							WHERE $fbz_sql_imoveis_where
							/*WHERE*/
							/*ORDERBY*/
							/*LIMIT*/";

$fbz_sql_query_marcadores = "SELECT
							latitude, longitude, valor_venda, valor_aluguel, id
							 FROM imoveis imo
							 WHERE
							 	$fbz_sql_imoveis_where
							 AND NOT ISNULL(latitude)
							 AND NOT ISNULL(longitude)
							 /*WHERE*/
							 /*ORDERBY*/
							 /*LIMIT*/";