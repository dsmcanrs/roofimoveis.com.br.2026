<?php

function is_email( $email ){
	if( preg_match("(^[A-Za-z0-9_.-]+@([A-Za-z0-9_.-]+\.)+[A-Za-z]{2,4}$)", $email) ){
		return true;
	} else {
		return false;
	}
}

function mail_send($de, $para, $titulo, $mensagem) {

	// var_export(func_get_args()); exit;

	global $site;

	$anexos = '';

	if( isset($_FILES) && sizeof($_FILES)>0 ){
		$anexos = mail_attachments();
	}

	$mensagem .= $anexos;

	if( is_array($site['smtp']) ){
		phpmailer_send($de, $para, $titulo, $mensagem);
	}else{
		mailgun_send($de, $para, $titulo, $mensagem);
	}

	// Mensagem + links dos anexos
	return $mensagem;

}

function mailgun_send($de, $para, $titulo, $mensagem){

	global $fbz_url_site;

	$date = date('Y-m-d H:i:s',time());

	$post = array(
		'de' 		=> $de,
		'para' 		=> $para,
		'titulo'   	=> $titulo,
		'mensagem' 	=> $mensagem
	);

	// var_export($post); exit;

	// $ch = curl_init('http://localhost/apps/mailsend/index.php');
	$ch = curl_init('https://www.fullbiz.com.br/mailsend/index.php');

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

	$response = curl_exec($ch);

	// var_export($response); exit;

	$err = curl_error($ch);

	curl_close($ch);

	chdir( dirname(__FILE__) );

	if($err){
		$log = json_encode($err, JSON_UNESCAPED_UNICODE);
		file_put_contents('../log/mail.log', "[$date] [ERROR] $log\n", FILE_APPEND);
	}else{
		$log = json_encode($response, JSON_UNESCAPED_UNICODE);
		file_put_contents('../log/mail.log', "[$date] [INFO] $log\n", FILE_APPEND);
	}

	return $response;

}

/**
 * PHP Mailer
 * https://github.com/PHPMailer/PHPMailer
 * https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting
 * debugging: 1 = errors and messages, 2 = messages only
 */
function phpmailer_send($de, $para, $titulo, $mensagem){

	$smtp = ($site['smtp']['host']!=""
			&& $site['smtp']['conta']!=""
			&& $site['smtp']['senha']!=""
			&& $site['smtp']['porta']!="") ? true : false;

	if( !$smtp ){
		echo "SMTP não configurado corretamente.";
		exit;
	}

	$mail = new PHPMailer();

	$mail->IsSMTP();
	$mail->IsHTML(true);

	$mail->Mailer = "smtp";
	$mail->CharSet = "UTF-8";
	$mail->Timeout = 60;
	// $mail->SMTPDebug = 2;

	$mail->SMTPAuth = true;
	$mail->SMTPSecure = 'ssl';

	$mail->Host 		= $site['smtp']['host'];
	$mail->Port 		= $site['smtp']['porta'];
	$mail->Username 	= $site['smtp']['conta'];
	$mail->Password 	= $site['smtp']['senha'];
	$mail->FromName 	= $site['geral']['nome'];
	$mail->Subject 		= $titulo;
	$mail->Body 		= $mensagem;

	$mail->SetFrom($site['smtp']['conta']);
	$mail->AddReplyTo($de);

	$to = preg_split("/[\s,;]/",$para);

	for($i=0;$i<count($to);$i++) {
		if( is_email($to[$i]) ){
			$mail->AddAddress($to[$i]);
		}
	}

	$return = $mail->Send();

	$log = json_encode($return, JSON_UNESCAPED_UNICODE);
	file_put_contents('../log/mail.log', "[$date] [INFO] $log\n", FILE_APPEND);

	return $response;

}

// Função para anexar arquivos
// Anexa arquivos enviados via $_FILES ao email
function mail_attachments(){

	global $fbz_url_site;

	$er_extensoes = "/php|js|css|cgi|exe|bat/i";
	$uploaded_files = [];
	$return = '';

	if( isset($_FILES) && sizeof($_FILES)>0 ){
		$files = [];
		$i = 0;
		foreach( $_FILES as $index => $__FILES ) {
			if( is_array($__FILES['name']) ){
				$count = count($__FILES['name']);
				for( $j=0; $j<$count; $j++ ){
					if( !empty($__FILES['name'][$j]) ){
						// echo "file name {$j}: ".$__FILES['name'][$j]."<br>";
						$files[$i]['name'] 		= $__FILES['name'][$j];
						$files[$i]['tmp_name'] 	= $__FILES['tmp_name'][$j];
						$files[$i]['type'] 		= $__FILES['type'][$j];
						$files[$i]['size'] 		= $__FILES['size'][$j];
						$i++;
					}
				}
			}else if( !empty($__FILES['name'][$i]) ){
				// echo "file name {$i}: ".$__FILES['name'][$i]."<br>";
				$files[$i]['name'] 		= $__FILES['name'];
				$files[$i]['tmp_name'] 	= $__FILES['tmp_name'];
				$files[$i]['type'] 		= $__FILES['type'];
				$files[$i]['size'] 		= $__FILES['size'];
				$i++;
			}
		}
	}

	// echo "<pre>";
	// var_export($files); exit;

	// Upload dos arquivos para a pasta assets/anexos
	if( is_array($files) ){

		$upload_dir = dirname(__FILE__) . '/../assets/anexos/';

		// Cria a pasta se não existir
		if (!is_dir($upload_dir)) {
			mkdir($upload_dir, 0755, true);
		}

		foreach( $files as $index => $file ) {
			$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
			if( !preg_match($er_extensoes, $ext) && $file['size']>0 ) {

				// Gera hash do conteúdo do arquivo
				$file_content = file_get_contents($file['tmp_name']);
				$file_hash = md5($file_content);
				$filename = $file_hash . '.' . $ext;
				$filepath = $upload_dir . $filename;

				// Verifica se o arquivo já existe (mesmo hash)
				if (!file_exists($filepath)) {
					// Move o arquivo para a pasta de destino apenas se não existir
					move_uploaded_file($file['tmp_name'], $filepath);
				}

				$file_url = $fbz_url_site . 'assets/anexos/' . $filename;
				$uploaded_files[] = array(
					'name' => $file['name'],
					'url' => $file_url
				);
			}
		}
	}

	// Adiciona os links dos arquivos ao final da mensagem
	if (!empty($uploaded_files)) {
		$return .= "<br>&nbsp;<br><b>Arquivos anexados</b>:<br>";
		foreach ($uploaded_files as $file) {
			$return .= "🔗 <a href=\"{$file['url']}\" target=\"_blank\">{$file['name']}</a><br>";
		}
	}

	return $return;

}