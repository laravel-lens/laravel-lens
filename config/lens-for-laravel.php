<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for the Lens For Laravel dashboard routes.
    | Default: 'lens-for-laravel'
    |
    */
    'route_prefix' => 'lens-for-laravel',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware that should be applied to the Lens For Laravel routes.
    | You might want to add 'auth' to restrict access in production.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Enabled Environments
    |--------------------------------------------------------------------------
    |
    | The environments where Lens For Laravel is allowed to run.
    | Usually, you only want this enabled in local development.
    |
    */
    'enabled_environments' => [
        'local',
    ],

    /*
    |--------------------------------------------------------------------------
    | Editor / IDE Integration
    |--------------------------------------------------------------------------
    |
    | When a source location is found, clicking it in the dashboard will open
    | your editor at the exact file and line. Set to 'none' to disable.
    |
    | Supported values: 'vscode', 'cursor', 'phpstorm', 'sublime', 'none'
    |
    */
    'editor' => env('LENS_FOR_LARAVEL_EDITOR', 'vscode'),

    /*
    |--------------------------------------------------------------------------
    | Crawl Max Pages
    |--------------------------------------------------------------------------
    |
    | Maximum number of pages to discover and scan in WHOLE_WEBSITE mode.
    | Increase this if your site has many pages. The crawl phase uses a plain
    | HTTP client (not headless Chrome), so higher limits are fast and safe.
    |
    */
    'crawl_max_pages' => env('LENS_FOR_LARAVEL_CRAWL_MAX_PAGES', 50),

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | The AI provider used for generating code fixes.
    |
    | Supported values: 'gemini', 'openai', 'anthropic'
    |
    */
    'ai_provider' => env('LENS_FOR_LARAVEL_AI_PROVIDER', 'gemini'),

];
