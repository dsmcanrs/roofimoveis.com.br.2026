<?php

/**
 * Paginação de Querys
 *
 * @param string 	$param     	parametros adicionais a url
 * @param int 		$pg        	pagina atual
 * @param int 		$regs_pp   	registros por pagina
 * @param int		$total_reg  total de registros
 *
 * @return HTML					Carrega dados no bloco PAGINACAO do template
 */

function fbz_paginacao($pg, $regs_pp, $total_reg, $query=null) {

	global $tpl, $fbz_url_site;

	// var_export(func_get_args()); exit;

	$_HTTP = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
	$_HOST = $_SERVER['HTTP_HOST'];
 	$_URI  = parse_url($_SERVER['REQUEST_URI']);
	$_URI  = $_URI['path'];

	// die($_URI);

	$path = $_HTTP . $_HOST . $_URI;
	$path = str_replace($fbz_url_site, '', $path);

	// die($path);

	$param 	= isset($query) ? $query : $query_string = http_build_query($_GET);;
	$param 	= str_replace("pg=$pg","",$param);
	$param 	= preg_replace('/^(&+)/','',$param);

	// die($param);

	$paginacao = array();

	$intervalo  = 6;
	$anterior   = $pg -1;
	$proximo    = $pg + 1;
	$tp         = $total_reg / $regs_pp;
	$pi         = ceil($pg-($intervalo/2));
	$pf         = $pg+($intervalo/2);

	if ($pi<1) 			$pi = 1;
	if ($pf<$intervalo) $pf = $intervalo;

	$paginacao['atual'] = $pg;

	// prev
	if ($pg > 1){
		$paginacao['prev'] = $path . '?pg='.$anterior.'&'.$param;
	}

	// next
	if ($pg < $tp){
		$paginacao['next'] = $path . '?pg='.$proximo.'&'.$param;
	}

	// numeral
	for ($pi; $pi<$pf; $pi++) {
		if ($pi < $tp+1){
			$paginacao['paginas'][$pi] = $path . '?pg='.$pi.'&'.$param;
		}
	}

	// var_export($paginacao); exit();

	$tpl['paginacao']['prev'] = isset($paginacao['prev']) ? $paginacao['prev'] : false;
	$tpl['paginacao']['next'] = isset($paginacao['next']) ? $paginacao['next'] : false;

	if( isset($paginacao['paginas']) ){
		foreach ($paginacao['paginas'] as $num => $url) {
	        $tpl['paginacao']['paginas'][$num]['pg'] 		= $num;
	        $tpl['paginacao']['paginas'][$num]['url'] 		= $url;
	        $tpl['paginacao']['paginas'][$num]['active'] 	= $paginacao['atual']==$num ? 'active' : '' ;
		}
	}

}

