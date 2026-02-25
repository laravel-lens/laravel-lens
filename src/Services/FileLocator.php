<?php

namespace LaravelLens\LaravelLens\Services;

use Symfony\Component\Finder\Finder;

class FileLocator
{
    /**
     * Locate the file and line number for a given HTML snippet and CSS selector.
     * Uses heuristics to find the best matching Blade file in the host application.
     *
     * @param string $htmlSnippet
     * @param string $selector
     * @return array|null Returns ['file' => string, 'line' => int] or null if not found.
     */
    public function locate(string $htmlSnippet, string $selector): ?array
    {
        $viewsPath = resource_path('views');

        // Check if the host app's views directory exists
        if (!is_dir($viewsPath)) {
            return null;
        }

        $tagName = $this->extractTagName($htmlSnippet);
        $id = $this->extractAttribute($htmlSnippet, 'id');
        $name = $this->extractAttribute($htmlSnippet, 'name');

        if (!$tagName) {
            return null;
        }

        $finder = new Finder();
        $finder->files()->in($viewsPath)->name('*.blade.php');

        foreach ($finder as $file) {
            $contents = file_get_contents($file->getRealPath());
            $lines = explode("
", $contents);

            foreach ($lines as $index => $line) {
                if ($this->isMatch($line, $tagName, $id, $name, $selector)) {
                    return [
                        'file' => $file->getRelativePathname(),
                        'line' => $index + 1,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Determine if a given line of Blade code matches our target criteria.
     */
    protected function isMatch(string $line, string $tagName, ?string $id, ?string $name, string $selector): bool
    {
        // 1. Check if the line contains the tag name (or a Blade component tag <x-)
        if (stripos($line, '<' . $tagName) === false && stripos($line, '<x-') === false) {
            return false;
        }

        // 2. Check for exact ID match
        if ($id && (stripos($line, 'id="' . $id . '"') !== false || stripos($line, "id='" . $id . "'") !== false)) {
            return true;
        }

        // 3. Check for exact Name match
        if ($name && (stripos($line, 'name="' . $name . '"') !== false || stripos($line, "name='" . $name . "'") !== false)) {
            return true;
        }

        // 4. Fallback: check if the line contains parts of the CSS selector (like class names or IDs)
        preg_match_all('/[#\.]([a-zA-Z0-9\-_]+)/', $selector, $matches);
        $selectorParts = $matches[1] ?? [];
        
        foreach ($selectorParts as $part) {
            if (stripos($line, $part) !== false) {
                return true;
            }
        }

        // If there's no ID, no Name, and no specific classes/IDs in the selector, 
        // we assume just matching the tag is the best we can do.
        if (empty($id) && empty($name) && empty($selectorParts)) {
            return true;
        }

        return false;
    }

    /**
     * Extract the main HTML tag from the snippet.
     */
    protected function extractTagName(string $html): ?string
    {
        preg_match('/^<([a-zA-Z0-9\-]+)/', trim($html), $matches);
        return $matches[1] ?? null;
    }

    /**
     * Extract a specific attribute value from the HTML snippet.
     */
    protected function extractAttribute(string $html, string $attribute): ?string
    {
        // Matches `attr="value"` or `attr='value'`
        preg_match('/' . preg_quote($attribute, '/') . '\s*=\s*["']([^"']+)["']/i', $html, $matches);
        return $matches[1] ?? null;
    }
}
