<?php

namespace App\Integrations\Mpesa;

use App\Contracts\Integrations\MpesaDriverInterface;
use App\Data\Integrations\B2cOtpConfirmData;
use App\Data\Integrations\B2cPaymentData;
use App\Data\Integrations\B2cPaymentResult;
use App\Data\Integrations\MpesaAccountBalanceResult;
use App\Data\Integrations\StkPushData;
use App\Data\Integrations\StkPushResult;
use App\Services\MpesaLifecycleService;
use Illuminate\Support\Str;

class MpesaStubDriver implements MpesaDriverInterface
{
    public function initiateStkPush(StkPushData $data): StkPushResult
    {
        $transactionId = (string) Str::uuid();

        app(MpesaLifecycleService::class)->recordOutbound(
            flow: 'stk',
            branchId: null,
            reference: $transactionId,
            phone: $data->phone,
            amount: $data->amount,
            payload: [
                'CheckoutRequestID' => $transactionId,
                'ResponseCode' => '0',
                'ResponseDescription' => 'STK push initiated',
            ],
        );

        return StkPushResult::pending($transactionId);
    }

    public function initiateB2cPayment(B2cPaymentData $data): B2cPaymentResult
    {
        $reference = 'B2C-'.strtoupper(Str::random(10));

        app(MpesaLifecycleService::class)->recordOutbound(
            flow: 'b2c',
            branchId: null,
            reference: $reference,
            phone: $data->phone,
            amount: $data->amount,
            payload: [
                'ConversationID' => $reference,
                'ResponseCode' => '0',
                'ResponseDescription' => 'Commission payout queued via M-Pesa.',
            ],
        );

        return B2cPaymentResult::success(
            reference: $reference,
            message: 'Commission payout queued via M-Pesa.',
        );
    }

    public function initiateAccountBalance(string $remarks = 'Account balance request'): MpesaAccountBalanceResult
    {
        $reference = 'BAL-'.strtoupper(Str::random(10));

        app(MpesaLifecycleService::class)->recordOutbound(
            flow: 'balance',
            branchId: null,
            reference: $reference,
            phone: null,
            amount: null,
            payload: [
                'ConversationID' => $reference,
                'ResponseCode' => '0',
                'ResponseDescription' => 'Account balance request queued via M-Pesa.',
            ],
        );

        return MpesaAccountBalanceResult::success(
            reference: $reference,
            message: 'Account balance request queued via M-Pesa.',
        );
    }
}
