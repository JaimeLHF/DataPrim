<?php

namespace App\DataBridge\Contracts;

/**
 * Interface para adapters que recebem dados via Webhook.
 *
 * Extende InvoiceAdapterInterface adicionando validação de assinatura HMAC,
 * necessária para verificar a autenticidade das requisições dos ERPs.
 */
interface WebhookAdapterInterface extends InvoiceAdapterInterface
{
    /**
     * Valida a assinatura HMAC do payload recebido.
     *
     * @param string $rawPayload   Corpo bruto da requisição (antes de qualquer parsing)
     * @param string $signatureHeader  Valor do header de assinatura enviado pelo ERP
     * @param string $secret       Secret configurado no webhook_config da empresa
     */
    public function validateSignature(
        string $rawPayload,
        string $signatureHeader,
        string $secret
    ): bool;

    /**
     * Retorna o nome do header HTTP que contém a assinatura para este ERP.
     * Ex: 'X-Bling-Signature-256', 'X-Hub-Signature-256'
     */
    public function signatureHeader(): string;
}
