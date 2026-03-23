<?

// Faz replace em caminhos de imagens

function blog_media_path($str) {
	global $fbz_url_site;
	$return = preg_replace('/..\/assets\//im', $fbz_url_site . "assets/", $str);
	return $return;
}

// Procura Imagem na notícia

function blog_media($noticia){

	if($noticia["video"]!=''){
		$media = 'https://img.youtube.com/vi/'.$noticia["video"].'/default.jpg';
	}else if($noticia["imagem"]!='') {
		$media = '../'.$noticia["imagem"];
	}else {
		$output = preg_match_all('/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $noticia["texto"], $matches);
		if(!empty($matches[1][0])){
			$media = $matches[1][0];
		}else{
			// $media = 'img/sem_foto.png';
			$media = '';
		}
	}
	return $media;

}