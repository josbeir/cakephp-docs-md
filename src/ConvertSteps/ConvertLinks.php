<?php
declare(strict_types=1);

namespace Cake\DocsMD\ConvertSteps;

class ConvertLinks
{
    private string $basePath;

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
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
                $path = ltrim($url, '/');

                return $basePath . '/' . $path . '.md';
            }

            return $url;
        };
        $content = preg_replace_callback(
            '/(?<![:\w])`([^`<]+)\s*<([^>]+)>`__/',
            function ($matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );
        $content = preg_replace_callback(
            '/(?<![:\w])`([^`<]+)\s*<([^>]+)>`_(?![_`])/',
            function ($matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );
        $content = preg_replace_callback(
            '/(?<![:\w])`([^<]+)<([^>]+)>`__/',
            function ($matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );

        return preg_replace_callback(
            '/(?<![:\w])`([^<]+)<([^>]+)>`_(?![_`])/',
            function ($matches) use ($processUrl): string {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);

                return sprintf('[%s](%s)', $linkText, $processedUrl);
            },
            $content,
        );
    }
}
