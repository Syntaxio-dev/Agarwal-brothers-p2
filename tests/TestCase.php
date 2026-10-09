<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Mail;

abstract class TestCase extends BaseTestCase
{
    /** Runs before any trait (RefreshDatabase) can touch the database. */
    public function createApplication()
    {
        $app = parent::createApplication();

        $db = (string) $app['config']->get('database.connections.' . $app['config']->get('database.default') . '.database');
        if (! str_ends_with($db, '_testing')) {
            throw new \RuntimeException("Refusing to run tests against database [$db]. Use a *_testing database.");
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }
}
