<?php

/**
 * Extrai o código do vídeo da url
 *
 * @param  	url 	url do video
 *
 * @return  string 	url de embed
 */
function youtube_video($url){

	$video = $url;

	// https://www.youtube.com/watch?v=XXX
	if( strstr($video, 'youtube.com/watch') ){
		$part = explode('?v=', $video);
		$video = $part[1];
	}

	// https://youtu.be/XXX
	if( strstr($video, 'youtu.be/') ){
		$part = explode('be/', $video);
		$video = $part[1];
	}

	// https://youtube.com/shorts/XXX
	if( strstr($video, 'youtube.com/shorts/') ){
		$part = explode('shorts/', $video);
		$video = $part[1];

	}

	$video = trim($video);

	return $video;

}

function getYoutubeShorts() {

    $apiUrl = "https://www.googleapis.com/youtube/v3/search";
    $channelId = 'UCIiDKVZ1S7C9BfKC3zeI5sQ';
    $apiKey = 'AIzaSyBL_mDFn124pLeW6-RDaN5C2p25B5VImrM';

    $logDir = 'log';
    $logFile = 'shorts.json';

    // Diretório e arquivo de log
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $filePath = $logDir . DIRECTORY_SEPARATOR . $logFile;

    // Verifica se o arquivo existe e se foi atualizado nas últimas 12 horas
    if (file_exists($filePath) && (time() - filemtime($filePath) < 12 * 60 * 60)) {
        return json_decode(file_get_contents($filePath), true);
    }

    $params = [
        'part' => 'snippet',
        'channelId' => $channelId,
        'maxResults' => 12, // Máximo permitido por página
        'order' => 'date',
        'type' => 'video',
        'key' => $apiKey
    ];

    // Inicializa cURL
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Armazena todos os vídeos "Shorts"
    $shorts = [];

    do {

        // Constrói a URL
        $query = http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, "$apiUrl?$query");

        // Executa a requisição
        $response = curl_exec($ch);

        $data = json_decode($response, true);

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {

                $videoId = $item['id']['videoId'];
                $videoImg = $item['snippet']['thumbnails']['high']['url'];
                $videoImg = "https://img.youtube.com/vi/{$item['id']['videoId']}/hq720.jpg";
                $description = $item['snippet']['description'];

                // Filtra vídeos que contenham "#shorts" na descrição
                if (stripos($description, '#shorts') !== false) {
                    $shorts[] = [
                        'title' => $item['snippet']['title'],
                        'videoId' => $videoId,
                        'videoImg' => $videoImg,
                        'url' => "https://www.youtube.com/watch?v=$videoId"
                    ];
                }

            }
        }

        // Próxima página, se disponível
        $params['pageToken'] = $data['nextPageToken'] ?? null;

    } while (!empty($params['pageToken']));

    // Fecha o cURL
    curl_close($ch);

    // Salva os resultados em um arquivo JSON
    file_put_contents($filePath, json_encode($shorts, JSON_PRETTY_PRINT));

    return $shorts;

}
