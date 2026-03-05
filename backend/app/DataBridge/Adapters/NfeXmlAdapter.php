<?php

namespace App\DataBridge\Adapters;

use App\DataBridge\Contracts\InvoiceAdapterInterface;
use App\DataBridge\DTOs\CanonicalInvoiceDto;
use App\DataBridge\DTOs\CanonicalItemDto;
use App\DataBridge\DTOs\CanonicalSupplierDto;
use App\DataBridge\DTOs\CanonicalTotalsDto;
use Exception;
use Illuminate\Support\Str;
use SimpleXMLElement;

class NfeXmlAdapter implements InvoiceAdapterInterface
{
    private const UF_TO_REGION = [
        'PR' => 'Sul', 'SC' => 'Sul', 'RS' => 'Sul',
        'SP' => 'Sudeste', 'RJ' => 'Sudeste', 'MG' => 'Sudeste', 'ES' => 'Sudeste',
        'MT' => 'Centro-Oeste', 'MS' => 'Centro-Oeste', 'GO' => 'Centro-Oeste', 'DF' => 'Centro-Oeste',
        'BA' => 'Nordeste', 'SE' => 'Nordeste', 'AL' => 'Nordeste', 'PE' => 'Nordeste',
        'PB' => 'Nordeste', 'RN' => 'Nordeste', 'CE' => 'Nordeste', 'PI' => 'Nordeste', 'MA' => 'Nordeste',
        'AM' => 'Norte', 'PA' => 'Norte', 'AC' => 'Norte', 'RO' => 'Norte',
        'RR' => 'Norte', 'AP' => 'Norte', 'TO' => 'Norte',
    ];

    private const CATEGORY_KEYWORDS = [
        'mdf'    => 'MDF',
        'paraf'  => 'Ferragens',
        'prego'  => 'Ferragens',
        'dobrad' => 'Ferragens',
        'quim'   => 'Químicos',
        'tinta'  => 'Químicos',
        'cola'   => 'Químicos',
        'arame'  => 'Aramados',
        'embal'  => 'Embalagens',
        'caixa'  => 'Embalagens',
        'acab'   => 'Acessórios',
        'pux'    => 'Acessórios',
    ];

    public function canHandle(string $channel, ?string $source): bool
    {
        return $channel === 'xml_upload';
    }

    /**
     * @throws Exception
     */
    public function normalize(string $rawPayload, int $companyId): CanonicalInvoiceDto
    {
        $content = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $rawPayload);
        $content = preg_replace('/\s{2,}/', ' ', $content);

        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new Exception('Arquivo XML inválido ou corrompido.');
        }

        $nfe    = $xml->NFe ?? $xml;
        $infNFe = $nfe->infNFe ?? null;

        if (!$infNFe) {
            throw new Exception('Estrutura de NF-e não encontrada no XML.');
        }

        $mod = (string) ($infNFe->ide->mod ?? '');
        if ($mod !== '55') {
            throw new Exception("Apenas NF-e modelo 55 é suportado. Modelo encontrado: {$mod}");
        }

        return $this->extractDto($infNFe, $companyId);
    }

    private function extractDto(SimpleXMLElement $infNFe, int $companyId): CanonicalInvoiceDto
    {
        $ide    = $infNFe->ide;
        $emit   = $infNFe->emit;
        $transp = $infNFe->transp ?? null;
        $total  = $infNFe->total->ICMSTot ?? null;

        $uf = strtoupper((string) ($emit->enderEmit->UF ?? ''));

        $supplier = new CanonicalSupplierDto(
            cnpj:   (string) ($emit->CNPJ ?? ''),
            name:   (string) ($emit->xNome ?? $emit->xFant ?? 'Fornecedor Desconhecido'),
            region: self::UF_TO_REGION[$uf] ?? 'Desconhecida',
            state:  $uf,
        );

        $items = [];
        foreach (($infNFe->det ?? []) as $det) {
            $prod    = $det->prod;
            $name    = (string) ($prod->xProd ?? 'Produto');
            $items[] = new CanonicalItemDto(
                description: $name,
                category:    $this->inferCategory($name),
                quantity:    (float) ($prod->qCom ?? 1),
                unitPrice:   (float) ($prod->vUnCom ?? 0),
                totalPrice:  (float) ($prod->vProd ?? 0),
            );
        }

        $freightValue = (float) ($transp !== null ? ($total->vFrete ?? 0) : 0);
        $taxValue     = (float) (($total->vICMS ?? 0) + ($total->vIPI ?? 0));
        $totalValue   = (float) ($total->vNF ?? 0);

        $totals = new CanonicalTotalsDto(
            goodsValue:   $totalValue - $freightValue - $taxValue,
            freightValue: $freightValue,
            taxValue:     $taxValue,
            totalValue:   $totalValue,
        );

        return new CanonicalInvoiceDto(
            companyId:     $companyId,
            invoiceNumber: (string) $ide->nNF . '-' . (string) $ide->serie,
            issueDate:     date('Y-m-d', strtotime((string) $ide->dhEmi)),
            supplier:      $supplier,
            items:         $items,
            totals:        $totals,
            sourceSystem:  'nfe_xml',
        );
    }

    private function inferCategory(string $productName): string
    {
        $name = Str::lower($productName);

        foreach (self::CATEGORY_KEYWORDS as $keyword => $category) {
            if (str_contains($name, $keyword)) {
                return $category;
            }
        }

        return 'Outros';
    }
}
