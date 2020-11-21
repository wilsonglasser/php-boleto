<?php

namespace WilsonGlasser\PhpBoleto\Service;

use WilsonGlasser\PhpBoleto\Contracts\Service\ServiceContract;
use WilsonGlasser\PhpBoleto\Boleto\Banco\Bradesco;
use WilsonGlasser\PhpBoleto\Certificado;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use WilsonGlasser\PhpBoleto\Util;

class BradescoNet extends AbstractService implements ServiceContract
{
    protected static $defaultBankSlip = [
        // const values
        "cdBanco" => "237",
        "cdTipoAcesso" => "2",
        "tpRegistro" => "1",
        "cdTipoContrato" => "48",
        "clubBanco" => "2269651",
        "tpVencimento" => "0",

        // empty values
        "nuSequenciaContrato" => "0",
        "eNuSequenciaContrato" => "0",
        "cdProduto" => "0",
        "nuTitulo" => "0",
        "tpProtestoAutomaticoNegativacao" => "0",
        "prazoProtestoAutomaticoNegativacao" => "0",
        "controleParticipante" => "",
        "cdPagamentoParcial" => "",
        "qtdePagamentoParcial" => "0",
        "percentualJuros" => "0",
        "vlJuros" => "0",
        "qtdeDiasJuros" => "0",
        "percentualMulta" => "0",
        "vlMulta" => "0",
        "qtdeDiasMulta" => "0",
        "percentualDesconto1" => "0",
        "vlDesconto1" => "0",
        "dataLimiteDesconto1" => "",
        "percentualDesconto2" => "0",
        "vlDesconto2" => "0",
        "dataLimiteDesconto2" => "",
        "percentualDesconto3" => "0",
        "vlDesconto3" => "0",
        "dataLimiteDesconto3" => "",
        "prazoBonificacao" => "0",
        "percentualBonificacao" => "0",
        "vlBonificacao" => "0",
        "dtLimiteBonificacao" => "",
        "vlAbatimento" => "0",
        "vlIOF" => "0",
        "endEletronicoPagador" => "",
        "nomeSacadorAvalista" => "",
        "logradouroSacadorAvalista" => "",
        "nuLogradouroSacadorAvalista" => "0",
        "complementoLogradouroSacadorAvalista" => '',
        "cepSacadorAvalista" => "0",
        "complementoCepSacadorAvalista" => "0",
        "bairroSacadorAvalista" => "",
        "municipioSacadorAvalista" => "",
        "ufSacadorAvalista" => "",
        "cdIndCpfcnpjSacadorAvalista" => "0",
        "nuCpfcnpjSacadorAvalista" => "0",
        "endEletronicoSacadorAvalista" => ""
    ];

    protected static $textFields = [
        'nomePagador',
        'logradouroPagador',
        'complementoLogradouroPagador',
        'bairroPagador',
        'municipioPagador',
        'ufPagador',
        'nomeSacadorAvalista',
        'logradouroSacadorAvalista',
        'complementoLogradouroSacadorAvalista',
        'bairroSacadorAvalista',
        'municipioSacadorAvalista',
        'ufSacadorAvalista'
    ];

    protected static $clipTextields = [
        ['nuCliente', 10],
        ['controleParticipante', 25],
        ['nomePagador', 70],
        ['logradouroPagador', 40],
        ['complementoLogradouroPagador', 15],
        ['bairroPagador', 40],
        ['municipioPagador', 30],
        ['ufPagador', 2],
        ['nomeSacadorAvalista', 40],
        ['logradouroSacadorAvalista', 40],
        ['complementoLogradouroSacadorAvalista', 15],
        ['bairroSacadorAvalista', 40],
        ['municipioSacadorAvalista', 40],
        ['ufSacadorAvalista', 2]
    ];

    private $homologacao;
    private $boleto;


    /**
     * BradescoNet constructor.
     * @param  Bradesco  $boleto
     * @param  Certificado  $certificado
     * @param  boolean  $homologacao
     */
    public function __construct(Bradesco $boleto, Certificado $certificado = null, $homologacao = false)
    {

        $this->boleto = $boleto;
        $this->certificado = $certificado;
        $this->homologacao = $homologacao;
    }

    /**
     * @param  boolean  $homologacao
     * @return BradescoNet
     */
    public function setHomologacao($homologacao)
    {
        $this->homologacao = $homologacao;
        return $this;
    }

    /**
     * @param  Certificado  $certificado  Certificado
     * @return BradescoNet
     */
    public function setCertificado(Certificado $certificado)
    {
        $this->certificado = $certificado;
        return $this;
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getEmissao()
    {
        return $this->boleto->getDataDocumento();
    }

    /**
     * @return \Carbon\Carbon
     */
    public function getVencimento()
    {
        return $this->boleto->getDataVencimento();
    }

    /**
     * @return string
     */
    public function getCarteira()
    {
        return str_pad($this->boleto->getCarteira(), 2, "0", STR_PAD_LEFT);
    }

    /**
     * @return float
     */
    public function getValor()
    {
        return (float) $this->boleto->getValor();
    }

    /**
     * @return string
     */
    public function getNossoNumero()
    {
        return ltrim($this->boleto->getNumero(), '0');
    }

    /**
     * @return string
     */
    public function getLinhaDigitavel()
    {
        return $this->boleto->getLinhaDigitavel();
    }

    /**
     * @return string
     */
    public function getCodigoBarras()
    {
        return $this->boleto->getCodigoBarras();
    }

    /**
     * @return string
     */
    private function getAgencia($semDigito = false)
    {
        $agencia = $this->boleto->getAgencia();
        if ($semDigito)  {
            list($agencia, $digito) = explode('-', $agencia);
        }
        
        return $agencia;
    }

    /**
     * @return string
     */
    private function getConta()
    {
        return $this->boleto->getConta();
    }

    /**
     * @return float Juros
     */
    public function getJuros()
    {
        return $this->boleto->getJuros();
    }

    /**
     * @return float
     */
    public function getMulta()
    {
        return $this->boleto->getMulta();
    }

    /**
     * @return float
     */
    public function getDesconto()
    {
        return (float) $this->boleto->getDesconto();
    }

    /**
     * @return Certificado
     */
    private function getCertificado()
    {
        return $this->certificado;
    }

    /**
     * @return string
     */
    private function getNumeroNegociacao()
    {
        return str_pad(Util::onlyNumbers($this->getAgencia(true)), 4, "0", STR_PAD_LEFT).str_pad(Util::onlyNumbers($this->getConta()), 14, "0", STR_PAD_LEFT);
    }

    /**
     * Retorna a entidade beneficiario
     *
     * @return PessoaContract
     */
    private function getBeneficiario() {
        return $this->boleto->getBeneficiario();
    }

    /**
     * Retorna a entidade pagador
     *
     * @return PessoaContract
     */
    private function getPagador() {
        return $this->boleto->getPagador();
    }


    public function send()
    {

        try {

            $arr = new \stdClass();
            
            $benefeciario = $this->boleto->getBeneficiario();
            $cnpj = Util::onlyNumbers($benefeciario->getDocumento());

            $arr->nuCPFCNPJ = $benefeciario->getTipoDocumento() === 'CNPJ' ? substr($cnpj, 0, 8) : null;
            $arr->filialCPFCNPJ = $benefeciario->getTipoDocumento() === 'CNPJ' ? substr($cnpj, 8, 4) : 0;
            $arr->ctrlCPFCNPJ = $benefeciario->getTipoDocumento() === 'CNPJ' ? substr($cnpj, 12, 2) : null;
            $arr->cdTipoAcesso = '2';
            $arr->idProduto = (string) $this->getCarteira();
            $arr->nuNegociacao = $this->getNumeroNegociacao();
            $arr->cdBanco = '237';
            $arr->tpRegistro = '1';

            $arr->nuTitulo = str_pad((string) $this->getNossoNumero(), 11, '0', STR_PAD_LEFT);
            $arr->nuCliente = (string)$this->getNossoNumero();
            $arr->dtEmissaoTitulo = $this->getEmissao()->format('d.m.Y');
            $arr->dtVencimentoTitulo = $this->getVencimento()->format('d.m.Y');
            $arr->vlNominalTitulo = (string)number_format($this->getValor(), 2, '', '');
            $arr->cdEspecieTitulo = '02';
            
            $pagador = $this->boleto->getPagador();
            $arr->nomePagador = substr(Util::normalizeChars($pagador->getNome()), 0, 70);
            $arr->logradouroPagador = substr(Util::normalizeChars($pagador->getEndereco()), 0, 40);
            $arr->nuLogradouroPagador = (string) substr($pagador->getNumero(), 0, 10);
            if (!empty($pagador->getComplemento()))
                $arr->complementoLogradouroPagador = (string) substr(Util::normalizeChars($pagador->getComplemento()), 0, 15);
            else
                $arr->complementoLogradouroPagador = null;

            $arr->cepPagador = substr($pagador->getCep(), 0, 5);
            $arr->complementoCepPagador = substr($pagador->getCep(), 6, 3);
            $arr->bairroPagador = substr(Util::normalizeChars($pagador->getBairro()), 0, 40);
            $arr->municipioPagador = substr(Util::normalizeChars($pagador->getCidade()), 0, 30);
            $arr->ufPagador = substr(Util::normalizeChars($pagador->getUf()), 0, 2);
            $arr->cdIndCpfcnpjPagador = $pagador->getTipoDocumento() === 'CPF' ? '1' : '2';
            $arr->nuCpfcnpjPagador = str_pad(Util::onlyNumbers($pagador->getDocumento()), 14, '0', STR_PAD_LEFT);

            $multa = $this->getMulta();
            if ($multa > 0) {
                $interval_multa = abs($this->getVencimento()->diffInDays($$this->boleto->getDataMulta()));
                $arr->percentualMulta = str_pad(number_format($multa, 5, '', ''), 8, "0",
                    STR_PAD_LEFT);
                $arr->vlMulta = '0';
                $arr->qtdeDiasMulta = $interval_multa;
            }


            $juros = $this->getJuros();
            if ($juros > 0) {                
                $interval_juros = abs($this->getVencimento()->diffInDays($$this->boleto->getDataJuros()));

                $arr->percentualJuros = str_pad(number_format($juros, 5, '', ''), 8, "0", STR_PAD_LEFT);
                $arr->vlJuros = '0';
                $arr->qtdeDiasJuros = $interval_juros;

            }

            if ($this->getDesconto() > 0) {
                
                $desconto = $this->getDesconto();
        
                $arr->dataLimiteDesconto1 = $this->getVencimento()->format('d.m.Y');
                $arr->vlDesconto1 = str_pad(number_format($desconto, 5, '', ''), 8, "0",
                    STR_PAD_LEFT);

                
            }

            $json = json_encode(self::fixAll($arr));
			
            $base64 = $this->certificado->signText($json);

            if ($this->homologacao) {
                $url = 'https://cobranca.bradesconetempresa.b.br/ibpjregistrotitulows/registrotitulohomologacao';
            } else {
                $url = 'https://cobranca.bradesconetempresa.b.br/ibpjregistrotitulows/registrotitulo';
            }
			
            $client = new Client(['verify' => false]);
            $res = $client->request('POST',
                $url, [
                    'body' => $base64
                ]);

            if ($res->getStatusCode() === 200) {
                $retorno = $res->getBody()->getContents();

                $doc = new \DOMDocument();
                $doc->loadXML($retorno);
                $retorno = $doc->getElementsByTagName('return')->item(0)->nodeValue;
                
                
                $retorno = preg_replace('/, }/i', '}', $retorno);
                $retorno = json_decode($retorno);
                if (!empty($retorno->cdErro)) {
                    throw new \Exception($retorno->cdErro, trim($retorno->msgErro));
                }

                return $retorno;
            }

        } catch (\RequestException $e) {
            echo $e->getMessage().PHP_EOL;
        } catch (\Exception $e) {
            echo $e->getMessage().PHP_EOL;
        }

    }

    public static function mergeWithDefaultData(array &$data)
    {
        $data = array_merge(static::$defaultBankSlip, $data);
    }

    public static function formatData(array &$data)
    {
        foreach(static::$textFields as $field) {
            if (!isset($data[$field]) && !empty($data[$field])) 
                continue;
            $data[$field] = preg_replace("/[^0-9a-z \-]/i", '', $data[$field]);
        }

        foreach(static::$clipTextields as $rule => $clip) {
            if (!isset($data[$field]) || strlen($data[$field]) <= $clip) 
                continue;
            $data[$field] = substr($data[$field], 0, $clip);
        }
    }

    public static function changeNullToEmpty(array &$data)
    {
        array_walk($data, function(&$item, $key) {
            if ($item === null) {
                $item = "";
            }
        });
    }

    public static function changeNumericToString(array &$data)
    {
        array_walk($data, function(&$item, $key) {
            if (is_float($item) || is_int($item)) {
                $item = (string) $item;
            }
        });
    }

    public static function setCustomerType(array &$data)
    {
        if (!isset($data['cdIndCpfcnpjPagador']))
            return;

        if (!isset($data['nuCpfcnpjPagador'])) 
            return;

        $data['cdIndCpfcnpjPagador'] = substr($data['nuCpfcnpjPagador'], 0, 3) === '000' ? "1" : "2";
    }

    public static function fixAll($data)
    {
        $data = (array) $data;
        // Per Bradesco API specs, all non-used fields must be
        // sent anyways but with their default values (0 or "")
        static::mergeWithDefaultData($data);

        // Format currency, date, text  and "CPF/CNPJ" fields to API specs
        static::formatData($data);

        // Bradesco API does not accept null, only empty
        static::changeNullToEmpty($data);

        // Bradesco API does not accept integer or float, only string
        static::changeNumericToString($data);

        // Automatically fill "cdIndCpfcnpjPagador" field
        static::setCustomerType($data);
        return $data;
    }
}