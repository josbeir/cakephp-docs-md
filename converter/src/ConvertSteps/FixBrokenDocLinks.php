<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class FixBrokenDocLinks
{
    private ConvertCrossReferences $crossRefConverter;

    public function __construct(string $basePath = '/en', array $labelToDocumentMap = [])
    {
        $this->crossRefConverter = new ConvertCrossReferences($basePath, $labelToDocumentMap);
    }

    public function __invoke(string $content): string
    {
        // Pattern to match broken doc links: [path/to/doc` (missing closing bracket and URL)
        $content = preg_replace_callback('/\[([^]]*?)`(?!\()/m', function (array $matches): string {
            $path = trim($matches[1]);

            // Skip if this doesn't look like a file path
            if (empty($path) || !preg_match('/^[a-zA-Z0-9\/_-]+$/', $path)) {
                return $matches[0]; // Return original if not a path-like string
            }

            // Reconstruct as a :doc: directive and process through the cross-reference converter
            $reconstructed = sprintf(':doc:`%s`', $path);
            $converter = $this->crossRefConverter;

            return $converter($reconstructed);
        }, $content);

        return $content;
    }
}
