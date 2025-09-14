<?php
//phpcs:ignoreFile
/**
 * CakePHP Documentation Branch Fetcher
 *
 * Fetches specified branches from the CakePHP docs repository to a temporary folder
 * for processing with the documentation converter.
 */
declare(strict_types=1);

class BranchFetcher
{
    private array $config;
    private array $branches;
    private string $tempDir;
    private string $repoUrl;
    private bool $verbose;

    public function __construct(?array $branches = null, ?string $tempDir = null, bool $verbose = false)
    {
        // Load global configuration
        $configPath = dirname(__DIR__) . '/config.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException("Global configuration file not found: {$configPath}");
        }
        
        $this->config = require $configPath;
        
        // Set properties from config or parameters
        $this->branches = $branches ?? $this->config['branches'];
        $this->tempDir = $tempDir ?? $this->config['repository']['temp_base_dir'];
        $this->repoUrl = $this->config['repository']['url'];
        $this->verbose = $verbose;
    }

    public function fetchBranches(): void
    {
        $this->log('CakePHP Documentation Branch Fetcher');
        $this->log('====================================');
        $this->log('Repository: ' . $this->repoUrl);
        $this->log("Target directory: {$this->tempDir}");
        $this->log('Branches to fetch: ' . implode(', ', $this->branches));
        $this->log('');

        // Create temp directory
        $this->createTempDirectory();

        // Fetch each branch
        foreach ($this->branches as $branch) {
            $this->fetchBranch($branch);
        }

        $this->log("\n✅ All branches fetched successfully!");
        $this->log("Branches are available in: {$this->tempDir}/");
    }

    private function createTempDirectory(): void
    {
        if (!is_dir($this->tempDir)) {
            if (!mkdir($this->tempDir, 0755, true)) {
                throw new RuntimeException("Failed to create temp directory: {$this->tempDir}");
            }
            $this->log("Created temp directory: {$this->tempDir}");
        } else {
            $this->log("Using existing temp directory: {$this->tempDir}");
        }
    }

    private function fetchBranch(string $branch): void
    {
        $branchDir = $this->tempDir . '/' . $branch;

        $this->log("Fetching branch '{$branch}'...");

        // Remove existing directory if it exists
        if (is_dir($branchDir)) {
            $this->log("  Removing existing directory: {$branchDir}");
            $this->executeCommand('rm -rf ' . escapeshellarg($branchDir));
        }

        // Clone the specific branch
        $cloneCommand = sprintf(
            'git clone --depth 1 --branch %s %s %s',
            escapeshellarg($branch),
            escapeshellarg($this->repoUrl),
            escapeshellarg($branchDir),
        );

        $this->log("  Cloning: git clone --depth 1 --branch {$branch} ...");

        $result = $this->executeCommand($cloneCommand);

        if ($result['success']) {
            // Remove .git directory to save space
            $gitDir = $branchDir . '/.git';
            if (is_dir($gitDir)) {
                $this->executeCommand('rm -rf ' . escapeshellarg($gitDir));
            }

            $this->log("  ✅ Branch '{$branch}' fetched successfully");

            // Show some stats
            $enDir = $branchDir . '/en';
            if (is_dir($enDir)) {
                $fileCount = $this->countRstFiles($enDir);
                $this->log("     Found {$fileCount} RST files in /en directory");
            }
        } else {
            $this->log("  ❌ Failed to fetch branch '{$branch}'");
            $this->log('     Error: ' . $result['error']);
        }

        $this->log('');
    }

    private function countRstFiles(string $dir): int
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $count = 0;
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'rst') {
                $count++;
            }
        }

        return $count;
    }

    private function executeCommand(string $command): array
    {
        $output = [];
        $returnCode = 0;

        if ($this->verbose) {
            $this->log("    Executing: {$command}");
        }

        exec($command . ' 2>&1', $output, $returnCode);

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
    echo "CakePHP Documentation Branch Fetcher\n";
    echo "Usage: php fetch-branches.php [options]\n\n";
    echo "Options:\n";
    echo "  -b, --branches <branches>    Comma-separated list of branches (default: 1.3,2.x,3.x,4.x,5.x)\n";
    echo "  -d, --dir <directory>        Target directory for branches (default: temp/branches)\n";
    echo "  -v, --verbose               Enable verbose output\n";
    echo "  -h, --help                  Show this help message\n\n";
    echo "Examples:\n";
    echo "  php fetch-branches.php\n";
    echo "  php fetch-branches.php -b \"3.x,4.x,5.x\" -d \"my-temp\"\n";
    echo "  php fetch-branches.php --branches \"5.x\" --verbose\n\n";
}

// Parse command line arguments
function parseArguments(array $argv): array
{
    $options = [
        'branches' => null,
        'dir' => null,
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

            case '-b':
            case '--branches':
                if (isset($argv[$i + 1])) {
                    $options['branches'] = $argv[++$i];
                } else {
                    throw new InvalidArgumentException("Option {$arg} requires a value");
                }
                break;

            case '-d':
            case '--dir':
                if (isset($argv[$i + 1])) {
                    $options['dir'] = $argv[++$i];
                } else {
                    throw new InvalidArgumentException("Option {$arg} requires a value");
                }
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

        $branches = $options['branches'] ? array_map('trim', explode(',', $options['branches'])) : null;
        $tempDir = $options['dir'];
        $verbose = $options['verbose'];

        $fetcher = new BranchFetcher($branches, $tempDir, $verbose);
        $fetcher->fetchBranches();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n\n";
        showUsage();
        exit(1);
    }
} else {
    echo "This script must be run from the command line.\n";
    exit(1);
}
