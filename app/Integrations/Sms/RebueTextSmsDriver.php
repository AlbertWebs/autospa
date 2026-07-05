<?php

namespace App\Integrations\Sms;

use App\Contracts\Integrations\SmsDriverInterface;
use App\Data\Integrations\SmsMessage;
use App\Data\Integrations\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RebueTextSmsDriver implements SmsDriverInterface
{
    public function __construct(
        protected string $accessToken,
        protected string $senderId,
        protected string $baseUrl = 'https://rebuetext.com/api/v1',
    ) {}

    public function send(SmsMessage $message): SmsResult
    {
        if ($this->accessToken === '' || $this->senderId === '') {
            return new SmsResult(false, null, 'RebueText SMS is not configured.');
        }

        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post(rtrim($this->baseUrl, '/').'/send-sms', [
                'sender' => $this->senderId,
                'message' => $message->message,
                'phone' => $this->normalizePhone($message->to),
                'correlator' => (string) Str::uuid(),
            ]);

        if (! $response->successful()) {
            return new SmsResult(
                false,
                null,
                $response->json('message') ?? 'RebueText SMS request failed.',
            );
        }

        $payload = $response->json();
        $result = is_array($payload) && array_is_list($payload)
            ? ($payload[0] ?? [])
            : (is_array($payload) ? $payload : []);

        return new SmsResult(
            (bool) ($result['status'] ?? false),
            $result['data']['uniqueId'] ?? null,
            $result['message'] ?? ($result['data']['remarks'] ?? null),
        );
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '254')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '254'.substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '254'.$digits;
        }

        return $digits;
    }
}
