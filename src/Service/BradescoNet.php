<?php

namespace WilsonGlasser\PhpBoleto\Service;

use WilsonGlasser\PhpBoleto\Boleto\Banco\Bradesco;
use WilsonGlasser\PhpBoleto\Certificado;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use WilsonGlasser\PhpBoleto\Util;

class BradescoNet extends AbstractService implements ServiceContract
{
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
        return $this->boleto->getNossoNumero();
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
    private function getAgencia()
    {
        return $this->boleto->getAgencia();
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
        return $this->getAgencia().str_pad($this->getConta(), 14, "0", STR_PAD_LEFT);
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
            $arr->filialCPFCNPJ = $benefeciario->getTipoDocumento() === 'CNPJ' ? substr($cnpj, 8, 4) : null;
            $arr->ctrlCPFCNPJ = $benefeciario->getTipoDocumento() === 'CNPJ' ? substr($cnpj, 12, 2) : null;
            $arr->cdTipoAcesso = '2';
            $arr->clubBanco = '0';
            $arr->cdTipoContrato = '0';
            $arr->nuSequenciaContrato = '0';
            $arr->idProduto = (string)$this->getCarteira();
            $arr->nuNegociacao = $this->getNumeroNegociacao();
            $arr->cdBanco = '237';
            $arr->eNuSequenciaContrato = '0';
            $arr->tpRegistro = '1';
            $arr->cdProduto = '0';
            $arr->nuTitulo = (string)$this->getNossoNumero();
            $arr->nuCliente = (string)$this->getNossoNumero();
            $arr->dtEmissaoTitulo = $this->getEmissao()->format('d.m.Y');
            $arr->dtVencimentoTitulo = $this->getVencimento()->format('d.m.Y');
            $arr->tpVencimento = '0';
            $arr->vlNominalTitulo = (string)number_format($this->getValor(), 2, '', '');
            $arr->cdEspecieTitulo = '99';
            $arr->tpProtestoAutomaticoNegativacao = '0';
            $arr->prazoProtestoAutomaticoNegativacao = '0';
            $arr->controleParticipante = '';
            $arr->cdPagamentoParcial = '';
            $arr->qtdePagamentoParcial = '0';

            $arr->percentualDesconto1 = '0';
            $arr->vlDesconto1 = '0';
            $arr->dataLimiteDesconto1 = '';
            $arr->percentualDesconto2 = '0';
            $arr->vlDesconto2 = '0';
            $arr->dataLimiteDesconto2 = '';
            $arr->percentualDesconto3 = '0';
            $arr->vlDesconto3 = '0';
            $arr->dataLimiteDesconto3 = '';

            $arr->prazoBonificacao = '0';
            $arr->percentualBonificacao = '0';
            $arr->vlBonificacao = '0';
            $arr->dtLimiteBonificacao = '';
            $arr->vlAbatimento = '0';
            $arr->vlIOF = '0';


            
            $pagador = $this->boleto->getPagador();
            $arr->nomePagador = substr(Util::ascii($pagador->getNome()), 0, 40);
            $arr->logradouroPagador = substr(Util::ascii($pagador->getEndereco()), 0, 40);
            $arr->nuLogradouroPagador = (string)$pagador->getNumero();
            $arr->complementoLogradouroPagador = substr(Util::ascii($pagador->getComplemento()), 0, 15);
            $arr->cepPagador = substr($pagador->getCep(), 0, 5);
            $arr->complementoCepPagador = substr($pagador->getCep(), 6, 3);
            $arr->bairroPagador = substr(Util::ascii($pagador->getBairro()), 0, 40);
            $arr->municipioPagador = substr(Util::ascii($pagador->getCidade()), 0, 30);
            $arr->ufPagador = substr(Util::ascii($pagador->getUf()), 0, 2);
            $arr->cdIndCpfcnpjPagador = $pagador->getTipoDocumento() === 'CPF' ? '1' : '2';
            $arr->nuCpfcnpjPagador = Util::onlyNumbers($pagador->getDocumento());
            $arr->endEletronicoPagador = substr(Util::ascii($pagador->getEmail()), 0, 50);
            $arr->nomeSacadorAvalista = '';
            $arr->logradouroSacadorAvalista = '';
            $arr->nuLogradouroSacadorAvalista = '0';
            $arr->complementoLogradouroSacadorAvalista = '';
            $arr->cepSacadorAvalista = '0';
            $arr->complementoCepSacadorAvalista = '0';
            $arr->bairroSacadorAvalista = '';
            $arr->municipioSacadorAvalista = '';
            $arr->ufSacadorAvalista = '';
            $arr->cdIndCpfcnpjSacadorAvalista = '0';
            $arr->nuCpfcnpjSacadorAvalista = '0';
            $arr->endEletronicoSacadorAvalista = '';


            $multa = $this->getMulta();
            if ($multa > 0) {
                $interval_multa = abs($this->getVencimento()->diffInDays($$this->boleto->getDataMulta()));
                $arr->percentualMulta = str_pad(number_format($multa, 5, '', ''), 8, "0",
                    STR_PAD_LEFT);
                $arr->vlMulta = '0';
                $arr->qtdeDiasMulta = $interval_multa;
            } else {
                $arr->percentualMulta = '0';
                $arr->vlMulta = '0';
                $arr->qtdeDiasMulta = '0';
            }


            $juros = $this->getJuros();
            if ($juros > 0) {
                $interval_juros = date_diff($this->getVencimento(), $this->boleto->getDataJuros());

                $arr->percentualJuros = str_pad(number_format($juros, 5, '', ''), 8, "0", STR_PAD_LEFT);
                $arr->vlJuros = '0';
                $arr->qtdeDiasJuros = $interval_juros;

            } else {
                $arr->percentualJuros = '0';
                $arr->vlJuros = '0';
                $arr->qtdeDiasJuros = '0';
            }

            if ($this->getDesconto() > 0) {
                
                $desconto = $this->getDesconto();
        
                $arr->dataLimiteDesconto1 = $this->getVencimento()->format('d.m.Y');
                $arr->vlDesconto1 = str_pad(number_format($desconto, 5, '', ''), 8, "0",
                    STR_PAD_LEFT);

                
            }


            $json = json_encode($arr);

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
            echo $e->getMessage().PHP_EOL;;
        } catch (\Exception $e) {
            echo $e->getMessage().PHP_EOL;;
        }

    }
}