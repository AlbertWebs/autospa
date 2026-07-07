<?php

namespace App\Integrations\Mpesa;

use App\Contracts\Integrations\MpesaDriverInterface;
use App\Data\Integrations\B2cOtpConfirmData;
use App\Data\Integrations\B2cPaymentData;
use App\Data\Integrations\B2cPaymentResult;
use App\Data\Integrations\StkPushData;
use App\Data\Integrations\StkPushResult;
use Illuminate\Support\Str;

class MpesaStubDriver implements MpesaDriverInterface
{
    public function initiateStkPush(StkPushData $data): StkPushResult
    {
        return StkPushResult::pending((string) Str::uuid());
    }

    public function initiateB2cPayment(B2cPaymentData $data): B2cPaymentResult
    {
        return B2cPaymentResult::success(
            reference: 'B2C-'.strtoupper(Str::random(10)),
            message: 'Commission payout queued via M-Pesa.',
        );
    }
}
