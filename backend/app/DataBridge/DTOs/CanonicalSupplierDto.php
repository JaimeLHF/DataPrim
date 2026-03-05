<?php

namespace App\DataBridge\DTOs;

class CanonicalSupplierDto
{
    public function __construct(
        public string $cnpj,
        public string $name,
        public string $region = 'Desconhecida',
        public string $state = '',
    ) {}
}
