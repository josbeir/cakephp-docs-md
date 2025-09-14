<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class HandleMetaDirective
{
    public function __invoke(string $content): string
    {
        $lines = explode("\n", $content);
        $metaContent = [];
        $result = [];
        $inMeta = false;

        foreach ($lines as $line) {
            if (trim($line) === '.. meta::') {
                $inMeta = true;
                continue;
            } elseif ($inMeta) {
                if (str_starts_with($line, '    :') || str_starts_with($line, "\t:")) {
                    $metaLine = trim($line);
                    if (str_starts_with($metaLine, ':') && str_contains(substr($metaLine, 1), ':')) {
                        [$prop, $value] = explode(':', substr($metaLine, 1), 2);
                        $cleanProp = trim($prop);
                        $cleanValue = trim($value);
                        $cleanProp = preg_replace('/\s+lang=\w+/', '', $cleanProp);
                        $cleanProp = str_replace(' ', '_', trim($cleanProp));
                        if (preg_match('/^[a-zA-Z_]\w*$/', $cleanProp)) {
                            if (preg_match('/[,:]/', $cleanValue)) {
                                $cleanValue = '"' . str_replace('"', '\\"', $cleanValue) . '"';
                            }

                            $metaContent[] = $cleanProp . ': ' . $cleanValue;
                        }
                    }
                } elseif (trim($line) === '' || trim($line) === '0') {
                    continue;
                } else {
                    $inMeta = false;
                    $result[] = $line;
                }
            } else {
                $result[] = $line;
            }
        }

        if ($metaContent !== []) {
            $frontMatter = array_merge(['---'], $metaContent, ['---', '']);

            return implode("\n", array_merge($frontMatter, $result));
        }

        return implode("\n", $result);
    }
}
