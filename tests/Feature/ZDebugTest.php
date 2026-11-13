<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_refreshes(): void
    {
        $this->assertDatabaseCount('users', 0);
    }
}
