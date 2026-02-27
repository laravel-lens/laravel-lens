<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use LaravelLens\LaravelLens\LaravelLensServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [LaravelLensServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('laravel-lens.enabled_environments', ['testing']);
        $app['config']->set('laravel-lens.route_prefix', 'laravel-lens');
        $app['config']->set('laravel-lens.middleware', ['web']);
        $app['config']->set('laravel-lens.crawl_max_pages', 5);
        $app['config']->set('laravel-lens.editor', 'vscode');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CSRF for all POST route tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
