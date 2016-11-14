<?php

/**
 * Cálculo de Frete
 *
 * Classe que realiza o cálculo do envio de produtos através dos Correios.
 *
 * @author Gabriel Prates <gabsprates@gmail.com>
 * @copyright Copyright (c) 2016 Gigapix Estúdio Multimídia LTDA - ME (http://gigapix.com.br)
 * @link https://github.com/gigapix/freteCorreios
 * @version 1.0.0
 */
class Frete
{
    // TODO: documentar constantes

    const ERR_DESCONHECIDO = -1;
    const ERR_CONSULTA_WEBSERVICE = 0;
    const ERR_XML_INVALIDO = 1;
    const ERR_SERVIDO_INDISPONIVEL = 7;

    /**
     * Identificação do serviço de entrega no sistema dos Correios
     *
     * @access private
     * @var string
     */
    private $servico;

    /**
     * CEP (Código de Endereçamento Postal) de origem do envio.
     * Este valor é salvo no formato de apenas números, sem o hífen entre o 5º e 6º número.
     *
     * @access private
     * @var string
     */
    private $CEPOrigem;

    /**
     * CEP (Código de Endereçamento Postal) de entrega dos produtos.
     * Este valor é salvo no formato de apenas números, sem o hífen entre o 5º e 6º número.
     *
     * @access private
     * @var string
     */
    private $CEPDestino;

    /**
     * Produtos que serão enviados no frete.
     *
     * @var array
     */
    private $produtos;

    /**
     * Indica se a encomenda será entregue com o serviço adicional mão própria.
     *
     * @access private
     * @var string
     */
    private $maoPropria = 'n';

    /**
     * Indica se a encomenda será entregue com o serviço adicional valor declarado.
     *
     * @access private
     * @var int
     */
    private $valorDeclarado = 0;

    /**
     * Indica se a encomenda será entregue com o serviço adicional aviso de recebimento.
     *
     * @access private
     * @var string
     */
    private $avisoRecebimento = 'n';

    /**
     * Valor final do frete.
     *
     * @access private
     * @var float
     */
    private $freteTotal = null;

    /**
     * Lista de chaves necessárias em cada produto informado para frete.
     *
     * @access private
     * @var array
     */
    private $chavesProdutos = [
        'qtd', // CHECK: poderia virar "quantidade", pra manter um padrão. Já temos comprimento que é um nome grande.
        'peso',
        'altura',
        'largura',
        'comprimento',
    ];

    /**
     * Construtor.
     *
     * @param string    $servico          Identificação do serviço de entrega no sistema dos Correios
     * @param string    $cepOrigem        CEP (Código de Endereçamento Postal) de origem do envio.
     * @param string    $cepDestino       CEP (Código de Endereçamento Postal) de entrega dos produtos.
     * @param array     $produtos         Produtos que serão enviados no frete.
     * @param bool      $maoPropria       Indica se a encomenda será entregue com o serviço adicional mão própria.
     * @param int|float $valorDeclarado   Indica se a encomenda será entregue com o serviço adicional valor declarado.
     *                                    Esse valor recebe 0 se não for utilizar o serviço, e o valor em reais se for
     *                                    utilizado.
     * @param bool      $avisoRecebimento Indica se a encomenda será entregue com o serviço adicional aviso de
     *                                    recebimento.
     */
    public function __construct(
        $servico,
        $cepOrigem,
        $cepDestino,
        $produtos,
        $maoPropria = false,
        $valorDeclarado = 0,
        $avisoRecebimento = false
    ) {
        // Todos os parâmetros são validados no construtor.
        if (is_string($servico) === false) {
            throw new InvalidArgumentException('A identificação do serviço dos Correios deve ser uma string.');
        }
        if ($this->validaCEP($cepOrigem) === false) {
            throw new InvalidArgumentException('O CEP de origem é inválido.');
        }
        if ($this->validaCEP($cepDestino) === false) {
            throw new InvalidArgumentException('O CEP de destino é inválido.');
        }
        if ($this->validaProdutos($produtos) === false) {
            throw new InvalidArgumentException('O array de produtos não possui o formato correto.');
        }
        if (is_bool($maoPropria) === false) {
            throw new InvalidArgumentException(
                'A identificação do uso do serviço adicional "mão própria" deve ser um valor booleano.'
            );
        }
        if (is_numeric($valorDeclarado) === false) {
            throw new InvalidArgumentException(
                'A identificação do uso do serviço adicional "valor declarado" deve ser um valor booleano.'
            );
        }
        if (is_bool($avisoRecebimento) === false) {
            throw new InvalidArgumentException(
                'A identificação do uso do serviço adicional "aviso de recebimento" deve ser um valor booleano.'
            );
        }

        $this->servico = $servico;
        $this->CEPOrigem = $this->formataCEP($cepOrigem);
        $this->CEPDestino = $this->formataCEP($cepDestino);
        $this->produtos = $produtos;
        $this->maoPropria = $this->formataValorBooleano($maoPropria);
        $this->valorDeclarado = $valorDeclarado;
        $this->avisoRecebimento = $this->formataValorBooleano($avisoRecebimento);
    }

    /**
     * Verifica se determinado CEP possui o formato correto.
     *
     * @access private
     * @param string $valor
     * @return bool
     */
    private function validaCEP($valor)
    {
        if (is_string($valor) === false) {
            return false;
        }
        return (bool) preg_match('#^[0-9]{5}-?[0-9]{3}$#', $valor);
    }

    /**
     * Verifica se o array de produtos possui o formato correto.
     *
     * @access private
     * @param array $produtos
     * @return bool
     */
    private function validaProdutos($produtos)
    {
        if (is_array($produtos) === false) {
            return false;
        }

        foreach ($produtos as $produto) {
            foreach ($this->chavesProdutos as $chaveNecessaria) {
                if (isset($produto[$chaveNecessaria]) === false) {
                    return false;
                }
            }

            // TODO: validar cada chave do array se possui o formato válido (medidas devem ser númericas e outros)
        }

        return true;
    }


    /**
     * Remove o hífen do valor do CEP, normalizando-o para uso no webservice dos Correios.
     *
     * @access private
     * @param $cep
     * @return string
     */
    private function formataCEP($cep)
    {
        return preg_replace('#[^0-9]#', '', $cep);
    }

    /**
     * Transforma valores booleanos em 's' e 'n' para uso no webservice dos Correios.
     *
     * @access private
     * @param $valor
     * @return string
     */
    private function formataValorBooleano($valor)
    {
        return ($valor) ? 's' : 'n';
    }

    /**
     * Formata um valor numérico de moeda brasileiro para um valor numérico do PHP.
     *
     * @access private
     * @param string $valor
     * @return float
     */
    public function formataValorEmReais($valor)
    {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        return (float) $valor;
    }

    /**
     * Obtém o valor final do frete.
     * Essa função retorna NULL se o frete ainda não foi calculado.
     *
     * @access public.
     * @return float
     */
    public function valorFinal()
    {
        return $this->freteTotal;
    }

    /**
     * Executa o cálculo do frete.
     *
     * O cálculo utiliza as medidas de volume e peso dos produtos para determinar o valor do frete,
     * aplicando a regra de transbordo sempre que necessário.
     *
     * @access public
     * @param  bool $retornaUrl Determina se deve ser retornado o valor de frete ou a URL de consulta nos Correios
     * @return float|string|array
     */
    public function calcular($retornaUrl = false)
    {
        if (is_bool($retornaUrl) === false) {
            throw new InvalidArgumentException(
                'A identificação do retorno da URL de consulta deve ser um valor booleano.'
            );
        }

        $pesoTotal = 0.00;
        $volumeTotal = 0;

        foreach ($this->produtos as $produto) {
            // CHECK: as normalizações dos valores mínimos poderiam ser feitas no construtor da classe
            $produto['altura'] = ($produto['altura'] < 2) ? 2 : $produto['altura'];
            $produto['largura'] = ($produto['largura'] < 11) ? 11 : $produto['largura'];
            $produto['comprimento'] = ($produto['comprimento'] < 16) ? 16 : $produto['comprimento'];

            $pesoTotal += $produto['peso'] * $produto['qtd'];
            $volumeTotal += ($produto['largura'] * $produto['altura'] * $produto['comprimento']) * $produto['qtd'];
        }

        $_medida = ceil(pow($volumeTotal, (1/3)));
        $medida = ($_medida < 16) ? 16 : $_medida;
        $pesoTotal = ($pesoTotal < 0.3) ? 0.3 : $pesoTotal;

        if (($medida > 66) or ($pesoTotal > 30)) {
            if ($pesoTotal > 30 && $medida < 67) {
                $somaFrete = $this->transbordoDePeso($retornaUrl, $pesoTotal, $medida);
            } elseif ($pesoTotal > 30  &&  $medida > 66) {
                $somaFrete = $this->transbordoDePesoEMedida($retornaUrl, $pesoTotal, $medida);
            }
            // FIXME: Se peso for menor que 30, e medida maior que 66, $somaFrete não é definida
        } else {
            $somaFrete = $this->obterValorDoFrete($retornaUrl, $pesoTotal, $medida, $medida, $medida);
        }

        $this->freteTotal = $somaFrete;
        return $this->valorFinal();
    }


    /**
     * Obtém o valor do frete através de uma consulta ao Webservice dos correios.
     * Essa função pode retornar a URL de consulta se desejar.
     *
     * @access private
     * @param bool $retornaUrl
     * @param float $peso
     * @param int $altura
     * @param int $largura
     * @param int $comprimento
     * @return float|string
     */
    private function obterValorDoFrete($retornaUrl, $peso, $altura, $largura, $comprimento)
    {
        $valores = array(
            'nCdEmpresa'            => "",
            'sDsSenha'              => "",
            'sCepOrigem'            => $this->CEPOrigem,
            'sCepDestino'           => $this->CEPDestino,
            'nVlPeso'               => $peso,
            'nCdFormato'            => 1,
            'nVlComprimento'        => $comprimento,
            'nVlAltura'             => $altura,
            'nVlLargura'            => $largura,
            'sCdMaoPropria'         => $this->maoPropria,
            'nVlValorDeclarado'     => $this->valorDeclarado,
            'sCdAvisoRecebimento'   => $this->avisoRecebimento,
            'nCdServico'            => $this->servico,
            'nVlDiametro'           => 0,
            'StrRetorno'            => 'xml',
            'nIndicaCalculo'        => 3
        );
        $webserviceUrl = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx';
        $queryString = http_build_query($valores);

        $url = "{$webserviceUrl}?{$queryString}";

        if ($retornaUrl) {
            return $url;
        }

        $cURL = curl_init();
        curl_setopt_array($cURL, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url
        ));
        $respostaWebservice = curl_exec($cURL);
        curl_close($cURL);

        if ($respostaWebservice === false) {
            throw new RuntimeException(
                'Não foi possível obter uma resposta do Webservice dos Correios',
                self::ERR_CONSULTA_WEBSERVICE
            );
        }

        $resposta = simplexml_load_string($respostaWebservice);
        if ($resposta === false) {
            throw new RuntimeException(
                'Não foi possível reconhecer o XML na resposta do Webservice dos Correios. '.
                'Talvez algum parâmetro enviado esteja inválido.',
                self::ERR_XML_INVALIDO
            );
        }

        $dadosFrete = $resposta->cServico;
        switch ((string) $dadosFrete->Erro) {
            case 0:
                return $this->formataValorEmReais((string) $dadosFrete->Valor);
            case 7:
                throw new RuntimeException('Serviço temporariamente indisponível.', self::ERR_SERVIDO_INDISPONIVEL);
            default:
                throw new RuntimeException(
                    ($dadosFrete->MsgErro && ((string) $dadosFrete->MsgErro))
                        ? $dadosFrete->MsgErro
                        : 'Não foi possível obter o valor do frete pelos Correios.',
                    self::ERR_DESCONHECIDO
                );
        }
    }

    // TODO: Formatar esta função
    private function transbordoDePeso($retornaUrl, $Peso, $Medida)
    {
        $consultas = array();

        $PESO_TEMP = $Peso;
        $Total = 0;
        do {
            if ($PESO_TEMP != $Peso) {
                // FIXME: A classe usa $Medida pra obter o valor, não $medida
                $medida = 16;
            } else {
                $medida = $Medida;
            }

            $Pesio  = intval($PESO_TEMP/30);
            array_push($consultas, $this->obterValorDoFrete($retornaUrl, ($Pesio*30), $Medida, $Medida, $Medida));
            $PESO_TEMP -= 30;

        } while ($PESO_TEMP>0);

        return ($retornaUrl) ? $consultas : array_sum($consultas);
    }

    // TODO: Formatar esta função
    private function transbordoDePesoEMedida($retornaUrl, $Peso, $Medida)
    {
        $consultas = array();

        $Total  = 0;

        $medida = $Medida;
        $peso   = $Peso;

        do {

            $medida = ($medida > 66) ? $medida-66 : $medida;
            $peso   = ($peso > 30) ? $peso-30 : $peso;
            $MED    = ( ($medida - ($medida-66)) > 16 ) ? $medida - ($medida-66) : $medida;
            $PES    = ($peso > 30) ? $peso - ($peso-30) : $peso;
            array_push($consultas, $this->obterValorDoFrete($retornaUrl, $PES, $MED, $MED, $MED));
        } while ($peso > 30 || $medida > 66);

        return ($retornaUrl) ? $consultas : array_sum($consultas);
    }
}
