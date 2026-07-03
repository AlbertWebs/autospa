<?php

namespace App\Data\Integrations;

readonly class WhatsAppResult
{
    public function __construct(
        public bool $success,
        public ?string $reference = null,
        public ?string $message = null,
    ) {}
}
