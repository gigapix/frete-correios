# Cálculo de frete dos Correios

Este projeto serve para o cálculo de frete dos Correios.

A API de cálculo de frete que os Correios disponibilizam faz a soma de itens.

```
> Item 1
Peso: 1kg
Altura: 15cm
Largura: 25cm
Comprimento: 30cm
Quantidade: 2


> Item 2
Peso: 0.5kg
Altura: 10cm
Largura: 5cm
Comprimento: 20cm
Quantidade: 5
```


Isso pode ser perigoso, já que se somar os valores de itens da forma incorreta, pode tornar a compra inviável.

Por este motivo, nós desenvolvemos este módulo que faz a cubagem dos itens, contando seu peso cúbico, volume e medidas, máximas e mínimas, segundo as especificações da empresa dos Correios.
