<?php

namespace App\Data\Integrations;

readonly class SmsMessage
{
    public function __construct(
        public string $to,
        public string $message,
    ) {}
}
