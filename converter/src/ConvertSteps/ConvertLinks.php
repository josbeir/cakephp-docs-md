<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertLinks
{
    private string $basePath;

    private string $currentFile;

    public function __construct(string $basePath = '', string $currentFile = '')
    {
        $this->basePath = $basePath;
        $this->currentFile = $currentFile;
    }

    public function __invoke(string $content): string
    {
        $basePath = $this->basePath;
        $processUrl = function ($url) use ($basePath) {
            if (preg_match('/^https?:\/\//', $url)) {
                return $url;
            }

            if (str_starts_with($url, '#')) {
                return $url;
            }

            if (str_starts_with($url, '/') || (!str_contains($url, '://') && !str_ends_with($url, '.md') && !str_ends_with($url, '.html'))) {
                $url = rtrim($url, '/');
                $resolvedPath = $this->resolvePath($url);
                $targetPath = $resolvedPath . '.md';

                return $this->getRelativePath($targetPath);
            }

            return $url;
        };
        $content = preg_replace_callback(
            '/(?<![:\w])`([^`<]+)\s*<([^>]+)>`__/',
            function (array $matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );
        $content = preg_replace_callback(
            '/(?<![:\w])`([^`<]+)\s*<([^>]+)>`_(?![_`])/',
            function (array $matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );
        $content = preg_replace_callback(
            '/(?<![:\w])`([^<]+)<([^>]+)>`__/',
            function (array $matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );

        return preg_replace_callback(
            '/(?<![:\w])`([^<]+)<([^>]+)>`_(?![_`])/',
            function (array $matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );
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
}
