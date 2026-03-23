<?php

include("inc/setup.php");

// Template Start
// ----------------------------------------------------------------------------

$tpl = [];
$latte = new Latte\Engine;
$TemplatePath = template_path(current_page());
$template = $latte->createTemplate($TemplatePath);
$latte->setTempDirectory('cache');

include("inc/header.php");

// Obrigado
// ----------------------------------------------------------------------------

$nome 	= isset($_GET['nome']) ? $_GET['nome'] : 'Obrigado';
$titulo = 'Mensagem enviada com sucesso';

// HTML Head
$tpl['site']['title'] = $titulo. " - ". $site['geral']['title'];
$tpl['site']['desc'] = $titulo;

$tpl['pagina']['titulo'] = $titulo;
$tpl['pagina']['nome'] = $nome;

// Template Output
// ----------------------------------------------------------------------------

include("inc/footer.php");

// echo "<pre>"; var_export($tpl); exit;

$output = $latte->renderToString($TemplatePath, $tpl);
$output = template_replace_path($output);

exit($output);