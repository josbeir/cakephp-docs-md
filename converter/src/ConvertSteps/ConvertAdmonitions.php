<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertAdmonitions
{
    private array $directiveMapping;

    public function __construct()
    {
        // Load mappings from config
        $configPath = dirname(__DIR__, 2) . '/config/config.php';
        $defaultMappings = [
            'note' => 'NOTE',
            'warning' => 'WARNING',
            'tip' => 'TIP',
            'seealso' => 'NOTE',
            'important' => 'IMPORTANT',
            'caution' => 'CAUTION',
        ];

        if (file_exists($configPath)) {
            $config = require $configPath;
            $mappings = $config['admonition_mappings'] ?? $defaultMappings;
        } else {
            $mappings = $defaultMappings;
        }

        // Convert to full directive format
        $this->directiveMapping = [];
        foreach ($mappings as $directive => $type) {
            $this->directiveMapping[$directive] = sprintf('> [!%s]', $type);
        }
    }

    public function __invoke(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (preg_match('/^(\s*)\.\.\s+(note|warning|tip|important|caution|seealso)::\s*(.*)$/', $line, $matches)) {
                [$_, $indent, $directive, $contentLine] = $matches;
                if (isset($this->directiveMapping[$directive])) {
                    $result[] = $this->directiveMapping[$directive];
                    if (trim($contentLine) !== '' && trim($contentLine) !== '0') {
                        $result[] = '> ' . trim($contentLine);
                    }

                    $i++;
                    $isFirstContentLine = !trim($contentLine);
                    while ($i < count($lines)) {
                        $nextLine = $lines[$i];
                        if (trim($nextLine) === '' || trim($nextLine) === '0') {
                            if (!$isFirstContentLine) {
                                $result[] = '>';
                            }
                        } elseif (str_starts_with($nextLine, $indent . '    ') || str_starts_with($nextLine, $indent . "\t")) {
                            $contentText = ltrim(substr($nextLine, strlen($indent)));
                            if ($contentText !== '' && $contentText !== '0') {
                                $result[] = '> ' . $contentText;
                                $isFirstContentLine = false;
                            } elseif (!$isFirstContentLine) {
                                $result[] = '>';
                            }
                        } else {
                            break;
                        }

                        $i++;
                    }

                    continue;
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }
}
