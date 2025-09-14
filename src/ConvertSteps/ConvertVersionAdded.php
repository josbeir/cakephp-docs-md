<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertVersionAdded
{
    public function __invoke(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (preg_match('/^(\s*)\.\.\s+versionadded::\s*(.*)$/', $line, $matches)) {
                [$_, $indent, $version] = $matches;
                $result[] = '> [!IMPORTANT]';
                $result[] = '> Added in version ' . trim($version);
                $i++;
                while ($i < count($lines)) {
                    $nextLine = $lines[$i];
                    if (trim($nextLine) === '' || trim($nextLine) === '0') {
                        $result[] = '>';
                    } elseif (str_starts_with($nextLine, $indent . '    ') || str_starts_with($nextLine, $indent . "\t")) {
                        $contentText = ltrim(substr($nextLine, strlen($indent)));
                        if ($contentText !== '' && $contentText !== '0') {
                            $result[] = '> ' . $contentText;
                        } else {
                            $result[] = '>';
                        }
                    } else {
                        break;
                    }

                    $i++;
                }

                continue;
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }
}
