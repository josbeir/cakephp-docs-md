<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertMiscDirectives
{
    public function __invoke(string $content): string
    {
        // Convert .. versionchanged:: and similar directives (but not versionadded - handled separately)
        $content = preg_replace('/^\s*\.\.\s+(versionchanged|deprecated)::\s*(.*)$/m', '> **$1:** $2', $content);
        // Convert .. toctree:: directive - just remove it and its content
        $lines = explode("\n", $content);
        $result = [];
        $inToctree = false;
        $toctreeIndent = 0;
        foreach ($lines as $line) {
            if (preg_match('/^\s*\.\.\s+toctree::\s*$/', $line)) {
                $inToctree = true;
                $toctreeIndent = strlen($line) - strlen(ltrim($line));
                continue;
            }

            if ($inToctree) {
                if (trim($line) === '') {
                    continue;
                } elseif (preg_match('/^\s*:/', $line)) {
                    continue;
                } elseif (strlen($line) - strlen(ltrim($line)) > $toctreeIndent) {
                    continue;
                } else {
                    $inToctree = false;
                    $result[] = $line;
                }
            } else {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }
}
