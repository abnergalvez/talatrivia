<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Artisan;

trait RefreshDatabase
{
    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed');
    }
}
