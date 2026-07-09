<?php

namespace App\Services;

use App\Data\Integrations\SmsMessage;
use App\Data\Integrations\B2cPaymentData;
use App\Data\Integrations\StkPushData;
use App\Data\Integrations\WhatsAppMessage;
use App\Models\Integration;
use App\Models\Scopes\BranchScope;
use App\Support\EmailSettings;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class IntegrationTestGroundService
{
    public function __construct(
        protected IntegrationService $integrationService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $integrations = Integration::withoutGlobalScope(BranchScope::class)
            ->whereNull('branch_id')
            ->get()
            ->keyBy('provider');

        return [
            'email' => [
                'enabled' => EmailSettings::enabled(),
                'driver' => config('mail.default'),
            ],
            'sms' => $this->channelStatus($integrations->get('sms'), config('integrations.sms.driver', 'stub')),
            'whatsapp' => $this->channelStatus($integrations->get('whatsapp'), config('integrations.whatsapp.driver', 'stub')),
            'mpesa' => $this->channelStatus($integrations->get('mpesa'), config('integrations.mpesa.driver', 'stub')),
        ];
    }

    /**
     * @return array{success: bool, message: string, details?: array<string, mixed>}
     */
    public function sendEmail(string $to, string $message, ?string $subject = null): array
    {
        $subject = $subject ?: 'AutoSpa test email';

        try {
            app()->instance('integration_test_bypass_email', true);

            Mail::raw($message, function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);
            });

            return [
                'success' => true,
                'message' => 'Test email was handed off to the mail transport.',
                'details' => [
                    'driver' => config('mail.default'),
                    'recipient' => $to,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'Email test failed: '.$e->getMessage(),
            ];
        } finally {
            app()->forgetInstance('integration_test_bypass_email');
        }
    }

    /**
     * @return array{success: bool, message: string, details?: array<string, mixed>}
     */
    public function sendSms(string $phone, string $message): array
    {
        try {
            $driver = $this->integrationService->sms();
            $result = $driver->send(new SmsMessage($phone, $message));

            return [
                'success' => $result->success,
                'message' => $result->message ?? ($result->success ? 'SMS sent successfully.' : 'SMS send failed.'),
                'details' => [
                    'driver' => class_basename($driver),
                    'reference' => $result->reference,
                    'recipient' => $phone,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'SMS test failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, details?: array<string, mixed>}
     */
    public function sendWhatsApp(string $phone, string $message): array
    {
        try {
            $driver = $this->integrationService->whatsapp();
            $result = $driver->send(new WhatsAppMessage($phone, $message));

            return [
                'success' => $result->success,
                'message' => $result->message ?? ($result->success ? 'WhatsApp message sent successfully.' : 'WhatsApp send failed.'),
                'details' => [
                    'driver' => class_basename($driver),
                    'reference' => $result->reference,
                    'recipient' => $phone,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'WhatsApp test failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, details?: array<string, mixed>}
     */
    public function sendMpesaStk(string $phone, float $amount): array
    {
        try {
            $driver = $this->integrationService->mpesa();
            $reference = 'TEST-'.Str::upper(Str::random(8));
            $result = $driver->initiateStkPush(new StkPushData(
                phone: $phone,
                amount: $amount,
                reference: $reference,
                description: 'AutoSpa integration test',
            ));

            return [
                'success' => in_array($result->status, ['pending', 'success'], true),
                'message' => $result->message ?? 'STK push initiated.',
                'details' => [
                    'driver' => class_basename($driver),
                    'status' => $result->status,
                    'transaction_id' => $result->transactionId,
                    'reference' => $reference,
                    'recipient' => $phone,
                    'amount' => $amount,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'M-Pesa test failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, details?: array<string, mixed>}
     */
    public function sendMpesaB2c(string $phone, float $amount): array
    {
        try {
            $driver = $this->integrationService->mpesa();
            $reference = 'B2C-TEST-'.Str::upper(Str::random(6));
            $result = $driver->initiateB2cPayment(new B2cPaymentData(
                phone: $phone,
                amount: $amount,
                reference: $reference,
                description: 'AutoSpa integration B2C test',
            ));

            return [
                'success' => $result->successful,
                'message' => $result->message ?? ($result->successful ? 'B2C payout initiated.' : 'B2C payout failed.'),
                'details' => [
                    'driver' => class_basename($driver),
                    'reference' => $result->reference ?? $reference,
                    'recipient' => $phone,
                    'amount' => $amount,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'M-Pesa B2C test failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{success: bool, message: string, details?: array<string, mixed>}
     */
    public function requestMpesaBalance(): array
    {
        try {
            $driver = $this->integrationService->mpesa();
            $result = $driver->initiateAccountBalance('AutoSpa integration balance test');

            return [
                'success' => $result->successful,
                'message' => $result->message ?? ($result->successful ? 'Account balance request initiated.' : 'Account balance request failed.'),
                'details' => [
                    'driver' => class_basename($driver),
                    'reference' => $result->reference,
                ],
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'M-Pesa balance test failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @return array{enabled: bool, driver: string}
     */
    protected function channelStatus(?Integration $integration, string $fallbackDriver): array
    {
        return [
            'enabled' => (bool) ($integration?->is_enabled ?? false),
            'driver' => $integration?->driver ?? $fallbackDriver,
        ];
    }
}
