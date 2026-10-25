<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SanityTest extends TestCase
{
    public function test_tests_use_the_isolated_test_database(): void
    {
        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('aclog_test', config('database.connections.mysql.database'));
        $this->assertSame('aclog_test', DB::connection()->getDatabaseName());
        $this->assertSame('testing', app()->environment());
    }

    public function test_gemini_key_is_configured_for_tests(): void
    {
        $this->assertSame('test-gemini-key', config('services.gemini.api_key'));
    }
}
