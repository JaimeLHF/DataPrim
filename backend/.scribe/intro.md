# Introduction

API para integração de dados de compras de múltiplos ERPs, análises de TCO, benchmark e alertas.

<aside>
    <strong>Base URL</strong>: <code>http://localhost:8000</code>
</aside>

    Bem-vindo à documentação da API da **Plataforma de Inteligência de Compras**.

    Esta API permite:
    - Importar notas fiscais (XML NF-e ou JSON canônico)
    - Consultar dashboards de análise (TCO, benchmark, saving)
    - Gerenciar fornecedores, alertas e contatos
    - Administrar chaves de API e convites

    ## Autenticação
    Todas as requisições (exceto login) exigem um token Bearer obtido via `POST /api/v1/auth/login`.

    ## Multi-Tenancy
    Envie o header `X-Company-Id` para especificar a empresa. Se omitido, será usada a empresa padrão do usuário.

    <aside>Os exemplos de código à direita mostram como interagir com a API em diferentes linguagens.</aside>
