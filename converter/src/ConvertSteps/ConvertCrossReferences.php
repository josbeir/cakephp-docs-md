<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertCrossReferences
{
    private string $basePath;

    private array $labelToDocumentMap;

    private array $titleCache = [];

    private string $currentFile;

    public function __construct(string $basePath = '', array $labelToDocumentMap = [], bool $useRelativePaths = true, string $currentFile = '')
    {
        $this->basePath = $basePath;
        $this->labelToDocumentMap = $labelToDocumentMap;
        $this->currentFile = $currentFile;
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
        $content = preg_replace_callback('/:abbr:`([^(]+?)\s*\(([^)]+?)\)`/s', function (array $matches): string {
            $abbr = trim($matches[1]);
            $fullForm = preg_replace('/\s+/', ' ', trim($matches[2]));

            return sprintf('<abbr title="%s">%s</abbr>', $fullForm, $abbr);
        }, $content);
        $content = preg_replace_callback('/:doc:`([^<]+)<([^>]+)>`/', function (array $matches) use ($basePath): string {
            $title = trim($matches[1]);
            $path = trim($matches[2]);
            if (preg_match('/^https?:\/\//', $path)) {
                return sprintf('[%s](%s)', $title, $path);
            }

            $targetPath = $this->resolvePath($path) . '.md';
            $relativePath = $this->getRelativePath($targetPath);

            return sprintf('[%s](%s)', $title, $relativePath);
        }, $content);
        $content = preg_replace_callback('/:doc:`([^`<]+)`/', function (array $matches) use ($basePath): string {
            $path = trim($matches[1]);
            if (preg_match('/^https?:\/\//', $path)) {
                return sprintf('[%s](%s)', $path, $path);
            }

            $resolvedPath = $this->resolvePath($path);
            $targetPath = $resolvedPath . '.md';
            $relativePath = $this->getRelativePath($targetPath);

            // Try to extract title from the target file (using basePath for file resolution)
            $fullPath = $basePath . '/' . $targetPath;
            $title = $this->extractTitle($fullPath);
            $linkText = $title ?: basename($resolvedPath); // Fallback to path if title not found

            return sprintf('[%s](%s)', $linkText, $relativePath);
        }, $content);
        $content = preg_replace_callback('/:ref:`([^<`]+)<([^>`]+)>`/', function (array $matches) use ($basePath, $labelToDocumentMap): string {
            $linkText = trim($matches[1]);
            $labelName = trim($matches[2]);
            $targetDocument = $labelToDocumentMap[$labelName] ?? null;
            if ($targetDocument) {
                $relativePath = $this->getRelativePath($targetDocument);

                return sprintf('[%s](%s#%s)', $linkText, $relativePath, $labelName);
            } else {
                return sprintf('[%s](#%s)', $linkText, $labelName);
            }
        }, $content);

        return preg_replace_callback('/:ref:`([^`]+)`/', function (array $matches) use ($basePath, $labelToDocumentMap): string {
            $reference = trim($matches[1]);
            $targetDocument = $labelToDocumentMap[$reference] ?? null;
            if ($targetDocument) {
                $relativePath = $this->getRelativePath($targetDocument);

                return sprintf('[%s](%s#%s)', $reference, $relativePath, $reference);
            } else {
                return sprintf('[%s](#%s)', $reference, $reference);
            }
        }, $content);
    }

    /**
     * Resolve a path from RST relative to the current file
     */
    private function resolvePath(string $path): string
    {
        // If no current file context, treat as absolute
        if (empty($this->currentFile)) {
            return ltrim($path, '/');
        }

        // If path starts with /, it's absolute from doc root
        if (str_starts_with($path, '/')) {
            return ltrim($path, '/');
        }

        // If path doesn't contain relative segments, it's relative to current directory
        if (!str_contains($path, '../') && !str_starts_with($path, './')) {
            // Resolve relative to current file's directory
            $currentDir = dirname($this->currentFile);
            if ($currentDir === '.' || $currentDir === '') {
                return $path;
            }

            return $currentDir . '/' . $path;
        }

        // Resolve relative path based on current file location
        $currentDir = dirname($this->currentFile);

        // If current file is at root, resolve relative to root
        if ($currentDir === '.' || $currentDir === '') {
            return ltrim($path, './');
        }

        // Build absolute path by combining current directory with relative path
        $pathParts = array_filter(explode('/', $currentDir));
        $relativeParts = explode('/', $path);

        foreach ($relativeParts as $part) {
            if ($part === '..') {
                array_pop($pathParts);
            } elseif ($part !== '.' && $part !== '') {
                $pathParts[] = $part;
            }
        }

        return implode('/', $pathParts);
    }

    /**
     * Calculate relative path from current file to target file
     */
    private function getRelativePath(string $targetPath): string
    {
        if (empty($this->currentFile)) {
            return $targetPath;
        }

        // Get the directory of the current file (relative to base)
        $currentDir = dirname($this->currentFile);

        // If current file is in root, return target as-is
        if ($currentDir === '.' || $currentDir === '') {
            return $targetPath;
        }

        // Split paths into components
        $currentParts = array_filter(explode('/', $currentDir));
        $targetParts = array_filter(explode('/', $targetPath));

        // Find common path components
        $commonLength = 0;
        $minLength = min(count($currentParts), count($targetParts));

        for ($i = 0; $i < $minLength; $i++) {
            if ($currentParts[$i] === $targetParts[$i]) {
                $commonLength++;
            } else {
                break;
            }
        }

        // Calculate relative path
        $upLevels = count($currentParts) - $commonLength;
        $downPath = array_slice($targetParts, $commonLength);

        $relativePath = str_repeat('../', $upLevels) . implode('/', $downPath);

        return $relativePath ?: $targetPath;
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
