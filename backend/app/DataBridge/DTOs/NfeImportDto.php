<?php

namespace App\DataBridge\DTOs;

class NfeImportDto
{
    public int $companyId;
    public string $invoiceNumber;
    public string $issueDate;
    public string $supplierName;
    public string $supplierCnpj   = '';
    public string $supplierRegion;
    public string $supplierState  = '';
    public float $totalValue   = 0.0;
    public float $freightValue = 0.0;
    public float $taxValue     = 0.0;
    public array $items        = [];
}
