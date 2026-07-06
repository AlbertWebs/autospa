<?php

namespace App\Services;

use App\Models\Company;

class CompanyService
{
    protected ?Company $company = null;

    protected bool $resolved = false;

    public function company(): ?Company
    {
        if ($this->resolved) {
            return $this->company;
        }

        $this->resolved = true;
        $this->company = Company::query()->first();

        return $this->company;
    }

    public function displayName(): string
    {
        $name = $this->company()?->name;

        return $name !== null && trim($name) !== ''
            ? trim($name)
            : config('app.name');
    }
}
