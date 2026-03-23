<?php

$directory = __DIR__;
$phpFiles = glob($directory . '/*.php');

foreach ($phpFiles as $file) {
	$filename = basename($file);
	if (strpos($filename, 'fnc_') === 0 || strpos($filename, 'class.') === 0) {
		require_once($file);
		// echo "<!-- Arquivo de funções incluído: $file -->\n";
	}
}