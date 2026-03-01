<?php

beforeEach(function () {
    $this->viewsPath = $this->app->resourcePath('views');

    if (! is_dir($this->viewsPath)) {
        mkdir($this->viewsPath, 0755, true);
    }

    $this->bladeFile = $this->viewsPath.'/lens-fix-test.blade.php';
});

afterEach(function () {
    if (file_exists($this->bladeFile)) {
        unlink($this->bladeFile);
    }
});

test('POST /fix/apply requires all fields', function () {
    $this->postJson(route('lens-for-laravel.fix.apply'), [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['fileName', 'originalCode', 'fixedCode']);
});

test('POST /fix/apply returns 403 when environment not allowed', function () {
    $this->app['config']->set('lens-for-laravel.enabled_environments', ['local']);

    $this->postJson(route('lens-for-laravel.fix.apply'), [
        'fileName' => 'test.blade.php',
        'originalCode' => '<img src="x.png">',
        'fixedCode' => '<img src="x.png" alt="Fixed">',
    ])->assertStatus(403);
});

test('POST /fix/apply applies fix and replaces content in blade file', function () {
    $original = '<img src="logo.png">';
    $fixed = '<img src="logo.png" alt="Company logo">';

    file_put_contents($this->bladeFile, "<div>\n{$original}\n</div>");

    $this->postJson(route('lens-for-laravel.fix.apply'), [
        'fileName' => basename($this->bladeFile),
        'originalCode' => $original,
        'fixedCode' => $fixed,
    ])->assertStatus(200)
        ->assertJson(['status' => 'success']);

    expect(file_get_contents($this->bladeFile))->toContain($fixed)
        ->not->toContain($original);
});

test('POST /fix/apply returns 422 when original code not found in file', function () {
    file_put_contents($this->bladeFile, '<div>Different content here</div>');

    $this->postJson(route('lens-for-laravel.fix.apply'), [
        'fileName' => basename($this->bladeFile),
        'originalCode' => '<img src="nonexistent.png">',
        'fixedCode' => '<img src="nonexistent.png" alt="Fixed">',
    ])->assertStatus(422)
        ->assertJson(['status' => 'error']);
});

test('POST /fix/apply blocks path traversal attempts', function () {
    $this->postJson(route('lens-for-laravel.fix.apply'), [
        'fileName' => '../../../etc/passwd',
        'originalCode' => 'root',
        'fixedCode' => 'hacked',
    ])->assertStatus(403)
        ->assertJson(['status' => 'error', 'message' => 'File access denied.']);
});

test('POST /fix/apply blocks access to files outside views directory', function () {
    $this->postJson(route('lens-for-laravel.fix.apply'), [
        'fileName' => '/etc/hosts',
        'originalCode' => 'localhost',
        'fixedCode' => 'hacked',
    ])->assertStatus(403)
        ->assertJson(['status' => 'error', 'message' => 'File access denied.']);
});
