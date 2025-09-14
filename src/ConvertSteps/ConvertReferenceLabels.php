<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertReferenceLabels
{
    public function __invoke(string $content): string
    {
        // Convert RST reference labels like .. _label-name: to HTML anchors
        $content = preg_replace('/^\s*\.\.\s+_([^:]+):\s*$/m', '<a id="$1"></a>', $content);

        return $content;
    }
}
