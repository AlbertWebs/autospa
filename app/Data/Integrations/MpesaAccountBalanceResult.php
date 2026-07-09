<?php

namespace App\Data\Integrations;

readonly class MpesaAccountBalanceResult
{
    public function __construct(
        public bool $successful,
        public ?string $reference = null,
        public ?string $message = null,
    ) {}

    public static function success(string $reference, ?string $message = null): self
    {
        return new self(true, $reference, $message);
    }

    public static function failed(?string $message = null): self
    {
        return new self(false, null, $message);
    }
}
