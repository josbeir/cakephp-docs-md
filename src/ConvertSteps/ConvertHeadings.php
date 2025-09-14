<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertHeadings
{
    public function __invoke(string $content): string
    {
        // Load heading mappings from config
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        $defaultLevelMapping = [
            '#' => 1, '*' => 1,
            '=' => 2,
            '-' => 3,
            '~' => 4,
            '^' => 5,
            '"' => 6,
        ];

        if (file_exists($configPath)) {
            $config = require $configPath;
            $levelMapping = $config['heading_mappings'] ?? $defaultLevelMapping;
        } else {
            $levelMapping = $defaultLevelMapping;
        }

        $lines = explode("\n", $content);
        $result = [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            if ($i + 1 < count($lines)) {
                $nextLine = $lines[$i + 1];
                $headingChars = array_keys($levelMapping);
                foreach ($headingChars as $char) {
                    if (
                        trim($nextLine) &&
                        str_repeat($char, strlen(trim($nextLine))) === trim($nextLine) &&
                        strlen(trim($nextLine)) >= strlen(trim($line)) &&
                        trim($line)
                    ) {
                        $level = $levelMapping[$char];
                        $result[] = str_repeat('#', $level) . ' ' . trim($line);
                        $i += 2;
                        continue 2;
                    }
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }
}
