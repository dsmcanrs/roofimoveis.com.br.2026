<?php

/**
 * Captcha para formulários
 *
 * @param  array  	$site['recaptcha']    	json de configuração para recptcha
 * @param  bool  	$fbz_captcha    		ativação do captcha simples
 *
 * @return html 	html captcha
 */

function fbz_captcha($acao='html') {

	global $site, $fbz_captcha;

	// var_export($site['recaptcha']);

	// recaptcha.json
	$recaptcha 	= $site['scripts']['google-recaptcha']['ativa'];
	$theme 		= $site['scripts']['google-recaptcha']['theme'];
	$secret_key = $site['scripts']['google-recaptcha']['secret-key'];
	$site_key 	= $site['scripts']['google-recaptcha']['site-key'];

	if( $recaptcha ){

		if ($acao=='validar') {

		    if ( !empty($_POST['g-recaptcha-response']) ) {
				$url = "https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$_POST['g-recaptcha-response'];
		    	$google = file_get_contents($url);
		    	$response = json_decode($google,true);
		    	// var_export($response); exit;
		    	if(!$response['success']) redir('js','Verificação anti-spam inválida.');
		    }else{
		    	redir('js','g-recaptcha-response empty.');
		    }
		}

		if ($acao=='html') {
			return '
					<div class="mx-auto">
						<div class="g-recaptcha"
							data-theme="'.$theme.'"
							data-sitekey="'.$site_key.'">
						</div>
					</div>
					<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
		}
	}

	else if( $fbz_captcha ){

		if( $acao=='validar' ){

			// var_export($_POST); exit;

			$post_cap = !empty($_POST["captcha"]) ? strtoupper($_POST["captcha"]) : null;
			$sess_cap = !empty($_SESSION["captcha"]) ? strtoupper($_SESSION["captcha"]) : null;

			// echo $post_cap .' = '. $sess_cap; exit;

			if( empty($post_cap) && empty($sess_cap) || ($post_cap!=$sess_cap) ){
				redir('js','Verificação anti-spam inválida.');
				exit;
			}

		}

		if ($acao=='html') {

			$return = '<div class="captcha row">
							<div class="col-md-6">
								<label class="d-none hidden">Anti-Spam</label>
								<input type="text" name="captcha" class="campo captcha valida form-control">
							</div>
							<div class="col-md-6">
								<img
									src="captcha/captcha.php"
									alt="captcha"
									class="rounded border"
									style="max-width:100%; max-height:38px">
							</div>
						</div>';

		}

		return $return;

	}

}