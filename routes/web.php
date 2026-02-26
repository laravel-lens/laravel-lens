<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelLens\LaravelLens\Exceptions\ScannerException;
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

Route::post('/preview', function (Request $request) {
    if (! in_array(app()->environment(), config('laravel-lens.enabled_environments', ['local']))) {
        abort(403, 'Laravel Lens is not allowed in this environment.');
    }

    $request->validate([
        'url'      => ['required', 'url'],
        'selector' => ['required', 'string', 'max:500'],
    ]);

    $selectorJson = json_encode($request->selector);

    // Injected after page load: scroll the element into view and draw a
    // translucent dark overlay with a red highlight rectangle on top of it.
    $highlightScript = <<<JS
    (function () {
        try {
            var el = document.querySelector({$selectorJson});
            if (!el) return;
            el.scrollIntoView({ behavior: 'instant', block: 'center' });
            var r = el.getBoundingClientRect();
            // Full-page dimming overlay
            var dim = document.createElement('div');
            dim.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.45);pointer-events:none;z-index:2147483646;';
            document.documentElement.appendChild(dim);
            // Red highlight box cut-out (just a border, no fill — so the element stays readable)
            var box = document.createElement('div');
            box.style.cssText = 'position:fixed;pointer-events:none;z-index:2147483647;box-sizing:border-box;border:3px solid #E11D48;outline:1px solid rgba(0,0,0,0.5);';
            box.style.top    = r.top    + 'px';
            box.style.left   = r.left   + 'px';
            box.style.width  = r.width  + 'px';
            box.style.height = r.height + 'px';
            document.documentElement.appendChild(box);
        } catch (e) {}
    })();
    JS;

    try {
        $screenshot = Browsershot::url($request->url)
            ->noSandbox()
            ->waitUntilNetworkIdle()
            ->windowSize(1280, 800)
            ->setOption('addScriptTag', json_encode(['content' => $highlightScript]))
            ->screenshot();

        return response($screenshot, 200, ['Content-Type' => 'image/png']);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
})->name('laravel-lens.preview');

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

