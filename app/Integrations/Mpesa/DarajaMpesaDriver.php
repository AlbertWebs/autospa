<?php

namespace App\Integrations\Mpesa;

use App\Contracts\Integrations\MpesaDriverInterface;
use App\Data\Integrations\B2cPaymentData;
use App\Data\Integrations\B2cPaymentResult;
use App\Data\Integrations\MpesaAccountBalanceResult;
use App\Data\Integrations\StkPushData;
use App\Data\Integrations\StkPushResult;
use App\Services\MpesaLifecycleService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class DarajaMpesaDriver implements MpesaDriverInterface
{
    public function __construct(
        protected string $consumerKey,
        protected string $consumerSecret,
        protected string $shortCode,
        protected string $passkey,
        protected string $initiatorName,
        protected string $securityCredential,
        protected string $queueTimeoutUrl,
        protected string $resultUrl,
        protected string $stkResultUrl,
        protected string $balanceResultUrl,
        protected string $balanceTimeoutUrl,
        protected string $baseUrl = 'https://sandbox.safaricom.co.ke',
    ) {}

    public function initiateStkPush(StkPushData $data): StkPushResult
    {
        $response = $this->request('mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => $this->shortCode,
            'Password' => base64_encode($this->shortCode.$this->passkey.now()->format('YmdHis')),
            'Timestamp' => now()->format('YmdHis'),
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) round($data->amount),
            'PartyA' => $this->normalizePhone($data->phone),
            'PartyB' => $this->shortCode,
            'PhoneNumber' => $this->normalizePhone($data->phone),
            'CallBackURL' => $this->stkResultUrl,
            'AccountReference' => Str::limit($data->reference, 12, ''),
            'TransactionDesc' => Str::limit($data->description, 13, ''),
        ]);

        if (($response['ResponseCode'] ?? null) !== '0') {
            throw new RuntimeException((string) ($response['errorMessage'] ?? $response['ResponseDescription'] ?? 'STK push request failed.'));
        }

        $result = StkPushResult::pending((string) ($response['CheckoutRequestID'] ?? Str::uuid()));

        app(MpesaLifecycleService::class)->recordOutbound(
            flow: 'stk',
            branchId: null,
            reference: $result->transactionId,
            phone: $data->phone,
            amount: $data->amount,
            payload: $response,
        );

        return $result;
    }

    public function initiateB2cPayment(B2cPaymentData $data): B2cPaymentResult
    {
        $response = $this->request('mpesa/b2c/v1/paymentrequest', [
            'InitiatorName' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => 'BusinessPayment',
            'Amount' => (int) round($data->amount),
            'PartyA' => $this->shortCode,
            'PartyB' => $this->normalizePhone($data->phone),
            'Remarks' => Str::limit($data->description, 100, ''),
            'QueueTimeOutURL' => $this->queueTimeoutUrl,
            'ResultURL' => $this->resultUrl,
            'Occasion' => Str::limit($data->reference, 100, ''),
        ]);

        if (($response['ResponseCode'] ?? null) !== '0') {
            return B2cPaymentResult::failed((string) ($response['errorMessage'] ?? $response['ResponseDescription'] ?? 'B2C request failed.'));
        }

        $reference = (string) ($response['ConversationID'] ?? $response['OriginatorConversationID'] ?? ('B2C-'.Str::upper(Str::random(10))));

        app(MpesaLifecycleService::class)->recordOutbound(
            flow: 'b2c',
            branchId: null,
            reference: $reference,
            phone: $data->phone,
            amount: $data->amount,
            payload: $response,
        );

        return B2cPaymentResult::success(
            reference: $reference,
            message: (string) ($response['ResponseDescription'] ?? 'M-Pesa B2C request accepted.'),
        );
    }

    public function initiateAccountBalance(string $remarks = 'Account balance request'): MpesaAccountBalanceResult
    {
        $response = $this->request('mpesa/accountbalance/v1/query', [
            'Initiator' => $this->initiatorName,
            'SecurityCredential' => $this->securityCredential,
            'CommandID' => 'AccountBalance',
            'PartyA' => $this->shortCode,
            'IdentifierType' => '4',
            'Remarks' => Str::limit($remarks, 100, ''),
            'QueueTimeOutURL' => $this->balanceTimeoutUrl,
            'ResultURL' => $this->balanceResultUrl,
        ]);

        if (($response['ResponseCode'] ?? null) !== '0') {
            return MpesaAccountBalanceResult::failed((string) ($response['errorMessage'] ?? $response['ResponseDescription'] ?? 'Account balance request failed.'));
        }

        $reference = (string) ($response['ConversationID'] ?? $response['OriginatorConversationID'] ?? ('BAL-'.Str::upper(Str::random(10))));

        app(MpesaLifecycleService::class)->recordOutbound(
            flow: 'balance',
            branchId: null,
            reference: $reference,
            phone: null,
            amount: null,
            payload: $response,
        );

        return MpesaAccountBalanceResult::success(
            reference: $reference,
            message: (string) ($response['ResponseDescription'] ?? 'M-Pesa account balance request accepted.'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function request(string $endpoint, array $payload): array
    {
        $response = Http::withToken($this->accessToken())
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post(rtrim($this->baseUrl, '/').'/'.$endpoint, $payload);

        if (! $response->successful()) {
            $error = $response->json('errorMessage')
                ?? $response->json('error_description')
                ?? $response->body();

            throw new RuntimeException('Daraja API request failed: '.$error);
        }

        $decoded = $response->json();

        return is_array($decoded) ? $decoded : [];
    }

    protected function accessToken(): string
    {
        $response = Http::asForm()
            ->withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->acceptJson()
            ->timeout(30)
            ->get(rtrim($this->baseUrl, '/').'/oauth/v1/generate', [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Unable to authenticate with Daraja API.');
        }

        $token = (string) $response->json('access_token');

        if ($token === '') {
            throw new RuntimeException('Daraja API returned an empty access token.');
        }

        return $token;
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
