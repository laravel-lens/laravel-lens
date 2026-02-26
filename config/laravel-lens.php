<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for the Laravel Lens dashboard routes.
    | Default: 'laravel-lens'
    |
    */
    'route_prefix' => 'laravel-lens',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | The middleware that should be applied to the Laravel Lens routes.
    | You might want to add 'auth' to restrict access in production.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Enabled Environments
    |--------------------------------------------------------------------------
    |
    | The environments where Laravel Lens is allowed to run.
    | Usually, you only want this enabled in local development.
    |
    */
    'enabled_environments' => [
        'local',
    ],

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
    'crawl_max_pages' => env('LARAVEL_LENS_CRAWL_MAX_PAGES', 50),

    /*
    |--------------------------------------------------------------------------
    | AI Fix Feature Flag
    |--------------------------------------------------------------------------
    |
    | Whether the experimental AI Fix feature should be enabled in the UI.
    |
    */
    'ai_fix_enabled' => env('LARAVEL_LENS_AI_FIX', false),
];
