<?php

function fbz_news_add($email,$nome="",$fone="") {

	$return = "";
	$ok = true;

	if ($nome=="") $nome = $email;

	if (!is_email($email)) {

		$ok = false;
		$return = "Email ".$email." é inválido.";

	} else {

		$sql = "SELECT * FROM fbz_mailing WHERE email = '$email'";
		$verifica = qry($sql);
		$records = qryn($verifica);

		if ($records>0) {

			$ok = false;
			$return .= "Email ".$email." já cadastrado. Obrigado por se cadastrar.";

		} else {

			$sql1 = "INSERT INTO fbz_mailing
					(nome,email,fone,data,ip)
					VALUES
					('$nome','$email','$fone','".date("Y-m-d H:i:s",time())."','".$_SERVER['REMOTE_ADDR']."')
					";

			$sql2 = "INSERT INTO fbz_mensagens
					(data, nome, email, fone, mensagem)
					values
					(NOW(), '$nome', '$email', '$fone', 'Cadastro newsletter')
					";

			qry($sql1);

			$return = "O E-mail ".$email." foi cadastrado com sucesso. Obrigado por se cadastrar.";

		}

	}

	return $return;

}
