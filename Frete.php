<?php

/**
 * Description of Frete
 *
 * @author Gabriel Prates <gabsprates@gmail.com>
 * @author Gigapix Estúdio Multimídia <contato@gigapix.com.br>
 *
 * @param servico:      Serviço dos correios
 * @param cepOrigem:    CEP Origem
 * @param cepDestino:   CEP Destino
 * @param produtos:
 *      Array de produtos com 'id' => array('id', 'peso', 'preco', 'altura', 'largura', 'comprimento')
 *
 */
class Frete {
    private $servico;
    private $cepOrigem;
    private $cepDestino;
    private $produtos;
    private $mostraURL;

    public $freteTotal = 0.00;

    function __construct($servico, $cepOrigem, $cepDestino, $produtos, $mostraURL = false) {

        $this->servico      = $servico;
        $this->cepOrigem    = $cepOrigem;
        $this->cepDestino   = $cepDestino;
        $this->produtos     = $produtos;
        $this->mostraURL    = $mostraURL;

    }


    public function getFrete () {
        return $this->freteTotal;
    }

    /*
     * Este é o método mais importante.
     * Ele faz o cálculo do frete com todos os produtos, aplicando a regra de
     * transbordo de caixa dos Correios, usando a médida máxima, volume máximo e
     * peso cúbico máximo.
     */
    public function calculaFrete() {

        $PESO_TOTAL     = 0.00;
        $VOLUME_TOTAL   = 0;
        $MEDIDA         = 0;

        foreach ($this->produtos as $PID => $PROD) :

            $QTD = $PROD['qtd'];

            if (!$PROD['altura'] || $PROD['altura'] < 2)
                $PROD['altura'] = 2;

            if (!$PROD['largura'] || $PROD['largura'] < 11)
                $PROD['largura'] = 11;

            if (!$PROD['comprimento'] || $PROD['comprimento'] < 16)
                $PROD['comprimento'] = 16;

            $PESO_TOTAL     += $PROD['peso'] * $QTD;
            $VOLUME_TOTAL   += ($PROD['largura'] * $PROD['altura'] * $PROD['comprimento']) * $QTD;

        endforeach;

        $MEDIDA     = ceil( pow($VOLUME_TOTAL, (1/3)));
        $MEDIDA     = ($MEDIDA < 16) ? 16 : $MEDIDA;
        $PESO_TOTAL = ($PESO_TOTAL < 0.3) ? 0.3 : $PESO_TOTAL;

        if ( ($MEDIDA > 66) || ($PESO_TOTAL > 30) ) {

            /// Preço ou Volume -> Maior que o excedente
            if ($PESO_TOTAL > 30  &&  $MEDIDA < 67) {

                // Peso excede, Medida não
                $somaFrete += $this->MPesoLMedida($PESO_TOTAL,$MEDIDA);

            } else if ($PESO_TOTAL > 30  &&  $MEDIDA > 66) {

                // Peso e Medida excedem
                $somaFrete += $this->PesoEMedida($PESO_TOTAL,$MEDIDA);

            }
        } else {

            /// Preço e Volume -> Menor que o excedente
            $somaFrete += $this->calculaFreteCaixa($PESO_TOTAL,$MEDIDA,$MEDIDA,$MEDIDA);

        }

        return $this->freteTotal = $somaFrete;
    }


    /*
     * Calcula frete de uma caixa
     */
    private function calculaFreteCaixa ($PESO, $ALTURA, $LARGURA, $COMPRIMENTO) {

        $sCepOrigem            = $this->cepOrigem;
        $sCepDestino           = $this->cepDestino;
        $nVlPeso               = $PESO;
        $nCdFormato            = 1;
        $nVlComprimento        = $COMPRIMENTO;
        $nVlAltura             = $ALTURA;
        $nVlLargura            = $LARGURA;
        $sCdMaoPropria         = 'n';
        $sCdMaoPropria         = (strtolower($sCdMaoPropria) == 's') ? 's':'n';
        $nVlValorDeclarado     = 0;
        $sCdAvisoRecebimento   = 'n';
        $sCdAvisoRecebimento   = (strtolower($sCdAvisoRecebimento) == 's') ? 's':'n';
        $nCdServico            = $this->servico;
        $nCdEmpresa            = "";
        $sDsSenha              = "" ;

        $url    = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?"
                . "nCdEmpresa={$nCdEmpresa}"
                . "&sDsSenha={$sDsSenha}"
                . "&sCepOrigem={$sCepOrigem}"
                . "&sCepDestino={$sCepDestino}"
                . "&nVlPeso={$nVlPeso}"
                . "&nCdFormato={$nCdFormato}"
                . "&nVlComprimento={$nVlComprimento}"
                . "&nVlAltura={$nVlAltura}"
                . "&nVlLargura={$nVlLargura}"
                . "&sCdMaoPropria={$sCdMaoPropria}"
                . "&nVlValorDeclarado={$nVlValorDeclarado}"
                . "&sCdAvisoRecebimento={$sCdAvisoRecebimento}"
                . "&nCdServico={$nCdServico}"
                . "&nVlDiametro=0&StrRetorno=xml&nIndicaCalculo=3";

        if ($this->mostraURL) {
            echo "<pre> {$url} </pre>";
        }

        $frete_calcula = simplexml_load_string(file_get_contents($url));

        $frete = $frete_calcula->cServico;

        if ($frete->Erro == '0') {

            $retorno = $frete->Valor;

        } elseif ($frete->Erro === '7') {

            $retorno = "Serviço temporariamente indisponível, tente novamente mais tarde.";

        } else {

            $retorno = "Seu frete não pôde ser calculado.<br />Entraremos em contato.";

        }

        return $retorno;
    }


    private function MPesoLMedida($Peso, $Medida) {

        $PESO_TEMP = $Peso;
        $Total = 0;
        do {
            if ($PESO_TEMP != $Peso) {
                $medida = 16;
            } else {
                $medida = $Medida;
            }

            $Pesio  = intval($PESO_TEMP/30);
            $Total += $this->calculaFreteCaixa(($Pesio*30), $Medida, $Medida, $Medida);
            $PESO_TEMP -= 30;

        } while ($PESO_TEMP>0);

        return $Total;
    }

    private function PesoEMedida($Peso, $Medida) {

        $Total  = 0;

        $medida = $Medida;
        $peso   = $Peso;

        do {

            $medida = ($medida > 66) ? $medida-66 : $medida;
            $peso   = ($peso > 30) ? $peso-30 : $peso;
            $MED    = ( ($medida - ($medida-66)) > 16 ) ? $medida - ($medida-66) : $medida;
            $PES    = ($peso > 30) ? $peso - ($peso-30) : $peso;
            $Total += $this->calculaFreteCaixa($PES, $MED, $MED, $MED);

        } while ($peso > 30 || $medida > 66);

        return $Total;
    }

}
