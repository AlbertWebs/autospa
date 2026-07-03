<?php

namespace App\Integrations\WhatsApp;

use App\Contracts\Integrations\WhatsAppDriverInterface;
use App\Data\Integrations\WhatsAppMessage;
use App\Data\Integrations\WhatsAppResult;
use Illuminate\Support\Str;

class WhatsAppStubDriver implements WhatsAppDriverInterface
{
    public function send(WhatsAppMessage $message): WhatsAppResult
    {
        return new WhatsAppResult(true, (string) Str::uuid(), 'WhatsApp message queued (stub)');
    }
}
