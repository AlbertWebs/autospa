<?php

namespace App\Services;

use App\Contracts\Integrations\MpesaDriverInterface;
use App\Contracts\Integrations\SmsDriverInterface;
use App\Contracts\Integrations\WhatsAppDriverInterface;
use App\Integrations\Mpesa\MpesaStubDriver;
use App\Integrations\Sms\SmsStubDriver;
use App\Integrations\WhatsApp\WhatsAppStubDriver;

class IntegrationService
{
    public function mpesa(): MpesaDriverInterface
    {
        return match (config('integrations.mpesa.driver')) {
            'stub', default => new MpesaStubDriver,
        };
    }

    public function sms(): SmsDriverInterface
    {
        return match (config('integrations.sms.driver')) {
            'stub', default => new SmsStubDriver,
        };
    }

    public function whatsapp(): WhatsAppDriverInterface
    {
        return match (config('integrations.whatsapp.driver')) {
            'stub', default => new WhatsAppStubDriver,
        };
    }
}
