<?php

namespace App\DataBridge\DTOs;

class CanonicalInvoiceDto
{
    public function __construct(
        public int $companyId,
        public string $invoiceNumber,
        public string $issueDate,
        public CanonicalSupplierDto $supplier,
        /** @var CanonicalItemDto[] */
        public array $items,
        public CanonicalTotalsDto $totals,
        public string $sourceSystem = 'nfe_xml',
        public ?string $sourceId = null,
        public ?string $deliveryDate = null,
        public ?int $paymentTerms = null,
    ) {}
}
