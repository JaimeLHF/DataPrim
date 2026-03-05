<?php

namespace App\DataBridge\Connectors;

use App\DataBridge\Connectors\BlingConnector;
use App\DataBridge\Connectors\TinyErpConnector;
use App\DataBridge\Connectors\StubConnector;
use App\DataBridge\Contracts\ConnectorInterface;
use App\DataCore\Exceptions\ConnectorException;
use App\DataCore\Models\ErpConnector;

/**
 * Resolve o conector concreto a partir de um ErpConnector model.
 *
 * Para adicionar um novo ERP:
 *   1. Criar App\DataBridge\Connectors\{ERP}Connector implements ConnectorInterface
 *   2. Adicionar um case no match abaixo
 *   3. Adicionar o erp_type na validação do ErpConnectorController
 */
class ConnectorFactory
{
    public static function make(ErpConnector $connector): ConnectorInterface
    {
        // Descriptografa automaticamente via mutator do model
        $creds  = $connector->credentials;
        $config = $connector->config ?? [];

        return match ($connector->erp_type) {
            'bling'    => new BlingConnector($connector, $config), // tokens vêm do modelo
            'tinyerp'  => new TinyErpConnector($creds, $config),
            'stub'     => new StubConnector(), // apenas em testes
            default    => throw ConnectorException::unsupportedErp($connector->erp_type),
        };
    }

    /**
     * Lista os tipos de ERP suportados (para validação no controller).
     */
    public static function supportedTypes(): array
    {
        return ['bling', 'tinyerp']; // 'stub' não exposto na API
    }
}
