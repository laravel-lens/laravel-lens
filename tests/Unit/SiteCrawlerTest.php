<?php

use Illuminate\Support\Facades\Http;
use LaravelLens\LaravelLens\Services\SiteCrawler;

function fakeSitemapNotFound(): array
{
    return [
        'https://example.com/sitemap.xml' => Http::response('', 404),
        'https://example.com/sitemap_index.xml' => Http::response('', 404),
        'https://example.com/sitemaps/sitemap.xml' => Http::response('', 404),
    ];
}

test('discovers internal links via BFS', function () {
    Http::fake([
        ...fakeSitemapNotFound(),
        'https://example.com' => Http::response(
            '<html><body><a href="/about">About</a><a href="/contact">Contact</a></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
        'https://example.com/about' => Http::response(
            '<html><body><a href="/">Home</a></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
        'https://example.com/contact' => Http::response(
            '<html><body></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
    ]);

    $urls = (new SiteCrawler)->crawl('https://example.com', 10);

    expect($urls)
        ->toContain('https://example.com')
        ->toContain('https://example.com/about')
        ->toContain('https://example.com/contact');
});

test('respects the max pages limit', function () {
    Http::fake([
        ...fakeSitemapNotFound(),
        'https://example.com' => Http::response(
            '<html><body>'.
            '<a href="/p1">1</a><a href="/p2">2</a><a href="/p3">3</a>'.
            '<a href="/p4">4</a><a href="/p5">5</a><a href="/p6">6</a>'.
            '</body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
        'https://example.com/*' => Http::response(
            '<html><body></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
    ]);

    $urls = (new SiteCrawler)->crawl('https://example.com', 3);

    expect(count($urls))->toBeLessThanOrEqual(3);
});

test('excludes external domains', function () {
    Http::fake([
        ...fakeSitemapNotFound(),
        'https://example.com' => Http::response(
            '<html><body><a href="https://external.com/page">Ext</a></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
    ]);

    $urls = (new SiteCrawler)->crawl('https://example.com', 10);

    expect($urls)->not->toContain('https://external.com/page');
});

test('excludes static asset extensions', function () {
    Http::fake([
        ...fakeSitemapNotFound(),
        'https://example.com' => Http::response(
            '<html><body>'.
            '<a href="/img.png">img</a>'.
            '<a href="/style.css">css</a>'.
            '<a href="/app.js">js</a>'.
            '<a href="/doc.pdf">pdf</a>'.
            '</body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
    ]);

    $urls = (new SiteCrawler)->crawl('https://example.com', 10);

    expect($urls)
        ->not->toContain('https://example.com/img.png')
        ->not->toContain('https://example.com/style.css')
        ->not->toContain('https://example.com/app.js')
        ->not->toContain('https://example.com/doc.pdf');
});

test('seeds urls from sitemap.xml', function () {
    Http::fake([
        'https://example.com/sitemap.xml' => Http::response(
            '<?xml version="1.0" encoding="UTF-8"?>'.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
            '<url><loc>https://example.com/from-sitemap</loc></url>'.
            '</urlset>',
            200,
            ['Content-Type' => 'application/xml']
        ),
        'https://example.com' => Http::response('<html><body></body></html>', 200, ['Content-Type' => 'text/html']),
        'https://example.com/from-sitemap' => Http::response('<html><body></body></html>', 200, ['Content-Type' => 'text/html']),
    ]);

    $urls = (new SiteCrawler)->crawl('https://example.com', 10);

    expect($urls)->toContain('https://example.com/from-sitemap');
});

test('ignores javascript mailto and tel hrefs', function () {
    Http::fake([
        ...fakeSitemapNotFound(),
        'https://example.com' => Http::response(
            '<html><body>'.
            '<a href="javascript:void(0)">JS</a>'.
            '<a href="mailto:x@x.com">Mail</a>'.
            '<a href="tel:+123">Phone</a>'.
            '</body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
    ]);

    $urls = (new SiteCrawler)->crawl('https://example.com', 10);

    expect($urls)->toHaveCount(1)
        ->toContain('https://example.com');
});

test('does not visit the same url twice', function () {
    Http::fake([
        ...fakeSitemapNotFound(),
        'https://example.com' => Http::response(
            '<html><body><a href="/">Self</a><a href="/page">Page</a></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
        'https://example.com/page' => Http::response(
            '<html><body><a href="/">Home</a></body></html>',
            200,
            ['Content-Type' => 'text/html']
        ),
    ]);

    $urls = (new SiteCrawler)->crawl('https://example.com', 20);

    expect(array_unique($urls))->toHaveCount(count($urls));
});
