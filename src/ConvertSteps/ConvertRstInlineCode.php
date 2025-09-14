<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertRstInlineCode
{
    public function __invoke(string $content): string
    {
        $content = preg_replace('/``([^`]+)``::/', '`$1`', $content);

        return preg_replace('/(?<!`)``([^`]+)``(?!:)(?!`)/', '`$1`', $content);
    }
}
