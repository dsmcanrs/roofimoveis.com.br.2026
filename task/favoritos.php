<?php

chdir('../');

require_once("inc/setup.php");

// var_dump($_GET); exit;
// var_dump($_POST); exit;

// Enviar Favoritos por Email
// ----------------------------------------------------------------------------

$nome_rem 	= post("nome");
$email_rem	= post("email");
$nome_dest	= post("nome_destinatario");
$email_dest	= post("email_destinatario");
$mensagem 	= post("mensagem");
$codigos 	= post("codigos");
$codigos 	= strstr($codigos, ',') ? explode(',', $codigos) : array($codigos);

// var_dump($codigos); exit;

if( $GLOBALS['fbz_captcha'] || $site['recaptcha']['active'] ) fbz_captcha('validar');

if( !is_email($email_rem) ) {
	redir('js','E-mail do remetende inválido.');
	exit;
}
if( !is_email($email_dest) ) {
	redir('js','E-mail do destinatário inválido.');
	exit;
}

if (is_array($codigos)) {

	$imoveis = '<table>';

	foreach($codigos as $codigo) {
		$imovel = fbz_imovel_detalhes($codigo);
		$url	= $fbz_url_site.fbz_imovel_url($imovel,$fbz_tipo_url);
		$foto 	= fbz_imovel_foto($imovel['id'],'g');
		$desc 	= $imovel['codigo'].": ";
		$desc 	.= fbz_ucfirst($imovel['categoria']).', '.$imovel['bairro'];
		$imoveis .= '<tr>
						<td>
						<a href="'.$url.'"><img src="'.$foto.'" style="width:80px"></a>
						</td>
						<td>
						'.$desc.'<br><a href="'.$url.'">ver no site</a>
						</td>
						</tr>';
	}

	$imoveis .= '</table>';

}

// die($imoveis);

// mail Destinatário
// ----------------------------------------------------------------------------

	$titulo	= "Imóveis Favoritos de ".$nome_rem;

	$html 	= "<h2>$titulo</h2><hr>";
	$html 	.= "<div style=\"margin-bottom:30px\">".$mensagem."</div>";
	$html 	.= $imoveis;

	// die($html);

	mail_send(
		$site['geral']['email'],
		$email_dest,
		$titulo,
		$html
	);

// Email Rementente
// ----------------------------------------------------------------------------

	$titulo 	= "Lista de imóveis enviados para ".$nome_dest;
	$html2  	= "<h2>$titulo</h2><hr>";
	$html2  	.= $imoveis;

	// die($html2);

	mail_send(
		$site['geral']['email'],
		$email_rem,
		$titulo,
		$html2
	);

// Add Mailing
// ----------------------------------------------------------------------------

if ($fbz_cad_news_auto){
	fbz_news_add($email_rem, $nome_rem);
	fbz_news_add($email_dest, $nome_dest);
}

redir('js','Sua lista de imóveis foi enviada com sucesso.');