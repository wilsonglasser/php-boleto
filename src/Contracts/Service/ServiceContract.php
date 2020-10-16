<?php

namespace WilsonGlasser\PhpBoleto\Contracts\Service;


interface ServiceContract
{
    public function getVencimento();
    public function getCarteira();
    public function getValor();
    public function getNossoNumero();
    public function getLinhaDigitavel();
    public function getCodigoBarras();
}