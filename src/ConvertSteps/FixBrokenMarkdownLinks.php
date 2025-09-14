<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class FixBrokenMarkdownLinks
{
    public function __invoke(string $content): string
    {
        $content = preg_replace('/`([^`]+)\]\(([^)]+)\.md\)_+/', '[$1]($2)', $content);

        return preg_replace('/`([^`]+)\]\(([^)]+)\)_+/', '[$1]($2)', $content);
    }
}
