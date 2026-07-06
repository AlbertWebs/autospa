<?php

namespace Tests\Unit;

use App\Models\Vehicle;
use App\Support\RegistrationNumber;
use PHPUnit\Framework\TestCase;

class RegistrationNumberTest extends TestCase
{
    public function test_normalize_uppercases_and_trims(): void
    {
        $this->assertSame('KDA 123A', RegistrationNumber::normalize('kda 123a'));
        $this->assertSame('KAA 123A', RegistrationNumber::normalize('  kaa 123a  '));
        $this->assertNull(RegistrationNumber::normalize(null));
    }

    public function test_vehicle_model_stores_registration_in_uppercase(): void
    {
        $vehicle = new Vehicle([
            'registration_number' => 'kdj 902k',
        ]);

        $this->assertSame('KDJ 902K', $vehicle->registration_number);
    }
}
