<?php
require 'autoload.php';
$retorno = \Wilsonglasser\PhpBoleto\Cnab\Retorno\Factory::make(__DIR__ . DIRECTORY_SEPARATOR . 'arquivos' . DIRECTORY_SEPARATOR . 'hsbc.ret');
$retorno->processar();

echo $retorno->getBancoNome();
dd($retorno->getDetalhes());
