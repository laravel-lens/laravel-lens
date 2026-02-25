<?php

namespace LaravelLens\LaravelLens\Services;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\File;
use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

class AiFixer
{
    /**
     * Suggest a fix for a given accessibility issue using Laravel AI SDK.
     */
    public function suggestFix(string $filePath, int $lineNumber, string $issueId, string $description): array
    {
        $absolutePath = resource_path('views/'.ltrim($filePath, '/'));

        if (! File::exists($absolutePath)) {
            $absolutePath = base_path($filePath);
            if (! File::exists($absolutePath)) {
                throw new \Exception("Unable to locate the Blade file for AI remediation. Path attempted: {$filePath}");
            }
        }

        try {
            $lines = file($absolutePath);
        } catch (\Throwable $e) {
            throw new \Exception("Failed to read file contents for AI remediation: {$e->getMessage()}");
        }

        // Extract +/- 3 lines of context around the error line
        $startLine = max(0, $lineNumber - 4); // 0-indexed, and we want 3 lines before
        $endLine = min(count($lines) - 1, $lineNumber + 2); // 3 lines after

        $contextSnippet = '';
        for ($i = $startLine; $i <= $endLine; $i++) {
            $contextSnippet .= $lines[$i];
        }

        $systemPrompt = "You are a Senior Laravel Architect. Analyze the provided Blade snippet and the specific WCAG violation. Your task is to return a JSON object with 'original_snippet' and 'fixed_snippet'. IMPORTANT: Maintain all existing Blade logic (@directives) and Laravel components (<x-). Only inject the necessary accessibility attributes to fix the reported issue.";

        $prompt = <<<PROMPT
Issue ID: {$issueId}
Issue Description: {$description}
File: {$filePath}
Line Number: {$lineNumber}

Context Snippet:
```blade
{$contextSnippet}
```
PROMPT;

        $response = agent(
            instructions: $systemPrompt,
            schema: fn (JsonSchema $schema) => [
                'original_snippet' => $schema->string()->description('The exact string of code from the original snippet that needs replacing. It must be an exact substring of the provided context.')->required(),
                'fixed_snippet' => $schema->string()->description('The corrected string of code.')->required(),
            ]
        )->prompt($prompt, provider: Lab::Gemini, model: 'gemini-3-flash');

        return [
            'original_snippet' => $response['original_snippet'] ?? '',
            'fixed_snippet' => $response['fixed_snippet'] ?? '',
            'file_path' => $filePath,
        ];
    }
}
