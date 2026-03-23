<?php

/**
 *
 * Anti SQL Injection
 * FILTER_SANITIZE_MAGIC_QUOTES
 *
 * Anti XSS Injection
 * FILTER_SANITIZE_SPECIAL_CHARS
 *
 */

// $_GET = filter_var_array($_GET, FILTER_SANITIZE_MAGIC_QUOTES);
// $_GET = filter_var_array($_GET, FILTER_SANITIZE_SPECIAL_CHARS);

// $_POST = filter_var_array($_POST, FILTER_SANITIZE_MAGIC_QUOTES);
// $_POST = filter_var_array($_POST, FILTER_SANITIZE_SPECIAL_CHARS);

// ----------------------------------------------------------------------------

/**
 * Escapa aspas simples e duplas
 * Converte caracteres HTML
 * htmlspecialchars($return, ENT_QUOTES, 'UTF-8');
 * @param   String  $value  receberá cada elemento do POST ou GET
 * @return  Array   retorna GET e POST convertidos
 */
function sanitize($value){
    if( is_array($value) ){
        $value = array_map('addslashes',$value);
        $value = array_map('htmlspecialchars',$value);
    }else{
        $value = addslashes($value);
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    return $value;
}

$_GET = array_map( 'sanitize', $_GET );
$_POST = array_map( 'sanitize', $_POST );

// var_export($_GET); exit;
// var_export($_POST); exit;

// ----------------------------------------------------------------------------

function InjectDetect(){

    $denied = array(
        "select",
        'into',
        'between',
        'groupby',
        "delete",
        "update",
        "insert",
        "alter",
        "table",
        "database",
        "drop",
        "union",
        "when",
        "then",
        "1=1",
        'or 1',
        'exec',
        'concat',
        'table_name',
        'like',
        'boolean',
        'columns',
        'values',
        'sleep',
        // '*',
        // '´',
        // '`',
    );

    if( !empty($_GET) ){

        foreach ($_GET as $key => $value) {
            foreach ($denied as $word) {
                if (stripos($value, $word) !== false) {
                    return true;
                }
            }
        }

    }

    return false;

}

function InjectDetect2(){

    $denied = array(
        // DDL / DML
        'select', 'insert', 'update', 'delete', 'drop', 'alter',
        'into', 'union', 'exec',
        // cláusulas e operadores
        'between', 'having', 'when', 'then', 'or 1', '1=1',
        // objetos de schema
        'table', 'table_name', 'database', 'columns', 'values',
        'information_schema',
        // funções de extração / erro usadas nos logs
        'extractvalue', 'updatexml', 'gtid_subset', 'json_keys',
        'procedure', 'analyse',
        'elt(', 'exp(', 'concat(', 'convert(', 'char(',
        'benchmark(', 'sleep(',
        'load_file', 'outfile',
        // obfuscação via comentário SQL (/**/), variáveis (@@) e hex (0x)
        '/**/', '@@', '0x',
        // outros
        'boolean', 'like',
    );

    // Coleta todos os valores escalares de GET e POST (suporta arrays aninhados)
    $inputs = array();
    array_walk_recursive($_GET,  function($v) use (&$inputs){ $inputs[] = $v; });
    array_walk_recursive($_POST, function($v) use (&$inputs){ $inputs[] = $v; });

    foreach ($inputs as $value) {
        if (!is_string($value)) continue;
        foreach ($denied as $word) {
            if (stripos($value, $word) !== false) {
                return true;
            }
        }
    }

    return false;

}

if( InjectDetect() ){
    exit('client denied by server configuration.');
}

// ----------------------------------------------------------------------------

/**
 * Não aceitar posts fora do domímio do site
 */

// if( $_SERVER['REQUEST_METHOD']=='POST' ) {
//     // echo $_SERVER['SERVER_NAME'] .': '.$_SERVER['HTTP_ORIGIN']; exit;
//     if( !empty($_SERVER['HTTP_ORIGIN']) ) {
//         if( !strstr($_SERVER['HTTP_ORIGIN'], $_SERVER['SERVER_NAME']) ){
//             exit('CSRF protection in POST request: detected invalid Origin header');
//         }
//     }
//     if( empty($_SERVER['HTTP_ORIGIN']) ) {
//         exit('HTTP Origin invalid.');
//     }
// }