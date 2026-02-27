<?php

use LaravelLens\LaravelLens\DTOs\Issue;

test('stores all constructor properties', function () {
    $issue = new Issue(
        id: 'image-alt',
        impact: 'critical',
        description: 'Images must have alternate text',
        helpUrl: 'https://dequeuniversity.com/rules/axe/4.7/image-alt',
        htmlSnippet: '<img src="logo.png">',
        selector: 'img.logo',
        tags: ['wcag2a', 'wcag2aa'],
        url: 'https://example.com',
    );

    expect($issue->id)->toBe('image-alt')
        ->and($issue->impact)->toBe('critical')
        ->and($issue->description)->toBe('Images must have alternate text')
        ->and($issue->helpUrl)->toBe('https://dequeuniversity.com/rules/axe/4.7/image-alt')
        ->and($issue->htmlSnippet)->toBe('<img src="logo.png">')
        ->and($issue->selector)->toBe('img.logo')
        ->and($issue->tags)->toBe(['wcag2a', 'wcag2aa'])
        ->and($issue->url)->toBe('https://example.com')
        ->and($issue->fileName)->toBeNull()
        ->and($issue->lineNumber)->toBeNull();
});

test('optional parameters default to correct values', function () {
    $issue = new Issue(
        id: 'test-rule',
        impact: 'minor',
        description: 'Test',
        helpUrl: 'https://example.com',
        htmlSnippet: '<div></div>',
        selector: 'div',
    );

    expect($issue->tags)->toBe([])
        ->and($issue->url)->toBeNull()
        ->and($issue->fileName)->toBeNull()
        ->and($issue->lineNumber)->toBeNull();
});

test('file location can be assigned after construction', function () {
    $issue = new Issue('id', 'impact', 'desc', 'url', 'html', 'sel');
    $issue->fileName = 'layouts/app.blade.php';
    $issue->lineNumber = 42;

    expect($issue->fileName)->toBe('layouts/app.blade.php')
        ->and($issue->lineNumber)->toBe(42);
});
