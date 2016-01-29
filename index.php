<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Cálculo de Teste</title>
<meta name=viewport content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="http://getbootstrap.com/dist/css/bootstrap.min.css">
</head>
<body>

<?php

$html = "";

if (isset($_GET['frete'])) {

    $servico    = $_POST['tipo'];
    $cepOrigem  = $_POST['cep-origem'];
    $cepDestino = $_POST['cep-destino'];
    $produtos   = $_POST['produto'];

    require_once 'Frete.php';

    $frete = new Frete($servico, $cepOrigem, $cepDestino, $produtos);

    $freteTotal = $frete->calculaFrete();

    $html = "<p class='container text-center alert alert-info'> Frete total = "
            ."<strong> R$ "
                . number_format($freteTotal, 2, ',', '')
            ."</strong> "
        ."</p>";


}

?>

<div class="container">
    <h1>Cálculo de frete</h1>

    <div class="row">
        <?= $html; ?>
    </div>

    <form action="./?frete" method="post">
        <div class="row">
            <div class="col-sm-6 form-group">
                <label>CEP Origem</label>
                <input name="cep-origem" class="form-control" required>
            </div>
            <div class="col-sm-6 form-group">
                <label>CEP Destino</label>
                <input name="cep-destino" class="form-control" required>
            </div>
        </div>
        <div id="campos" data-campos="1">
            <div class="row">
                <div class="container">
                    <label>Produto 1</label>
                </div>

                <div class="col-sm-1 form-group">
                    <label>Quantidade</label>
                    <input name="produto[1][qtd]" class="form-control" type="number" required />
                </div>
                <div class="col-sm-2 form-group">
                    <label>Peso (kg)</label>
                    <input name="produto[1][peso]" class="form-control" required />
                </div>
                <div class="col-sm-3 form-group">
                    <label>Altura (cm)</label>
                    <input name="produto[1][altura]" class="form-control" type="number" required />
                </div>
                <div class="col-sm-3 form-group">
                    <label>Largura (cm)</label>
                    <input name="produto[1][largura]" class="form-control" type="number" required />
                </div>
                <div class="col-sm-3 form-group">
                    <label>Comprimento (cm)</label>
                    <input name="produto[1][comprimento]" class="form-control" type="number" required />
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <h3>Selecione o tipo de envio</h3>
            </div>
            <div class="col-sm-12 radio">
                <label>
                    <input type="radio" name="tipo" value="41106" checked>
                    PAC
                </label>
            </div>
            <div class="col-sm-12 radio">
                <label>
                    <input type="radio" name="tipo" value="40010">
                    SEDEX
                </label>
            </div>
            <div class="col-sm-12 radio">
                <label>
                    <input type="radio" name="tipo" value="40045">
                    SEDEX a Cobrar
                </label>
            </div>
            <div class="col-sm-12 radio">
                <label>
                    <input type="radio" name="tipo" value="40215">
                    SEDEX 10
                </label>
            </div>
        </div>

        <button
            type="submit"
            class="pull-left btn btn-success">
            CALCULAR FRETE
        </button>
        <button
            type="button"
            onclick="novoProduto();"
            class="pull-right btn btn-primary">
            ADICIONAR PRODUTO
        </button>
    </form>
</div>

<script type="text/javascript">

var div = document.getElementById("campos");

function novoProduto () {
    var html        = div.innerHTML;
    var qtdCampos   = parseInt(div.getAttribute("data-campos")) + 1;

    div.setAttribute("data-campos", qtdCampos);

    div.innerHTML  += "<div class='row'>"
        + "<div class='container'>"
            + "<label>Produto " + qtdCampos + "</label>"
        + "</div>"
        + "<div class='col-sm-1 form-group'>"
            + "<label>Quantidade</label>"
            + "<input name='produto[" + qtdCampos + "][qtd]' class='form-control' type='number' required />"
        + "</div>"
        + "<div class='col-sm-2 form-group'>"
            + "<label>Peso (kg)</label>"
            + "<input name='produto[" + qtdCampos + "][peso]' class='form-control' required />"
        + "</div>"
        + "<div class='col-sm-3 form-group'>"
            + "<label>Altura (cm)</label>"
            + "<input name='produto[" + qtdCampos + "][altura]' class='form-control' type='number' required />"
        + "</div>"
        + "<div class='col-sm-3 form-group'>"
            + "<label>Largura (cm)</label>"
            + "<input name='produto[" + qtdCampos + "][largura]' class='form-control' type='number' required />"
        + "</div>"
        + "<div class='col-sm-3 form-group'>"
            + "<label>Comprimento (cm)</label>"
            + "<input name='produto[" + qtdCampos + "][comprimento]' class='form-control' type='number' required />"
        + "</div>"
    + "</div>";

}

</script>
</body>
</html>
