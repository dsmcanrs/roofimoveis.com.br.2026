<?php

include("inc/setup.php");

// Template Start
// ----------------------------------------------------------------------------

$tpl = [];
$latte = new Latte\Engine;
$TemplatePath = template_path(current_page());
$template = $latte->createTemplate($TemplatePath);
$latte->setTempDirectory('cache');

include("inc/header.php");

// Formulário
// ----------------------------------------------------------------------------

if( $template->hasBlock('form_depoimento') ) {

	// Formato de nomes dos campos
	$formulario_set_names = true;

	$json_form = array();


	$json_form['fields'][] = array(
		"label" => "send",
		"type" => "hidden",
		"class" => "valida",
		"value" => 1
	);
	$json_form['fields'][] = array(
		"label" => "Nome",
		"placeholder" => "Nome",
		"type" => "text","class" => "valida",
		"cols" => 12
	);
	$json_form['fields'][] = array(
		"label" => "E-mail",
		"placeholder" => "E-mail",
		"type" => "text",
		"class" => "valida valida-email",
		"cols" => 12
	);
	$json_form['fields'][] = array(
		"label" => "Telefone",
		"placeholder" => "Telefone",
		"type" => "text",
		"class" => "valida mask-fone",
		"cols" => 12
	);
	$json_form['fields'][] = array(
		"label" => "Conte como foi sua experiência",
		"placeholder" => "Conte-nos sua percepção durante o processo, qualidade do atendimento, habilidade e assistência do corretor.",
		"type" => "textarea",
		"class" => "valida",
		"cols" => 12,
		"rows" => 3
	);
	$json_form['fields'][] = array(
		"label" => "Anexar Foto",
		"type" => "file",
	);

	// Send Mail
	if ( isset($_POST['send']) ) {

		// var_export($json_form);
		// var_export($_POST);
		// var_export($_FILES);
		// exit;

		// setup.php || recaptcha.json
		if( $GLOBALS['fbz_captcha'] || $site['recaptcha']['active'] ) fbz_captcha('validar');

		unset($_POST['send']);

		$para = $site['geral']['email'];
		$nome = $_POST['nome'];
		$email = $_POST['email'];
		$telefone = $_POST['telefone'];
		$nota = $_POST['nota'];
		$texto = $_POST['conte_como_foi_sua_experiencia'];

		$titulo = 'Depoimento de Cliente';

		// header
		$mensagem .= "<h2>$titulo</h2><br>";

		// body
		foreach($_POST as $index => $value){
			if( $value!='' ){
				$mensagem .= "<strong>". fbz_ucfirst(str_replace('_',' ',$index)) ."</strong>: $value<br>";
			}
		}

		// footer
		$mensagem .= "<br><a href=\"$fbz_url_site\" target=\"_blank\">$fbz_url_site</a>";
		$mensagem .= "<br><br>⚠️ <b>Este depoimento aguarda aprovação via gerenciador.</b>";

		// die($mensagem);

		$query = "INSERT into depoimentos";
		$query .= "(nome, email, telefone, texto, nota, data)";
		$query .= "values";
		$query .= "('{$nome}', '{$email}', '{$telefone}', '{$texto}', {$nota}, NOW())";

		query($query);

		$id = mysqli_insert_id($conn);

		// Upload da Foto

		$max_width = 600;
		$path = mb_strtolower( "assets/depoimentos/{$id}/imagem/" );

		// echo $path; exit;

		// var_export($_FILES); exit;

		foreach($_FILES as $file){
			if($file['size']>0){
				$imagem = upload_image($file, $path, $max_width);
				$query = "UPDATE depoimentos
						SET imagem = '../{$imagem}'
						WHERE id = {$id}";
				query($query);
			}
		}

		mail_send( $email, $para, $titulo, $mensagem);
		mail_send( $email, 'dsmcanrs@gmail.com', $titulo, $mensagem);

		redir('obrigado?nome='.$nome);

	}

	// Montagem do Formulário
	$tpl['form_depoimento']['action'] =  $fbz_url_site . 'depoimentos';;
	$tpl['form_depoimento']['form'] = formulario_html($json_form);
	$tpl['form_depoimento']['captcha'] = fbz_captcha();

}

// Dados da página
// ----------------------------------------------------------------------------

$tpl['titulo'] = 'Depoimentos';
$tpl['chamada'] = 'Descubra o que nossos clientes têm a dizer sobre a experiência imobiliária conosco!
							<br>De elogios ao atendimento a valiosos insights sobre negociações eficientes e transparência.
							<br>Satisfação garantida é a nossa prioridade.';

// HTML Head
$tpl['site']['title'] = $tpl['titulo']." - ".$site['geral']['title'];
$tpl['site']['desc'] = $tpl['chamada'];

// Depoimentos
// ----------------------------------------------------------------------------

if( $template->hasBlock('depoimentos') ) {

	if (get('q')!="") {
		$q = urldecode(get('q'));
		$sql_where = "AND nome like '%{$q}%' ";
		$tpl['q'] = $q;
	}

	$sql_depos = "SELECT * from depoimentos
				  WHERE ativo = 1
				  {$sql_where}
				  order by data desc";

	// echo $sql_depos; exit;

	$total_reg  = get_total($sql_depos);
	$regs_pp    = 10;
	$pg         = !isset($_GET["pg"]) ? 1 : $_GET["pg"];
	$comeca_em  = ($pg-1) * $regs_pp;
	$sql_limit  = " LIMIT " . $comeca_em . "," . $regs_pp;

	$query_depos = query($sql_depos . $sql_limit);

	if (query_num($query_depos)>0) {

		while($depoimento = fetch($query_depos)) {

			$dia = date('d', strtotime($depoimento["data"]));
			$mes = ucfirst(monthname(date('m', strtotime($depoimento["data"]))));
			$ano = date('Y', strtotime($depoimento["data"]));
			$estrelas = '';

			for ($i=0; $i<5; $i++) {
				if( $depoimento["nota"]>$i ){
					$estrelas .= "<i class='bx bxs-star'></i>";
				}else{
					$estrelas .= "<i class='bx bx-star'></i>";
				}
			}

			$foto = $depoimento["imagem"]!=''
					? blog_media_path($depoimento["imagem"])
					: "{$fbz_url_site}img/user-happy.png";

			$depoimento['data']		= "$mes de $ano";
			$depoimento['nome']		= $depoimento["nome"];
			$depoimento['texto']	= $depoimento["texto"];
			$depoimento['estrelas']	= $estrelas;
			$depoimento['foto']		= $foto;

			$tpl['depoimentos'][] = $depoimento;

		}

	}

	if (query_num($query_depos)==0) {
		$tpl['titulo'] = 'Depoimentos';
		$tpl['chamada'] = 'Queremos saber a sua opinião sobre a experiência conosco.';
	}

	// Paginacao

	if($total_reg>$regs_pp) {
		fbz_paginacao(
			$pg,
			$regs_pp,
			$total_reg
		);
	}

}

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);