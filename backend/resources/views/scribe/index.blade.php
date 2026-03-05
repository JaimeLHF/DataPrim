<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Plataforma de Inteligência de Compras — API</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
        href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
        body .content .bash-example code {
            display: none;
        }

        body .content .javascript-example code {
            display: none;
        }

        body .content .php-example code {
            display: none;
        }

        body .content .python-example code {
            display: none;
        }
    </style>

    <script>
        var tryItOutBaseUrl = "http://localhost:8000";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.8.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.8.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;,&quot;php&quot;,&quot;python&quot;]">

    <a href="#" id="nav-button">
        <span>
            MENU
            <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image" />
        </span>
    </a>
    <div class="tocify-wrapper">

        <div class="lang-selector">
            <button type="button" class="lang-button" data-language-name="bash">bash</button>
            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
            <button type="button" class="lang-button" data-language-name="php">php</button>
            <button type="button" class="lang-button" data-language-name="python">python</button>
        </div>

        <div class="search">
            <input type="text" class="search" id="input-search" placeholder="Search">
        </div>

        <div id="toc">
            <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
            </ul>
            <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
            </ul>
            <ul id="tocify-header-autenticacao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="autenticacao">
                    <a href="#autenticacao">Autenticação</a>
                </li>
                <ul id="tocify-subheader-autenticacao" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-login">
                        <a href="#autenticacao-POSTapi-v1-auth-login">Login</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="autenticacao-POSTapi-v1-auth-logout">
                        <a href="#autenticacao-POSTapi-v1-auth-logout">Logout</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="autenticacao-GETapi-v1-auth-me">
                        <a href="#autenticacao-GETapi-v1-auth-me">Usuário autenticado</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-empresas" class="tocify-header">
                <li class="tocify-item level-1" data-unique="empresas">
                    <a href="#empresas">Empresas</a>
                </li>
                <ul id="tocify-subheader-empresas" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="empresas-GETapi-v1-companies">
                        <a href="#empresas-GETapi-v1-companies">Listar empresas</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-importacao-de-notas-fiscais" class="tocify-header">
                <li class="tocify-item level-1" data-unique="importacao-de-notas-fiscais">
                    <a href="#importacao-de-notas-fiscais">Importação de Notas Fiscais</a>
                </li>
                <ul id="tocify-subheader-importacao-de-notas-fiscais" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="importacao-de-notas-fiscais-POSTapi-v1-invoices-preview-xml">
                        <a href="#importacao-de-notas-fiscais-POSTapi-v1-invoices-preview-xml">Preview XML</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="importacao-de-notas-fiscais-POSTapi-v1-invoices-import-xml">
                        <a href="#importacao-de-notas-fiscais-POSTapi-v1-invoices-import-xml">Importar XML</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="importacao-de-notas-fiscais-POSTapi-v1-invoices-import-json">
                        <a href="#importacao-de-notas-fiscais-POSTapi-v1-invoices-import-json">Importar JSON (API Push)</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-notas-fiscais" class="tocify-header">
                <li class="tocify-item level-1" data-unique="notas-fiscais">
                    <a href="#notas-fiscais">Notas Fiscais</a>
                </li>
                <ul id="tocify-subheader-notas-fiscais" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="notas-fiscais-GETapi-v1-invoices">
                        <a href="#notas-fiscais-GETapi-v1-invoices">Listar notas fiscais</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="notas-fiscais-GETapi-v1-invoices--id-">
                        <a href="#notas-fiscais-GETapi-v1-invoices--id-">Detalhe de nota fiscal</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="notas-fiscais-DELETEapi-v1-invoices--id-">
                        <a href="#notas-fiscais-DELETEapi-v1-invoices--id-">Excluir nota fiscal</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-dashboard-analises" class="tocify-header">
                <li class="tocify-item level-1" data-unique="dashboard-analises">
                    <a href="#dashboard-analises">Dashboard & Análises</a>
                </li>
                <ul id="tocify-subheader-dashboard-analises" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-kpis">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-kpis">KPIs principais</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-tco-breakdown">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-tco-breakdown">Composição do TCO</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-gross-vs-net">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-gross-vs-net">Custo bruto vs líquido</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-price-evolution">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-price-evolution">Evolução de preços</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-price-index">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-price-index">Índice de preços</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-dispersion">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-dispersion">Dispersão de preços</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-freight-impact">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-freight-impact">Impacto do frete</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="dashboard-analises-GETapi-v1-dashboard-category-ranking">
                        <a href="#dashboard-analises-GETapi-v1-dashboard-category-ranking">Ranking de categorias</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-saving-cost-avoidance" class="tocify-header">
                <li class="tocify-item level-1" data-unique="saving-cost-avoidance">
                    <a href="#saving-cost-avoidance">Saving & Cost Avoidance</a>
                </li>
                <ul id="tocify-subheader-saving-cost-avoidance" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="saving-cost-avoidance-GETapi-v1-dashboard-saving">
                        <a href="#saving-cost-avoidance-GETapi-v1-dashboard-saving">Saving por categoria</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-benchmark-de-estrutura-de-custo" class="tocify-header">
                <li class="tocify-item level-1" data-unique="benchmark-de-estrutura-de-custo">
                    <a href="#benchmark-de-estrutura-de-custo">Benchmark de Estrutura de Custo</a>
                </li>
                <ul id="tocify-subheader-benchmark-de-estrutura-de-custo" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark">
                        <a href="#benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark">Benchmark por categoria</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark-periods">
                        <a href="#benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark-periods">Períodos disponíveis</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark-category-products">
                        <a href="#benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark-category-products">Produtos por categoria</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-fornecedores" class="tocify-header">
                <li class="tocify-item level-1" data-unique="fornecedores">
                    <a href="#fornecedores">Fornecedores</a>
                </li>
                <ul id="tocify-subheader-fornecedores" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="fornecedores-GETapi-v1-suppliers">
                        <a href="#fornecedores-GETapi-v1-suppliers">Ranking de fornecedores</a>
                    </li>
                    <li class="tocify-item level-2" data-unique="fornecedores-GETapi-v1-suppliers--id-">
                        <a href="#fornecedores-GETapi-v1-suppliers--id-">Detalhe do fornecedor</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-contatos" class="tocify-header">
                <li class="tocify-item level-1" data-unique="contatos">
                    <a href="#contatos">Contatos</a>
                </li>
                <ul id="tocify-subheader-contatos" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="contatos-GETapi-v1-contacts">
                        <a href="#contatos-GETapi-v1-contacts">Listar contatos</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-alertas" class="tocify-header">
                <li class="tocify-item level-1" data-unique="alertas">
                    <a href="#alertas">Alertas</a>
                </li>
                <ul id="tocify-subheader-alertas" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="alertas-GETapi-v1-alerts">
                        <a href="#alertas-GETapi-v1-alerts">Listar alertas</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-status-de-ingestao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="status-de-ingestao">
                    <a href="#status-de-ingestao">Status de Ingestão</a>
                </li>
                <ul id="tocify-subheader-status-de-ingestao" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="status-de-ingestao-GETapi-v1-ingestions--id--status">
                        <a href="#status-de-ingestao-GETapi-v1-ingestions--id--status">Consultar status</a>
                    </li>
                </ul>
            </ul>
            <ul id="tocify-header-administracao" class="tocify-header">
                <li class="tocify-item level-1" data-unique="administracao">
                    <a href="#administracao">Administração</a>
                </li>
                <ul id="tocify-subheader-administracao" class="tocify-subheader">
                    <li class="tocify-item level-2" data-unique="administracao-membros-gestao-de-membros-da-empresa-requer-role-admin-ou-superior">
                        <a href="#administracao-membros-gestao-de-membros-da-empresa-requer-role-admin-ou-superior">Membros

                            Gestão de membros da empresa (requer role admin ou superior).</a>
                    </li>
                    <ul id="tocify-subheader-administracao-membros-gestao-de-membros-da-empresa-requer-role-admin-ou-superior" class="tocify-subheader">
                        <li class="tocify-item level-3" data-unique="administracao-GETapi-v1-invites">
                            <a href="#administracao-GETapi-v1-invites">Listar membros</a>
                        </li>
                        <li class="tocify-item level-3" data-unique="administracao-POSTapi-v1-invites">
                            <a href="#administracao-POSTapi-v1-invites">Convidar membro</a>
                        </li>
                    </ul>
                    <li class="tocify-item level-2" data-unique="administracao-chaves-de-api-gestao-de-chaves-de-api-para-integracao-via-api-push-requer-role-admin-ou-superior">
                        <a href="#administracao-chaves-de-api-gestao-de-chaves-de-api-para-integracao-via-api-push-requer-role-admin-ou-superior">Chaves de API

                            Gestão de chaves de API para integração via API Push (requer role admin ou superior).</a>
                    </li>
                    <ul id="tocify-subheader-administracao-chaves-de-api-gestao-de-chaves-de-api-para-integracao-via-api-push-requer-role-admin-ou-superior" class="tocify-subheader">
                        <li class="tocify-item level-3" data-unique="administracao-GETapi-v1-api-keys">
                            <a href="#administracao-GETapi-v1-api-keys">Listar chaves</a>
                        </li>
                        <li class="tocify-item level-3" data-unique="administracao-POSTapi-v1-api-keys">
                            <a href="#administracao-POSTapi-v1-api-keys">Criar chave</a>
                        </li>
                        <li class="tocify-item level-3" data-unique="administracao-DELETEapi-v1-api-keys--id-">
                            <a href="#administracao-DELETEapi-v1-api-keys--id-">Revogar chave</a>
                        </li>
                    </ul>
                </ul>
            </ul>
        </div>

        <ul class="toc-footer" id="toc-footer">
            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
            <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
        </ul>

        <ul class="toc-footer" id="last-updated">
            <li>Last updated: March 3, 2026</li>
        </ul>
    </div>

    <div class="page-wrapper">
        <div class="dark-box"></div>
        <div class="content">
            <h1 id="introduction">Introduction</h1>
            <p>API para integração de dados de compras de múltiplos ERPs, análises de TCO, benchmark e alertas.</p>
            <aside>
                <strong>Base URL</strong>: <code>http://localhost:8000</code>
            </aside>
            <pre><code>Bem-vindo à documentação da API da **Plataforma de Inteligência de Compras**.

Esta API permite:
- Importar notas fiscais (XML NF-e ou JSON canônico)
- Consultar dashboards de análise (TCO, benchmark, saving)
- Gerenciar fornecedores, alertas e contatos
- Administrar chaves de API e convites

## Autenticação
Todas as requisições (exceto login) exigem um token Bearer obtido via `POST /api/v1/auth/login`.

## Multi-Tenancy
Envie o header `X-Company-Id` para especificar a empresa. Se omitido, será usada a empresa padrão do usuário.

&lt;aside&gt;Os exemplos de código à direita mostram como interagir com a API em diferentes linguagens.&lt;/aside&gt;</code></pre>

            <h1 id="authenticating-requests">Authenticating requests</h1>
            <p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {TOKEN}"</code></strong>.</p>
            <p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
            <p>Obtenha seu token via <strong>POST /api/v1/auth/login</strong> com <code>email</code> e <code>password</code>. Use o valor retornado no header: <code>Authorization: Bearer {TOKEN}</code>.</p>

            <h1 id="autenticacao">Autenticação</h1>

            <p>Endpoints para login, logout e consulta do usuário autenticado.</p>

            <h2 id="autenticacao-POSTapi-v1-auth-login">Login</h2>

            <p>
            </p>

            <p>Autentica o usuário e retorna um token Bearer para uso nas demais requisições.</p>

            <span id="example-requests-POSTapi-v1-auth-login">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1" \
    --data "{
    \"email\": \"admin@moveisruiz.com.br\",
    \"password\": \"password\"
}"
</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};

let body = {
    "email": "admin@moveisruiz.com.br",
    "password": "password"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/login';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'json' =&gt; [
            'email' =&gt; 'admin@moveisruiz.com.br',
            'password' =&gt; 'password',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/auth/login'
payload = {
    "email": "admin@moveisruiz.com.br",
    "password": "password"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-POSTapi-v1-auth-login">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;token&quot;: &quot;1|abc123...&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Admin Ruiz&quot;,
        &quot;email&quot;: &quot;admin@moveisruiz.com.br&quot;
    },
    &quot;companies&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;M&oacute;veis Ruiz&quot;,
            &quot;slug&quot;: &quot;moveis-ruiz&quot;,
            &quot;plan&quot;: &quot;professional&quot;,
            &quot;role&quot;: &quot;owner&quot;
        }
    ]
}</code>
 </pre>
                <blockquote>
                    <p>Example response (401):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Credenciais inv&aacute;lidas.&quot;,
    &quot;message&quot;: &quot;E-mail ou senha incorretos.&quot;
}</code>
 </pre>
            </span>
            <span id="execution-results-POSTapi-v1-auth-login" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v1-auth-login"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-POSTapi-v1-auth-login" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-POSTapi-v1-auth-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-POSTapi-v1-auth-login" data-method="POST"
                data-path="api/v1/auth/login"
                data-authed="0"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-login', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v1-auth-login"
                        onclick="tryItOut('POSTapi-v1-auth-login');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v1-auth-login"
                        onclick="cancelTryOut('POSTapi-v1-auth-login');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v1-auth-login"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-black">POST</small>
                    <b><code>api/v1/auth/login</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="POSTapi-v1-auth-login"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="POSTapi-v1-auth-login"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="POSTapi-v1-auth-login"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="email" data-endpoint="POSTapi-v1-auth-login"
                        value="admin@moveisruiz.com.br"
                        data-component="body">
                    <br>
                    <p>E-mail do usuário. Example: <code>admin@moveisruiz.com.br</code></p>
                </div>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="password" data-endpoint="POSTapi-v1-auth-login"
                        value="password"
                        data-component="body">
                    <br>
                    <p>Senha do usuário. Example: <code>password</code></p>
                </div>
            </form>

            <h2 id="autenticacao-POSTapi-v1-auth-logout">Logout</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Revoga o token atual do usuário.</p>

            <span id="example-requests-POSTapi-v1-auth-logout">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/auth/logout" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/logout"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/logout';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/auth/logout'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('POST', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-POSTapi-v1-auth-logout">
                <blockquote>
                    <p>Example response (204):</p>
                </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
            </span>
            <span id="execution-results-POSTapi-v1-auth-logout" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v1-auth-logout"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-POSTapi-v1-auth-logout" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-POSTapi-v1-auth-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-POSTapi-v1-auth-logout" data-method="POST"
                data-path="api/v1/auth/logout"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-logout', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v1-auth-logout"
                        onclick="tryItOut('POSTapi-v1-auth-logout');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v1-auth-logout"
                        onclick="cancelTryOut('POSTapi-v1-auth-logout');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v1-auth-logout"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-black">POST</small>
                    <b><code>api/v1/auth/logout</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="POSTapi-v1-auth-logout"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="POSTapi-v1-auth-logout"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="POSTapi-v1-auth-logout"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="POSTapi-v1-auth-logout"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h2 id="autenticacao-GETapi-v1-auth-me">Usuário autenticado</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna dados do usuário logado e suas empresas.</p>

            <span id="example-requests-GETapi-v1-auth-me">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/auth/me" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/auth/me"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/auth/me';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/auth/me'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-auth-me">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;user&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Admin Ruiz&quot;,
        &quot;email&quot;: &quot;admin@moveisruiz.com.br&quot;
    },
    &quot;companies&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;M&oacute;veis Ruiz&quot;,
            &quot;slug&quot;: &quot;moveis-ruiz&quot;,
            &quot;plan&quot;: &quot;professional&quot;,
            &quot;role&quot;: &quot;owner&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-auth-me" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-auth-me"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-auth-me"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-auth-me" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-auth-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-auth-me" data-method="GET"
                data-path="api/v1/auth/me"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-auth-me', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-auth-me"
                        onclick="tryItOut('GETapi-v1-auth-me');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-auth-me"
                        onclick="cancelTryOut('GETapi-v1-auth-me');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-auth-me"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/auth/me</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-auth-me"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-auth-me"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-auth-me"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-auth-me"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h1 id="empresas">Empresas</h1>

            <p>Empresas vinculadas ao usuário autenticado.</p>

            <h2 id="empresas-GETapi-v1-companies">Listar empresas</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna todas as empresas ativas vinculadas ao usuário.</p>

            <span id="example-requests-GETapi-v1-companies">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/companies" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/companies"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/companies';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/companies'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-companies">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;M&oacute;veis Ruiz&quot;,
            &quot;slug&quot;: &quot;moveis-ruiz&quot;,
            &quot;plan&quot;: &quot;professional&quot;,
            &quot;role&quot;: &quot;owner&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-companies" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-companies"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-companies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-companies" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-companies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-companies" data-method="GET"
                data-path="api/v1/companies"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-companies', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-companies"
                        onclick="tryItOut('GETapi-v1-companies');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-companies"
                        onclick="cancelTryOut('GETapi-v1-companies');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-companies"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/companies</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-companies"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-companies"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-companies"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-companies"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h1 id="importacao-de-notas-fiscais">Importação de Notas Fiscais</h1>

            <p>Endpoints para importar NF-e via XML ou JSON canônico.
                As importações são assíncronas — retornam 202 e o processamento ocorre em fila.</p>

            <h2 id="importacao-de-notas-fiscais-POSTapi-v1-invoices-preview-xml">Preview XML</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Extrai e retorna dados do XML NF-e para visualização, sem persistir.
                Este endpoint é síncrono.</p>

            <span id="example-requests-POSTapi-v1-invoices-preview-xml">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/invoices/preview-xml" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1" \
    --form "xml_file=@C:\Users\Cliente\AppData\Local\Temp\php1693.tmp" </code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices/preview-xml"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
    "X-Company-Id": "1",
};

const body = new FormData();
body.append('xml_file', document.querySelector('input[name="xml_file"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices/preview-xml';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'multipart/form-data',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'multipart' =&gt; [
            [
                'name' =&gt; 'xml_file',
                'contents' =&gt; fopen('C:\Users\Cliente\AppData\Local\Temp\php1693.tmp', 'r')
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invoices/preview-xml'
files = {
  'xml_file': open('C:\Users\Cliente\AppData\Local\Temp\php1693.tmp', 'rb')}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'multipart/form-data',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('POST', url, headers=headers, files=files)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-POSTapi-v1-invoices-preview-xml">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;invoice_number&quot;: &quot;000123&quot;,
    &quot;issue_date&quot;: &quot;15/01/2026&quot;,
    &quot;supplier&quot;: &quot;Fornecedor Exemplo&quot;,
    &quot;supplier_cnpj&quot;: &quot;12345678000190&quot;,
    &quot;supplier_region&quot;: &quot;Sul&quot;,
    &quot;supplier_state&quot;: &quot;PR&quot;,
    &quot;total_value&quot;: 5136.75,
    &quot;freight_value&quot;: 200,
    &quot;tax_value&quot;: 386.75,
    &quot;items_count&quot;: 2,
    &quot;items&quot;: [
        {
            &quot;product_description&quot;: &quot;MDF 18mm Branco&quot;,
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;quantity&quot;: 100,
            &quot;unit_price&quot;: 45.5,
            &quot;total_price&quot;: 4550
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-POSTapi-v1-invoices-preview-xml" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v1-invoices-preview-xml"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-POSTapi-v1-invoices-preview-xml"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-POSTapi-v1-invoices-preview-xml" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-POSTapi-v1-invoices-preview-xml">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-POSTapi-v1-invoices-preview-xml" data-method="POST"
                data-path="api/v1/invoices/preview-xml"
                data-authed="1"
                data-hasfiles="1"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invoices-preview-xml', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v1-invoices-preview-xml"
                        onclick="tryItOut('POSTapi-v1-invoices-preview-xml');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v1-invoices-preview-xml"
                        onclick="cancelTryOut('POSTapi-v1-invoices-preview-xml');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v1-invoices-preview-xml"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-black">POST</small>
                    <b><code>api/v1/invoices/preview-xml</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="POSTapi-v1-invoices-preview-xml"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="POSTapi-v1-invoices-preview-xml"
                        value="multipart/form-data"
                        data-component="header">
                    <br>
                    <p>Example: <code>multipart/form-data</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="POSTapi-v1-invoices-preview-xml"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="POSTapi-v1-invoices-preview-xml"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>xml_file</code></b>&nbsp;&nbsp;
                    <small>file</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="file" style="display: none"
                        name="xml_file" data-endpoint="POSTapi-v1-invoices-preview-xml"
                        value=""
                        data-component="body">
                    <br>
                    <p>Arquivo XML NF-e modelo 55. Max 10MB. Example: <code>C:\Users\Cliente\AppData\Local\Temp\php1693.tmp</code></p>
                </div>
            </form>

            <h2 id="importacao-de-notas-fiscais-POSTapi-v1-invoices-import-xml">Importar XML</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Recebe arquivo XML NF-e, salva na staging area (<code>raw_ingestions</code>) e enfileira processamento assíncrono.
                Duplicatas (mesmo hash SHA-256) retornam 409.</p>

            <span id="example-requests-POSTapi-v1-invoices-import-xml">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/invoices/import-xml" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: multipart/form-data" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1" \
    --form "xml_file=@C:\Users\Cliente\AppData\Local\Temp\php16A4.tmp" </code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices/import-xml"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "multipart/form-data",
    "Accept": "application/json",
    "X-Company-Id": "1",
};

const body = new FormData();
body.append('xml_file', document.querySelector('input[name="xml_file"]').files[0]);

fetch(url, {
    method: "POST",
    headers,
    body,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices/import-xml';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'multipart/form-data',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'multipart' =&gt; [
            [
                'name' =&gt; 'xml_file',
                'contents' =&gt; fopen('C:\Users\Cliente\AppData\Local\Temp\php16A4.tmp', 'r')
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invoices/import-xml'
files = {
  'xml_file': open('C:\Users\Cliente\AppData\Local\Temp\php16A4.tmp', 'rb')}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'multipart/form-data',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('POST', url, headers=headers, files=files)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-POSTapi-v1-invoices-import-xml">
                <blockquote>
                    <p>Example response (202):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;XML recebido. Processamento iniciado.&quot;,
    &quot;ingestion_id&quot;: 42,
    &quot;status&quot;: &quot;pending&quot;
}</code>
 </pre>
                <blockquote>
                    <p>Example response (409):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Este arquivo j&aacute; foi recebido anteriormente.&quot;,
    &quot;ingestion_id&quot;: 42,
    &quot;status&quot;: &quot;done&quot;
}</code>
 </pre>
            </span>
            <span id="execution-results-POSTapi-v1-invoices-import-xml" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v1-invoices-import-xml"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-POSTapi-v1-invoices-import-xml"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-POSTapi-v1-invoices-import-xml" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-POSTapi-v1-invoices-import-xml">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-POSTapi-v1-invoices-import-xml" data-method="POST"
                data-path="api/v1/invoices/import-xml"
                data-authed="1"
                data-hasfiles="1"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invoices-import-xml', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v1-invoices-import-xml"
                        onclick="tryItOut('POSTapi-v1-invoices-import-xml');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v1-invoices-import-xml"
                        onclick="cancelTryOut('POSTapi-v1-invoices-import-xml');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v1-invoices-import-xml"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-black">POST</small>
                    <b><code>api/v1/invoices/import-xml</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="POSTapi-v1-invoices-import-xml"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="POSTapi-v1-invoices-import-xml"
                        value="multipart/form-data"
                        data-component="header">
                    <br>
                    <p>Example: <code>multipart/form-data</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="POSTapi-v1-invoices-import-xml"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="POSTapi-v1-invoices-import-xml"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>xml_file</code></b>&nbsp;&nbsp;
                    <small>file</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="file" style="display: none"
                        name="xml_file" data-endpoint="POSTapi-v1-invoices-import-xml"
                        value=""
                        data-component="body">
                    <br>
                    <p>Arquivo XML NF-e modelo 55. Max 10MB. Example: <code>C:\Users\Cliente\AppData\Local\Temp\php16A4.tmp</code></p>
                </div>
            </form>

            <h2 id="importacao-de-notas-fiscais-POSTapi-v1-invoices-import-json">Importar JSON (API Push)</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Recebe notas fiscais no formato JSON canônico. Suporta batch (múltiplas notas por request).
                Cada nota é salva individualmente na staging area e processada de forma assíncrona.
                Duplicatas são detectadas por hash SHA-256 e retornadas com <code>skipped: true</code>.</p>

            <span id="example-requests-POSTapi-v1-invoices-import-json">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/invoices/import-json" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1" \
    --data "{
    \"source\": \"oracle_erp\",
    \"invoices\": [
        {
            \"invoice_number\": \"NF-001\",
            \"issue_date\": \"2026-01-15\",
            \"delivery_date\": \"2026-01-20\",
            \"payment_terms\": 30,
            \"supplier\": {
                \"cnpj\": \"12345678000190\",
                \"name\": \"Fornecedor Exemplo\",
                \"state\": \"PR\"
            },
            \"items\": [
                {
                    \"description\": \"MDF 18mm Branco\",
                    \"quantity\": 100,
                    \"unit_price\": 45.5,
                    \"category\": \"MDF\"
                }
            ],
            \"totals\": {
                \"total\": 5136.75,
                \"goods\": 4550,
                \"freight\": 200,
                \"tax\": 386.75
            }
        }
    ]
}"
</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices/import-json"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};

let body = {
    "source": "oracle_erp",
    "invoices": [
        {
            "invoice_number": "NF-001",
            "issue_date": "2026-01-15",
            "delivery_date": "2026-01-20",
            "payment_terms": 30,
            "supplier": {
                "cnpj": "12345678000190",
                "name": "Fornecedor Exemplo",
                "state": "PR"
            },
            "items": [
                {
                    "description": "MDF 18mm Branco",
                    "quantity": 100,
                    "unit_price": 45.5,
                    "category": "MDF"
                }
            ],
            "totals": {
                "total": 5136.75,
                "goods": 4550,
                "freight": 200,
                "tax": 386.75
            }
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices/import-json';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'json' =&gt; [
            'source' =&gt; 'oracle_erp',
            'invoices' =&gt; [
                [
                    'invoice_number' =&gt; 'NF-001',
                    'issue_date' =&gt; '2026-01-15',
                    'delivery_date' =&gt; '2026-01-20',
                    'payment_terms' =&gt; 30,
                    'supplier' =&gt; [
                        'cnpj' =&gt; '12345678000190',
                        'name' =&gt; 'Fornecedor Exemplo',
                        'state' =&gt; 'PR',
                    ],
                    'items' =&gt; [
                        [
                            'description' =&gt; 'MDF 18mm Branco',
                            'quantity' =&gt; 100.0,
                            'unit_price' =&gt; 45.5,
                            'category' =&gt; 'MDF',
                        ],
                    ],
                    'totals' =&gt; [
                        'total' =&gt; 5136.75,
                        'goods' =&gt; 4550.0,
                        'freight' =&gt; 200.0,
                        'tax' =&gt; 386.75,
                    ],
                ],
            ],
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invoices/import-json'
payload = {
    "source": "oracle_erp",
    "invoices": [
        {
            "invoice_number": "NF-001",
            "issue_date": "2026-01-15",
            "delivery_date": "2026-01-20",
            "payment_terms": 30,
            "supplier": {
                "cnpj": "12345678000190",
                "name": "Fornecedor Exemplo",
                "state": "PR"
            },
            "items": [
                {
                    "description": "MDF 18mm Branco",
                    "quantity": 100,
                    "unit_price": 45.5,
                    "category": "MDF"
                }
            ],
            "totals": {
                "total": 5136.75,
                "goods": 4550,
                "freight": 200,
                "tax": 386.75
            }
        }
    ]
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-POSTapi-v1-invoices-import-json">
                <blockquote>
                    <p>Example response (202):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;1 nota(s) recebida(s). Processamento iniciado.&quot;,
    &quot;ingestions&quot;: [
        {
            &quot;invoice_number&quot;: &quot;NF-001&quot;,
            &quot;ingestion_id&quot;: 43,
            &quot;status&quot;: &quot;pending&quot;,
            &quot;skipped&quot;: false
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-POSTapi-v1-invoices-import-json" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v1-invoices-import-json"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-POSTapi-v1-invoices-import-json"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-POSTapi-v1-invoices-import-json" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-POSTapi-v1-invoices-import-json">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-POSTapi-v1-invoices-import-json" data-method="POST"
                data-path="api/v1/invoices/import-json"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invoices-import-json', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v1-invoices-import-json"
                        onclick="tryItOut('POSTapi-v1-invoices-import-json');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v1-invoices-import-json"
                        onclick="cancelTryOut('POSTapi-v1-invoices-import-json');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v1-invoices-import-json"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-black">POST</small>
                    <b><code>api/v1/invoices/import-json</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="POSTapi-v1-invoices-import-json"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="POSTapi-v1-invoices-import-json"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="POSTapi-v1-invoices-import-json"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="POSTapi-v1-invoices-import-json"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>source</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="source" data-endpoint="POSTapi-v1-invoices-import-json"
                        value="oracle_erp"
                        data-component="body">
                    <br>
                    <p>Identificador do sistema de origem. Example: <code>oracle_erp</code></p>
                </div>
                <div style=" padding-left: 28px;  clear: unset;">
                    <details>
                        <summary style="padding-bottom: 10px;">
                            <b style="line-height: 2;"><code>invoices</code></b>&nbsp;&nbsp;
                            <small>object[]</small>&nbsp;
                            &nbsp;
                            &nbsp;
                            <br>
                            <p>Lista de notas fiscais.</p>
                        </summary>
                        <div style="margin-left: 14px; clear: unset;">
                            <b style="line-height: 2;"><code>invoice_number</code></b>&nbsp;&nbsp;
                            <small>string</small>&nbsp;
                            &nbsp;
                            &nbsp;
                            <input type="text" style="display: none"
                                name="invoices.0.invoice_number" data-endpoint="POSTapi-v1-invoices-import-json"
                                value="NF-001"
                                data-component="body">
                            <br>
                            <p>Número da nota. Example: <code>NF-001</code></p>
                        </div>
                        <div style="margin-left: 14px; clear: unset;">
                            <b style="line-height: 2;"><code>issue_date</code></b>&nbsp;&nbsp;
                            <small>string</small>&nbsp;
                            &nbsp;
                            &nbsp;
                            <input type="text" style="display: none"
                                name="invoices.0.issue_date" data-endpoint="POSTapi-v1-invoices-import-json"
                                value="2026-01-15"
                                data-component="body">
                            <br>
                            <p>Data de emissão (YYYY-MM-DD). Example: <code>2026-01-15</code></p>
                        </div>
                        <div style="margin-left: 14px; clear: unset;">
                            <b style="line-height: 2;"><code>delivery_date</code></b>&nbsp;&nbsp;
                            <small>string</small>&nbsp;
                            <i>optional</i> &nbsp;
                            &nbsp;
                            <input type="text" style="display: none"
                                name="invoices.0.delivery_date" data-endpoint="POSTapi-v1-invoices-import-json"
                                value="2026-01-20"
                                data-component="body">
                            <br>
                            <p>Data de entrega (YYYY-MM-DD). Example: <code>2026-01-20</code></p>
                        </div>
                        <div style="margin-left: 14px; clear: unset;">
                            <b style="line-height: 2;"><code>payment_terms</code></b>&nbsp;&nbsp;
                            <small>integer</small>&nbsp;
                            <i>optional</i> &nbsp;
                            &nbsp;
                            <input type="number" style="display: none"
                                step="any" name="invoices.0.payment_terms" data-endpoint="POSTapi-v1-invoices-import-json"
                                value="30"
                                data-component="body">
                            <br>
                            <p>Prazo de pagamento em dias. Example: <code>30</code></p>
                        </div>
                        <div style=" margin-left: 14px; clear: unset;">
                            <details>
                                <summary style="padding-bottom: 10px;">
                                    <b style="line-height: 2;"><code>supplier</code></b>&nbsp;&nbsp;
                                    <small>object</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <br>
                                    <p>Dados do fornecedor.</p>
                                </summary>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>cnpj</code></b>&nbsp;&nbsp;
                                    <small>string</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <input type="text" style="display: none"
                                        name="invoices.0.supplier.cnpj" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="12345678000190"
                                        data-component="body">
                                    <br>
                                    <p>CNPJ do fornecedor. Example: <code>12345678000190</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
                                    <small>string</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <input type="text" style="display: none"
                                        name="invoices.0.supplier.name" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="Fornecedor Exemplo"
                                        data-component="body">
                                    <br>
                                    <p>Nome do fornecedor. Example: <code>Fornecedor Exemplo</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>state</code></b>&nbsp;&nbsp;
                                    <small>string</small>&nbsp;
                                    <i>optional</i> &nbsp;
                                    &nbsp;
                                    <input type="text" style="display: none"
                                        name="invoices.0.supplier.state" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="PR"
                                        data-component="body">
                                    <br>
                                    <p>UF (2 letras). Example: <code>PR</code></p>
                                </div>
                            </details>
                        </div>
                        <div style=" margin-left: 14px; clear: unset;">
                            <details>
                                <summary style="padding-bottom: 10px;">
                                    <b style="line-height: 2;"><code>items</code></b>&nbsp;&nbsp;
                                    <small>object[]</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <br>
                                    <p>Itens da nota.</p>
                                </summary>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>description</code></b>&nbsp;&nbsp;
                                    <small>string</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <input type="text" style="display: none"
                                        name="invoices.0.items.0.description" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="MDF 18mm Branco"
                                        data-component="body">
                                    <br>
                                    <p>Descrição do produto. Example: <code>MDF 18mm Branco</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>quantity</code></b>&nbsp;&nbsp;
                                    <small>number</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <input type="number" style="display: none"
                                        step="any" name="invoices.0.items.0.quantity" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="100"
                                        data-component="body">
                                    <br>
                                    <p>Quantidade. Example: <code>100</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>unit_price</code></b>&nbsp;&nbsp;
                                    <small>number</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <input type="number" style="display: none"
                                        step="any" name="invoices.0.items.0.unit_price" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="45.5"
                                        data-component="body">
                                    <br>
                                    <p>Preço unitário. Example: <code>45.5</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
                                    <small>string</small>&nbsp;
                                    <i>optional</i> &nbsp;
                                    &nbsp;
                                    <input type="text" style="display: none"
                                        name="invoices.0.items.0.category" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="MDF"
                                        data-component="body">
                                    <br>
                                    <p>Categoria do produto. Example: <code>MDF</code></p>
                                </div>
                            </details>
                        </div>
                        <div style=" margin-left: 14px; clear: unset;">
                            <details>
                                <summary style="padding-bottom: 10px;">
                                    <b style="line-height: 2;"><code>totals</code></b>&nbsp;&nbsp;
                                    <small>object</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <br>
                                    <p>Totais da nota.</p>
                                </summary>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>total</code></b>&nbsp;&nbsp;
                                    <small>number</small>&nbsp;
                                    &nbsp;
                                    &nbsp;
                                    <input type="number" style="display: none"
                                        step="any" name="invoices.0.totals.total" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="5136.75"
                                        data-component="body">
                                    <br>
                                    <p>Valor total. Example: <code>5136.75</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>goods</code></b>&nbsp;&nbsp;
                                    <small>number</small>&nbsp;
                                    <i>optional</i> &nbsp;
                                    &nbsp;
                                    <input type="number" style="display: none"
                                        step="any" name="invoices.0.totals.goods" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="4550"
                                        data-component="body">
                                    <br>
                                    <p>Valor dos produtos. Example: <code>4550</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>freight</code></b>&nbsp;&nbsp;
                                    <small>number</small>&nbsp;
                                    <i>optional</i> &nbsp;
                                    &nbsp;
                                    <input type="number" style="display: none"
                                        step="any" name="invoices.0.totals.freight" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="200"
                                        data-component="body">
                                    <br>
                                    <p>Valor do frete. Example: <code>200</code></p>
                                </div>
                                <div style="margin-left: 28px; clear: unset;">
                                    <b style="line-height: 2;"><code>tax</code></b>&nbsp;&nbsp;
                                    <small>number</small>&nbsp;
                                    <i>optional</i> &nbsp;
                                    &nbsp;
                                    <input type="number" style="display: none"
                                        step="any" name="invoices.0.totals.tax" data-endpoint="POSTapi-v1-invoices-import-json"
                                        value="386.75"
                                        data-component="body">
                                    <br>
                                    <p>Valor dos impostos. Example: <code>386.75</code></p>
                                </div>
                            </details>
                        </div>
                    </details>
                </div>
            </form>

            <h1 id="notas-fiscais">Notas Fiscais</h1>

            <p>Listagem, consulta e exclusão de notas fiscais importadas.</p>

            <h2 id="notas-fiscais-GETapi-v1-invoices">Listar notas fiscais</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna notas fiscais paginadas com filtros opcionais.</p>

            <span id="example-requests-GETapi-v1-invoices">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/invoices?supplier_id=1&amp;start_date=2026-01-01&amp;end_date=2026-12-31&amp;category=MDF&amp;search=NF-001&amp;per_page=15" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices"
);

const params = {
    "supplier_id": "1",
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
    "category": "MDF",
    "search": "NF-001",
    "per_page": "15",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'supplier_id' =&gt; '1',
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
            'category' =&gt; 'MDF',
            'search' =&gt; 'NF-001',
            'per_page' =&gt; '15',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invoices'
params = {
  'supplier_id': '1',
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
  'category': 'MDF',
  'search': 'NF-001',
  'per_page': '15',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-invoices">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;invoice_number&quot;: &quot;NF-001&quot;,
            &quot;issue_date&quot;: &quot;15/01/2026&quot;,
            &quot;supplier&quot;: &quot;Fornecedor Exemplo&quot;,
            &quot;supplier_id&quot;: 1,
            &quot;total_value&quot;: 5136.75,
            &quot;freight_value&quot;: 200,
            &quot;tax_value&quot;: 386.75,
            &quot;items_count&quot;: 2,
            &quot;categories&quot;: [
                &quot;MDF&quot;,
                &quot;Ferragens&quot;
            ]
        }
    ],
    &quot;meta&quot;: {
        &quot;current_page&quot;: 1,
        &quot;last_page&quot;: 1,
        &quot;per_page&quot;: 15,
        &quot;total&quot;: 1
    }
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-invoices" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-invoices"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-invoices"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-invoices" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-invoices">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-invoices" data-method="GET"
                data-path="api/v1/invoices"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-invoices', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-invoices"
                        onclick="tryItOut('GETapi-v1-invoices');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-invoices"
                        onclick="cancelTryOut('GETapi-v1-invoices');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-invoices"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/invoices</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-invoices"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-invoices"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-invoices"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-invoices"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>supplier_id</code></b>&nbsp;&nbsp;
                    <small>integer</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="number" style="display: none"
                        step="any" name="supplier_id" data-endpoint="GETapi-v1-invoices"
                        value="1"
                        data-component="query">
                    <br>
                    <p>Filtrar por fornecedor. Example: <code>1</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-invoices"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-invoices"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="category" data-endpoint="GETapi-v1-invoices"
                        value="MDF"
                        data-component="query">
                    <br>
                    <p>Filtrar por categoria de item. Example: <code>MDF</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="search" data-endpoint="GETapi-v1-invoices"
                        value="NF-001"
                        data-component="query">
                    <br>
                    <p>Busca por número da nota ou nome do fornecedor. Example: <code>NF-001</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>per_page</code></b>&nbsp;&nbsp;
                    <small>integer</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="number" style="display: none"
                        step="any" name="per_page" data-endpoint="GETapi-v1-invoices"
                        value="15"
                        data-component="query">
                    <br>
                    <p>Itens por página (padrão: 15). Example: <code>15</code></p>
                </div>
            </form>

            <h2 id="notas-fiscais-GETapi-v1-invoices--id-">Detalhe de nota fiscal</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna dados completos de uma nota fiscal com seus itens.</p>

            <span id="example-requests-GETapi-v1-invoices--id-">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/invoices/1" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices/1"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invoices/1'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-invoices--id-">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 1,
    &quot;invoice_number&quot;: &quot;NF-001&quot;,
    &quot;issue_date&quot;: &quot;15/01/2026&quot;,
    &quot;supplier&quot;: &quot;Fornecedor Exemplo&quot;,
    &quot;total_value&quot;: 5136.75,
    &quot;freight_value&quot;: 200,
    &quot;tax_value&quot;: 386.75,
    &quot;items&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;product_description&quot;: &quot;MDF 18mm Branco&quot;,
            &quot;quantity&quot;: 100,
            &quot;unit_price&quot;: 45.5,
            &quot;total_price&quot;: 4550
        }
    ]
}</code>
 </pre>
                <blockquote>
                    <p>Example response (404):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;No query results for model [App\\Models\\Invoice] 999&quot;
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-invoices--id-" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-invoices--id-"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-invoices--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-invoices--id-" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-invoices--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-invoices--id-" data-method="GET"
                data-path="api/v1/invoices/{id}"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-invoices--id-', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-invoices--id-"
                        onclick="tryItOut('GETapi-v1-invoices--id-');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-invoices--id-"
                        onclick="cancelTryOut('GETapi-v1-invoices--id-');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-invoices--id-"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/invoices/{id}</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-invoices--id-"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-invoices--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-invoices--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-invoices--id-"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
                    <small>integer</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="number" style="display: none"
                        step="any" name="id" data-endpoint="GETapi-v1-invoices--id-"
                        value="1"
                        data-component="url">
                    <br>
                    <p>ID da nota fiscal. Example: <code>1</code></p>
                </div>
            </form>

            <h2 id="notas-fiscais-DELETEapi-v1-invoices--id-">Excluir nota fiscal</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Remove a nota fiscal e todos os seus itens.</p>

            <span id="example-requests-DELETEapi-v1-invoices--id-">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/invoices/1" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invoices/1"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invoices/1';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invoices/1'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('DELETE', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-DELETEapi-v1-invoices--id-">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Nota fiscal exclu&iacute;da com sucesso.&quot;
}</code>
 </pre>
            </span>
            <span id="execution-results-DELETEapi-v1-invoices--id-" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-DELETEapi-v1-invoices--id-"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-DELETEapi-v1-invoices--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-DELETEapi-v1-invoices--id-" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-DELETEapi-v1-invoices--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-DELETEapi-v1-invoices--id-" data-method="DELETE"
                data-path="api/v1/invoices/{id}"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-invoices--id-', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-DELETEapi-v1-invoices--id-"
                        onclick="tryItOut('DELETEapi-v1-invoices--id-');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-DELETEapi-v1-invoices--id-"
                        onclick="cancelTryOut('DELETEapi-v1-invoices--id-');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-DELETEapi-v1-invoices--id-"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-red">DELETE</small>
                    <b><code>api/v1/invoices/{id}</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="DELETEapi-v1-invoices--id-"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="DELETEapi-v1-invoices--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="DELETEapi-v1-invoices--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="DELETEapi-v1-invoices--id-"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
                    <small>integer</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="number" style="display: none"
                        step="any" name="id" data-endpoint="DELETEapi-v1-invoices--id-"
                        value="1"
                        data-component="url">
                    <br>
                    <p>ID da nota fiscal. Example: <code>1</code></p>
                </div>
            </form>

            <h1 id="dashboard-analises">Dashboard & Análises</h1>

            <p>Endpoints de análise: KPIs, TCO, evolução de preços, dispersão, frete e ranking de categorias.
                Todos aceitam filtros opcionais de período (start_date, end_date).</p>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-kpis">KPIs principais</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna indicadores-chave: TCO médio, variação vs mercado, peso do frete, categoria top e benchmark por categoria.</p>

            <span id="example-requests-GETapi-v1-dashboard-kpis">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/kpis?start_date=2026-01-01&amp;end_date=2026-12-31" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/kpis"
);

const params = {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/kpis';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/kpis'
params = {
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-kpis">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;overall_tco_average&quot;: 52.3,
    &quot;market_variation_percent&quot;: -3.5,
    &quot;freight_weight_percent&quot;: 8.2,
    &quot;top_category&quot;: {
        &quot;category&quot;: &quot;MDF&quot;,
        &quot;total_value&quot;: 45000
    },
    &quot;benchmark_by_category&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;company_tco_avg&quot;: 48.5,
            &quot;market_tco_avg&quot;: 50.2,
            &quot;variation_percent&quot;: -3.4,
            &quot;status&quot;: &quot;below_market&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-kpis" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-kpis"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-kpis"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-kpis" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-kpis">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-kpis" data-method="GET"
                data-path="api/v1/dashboard/kpis"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-kpis', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-kpis"
                        onclick="tryItOut('GETapi-v1-dashboard-kpis');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-kpis"
                        onclick="cancelTryOut('GETapi-v1-dashboard-kpis');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-kpis"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/kpis</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-kpis"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-kpis"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-kpis"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-kpis"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-dashboard-kpis"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-dashboard-kpis"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
            </form>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-tco-breakdown">Composição do TCO</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna breakdown do TCO (preço unitário + frete + imposto) por categoria, empresa vs mercado.</p>

            <span id="example-requests-GETapi-v1-dashboard-tco-breakdown">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/tco-breakdown?start_date=2026-01-01&amp;end_date=2026-12-31" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/tco-breakdown"
);

const params = {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/tco-breakdown';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/tco-breakdown'
params = {
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-tco-breakdown">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;company&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;avg_unit_price&quot;: 45.5,
            &quot;avg_freight&quot;: 3.2,
            &quot;avg_tax&quot;: 4.1,
            &quot;tco_total&quot;: 52.8
        }
    ],
    &quot;market&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;avg_unit_price&quot;: 47,
            &quot;avg_freight&quot;: 3.5,
            &quot;avg_tax&quot;: 4.3,
            &quot;tco_total&quot;: 54.8
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-tco-breakdown" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-tco-breakdown"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-tco-breakdown"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-tco-breakdown" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-tco-breakdown">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-tco-breakdown" data-method="GET"
                data-path="api/v1/dashboard/tco-breakdown"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-tco-breakdown', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-tco-breakdown"
                        onclick="tryItOut('GETapi-v1-dashboard-tco-breakdown');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-tco-breakdown"
                        onclick="cancelTryOut('GETapi-v1-dashboard-tco-breakdown');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-tco-breakdown"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/tco-breakdown</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-tco-breakdown"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-tco-breakdown"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-tco-breakdown"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-tco-breakdown"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-dashboard-tco-breakdown"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-dashboard-tco-breakdown"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
            </form>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-gross-vs-net">Custo bruto vs líquido</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna custo bruto, crédito tributário e custo líquido por categoria.</p>

            <span id="example-requests-GETapi-v1-dashboard-gross-vs-net">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/gross-vs-net?start_date=2026-01-01&amp;end_date=2026-12-31" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/gross-vs-net"
);

const params = {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/gross-vs-net';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/gross-vs-net'
params = {
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-gross-vs-net">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;gross_cost&quot;: 45000,
            &quot;tax_credit&quot;: 3825,
            &quot;net_cost&quot;: 41175,
            &quot;credit_percent&quot;: 8.5
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-gross-vs-net" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-gross-vs-net"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-gross-vs-net"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-gross-vs-net" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-gross-vs-net">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-gross-vs-net" data-method="GET"
                data-path="api/v1/dashboard/gross-vs-net"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-gross-vs-net', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-gross-vs-net"
                        onclick="tryItOut('GETapi-v1-dashboard-gross-vs-net');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-gross-vs-net"
                        onclick="cancelTryOut('GETapi-v1-dashboard-gross-vs-net');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-gross-vs-net"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/gross-vs-net</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-gross-vs-net"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-gross-vs-net"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-gross-vs-net"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-gross-vs-net"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-dashboard-gross-vs-net"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-dashboard-gross-vs-net"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
            </form>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-price-evolution">Evolução de preços</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna evolução mensal de preços médios por categoria, incluindo curva de mercado.</p>

            <span id="example-requests-GETapi-v1-dashboard-price-evolution">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/price-evolution?category=MDF" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/price-evolution"
);

const params = {
    "category": "MDF",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/price-evolution';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'category' =&gt; 'MDF',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/price-evolution'
params = {
  'category': 'MDF',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-price-evolution">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;month&quot;: &quot;2026-01&quot;,
            &quot;MDF&quot;: 45.5,
            &quot;Mercado MDF&quot;: 47
        }
    ],
    &quot;categories&quot;: [
        &quot;MDF&quot;,
        &quot;Ferragens&quot;
    ],
    &quot;market_categories&quot;: [
        &quot;Mercado MDF&quot;,
        &quot;Mercado Ferragens&quot;
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-price-evolution" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-price-evolution"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-price-evolution"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-price-evolution" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-price-evolution">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-price-evolution" data-method="GET"
                data-path="api/v1/dashboard/price-evolution"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-price-evolution', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-price-evolution"
                        onclick="tryItOut('GETapi-v1-dashboard-price-evolution');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-price-evolution"
                        onclick="cancelTryOut('GETapi-v1-dashboard-price-evolution');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-price-evolution"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/price-evolution</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-price-evolution"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-price-evolution"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-price-evolution"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-price-evolution"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="category" data-endpoint="GETapi-v1-dashboard-price-evolution"
                        value="MDF"
                        data-component="query">
                    <br>
                    <p>Filtrar por categoria específica. Example: <code>MDF</code></p>
                </div>
            </form>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-price-index">Índice de preços</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna índice percentual por período (base 100) para análise de sazonalidade.</p>

            <span id="example-requests-GETapi-v1-dashboard-price-index">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/price-index?category=MDF" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/price-index"
);

const params = {
    "category": "MDF",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/price-index';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'category' =&gt; 'MDF',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/price-index'
params = {
  'category': 'MDF',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-price-index">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;month&quot;: &quot;2026-01&quot;,
            &quot;MDF&quot;: 100,
            &quot;Ferragens&quot;: 100
        }
    ],
    &quot;categories&quot;: [
        &quot;MDF&quot;,
        &quot;Ferragens&quot;
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-price-index" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-price-index"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-price-index"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-price-index" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-price-index">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-price-index" data-method="GET"
                data-path="api/v1/dashboard/price-index"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-price-index', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-price-index"
                        onclick="tryItOut('GETapi-v1-dashboard-price-index');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-price-index"
                        onclick="cancelTryOut('GETapi-v1-dashboard-price-index');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-price-index"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/price-index</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-price-index"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-price-index"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-price-index"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-price-index"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="category" data-endpoint="GETapi-v1-dashboard-price-index"
                        value="MDF"
                        data-component="query">
                    <br>
                    <p>Filtrar por categoria específica. Example: <code>MDF</code></p>
                </div>
            </form>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-dispersion">Dispersão de preços</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna preço mínimo, médio e máximo por categoria, indicando variabilidade.</p>

            <span id="example-requests-GETapi-v1-dashboard-dispersion">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/dispersion?start_date=2026-01-01&amp;end_date=2026-12-31" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/dispersion"
);

const params = {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/dispersion';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/dispersion'
params = {
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-dispersion">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;min_price&quot;: 38,
            &quot;avg_price&quot;: 45.5,
            &quot;max_price&quot;: 58,
            &quot;range&quot;: 20
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-dispersion" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-dispersion"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-dispersion"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-dispersion" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-dispersion">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-dispersion" data-method="GET"
                data-path="api/v1/dashboard/dispersion"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-dispersion', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-dispersion"
                        onclick="tryItOut('GETapi-v1-dashboard-dispersion');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-dispersion"
                        onclick="cancelTryOut('GETapi-v1-dashboard-dispersion');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-dispersion"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/dispersion</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-dispersion"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-dispersion"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-dispersion"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-dispersion"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-dashboard-dispersion"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-dashboard-dispersion"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
            </form>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-freight-impact">Impacto do frete</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna o peso percentual do frete no custo total por categoria.</p>

            <span id="example-requests-GETapi-v1-dashboard-freight-impact">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/freight-impact?start_date=2026-01-01&amp;end_date=2026-12-31" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/freight-impact"
);

const params = {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/freight-impact';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/freight-impact'
params = {
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-freight-impact">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;total_invoiced&quot;: 45000,
            &quot;total_freight&quot;: 3600,
            &quot;freight_percent&quot;: 8
        }
    ],
    &quot;overall_percent&quot;: 7.5
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-freight-impact" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-freight-impact"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-freight-impact"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-freight-impact" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-freight-impact">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-freight-impact" data-method="GET"
                data-path="api/v1/dashboard/freight-impact"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-freight-impact', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-freight-impact"
                        onclick="tryItOut('GETapi-v1-dashboard-freight-impact');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-freight-impact"
                        onclick="cancelTryOut('GETapi-v1-dashboard-freight-impact');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-freight-impact"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/freight-impact</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-freight-impact"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-freight-impact"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-freight-impact"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-freight-impact"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-dashboard-freight-impact"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-dashboard-freight-impact"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
            </form>

            <h2 id="dashboard-analises-GETapi-v1-dashboard-category-ranking">Ranking de categorias</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna categorias ordenadas por valor total investido.</p>

            <span id="example-requests-GETapi-v1-dashboard-category-ranking">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/category-ranking?start_date=2026-01-01&amp;end_date=2026-12-31" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/category-ranking"
);

const params = {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/category-ranking';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/category-ranking'
params = {
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-category-ranking">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;rank&quot;: 1,
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;total_value&quot;: 45000,
            &quot;total_qty&quot;: 1000,
            &quot;items_count&quot;: 15
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-category-ranking" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-category-ranking"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-category-ranking"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-category-ranking" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-category-ranking">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-category-ranking" data-method="GET"
                data-path="api/v1/dashboard/category-ranking"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-category-ranking', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-category-ranking"
                        onclick="tryItOut('GETapi-v1-dashboard-category-ranking');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-category-ranking"
                        onclick="cancelTryOut('GETapi-v1-dashboard-category-ranking');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-category-ranking"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/category-ranking</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-category-ranking"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-category-ranking"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-category-ranking"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-category-ranking"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-dashboard-category-ranking"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-dashboard-category-ranking"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
            </form>

            <h1 id="saving-cost-avoidance">Saving & Cost Avoidance</h1>

            <p>Análise de economia realizada e custos evitados por categoria.
                Compara preço médio do período atual (3 meses) vs anterior (3 meses).</p>

            <h2 id="saving-cost-avoidance-GETapi-v1-dashboard-saving">Saving por categoria</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna saving real (variação de preço) e cost avoidance (vs índice de mercado) por categoria.</p>

            <span id="example-requests-GETapi-v1-dashboard-saving">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/saving" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/saving"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/saving';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/saving'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-saving">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;total_saving&quot;: 1250,
    &quot;total_cost_avoid&quot;: 3800,
    &quot;period_label&quot;: &quot;&Uacute;ltimos 3 meses vs. 3 meses anteriores&quot;,
    &quot;categories&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;prev_avg_price&quot;: 48,
            &quot;curr_avg_price&quot;: 45.5,
            &quot;price_change_pct&quot;: 5.21,
            &quot;saving_abs&quot;: 250,
            &quot;cost_avoid_abs&quot;: 800,
            &quot;market_index_pct&quot;: 8.2,
            &quot;hypothetical_avg&quot;: 51.94,
            &quot;total_spend&quot;: 45000,
            &quot;status&quot;: &quot;saving&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-saving" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-saving"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-saving"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-saving" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-saving">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-saving" data-method="GET"
                data-path="api/v1/dashboard/saving"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-saving', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-saving"
                        onclick="tryItOut('GETapi-v1-dashboard-saving');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-saving"
                        onclick="cancelTryOut('GETapi-v1-dashboard-saving');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-saving"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/saving</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-saving"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-saving"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-saving"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-saving"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h1 id="benchmark-de-estrutura-de-custo">Benchmark de Estrutura de Custo</h1>

            <p>Comparação da estrutura de custo da empresa com benchmarks de mercado por categoria e período.</p>

            <h2 id="benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark">Benchmark por categoria</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna estrutura de custo da empresa vs mercado por categoria num período específico.
                Se nenhum período for informado, usa o mais recente com dados disponíveis.</p>

            <span id="example-requests-GETapi-v1-dashboard-cost-structure-benchmark">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/cost-structure-benchmark?period=2026-01&amp;region=Sul" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/cost-structure-benchmark"
);

const params = {
    "period": "2026-01",
    "region": "Sul",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/cost-structure-benchmark';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'period' =&gt; '2026-01',
            'region' =&gt; 'Sul',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/cost-structure-benchmark'
params = {
  'period': '2026-01',
  'region': 'Sul',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-cost-structure-benchmark">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;meta&quot;: {
        &quot;company_id&quot;: 1,
        &quot;company_name&quot;: &quot;M&oacute;veis Ruiz&quot;,
        &quot;period&quot;: &quot;2026-01&quot;,
        &quot;period_label&quot;: &quot;Jan/2026&quot;,
        &quot;region&quot;: &quot;Sul&quot;,
        &quot;benchmark_region&quot;: &quot;Sul&quot;,
        &quot;benchmark_sample_size&quot;: 15,
        &quot;benchmark_is_valid&quot;: true,
        &quot;total_company_spend&quot;: 120000,
        &quot;calculated_at&quot;: &quot;2026-01-15T10:00:00Z&quot;
    },
    &quot;categories&quot;: [
        {
            &quot;category_slug&quot;: &quot;mdf&quot;,
            &quot;category_name&quot;: &quot;MDF&quot;,
            &quot;company_spend&quot;: 45000,
            &quot;company_percentage&quot;: 37.5,
            &quot;benchmark_percentage&quot;: 35,
            &quot;benchmark_median&quot;: 42,
            &quot;benchmark_p25&quot;: 38,
            &quot;benchmark_p75&quot;: 48,
            &quot;delta_percentage&quot;: 2.5,
            &quot;delta_status&quot;: &quot;above_market&quot;,
            &quot;financial_impact&quot;: 3000,
            &quot;financial_impact_label&quot;: &quot;R$ 3.000 acima do mercado&quot;
        }
    ],
    &quot;summary&quot;: {
        &quot;total_potential_saving&quot;: 5000,
        &quot;categories_above_market&quot;: 2,
        &quot;categories_below_market&quot;: 3,
        &quot;categories_aligned&quot;: 1,
        &quot;worst_category&quot;: {
            &quot;slug&quot;: &quot;mdf&quot;,
            &quot;name&quot;: &quot;MDF&quot;,
            &quot;delta&quot;: 2.5,
            &quot;impact&quot;: 3000
        }
    }
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-cost-structure-benchmark" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-cost-structure-benchmark"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-cost-structure-benchmark"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-cost-structure-benchmark" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-cost-structure-benchmark">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-cost-structure-benchmark" data-method="GET"
                data-path="api/v1/dashboard/cost-structure-benchmark"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-cost-structure-benchmark', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-cost-structure-benchmark"
                        onclick="tryItOut('GETapi-v1-dashboard-cost-structure-benchmark');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-cost-structure-benchmark"
                        onclick="cancelTryOut('GETapi-v1-dashboard-cost-structure-benchmark');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-cost-structure-benchmark"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/cost-structure-benchmark</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>period</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="period" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark"
                        value="2026-01"
                        data-component="query">
                    <br>
                    <p>Período no formato YYYY-MM. Example: <code>2026-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>region</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="region" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark"
                        value="Sul"
                        data-component="query">
                    <br>
                    <p>Filtrar por região. Example: <code>Sul</code></p>
                </div>
            </form>

            <h2 id="benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark-periods">Períodos disponíveis</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna lista de períodos (YYYY-MM) para o dropdown de seleção.</p>

            <span id="example-requests-GETapi-v1-dashboard-cost-structure-benchmark-periods">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/periods" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/periods"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/periods';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/periods'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-cost-structure-benchmark-periods">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;periods&quot;: [
        {
            &quot;value&quot;: &quot;2026-01&quot;,
            &quot;label&quot;: &quot;Jan/2026&quot;
        },
        {
            &quot;value&quot;: &quot;2025-12&quot;,
            &quot;label&quot;: &quot;Dez/2025&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-cost-structure-benchmark-periods" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-cost-structure-benchmark-periods"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-cost-structure-benchmark-periods"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-cost-structure-benchmark-periods" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-cost-structure-benchmark-periods">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-cost-structure-benchmark-periods" data-method="GET"
                data-path="api/v1/dashboard/cost-structure-benchmark/periods"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-cost-structure-benchmark-periods', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-cost-structure-benchmark-periods"
                        onclick="tryItOut('GETapi-v1-dashboard-cost-structure-benchmark-periods');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-cost-structure-benchmark-periods"
                        onclick="cancelTryOut('GETapi-v1-dashboard-cost-structure-benchmark-periods');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-cost-structure-benchmark-periods"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/cost-structure-benchmark/periods</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-periods"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-periods"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-periods"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-periods"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h2 id="benchmark-de-estrutura-de-custo-GETapi-v1-dashboard-cost-structure-benchmark-category-products">Produtos por categoria</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna detalhamento dos produtos comprados em uma categoria específica.</p>

            <span id="example-requests-GETapi-v1-dashboard-cost-structure-benchmark-category-products">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/category-products?category=mdf&amp;period=2026-01" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/category-products"
);

const params = {
    "category": "mdf",
    "period": "2026-01",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/category-products';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'category' =&gt; 'mdf',
            'period' =&gt; '2026-01',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/dashboard/cost-structure-benchmark/category-products'
params = {
  'category': 'mdf',
  'period': '2026-01',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-dashboard-cost-structure-benchmark-category-products">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;category&quot;: {
        &quot;slug&quot;: &quot;mdf&quot;,
        &quot;name&quot;: &quot;MDF&quot;,
        &quot;total_spend&quot;: 45000,
        &quot;company_percentage&quot;: 37.5,
        &quot;benchmark_percentage&quot;: 35,
        &quot;delta_percentage&quot;: 2.5,
        &quot;delta_status&quot;: &quot;above_market&quot;
    },
    &quot;meta&quot;: {
        &quot;period&quot;: &quot;2026-01&quot;,
        &quot;total_company_spend&quot;: 120000,
        &quot;products_count&quot;: 5,
        &quot;unique_suppliers&quot;: 2
    },
    &quot;products&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;product_description&quot;: &quot;MDF 18mm Branco&quot;,
            &quot;quantity&quot;: 100,
            &quot;unit_price&quot;: 45.5,
            &quot;total_price&quot;: 4550,
            &quot;pct_of_category&quot;: 10.1,
            &quot;pct_of_company&quot;: 3.8,
            &quot;supplier_name&quot;: &quot;Fornecedor Exemplo&quot;,
            &quot;supplier_cnpj&quot;: &quot;12345678000190&quot;,
            &quot;invoice_number&quot;: &quot;NF-001&quot;,
            &quot;issue_date&quot;: &quot;2026-01-15&quot;,
            &quot;freight_allocated&quot;: 36,
            &quot;tax_allocated&quot;: 34,
            &quot;total_with_costs&quot;: 4620
        }
    ]
}</code>
 </pre>
                <blockquote>
                    <p>Example response (404):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Categoria n&atilde;o encontrada.&quot;
}</code>
 </pre>
                <blockquote>
                    <p>Example response (422):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Par&acirc;metros \&quot;category\&quot; e \&quot;period\&quot; s&atilde;o obrigat&oacute;rios.&quot;
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-dashboard-cost-structure-benchmark-category-products" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-dashboard-cost-structure-benchmark-category-products"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-dashboard-cost-structure-benchmark-category-products"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-dashboard-cost-structure-benchmark-category-products" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-dashboard-cost-structure-benchmark-category-products">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-dashboard-cost-structure-benchmark-category-products" data-method="GET"
                data-path="api/v1/dashboard/cost-structure-benchmark/category-products"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-dashboard-cost-structure-benchmark-category-products', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        onclick="tryItOut('GETapi-v1-dashboard-cost-structure-benchmark-category-products');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        onclick="cancelTryOut('GETapi-v1-dashboard-cost-structure-benchmark-category-products');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/dashboard/cost-structure-benchmark/category-products</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>category</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="category" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        value="mdf"
                        data-component="query">
                    <br>
                    <p>Slug da categoria. Example: <code>mdf</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>period</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="period" data-endpoint="GETapi-v1-dashboard-cost-structure-benchmark-category-products"
                        value="2026-01"
                        data-component="query">
                    <br>
                    <p>Período no formato YYYY-MM. Example: <code>2026-01</code></p>
                </div>
            </form>

            <h1 id="fornecedores">Fornecedores</h1>

            <p>Análise e ranking de fornecedores com score de classificação.</p>

            <h2 id="fornecedores-GETapi-v1-suppliers">Ranking de fornecedores</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna todos os fornecedores com métricas de compra, score e classificação (Estratégico/Alternativo/Risco).</p>

            <span id="example-requests-GETapi-v1-suppliers">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/suppliers?start_date=2026-01-01&amp;end_date=2026-12-31" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/suppliers"
);

const params = {
    "start_date": "2026-01-01",
    "end_date": "2026-12-31",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/suppliers';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'start_date' =&gt; '2026-01-01',
            'end_date' =&gt; '2026-12-31',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/suppliers'
params = {
  'start_date': '2026-01-01',
  'end_date': '2026-12-31',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-suppliers">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;supplier_id&quot;: 1,
            &quot;supplier_name&quot;: &quot;Fornecedor Exemplo&quot;,
            &quot;cnpj&quot;: &quot;12345678000190&quot;,
            &quot;region&quot;: &quot;Sul&quot;,
            &quot;state&quot;: &quot;PR&quot;,
            &quot;invoice_count&quot;: 10,
            &quot;total_purchased&quot;: 50000,
            &quot;avg_invoice_value&quot;: 5000,
            &quot;total_freight&quot;: 4000,
            &quot;avg_freight_pct&quot;: 8,
            &quot;category_count&quot;: 3,
            &quot;first_purchase&quot;: &quot;2025-06-01&quot;,
            &quot;last_purchase&quot;: &quot;2026-01-15&quot;,
            &quot;score&quot;: 75,
            &quot;classification&quot;: &quot;Estrat&eacute;gico&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-suppliers" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-suppliers"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-suppliers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-suppliers" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-suppliers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-suppliers" data-method="GET"
                data-path="api/v1/suppliers"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-suppliers', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-suppliers"
                        onclick="tryItOut('GETapi-v1-suppliers');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-suppliers"
                        onclick="cancelTryOut('GETapi-v1-suppliers');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-suppliers"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/suppliers</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-suppliers"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-suppliers"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-suppliers"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-suppliers"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>start_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="start_date" data-endpoint="GETapi-v1-suppliers"
                        value="2026-01-01"
                        data-component="query">
                    <br>
                    <p>Data inicial (YYYY-MM-DD). Example: <code>2026-01-01</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>end_date</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="end_date" data-endpoint="GETapi-v1-suppliers"
                        value="2026-12-31"
                        data-component="query">
                    <br>
                    <p>Data final (YYYY-MM-DD). Example: <code>2026-12-31</code></p>
                </div>
            </form>

            <h2 id="fornecedores-GETapi-v1-suppliers--id-">Detalhe do fornecedor</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna categorias fornecidas, estatísticas de preço e evolução mensal de compras.</p>

            <span id="example-requests-GETapi-v1-suppliers--id-">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/suppliers/1" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/suppliers/1"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/suppliers/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/suppliers/1'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-suppliers--id-">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;supplier&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Fornecedor Exemplo&quot;,
        &quot;cnpj&quot;: &quot;12345678000190&quot;,
        &quot;region&quot;: &quot;Sul&quot;,
        &quot;state&quot;: &quot;PR&quot;
    },
    &quot;categories&quot;: [
        {
            &quot;category&quot;: &quot;MDF&quot;,
            &quot;item_count&quot;: 15,
            &quot;total_value&quot;: 45000,
            &quot;avg_unit_price&quot;: 45.5,
            &quot;min_unit_price&quot;: 38,
            &quot;max_unit_price&quot;: 58
        }
    ],
    &quot;monthly_evolution&quot;: [
        {
            &quot;month&quot;: &quot;2026-01&quot;,
            &quot;total_value&quot;: 5000,
            &quot;invoice_count&quot;: 2
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-suppliers--id-" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-suppliers--id-"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-suppliers--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-suppliers--id-" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-suppliers--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-suppliers--id-" data-method="GET"
                data-path="api/v1/suppliers/{id}"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-suppliers--id-', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-suppliers--id-"
                        onclick="tryItOut('GETapi-v1-suppliers--id-');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-suppliers--id-"
                        onclick="cancelTryOut('GETapi-v1-suppliers--id-');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-suppliers--id-"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/suppliers/{id}</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-suppliers--id-"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-suppliers--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-suppliers--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-suppliers--id-"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
                    <small>integer</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="number" style="display: none"
                        step="any" name="id" data-endpoint="GETapi-v1-suppliers--id-"
                        value="1"
                        data-component="url">
                    <br>
                    <p>ID do fornecedor. Example: <code>1</code></p>
                </div>
            </form>

            <h1 id="contatos">Contatos</h1>

            <p>Gestão de contatos de fornecedores com análise de prazos de pagamento vs mercado.</p>

            <h2 id="contatos-GETapi-v1-contacts">Listar contatos</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna fornecedores com dados de contato, categorias, prazos de pagamento e comparação com mercado.</p>

            <span id="example-requests-GETapi-v1-contacts">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/contacts?search=Madeiras&amp;region=Sul" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/contacts"
);

const params = {
    "search": "Madeiras",
    "region": "Sul",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/contacts';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'query' =&gt; [
            'search' =&gt; 'Madeiras',
            'region' =&gt; 'Sul',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/contacts'
params = {
  'search': 'Madeiras',
  'region': 'Sul',
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-contacts">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;market_payment_terms&quot;: 42,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Fornecedor Exemplo&quot;,
            &quot;cnpj&quot;: &quot;12345678000190&quot;,
            &quot;region&quot;: &quot;Sul&quot;,
            &quot;state&quot;: &quot;PR&quot;,
            &quot;contact_name&quot;: &quot;Jo&atilde;o Silva&quot;,
            &quot;contact_email&quot;: &quot;joao@fornecedor.com&quot;,
            &quot;contact_phone&quot;: &quot;(41) 99999-0000&quot;,
            &quot;payment_terms&quot;: 30,
            &quot;market_payment_terms&quot;: 42,
            &quot;terms_delta&quot;: -12,
            &quot;terms_status&quot;: &quot;above_market&quot;,
            &quot;categories&quot;: [
                &quot;MDF&quot;,
                &quot;Ferragens&quot;
            ],
            &quot;invoice_count&quot;: 10,
            &quot;total_purchased&quot;: 50000
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-contacts" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-contacts"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-contacts"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-contacts" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-contacts">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-contacts" data-method="GET"
                data-path="api/v1/contacts"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-contacts', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-contacts"
                        onclick="tryItOut('GETapi-v1-contacts');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-contacts"
                        onclick="cancelTryOut('GETapi-v1-contacts');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-contacts"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/contacts</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-contacts"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-contacts"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-contacts"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-contacts"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>search</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="search" data-endpoint="GETapi-v1-contacts"
                        value="Madeiras"
                        data-component="query">
                    <br>
                    <p>Busca por nome, contato, e-mail ou região. Example: <code>Madeiras</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>region</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    <i>optional</i> &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="region" data-endpoint="GETapi-v1-contacts"
                        value="Sul"
                        data-component="query">
                    <br>
                    <p>Filtrar por região. Example: <code>Sul</code></p>
                </div>
            </form>

            <h1 id="alertas">Alertas</h1>

            <p>Alertas automáticos gerados pela análise de dados de compras.
                Tipos: anomalia de preço, frete alto, alta consecutiva e concentração de fornecedor.</p>

            <h2 id="alertas-GETapi-v1-alerts">Listar alertas</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna alertas ordenados por severidade (high primeiro) e data.</p>

            <span id="example-requests-GETapi-v1-alerts">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/alerts" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/alerts"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/alerts';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/alerts'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-alerts">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;total&quot;: 5,
    &quot;high&quot;: 2,
    &quot;medium&quot;: 3,
    &quot;alerts&quot;: [
        {
            &quot;type&quot;: &quot;price_anomaly&quot;,
            &quot;severity&quot;: &quot;high&quot;,
            &quot;title&quot;: &quot;Pre&ccedil;o acima da m&eacute;dia &mdash; MDF&quot;,
            &quot;message&quot;: &quot;MDF 18mm Branco comprado por R$ 65,00 (+42.9% acima da m&eacute;dia hist&oacute;rica)&quot;,
            &quot;detail&quot;: &quot;NF-e NF-001 &middot; Fornecedor Exemplo &middot; 15/01/2026&quot;,
            &quot;date&quot;: &quot;2026-01-15&quot;
        },
        {
            &quot;type&quot;: &quot;high_freight&quot;,
            &quot;severity&quot;: &quot;medium&quot;,
            &quot;title&quot;: &quot;Frete alto &mdash; Ferragens&quot;,
            &quot;message&quot;: &quot;Impacto m&eacute;dio de 15.2% do frete no custo total desta categoria (8 notas)&quot;,
            &quot;detail&quot;: &quot;Considere renegociar frete ou buscar fornecedores mais pr&oacute;ximos&quot;,
            &quot;date&quot;: &quot;2026-03-03&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-alerts" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-alerts"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-alerts"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-alerts" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-alerts">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-alerts" data-method="GET"
                data-path="api/v1/alerts"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-alerts', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-alerts"
                        onclick="tryItOut('GETapi-v1-alerts');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-alerts"
                        onclick="cancelTryOut('GETapi-v1-alerts');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-alerts"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/alerts</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-alerts"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-alerts"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-alerts"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-alerts"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h1 id="status-de-ingestao">Status de Ingestão</h1>

            <p>Acompanhamento do processamento assíncrono de importações.</p>

            <h2 id="status-de-ingestao-GETapi-v1-ingestions--id--status">Consultar status</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna o status de processamento de uma ingestão específica.
                Use após importar XML ou JSON para acompanhar se o processamento foi concluído.</p>

            <span id="example-requests-GETapi-v1-ingestions--id--status">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/ingestions/42/status" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/ingestions/42/status"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/ingestions/42/status';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/ingestions/42/status'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-ingestions--id--status">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;id&quot;: 42,
    &quot;channel&quot;: &quot;xml_upload&quot;,
    &quot;source&quot;: &quot;nfe_xml&quot;,
    &quot;status&quot;: &quot;done&quot;,
    &quot;attempts&quot;: 1,
    &quot;error_message&quot;: null,
    &quot;created_at&quot;: &quot;2026-01-15T10:00:00+00:00&quot;,
    &quot;processed_at&quot;: &quot;2026-01-15T10:00:05+00:00&quot;
}</code>
 </pre>
                <blockquote>
                    <p>Example response (404):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Ingest&atilde;o n&atilde;o encontrada.&quot;
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-ingestions--id--status" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-ingestions--id--status"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-ingestions--id--status"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-ingestions--id--status" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-ingestions--id--status">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-ingestions--id--status" data-method="GET"
                data-path="api/v1/ingestions/{id}/status"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-ingestions--id--status', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-ingestions--id--status"
                        onclick="tryItOut('GETapi-v1-ingestions--id--status');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-ingestions--id--status"
                        onclick="cancelTryOut('GETapi-v1-ingestions--id--status');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-ingestions--id--status"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/ingestions/{id}/status</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-ingestions--id--status"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-ingestions--id--status"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-ingestions--id--status"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-ingestions--id--status"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
                    <small>integer</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="number" style="display: none"
                        step="any" name="id" data-endpoint="GETapi-v1-ingestions--id--status"
                        value="42"
                        data-component="url">
                    <br>
                    <p>ID da ingestão retornado pelo endpoint de importação. Example: <code>42</code></p>
                </div>
            </form>

            <h1 id="administracao">Administração</h1>



            <h2 id="administracao-membros-gestao-de-membros-da-empresa-requer-role-admin-ou-superior">Membros

                Gestão de membros da empresa (requer role admin ou superior).</h2>
            <h2 id="administracao-GETapi-v1-invites">Listar membros</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna todos os membros vinculados à empresa atual.</p>

            <span id="example-requests-GETapi-v1-invites">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/invites" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invites"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invites';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invites'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-invites">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Admin Ruiz&quot;,
            &quot;email&quot;: &quot;admin@moveisruiz.com.br&quot;,
            &quot;role&quot;: &quot;owner&quot;,
            &quot;joined_at&quot;: &quot;2026-01-01T00:00:00Z&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-invites" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-invites"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-invites"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-invites" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-invites">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-invites" data-method="GET"
                data-path="api/v1/invites"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-invites', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-invites"
                        onclick="tryItOut('GETapi-v1-invites');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-invites"
                        onclick="cancelTryOut('GETapi-v1-invites');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-invites"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/invites</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-invites"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-invites"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-invites"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-invites"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h2 id="administracao-POSTapi-v1-invites">Convidar membro</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Adiciona um novo membro à empresa. Se o e-mail não existir, cria o usuário automaticamente.</p>

            <span id="example-requests-POSTapi-v1-invites">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/invites" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1" \
    --data "{
    \"email\": \"analista@empresa.com\",
    \"name\": \"Maria Analista\",
    \"role\": \"analyst\"
}"
</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/invites"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};

let body = {
    "email": "analista@empresa.com",
    "name": "Maria Analista",
    "role": "analyst"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/invites';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'json' =&gt; [
            'email' =&gt; 'analista@empresa.com',
            'name' =&gt; 'Maria Analista',
            'role' =&gt; 'analyst',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/invites'
payload = {
    "email": "analista@empresa.com",
    "name": "Maria Analista",
    "role": "analyst"
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-POSTapi-v1-invites">
                <blockquote>
                    <p>Example response (201):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 5,
        &quot;name&quot;: &quot;Maria Analista&quot;,
        &quot;email&quot;: &quot;analista@empresa.com&quot;,
        &quot;role&quot;: &quot;analyst&quot;
    }
}</code>
 </pre>
                <blockquote>
                    <p>Example response (422):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;error&quot;: &quot;Usu&aacute;rio j&aacute; &eacute; membro.&quot;,
    &quot;message&quot;: &quot;Este e-mail j&aacute; est&aacute; vinculado a esta empresa.&quot;
}</code>
 </pre>
            </span>
            <span id="execution-results-POSTapi-v1-invites" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v1-invites"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-POSTapi-v1-invites"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-POSTapi-v1-invites" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-POSTapi-v1-invites">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-POSTapi-v1-invites" data-method="POST"
                data-path="api/v1/invites"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-invites', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v1-invites"
                        onclick="tryItOut('POSTapi-v1-invites');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v1-invites"
                        onclick="cancelTryOut('POSTapi-v1-invites');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v1-invites"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-black">POST</small>
                    <b><code>api/v1/invites</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="POSTapi-v1-invites"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="POSTapi-v1-invites"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="POSTapi-v1-invites"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="POSTapi-v1-invites"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="email" data-endpoint="POSTapi-v1-invites"
                        value="analista@empresa.com"
                        data-component="body">
                    <br>
                    <p>E-mail do novo membro. Example: <code>analista@empresa.com</code></p>
                </div>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="name" data-endpoint="POSTapi-v1-invites"
                        value="Maria Analista"
                        data-component="body">
                    <br>
                    <p>Nome do membro. Example: <code>Maria Analista</code></p>
                </div>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>role</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="role" data-endpoint="POSTapi-v1-invites"
                        value="analyst"
                        data-component="body">
                    <br>
                    <p>Role do membro: admin, analyst ou viewer. Example: <code>analyst</code></p>
                </div>
            </form>

            <h2 id="administracao-chaves-de-api-gestao-de-chaves-de-api-para-integracao-via-api-push-requer-role-admin-ou-superior">Chaves de API

                Gestão de chaves de API para integração via API Push (requer role admin ou superior).</h2>
            <h2 id="administracao-GETapi-v1-api-keys">Listar chaves</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Retorna todas as chaves de API ativas da empresa.</p>

            <span id="example-requests-GETapi-v1-api-keys">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost:8000/api/v1/api-keys" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/api-keys"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/api-keys';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/api-keys'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-GETapi-v1-api-keys">
                <blockquote>
                    <p>Example response (200):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: [
        {
            &quot;id&quot;: 10,
            &quot;name&quot;: &quot;ERP Protheus&quot;,
            &quot;last_used_at&quot;: &quot;2026-01-15T10:00:00Z&quot;,
            &quot;created_at&quot;: &quot;2026-01-01T00:00:00Z&quot;
        }
    ]
}</code>
 </pre>
            </span>
            <span id="execution-results-GETapi-v1-api-keys" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-GETapi-v1-api-keys"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-GETapi-v1-api-keys"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-GETapi-v1-api-keys" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-GETapi-v1-api-keys">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-GETapi-v1-api-keys" data-method="GET"
                data-path="api/v1/api-keys"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-api-keys', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-GETapi-v1-api-keys"
                        onclick="tryItOut('GETapi-v1-api-keys');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-GETapi-v1-api-keys"
                        onclick="cancelTryOut('GETapi-v1-api-keys');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-GETapi-v1-api-keys"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-green">GET</small>
                    <b><code>api/v1/api-keys</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="GETapi-v1-api-keys"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="GETapi-v1-api-keys"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="GETapi-v1-api-keys"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="GETapi-v1-api-keys"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
            </form>

            <h2 id="administracao-POSTapi-v1-api-keys">Criar chave</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Gera uma nova chave de API. A chave em texto puro é retornada apenas nesta resposta.</p>

            <span id="example-requests-POSTapi-v1-api-keys">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request POST \
    "http://localhost:8000/api/v1/api-keys" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1" \
    --data "{
    \"name\": \"ERP Protheus\"
}"
</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/api-keys"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};

let body = {
    "name": "ERP Protheus"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/api-keys';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
        'json' =&gt; [
            'name' =&gt; 'ERP Protheus',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/api-keys'
payload = {
    "name": "ERP Protheus"
}
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-POSTapi-v1-api-keys">
                <blockquote>
                    <p>Example response (201):</p>
                </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;data&quot;: {
        &quot;id&quot;: 10,
        &quot;name&quot;: &quot;ERP Protheus&quot;,
        &quot;key&quot;: &quot;1|abc123def456...&quot;,
        &quot;created_at&quot;: &quot;2026-01-01T00:00:00Z&quot;
    }
}</code>
 </pre>
            </span>
            <span id="execution-results-POSTapi-v1-api-keys" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-POSTapi-v1-api-keys"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-POSTapi-v1-api-keys"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-POSTapi-v1-api-keys" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-POSTapi-v1-api-keys">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-POSTapi-v1-api-keys" data-method="POST"
                data-path="api/v1/api-keys"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-api-keys', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-POSTapi-v1-api-keys"
                        onclick="tryItOut('POSTapi-v1-api-keys');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-POSTapi-v1-api-keys"
                        onclick="cancelTryOut('POSTapi-v1-api-keys');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-POSTapi-v1-api-keys"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-black">POST</small>
                    <b><code>api/v1/api-keys</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="POSTapi-v1-api-keys"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="POSTapi-v1-api-keys"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="POSTapi-v1-api-keys"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="POSTapi-v1-api-keys"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
                <div style=" padding-left: 28px;  clear: unset;">
                    <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
                    <small>string</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="name" data-endpoint="POSTapi-v1-api-keys"
                        value="ERP Protheus"
                        data-component="body">
                    <br>
                    <p>Nome identificador da chave. Example: <code>ERP Protheus</code></p>
                </div>
            </form>

            <h2 id="administracao-DELETEapi-v1-api-keys--id-">Revogar chave</h2>

            <p>
                <small class="badge badge-darkred">requires authentication</small>
            </p>

            <p>Remove a chave de API e todos os tokens associados.</p>

            <span id="example-requests-DELETEapi-v1-api-keys--id-">
                <blockquote>Example request:</blockquote>


                <div class="bash-example">
                    <pre><code class="language-bash">curl --request DELETE \
    "http://localhost:8000/api/v1/api-keys/10" \
    --header "Authorization: Bearer {TOKEN}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-Company-Id: 1"</code></pre>
                </div>


                <div class="javascript-example">
                    <pre><code class="language-javascript">const url = new URL(
    "http://localhost:8000/api/v1/api-keys/10"
);

const headers = {
    "Authorization": "Bearer {TOKEN}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Company-Id": "1",
};


fetch(url, {
    method: "DELETE",
    headers,
}).then(response =&gt; response.json());</code></pre>
                </div>


                <div class="php-example">
                    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://localhost:8000/api/v1/api-keys/10';
$response = $client-&gt;delete(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {TOKEN}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-Company-Id' =&gt; '1',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre>
                </div>


                <div class="python-example">
                    <pre><code class="language-python">import requests
import json

url = 'http://localhost:8000/api/v1/api-keys/10'
headers = {
  'Authorization': 'Bearer {TOKEN}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-Company-Id': '1'
}

response = requests.request('DELETE', url, headers=headers)
response.json()</code></pre>
                </div>

            </span>

            <span id="example-responses-DELETEapi-v1-api-keys--id-">
                <blockquote>
                    <p>Example response (204):</p>
                </blockquote>
                <pre>
<code>Empty response</code>
 </pre>
            </span>
            <span id="execution-results-DELETEapi-v1-api-keys--id-" hidden>
                <blockquote>Received response<span
                        id="execution-response-status-DELETEapi-v1-api-keys--id-"></span>:
                </blockquote>
                <pre class="json"><code id="execution-response-content-DELETEapi-v1-api-keys--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
            </span>
            <span id="execution-error-DELETEapi-v1-api-keys--id-" hidden>
                <blockquote>Request failed with error:</blockquote>
                <pre><code id="execution-error-message-DELETEapi-v1-api-keys--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
            </span>
            <form id="form-DELETEapi-v1-api-keys--id-" data-method="DELETE"
                data-path="api/v1/api-keys/{id}"
                data-authed="1"
                data-hasfiles="0"
                data-isarraybody="0"
                autocomplete="off"
                onsubmit="event.preventDefault(); executeTryOut('DELETEapi-v1-api-keys--id-', this);">
                <h3>
                    Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                        style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-tryout-DELETEapi-v1-api-keys--id-"
                        onclick="tryItOut('DELETEapi-v1-api-keys--id-');">Try it out ⚡
                    </button>
                    <button type="button"
                        style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-canceltryout-DELETEapi-v1-api-keys--id-"
                        onclick="cancelTryOut('DELETEapi-v1-api-keys--id-');" hidden>Cancel 🛑
                    </button>&nbsp;&nbsp;
                    <button type="submit"
                        style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                        id="btn-executetryout-DELETEapi-v1-api-keys--id-"
                        data-initial-text="Send Request 💥"
                        data-loading-text="⏱ Sending..."
                        hidden>Send Request 💥
                    </button>
                </h3>
                <p>
                    <small class="badge badge-red">DELETE</small>
                    <b><code>api/v1/api-keys/{id}</code></b>
                </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Authorization" class="auth-value" data-endpoint="DELETEapi-v1-api-keys--id-"
                        value="Bearer {TOKEN}"
                        data-component="header">
                    <br>
                    <p>Example: <code>Bearer {TOKEN}</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Content-Type" data-endpoint="DELETEapi-v1-api-keys--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="Accept" data-endpoint="DELETEapi-v1-api-keys--id-"
                        value="application/json"
                        data-component="header">
                    <br>
                    <p>Example: <code>application/json</code></p>
                </div>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>X-Company-Id</code></b>&nbsp;&nbsp;
                    &nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="text" style="display: none"
                        name="X-Company-Id" data-endpoint="DELETEapi-v1-api-keys--id-"
                        value="1"
                        data-component="header">
                    <br>
                    <p>Example: <code>1</code></p>
                </div>
                <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                <div style="padding-left: 28px; clear: unset;">
                    <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
                    <small>integer</small>&nbsp;
                    &nbsp;
                    &nbsp;
                    <input type="number" style="display: none"
                        step="any" name="id" data-endpoint="DELETEapi-v1-api-keys--id-"
                        value="10"
                        data-component="url">
                    <br>
                    <p>ID da chave (user_id do service user). Example: <code>10</code></p>
                </div>
            </form>




        </div>
        <div class="dark-box">
            <div class="lang-selector">
                <button type="button" class="lang-button" data-language-name="bash">bash</button>
                <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                <button type="button" class="lang-button" data-language-name="php">php</button>
                <button type="button" class="lang-button" data-language-name="python">python</button>
            </div>
        </div>
    </div>
</body>

</html>