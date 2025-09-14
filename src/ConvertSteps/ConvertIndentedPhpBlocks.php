<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertIndentedPhpBlocks
{
    /**
     * Language detection patterns
     */
    private array $languagePatterns = [
        'php' => [
            '/<\?php/',
            '/<\?(?!=xml)/',
            '/namespace\s+[\w\\\\]+\s*;/',
            '/class\s+\w+/',
            '/function\s+\w+\s*\(/',
            '/\$\w+/',
            '/use\s+[\w\\\\]+\s*;/',
            '/public\s+function/',
            '/private\s+function/',
            '/protected\s+function/',
            '/->\w+\(/',
            '/::[\w$]+/',
            '/new\s+\w+\s*\(/',
            '/echo\s+/',
            '/return\s+/',
        ],
        'javascript' => [
            '/function\s*\(/',
            '/var\s+\w+/',
            '/let\s+\w+/',
            '/const\s+\w+/',
            '/console\.log\s*\(/',
            '/document\./',
            '/window\./',
            '/\$\(/', // jQuery
            '/=>\s*{/', // Arrow functions
        ],
        'sql' => [
            '/SELECT\s+/i',
            '/INSERT\s+INTO/i',
            '/UPDATE\s+/i',
            '/DELETE\s+FROM/i',
            '/CREATE\s+TABLE/i',
            '/ALTER\s+TABLE/i',
            '/DROP\s+TABLE/i',
            '/FROM\s+\w+/i',
            '/WHERE\s+/i',
            '/ORDER\s+BY/i',
        ],
        'bash' => [
            '/^\s*\$\s+/', // Shell prompt
            '/^\s*#\s+/', // Comments
            '/sudo\s+/',
            '/apt-get\s+/',
            '/composer\s+/',
            '/php\s+bin\//',
            '/mkdir\s+/',
            '/cd\s+/',
            '/ls\s+/',
            '/chmod\s+/',
        ],
        'yaml' => [
            '/^\s*\w+:\s*$/', // YAML keys
            '/^\s*-\s+\w+:/', // YAML list items with keys
        ],
        'json' => [
            '/^\s*{/', // JSON object start
            '/^\s*\[/', // JSON array start
            '/"\w+":\s*"/', // JSON key-value pairs
        ],
        'html' => [
            '/<\w+[^>]*>/', // HTML tags
            '/<!DOCTYPE/',
            '/<\/\w+>/',
        ],
        'css' => [
            '/\w+\s*{/', // CSS selectors
            '/[\w-]+:\s*[\w#%-]+;/', // CSS properties
        ],
    ];

    public function __invoke(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Skip empty lines
            if (trim($line) === '' || trim($line) === '0') {
                $result[] = $line;
                $i++;
                continue;
            }

            // Check for indented code blocks after double colon or backticks with double colon
            if ($this->isCodeBlockTrigger($line)) {
                $result[] = $line;
                $i++;

                // Skip empty lines after the trigger
                while ($i < count($lines) && !trim($lines[$i])) {
                    $result[] = $lines[$i];
                    $i++;
                }

                // Process the indented block if it exists
                if ($i < count($lines) && $this->isIndented($lines[$i])) {
                    $blockData = $this->extractIndentedBlock($lines, $i);
                    $language = $this->detectLanguage($blockData['content']);

                    // Add the code block with proper language
                    $result[] = "```$language";
                    foreach ($blockData['lines'] as $blockLine) {
                        $result[] = $blockLine;
                    }
                    $result[] = '```';

                    $i = $blockData['nextIndex'];
                    continue;
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    /**
     * Check if a line is a code block trigger (ends with :: or backticks::)
     */
    private function isCodeBlockTrigger(string $line): bool
    {
        return preg_match('/::$/', trim($line)) || preg_match('/``[^`]+``::$/', $line);
    }

    /**
     * Check if a line is indented (has leading whitespace)
     */
    private function isIndented(string $line): bool
    {
        return strlen($line) > strlen(ltrim($line)) && trim($line) !== '';
    }

    /**
     * Extract an indented code block and return the content, lines, and next index
     */
    private function extractIndentedBlock(array $lines, int $startIndex): array
    {
        $blockLines = [];
        $indent = strlen($lines[$startIndex]) - strlen(ltrim($lines[$startIndex]));
        $i = $startIndex;

        while ($i < count($lines)) {
            $currentLine = $lines[$i];

            // Empty lines are included in the block
            if (trim($currentLine) === '' || trim($currentLine) === '0') {
                $blockLines[] = '';
                $i++;
                continue;
            }

            $currentIndent = strlen($currentLine) - strlen(ltrim($currentLine));

            // If the line has the same or greater indentation, it's part of the block
            if ($currentIndent >= $indent) {
                // Remove the base indentation
                $blockLines[] = substr($currentLine, $indent);
                $i++;
            } else {
                // Different indentation level means end of block
                break;
            }
        }

        // Join all non-empty lines for language detection
        $content = implode("\n", array_filter($blockLines, 'trim'));

        return [
            'lines' => $blockLines,
            'content' => $content,
            'nextIndex' => $i
        ];
    }

    /**
     * Detect the programming language based on code content
     */
    private function detectLanguage(string $content): string
    {
        if (empty(trim($content))) {
            return '';
        }

        $scores = [];

        // Score each language based on pattern matches
        foreach ($this->languagePatterns as $language => $patterns) {
            $score = 0;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $score++;
                }
            }
            if ($score > 0) {
                $scores[$language] = $score;
            }
        }

        // Return the language with the highest score
        if (!empty($scores)) {
            arsort($scores);
            return array_key_first($scores);
        }

        return '';
    }
}
