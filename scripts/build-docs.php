<?php
/**
 * CakePHP Documentation Build Orchestrator
 *
 * This script orchestrates the complete documentation build process:
 * 1. Fetches all configured branches from the CakePHP docs repository
 * 2. Converts each branch's RST documentation to Markdown using the conversion mappings
 */
declare(strict_types=1);

class DocumentationOrchestrator
{
    private array $config;
    private bool $verbose;
    private string $projectRoot;

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
        $this->projectRoot = dirname(__DIR__);

        // Load global configuration
        $configPath = $this->projectRoot . '/config.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException("Global configuration file not found: {$configPath}");
        }

        $this->config = require $configPath;
    }

    public function run(): void
    {
        $this->log('CakePHP Documentation Build Orchestrator');
        $this->log('========================================');
        $this->log("Project root: {$this->projectRoot}");
        $this->log('');

        try {
            // Step 1: Fetch branches
            $this->fetchBranches();

            // Step 2: Convert documentation for each mapping
            $this->convertDocumentation();

            $this->log("\n✅ Documentation build completed successfully!");
        } catch (Exception $e) {
            $this->log("\n❌ Build failed: " . $e->getMessage());
            exit(1);
        }
    }

    private function fetchBranches(): void
    {
        $this->log('📥 Step 1: Fetching branches from repository');
        $this->log('============================================');

        $fetchScript = $this->projectRoot . '/scripts/fetch-branches.php';
        if (!file_exists($fetchScript)) {
            throw new RuntimeException("Fetch branches script not found: {$fetchScript}");
        }

        $branches = implode(',', $this->config['branches']);
        $tempDir = $this->config['repository']['temp_base_dir'];

        $command = sprintf(
            'php %s --branches %s --dir %s%s',
            escapeshellarg($fetchScript),
            escapeshellarg($branches),
            escapeshellarg($tempDir),
            $this->verbose ? ' --verbose' : '',
        );

        $this->log("Executing: php fetch-branches.php --branches \"{$branches}\" --dir \"{$tempDir}\"" . ($this->verbose ? ' --verbose' : ''));
        $this->log('');

        $result = $this->executeCommand($command);

        if (!$result['success']) {
            throw new RuntimeException('Failed to fetch branches: ' . $result['error']);
        }

        $this->log('✅ Branch fetching completed');
        $this->log('');
    }

    private function convertDocumentation(): void
    {
        $this->log('🔄 Step 2: Converting documentation');
        $this->log('==================================');

        $conversionMappings = $this->config['conversion_mappings'];
        $convertScript = $this->projectRoot . '/scripts/convert.php';

        if (!file_exists($convertScript)) {
            throw new RuntimeException("Convert script not found: {$convertScript}");
        }

        $this->log('Found ' . count($conversionMappings) . ' conversion mappings');
        $this->log('');

        $successCount = 0;
        $failureCount = 0;

        foreach ($conversionMappings as $source => $destination) {
            $this->log("Converting: {$source} -> {$destination}");

            // Build full paths
            $sourcePath = $this->projectRoot . '/' . $source;
            $destPath = $this->projectRoot . '/' . $destination;

            // Check if source exists
            if (!is_dir($sourcePath)) {
                $this->log("  ⚠️  Source directory not found: {$sourcePath}");
                $this->log('     Skipping this conversion...');
                $failureCount++;
                continue;
            }

            // Create destination directory if it doesn't exist
            $destDir = dirname($destPath);
            if (!is_dir($destDir)) {
                $this->log("  📁 Creating destination directory: {$destDir}");
                if (!mkdir($destDir, 0755, true)) {
                    $this->log('  ❌ Failed to create destination directory');
                    $failureCount++;
                    continue;
                }
            }

            // Run conversion
            $command = sprintf(
                'php %s %s %s',
                escapeshellarg($convertScript),
                escapeshellarg($sourcePath),
                escapeshellarg($destPath),
            );

            if ($this->verbose) {
                $this->log("  Executing: php convert.php \"{$sourcePath}\" \"{$destPath}\"");
            }

            $result = $this->executeCommand($command);

            if ($result['success']) {
                // Count converted files
                $fileCount = $this->countMarkdownFiles($destPath);
                $this->log("  ✅ Conversion successful - {$fileCount} markdown files created");
                $successCount++;
            } else {
                $this->log('  ❌ Conversion failed: ' . $result['error']);
                $failureCount++;
            }

            $this->log('');
        }

        // Summary
        $this->log('Conversion Summary:');
        $this->log("  ✅ Successful: {$successCount}");
        if ($failureCount > 0) {
            $this->log("  ❌ Failed: {$failureCount}");
        }

        if ($failureCount > 0 && $successCount === 0) {
            throw new RuntimeException('All conversions failed');
        } elseif ($failureCount > 0) {
            $this->log('  ⚠️  Some conversions failed, but build completed partially');
        }
    }

    private function countMarkdownFiles(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'md') {
                $count++;
            }
        }

        return $count;
    }

    private function executeCommand(string $command): array
    {
        $output = [];
        $returnCode = 0;

        exec($command . ' 2>&1', $output, $returnCode);

        if ($this->verbose && !empty($output)) {
            foreach ($output as $line) {
                $this->log('    ' . $line);
            }
        }

        return [
            'success' => $returnCode === 0,
            'output' => $output,
            'error' => $returnCode !== 0 ? implode("\n", $output) : null,
        ];
    }

    private function log(string $message): void
    {
        echo $message . "\n";
    }
}

// CLI Usage
function showUsage(): void
{
    echo "CakePHP Documentation Build Orchestrator\n";
    echo "Usage: php build-docs.php [options]\n\n";
    echo "Options:\n";
    echo "  -v, --verbose    Enable verbose output\n";
    echo "  -h, --help       Show this help message\n\n";
    echo "Description:\n";
    echo "  This script orchestrates the complete documentation build process:\n";
    echo "  1. Fetches all configured branches from the CakePHP docs repository\n";
    echo "  2. Converts each branch's RST documentation to Markdown\n\n";
    echo "  The script uses the global configuration in config.php to determine\n";
    echo "  which branches to fetch and how to map source directories to\n";
    echo "  destination directories for conversion.\n\n";
    echo "Examples:\n";
    echo "  php build-docs.php\n";
    echo "  php build-docs.php --verbose\n\n";
}

// Parse command line arguments
function parseArguments(array $argv): array
{
    $options = [
        'verbose' => false,
        'help' => false,
    ];

    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];

        switch ($arg) {
            case '-h':
            case '--help':
                $options['help'] = true;
                break;

            case '-v':
            case '--verbose':
                $options['verbose'] = true;
                break;

            default:
                throw new InvalidArgumentException("Unknown option: {$arg}");
        }
    }

    return $options;
}

// Main execution
if (php_sapi_name() === 'cli') {
    try {
        $options = parseArguments($argv);

        if ($options['help']) {
            showUsage();
            exit(0);
        }

        $orchestrator = new DocumentationOrchestrator($options['verbose']);
        $orchestrator->run();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n\n";
        showUsage();
        exit(1);
    }
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}
