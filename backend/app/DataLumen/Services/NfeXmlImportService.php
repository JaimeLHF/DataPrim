<?php

namespace App\DataLumen\Services;

use App\DataBridge\DTOs\NfeImportDto;
use App\DataCore\Models\Company;
use App\DataCore\Models\Invoice;
use App\DataCore\Models\Supplier;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class NfeXmlImportService
{
    /**
     * Extrai dados do XML sem persistir (preview).
     *
     * @throws Exception
     */
    public function preview(UploadedFile $file, int $companyId): NfeImportDto
    {
        $content = file_get_contents($file->getPathname());

        // Remove namespace para simplificar o parse
        $content = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $content);
        $content = preg_replace('/\s{2,}/', ' ', $content);

        $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            throw new Exception('Arquivo XML inválido ou corrompido.');
        }

        // Suporte a NFe com ou sem wrapper nfeProc
        $nfe = $xml->NFe ?? $xml;
        $infNFe = $nfe->infNFe ?? null;

        if (!$infNFe) {
            throw new Exception('Estrutura de NF-e não encontrada no XML.');
        }

        // Verificar modelo 55
        $mod = (string) ($infNFe->ide->mod ?? '');
        if ($mod !== '55') {
            throw new Exception("Apenas NF-e modelo 55 é suportado. Modelo encontrado: {$mod}");
        }

        return $this->extractDto($infNFe, $companyId);
    }

    /**
     * Parse e persiste um arquivo XML de NF-e modelo 55.
     *
     * @throws Exception
     */
    public function import(UploadedFile $file, int $companyId): array
    {
        $dto = $this->preview($file, $companyId);

        // Verificar duplicidade
        if (Invoice::where('invoice_number', $dto->invoiceNumber)->exists()) {
            throw new Exception("NF-e número {$dto->invoiceNumber} já importada anteriormente.");
        }

        return $this->persist($dto);
    }

    private function extractDto(\SimpleXMLElement $infNFe, int $companyId): NfeImportDto
    {
        $ide  = $infNFe->ide;
        $emit = $infNFe->emit;
        $transp = $infNFe->transp ?? null;
        $total  = $infNFe->total->ICMSTot ?? null;

        $dto = new NfeImportDto();
        $dto->companyId     = $companyId;
        $dto->invoiceNumber = (string) $ide->nNF . '-' . (string) $ide->serie;
        $dto->issueDate     = date('Y-m-d', strtotime((string) $ide->dhEmi));
        $dto->supplierName   = (string) ($emit->xNome ?? $emit->xFant ?? 'Fornecedor Desconhecido');
        $dto->supplierCnpj  = (string) ($emit->CNPJ ?? '');
        $uf                  = strtoupper((string) ($emit->enderEmit->UF ?? ''));
        $dto->supplierState  = $uf;
        $dto->supplierRegion = $this->mapUfToRegion($uf);
        $dto->totalValue    = (float) ($total->vNF ?? 0);
        $dto->freightValue  = (float) ($transp->modFrete !== null ? ($infNFe->total->ICMSTot->vFrete ?? 0) : 0);
        $dto->taxValue      = (float) (($total->vICMS ?? 0) + ($total->vIPI ?? 0));

        $dto->items = [];
        $dets = $infNFe->det ?? [];

        foreach ($dets as $det) {
            $prod = $det->prod;
            $dto->items[] = [
                'product_description' => (string) ($prod->xProd ?? 'Produto'),
                'category'            => $this->inferCategory((string) ($prod->xProd ?? '')),
                'quantity'            => (float) ($prod->qCom ?? 1),
                'unit_price'          => (float) ($prod->vUnCom ?? 0),
                'total_price'         => (float) ($prod->vProd ?? 0),
            ];
        }

        return $dto;
    }

    private function mapUfToRegion(string $uf): string
    {
        $map = [
            'PR' => 'Sul', 'SC' => 'Sul', 'RS' => 'Sul',
            'SP' => 'Sudeste', 'RJ' => 'Sudeste', 'MG' => 'Sudeste', 'ES' => 'Sudeste',
            'MT' => 'Centro-Oeste', 'MS' => 'Centro-Oeste', 'GO' => 'Centro-Oeste', 'DF' => 'Centro-Oeste',
            'BA' => 'Nordeste', 'SE' => 'Nordeste', 'AL' => 'Nordeste', 'PE' => 'Nordeste',
            'PB' => 'Nordeste', 'RN' => 'Nordeste', 'CE' => 'Nordeste', 'PI' => 'Nordeste', 'MA' => 'Nordeste',
            'AM' => 'Norte', 'PA' => 'Norte', 'AC' => 'Norte', 'RO' => 'Norte',
            'RR' => 'Norte', 'AP' => 'Norte', 'TO' => 'Norte',
        ];

        return $map[$uf] ?? 'Desconhecida';
    }

    /**
     * Infere categoria do produto pelo nome (simplificado para MVP).
     */
    private function inferCategory(string $productName): string
    {
        $name = Str::lower($productName);

        $map = [
            'mdf'       => 'MDF',
            'paraf'     => 'Ferragens',
            'prego'     => 'Ferragens',
            'dobrad'    => 'Ferragens',
            'quim'      => 'Químicos',
            'tinta'     => 'Químicos',
            'cola'      => 'Químicos',
            'arame'     => 'Aramados',
            'embal'     => 'Embalagens',
            'caixa'     => 'Embalagens',
            'acab'      => 'Acessórios',
            'pux'       => 'Acessórios',
        ];

        foreach ($map as $keyword => $category) {
            if (str_contains($name, $keyword)) {
                return $category;
            }
        }

        return 'Outros';
    }

    private function persist(NfeImportDto $dto): array
    {
        $supplier = Supplier::firstOrCreate(
            ['cnpj' => $dto->supplierCnpj],
            [
                'name'   => $dto->supplierName,
                'region' => $dto->supplierRegion,
                'state'  => $dto->supplierState,
            ]
        );

        $invoice = Invoice::create([
            'company_id'     => $dto->companyId,
            'supplier_id'    => $supplier->id,
            'invoice_number' => $dto->invoiceNumber,
            'issue_date'     => $dto->issueDate,
            'total_value'    => $dto->totalValue,
            'freight_value'  => $dto->freightValue,
            'tax_value'      => $dto->taxValue,
        ]);

        $items = [];
        foreach ($dto->items as $item) {
            $items[] = $invoice->items()->create($item);
        }

        return [
            'invoice'      => $invoice->load('supplier'),
            'items'        => $items,
            'items_count'  => count($items),
        ];
    }
}
