<?php

namespace LaravelLens\LaravelLens\Services;

use Illuminate\Support\Facades\File;
use Laravel\Ai\Facades\Ai;

class AiFixer
{
    /**
     * Suggest a fix for a given accessibility issue using Laravel AI SDK.
     */
    public function suggestFix(string $filePath, int $lineNumber, string $issueId, string $description): array
    {
        $absolutePath = resource_path('views/'.$filePath);

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

        $prompt = <<<PROMPT
You are a strict Laravel & WCAG expert. Fix the accessibility issue in the provided Blade snippet.
DO NOT modify Laravel directives, variables, or unrelated HTML. Return structured JSON.

Issue ID: {$issueId}
Issue Description: {$description}
File: {$filePath}
Line Number: {$lineNumber}

Context Snippet:
```blade
{$contextSnippet}
```
PROMPT;

        $response = Ai::driver('gemini')->chat()->model('gemini-3-flash')->system('You are a strict Laravel & WCAG expert. Fix the accessibility issue in the provided Blade snippet. DO NOT modify Laravel directives, variables, or unrelated HTML. Return structured JSON.')
            ->prompt($prompt)
            ->schema([
                'type' => 'object',
                'properties' => [
                    'original_snippet' => [
                        'type' => 'string',
                        'description' => 'The exact string of code from the original snippet that needs replacing. It must be an exact substring of the provided context.',
                    ],
                    'fixed_snippet' => [
                        'type' => 'string',
                        'description' => 'The corrected string of code.',
                    ],
                ],
                'required' => ['original_snippet', 'fixed_snippet'],
            ])
            ->send();

        $result = $response->json();

        return [
            'original_snippet' => $result['original_snippet'] ?? '',
            'fixed_snippet' => $result['fixed_snippet'] ?? '',
            'file_path' => $filePath,
        ];
    }
}
