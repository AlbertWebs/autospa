<?php

namespace App\Services;

use App\Contracts\Integrations\SmsDriverInterface;
use App\Integrations\Sms\RebueTextSmsDriver;
use App\Integrations\Sms\SmsStubDriver;
use App\Models\Integration;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;

class IntegrationService
{
    public function sms(): SmsDriverInterface
    {
        $integration = Integration::withoutGlobalScope(BranchScope::class)
            ->where('provider', 'sms')
            ->whereNull('branch_id')
            ->first();

        $driver = $integration?->driver ?? config('integrations.sms.driver', 'stub');
        $enabled = (bool) ($integration?->is_enabled ?? false);

        if ($driver === 'rebuetext' && $enabled) {
            return $this->rebueTextDriver($integration);
        }

        if (config('integrations.sms.driver') === 'rebuetext' && $this->rebueTextAccessToken($integration) !== '') {
            return $this->rebueTextDriver($integration);
        }

        return new SmsStubDriver();
    }

    protected function rebueTextDriver(?Integration $integration): RebueTextSmsDriver
    {
        return new RebueTextSmsDriver(
            accessToken: $this->rebueTextAccessToken($integration),
            senderId: $this->rebueTextSenderId($integration),
            baseUrl: (string) config('integrations.sms.rebuetext.base_url', 'https://rebuetext.com/api/v1'),
        );
    }

    protected function rebueTextAccessToken(?Integration $integration): string
    {
        return (string) (
            $integration?->credentials['access_token']
            ?? config('integrations.sms.rebuetext.access_token')
            ?? ''
        );
    }

    protected function rebueTextSenderId(?Integration $integration): string
    {
        return (string) (
            $integration?->settings['sender_id']
            ?? Setting::getValue('sms', 'sender_id', config('integrations.sms.rebuetext.sender_id'))
            ?? ''
        );
    }

    public function mpesa(): \App\Contracts\Integrations\MpesaDriverInterface
    {
        return match (config('integrations.mpesa.driver')) {
            'stub' => new \App\Integrations\Mpesa\MpesaStubDriver(),
            default => new \App\Integrations\Mpesa\MpesaStubDriver(),
        };
    }

    public function whatsapp(): \App\Contracts\Integrations\WhatsAppDriverInterface
    {
        return match (config('integrations.whatsapp.driver')) {
            'stub' => new \App\Integrations\WhatsApp\WhatsAppStubDriver(),
            default => new \App\Integrations\WhatsApp\WhatsAppStubDriver(),
        };
    }
}
