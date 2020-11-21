<?php
require 'autoload.php';
$beneficiario = new \WilsonGlasser\PhpBoleto\Pessoa(
    [
    'nome' => 'ACME',
    'endereco' => 'Rua um, 123',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '99.999.999/9999-99',
    ]
);

$pagador = new \WilsonGlasser\PhpBoleto\Pessoa(
    [
    'nome' => 'Cliente',
    'endereco' => 'Rua um, 123',
    'bairro' => 'Bairro',
    'cep' => '99999-999',
    'uf' => 'UF',
    'cidade' => 'CIDADE',
    'documento' => '999.999.999-99',
    ]
);

$boleto = new WilsonGlasser\PhpBoleto\Boleto\Banco\Bradesco(
    [
    'logo' => realpath(__DIR__ . '/../logos/') . DIRECTORY_SEPARATOR . '237.png',
    'dataVencimento' => new \Carbon\Carbon(),
    'valor' => 100,
    'multa' => false,
    'juros' => false,
    'numero' => 1,
    'numeroDocumento' => 1,
    'pagador' => $pagador,
    'beneficiario' => $beneficiario,
    'carteira' => '09',
    'agencia' => 1111,
    'conta' => 9999999,
    'descricaoDemonstrativo' => ['demonstrativo 1', 'demonstrativo 2', 'demonstrativo 3'],
    'instrucoes' =>  ['instrucao 1', 'instrucao 2', 'instrucao 3'],
    'aceite' => 'S',
    'especieDoc' => 'DM'
    ]
);

// CERTIFICADO A1 da empresa
$certificado = new WilsonGlasser\PhpBoleto\Certificado('certs/nome_arquivo.pfx', '1234');

// 3º parametro true, para homologação
$service = new WilsonGlasser\PhpBoleto\Service\BradescoNet($boleto, $certificado, true);
