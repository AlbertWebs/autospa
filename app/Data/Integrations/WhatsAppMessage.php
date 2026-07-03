<?php

namespace App\Data\Integrations;

readonly class WhatsAppMessage
{
    public function __construct(
        public string $to,
        public string $message,
    ) {}
}
