<?php

namespace App\DataBridge\Contracts;

use App\DataBridge\DTOs\CanonicalInvoiceDto;

interface InvoiceAdapterInterface
{
    /**
     * Determina se este adapter sabe processar o canal/source informado.
     */
    public function canHandle(string $channel, ?string $source): bool;

    /**
     * Converte o payload bruto no modelo canônico.
     */
    public function normalize(string $rawPayload, int $companyId): CanonicalInvoiceDto;
}
