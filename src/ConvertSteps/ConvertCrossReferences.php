<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertCrossReferences
{
    private string $basePath;

    private array $labelToDocumentMap;

    private array $titleCache = [];

    public function __construct(string $basePath = '', array $labelToDocumentMap = [])
    {
        $this->basePath = $basePath;
        $this->labelToDocumentMap = $labelToDocumentMap;
    }

    public function __invoke(string $content): string
    {
        $basePath = $this->basePath;
        $labelToDocumentMap = $this->labelToDocumentMap;
        // Helper function to clean PHP references (remove tilde and convert double backslashes)
        $cleanPhpReference = function ($matches): string {
            $reference = $matches[1];
            $reference = ltrim($reference, '~');
            $reference = str_replace('\\\\', '\\', $reference);

            return '`' . $reference . '`';
        };
        $content = preg_replace_callback('/:php:class:`([^`]+)`/', $cleanPhpReference, $content);
        $content = preg_replace_callback('/:php:meth:`([^`]+)`/', $cleanPhpReference, $content);
        $content = preg_replace_callback('/:php:attr:`([^`]+)`/', $cleanPhpReference, $content);
        $content = preg_replace_callback('/:php:func:`([^`]+)`/', $cleanPhpReference, $content);
        $content = preg_replace_callback('/:php:const:`([^`]+)`/', $cleanPhpReference, $content);
        $content = preg_replace_callback('/:php:exc:`([^`]+)`/', $cleanPhpReference, $content);
        $content = preg_replace_callback('/:abbr:`([^(]+?)\s*\(([^)]+?)\)`/s', function ($matches): string {
            $abbr = trim($matches[1]);
            $fullForm = preg_replace('/\s+/', ' ', trim($matches[2]));

            return sprintf('<abbr title="%s">%s</abbr>', $fullForm, $abbr);
        }, $content);
        $content = preg_replace_callback('/:doc:`([^<]+)<([^>]+)>`/', function ($matches) use ($basePath): string {
            $title = trim($matches[1]);
            $path = trim($matches[2]);
            if (preg_match('/^https?:\/\//', $path)) {
                return sprintf('[%s](%s)', $title, $path);
            }

            $path = ltrim($path, '/');

            return sprintf('[%s](%s/%s.md)', $title, $basePath, $path);
        }, $content);
        $content = preg_replace_callback('/:doc:`([^`<]+)`/', function ($matches) use ($basePath): string {
            $path = trim($matches[1]);
            if (preg_match('/^https?:\/\//', $path)) {
                return sprintf('[%s](%s)', $path, $path);
            }

            $path = ltrim($path, '/');
            $fullPath = $basePath . '/' . $path . '.md';

            // Try to extract title from the target file
            $title = $this->extractTitle($fullPath);
            $linkText = $title ?: $path; // Fallback to path if title not found

            return sprintf('[%s](%s)', $linkText, $fullPath);
        }, $content);
        $content = preg_replace_callback('/:ref:`([^<`]+)<([^>`]+)>`/', function ($matches) use ($basePath, $labelToDocumentMap): string {
            $linkText = trim($matches[1]);
            $labelName = trim($matches[2]);
            $targetDocument = $labelToDocumentMap[$labelName] ?? null;
            if ($targetDocument) {
                return sprintf('[%s](%s/%s#%s)', $linkText, $basePath, $targetDocument, $labelName);
            } else {
                return sprintf('[%s](#%s)', $linkText, $labelName);
            }
        }, $content);

        return preg_replace_callback('/:ref:`([^`]+)`/', function ($matches) use ($basePath, $labelToDocumentMap): string {
            $reference = trim($matches[1]);
            $targetDocument = $labelToDocumentMap[$reference] ?? null;
            if ($targetDocument) {
                return sprintf('[%s](%s/%s#%s)', $reference, $basePath, $targetDocument, $reference);
            } else {
                return sprintf('[%s](#%s)', $reference, $reference);
            }
        }, $content);
    }

    /**
     * Extract title from a markdown file
     */
    private function extractTitle(string $filePath): ?string
    {
        // Check cache first
        if (isset($this->titleCache[$filePath])) {
            return $this->titleCache[$filePath];
        }

        // Try to find the file
        $fullPath = __DIR__ . '/../../docs' . $filePath;
        if (!file_exists($fullPath)) {
            $this->titleCache[$filePath] = null;
            return null;
        }

        $content = file_get_contents($fullPath);
        if (!$content) {
            $this->titleCache[$filePath] = null;
            return null;
        }

        // First try to extract from YAML frontmatter
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n/s', $content, $matches)) {
            $frontmatter = $matches[1];
            if (preg_match('/^title:\s*(.+)$/m', $frontmatter, $titleMatch)) {
                $title = trim($titleMatch[1], '"\'');
                $this->titleCache[$filePath] = $title;
                return $title;
            }
        }

        // Fallback to first heading
        if (preg_match('/^#\s+(.+)$/m', $content, $headingMatch)) {
            $title = trim($headingMatch[1]);
            $this->titleCache[$filePath] = $title;
            return $title;
        }

        $this->titleCache[$filePath] = null;
        return null;
    }
}
