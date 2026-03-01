<?php

test('POST /preview requires url', function () {
    $this->postJson(route('lens-for-laravel.preview'), ['selector' => 'img'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('POST /preview requires selector', function () {
    $this->postJson(route('lens-for-laravel.preview'), ['url' => 'https://example.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['selector']);
});

test('POST /preview rejects selector longer than 500 characters', function () {
    $this->postJson(route('lens-for-laravel.preview'), [
        'url' => 'https://example.com',
        'selector' => str_repeat('a', 501),
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['selector']);
});

test('POST /preview returns 403 when environment not allowed', function () {
    $this->app['config']->set('lens-for-laravel.enabled_environments', ['local']);

    $this->postJson(route('lens-for-laravel.preview'), [
        'url' => 'https://example.com',
        'selector' => 'img',
    ])->assertStatus(403);
});

test('POST /preview returns error json when browsershot fails', function () {
    // Without mocking Browsershot, it will throw — the route catches Throwable and returns JSON
    $this->postJson(route('lens-for-laravel.preview'), [
        'url' => 'https://example.com',
        'selector' => 'img.logo',
    ])->assertStatus(500)
        ->assertJson(['status' => 'error']);
});
