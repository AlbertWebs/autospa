<?php

namespace App\Data\Integrations;

readonly class StkPushResult
{
    public function __construct(
        public string $status,
        public ?string $transactionId = null,
        public ?string $message = null,
    ) {}

    public static function pending(string $transactionId): self
    {
        return new self('pending', $transactionId, 'STK push initiated');
    }
}
