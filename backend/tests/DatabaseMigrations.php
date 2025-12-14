<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

trait DatabaseMigrations
{

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
    }

    protected function tearDown(): void
    {
        DB::disconnect();
        parent::tearDown();
    }
}