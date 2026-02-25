<?php

namespace LaravelLens\LaravelLens\DTOs;

class Issue
{
    public function __construct(
        public string $id,
        public string $impact,
        public string $description,
        public string $helpUrl,
        public string $htmlSnippet,
        public string $selector,
        public ?string $fileName = null,
        public ?int $lineNumber = null
    ) {}
}
