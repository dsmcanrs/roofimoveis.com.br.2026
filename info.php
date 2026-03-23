<?

if(!isset($_SERVER['DOCUMENT_ROOT'])){
	if(isset($_SERVER['SCRIPT_FILENAME'])){
  	$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
	}
}

if(!isset($_SERVER['DOCUMENT_ROOT'])){
	if(isset($_SERVER['PATH_TRANSLATED'])){
  	$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
	}
}

if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
  if (isset($_SERVER['QUERY_STRING'])) {
   	$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
   }
}

echo 'SERVER_NAME: <strong>'.$_SERVER['SERVER_NAME'].'</strong><br />';
echo 'DOCUMENT_ROOT: <strong>'.$_SERVER['DOCUMENT_ROOT'].'</strong><br />';
echo 'HTTP_REFERER: <strong>'.$_SERVER['HTTP_REFERER'].'</strong><br />';
echo 'HTTP_HOST: <strong>'.$_SERVER['HTTP_HOST'].'</strong><br />';
echo 'path translated: <strong>'.$_SERVER['PATH_TRANSLATED'].'</strng><br />';
echo 'PATH_TRANSLATED: <strong>'.$_SERVER['PHP_SELF'].'</strong><br />';
echo 'REQUEST_URI: <strong>'.$_SERVER['REQUEST_URI'].'</strong><br />';
echo 'SCRIPT_FILENAME: <strong>'.$_SERVER['SCRIPT_FILENAME'].'</strong><br />';
echo 'Date: <strong>'.date("d-m-Y H:i:s",time()).'</strong><br />';

phpinfo();