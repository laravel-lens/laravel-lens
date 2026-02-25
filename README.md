# Laravel Lens

Laravel Lens is a plug-and-play accessibility auditor for your Laravel applications. It dynamically scans your local application for WCAG compliance using [Axe-core](https://github.com/dequelabs/axe-core).

Best of all? It attempts to reverse-engineer failing CSS selectors to tell you exactly **which Blade file and line number** is causing the issue.

## Features

- **No Frontend Build Required:** Uses Alpine.js and Tailwind CSS via CDN. Works the moment you install it.
- **Powered by Axe-core:** Leverages the industry-standard accessibility testing engine.
- **Blade File Locator (Heuristics):** Maps the compiled HTML errors back to your `resources/views/**/*.blade.php` files.
- **Developer-Focused:** Beautiful dark-mode UI with clear explanations and direct links to MDN/Deque rule documentation.

## Requirements

Since Laravel Lens uses [Spatie Browsershot](https://github.com/spatie/browsershot) under the hood to render Javascript and execute Axe-core, your server/local environment **must** have:

1. **Node.js** installed.
2. **Puppeteer** installed (either globally or locally within your project).

```bash
npm install puppeteer --save-dev
```

## Installation

We highly recommend installing Laravel Lens as a development-only dependency.

```bash
composer require laravel-lens/laravel-lens --dev
```

*(Note: Currently, this package is not published to Packagist. To use it locally, add it as a path repository in your host application's `composer.json`.)*

## Usage

Once installed, simply start your Laravel server (or use Laravel Herd) and navigate to the dashboard:

```text
http://your-app.test/laravel-lens/dashboard
```

Enter the local URL you want to test and click **"Scan Now"**.

### Configuration (Optional)

By default, Laravel Lens is only accessible in the `local` environment. You can publish the configuration file to change this behavior (e.g., enabling it on a staging server):

```bash
php artisan vendor:publish --tag="laravel-lens-config"
```

This will create `config/laravel-lens.php` where you can modify the `route_prefix` and `enabled_environments`.

## Disclaimer

Automated accessibility tools like Axe-core can typically only catch **20–30%** of total WCAG violations. Passing a scan does not mean your application is fully accessible or compliant with the ADA, Section 508, or the European Accessibility Act.

Always complement automated testing with manual keyboard testing, screen reader testing, and cognitive walkthroughs.
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
