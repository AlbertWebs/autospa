<?php

namespace App\Http\Requests\Setup;

use App\Services\InstallService;
use Illuminate\Foundation\Http\FormRequest;

abstract class SetupStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! app(InstallService::class)->isInstalled();
    }
}
