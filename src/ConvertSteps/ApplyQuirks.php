<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ApplyQuirks
{
    private array $quirksRegistry;

    public function __construct(array $quirksRegistry = [])
    {
        // Load default quirks if none provided
        if (empty($quirksRegistry)) {
            $configPath = dirname(__DIR__, 2) . '/config/quirks.php';
            if (file_exists($configPath)) {
                $quirksRegistry = require $configPath;
            }
        }

        $this->quirksRegistry = $quirksRegistry;
    }

    public function __invoke(string $content): string
    {
        foreach ($this->quirksRegistry as $pattern => $replacement) {
            if (str_starts_with($pattern, '/')) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content = str_replace($pattern, $replacement, $content);
            }
        }

        return $content;
    }
}
