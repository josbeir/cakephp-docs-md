<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertCodeBlocks
{
    public function __invoke(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $inCodeBlock = false;
        $codeIndent = 0;
        $currentLanguage = '';
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (preg_match('/^\s*\.\.\s+code-block::\s*(.*)/', $line, $matches)) {
                if ($inCodeBlock) {
                    $result[] = '```';
                    $inCodeBlock = false;
                }

                $language = trim($matches[1]);
                // Apply language mappings from config
                $configPath = dirname(__DIR__, 2) . '/config/config.php';
                $mappings = ['console' => 'bash', 'mysql' => 'sql', 'apacheconf' => 'apache']; // fallback
                if (file_exists($configPath)) {
                    $config = require $configPath;
                    $mappings = $config['code_language_mappings'] ?? $mappings;
                }

                $language = $mappings[$language] ?? $language;
                $result[] = '';
                $result[] = '```' . $language;
                $inCodeBlock = true;
                $currentLanguage = $language;
                $j = $i + 1;

                // Skip empty lines but don't add them to output right after opening backticks
                while ($j < count($lines) && !trim($lines[$j])) {
                    $j++;
                }

                if ($j < count($lines)) {
                    $codeIndent = strlen($lines[$j]) - strlen(ltrim($lines[$j]));
                }

                $i = $j;
                continue;
            }

            if (
                str_ends_with(rtrim($line), '::') && !$inCodeBlock &&
                !preg_match('/^\s*\.\.\s+(note|warning|tip|important|caution|seealso)::\s*/', $line) &&
                !str_contains($line, '> [!') // Don't process already converted admonitions
            ) {
                $textBefore = rtrim(substr(rtrim($line), 0, -2));
                if ($textBefore !== '' && $textBefore !== '0') {
                    $result[] = $textBefore;
                    $result[] = '';
                }

                // Look ahead to detect language from the indented content
                $language = $this->detectLanguageFromIndentedBlock($lines, $i + 1);

                $result[] = '```' . $language;
                $inCodeBlock = true;
                $currentLanguage = $language;
                $j = $i + 1;

                // Skip empty lines but don't add them to output right after opening backticks
                while ($j < count($lines) && !trim($lines[$j])) {
                    $j++;
                }

                if ($j < count($lines)) {
                    $codeIndent = strlen($lines[$j]) - strlen(ltrim($lines[$j]));
                }

                $i = $j;
                continue;
            }

            if ($inCodeBlock) {
                if (trim($line) === '' || trim($line) === '0') {
                    $result[] = $line;
                } elseif (strlen($line) - strlen(ltrim($line)) >= $codeIndent) {
                    $codeContent = substr($line, $codeIndent);
                    if (preg_match('/^```/', $codeContent)) {
                        $codeContent = '\\' . $codeContent;
                    }

                    $result[] = $codeContent;
                } else {
                    $result[] = '```';
                    $result[] = '';
                    $result[] = $line;
                    $inCodeBlock = false;
                    $currentLanguage = '';
                    $codeIndent = 0;
                }
            } else {
                $result[] = $line;
            }

            $i++;
        }

        if ($inCodeBlock) {
            $result[] = '```';
        }

        return implode("\n", $result);
    }

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

    /**
     * Detect language from upcoming indented block content
     */
    private function detectLanguageFromIndentedBlock(array $lines, int $startIndex): string
    {
        $content = '';
        $i = $startIndex;

        // Skip empty lines
        while ($i < count($lines) && !trim($lines[$i])) {
            $i++;
        }

        // If no content found, return empty
        if ($i >= count($lines)) {
            return '';
        }

        // Get the indentation level
        $indent = strlen($lines[$i]) - strlen(ltrim($lines[$i]));

        // Extract indented content for analysis
        while ($i < count($lines)) {
            $line = $lines[$i];

            if (trim($line) === '' || trim($line) === '0') {
                $content .= "\n";
                $i++;
                continue;
            }

            $currentIndent = strlen($line) - strlen(ltrim($line));
            if ($currentIndent >= $indent) {
                $content .= substr($line, $indent) . "\n";
                $i++;
            } else {
                break;
            }
        }

        return $this->detectLanguage(trim($content));
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
