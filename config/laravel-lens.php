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
    | AI Fix Feature Flag
    |--------------------------------------------------------------------------
    |
    | Whether the experimental AI Fix feature should be enabled in the UI.
    |
    */
    'ai_fix_enabled' => env('LARAVEL_LENS_AI_FIX', false),
];
