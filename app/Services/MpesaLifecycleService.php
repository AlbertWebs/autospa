<?php

namespace App\Services;

use App\Enums\PaymentMethodType;
use App\Models\Commission;
use App\Models\MpesaTransaction;
use App\Models\Payment;

class MpesaLifecycleService
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function recordOutbound(
        string $flow,
        ?int $branchId,
        ?string $reference,
        ?string $phone,
        ?float $amount,
        array $payload
    ): void {
        MpesaTransaction::query()->create([
            'branch_id' => $branchId,
            'flow' => $flow,
            'direction' => 'outbound',
            'reference' => $reference,
            'conversation_id' => $payload['ConversationID'] ?? null,
            'originator_conversation_id' => $payload['OriginatorConversationID'] ?? null,
            'checkout_request_id' => $payload['CheckoutRequestID'] ?? null,
            'merchant_request_id' => $payload['MerchantRequestID'] ?? null,
            'phone' => $phone,
            'amount' => $amount,
            'status' => 'accepted',
            'result_code' => (string) ($payload['ResponseCode'] ?? ''),
            'result_description' => $payload['ResponseDescription'] ?? null,
            'payload' => $payload,
            'processed_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleResult(array $payload): void
    {
        if (isset($payload['Body']['stkCallback'])) {
            $this->handleStkResult($payload);

            return;
        }

        $conversationId = (string) ($payload['Result']['ConversationID'] ?? '');
        $originatorConversationId = (string) ($payload['Result']['OriginatorConversationID'] ?? '');

        $outbound = MpesaTransaction::query()
            ->where('direction', 'outbound')
            ->where(function ($query) use ($conversationId, $originatorConversationId) {
                if ($conversationId !== '') {
                    $query->orWhere('conversation_id', $conversationId)
                        ->orWhere('reference', $conversationId);
                }

                if ($originatorConversationId !== '') {
                    $query->orWhere('originator_conversation_id', $originatorConversationId)
                        ->orWhere('reference', $originatorConversationId);
                }
            })
            ->latest('id')
            ->first();

        if ($outbound?->flow === 'balance') {
            $this->handleBalanceResult($payload);

            return;
        }

        $this->handleB2cResult($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleStkResult(array $payload): void
    {
        $callback = $payload['Body']['stkCallback'] ?? [];
        $checkoutRequestId = (string) ($callback['CheckoutRequestID'] ?? '');

        if ($checkoutRequestId === '') {
            MpesaTransaction::query()->create([
                'flow' => 'stk',
                'direction' => 'inbound',
                'status' => 'invalid',
                'payload' => $payload,
                'result_description' => 'Missing CheckoutRequestID in STK callback.',
                'processed_at' => now(),
            ]);

            return;
        }

        $metadataItems = $callback['CallbackMetadata']['Item'] ?? [];
        $metadata = $this->extractMetadata($metadataItems);
        $mpesaReceipt = $metadata['MpesaReceiptNumber'] ?? null;
        $amount = isset($metadata['Amount']) ? (float) $metadata['Amount'] : null;
        $phone = isset($metadata['PhoneNumber']) ? (string) $metadata['PhoneNumber'] : null;
        $status = ((string) ($callback['ResultCode'] ?? '1')) === '0' ? 'success' : 'failed';

        MpesaTransaction::query()->create([
            'flow' => 'stk',
            'direction' => 'inbound',
            'reference' => $checkoutRequestId,
            'checkout_request_id' => $checkoutRequestId,
            'merchant_request_id' => $callback['MerchantRequestID'] ?? null,
            'phone' => $phone,
            'amount' => $amount,
            'status' => $status,
            'result_code' => (string) ($callback['ResultCode'] ?? ''),
            'result_description' => $callback['ResultDesc'] ?? null,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        MpesaTransaction::query()
            ->where('flow', 'stk')
            ->where('direction', 'outbound')
            ->where(function ($query) use ($checkoutRequestId) {
                $query->where('reference', $checkoutRequestId)
                    ->orWhere('checkout_request_id', $checkoutRequestId);
            })
            ->latest('id')
            ->limit(1)
            ->update([
                'status' => $status,
                'result_code' => (string) ($callback['ResultCode'] ?? ''),
                'result_description' => $callback['ResultDesc'] ?? null,
                'processed_at' => now(),
            ]);

        $payment = Payment::query()
            ->where('method', PaymentMethodType::Mpesa)
            ->where(function ($query) use ($checkoutRequestId) {
                $query->where('reference', $checkoutRequestId)
                    ->orWhere('metadata->stk_checkout_request_id', $checkoutRequestId);
            })
            ->latest('id')
            ->first();

        if ($payment) {
            $metadataPayload = is_array($payment->metadata) ? $payment->metadata : [];
            $metadataPayload['stk_callback'] = [
                'result_code' => (string) ($callback['ResultCode'] ?? ''),
                'result_desc' => $callback['ResultDesc'] ?? null,
                'mpesa_receipt' => $mpesaReceipt,
                'amount' => $amount,
                'phone' => $phone,
            ];

            $payment->update([
                'status' => $status === 'success' ? 'completed' : 'failed',
                'reference' => $mpesaReceipt ?: $payment->reference,
                'metadata' => $metadataPayload,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleB2cResult(array $payload): void
    {
        $result = $payload['Result'] ?? [];
        $conversationId = (string) ($result['ConversationID'] ?? '');
        $originatorConversationId = (string) ($result['OriginatorConversationID'] ?? '');
        $reference = $conversationId !== '' ? $conversationId : $originatorConversationId;
        $resultCode = (string) ($result['ResultCode'] ?? '');
        $status = $resultCode === '0' ? 'success' : 'failed';

        MpesaTransaction::query()->create([
            'flow' => 'b2c',
            'direction' => 'inbound',
            'reference' => $reference !== '' ? $reference : null,
            'conversation_id' => $conversationId !== '' ? $conversationId : null,
            'originator_conversation_id' => $originatorConversationId !== '' ? $originatorConversationId : null,
            'status' => $status,
            'result_code' => $resultCode,
            'result_description' => $result['ResultDesc'] ?? null,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        MpesaTransaction::query()
            ->where('flow', 'b2c')
            ->where('direction', 'outbound')
            ->when($reference !== '', function ($query) use ($conversationId, $originatorConversationId, $reference) {
                $query->where(function ($inner) use ($conversationId, $originatorConversationId, $reference) {
                    if ($conversationId !== '') {
                        $inner->orWhere('conversation_id', $conversationId)->orWhere('reference', $conversationId);
                    }
                    if ($originatorConversationId !== '') {
                        $inner->orWhere('originator_conversation_id', $originatorConversationId)->orWhere('reference', $originatorConversationId);
                    }
                    if ($reference !== '') {
                        $inner->orWhere('reference', $reference);
                    }
                });
            })
            ->update([
                'status' => $status,
                'result_code' => $resultCode,
                'result_description' => $result['ResultDesc'] ?? null,
                'processed_at' => now(),
            ]);

        if ($status !== 'success' || $reference === '') {
            return;
        }

        Commission::query()
            ->where('payment_method', 'mpesa')
            ->where('payment_reference', $reference)
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleBalanceResult(array $payload): void
    {
        $result = $payload['Result'] ?? [];
        $conversationId = (string) ($result['ConversationID'] ?? '');
        $originatorConversationId = (string) ($result['OriginatorConversationID'] ?? '');
        $reference = $conversationId !== '' ? $conversationId : $originatorConversationId;
        $resultCode = (string) ($result['ResultCode'] ?? '');
        $status = $resultCode === '0' ? 'success' : 'failed';

        MpesaTransaction::query()->create([
            'flow' => 'balance',
            'direction' => 'inbound',
            'reference' => $reference !== '' ? $reference : null,
            'conversation_id' => $conversationId !== '' ? $conversationId : null,
            'originator_conversation_id' => $originatorConversationId !== '' ? $originatorConversationId : null,
            'status' => $status,
            'result_code' => $resultCode,
            'result_description' => $result['ResultDesc'] ?? null,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        MpesaTransaction::query()
            ->where('flow', 'balance')
            ->where('direction', 'outbound')
            ->when($reference !== '', function ($query) use ($conversationId, $originatorConversationId, $reference) {
                $query->where(function ($inner) use ($conversationId, $originatorConversationId, $reference) {
                    if ($conversationId !== '') {
                        $inner->orWhere('conversation_id', $conversationId)->orWhere('reference', $conversationId);
                    }
                    if ($originatorConversationId !== '') {
                        $inner->orWhere('originator_conversation_id', $originatorConversationId)->orWhere('reference', $originatorConversationId);
                    }
                    if ($reference !== '') {
                        $inner->orWhere('reference', $reference);
                    }
                });
            })
            ->update([
                'status' => $status,
                'result_code' => $resultCode,
                'result_description' => $result['ResultDesc'] ?? null,
                'processed_at' => now(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleTimeout(string $flow, array $payload): void
    {
        $conversationId = (string) ($payload['ConversationID'] ?? '');
        $originatorConversationId = (string) ($payload['OriginatorConversationID'] ?? '');
        $reference = $conversationId !== '' ? $conversationId : $originatorConversationId;

        MpesaTransaction::query()->create([
            'flow' => $flow,
            'direction' => 'inbound',
            'reference' => $reference !== '' ? $reference : null,
            'conversation_id' => $conversationId !== '' ? $conversationId : null,
            'originator_conversation_id' => $originatorConversationId !== '' ? $originatorConversationId : null,
            'status' => 'timeout',
            'result_description' => 'Daraja callback timed out.',
            'payload' => $payload,
            'processed_at' => now(),
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    protected function extractMetadata(array $items): array
    {
        $metadata = [];

        foreach ($items as $item) {
            $name = $item['Name'] ?? null;

            if (! is_string($name) || $name === '') {
                continue;
            }

            $metadata[$name] = $item['Value'] ?? null;
        }

        return $metadata;
    }
}
