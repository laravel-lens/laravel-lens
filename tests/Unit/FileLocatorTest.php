<?php

use LensForLaravel\LensForLaravel\Services\FileLocator;

beforeEach(function () {
    $this->viewsPath = $this->app->resourcePath('views');

    if (! is_dir($this->viewsPath)) {
        mkdir($this->viewsPath, 0755, true);
    }

    $this->bladeFile = $this->viewsPath.'/lens-locator-test.blade.php';
});

afterEach(function () {
    if (file_exists($this->bladeFile)) {
        unlink($this->bladeFile);
    }
});

test('locates element by id attribute', function () {
    file_put_contents($this->bladeFile, '<img id="main-logo" src="logo.png" alt="Logo">');

    $result = (new FileLocator)->locate('<img id="main-logo" src="logo.png">', '#main-logo');

    expect($result)->not->toBeNull()
        ->and($result['file'])->toEndWith('lens-locator-test.blade.php')
        ->and($result['line'])->toBe(1);
});

test('locates element by name attribute', function () {
    file_put_contents($this->bladeFile, '<input name="email" type="email">');

    $result = (new FileLocator)->locate('<input name="email" type="email">', 'input[name="email"]');

    expect($result)->not->toBeNull()
        ->and($result['file'])->toEndWith('lens-locator-test.blade.php');
});

test('locates element by css class from selector', function () {
    file_put_contents($this->bladeFile, '<button class="submit-btn primary">Submit</button>');

    $result = (new FileLocator)->locate('<button class="submit-btn">Submit</button>', '.submit-btn');

    expect($result)->not->toBeNull();
});

test('returns null when no match found in any file', function () {
    file_put_contents($this->bladeFile, '<p>No matching element here</p>');

    $result = (new FileLocator)->locate('<img id="ghost-element" src="x.png">', '#ghost-element');

    expect($result)->toBeNull();
});

test('returns null when html snippet is empty', function () {
    $result = (new FileLocator)->locate('', 'div');

    expect($result)->toBeNull();
});

test('returns correct line number in a multi-line file', function () {
    file_put_contents(
        $this->bladeFile,
        "<div>\n".
        "    <p>First</p>\n".
        "    <img id=\"hero-img\" src=\"hero.jpg\" alt=\"Hero\">\n".
        "    <p>Last</p>\n".
        '</div>'
    );

    $result = (new FileLocator)->locate('<img id="hero-img" src="hero.jpg">', '#hero-img');

    expect($result)->not->toBeNull()
        ->and($result['line'])->toBe(3);
});
