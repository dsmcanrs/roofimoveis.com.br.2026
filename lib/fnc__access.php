<?php

/**
 * Log de acesso
 * Usado para vericar sites que estão tomando ataques DDoS
 * @param   pg              path+nome do arquivo
 * @param   INPUT_SERVER
 */

function fbz_log_acesso($file='') {

    $date = date('Ymd');
    $datetime = date('Y-m-d H:i:s');

    $file = $file=='' ? "log/access-$date.log" : $file;

    $remote_addr = $_SERVER['REMOTE_ADDR'];
    $http_refferer = $_SERVER['HTTP_REFERER'];
    $request_method = $_SERVER['REQUEST_METHOD'];
    $http_user_agent = $_SERVER['HTTP_USER_AGENT'];

    $url = $_SERVER['SCRIPT_NAME'];
    $get = $_SERVER['QUERY_STRING'];
    
    $log = "$datetime\t\t$remote_addr\t\t$url\t\t$get\t\t$http_user_agent\n";    

    file_put_contents($file, $log, FILE_APPEND);

    // Checar a última vez que os arquivos foram limpos
    $last_clean_file = 'log/access-clean.log';
    $last_clean_time = file_exists($last_clean_file) ? intval(file_get_contents($last_clean_file)) : 0;
    $current_time = time();
    $one_day = 24 * 60 * 60; // Um dia em segundos

    if ($current_time - $last_clean_time >= $one_day) {
        
        // Excluir arquivos de log mais antigos do que 7 dias
        $log_directory = "log/";
        $files = glob($log_directory . "access-*.log");
        $expiry_time = $current_time - (7 * $one_day);

        foreach ($files as $file) {
            if (filemtime($file) < $expiry_time) {
                @unlink($file);
            }
        }

        // Salvar a data e hora da última limpeza
        file_put_contents($last_clean_file, $current_time);
    }

}

fbz_log_acesso();