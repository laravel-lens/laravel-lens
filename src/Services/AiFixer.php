<?php

namespace LensForLaravel\LensForLaravel\Services;

use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

class AiFixer
{
    /**
     * Generate an AI-powered accessibility fix suggestion.
     *
     * Reads ±20 lines of context around the issue location, sends them to
     * Gemini via laravel/ai, and returns the original + fixed code blocks.
     *
     * @return array{originalCode: string, fixedCode: string, explanation: string, fileName: string, startLine: int}
     */
    public function suggestFix(
        string $htmlSnippet,
        string $description,
        string $fileName,
        int $lineNumber,
        array $tags = []
    ): array {
        $viewsBase = resource_path('views');
        $fullPath = realpath($viewsBase.DIRECTORY_SEPARATOR.$fileName);

        if (! $fullPath || ! str_starts_with($fullPath, $viewsBase.DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException('File access denied: path is outside the views directory.');
        }

        $lines = explode("\n", file_get_contents($fullPath));
        $context = 20;
        $startIndex = max(0, $lineNumber - 1 - $context);
        $endIndex = min(count($lines) - 1, $lineNumber - 1 + $context);
        $codeBlock = implode("\n", array_slice($lines, $startIndex, $endIndex - $startIndex + 1));

        $wcagTags = implode(', ', array_filter($tags, fn ($t) => str_starts_with($t, 'wcag')));

        $prompt = <<<PROMPT
Fix the following accessibility issue found by axe-core in a Laravel Blade file.

## Accessibility Issue
Rule: {$description}
WCAG Standards: {$wcagTags}

## Failing HTML element (as detected by axe-core)
{$htmlSnippet}

## Current Blade code block (around line {$lineNumber} of the file)
{$codeBlock}

Return the corrected version of the ENTIRE code block shown above. Only fix what is necessary — do not reformat unrelated code. Preserve all Blade directives, whitespace, and indentation exactly.
PROMPT;

        $result = agent(
            instructions: 'You are an expert in web accessibility (WCAG) and Laravel Blade templates. You produce minimal, precise fixes that resolve accessibility violations without touching unrelated code.',
            schema: fn ($schema) => [
                'fixedCode' => $schema->string()->required(),
                'explanation' => $schema->string()->required(),
            ],
        )->prompt(
            $prompt,
            provider: Lab::Gemini,
        );

        return [
            'originalCode' => $codeBlock,
            'fixedCode' => $result['fixedCode'],
            'explanation' => $result['explanation'],
            'fileName' => $fileName,
            'startLine' => $startIndex + 1,
        ];
    }
}
