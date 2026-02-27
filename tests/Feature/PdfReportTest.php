<?php

test('POST /report/pdf requires issues array', function () {
    $this->postJson(route('laravel-lens.report.pdf'), ['url' => 'https://example.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['issues']);
});

test('POST /report/pdf requires url', function () {
    $this->postJson(route('laravel-lens.report.pdf'), ['issues' => []])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('POST /report/pdf returns 403 when environment not allowed', function () {
    $this->app['config']->set('laravel-lens.enabled_environments', ['local']);

    $this->postJson(route('laravel-lens.report.pdf'), [
        'issues' => [],
        'url' => 'https://example.com',
    ])->assertStatus(403);
});

test('POST /report/pdf returns error json when browsershot fails', function () {
    // Without headless Chrome, Browsershot throws — the route catches Throwable
    $this->postJson(route('laravel-lens.report.pdf'), [
        'issues' => [
            [
                'id' => 'image-alt',
                'impact' => 'critical',
                'description' => 'Images must have alt text',
                'htmlSnippet' => '<img src="x.png">',
                'selector' => 'img',
                'tags' => ['wcag2a'],
            ],
        ],
        'url' => 'https://example.com',
    ])->assertStatus(500)
        ->assertJson(['status' => 'error']);
});
