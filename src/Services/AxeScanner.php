<?php

namespace LaravelLens\LaravelLens\Services;

use Illuminate\Support\Collection;
use LaravelLens\LaravelLens\DTOs\Issue;
use LaravelLens\LaravelLens\Exceptions\ScannerException;
use Spatie\Browsershot\Browsershot;
use Throwable;

class AxeScanner
{
    /**
     * Scan a given URL for accessibility violations using Axe-core via Browsershot.
     *
     * @return Collection<Issue>
     *
     * @throws ScannerException
     */
    public function scan(string $url): Collection
    {
        try {
            // Configure Browsershot. Note that in some environments you may need
            // to configure the Node/NPM/Puppeteer path explicitly.
            $browsershot = Browsershot::url($url)
                ->waitUntilNetworkIdle(); // Wait for the page to fully load

            // We need to inject the axe-core library and run it.
            // Spatie Browsershot allows evaluating JavaScript on the page.
            $script = <<<'JS'
                return (async () => {
                    // Fetch and inject axe-core if it's not already present
                    if (typeof window.axe === 'undefined') {
                        await new Promise((resolve, reject) => {
                            const script = document.createElement('script');
                            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/axe-core/4.8.2/axe.min.js';
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                    }
                    
                    // Run axe and return the violations array
                    const results = await window.axe.run();
                    return results.violations;
                })();
JS;

            $violations = $browsershot->evaluate($script);

            return $this->mapViolationsToIssues($violations);
        } catch (Throwable $e) {
            throw new ScannerException('Failed to run Axe-core scan: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Map the raw JSON array of violations from Axe to our DTO Collection.
     *
     * @return Collection<Issue>
     */
    protected function mapViolationsToIssues(array $violations): Collection
    {
        $issues = collect();

        foreach ($violations as $violation) {
            // Axe groups nodes (HTML elements) under each violation type.
            // We want an individual Issue DTO for every HTML element that failed.
            foreach ($violation['nodes'] ?? [] as $node) {
                $issues->push(new Issue(
                    id: $violation['id'],
                    impact: $violation['impact'] ?? 'unknown',
                    description: $violation['description'],
                    helpUrl: $violation['helpUrl'] ?? '',
                    htmlSnippet: $node['html'] ?? '',
                    selector: implode(', ', $node['target'] ?? [])
                ));
            }
        }

        return $issues;
    }
}
