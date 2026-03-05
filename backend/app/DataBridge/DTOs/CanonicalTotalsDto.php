<?php

namespace App\DataBridge\DTOs;

class CanonicalTotalsDto
{
    public function __construct(
        public float $goodsValue = 0.0,
        public float $freightValue = 0.0,
        public float $taxValue = 0.0,
        public float $totalValue = 0.0,
    ) {}
}
