<?php

/**
 * Função Padrão para Curl
 * @param method 	STRING 		post/get
 * @param url 		STRING 		url da api
 * @param headers 	ARRAY 		cabeçalhos http
 * @param data 		ARRAY/JSON
 *
 */
// function fbz_curl($method='get', $url, $query, $headers=null) {
function fbz_curl($url, $options=null) {

	// chdir( dirname(__FILE__) );

    $method   = !empty($options['method']) ? $options['method'] : 'get';
	$data     = !empty($options['data']) ? $options['data'] : null;
	$headers  = !empty($options['data']) ? $options['headers'] : null;

	$date 		= date("Y-m-d H:i:s");
	$dir  		= getcwd();
	$log_path 	= "{$dir}/log/curl_error.log";
	$log 		= '';
	$return		= [];

	if( !isset($headers) ) {
		$headers = array();
		$headers[] = "Accept: application/json";
	}

	$ch = curl_init();

	if( !empty($data) ) {

	    if(strtolower($method)=='get' && $data!=''){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	    	$data = is_array($data) ? http_build_query($data) : urlencode($data);
			$url = $url.'?'.$data;
		}

		if(strtolower($method)=='post'){
			// Body JSON
			if( gettype($data)=='string' && json_decode($data) ){
				$headers[] = 'Content-Type: application/json';
				$data = $data;
			}
			// Form Data
			else if( is_array($data) ){
				$data = http_build_query($data);
			}
			// Raw
			else{
				$data = $data;
			}
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

	}

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	$response = curl_exec($ch);

	$error = curl_error($ch);

	curl_close($ch);

	// Debug
	$log .= "\n\n$date";
	$log .= "\nurl: $url";
	$log .= "\nmethod: $method";
	$log .= "\nheaders:" . var_export($headers,true);
	$log .= "\nquery:" . var_export($data,true);
	$log .= "\nresponse:" . var_export($response,true);

	if( $error ){
		$log .= "\nerror: $error";
		file_put_contents($log_path, $log, FILE_APPEND);
		$return['error'] = $error;
	}else{
		// Debug
		// file_put_contents($log_path, $log, FILE_APPEND);
		$return = $response;
	}

	return $return;

}

