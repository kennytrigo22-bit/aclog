<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\MakesData;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;
    use MakesData;

    /**
     * Garde-fou : on ne laisse JAMAIS migrate:fresh toucher une autre base que aclog_test.
     * Appele par RefreshDatabase avant de migrer.
     */
    protected function beforeRefreshingDatabase()
    {
        $default = config('database.default');
        $db = config("database.connections.{$default}.database");

        if ($default !== 'mysql' || $db !== 'aclog_test') {
            throw new \RuntimeException("SECURITE: les tests doivent utiliser mysql/aclog_test, pas {$default}/{$db}.");
        }
    }

    protected function setUp(): void
    {
        // DomPDF (certificat) est gourmand avec les images du gabarit.
        ini_set('memory_limit', '768M');

        parent::setUp();

        config(['services.gemini.api_key' => 'test-gemini-key']);
        config(['services.gemini.model' => 'gemini-test']);
    }
}
