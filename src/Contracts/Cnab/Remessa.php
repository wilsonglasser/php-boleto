<?php
namespace Wilsonglasser\PhpBoleto\Contracts\Cnab;

interface Remessa extends Cnab
{
    public function gerar();
}
