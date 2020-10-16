<?php


namespace WilsonGlasser\PhpBoleto\Service;
/**
 * Class AbstractBoleto
 *
 * @package WilsonGlasser\PhpBoleto\Boleto
 */
abstract class AbstractService {

    public function ascii($string)
    {
        return preg_replace('/[`^~\'"]/', null, str_replace('?', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string)));
    }
}