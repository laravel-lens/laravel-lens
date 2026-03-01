<?php

test('dashboard returns 200 in testing environment', function () {
    $this->get(route('lens-for-laravel.dashboard'))
        ->assertStatus(200);
});

test('dashboard returns 403 when environment is not in allowed list', function () {
    // Remove 'testing' from allowed envs — the app still runs under 'testing'
    $this->app['config']->set('lens-for-laravel.enabled_environments', ['local']);

    $this->get(route('lens-for-laravel.dashboard'))
        ->assertStatus(403);
});

test('dashboard renders the main blade view', function () {
    $response = $this->get(route('lens-for-laravel.dashboard'));

    $response->assertStatus(200)
        ->assertSee('Lens For Laravel');
});
