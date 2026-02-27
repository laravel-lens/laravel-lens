<?php

use LaravelLens\LaravelLens\DTOs\Issue;
use LaravelLens\LaravelLens\Exceptions\ScannerException;
use LaravelLens\LaravelLens\Services\AxeScanner;
use LaravelLens\LaravelLens\Services\FileLocator;

test('POST /scan requires url', function () {
    $this->postJson(route('laravel-lens.scan'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('POST /scan rejects invalid url format', function () {
    $this->postJson(route('laravel-lens.scan'), ['url' => 'not-a-url'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['url']);
});

test('POST /scan returns 403 when environment not allowed', function () {
    $this->app['config']->set('laravel-lens.enabled_environments', ['local']);

    $this->postJson(route('laravel-lens.scan'), ['url' => 'https://example.com'])
        ->assertStatus(403);
});

test('POST /scan returns violations on success', function () {
    $mockIssue = new Issue(
        id: 'image-alt',
        impact: 'critical',
        description: 'Images must have alternate text',
        helpUrl: 'https://dequeuniversity.com/image-alt',
        htmlSnippet: '<img src="x.png">',
        selector: 'img',
        tags: ['wcag2a'],
        url: 'https://example.com',
    );

    $scannerMock = Mockery::mock(AxeScanner::class);
    $scannerMock->shouldReceive('scan')
        ->once()
        ->with('https://example.com')
        ->andReturn(collect([$mockIssue]));
    app()->instance(AxeScanner::class, $scannerMock);

    $locatorMock = Mockery::mock(FileLocator::class);
    $locatorMock->shouldReceive('locate')->andReturn(null);
    app()->instance(FileLocator::class, $locatorMock);

    $this->postJson(route('laravel-lens.scan'), ['url' => 'https://example.com'])
        ->assertStatus(200)
        ->assertJson(['status' => 'success'])
        ->assertJsonStructure(['status', 'issues']);
});

test('POST /scan returns 500 when scanner throws exception', function () {
    $scannerMock = Mockery::mock(AxeScanner::class);
    $scannerMock->shouldReceive('scan')
        ->andThrow(new ScannerException('Puppeteer not available'));
    app()->instance(AxeScanner::class, $scannerMock);

    $this->postJson(route('laravel-lens.scan'), ['url' => 'https://example.com'])
        ->assertStatus(500)
        ->assertJson(['status' => 'error']);
});

test('POST /scan enriches issues with blade file locations', function () {
    $mockIssue = new Issue(
        id: 'image-alt',
        impact: 'critical',
        description: 'desc',
        helpUrl: 'https://help.url',
        htmlSnippet: '<img id="logo" src="x.png">',
        selector: '#logo',
        tags: ['wcag2a'],
        url: 'https://example.com',
    );

    $scannerMock = Mockery::mock(AxeScanner::class);
    $scannerMock->shouldReceive('scan')->andReturn(collect([$mockIssue]));
    app()->instance(AxeScanner::class, $scannerMock);

    $locatorMock = Mockery::mock(FileLocator::class);
    $locatorMock->shouldReceive('locate')
        ->andReturn(['file' => 'layouts/app.blade.php', 'line' => 15]);
    app()->instance(FileLocator::class, $locatorMock);

    $response = $this->postJson(route('laravel-lens.scan'), ['url' => 'https://example.com'])
        ->assertStatus(200);

    $issues = $response->json('issues');
    expect($issues[0]['fileName'])->toBe('layouts/app.blade.php')
        ->and($issues[0]['lineNumber'])->toBe(15);
});
