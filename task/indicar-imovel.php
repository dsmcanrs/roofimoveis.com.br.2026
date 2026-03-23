<?php

chdir('../');

require_once("inc/setup.php");

// header("Content-Type: text/html; charset=UTF-8",true);

// Indicar Imovel por Email
// ----------------------------------------------------------------------------

// echo '<pre>';
// var_dump($_POST); exit;

if( !is_array(post("codigo")) ) die('Código do imóvel não informado');

if( $GLOBALS['fbz_captcha'] || $site['recaptcha']['active'] ) fbz_captcha('validar');

$codigo 	= post("codigo");
$imovel 	= fbz_imovel_detalhes($codigo);
$foto 		= fbz_imovel_foto($imovel['id'],'g');

$nome 		= post("nome");
$email 		= post("email");
$dest_nome 	= post("destinatario");
$dest_email = post("emaildestinatario");
$mensagem 	= post("mensagem");

if(!is_email($email) || !is_email($dest_email)) {
	redir('js','Informe um e-mail válido.'); exit;
}

if ($fbz_cad_news_auto){
	fbz_news_add($email,$nome);
	fbz_news_add($dest_email,$dest_nome);
}

$corpo ='<table width="100%" border="0" cellspacing="0" cellpadding="2" style="font-family:Arial">
			<tr>
				<td colspan="2">
					Ol&aacute; <strong>'.$dest_nome.'</strong>,
					seu amigo(a) <strong>'.$nome.' ('.$email.')</strong>
					lhe indicou este imóvel:<br><br>
					<strong>'.nl2br($mensagem).'</strong>
					<br><br><hr />
				</td>
			</tr>
			<tr>
				<td width=200>
					<img src="'.$foto.'" width=180 height=160 />
				</td>
				<td>'.$imovel["categoria"].'<br>
					<strong>'.$imovel["bairro"].'</strong><br>
					<strong>'.$imovel["cidade"].'</strong><br />
					<p><a href="'.$fbz_url_site.'detalhes.php?id='.$codigo.'">Veja detalhes</a></p>
				</td>
				</tr>
				<tr>
					<td colspan="2" align="center" style="font-size:11px">
						<hr />
						<strong>'.$site['geral']['nome'].'</strong><br />'.$fbz_url_site.'<br />
					</td>
				</tr>
		</table>';

$html = $corpo;

// echo $html; exit;

$subject = "Indicação de imóvel";

if(!is_email($dest_email)) {
	redir('js','Informe um e-mail válido.');
	exit;
}

mail_send($email, $dest_email, $subject, $html);

redir('../obrigado?nome='.$nome);
