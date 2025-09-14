<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertImages
{
    public function __invoke(string $content): string
    {
        // First handle .. image:: directives
        $content = preg_replace_callback('/^\s*\.\.\s+image::\s*(.+)$/m', function ($matches): string {
            $imagePath = trim($matches[1]);
            $imagePath = ltrim($imagePath, '/');

            return sprintf('![](%s)', $imagePath);
        }, $content);

        // Then handle .. figure:: directives with their options
        $content = $this->convertFigureDirectives($content);

        return $content;
    }

    /**
     * Convert RST figure directives to markdown images
     */
    private function convertFigureDirectives(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for figure directive
            if (preg_match('/^\s*\.\.\s+figure::\s*(.+)$/', $line, $matches)) {
                $imagePath = trim($matches[1]);
                $imagePath = ltrim($imagePath, '/');

                $altText = '';
                $i++; // Move to next line

                // Process figure options (like :align:, :alt:, etc.)
                while ($i < count($lines)) {
                    $optionLine = $lines[$i];

                    // Check for option lines (may or may not be indented, starting with :)
                    if (preg_match('/^\s*:alt:\s*(.+)$/', $optionLine, $altMatches)) {
                        $altText = trim($altMatches[1]);
                        $i++;
                    } elseif (preg_match('/^\s*:\w+:\s*.*$/', $optionLine)) {
                        // Other options like :align:, :width:, etc. - skip for now
                        // Could be extended later if needed
                        $i++;
                    } else {
                        // If it's not an option line, we've reached the end
                        // Don't increment i here as this line should be processed normally
                        break;
                    }
                }

                // Generate markdown image with alt text if provided
                if ($altText) {
                    $result[] = sprintf('![%s](%s)', $altText, $imagePath);
                } else {
                    $result[] = sprintf('![](%s)', $imagePath);
                }

                continue;
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }
}
