<?php

namespace App\DataCore\Exceptions;

use RuntimeException;

/**
 * Exceção lançada por conectores de ERP em falhas de integração.
 *
 * Cobre: falha de autenticação, rate limit, API indisponível,
 * resposta inesperada, credenciais inválidas.
 *
 * Separada de Exception genérica para que o SyncErpConnectorJob
 * possa tratar falhas de conector de forma específica.
 */
class ConnectorException extends RuntimeException
{
    public static function authFailed(string $erp, string $reason = ''): self
    {
        $msg = "Falha de autenticação no {$erp}";
        if ($reason) $msg .= ": {$reason}";
        return new self($msg);
    }

    public static function apiError(string $erp, int $status, string $body = ''): self
    {
        $preview = $body ? (' — ' . substr($body, 0, 200)) : '';
        return new self("API {$erp} retornou HTTP {$status}{$preview}");
    }

    public static function unsupportedErp(string $erpType): self
    {
        return new self("ERP '{$erpType}' não é suportado. Conectores disponíveis: bling, tinyerp.");
    }
}
