<?php

namespace App\Contracts\Integrations;

use App\Data\Integrations\SmsMessage;
use App\Data\Integrations\SmsResult;

interface SmsDriverInterface
{
    public function send(SmsMessage $message): SmsResult;
}
