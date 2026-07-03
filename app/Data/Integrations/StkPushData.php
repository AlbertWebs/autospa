<?php

namespace App\Data\Integrations;

readonly class StkPushData
{
    public function __construct(
        public string $phone,
        public float $amount,
        public string $reference,
        public string $description,
    ) {}
}
