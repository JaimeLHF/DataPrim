<?php

namespace App\DataBridge\DTOs;

class CanonicalItemDto
{
    public function __construct(
        public string $description,
        public string $category,
        public float $quantity,
        public float $unitPrice,
        public float $totalPrice,
    ) {}
}
