# Ativação Bolt

Painel próprio de ativação de dispositivo (usuário/senha + MAC), com visual
independente. O front-end chama a API de parceria do BoltPlay diretamente
pelo navegador — não precisa de servidor próprio.

## Estrutura

- `index.html` — front-end completo (duas etapas: credenciais do cliente →
  MAC do dispositivo). As credenciais da parceria (`codigo`/`token`) ficam
  no bloco `window.PARCERIA` no topo do script, nunca exibidas na tela,
  mensagens ou console.
- `.github/workflows/pages.yml` — publica o site no GitHub Pages a cada
  push em `main`.
- `backend-php-opcional/` — versão alternativa com backend em PHP
  (`ativar.php` + `config.php`), caso em algum momento seja necessário um
  proxy de servidor (por exemplo, se a API de parceria passar a exigir
  que a chamada venha do domínio dela, por CORS). Não é usada no deploy
  do GitHub Pages.

## Publicando no GitHub Pages

1. Em **Settings → Pages**, defina **Source: GitHub Actions**.
2. Dê push na branch `main` — o workflow builda e publica o site.
3. O site fica disponível em `https://SEU-USUARIO.github.io/ativacaobolt/`
   (ou no domínio customizado configurado em Settings → Pages).

## Rodando localmente

Basta abrir `index.html` no navegador, ou servir a pasta com qualquer
servidor estático:

```bash
python3 -m http.server 8000
```

## Observação sobre segurança

O `codigo`/`token` da parceria ficam no código-fonte do `index.html` e
**não são um segredo criptográfico** — qualquer pessoa com acesso ao
arquivo (ou que inspecione a página) consegue lê-los. Eles só ficam fora
da interface visível (tela, mensagens, histórico de ações do usuário).
Se em algum momento for necessário impedir que esse valor seja lido por
qualquer visitante (não só escondido da UI), use a versão em
`backend-php-opcional/`, hospedada em um servidor com PHP, que mantém o
token fora do que é enviado ao navegador.
