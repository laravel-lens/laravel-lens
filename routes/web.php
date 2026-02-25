<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelLens\LaravelLens\Services\AxeScanner;
use LaravelLens\LaravelLens\Services\FileLocator;
use LaravelLens\LaravelLens\Exceptions\ScannerException;

// The prefix and middleware for these routes are automatically applied
// by the LaravelLensServiceProvider based on your config.

Route::get('/dashboard', function () {
    if (!in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
        abort(403, 'Laravel Lens is not allowed in this environment.');
    }

    return view('laravel-lens::dashboard');
})->name('laravel-lens.dashboard');

Route::post('/scan', function (Request $request) {
    if (!in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
        abort(403, 'Laravel Lens is not allowed in this environment.');
    }

    $request->validate([
        'url' => ['required', 'url'],
    ]);

    try {
        $scanner = new AxeScanner();
        $issues = $scanner->scan($request->url);

        $fileLocator = new FileLocator();
        
        // Enhance each issue with its estimated file location
        foreach ($issues as $issue) {
            $location = $fileLocator->locate($issue->htmlSnippet, $issue->selector);
            if ($location) {
                $issue->fileName = $location['file'];
                $issue->lineNumber = $location['line'];
            }
        }

        return response()->json([
            'status' => 'success',
            'issues' => $issues,
        ]);
    } catch (ScannerException $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('laravel-lens.scan');
