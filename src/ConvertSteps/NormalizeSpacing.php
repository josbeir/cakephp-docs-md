<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class NormalizeSpacing
{
    public function __invoke(string $content): string
    {
        // Split content into lines for easier processing
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check if this is the start of an alert block
            if (preg_match('/^> \[!(?:NOTE|WARNING|TIP|IMPORTANT|CAUTION)\]/', $line)) {
                // Ensure blank line before alert if previous line isn't blank
                if (!empty($result) && trim(end($result)) !== '') {
                    $result[] = '';
                }

                // Add the alert block
                $result[] = $line;
                $i++;

                // Continue adding alert lines (lines starting with >)
                while ($i < count($lines) && preg_match('/^>/', $lines[$i])) {
                    $result[] = $lines[$i];
                    $i++;
                }

                // Ensure blank line after alert if next line exists and isn't blank
                if ($i < count($lines) && trim($lines[$i]) !== '') {
                    $result[] = '';
                }
                continue;
            }

            // Check if this is the start of a code block
            if (preg_match('/^```/', $line)) {
                // Ensure blank line before code block if previous line isn't blank
                if (!empty($result) && trim(end($result)) !== '') {
                    $result[] = '';
                }

                // Add the code block
                $result[] = $line;
                $i++;

                // Continue until closing ```
                while ($i < count($lines)) {
                    $result[] = $lines[$i];
                    if (preg_match('/^```\s*$/', $lines[$i])) {
                        $i++;
                        break;
                    }
                    $i++;
                }

                // Ensure blank line after code block if next line exists and isn't blank
                if ($i < count($lines) && trim($lines[$i]) !== '') {
                    $result[] = '';
                }
                continue;
            }

            // Regular line - just add it
            $result[] = $line;
            $i++;
        }

        // Join lines back together
        $content = implode("\n", $result);

        // Clean up any excessive blank lines (more than 2)
        $content = preg_replace('/\n{4,}/', "\n\n\n", $content);

        return $content;
    }
}