<?php

include("inc/setup.php");

// Template Start
// ----------------------------------------------------------------------------

$tpl = [];
$latte = new Latte\Engine;
$latte->setTempDirectory('cache');
$TemplatePath = template_path(current_page());
$template = $latte->createTemplate($TemplatePath);

include("inc/header.php");

// ----------------------------------------------------------------------------

// var_export($_GET); exit;

$parsed_url = parse_url($_SERVER['REQUEST_URI']);
$path = pathinfo($parsed_url['path'], PATHINFO_FILENAME);
$pg = str_replace("-","%",$path);

$sql = "SELECT * FROM paginas WHERE ativa=1 AND titulo like '$pg'";

// exit($sql);

$query = query($sql);

if(query_num($query)==0) header("location:${$fbz_url_site}404");

$pagina = fetch($query);

// var_export($pagina); exit;

// HTML Head
$tpl['site']['title'] = $pagina['titulo']." - ".$site['geral']['title'];
$tpl['site']['desc'] = $pagina['chamada'];

// Dados da página
// ----------------------------------------------------------------------------

$tpl['pagina']['tipo'] = $pagina['tipo'];
$tpl['pagina']['grupo'] = $pagina['grupo'];
$tpl['pagina']['titulo'] = $pagina['titulo'];
$tpl['pagina']['chamada'] = nl2br($pagina['chamada']);
$tpl['pagina']['texto'] = blog_media_path($pagina['texto']);
$tpl['pagina']['show_texto'] = trim(strip_tags($pagina['texto']))!='' ? true : false;

if( trim($pagina['banner'])!='' ){
	$tpl['pagina']['banner'] = str_replace('../','',$pagina['banner']);
}

// Menu da seção
// ----------------------------------------------------------------------------

if( $template->hasBlock('secao_menu') ) {

	$sql_mn = query("SELECT * from paginas
					  where
					  	  ativa=1
					  AND grupo like '{$pagina['grupo']}'
					  ORDER BY ordem
					");

	$total=0;

	while($secao_menu = fetch($sql_mn)) {

		$total++;

		$tpl['secao_menu']['total'] = $total;

		$url = !empty($secao_menu["url"])
			? $secao_menu["url"]
			: file_name_format($secao_menu['titulo']);

		$active = strpos($_SERVER['REQUEST_URI'], $url)
			? 'bg-color-1 text-white'
			: '';

		$tpl['secao_menu']['paginas'][] = [
			'grupo' => $secao_menu["grupo"],
			'titulo' => $secao_menu["titulo"],
			'target' => $secao_menu["target"],
			'url' => $url,
			'active' => $active,
		];
	}

	// echo "<pre>"; var_export($tpl['secao_menu']); exit;

}

// Formulário
// ----------------------------------------------------------------------------

if( $pagina["form"]!='' ){

	// Formato de nomes dos campos
	$formulario_set_names = false;

	// Form JSON
	$json_form = $site['forms'][$pagina["form"]];

	// Send Mail
	if ( isset($_POST['send']) ) {

		// var_export($_POST); exit;
		// var_export($_FILES); exit;

		// setup.php || recaptcha.json
		if( $GLOBALS['fbz_captcha'] || $site['recaptcha']['active'] ) fbz_captcha('validar');

		// Localizar nome, email e telefone
		foreach( $json_form['fields'] as $index => $campo ){
			if( isset($campo['type']) && $campo['type']!='hidden'){
				$campo['label'] = trim($campo['label']);
				$field = $formulario_set_names ? formulario_campo_name($campo['label']) : "campo_" . ($index) ;
				if( !isset($nome) && preg_match('/nome/i',$campo['label']) )
					$nome = $_POST[$field];
				if( !isset($email) && preg_match('/(e|e-)mail/i',$campo['label']) )
					$email = $_POST[$field];
				if( !isset($telefone) && preg_match('/telefone|fone|celular/i',$campo['label']) )
					$telefone = $_POST[$field];
			}
		}

		// die("$nome > $email > $telefone");

		if( $nome=='' || $email=='' ){
			echo 'O formulário precisa ter obrigatóriamente os campos: Nome e E-mail. ';
			exit;
		}

		$para 		= !empty($json_form['email']) ? $json_form['email'] : $site['geral']['email'];
		$titulo 	= "{$json_form['nome']}: {$nome}";
		$mensagem 	= formulario_email($json_form, 'tpl/form-mail.html');
		$mensagem 	= str_replace('%TITULO%', $titulo, $mensagem);
		$mensagem 	= str_replace('%DOMINIO%', $fbz_url_site, $mensagem);

		// die($mensagem);

		$mensagem = mail_send( $email, 'dsmcanrs@gmail.com', $titulo, $mensagem);
		$mensagem = mail_send( $email, $para, $titulo, $mensagem);

		// die($return);

		$contato['nome'] = $nome;
		$contato['email'] = $email;
		$contato['telefone'] = $telefone;
		$contato['mensagem'] = $mensagem;
		$contato['origem'] = $json_form['nome'];

		// formulario_salvar($contato);
		lead_save($contato);

		redir('obrigado?nome='.$nome);

	}
	// send

	// Montagem do Formulário
	$tpl['form_pagina']['action'] 	= $basename;
	$tpl['form_pagina']['btn'] 		= $json_form['botao'];
	$tpl['form_pagina']['fields'] 	= formulario_html($json_form);
	$tpl['form_pagina']['captcha'] 	= fbz_captcha();

}

// Corretores
// ----------------------------------------------------------------------------

if( $pagina['corretores']==1 ) {

	$sql_usuarios = "SELECT
					(SELECT arquivo FROM arquivos
					 WHERE registro=usuarios.id AND modulo='usuarios' LIMIT 1) AS foto,
					 usuarios.*
					FROM usuarios
					WHERE ativo=1 AND exibir=1
					ORDER BY nome";

	$query_usuarios = query($sql_usuarios);

	while($corretor = fetch($query_usuarios)) {
		$tpl['corretores'][] = [
			'nome' => $corretor['nome'],
			'foto' => $corretor['foto']!='' ? $corretor['foto'] : 'assets/img/corretor.jpg',
			'telefone' => $corretor['fone_celular'],
			'telefone_num' => '55'.preg_replace("/[^0-9]/","",$corretor["fone_celular"]),
			'email' => $corretor['email'],
		];
	}

}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl['pagina']); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);