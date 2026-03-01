<?php

use LensForLaravel\LensForLaravel\Services\SiteCrawler;

test('POST /crawl requires url', function () {
    $this->postJson(route('lens-for-laravel.crawl'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('POST /crawl rejects invalid url format', function () {
    $this->postJson(route('lens-for-laravel.crawl'), ['url' => 'not-a-url'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('POST /crawl returns 403 when environment not allowed', function () {
    $this->app['config']->set('lens-for-laravel.enabled_environments', ['local']);

    $this->postJson(route('lens-for-laravel.crawl'), ['url' => 'https://example.com'])
        ->assertStatus(403);
});

test('POST /crawl returns discovered urls on success', function () {
    $crawlerMock = Mockery::mock(SiteCrawler::class);
    $crawlerMock->shouldReceive('crawl')
        ->once()
        ->andReturn(['https://example.com', 'https://example.com/about']);
    app()->instance(SiteCrawler::class, $crawlerMock);

    $this->postJson(route('lens-for-laravel.crawl'), ['url' => 'https://example.com'])
        ->assertStatus(200)
        ->assertJson([
            'status' => 'success',
            'urls' => ['https://example.com', 'https://example.com/about'],
        ]);
});

test('POST /crawl returns 500 on crawler failure', function () {
    $crawlerMock = Mockery::mock(SiteCrawler::class);
    $crawlerMock->shouldReceive('crawl')
        ->andThrow(new RuntimeException('Network error'));
    app()->instance(SiteCrawler::class, $crawlerMock);

    $this->postJson(route('lens-for-laravel.crawl'), ['url' => 'https://example.com'])
        ->assertStatus(500)
        ->assertJson(['status' => 'error']);
});
