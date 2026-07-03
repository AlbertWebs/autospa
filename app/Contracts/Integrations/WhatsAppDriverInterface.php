<?php

namespace App\Contracts\Integrations;

use App\Data\Integrations\WhatsAppMessage;
use App\Data\Integrations\WhatsAppResult;

interface WhatsAppDriverInterface
{
    public function send(WhatsAppMessage $message): WhatsAppResult;
}
