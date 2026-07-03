<?php

namespace App\Integrations\Mpesa;

use App\Contracts\Integrations\MpesaDriverInterface;
use App\Data\Integrations\StkPushData;
use App\Data\Integrations\StkPushResult;
use Illuminate\Support\Str;

class MpesaStubDriver implements MpesaDriverInterface
{
    public function initiateStkPush(StkPushData $data): StkPushResult
    {
        return StkPushResult::pending((string) Str::uuid());
    }
}
