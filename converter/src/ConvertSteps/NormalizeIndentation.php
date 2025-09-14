<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class NormalizeIndentation
{
    public function __invoke(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $inCodeBlock = false;
        $inBlockQuote = false;
        $afterCodeBlockMarker = false;
        foreach ($lines as $i => $line) {
            if (preg_match('/^```/', $line)) {
                $inCodeBlock = !$inCodeBlock;
                $result[] = $line;
                continue;
            }

            if ($i > 0 && str_ends_with(rtrim($lines[$i - 1]), '::')) {
                $afterCodeBlockMarker = true;
            }

            if ($afterCodeBlockMarker && !preg_match('/^\s{4,}/', $line) && trim($line)) {
                $afterCodeBlockMarker = false;
            }

            if (preg_match('/^\s*>/', $line)) {
                $inBlockQuote = true;
                $result[] = $line;
                continue;
            } elseif ($inBlockQuote && !trim($line)) {
                $result[] = $line;
                continue;
            } elseif ($inBlockQuote && !preg_match('/^\s*>/', $line) && trim($line)) {
                $inBlockQuote = false;
            }

            if ($inCodeBlock || $inBlockQuote || $afterCodeBlockMarker) {
                $result[] = $line;
                continue;
            }

            if (preg_match('/^(\s{3,})(.+)$/', $line, $matches)) {
                $indent = $matches[1];
                $textContent = $matches[2];
                $looksLikeCode = preg_match('/^[\$<>#]/', $textContent) ||
                                 preg_match('/^\w+\s*[=:(\[]/', $textContent) ||
                                 preg_match('/^(public|private|protected|class|function|namespace|use|return|if|for|while|echo|var|let|const)\s/', $textContent) ||
                                 preg_match('/^\w+\(\)/', $textContent) ||
                                 preg_match('/^\/\*|^\/\/|^\*|^<!--/', $textContent) ||
                                 preg_match('/^\{|\}$/', $textContent) ||
                                 preg_match('/^[A-Z_]+\s*=/', $textContent) ||
                                 preg_match('/^\/\/|^#|^\*\s/', $textContent) ||
                                 preg_match('/^(array|->|::|\$[a-zA-Z])/', $textContent);
                if ($looksLikeCode) {
                    $result[] = $line;
                } else {
                    $result[] = $textContent;
                }
            } else {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }
}
