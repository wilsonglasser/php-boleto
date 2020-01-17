<?php
namespace WilsonGlasser\PhpBoleto\Contracts\Cnab;

interface Remessa extends Cnab
{
    public function gerar();
}
