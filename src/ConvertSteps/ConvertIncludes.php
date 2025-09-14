<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertIncludes
{
    public function __invoke(string $content): string
    {
        // Convert .. include:: /path/to/file.rst to <!--@include: ./path/to/file.md-->
        $content = preg_replace_callback('/^\s*\.\.\s+include::\s*(.+)$/m', function ($matches): string {
            $includePath = trim($matches[1]);
            $includePath = ltrim($includePath, '/');
            $includePath = preg_replace('/\.rst$/', '.md', $includePath);

            return sprintf('<!--@include: ./%s-->', $includePath);
        }, $content);

        return $content;
    }
}
