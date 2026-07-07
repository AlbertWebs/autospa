<?php

namespace App\Contracts\Integrations;

use App\Data\Integrations\B2cPaymentData;
use App\Data\Integrations\B2cPaymentResult;
use App\Data\Integrations\StkPushData;
use App\Data\Integrations\StkPushResult;

interface MpesaDriverInterface
{
    public function initiateStkPush(StkPushData $data): StkPushResult;

    public function initiateB2cPayment(B2cPaymentData $data): B2cPaymentResult;
}
