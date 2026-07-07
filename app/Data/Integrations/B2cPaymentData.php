<?php

namespace App\Data\Integrations;

readonly class B2cPaymentData
{
    public function __construct(
        public string $phone,
        public float $amount,
        public string $reference,
        public string $description,
    ) {}
}
