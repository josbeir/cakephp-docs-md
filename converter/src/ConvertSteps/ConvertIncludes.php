<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertIncludes
{
    private string $currentFile;

    public function __construct(string $currentFile = '')
    {
        $this->currentFile = $currentFile;
    }

    public function __invoke(string $content): string
    {
        // First, fix existing malformed includes (already converted but with leftover metadata)
        $content = $this->fixExistingIncludes($content);

        // Then process any remaining RST includes
        // First, convert simple includes without options
        $content = preg_replace_callback(
            '/^\s*\.\.\s+include::\s*(.+)$/m',
            function (array $matches): string {
                $includePath = trim($matches[1]);
                $resolvedPath = $this->resolvePath($includePath);
                $resolvedPath = preg_replace('/\.rst$/', '.md', $resolvedPath);

                $relativePath = $this->getRelativePath($resolvedPath);

                return sprintf('<!--@include: %s-->', $relativePath);
            },
            $content,
        );

        // Then, handle includes with options by finding them as multi-line blocks
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check if this line is an include directive we already converted
            if (preg_match('/<!--@include:\s*(.+?)-->/', $line, $includeMatch)) {
                $includePath = trim($includeMatch[1]);

                // Look ahead for include options
                $startLine = null;
                $endLine = null;
                $endBefore = null;
                $j = $i + 1;

                while ($j < count($lines) && preg_match('/^\s*:(?:start-line|end-before|end-line|lines):\s*(.+)$/', $lines[$j], $optionMatch)) {
                    $optionLine = $lines[$j];

                    if (preg_match('/:start-line:\s*(\d+)/', $optionLine, $startMatch)) {
                        $startLine = (int)$startMatch[1];
                    } elseif (preg_match('/:end-before:\s*(.+)/', $optionLine, $endMatch)) {
                        $endBefore = trim($endMatch[1]);
                    } elseif (preg_match('/:end-line:\s*(\d+)/', $optionLine, $endLineMatch)) {
                        $endLine = (int)$endLineMatch[1];
                    }

                    $j++;
                }

                // Build the enhanced include directive with resolved and relative path
                $resolvedPath = $this->resolvePath($includePath);
                $relativePath = $this->getRelativePath($resolvedPath);
                $includeDirective = sprintf('<!--@include: %s', $relativePath);

                // Add line range specification
                if ($startLine !== null && $endLine !== null) {
                    $includeDirective .= sprintf('{%d,%d}', $startLine, $endLine);
                } elseif ($startLine !== null) {
                    $includeDirective .= sprintf('{%d,}', $startLine);
                } elseif ($endLine !== null) {
                    $includeDirective .= sprintf('{,%d}', $endLine);
                }

                // Note: VitePress doesn't support end-before directive
                // We skip adding this as a comment to keep clean VitePress syntax

                $includeDirective .= '-->';

                $result[] = $includeDirective;

                // Skip the option lines we processed
                $i = $j;
            } else {
                $result[] = $line;
                $i++;
            }
        }

        $content = implode("\n", $result);

        // Clean up any remaining orphaned option lines
        $content = preg_replace('/^\s*:(?:start-line|start-after|end-before|end-line|lines):\s*.+$/m', '', $content);

        // Clean up multiple consecutive empty lines
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);

        return $content;
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

        // If path doesn't contain relative segments, it's relative to document base
        if (!str_contains($path, '../') && !str_starts_with($path, './')) {
            // Extract document base from current file (e.g., "docs/4/en" from "docs/4/en/views/helpers/text.md")
            $currentDir = dirname($this->currentFile);
            if ($currentDir === '.' || $currentDir === '') {
                return $path;
            }

            // Find the document base (assumes structure like docs/X/en or docs/X/lang)
            $pathParts = explode('/', $currentDir);
            if (count($pathParts) >= 3 && $pathParts[0] === 'docs') {
                $docBase = implode('/', array_slice($pathParts, 0, 3)); // e.g., "docs/4/en"

                return $docBase . '/' . $path;
            }

            // Fallback: resolve relative to current directory
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
     * Fix existing malformed includes that have leftover RST metadata
     */
    private function fixExistingIncludes(string $content): string
    {
        // First, clean up includes that already have line ranges but have RST-style comments
        $content = preg_replace('/<!--@include:\s*([^{]+\{[^}]*\})\s*<!--\s*[^>]*\s*-->/', '<!--@include: $1-->', $content);
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check if this line is an existing include directive
            if (preg_match('/<!--@include:\s*(.+?)-->/', $line, $includeMatch)) {
                $includePath = trim($includeMatch[1]);

                // Look ahead for orphaned include options
                $startLine = null;
                $endLine = null;
                $endBefore = null;
                $startAfter = null;
                $j = $i + 1;

                while ($j < count($lines)) {
                    $nextLine = $lines[$j];

                    // Skip empty lines
                    if (trim($nextLine) === '') {
                        $j++;
                        continue;
                    }

                    // Check for include options
                    if (preg_match('/^\s*:start-line:\s*(\d+)\s*$/', $nextLine, $startMatch)) {
                        $startLine = (int)$startMatch[1];
                        $j++;
                        continue;
                    } elseif (preg_match('/^\s*:start-after:\s*(.+)\s*$/', $nextLine, $startAfterMatch)) {
                        $startAfter = trim($startAfterMatch[1]);
                        $j++;
                        continue;
                    } elseif (preg_match('/^\s*:end-before:\s*(.+)\s*$/', $nextLine, $endMatch)) {
                        $endBefore = trim($endMatch[1]);
                        $j++;
                        continue;
                    } elseif (preg_match('/^\s*:end-line:\s*(\d+)\s*$/', $nextLine, $endLineMatch)) {
                        $endLine = (int)$endLineMatch[1];
                        $j++;
                        continue;
                    } else {
                        // Not an include option, stop looking
                        break;
                    }
                }

                // Build the corrected include directive with resolved and relative path
                $resolvedPath = $this->resolvePath($includePath);
                $relativePath = $this->getRelativePath($resolvedPath);
                $includeDirective = sprintf('<!--@include: %s', $relativePath);

                // Add line range specification
                if ($startLine !== null && $endLine !== null) {
                    $includeDirective .= sprintf('{%d,%d}', $startLine, $endLine);
                } elseif ($startLine !== null) {
                    $includeDirective .= sprintf('{%d,}', $startLine);
                } elseif ($endLine !== null) {
                    $includeDirective .= sprintf('{,%d}', $endLine);
                }

                // Note: VitePress doesn't support start-after/end-before in include syntax
                // These are RST-specific features that would need manual handling
                // For now, we'll just use the clean VitePress syntax without these options

                $includeDirective .= '-->';

                $result[] = $includeDirective;

                // Skip the option lines we processed
                $i = $j;
            } else {
                $result[] = $line;
                $i++;
            }
        }

        return implode("\n", $result);
    }
}
