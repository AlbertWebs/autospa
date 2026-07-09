<?php

namespace App\Services;

use App\Contracts\Integrations\MpesaDriverInterface;
use App\Contracts\Integrations\SmsDriverInterface;
use App\Integrations\Mpesa\DarajaMpesaDriver;
use App\Integrations\Mpesa\MpesaStubDriver;
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
            ->latest('id')
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

    public function mpesa(): MpesaDriverInterface
    {
        $integration = Integration::withoutGlobalScope(BranchScope::class)
            ->where('provider', 'mpesa')
            ->whereNull('branch_id')
            ->latest('id')
            ->first();

        $driver = $integration?->driver ?? config('integrations.mpesa.driver', 'stub');
        $enabled = (bool) ($integration?->is_enabled ?? false);

        if ($driver === 'daraja' && $enabled) {
            return $this->darajaDriver($integration);
        }

        if (config('integrations.mpesa.driver') === 'daraja' && $this->darajaConfigValue($integration, 'consumer_key') !== '') {
            return $this->darajaDriver($integration);
        }

        return new MpesaStubDriver();
    }

    protected function darajaDriver(?Integration $integration): DarajaMpesaDriver
    {
        return new DarajaMpesaDriver(
            consumerKey: $this->darajaConfigValue($integration, 'consumer_key'),
            consumerSecret: $this->darajaConfigValue($integration, 'consumer_secret'),
            shortCode: $this->darajaConfigValue($integration, 'shortcode'),
            passkey: $this->darajaConfigValue($integration, 'passkey'),
            initiatorName: $this->darajaConfigValue($integration, 'initiator_name'),
            securityCredential: $this->darajaConfigValue($integration, 'security_credential'),
            queueTimeoutUrl: $this->darajaConfigValue($integration, 'queue_timeout_url'),
            resultUrl: $this->darajaConfigValue($integration, 'result_url'),
            stkResultUrl: $this->darajaConfigValue($integration, 'stk_result_url'),
            balanceResultUrl: $this->darajaConfigValue($integration, 'balance_result_url'),
            balanceTimeoutUrl: $this->darajaConfigValue($integration, 'balance_timeout_url'),
            baseUrl: $this->darajaConfigValue($integration, 'base_url'),
        );
    }

    protected function darajaConfigValue(?Integration $integration, string $key): string
    {
        return (string) (
            $integration?->credentials[$key]
            ?? $integration?->settings[$key]
            ?? config("integrations.mpesa.daraja.{$key}")
            ?? ''
        );
    }

    public function whatsapp(): \App\Contracts\Integrations\WhatsAppDriverInterface
    {
        return match (config('integrations.whatsapp.driver')) {
            'stub' => new \App\Integrations\WhatsApp\WhatsAppStubDriver(),
            default => new \App\Integrations\WhatsApp\WhatsAppStubDriver(),
        };
    }
}
