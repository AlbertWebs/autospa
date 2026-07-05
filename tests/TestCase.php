<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\InstallsApplication;

abstract class TestCase extends BaseTestCase
{
    use InstallsApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->maybeInstallApplication();
    }
}
