<?php

namespace App\Contracts\Integrations;

use App\Data\Integrations\StkPushData;
use App\Data\Integrations\StkPushResult;

interface MpesaDriverInterface
{
    public function initiateStkPush(StkPushData $data): StkPushResult;
}
