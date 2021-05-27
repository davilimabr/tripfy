# Tripfy
![alt text](apresentacao.gif)
## Indice
- [Sobre](#-sobre)
- [Como funciona](#-como-funciona)
- [Tecnologias](#-tecnologias)
- [Considerações](#-considerações)
## 🔖 Sobre
Tripfy é uma aplicação web que cria uma playlist no Spotify com o mesmo tempo de duração que uma viagem entre dois endereços escolhidos pelo usuário.
## 🛠 Como funciona
A aplicação recebe como entrada dois endereços, que representam a saída e chegada do trajeto do usuário. Com isso em mãos, consome a **API do Bing Maps** para obter o tempo de duração do trajeto. Por fim, faz uso da **API do Spotify** para obter as musicas favoritas do usuário (é preciso realizar um login antes) e depois cria uma playlist na conta Spotify vinculada com o tempo da duração do trajeto, com uma margem de erro de 1 minuto.  
## 🚀 Tecnologias
- [Slim](https://www.slimframework.com);
- [Phpdotenv](https://github.com/vlucas/phpdotenv);
- [jQuery](https://jquery.com);
- [Ajax](https://developer.mozilla.org/pt-BR/docs/Web/Guide/AJAX);
- [Bootstrap](https://getbootstrap.com).
## 📃 Considerações
Com essa aplicação aprendi sobre consumo de APIs, JSON, Requisições assíncronas usando Ajax, bem como sua utilização com o jQuey.