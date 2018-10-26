# Cálculo de frete dos Correios

Este projeto serve para o cálculo de frete dos Correios.

A API de cálculo de frete que os Correios disponibilizam retorna o valor de um pacote.

Isso pode ser perigoso, já que se somar os valores de itens da forma incorreta, pode tornar a compra inviável.

Por este motivo, foi desenvolvido este módulo que faz a cubagem dos itens, contando seu peso cúbico, volume e medidas, máximas e mínimas, segundo as especificações da empresa dos Correios.

## Considerações
Faixa de Isenção de Precificação Cúbica (tarifação exclusivamente peso real): **Todas postagens com peso cúbico até 10  kg**

#### Regras de dimensões
Limites de dimensões de embalagens:

* PACOTE E CAIXA

Especificações|Mínimo|Máximo
--------------|------|------
Comprimento (C)|16 cm|105 cm
Largura (L)|11 cm|105 cm
Altura (A)|2 cm|105 cm
Soma (C+L+A)|29 cm|200 cm
> A soma resultante do comprimento + largura + altura não deve superar 200 cm.
> A soma resultante do comprimento + o dobro do diâmetro não pode ser menor que 28 cm.


* ROLO

Especificações|Mínimo|Máximo
--------------|------|------
Comprimento (C)|18 cm|105 cm
Diâmetro (D)|5 cm|91 cm
Soma (C + 2D)|28 cm|200 cm

* ENVELOPE

Especificações|Mínimo|Máximo
--------------|------|------
Comprimento (C)|16 cm|60 cm
Largura (L)|11 cm|60 cm
Soma (C + L)|27 cm|120 cm

#### Peso cúbico
1. Medir as dimensões do objeto (comprimento, largura e altura), em centímetros.
2. Calcular o volume do objeto multiplicando o comprimento pela largura e pela altura, considerando a parte mais representativa de cada dimensão;
3. Dividir o produto da multiplicação pelo fator de cubagem dos Correios, que é 6000;
**O resultado será o peso cúbico do objeto.**

#### Peso bruto (balança)
1. Pesar o objeto para obter o peso bruto (balança).

## Como é obtido o preço da postagem

### Postagem Individual
O preço a ser cobrado corresponderá ao maior dos dois pesos (bruto ou cúbico).

**Um exemplo:**
Um objeto pesando 7,76 kg e medindo 45 cm de comprimento, 38 cm de largura e 40 cm de altura terá seu preço determinado da seguinte forma:

**1. Calcular o peso cúbico:**
    Volume = 45 x 38 x 40 = 68.400 cm3
    Peso cúbico = 68.400 / 6000 = 11,40, ou seja, 12kg

**2. Pesar o objeto**
    Peso real = 8 kg

**3. Será cobrado o maior dos dois pesos, ou seja, 12kg**

### Postagem Agrupada
Proceder a mesma regra para cálculo do peso cúbico, sendo que para postagem agrupada o preço do serviço terá como base o somatório dos maiores pesos verificados entre o peso bruto e o cúbico de cada objeto.
