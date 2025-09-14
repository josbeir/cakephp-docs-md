<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class RemoveIndexDirectives
{
    private array $removedDirectives = [];

    public function __invoke(string $content): string
    {
        $this->removedDirectives = [];

        $content = preg_replace_callback('/^[ \t]*\.\. index::\s*(.+)$/m', function ($matches): string {
            $this->removedDirectives[] = trim($matches[1]);
            return '';
        }, $content);

        // Clean up extra empty lines left by removed directives
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        return $content;
    }

    public function getRemovedDirectives(): array
    {
        return $this->removedDirectives;
    }
}