<?php

namespace App\Data\Integrations;

readonly class SmsResult
{
    public function __construct(
        public bool $success,
        public ?string $reference = null,
        public ?string $message = null,
    ) {}
}
