<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertContainers
{
    public function __invoke(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];
            if (preg_match('/^(\s*)\.\.\s+container::\s*(.*)$/', $line, $matches)) {
                [$_, $indent, $containerClass] = $matches;
                $result[] = $indent . '---';
                $result[] = '';
                $i++;
                $hasContent = false;
                while ($i < count($lines)) {
                    $nextLine = $lines[$i];
                    if (trim($nextLine) === '' || trim($nextLine) === '0') {
                        if ($hasContent) {
                            $result[] = '';
                        }
                    } elseif (str_starts_with($nextLine, $indent . '    ') || str_starts_with($nextLine, $indent . "\t")) {
                        $contentText = ltrim(substr($nextLine, strlen($indent)));
                        if (str_starts_with($contentText, '    ')) {
                            $contentText = substr($contentText, 4);
                        } elseif (str_starts_with($contentText, "\t")) {
                            $contentText = substr($contentText, 1);
                        }

                        $result[] = $indent . $contentText;
                        $hasContent = true;
                    } else {
                        break;
                    }

                    $i++;
                }

                $result[] = '';
                $result[] = $indent . '---';
                $result[] = '';
                continue;
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }
}
