<?php

namespace App\Integrations\Sms;

use App\Contracts\Integrations\SmsDriverInterface;
use App\Data\Integrations\SmsMessage;
use App\Data\Integrations\SmsResult;
use Illuminate\Support\Str;

class SmsStubDriver implements SmsDriverInterface
{
    public function send(SmsMessage $message): SmsResult
    {
        return new SmsResult(true, (string) Str::uuid(), 'SMS queued (stub)');
    }
}
