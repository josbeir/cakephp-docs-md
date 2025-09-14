<?php
// phpcs:ignoreFile
declare(strict_types=1);

use Cake\DocsMD\ConvertSteps\RemoveIndexDirectives;

require_once __DIR__ . '/../vendor/autoload.php';

use Cake\DocsMD\Converter;

function showHelp(): void
{
    echo <<<HELP
CakePHP Documentation RST to Markdown Converter

USAGE:
    php convert.php [input_dir] [output_dir]

ARGUMENTS:
    input_dir    Input directory containing RST files (default: ./en)
    output_dir   Output directory for Markdown files (default: ./docs)

OPTIONS:
    -h, --help   Show this help message

EXAMPLES:
    php convert.php
    php convert.php ./en ./docs
    php convert.php ./en ./output/markdown

DESCRIPTION:
    This script converts CakePHP's RST documentation to Markdown format while
    preserving custom Sphinx directives. It handles:

    - PHP-specific directives (.. php:class::, .. php:method::, etc.)
    - Cross-references (:php:class:, :doc:, :ref:, etc.)
    - Standard directives (.. note::, .. warning::, etc.)
    - Code blocks and syntax highlighting
    - Heading conversions
    - List formatting

    The converter focuses on the /en folder by default but can process any
    RST directory structure.

HELP;
}

function findRstFiles(string $dir): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'rst') {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function buildLabelToDocumentMap(string $enDir): array
{
    $labelToDocumentMap = [];
    $rstFiles = findRstFiles($enDir);
    foreach ($rstFiles as $rstFile) {
        $content = file_get_contents($rstFile);
        $relativePath = str_replace($enDir . '/', '', $rstFile);
        $mdPath = preg_replace('/\.rst$/', '.md', $relativePath);
        if (preg_match_all('/^\s*\.\.\s+_([^:]+):\s*$/m', $content, $matches)) {
            foreach ($matches[1] as $label) {
                $labelToDocumentMap[trim($label)] = $mdPath;
            }
        }
    }

    return $labelToDocumentMap;
}

function copyDirectory(string $src, string $dst): void
{
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }

    while (($file = readdir($dir)) !== false) {
        if ($file !== '.' && $file !== '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }

    closedir($dir);
}

function removeDirectory(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }

    rmdir($dir);
}

function convertFile(
    string $rstFile,
    string $outputDir,
    string $basePath,
    array $labelToDocumentMap,
    ?string $inputBaseDir = null,
): array {
    $content = file_get_contents($rstFile);
    
    // Calculate current file path relative to input base directory
    $currentFileRelative = '';
    if ($inputBaseDir !== null) {
        $currentFileRelative = str_replace($inputBaseDir . '/', '', $rstFile);
        $currentFileRelative = preg_replace('/\.rst$/', '.md', $currentFileRelative);
    }
    
    $pipeline = Converter::getPipeline($basePath, $labelToDocumentMap, $currentFileRelative);
    $removedIndexDirectives = [];

    foreach ($pipeline as $step) {
        $content = $step($content);
        // Collect removed index directives if this step is RemoveIndexDirectives
        if ($step instanceof RemoveIndexDirectives) {
            $removedIndexDirectives = $step->getRemovedDirectives();
        }
    }

    // Calculate output file path
    if ($inputBaseDir !== null) {
        $relativePath = str_replace($inputBaseDir . '/', '', $rstFile);
    } else {
        $inputDir = dirname($rstFile);
        $relativePath = str_replace(dirname($inputDir) . '/', '', $rstFile);
    }

    $mdFile = $outputDir . '/' . preg_replace('/\.rst$/', '.md', $relativePath);
    $mdDir = dirname($mdFile);
    if (!is_dir($mdDir)) {
        mkdir($mdDir, 0755, true);
    }

    file_put_contents($mdFile, $content);
    echo sprintf('Converted: %s -> %s%s', $rstFile, $mdFile, PHP_EOL);

    return $removedIndexDirectives;
}

function convertDirectory(string $inputDir, string $outputDir): void
{
    echo sprintf('Converting RST files from %s to %s%s', $inputDir, $outputDir, PHP_EOL);
    $basePath = '/' . trim(basename(realpath($inputDir)), '/');
    echo sprintf('Using base path for file resolution: %s%s', $basePath, PHP_EOL);
    echo 'Generating relative paths in markdown links' . PHP_EOL;
    // Copy static files and other assets
    $staticDir = $inputDir . '/_static';
    if (is_dir($staticDir)) {
        $outputStatic = $outputDir . '/_static';
        if (is_dir($outputStatic)) {
            removeDirectory($outputStatic);
        }

        copyDirectory($staticDir, $outputStatic);
        echo sprintf('Copied static files to %s%s', $outputStatic, PHP_EOL);
    }

    // Build label map
    $labelToDocumentMap = buildLabelToDocumentMap($inputDir);
    // Find and convert all RST files
    $rstFiles = findRstFiles($inputDir);
    $allRemovedDirectives = [];

    foreach ($rstFiles as $rstFile) {
        try {
            $removedDirectives = convertFile($rstFile, $outputDir, $basePath, $labelToDocumentMap, $inputDir);
            if ($removedDirectives !== []) {
                $relativePath = str_replace($inputDir . '/', '', $rstFile);
                $allRemovedDirectives[$relativePath] = $removedDirectives;
            }
        } catch (Exception $e) {
            echo sprintf('Error converting %s: ', $rstFile) . $e->getMessage() . "\n";
        }
    }

    echo 'Conversion complete. Processed ' . count($rstFiles) . " files.\n";

    // Display summary of removed index directives
    if ($allRemovedDirectives !== []) {
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "SUMMARY: Removed Index Directives\n";
        echo str_repeat('=', 60) . "\n";

        $totalRemoved = 0;
        foreach ($allRemovedDirectives as $file => $directives) {
            echo sprintf("\n%s:\n", $file);
            foreach ($directives as $directive) {
                echo sprintf("  - %s\n", $directive);
                $totalRemoved++;
            }
        }

        echo sprintf("\nTotal index directives removed: %d\n", $totalRemoved);
        echo str_repeat('=', 60) . "\n\n";
    }
}

function main(): int
{
    global $argv;
    if (in_array('-h', $argv) || in_array('--help', $argv)) {
        showHelp();

        return 0;
    }

    $inputDir = './en';
    $outputDir = './docs';
    $counter = count($argv);
    for ($i = 1; $i < $counter; $i++) {
        $arg = $argv[$i];
        if (!str_starts_with($arg, '--')) {
            if ($i === 1) {
                $inputDir = $arg;
            } elseif ($i === 2) {
                $outputDir = $arg;
            }
        }
    }

    if (!is_dir($inputDir)) {
        echo "Error: Input directory '{$inputDir}' does not exist\n";
        echo "Use -h or --help for usage information\n";

        return 1;
    }

    convertDirectory($inputDir, $outputDir);

    return 0;
}

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($argv[0])) {
    exit(main());
}
