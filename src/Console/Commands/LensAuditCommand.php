<?php

namespace LaravelLens\LaravelLens\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelLens\LaravelLens\DTOs\Issue;
use LaravelLens\LaravelLens\Exceptions\ScannerException;
use LaravelLens\LaravelLens\Services\AxeScanner;
use LaravelLens\LaravelLens\Services\FileLocator;
use LaravelLens\LaravelLens\Services\SiteCrawler;

class LensAuditCommand extends Command
{
    protected $signature = 'lens:audit
                            {url? : Target URL to audit (defaults to app URL)}
                            {--a : Report only WCAG Level A violations}
                            {--aa : Report WCAG Level A and AA violations}
                            {--all : Report all violation levels including AAA and best-practice (default)}
                            {--threshold=0 : Exit code 1 if violation count exceeds this threshold}
                            {--crawl : Crawl the entire website and audit all discovered pages}';

    protected $description = 'Run an accessibility audit using axe-core via Browsershot and report WCAG violations';

    public function handle(): int
    {
        $url = $this->argument('url') ?? url('/');
        $threshold = (int) $this->option('threshold');
        $levelFilter = $this->resolveLevelFilter();
        $crawlMode = (bool) $this->option('crawl');

        $this->renderHeader($url, $levelFilter, $threshold, $crawlMode);

        // ── Scan ──────────────────────────────────────────────────────────────
        if ($crawlMode) {
            $result = $this->runCrawlScan($url);

            if ($result === null) {
                return self::FAILURE;
            }

            [$issues, $scannedUrls] = $result;
        } else {
            $issues = $this->runScan($url);
            $scannedUrls = [$url];

            if ($issues === null) {
                return self::FAILURE;
            }
        }

        // ── Filter + render ───────────────────────────────────────────────────
        $filtered = $this->filterByLevel($issues, $levelFilter);
        $violationCount = $filtered->count();

        if ($violationCount === 0) {
            $this->newLine();
            $this->components->success('No violations found.');

            return self::SUCCESS;
        }

        if ($crawlMode) {
            $this->renderCrawlTable($filtered);
        } else {
            $this->renderTable($filtered);
        }

        $this->renderSummary($filtered, $scannedUrls);

        // ── CI/CD quality gate ────────────────────────────────────────────────
        if ($violationCount > $threshold) {
            $this->newLine();
            $this->components->error(
                "Quality gate failed: {$violationCount} violation(s) found (threshold: {$threshold})"
            );

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info(
            "Quality gate passed: {$violationCount} violation(s) found (threshold: {$threshold})"
        );

        return self::SUCCESS;
    }

    // ─── Single-URL scan ────────────────────────────────────────────────────────

    private function runScan(string $url): ?Collection
    {
        $this->newLine();

        try {
            $scanner = app(AxeScanner::class);
            $issues = null;

            $this->components->task('Launching Browsershot + axe-core', function () {});

            $this->components->task("Scanning <href={$url}>{$url}</>", function () use ($url, $scanner, &$issues) {
                $issues = $scanner->scan($url);
            });

            $this->components->task('Resolving Blade source locations', function () use ($issues) {
                $locator = app(FileLocator::class);
                foreach ($issues as $issue) {
                    $location = $locator->locate($issue->htmlSnippet, $issue->selector);
                    if ($location) {
                        $issue->fileName = $location['file'];
                        $issue->lineNumber = $location['line'];
                    }
                }
            });

            return $issues;
        } catch (ScannerException $e) {
            $this->newLine();
            $this->components->error('Scan failed: '.$e->getMessage());
            $this->renderTroubleshooting();

            return null;
        }
    }

    // ─── Crawl scan ─────────────────────────────────────────────────────────────

    /**
     * Crawl the site, scan each discovered URL with axe-core, and return
     * all collected issues along with the list of actually-scanned URLs.
     *
     * @return array{0: Collection<Issue>, 1: string[]}|null
     */
    private function runCrawlScan(string $url): ?array
    {
        $maxPages = (int) config('laravel-lens.crawl_max_pages', 50);

        $this->newLine();

        // ── Step 1: discover URLs ──────────────────────────────────────────────
        $urls = [];

        $this->components->task(
            "Crawling site (limit: {$maxPages} pages)",
            function () use ($url, $maxPages, &$urls) {
                $urls = app(SiteCrawler::class)->crawl($url, $maxPages);
            }
        );

        if (empty($urls)) {
            $this->newLine();
            $this->components->error('No internal pages discovered. Check the URL and try again.');

            return null;
        }

        $discovered = count($urls);
        $this->line("  <fg=gray>Found {$discovered} page(s) to audit.</>");
        $this->newLine();

        // ── Step 2: axe-core scan per page ────────────────────────────────────
        $scanner = app(AxeScanner::class);
        $locator = app(FileLocator::class);
        $allIssues = collect();
        $scannedUrls = [];
        $failedUrls = [];

        $bar = $this->output->createProgressBar($discovered);
        $bar->setFormat("  <fg=gray>%message%</>\n  [%bar%] %current%/%max% (%percent:3s%%)");
        $bar->setMessage('Initializing...');
        $bar->start();

        foreach ($urls as $pageUrl) {
            $displayPath = mb_strimwidth(
                parse_url($pageUrl, PHP_URL_PATH) ?: '/',
                0,
                55,
                '…'
            );

            $bar->setMessage($displayPath);
            $bar->display();

            try {
                $pageIssues = $scanner->scan($pageUrl);

                foreach ($pageIssues as $issue) {
                    $location = $locator->locate($issue->htmlSnippet, $issue->selector);
                    if ($location) {
                        $issue->fileName = $location['file'];
                        $issue->lineNumber = $location['line'];
                    }
                }

                $allIssues = $allIssues->merge($pageIssues);
                $scannedUrls[] = $pageUrl;
            } catch (ScannerException $e) {
                $failedUrls[] = $pageUrl;
            }

            $bar->advance();
        }

        $bar->setMessage('Done.');
        $bar->finish();

        $this->newLine(2);

        if (! empty($failedUrls)) {
            $this->line(sprintf(
                '  <fg=yellow>⚠ %d page(s) could not be scanned and were skipped.</>',
                count($failedUrls)
            ));
            $this->newLine();
        }

        if (empty($scannedUrls)) {
            $this->components->error('All pages failed to scan. Check the Browsershot/Puppeteer setup.');
            $this->renderTroubleshooting();

            return null;
        }

        return [$allIssues, $scannedUrls];
    }

    // ─── Filtering ──────────────────────────────────────────────────────────────

    private function resolveLevelFilter(): string
    {
        if ($this->option('a')) {
            return 'a';
        }

        if ($this->option('aa')) {
            return 'aa';
        }

        return 'all';
    }

    private function filterByLevel(Collection $issues, string $level): Collection
    {
        return match ($level) {
            'a' => $issues->filter(fn (Issue $i) => in_array('wcag2a', $i->tags)),
            'aa' => $issues->filter(fn (Issue $i) => in_array('wcag2a', $i->tags) || in_array('wcag2aa', $i->tags)),
            default => $issues,
        };
    }

    // ─── Rendering ──────────────────────────────────────────────────────────────

    private function renderHeader(string $url, string $levelFilter, int $threshold, bool $crawlMode): void
    {
        $levelLabel = match ($levelFilter) {
            'a' => 'WCAG A only',
            'aa' => 'WCAG A + AA',
            default => 'A + AA + AAA + Best Practice',
        };

        $modeLabel = $crawlMode ? '<fg=cyan>WHOLE_WEBSITE</>' : '<fg=gray>SINGLE_URL</>';

        $this->newLine();
        $this->line('  <options=bold>Laravel Lens — Accessibility Audit</>');
        $this->line('  ─────────────────────────────────────────────');
        $this->line("  <fg=gray>URL</>       : {$url}");
        $this->line("  <fg=gray>Mode</>      : {$modeLabel}");
        $this->line("  <fg=gray>Levels</>    : {$levelLabel}");
        $this->line("  <fg=gray>Threshold</> : {$threshold}");
    }

    /**
     * Table for single-URL scan: one row per violation node.
     */
    private function renderTable(Collection $issues): void
    {
        $verbose = $this->output->isVerbose();

        $this->newLine();
        $this->line('  <options=bold>Diagnostic Report</>');
        if (! $verbose) {
            $this->line('  <fg=gray>Tip: run with -v to see full HTML nodes</>');
        }
        $this->newLine();

        $nodeHeader = $verbose ? 'Failing Node (full)' : 'Node';

        $rows = $issues->values()->map(function (Issue $issue) use ($verbose) {
            return [
                $this->formatLevel($issue->tags),
                wordwrap($issue->id, 30, "\n", true),
                $this->formatImpact($issue->impact),
                $this->formatNode($issue->htmlSnippet, $verbose),
                $issue->fileName ? "{$issue->fileName}:{$issue->lineNumber}" : '—',
            ];
        })->all();

        $this->table(
            ['Level', 'Rule ID', 'Impact', $nodeHeader, 'Location'],
            $rows
        );
    }

    /**
     * Table for crawl scan: issues grouped by rule ID to avoid infinite rows.
     * Shows an "Occurrences" column: total violations + how many pages affected.
     */
    private function renderCrawlTable(Collection $issues): void
    {
        $this->newLine();
        $this->line('  <options=bold>Diagnostic Report — aggregated across all pages</>');
        $this->line('  <fg=gray>Issues are grouped by rule ID. Use -v to see full node HTML.</>');
        $this->newLine();

        $verbose = $this->output->isVerbose();

        $grouped = $issues->groupBy('id')->sortByDesc(fn ($group) => $group->count());

        $rows = $grouped->map(function (Collection $group, string $ruleId) use ($verbose) {
            /** @var Issue $first */
            $first = $group->first();

            $totalOccurrences = $group->count();
            $affectedPages = $group->pluck('url')->filter()->unique()->count();

            $occurrences = $affectedPages > 1
                ? "{$totalOccurrences} ({$affectedPages} pages)"
                : (string) $totalOccurrences;

            $location = $first->fileName
                ? "{$first->fileName}:{$first->lineNumber}"
                : '—';

            return [
                $this->formatLevel($first->tags),
                wordwrap($ruleId, 28, "\n", true),
                $this->formatImpact($first->impact),
                $this->formatNode($first->htmlSnippet, $verbose),
                $occurrences,
                $location,
            ];
        })->values()->all();

        $this->table(
            ['Level', 'Rule ID', 'Impact', 'Node (example)', 'Occurrences', 'Location (first)'],
            $rows
        );
    }

    private function renderSummary(Collection $issues, array $scannedUrls): void
    {
        $total = $issues->count();
        $byImpact = $issues->groupBy('impact');
        $isCrawl = count($scannedUrls) > 1;

        $levelCounts = [
            'A' => $issues->filter(fn (Issue $i) => in_array('wcag2a', $i->tags))->count(),
            'AA' => $issues->filter(fn (Issue $i) => in_array('wcag2aa', $i->tags) && ! in_array('wcag2a', $i->tags))->count(),
            'AAA' => $issues->filter(fn (Issue $i) => in_array('wcag2aaa', $i->tags) && ! in_array('wcag2a', $i->tags) && ! in_array('wcag2aa', $i->tags))->count(),
            'Best Practice' => $issues->filter(fn (Issue $i) => ! in_array('wcag2a', $i->tags) && ! in_array('wcag2aa', $i->tags) && ! in_array('wcag2aaa', $i->tags))->count(),
        ];

        $this->line('  <options=bold>Summary</>');
        $this->line('  ─────────────────────────────────────────────');

        if ($isCrawl) {
            $uniqueRules = $issues->pluck('id')->unique()->count();
            $this->line('  Pages scanned     : <options=bold>'.count($scannedUrls).'</>');
            $this->line("  Unique rules hit  : <options=bold>{$uniqueRules}</>");
        }

        $this->line("  Total violations  : <fg=red;options=bold>{$total}</>");

        $this->newLine();
        $this->line('  <fg=gray>By WCAG level:</>');
        foreach ($levelCounts as $label => $count) {
            if ($count > 0) {
                $this->line("  · {$label}: {$count}");
            }
        }

        $this->newLine();
        $this->line('  <fg=gray>By impact:</>');
        foreach (['critical', 'serious', 'moderate', 'minor'] as $impact) {
            $count = $byImpact->get($impact, collect())->count();
            if ($count > 0) {
                $this->line("  · {$this->formatImpact($impact)}: {$count}");
            }
        }

        $this->newLine();
    }

    // ─── Formatters ─────────────────────────────────────────────────────────────

    private function formatLevel(array $tags): string
    {
        if (in_array('wcag2a', $tags)) {
            return '<fg=red;options=bold>A</>';
        }
        if (in_array('wcag2aa', $tags)) {
            return '<fg=yellow>AA</>';
        }
        if (in_array('wcag2aaa', $tags)) {
            return '<fg=blue>AAA</>';
        }

        return '<fg=gray>BP</>';
    }

    private function formatImpact(string $impact): string
    {
        return match ($impact) {
            'critical' => '<fg=red;options=bold>critical</>',
            'serious' => '<fg=red>serious</>',
            'moderate' => '<fg=yellow>moderate</>',
            'minor' => '<fg=gray>minor</>',
            default => $impact,
        };
    }

    /**
     * Format the HTML snippet for the Node column.
     *
     * Default: [tag] "extracted text" truncated to 30 chars.
     * Verbose (-v): raw HTML unchanged.
     */
    private function formatNode(string $html, bool $verbose): string
    {
        if ($verbose) {
            return $html;
        }

        preg_match('/^<(\w+)/i', ltrim($html), $tagMatch);
        $tag = $tagMatch[1] ?? '?';

        $text = $this->extractNodeText($html, $tag);
        $label = "[{$tag}]".($text !== '' ? " \"{$text}\"" : '');

        return Str::limit($label, 27);
    }

    /**
     * Pull the most meaningful human-readable text from an HTML snippet.
     *
     * Priority:
     *   img/area  → alt → basename(src)
     *   input/textarea/select → aria-label → placeholder → value
     *   any → aria-label → title → stripped inner text
     */
    private function extractNodeText(string $html, string $tag): string
    {
        if (in_array($tag, ['img', 'area'])) {
            if (preg_match('/\balt=["\']([^"\']*)["\']/', $html, $m) && $m[1] !== '') {
                return $m[1];
            }
            if (preg_match('/\bsrc=["\']([^"\']*)["\']/', $html, $m)) {
                return basename(parse_url($m[1], PHP_URL_PATH) ?? $m[1]);
            }

            return '';
        }

        if (in_array($tag, ['input', 'textarea', 'select'])) {
            if (preg_match('/\baria-label=["\']([^"\']+)["\']/', $html, $m)) {
                return $m[1];
            }
            if (preg_match('/\bplaceholder=["\']([^"\']+)["\']/', $html, $m)) {
                return $m[1];
            }
            if (preg_match('/\bvalue=["\']([^"\']+)["\']/', $html, $m)) {
                return $m[1];
            }

            return '';
        }

        if (preg_match('/\baria-label=["\']([^"\']+)["\']/', $html, $m)) {
            return $m[1];
        }

        if (preg_match('/\btitle=["\']([^"\']+)["\']/', $html, $m)) {
            return $m[1];
        }

        return trim(strip_tags($html));
    }

    private function renderTroubleshooting(): void
    {
        $this->newLine();
        $this->line('  <fg=yellow>Troubleshooting:</>');
        $this->line('  · Ensure Node.js and npm are installed');
        $this->line('  · Run: npm install -g puppeteer');
        $this->line('  · Or configure Browsershot with a local Chromium path in your environment');
        $this->newLine();
    }
}
