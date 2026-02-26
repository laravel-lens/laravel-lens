<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use LaravelLens\LaravelLens\Exceptions\ScannerException;
use LaravelLens\LaravelLens\Services\AiFixer;
use LaravelLens\LaravelLens\Services\AxeScanner;
use LaravelLens\LaravelLens\Services\FileLocator;
use LaravelLens\LaravelLens\Services\SiteCrawler;
use Spatie\Browsershot\Browsershot;

// The prefix and middleware for these routes are automatically applied
// by the LaravelLensServiceProvider based on your config.

Route::get('/dashboard', function () {
    if (! in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
        abort(403, 'Laravel Lens is not allowed in this environment.');
    }

    return view('laravel-lens::dashboard');
})->name('laravel-lens.dashboard');

Route::post('/crawl', function (Request $request) {
    if (! in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
        abort(403, 'Laravel Lens is not allowed in this environment.');
    }

    $request->validate([
        'url' => ['required', 'url'],
    ]);

    try {
        $crawler = new SiteCrawler;
        $urls = $crawler->crawl($request->url, config('laravel-lens.crawl_max_pages', 50));

        return response()->json([
            'status' => 'success',
            'urls' => $urls,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('laravel-lens.crawl');

Route::post('/scan', function (Request $request) {
    if (! in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
        abort(403, 'Laravel Lens is not allowed in this environment.');
    }

    $request->validate([
        'url' => ['required', 'url'],
    ]);

    try {
        $scanner = new AxeScanner;
        $issues = $scanner->scan($request->url);

        $fileLocator = new FileLocator;

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
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('laravel-lens.scan');

Route::post('/report/pdf', function (Request $request) {
    if (! in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
        abort(403, 'Laravel Lens is not allowed in this environment.');
    }

    $request->validate([
        'issues' => ['required', 'array'],
        'url'    => ['required', 'string'],
    ]);

    try {
        $html = view('laravel-lens::report', [
            'issues'      => $request->issues,
            'url'         => $request->url,
            'generatedAt' => now(),
        ])->render();

        $pdf = Browsershot::html($html)
            ->noSandbox()
            ->format('A4')
            ->margins(0, 0, 0, 0)
            ->pdf();

        $filename = 'accessibility-report-'.now()->format('Y-m-d').'.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
})->name('laravel-lens.report.pdf');

if (config('laravel-lens.ai_fix_enabled')) {
    Route::post('/fix/suggest', function (Request $request) {
        if (! in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
            abort(403, 'Laravel Lens is not allowed in this environment.');
        }

        $request->validate([
            'file_path' => ['required', 'string'],
            'line_number' => ['required', 'integer'],
            'issue_id' => ['required', 'string'],
            'description' => ['required', 'string'],
        ]);

        try {
            $fixer = new AiFixer;
            $suggestion = $fixer->suggestFix(
                $request->file_path,
                $request->line_number,
                $request->issue_id,
                $request->description
            );

            return response()->json([
                'status' => 'success',
                'suggestion' => $suggestion,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    })->name('laravel-lens.fix.suggest');

    Route::post('/fix/apply', function (Request $request) {
        if (! in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
            abort(403, 'Laravel Lens is not allowed in this environment.');
        }

        $request->validate([
            'file_path' => ['required', 'string'],
            'original_snippet' => ['required', 'string'],
            'fixed_snippet' => ['required', 'string'],
        ]);

        try {
            $viewsBase = realpath(resource_path('views'));
            $absolutePath = realpath(resource_path('views/'.$request->file_path));

            if ($absolutePath === false || $viewsBase === false || ! str_starts_with($absolutePath, $viewsBase.DIRECTORY_SEPARATOR)) {
                throw new \Exception('Invalid or disallowed file path.');
            }

            if (! File::exists($absolutePath)) {
                throw new \Exception("Unable to locate the Blade file for writing: {$request->file_path}");
            }

            try {
                $content = File::get($absolutePath);
            } catch (\Throwable $e) {
                throw new \Exception("Failed to read file contents for writing: {$e->getMessage()}");
            }

            if (strpos($content, $request->original_snippet) === false) {
                throw new \Exception('The original snippet could not be perfectly matched in the file. It may have changed.');
            }

            $newContent = str_replace($request->original_snippet, $request->fixed_snippet, $content);

            File::put($absolutePath, $newContent);

            return response()->json([
                'status' => 'success',
                'message' => 'Fix applied successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    })->name('laravel-lens.fix.apply');
}
