<?php
require 'autoload.php';

$retorno = \WilsonGlasser\PhpBoleto\Cnab\Retorno\Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'banrisul.ret');
$retorno->processar();

echo $retorno->getBancoNome();
echo '<pre>',print_r($retorno->getDetalhes());
