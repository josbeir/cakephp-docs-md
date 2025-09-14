<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertLists
{
    public function __invoke(string $content): string
    {
        $content = preg_replace('/^(\s*)\*\s+/m', '$1- ', $content);

        return preg_replace('/^(\s*)(\d+)\.\s+/m', '$1$2. ', $content);
    }
}
