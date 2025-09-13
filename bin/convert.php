#!/usr/bin/env php
<?php

/**
 * CakePHP Documentation RST to Markdown Converter
 *
 * This script converts CakePHP's RST documentation to Markdown format while preserving
 * custom Sphinx directives and converting them to appropriate Markdown equivalents.
 *
 * Usage: php convert_rst_to_md.php [input_dir] [output_dir]
 */

class RSTToMarkdownConverter
{
    private array $directiveMapping = [
        'note' => '> [!NOTE]',
        'warning' => '> [!WARNING]',
        'tip' => '> [!TIP]',
        'seealso' => '> [!NOTE]',
        'important' => '> [!IMPORTANT]',
        'caution' => '> [!CAUTION]',
    ];

    private array $labelToDocumentMap = [];
    private string $basePath = '';

    private array $quirksRegistry = [
        // Format: [pattern => replacement]
        // String keys are treated as literal strings (str_replace)
        // Keys starting with '/' are treated as regex patterns (preg_replace)

        // Simple string replacements
        'add<registry-object>()' => '`add<registry-object>()`',
        'proper <p> around' => 'proper `<p>` around',
        'and <br> where' => 'and `<br>` where',
        '<td>-attributes.' => '`<td>`-attributes.',
        '<tr> attributes' => '`<tr>` attributes',
        'inside of <table>' => 'inside of `<table>`',
        '/Fallback text<\/video>\n```/' => 'Fallback text\\</video>\\n', // html.md
        'codecs=\'theora, vorbis\'"/>\\n    </video>' => 'codecs=\'theora, vorbis\'"/>\\n    </video>\n```', // html.md
        // Regex patterns (must start with /)
        '/(?<!\\\\)<locale>/' => '\\<locale\\>',  // Replace <locale> with \<locale\> unless already escaped
        '  <?= $variable ?>' => '```php'. "\n" .'<?= $variable ?>'. "\n" .'```', // Embed PHP short tags in code blocks
        '_static/img/' => '/',
    ];

    public function applyQuirks(string $content): string
    {
        // Apply quirks - support both string and regex patterns
        foreach ($this->quirksRegistry as $pattern => $replacement) {
            if (str_starts_with($pattern, '/')) {
                // Treat as regex pattern
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                // Treat as literal string
                $content = str_replace($pattern, $replacement, $content);
            }
        }

        return $content;
    }

    public function convertHeadings(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check if next line is a heading underline
            if ($i + 1 < count($lines)) {
                $nextLine = $lines[$i + 1];

                // RST heading patterns mapped to markdown levels
                // H1: # and *, H2: =, H3: -, H4: ~, H5: ^, H6: "
                $headingChars = ['#', '*', '=', '-', '~', '^', '"'];
                $levelMapping = [
                    '#' => 1, '*' => 1,  // H1
                    '=' => 2,            // H2
                    '-' => 3,            // H3
                    '~' => 4,            // H4
                    '^' => 5,            // H5
                    '"' => 6             // H6
                ];

                foreach ($headingChars as $char) {
                    if (
                        trim($nextLine) &&
                        str_repeat($char, strlen(trim($nextLine))) === trim($nextLine) &&
                        strlen(trim($nextLine)) >= strlen(trim($line)) &&
                        trim($line)
                    ) {
                        // Determine heading level based on character mapping
                        $level = $levelMapping[$char];

                        // Convert to Markdown heading
                        $result[] = str_repeat('#', $level) . ' ' . trim($line);
                        $i += 2; // Skip both heading line and underline
                        continue 2;
                    }
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    public function convertPhpDirectives(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $currentNamespace = '';
        $currentClass = '';

        foreach ($lines as $line) {
            // Check for PHP namespace directive
            if (preg_match('/^\.\. php:namespace::\s*(.+)$/', $line, $matches)) {
                $currentNamespace = trim($matches[1]);
                $result[] = "**Namespace:** `$currentNamespace`";
                $result[] = '';
                continue;
            }

            // Check for PHP class directive
            if (preg_match('/^\.\. php:class::\s*(.+)$/', $line, $matches)) {
                $currentClass = trim($matches[1]);
                $fqdn = $currentNamespace ? "$currentNamespace\\$currentClass" : $currentClass;
                $result[] = "### Class `$fqdn`";
                $result[] = '';
                continue;
            }

            // Check for PHP method directive
            if (preg_match('/^\.\. php:method::\s*(.+)$/', $line, $matches)) {
                $methodSignature = trim($matches[1]);

                if ($currentNamespace && $currentClass) {
                    // Extract method name from signature (everything before the first parenthesis or space)
                    $methodName = preg_split('/[\(\s]/', $methodSignature, 2)[0];
                    $fqdn = "$currentNamespace\\$currentClass::$methodSignature";

                    // Use the full signature in the FQDN
                    $result[] = "#### Method `$fqdn`";
                    $result[] = '';
                } else {
                    // Fallback if no context available
                    $result[] = "#### Method `$methodSignature`";
                    $result[] = '';
                }
                continue;
            }

            // Check for PHP staticmethod directive
            if (preg_match('/^\.\. php:staticmethod::\s*(.+)$/', $line, $matches)) {
                $methodSignature = trim($matches[1]);

                if ($currentNamespace && $currentClass) {
                    $fqdn = "$currentNamespace\\$currentClass::$methodSignature";
                    $result[] = "#### Static Method `$fqdn`";
                    $result[] = '';
                } else {
                    $result[] = "#### Static Method `$methodSignature`";
                    $result[] = '';
                }
                continue;
            }

            // Check for PHP function directive (global functions, no class context)
            if (preg_match('/^\.\. php:function::\s*(.+)$/', $line, $matches)) {
                $functionSignature = trim($matches[1]);
                $result[] = "#### Function `$functionSignature`";
                $result[] = '';
                continue;
            }

            // Check for PHP attribute directive
            if (preg_match('/^\.\. php:attr::\s*(.+)$/', $line, $matches)) {
                $attributeName = trim($matches[1]);

                if ($currentNamespace && $currentClass) {
                    $fqdn = "$currentNamespace\\$currentClass::\$$attributeName";
                    $result[] = "#### Property `$fqdn`";
                } else {
                    $result[] = "#### Property `$attributeName`";
                }
                $result[] = '';
                continue;
            }

            // Regular line, add as-is
            $result[] = $line;
        }

        return implode("\n", $result);
    }

    public function convertCrossReferences(string $content): string
    {
        // Helper function to clean PHP references (remove tilde and convert double backslashes)
        $cleanPhpReference = function($matches) {
            $reference = $matches[1];
            // Remove tilde prefix if present
            $reference = ltrim($reference, '~');
            // Convert double backslashes to single backslashes
            $reference = str_replace('\\\\', '\\', $reference);
            return '`' . $reference . '`';
        };

        // Convert :php:class:`ClassName` to `ClassName`
        $content = preg_replace_callback('/:php:class:`([^`]+)`/', $cleanPhpReference, $content);

        // Convert :php:meth:`Method` to `Method`
        $content = preg_replace_callback('/:php:meth:`([^`]+)`/', $cleanPhpReference, $content);

        // Convert :php:attr:`Attribute` to `Attribute`
        $content = preg_replace_callback('/:php:attr:`([^`]+)`/', $cleanPhpReference, $content);

        // Convert :php:func:`Function` to `Function`
        $content = preg_replace_callback('/:php:func:`([^`]+)`/', $cleanPhpReference, $content);

        // Convert :php:const:`Constant` to `Constant`
        $content = preg_replace_callback('/:php:const:`([^`]+)`/', $cleanPhpReference, $content);

        // Convert :php:exc:`Exception` to `Exception`
        $content = preg_replace_callback('/:php:exc:`([^`]+)`/', $cleanPhpReference, $content);

        // Convert :abbr:`ABBR (Full Form)` to <abbr title="Full Form">ABBR</abbr>
        $content = preg_replace_callback('/:abbr:`([^(]+?)\s*\(([^)]+?)\)`/s', function($matches) {
            $abbr = trim($matches[1]);
            $fullForm = preg_replace('/\s+/', ' ', trim($matches[2])); // Normalize whitespace
            return "<abbr title=\"{$fullForm}\">{$abbr}</abbr>";
        }, $content);

        // Convert :doc:`title <path>` first (more specific pattern)
        $content = preg_replace_callback('/:doc:`([^<]+)<([^>]+)>`/', function($matches) {
            $title = trim($matches[1]);
            $path = trim($matches[2]);

            // Don't process absolute URLs
            if (preg_match('/^https?:\/\//', $path)) {
                return "[{$title}]({$path})";
            }

            // Make absolute path with base path
            $path = ltrim($path, '/');
            return "[{$title}]({$this->basePath}/{$path}.md)";
        }, $content);

        // Convert :doc:`path` (simple pattern without < character)
        $content = preg_replace_callback('/:doc:`([^`<]+)`/', function($matches) {
            $path = trim($matches[1]);

            // Don't process absolute URLs
            if (preg_match('/^https?:\/\//', $path)) {
                return "[{$path}]({$path})";
            }

            // Make absolute path with base path
            $path = ltrim($path, '/');
            return "[{$path}]({$this->basePath}/{$path}.md)";
        }, $content);

        // Convert :ref:`Link text <label-name>` to [Link text](#label-name) or [Link text](document.md#label-name)
        $content = preg_replace_callback('/:ref:`([^<`]+)<([^>`]+)>`/', function($matches) {
            $linkText = trim($matches[1]);
            $labelName = trim($matches[2]);

            // Try to determine if this is a cross-document reference
            $targetDocument = $this->findDocumentForLabel($labelName);
            if ($targetDocument) {
                return "[{$linkText}]({$this->basePath}/{$targetDocument}#{$labelName})";
            } else {
                return "[{$linkText}](#{$labelName})";
            }
        }, $content);

        // Convert :ref:`reference` to [reference](#reference) or cross-document link
        $content = preg_replace_callback('/:ref:`([^`]+)`/', function($matches) {
            $reference = trim($matches[1]);

            // Try to determine if this is a cross-document reference
            $targetDocument = $this->findDocumentForLabel($reference);
            if ($targetDocument) {
                return "[{$reference}]({$this->basePath}/{$targetDocument}#{$reference})";
            } else {
                return "[{$reference}](#{$reference})";
            }
        }, $content);

        return $content;
    }

    public function convertCodeBlocks(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $inCodeBlock = false;
        $codeIndent = 0;
        $currentLanguage = '';

        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for explicit code-block directive
            if (preg_match('/^\s*\.\.\s+code-block::\s*(.*)/', $line, $matches)) {
                // Close any existing code block first
                if ($inCodeBlock) {
                    $result[] = '```';
                    $inCodeBlock = false;
                }

                $language = trim($matches[1]);
                // Map some language aliases
                $language = match($language) {
                    'console' => 'bash',
                    'mysql' => 'sql',
                    'apacheconf' => 'apache',
                    default => $language
                };

                // Add blank line before code block
                $result[] = '';
                $result[] = '```' . $language;
                $inCodeBlock = true;
                $currentLanguage = $language;

                // Find indentation of next non-empty line
                $j = $i + 1;
                while ($j < count($lines) && !trim($lines[$j])) {
                    $result[] = '';
                    $j++;
                }

                if ($j < count($lines)) {
                    $codeIndent = strlen($lines[$j]) - strlen(ltrim($lines[$j]));
                }

                $i = $j;
                continue;
            }

            // Check for code block start (line ending with ::), but exclude admonition directives
            if (str_ends_with(rtrim($line), '::') && !$inCodeBlock &&
                !preg_match('/^\s*\.\.\s+(note|warning|tip|important|caution|seealso)::\s*/', $line)) {
                // Remove :: and add code block start
                $textBefore = rtrim(substr(rtrim($line), 0, -2));
                if ($textBefore) {
                    $result[] = $textBefore;
                    // Add blank line after the text and before code block
                    $result[] = '';
                }

                // Detect language based on the next few lines of code
                $language = $this->detectCodeLanguage($lines, $i + 1);
                $result[] = '```' . $language;
                $inCodeBlock = true;
                $currentLanguage = $language;

                // Find indentation of next non-empty line
                $j = $i + 1;
                while ($j < count($lines) && !trim($lines[$j])) {
                    $result[] = '';
                    $j++;
                }

                if ($j < count($lines)) {
                    $codeIndent = strlen($lines[$j]) - strlen(ltrim($lines[$j]));
                }

                $i = $j;
                continue;
            }

            if ($inCodeBlock) {
                // Check if we're still in the code block
                if (!trim($line)) {
                    $result[] = $line;
                } elseif ((strlen($line) - strlen(ltrim($line))) >= $codeIndent) {
                    // Still indented, part of code block
                    $codeContent = substr($line, $codeIndent);

                    // Check for nested ``` markers and escape them
                    if (preg_match('/^```/', $codeContent)) {
                        $codeContent = '\\' . $codeContent;
                    }

                    $result[] = $codeContent;
                } else {
                    // End of code block
                    $result[] = '```';
                    $result[] = '';
                    $result[] = $line;
                    $inCodeBlock = false;
                    $currentLanguage = '';
                    $codeIndent = 0;
                }
            } else {
                $result[] = $line;
            }

            $i++;
        }

        // Close any remaining code block
        if ($inCodeBlock) {
            $result[] = '```';
        }

        // Clean up code blocks: remove extra blank lines at start and end
        return $this->cleanupCodeBlocks(implode("\n", $result));
    }

    public function convertReferenceLabels(string $content): string
    {
        // Convert RST reference labels like .. _label-name: to HTML anchors
        $content = preg_replace('/^\s*\.\.\s+_([^:]+):\s*$/m', '<a id="$1"></a>', $content);

        return $content;
    }

    public function convertIncludes(string $content): string
    {
        // Convert .. include:: /path/to/file.rst to <!--@include: ./path/to/file.md-->
        $content = preg_replace_callback('/^\s*\.\.\s+include::\s*(.+)$/m', function($matches) {
            $includePath = trim($matches[1]);
            // Remove leading slash to make path relative
            $includePath = ltrim($includePath, '/');
            // Convert .rst extension to .md
            $includePath = preg_replace('/\.rst$/', '.md', $includePath);
            return "<!--@include: ./{$includePath}-->";
        }, $content);

        return $content;
    }

    public function convertMiscDirectives(string $content): string
    {
        // Convert .. versionchanged:: and similar directives (but not versionadded - handled separately)
        $content = preg_replace('/^\s*\.\.\s+(versionchanged|deprecated)::\s*(.*)$/m', '> **$1:** $2', $content);

        // Convert .. toctree:: directive - just remove it and its content
        $lines = explode("\n", $content);
        $result = [];
        $inToctree = false;
        $toctreeIndent = 0;

        foreach ($lines as $line) {
            if (preg_match('/^\s*\.\.\s+toctree::\s*$/', $line)) {
                $inToctree = true;
                $toctreeIndent = strlen($line) - strlen(ltrim($line));
                continue;
            }

            if ($inToctree) {
                // Check if we're still in the toctree directive
                if (trim($line) === '') {
                    continue;
                } elseif (preg_match('/^\s*:/', $line)) {
                    // toctree option, skip it
                    continue;
                } elseif ((strlen($line) - strlen(ltrim($line))) > $toctreeIndent) {
                    // Still indented, part of toctree content, skip it
                    continue;
                } else {
                    // End of toctree
                    $inToctree = false;
                    $result[] = $line;
                }
            } else {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }

    private function detectCodeLanguage(array $lines, int $startIndex): string
    {
        // Look at the next few lines to detect language
        $codeLines = [];
        $j = $startIndex;

        // Skip empty lines
        while ($j < count($lines) && !trim($lines[$j])) {
            $j++;
        }

        // Collect first few non-empty code lines
        $indent = null;
        while ($j < count($lines) && count($codeLines) < 10) {
            $line = $lines[$j];
            if (!trim($line)) {
                $j++;
                continue;
            }

            $currentIndent = strlen($line) - strlen(ltrim($line));
            if ($indent === null) {
                $indent = $currentIndent;
            } elseif ($currentIndent < $indent && trim($line)) {
                // Dedented line, probably end of code block
                break;
            }

            $codeLines[] = trim($line);
            $j++;
        }

        $codeText = implode(' ', $codeLines);

        // PHP detection patterns
        if (preg_match('/^\s*<\?php/', $codeText) ||
            preg_match('/<\?=/', $codeText) || // PHP short echo tags
            preg_match('/namespace\s+\w+/', $codeText) ||
            preg_match('/class\s+\w+/', $codeText) ||
            preg_match('/function\s+\w+\s*\(/', $codeText) ||
            preg_match('/\$\w+/', $codeText) ||
            preg_match('/public\s+function/', $codeText) ||
            preg_match('/private\s+function/', $codeText) ||
            preg_match('/protected\s+function/', $codeText) ||
            preg_match('/use\s+[\w\\\\]+;/', $codeText) ||
            // Enhanced PHP detection patterns
            preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*\s*=/', $codeText) || // Variable assignment
            preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*\[[\'"][^\'"]*[\'"]\]/', $codeText) || // Array access with quotes
            preg_match('/\$[a-zA-Z_][a-zA-Z0-9_]*\[\$\w+\]/', $codeText) || // Array access with variable
            preg_match('/\$\w+->\w+/', $codeText) || // Object method/property access
            preg_match('/\w+::\w+/', $codeText) || // Static method/constant access
            preg_match('/array\s*\(/', $codeText) || // array() syntax
            preg_match('/\[\s*[\'"][^\'"]*[\'"]/', $codeText) || // Array syntax with quotes
            preg_match('/\$_(GET|POST|SESSION|COOKIE|SERVER|REQUEST)\b/', $codeText) || // PHP superglobals
            preg_match('/echo\s+/', $codeText) || // echo statement
            preg_match('/print\s+/', $codeText) || // print statement
            preg_match('/return\s+\$\w+/', $codeText) || // return with variable
            preg_match('/if\s*\(\s*\$\w+/', $codeText) || // if statement with variable
            preg_match('/foreach\s*\(\s*\$\w+/', $codeText) || // foreach with variable
            preg_match('/\$this->\w+/', $codeText) || // $this usage
            preg_match('/new\s+[A-Z]\w+/', $codeText) || // Object instantiation
            preg_match('/instanceof\s+[A-Z]\w+/', $codeText) || // instanceof operator
            preg_match('/\?\?\s*/', $codeText) || // Null coalescing operator
            preg_match('/\?->\w+/', $codeText) || // Nullsafe operator (PHP 8)
            preg_match('/fn\s*\(/', $codeText) || // Arrow functions
            preg_match('/\.\s*[\'"]/', $codeText) || // String concatenation
            preg_match('/[\'"].*[\'"]\./', $codeText) || // String concatenation with dot
            preg_match('/implements\s+\w+/', $codeText) || // implements keyword
            preg_match('/extends\s+\w+/', $codeText) || // extends keyword
            preg_match('/\bconst\s+\w+/', $codeText) || // const keyword
            preg_match('/\btrait\s+\w+/', $codeText) || // trait keyword
            preg_match('/\binterface\s+\w+/', $codeText) || // interface keyword
            preg_match('/\babstract\s+class/', $codeText) || // abstract class
            preg_match('/\bfinal\s+class/', $codeText) || // final class
            preg_match('/\|\|\s*/', $codeText) || // Logical OR
            preg_match('/&&\s*/', $codeText) || // Logical AND
            preg_match('/===\s*/', $codeText) || // Strict equality
            preg_match('/!==\s*/', $codeText) || // Strict inequality
            preg_match('/\bstatic\s+function/', $codeText) || // static function
            preg_match('/\bstatic\s+\$\w+/', $codeText) || // static variable
            preg_match('/\b__construct\b/', $codeText) || // Constructor
            preg_match('/\b__destruct\b/', $codeText) || // Destructor
            preg_match('/\b__(get|set|call|toString)\b/', $codeText) || // Magic methods
            preg_match('/\btry\s*\{/', $codeText) || // try-catch blocks
            preg_match('/\bcatch\s*\(/', $codeText) || // catch blocks
            preg_match('/\bfinally\s*\{/', $codeText) || // finally blocks
            preg_match('/\bthrow\s+new/', $codeText) || // throw exception
            preg_match('/\binclude\s+/', $codeText) || // include statement
            preg_match('/\brequire\s+/', $codeText) || // require statement
            preg_match('/\binclude_once\s+/', $codeText) || // include_once statement
            preg_match('/\brequire_once\s+/', $codeText)) { // require_once statement
            return 'php';
        }

        // HTML detection
        if (preg_match('/<[a-zA-Z][^>]*>/', $codeText) ||
            preg_match('/<\/[a-zA-Z]+>/', $codeText)) {
            return 'html';
        }

        // CSS detection
        if (preg_match('/[a-zA-Z-]+\s*:\s*[^;]+;/', $codeText) ||
            preg_match('/[.#]?[\w-]+\s*\{/', $codeText)) {
            return 'css';
        }

        // JavaScript detection
        if (preg_match('/function\s*\(/', $codeText) ||
            preg_match('/var\s+\w+/', $codeText) ||
            preg_match('/let\s+\w+/', $codeText) ||
            preg_match('/const\s+\w+/', $codeText) ||
            preg_match('/console\.log/', $codeText)) {
            return 'javascript';
        }

        // JSON detection
        if (preg_match('/^\s*[\{\[]/', $codeText) ||
            preg_match('/"[\w-]+"\s*:/', $codeText)) {
            return 'json';
        }

        // Shell/Bash detection (check before SQL to avoid conflicts)
        if (preg_match('/^\s*[\$#]/', $codeText) ||
            preg_match('/\b(cd|ls|mkdir|cp|mv|rm|grep|find)\b/', $codeText) ||
            preg_match('/bin\/cake\b/', $codeText) ||
            preg_match('/php\s+composer\b/', $codeText) ||
            preg_match('/composer\s+(install|require|update|create-project|dump-autoload)\b/', $codeText) ||
            preg_match('/\.\/bin\/cake/', $codeText)) {
            return 'bash';
        }

        // SQL detection
        if (preg_match('/\b(SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER)\b/i', $codeText)) {
            return 'sql';
        }

        // XML detection
        if (preg_match('/<\?xml/', $codeText) ||
            preg_match('/<[\w:-]+[^>]*\/>/', $codeText)) {
            return 'xml';
        }

        // INI/Config detection
        if (preg_match('/^\s*[\w.]+\s*=/', $codeText) ||
            preg_match('/^\s*\[[\w\s]+\]/', $codeText)) {
            return 'ini';
        }

        // Default to no language specification for unknown types
        return '';
    }

    public function cleanupCodeBlocks(string $content): string
    {
        // Use regex to find and clean up code blocks
        $pattern = '/```(\w*)\n\n+(.*?)\n+```/s';

        $content = preg_replace_callback($pattern, function ($matches) {
            $language = $matches[1];
            $code = $matches[2];

            // Trim leading and trailing whitespace/newlines from code content
            $code = trim($code);

            return '```' . $language . "\n" . $code . "\n```";
        }, $content);

        // Also handle cases where there's only one extra newline
        $pattern2 = '/```(\w*)\n\n(.*?)\n```/s';
        $content = preg_replace_callback($pattern2, function ($matches) {
            $language = $matches[1];
            $code = $matches[2];

            // Trim leading and trailing whitespace/newlines from code content
            $code = trim($code);

            return '```' . $language . "\n" . $code . "\n```";
        }, $content);

        // Handle cases with no extra newlines but improper formatting
        $pattern3 = '/```(\w*)\n([^`]+?)```/s';
        $content = preg_replace_callback($pattern3, function ($matches) {
            $language = $matches[1];
            $code = $matches[2];

            // Trim leading and trailing whitespace/newlines from code content
            $code = trim($code);

            // Ensure proper formatting
            return '```' . $language . "\n" . $code . "\n```";
        }, $content);

        return $content;
    }

    public function convertAdmonitions(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for admonition directive
            if (preg_match('/^(\s*)\.\.\s+(note|warning|tip|important|caution|seealso)::\s*(.*)$/', $line, $matches)) {
                [$_, $indent, $directive, $contentLine] = $matches;

                // Get the markdown equivalent - remove indentation for alerts
                if (isset($this->directiveMapping[$directive])) {
                    $result[] = $this->directiveMapping[$directive];

                    if (trim($contentLine)) {
                        $result[] = '> ' . trim($contentLine);
                    }

                    // Process indented content that follows
                    $i++;
                    $isFirstContentLine = !trim($contentLine); // True if we haven't added content yet

                    while ($i < count($lines)) {
                        $nextLine = $lines[$i];
                        if (!trim($nextLine)) {
                            // Skip blank lines at the beginning, add them as blockquote lines later
                            if (!$isFirstContentLine) {
                                $result[] = '>';
                            }
                        } elseif (str_starts_with($nextLine, $indent . '    ') || str_starts_with($nextLine, $indent . "\t")) {
                            // Remove extra indentation and add blockquote marker
                            $contentText = ltrim(substr($nextLine, strlen($indent)));
                            if ($contentText) {
                                $result[] = '> ' . $contentText;
                                $isFirstContentLine = false;
                            } elseif (!$isFirstContentLine) {
                                $result[] = '>';
                            }
                        } else {
                            break;
                        }
                        $i++;
                    }
                    continue;
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    public function convertVersionAdded(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for versionadded directive
            if (preg_match('/^(\s*)\.\.\s+versionadded::\s*(.*)$/', $line, $matches)) {
                [$_, $indent, $version] = $matches;

                // Convert to GitHub-style IMPORTANT alert - remove indentation
                $result[] = '> [!IMPORTANT]';
                $result[] = '> Added in version ' . trim($version);

                // Process any indented content that follows
                $i++;
                while ($i < count($lines)) {
                    $nextLine = $lines[$i];
                    if (!trim($nextLine)) {
                        // Skip blank lines at the beginning, add them as blockquote lines later
                        $result[] = '>';
                    } elseif (str_starts_with($nextLine, $indent . '    ') || str_starts_with($nextLine, $indent . "\t")) {
                        // Remove extra indentation and add blockquote marker
                        $contentText = ltrim(substr($nextLine, strlen($indent)));
                        if ($contentText) {
                            $result[] = '> ' . $contentText;
                        } else {
                            $result[] = '>';
                        }
                    } else {
                        break;
                    }
                    $i++;
                }
                continue;
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    public function convertContainers(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for container directive
            if (preg_match('/^(\s*)\.\.\s+container::\s*(.*)$/', $line, $matches)) {
                [$_, $indent, $containerClass] = $matches;

                // Add a horizontal rule to separate the container content
                $result[] = $indent . '---';
                $result[] = '';

                // Process indented content that follows
                $i++;
                $hasContent = false;
                while ($i < count($lines)) {
                    $nextLine = $lines[$i];
                    if (!trim($nextLine)) {
                        // Only add blank lines if we've seen content and it's not at the start
                        if ($hasContent) {
                            $result[] = '';
                        }
                    } elseif (str_starts_with($nextLine, $indent . '    ') || str_starts_with($nextLine, $indent . "\t")) {
                        // Remove extra indentation
                        $contentText = ltrim(substr($nextLine, strlen($indent)));
                        if (str_starts_with($contentText, '    ')) {
                            $contentText = substr($contentText, 4);
                        } elseif (str_starts_with($contentText, "\t")) {
                            $contentText = substr($contentText, 1);
                        }
                        $result[] = $indent . $contentText;
                        $hasContent = true;
                    } else {
                        break;
                    }
                    $i++;
                }

                // Add closing horizontal rule and line break
                $result[] = '';
                $result[] = $indent . '---';
                $result[] = '';
                continue;
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    public function convertImages(string $content): string
    {
        // Convert .. image:: path to ![alt](path) with relative path
        $content = preg_replace_callback('/^\s*\.\.\s+image::\s*(.+)$/m', function($matches) {
            $imagePath = trim($matches[1]);
            // Remove leading slash to make path relative
            $imagePath = ltrim($imagePath, '/');
            return "![]({$imagePath})";
        }, $content);

        return $content;
    }

    public function convertLinks(string $content): string
    {
        // Helper function to process URLs - add .md extension and prefix for internal docs
        $processUrl = function($url) {
            // Don't process absolute URLs (http/https)
            if (preg_match('/^https?:\/\//', $url)) {
                return $url;
            }

            // Don't process anchor-only links
            if (str_starts_with($url, '#')) {
                return $url;
            }

            // For internal documentation links (starting with / or relative paths without extension)
            if (str_starts_with($url, '/') || (!str_contains($url, '://') && !str_ends_with($url, '.md') && !str_ends_with($url, '.html'))) {
                // Remove trailing slash if present
                $url = rtrim($url, '/');
                // Make absolute path with base path
                $path = ltrim($url, '/');
                return $this->basePath . '/' . $path . '.md';
            }

            return $url;
        };

        // Convert RST-style inline links with double underscore: `link text <URL>`__ to [link text](URL)
        // Use negative lookbehind to avoid matching :ref:, :doc:, :php: patterns
        $content = preg_replace_callback(
            '/(?<![:\w])`([^`<]+)\s*<([^>]+)>`__/',
            function ($matches) use ($processUrl) {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);
                return "[{$linkText}]({$processedUrl})";
            },
            $content
        );

        // Convert RST-style inline links with single underscore: `link text <URL>`_ to [link text](URL)
        $content = preg_replace_callback(
            '/(?<![:\w])`([^`<]+)\s*<([^>]+)>`_(?![_`])/',
            function ($matches) use ($processUrl) {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);
                return "[{$linkText}]({$processedUrl})";
            },
            $content
        );

        // Also handle the case where the link text might contain backticks (with double underscore)
        $content = preg_replace_callback(
            '/(?<![:\w])`([^<]+)<([^>]+)>`__/',
            function ($matches) use ($processUrl) {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);
                return "[{$linkText}]({$processedUrl})";
            },
            $content
        );

        // Also handle the case where the link text might contain backticks (with single underscore)
        $content = preg_replace_callback(
            '/(?<![:\w])`([^<]+)<([^>]+)>`_(?![_`])/',
            function ($matches) use ($processUrl) {
                $linkText = trim($matches[1]);
                $url = trim($matches[2]);
                $processedUrl = $processUrl($url);
                return "[{$linkText}]({$processedUrl})";
            },
            $content
        );

        return $content;
    }

    public function convertIndentedPhpBlocks(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for standalone indented blocks that contain PHP code
            if (!trim($line)) {
                $result[] = $line;
                $i++;
                continue;
            }

            // Look for indented content that follows a line ending with double backticks and ::
            if (preg_match('/``[^`]+``::$/', $line)) {
                $result[] = $line; // We'll fix this line in the next method
                $i++;

                // Skip empty lines
                while ($i < count($lines) && !trim($lines[$i])) {
                    $result[] = $lines[$i];
                    $i++;
                }

                // Check if the next block is indented and contains PHP
                if ($i < count($lines) && strlen($lines[$i]) > strlen(ltrim($lines[$i]))) {
                    $blockLines = [];
                    $indent = strlen($lines[$i]) - strlen(ltrim($lines[$i]));

                    // Collect all indented lines
                    $j = $i;
                    while ($j < count($lines)) {
                        $currentLine = $lines[$j];
                        if (!trim($currentLine)) {
                            $blockLines[] = '';
                            $j++;
                            continue;
                        }

                        $currentIndent = strlen($currentLine) - strlen(ltrim($currentLine));
                        if ($currentIndent >= $indent) {
                            $blockLines[] = substr($currentLine, $indent);
                            $j++;
                        } else {
                            break;
                        }
                    }

                    // Check if this block contains PHP code
                    $blockContent = implode(' ', array_filter($blockLines, 'trim'));
                    if ($this->detectCodeLanguage(['', $blockContent], 0) === 'php') {
                        // Convert to PHP code block
                        $result[] = '';
                        $result[] = '```php';
                        foreach ($blockLines as $blockLine) {
                            $result[] = $blockLine;
                        }
                        $result[] = '```';
                        $result[] = '';
                        $i = $j;
                        continue;
                    }
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    public function convertRstInlineCode(string $content): string
    {
        // Fix double backticks with trailing colons: ``text``:: -> `text`
        $content = preg_replace('/``([^`]+)``::/', '`$1`', $content);

        // Fix standalone double backticks: ``text`` -> `text`
        // But exclude code block markers like ```php, ```html, etc.
        $content = preg_replace('/(?<!`)``([^`]+)``(?!:)(?!`)/', '`$1`', $content);

        return $content;
    }

    public function convertLists(string $content): string
    {
        // Convert bullet lists (* to -)
        $content = preg_replace('/^(\s*)\*\s+/m', '$1- ', $content);

        // Convert numbered lists (1. remains the same, but ensure proper format)
        $content = preg_replace('/^(\s*)(\d+)\.\s+/m', '$1$2. ', $content);

        return $content;
    }

    public function convertTables(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check for RST table patterns
            if ($this->isTableBorderLine($line)) {
                $tableResult = $this->processTable($lines, $i);
                if ($tableResult !== null) {
                    $result = array_merge($result, $tableResult['content']);
                    $i = $tableResult['nextIndex'];
                    continue;
                }
            }

            $result[] = $line;
            $i++;
        }

        return implode("\n", $result);
    }

    private function isTableBorderLine(string $line): bool
    {
        // Check for RST table border lines like +---+---+ or ===== ===== or ----- -----
        return preg_match('/^\s*\+[-=]+(\+[-=]+)*\+\s*$/', $line) ||
               preg_match('/^\s*=+\s+=+(\s+=+)*\s*$/', $line) ||
               preg_match('/^\s*-+\s+-+(\s+-+)*\s*$/', $line);
    }

    private function processTable(array $lines, int $startIndex): ?array
    {
        $line = $lines[$startIndex];
        $result = [];
        $i = $startIndex;

        // Handle grid tables (+---+---+)
        if (preg_match('/^\s*\+[-=]+(\+[-=]+)*\+\s*$/', $line)) {
            return $this->processGridTable($lines, $i);
        }

        // Handle simple tables (=== ===)
        if (preg_match('/^\s*=+\s+=+(\s+=+)*\s*$/', $line)) {
            return $this->processSimpleTable($lines, $i);
        }

        return null;
    }

    private function processGridTable(array $lines, int $startIndex): ?array
    {
        $result = [];
        $i = $startIndex;
        $headerRow = null;
        $dataRows = [];
        $columns = [];

        // Find column positions from the first border line
        $borderLine = $lines[$i];
        $columnPositions = $this->extractColumnPositions($borderLine);

        if (empty($columnPositions)) {
            return null;
        }

        $i++; // Move past first border

        // Process table content
        while ($i < count($lines)) {
            $line = $lines[$i];

            // Check if this is another border line
            if ($this->isTableBorderLine($line)) {
                if ($headerRow === null && !empty($dataRows)) {
                    // First data row becomes header
                    $headerRow = array_shift($dataRows);
                }
                $i++;
                continue;
            }

            // Check if we've reached the end of the table
            if (trim($line) === '' && $i > $startIndex + 2) {
                // Look ahead to see if we have more table content
                $hasMoreTableContent = false;
                for ($j = $i + 1; $j < count($lines) && $j < $i + 3; $j++) {
                    if ($this->isTableBorderLine($lines[$j]) || preg_match('/^\s*\|/', $lines[$j])) {
                        $hasMoreTableContent = true;
                        break;
                    }
                }
                if (!$hasMoreTableContent) {
                    break;
                }
            }

            // Extract cell content from grid table row
            if (preg_match('/^\s*\|/', $line) || !empty(trim($line))) {
                $cells = $this->extractGridTableCells($line, $columnPositions);
                if (!empty($cells)) {
                    $dataRows[] = $cells;
                }
            }

            $i++;
        }

        // Convert to Markdown table
        if ($headerRow === null && !empty($dataRows)) {
            $headerRow = array_shift($dataRows);
        }

        if ($headerRow !== null) {
            $result[] = '';
            $result[] = '| ' . implode(' | ', $headerRow) . ' |';
            $result[] = '|' . str_repeat(' --- |', count($headerRow));

            foreach ($dataRows as $row) {
                // Pad row to match header length
                while (count($row) < count($headerRow)) {
                    $row[] = '';
                }
                $result[] = '| ' . implode(' | ', array_slice($row, 0, count($headerRow))) . ' |';
            }
            $result[] = '';
        }

        return ['content' => $result, 'nextIndex' => $i];
    }

    private function processSimpleTable(array $lines, int $startIndex): ?array
    {
        $result = [];
        $i = $startIndex;
        $headerRow = null;
        $dataRows = [];

        // Skip the first border line
        $i++;

        // Collect header row
        if ($i < count($lines) && !$this->isTableBorderLine($lines[$i])) {
            $headerRow = $this->parseSimpleTableRow($lines[$i]);
            $i++;
        }

        // Skip separator line
        if ($i < count($lines) && $this->isTableBorderLine($lines[$i])) {
            $i++;
        }

        // Collect data rows
        while ($i < count($lines)) {
            $line = $lines[$i];

            // Skip dash separator lines (row separators within the table)
            if (preg_match('/^\s*-+\s+-+(\s+-+)*\s*$/', $line)) {
                $i++;
                continue;
            }

            // Check for table end (equals border line or grid border)
            if (preg_match('/^\s*=+\s+=+(\s+=+)*\s*$/', $line) ||
                preg_match('/^\s*\+[-=]+(\+[-=]+)*\+\s*$/', $line)) {
                $i++;
                break;
            }

            if (trim($line) === '') {
                break;
            }

            $row = $this->parseSimpleTableRow($line);
            if (!empty($row)) {
                $dataRows[] = $row;
            }

            $i++;
        }

        // Convert to Markdown table
        if ($headerRow !== null && !empty($headerRow)) {
            $result[] = '';
            $result[] = '| ' . implode(' | ', $headerRow) . ' |';
            $result[] = '|' . str_repeat(' --- |', count($headerRow));

            foreach ($dataRows as $row) {
                // Pad row to match header length
                while (count($row) < count($headerRow)) {
                    $row[] = '';
                }
                $result[] = '| ' . implode(' | ', array_slice($row, 0, count($headerRow))) . ' |';
            }
            $result[] = '';
        }

        return ['content' => $result, 'nextIndex' => $i];
    }

    private function extractColumnPositions(string $borderLine): array
    {
        $positions = [];
        preg_match_all('/\+/', $borderLine, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            $positions[] = $match[1];
        }

        return $positions;
    }

    private function extractGridTableCells(string $line, array $columnPositions): array
    {
        $cells = [];
        $line = rtrim($line);

        for ($i = 0; $i < count($columnPositions) - 1; $i++) {
            $start = $columnPositions[$i] + 1;
            $end = $columnPositions[$i + 1];

            if ($start < strlen($line) && $end <= strlen($line)) {
                $cellContent = substr($line, $start, $end - $start - 1);
                $cells[] = trim($cellContent);
            } elseif ($start < strlen($line)) {
                $cellContent = substr($line, $start);
                $cells[] = trim($cellContent);
            } else {
                $cells[] = '';
            }
        }

        return $cells;
    }

    private function parseSimpleTableRow(string $line): array
    {
        // Split by multiple spaces (RST simple table column separator)
        $cells = preg_split('/\s{2,}/', trim($line));
        return array_map('trim', $cells);
    }

    public function fixBrokenMarkdownLinks(string $content): string
    {
        // Fix broken markdown links patterns like:
        // `text](url.md)_ -> [text](url)
        // `text](url.md)__ -> [text](url)
        $content = preg_replace('/`([^`]+)\]\(([^)]+)\.md\)_+/', '[$1]($2)', $content);

        // Also fix cases without .md extension:
        // `text](url)_ -> [text](url)
        // `text](url)__ -> [text](url)
        $content = preg_replace('/`([^`]+)\]\(([^)]+)\)_+/', '[$1]($2)', $content);

        return $content;
    }

    public function normalizeIndentation(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        $inCodeBlock = false;
        $inBlockQuote = false;
        $afterCodeBlockMarker = false;

        foreach ($lines as $i => $line) {
            // Check if we're in a code block
            if (preg_match('/^```/', $line)) {
                $inCodeBlock = !$inCodeBlock;
                $result[] = $line;
                continue;
            }

            // Check if the previous line ended with ::, indicating following code
            if ($i > 0 && str_ends_with(rtrim($lines[$i - 1]), '::')) {
                $afterCodeBlockMarker = true;
            }

            // Reset code block marker flag if we hit a non-indented line
            if ($afterCodeBlockMarker && !preg_match('/^\s{4,}/', $line) && trim($line)) {
                $afterCodeBlockMarker = false;
            }

            // Check if we're in a blockquote (GitHub alerts or regular blockquotes)
            if (preg_match('/^\s*>/', $line)) {
                $inBlockQuote = true;
                $result[] = $line;
                continue;
            } elseif ($inBlockQuote && !trim($line)) {
                // Empty line in blockquote
                $result[] = $line;
                continue;
            } elseif ($inBlockQuote && !preg_match('/^\s*>/', $line) && trim($line)) {
                // End of blockquote
                $inBlockQuote = false;
            }

            // If we're in a code block, blockquote, or after a :: marker, preserve indentation
            if ($inCodeBlock || $inBlockQuote || $afterCodeBlockMarker) {
                $result[] = $line;
                continue;
            }

            // For regular text, remove excessive indentation that would be interpreted as code
            if (preg_match('/^(\s{3,})(.+)$/', $line, $matches)) {
                $indent = $matches[1];
                $textContent = $matches[2];

                // Check if this looks like code (more comprehensive patterns)
                $looksLikeCode = preg_match('/^[\$<>#]/', $textContent) ||
                                preg_match('/^\w+\s*[=:(\[]/', $textContent) ||
                                preg_match('/^(public|private|protected|class|function|namespace|use|return|if|for|while|echo|var|let|const)\s/', $textContent) ||
                                preg_match('/^\w+\(\)/', $textContent) ||
                                preg_match('/^\/\*|^\/\/|^\*|^<!--/', $textContent) ||
                                preg_match('/^\{|\}$/', $textContent) ||
                                preg_match('/^[A-Z_]+\s*=/', $textContent) ||
                                preg_match('/^\/\/|^#|^\*\s/', $textContent) ||
                                preg_match('/^(array|->|::|\$[a-zA-Z])/', $textContent);

                if ($looksLikeCode) {
                    // Keep indentation for actual code
                    $result[] = $line;
                } else {
                    // Remove excessive indentation for regular text - no indentation for prose
                    $result[] = $textContent;
                }
            } else {
                $result[] = $line;
            }
        }

        return implode("\n", $result);
    }

    public function handleMetaDirective(string $content): string
    {
        $lines = explode("\n", $content);
        $metaContent = [];
        $result = [];
        $inMeta = false;

        foreach ($lines as $line) {
            if (trim($line) === '.. meta::') {
                $inMeta = true;
                continue;
            } elseif ($inMeta) {
                if (str_starts_with($line, '    :') || str_starts_with($line, "\t:")) {
                    // Meta property line
                    $metaLine = trim($line);
                    if (str_starts_with($metaLine, ':') && str_contains(substr($metaLine, 1), ':')) {
                        [$prop, $value] = explode(':', substr($metaLine, 1), 2);
                        $cleanProp = trim($prop);
                        $cleanValue = trim($value);

                        // Clean up property name - remove lang attributes and spaces
                        $cleanProp = preg_replace('/\s+lang=\w+/', '', $cleanProp);
                        $cleanProp = str_replace(' ', '_', trim($cleanProp));

                        // Ensure property name is valid YAML key
                        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $cleanProp)) {
                            // Quote the value if it contains special characters
                            if (preg_match('/[,:]/', $cleanValue)) {
                                $cleanValue = '"' . str_replace('"', '\\"', $cleanValue) . '"';
                            }
                            $metaContent[] = $cleanProp . ': ' . $cleanValue;
                        }
                    }
                } elseif (!trim($line)) {
                    continue;
                } else {
                    $inMeta = false;
                    $result[] = $line;
                }
            } else {
                $result[] = $line;
            }
        }

        // Add YAML front matter if we have meta content
        if ($metaContent) {
            $frontMatter = array_merge(['---'], $metaContent, ['---', '']);
            return implode("\n", array_merge($frontMatter, $result));
        }

        return implode("\n", $result);
    }

    public function fixAbsolutePaths(string $content): string
    {
        // Note: This method previously converted absolute paths to relative paths
        // but we now want to keep absolute paths for proper cross-document linking
        return $content;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = '/' . trim($basePath, '/');
    }

    private function findDocumentForLabel(string $labelName): ?string
    {
        // Initialize label map if not already done
        if (empty($this->labelToDocumentMap)) {
            $this->buildLabelToDocumentMap();
        }

        return $this->labelToDocumentMap[$labelName] ?? null;
    }

    private function buildLabelToDocumentMap(): void
    {
        $baseDir = getcwd();
        $enDir = $baseDir . '/en';

        if (!is_dir($enDir)) {
            return;
        }

        $rstFiles = $this->findRstFiles($enDir);

        foreach ($rstFiles as $rstFile) {
            $content = file_get_contents($rstFile);
            $relativePath = str_replace($enDir . '/', '', $rstFile);
            $mdPath = preg_replace('/\.rst$/', '.md', $relativePath);

            // Find all labels in this file using .. _label-name: pattern
            if (preg_match_all('/^\s*\.\.\s+_([^:]+):\s*$/m', $content, $matches)) {
                foreach ($matches[1] as $label) {
                    $this->labelToDocumentMap[trim($label)] = $mdPath;
                }
            }
        }
    }


    public function convertFile(string $rstFile, string $outputDir, string $inputBaseDir = null): void
    {
        $content = file_get_contents($rstFile);

        // Apply conversions in order
        $content = $this->handleMetaDirective($content);
        $content = $this->convertReferenceLabels($content);
        $content = $this->convertVersionAdded($content);
        $content = $this->convertIncludes($content);
        $content = $this->convertMiscDirectives($content);
        $content = $this->convertHeadings($content);
        $content = $this->convertPhpDirectives($content);
        $content = $this->convertCrossReferences($content);
        $content = $this->convertCodeBlocks($content);
        $content = $this->convertIndentedPhpBlocks($content);
        $content = $this->convertRstInlineCode($content);
        $content = $this->convertAdmonitions($content);
        $content = $this->convertContainers($content);
        $content = $this->convertTables($content);
        $content = $this->convertImages($content);
        $content = $this->convertLinks($content);
        $content = $this->convertLists($content);
        $content = $this->fixAbsolutePaths($content);
        $content = $this->fixBrokenMarkdownLinks($content);
        $content = $this->applyQuirks($content);
        $content = $this->normalizeIndentation($content);

        // Calculate output file path
        if ($inputBaseDir !== null) {
            // Remove the input base directory from the file path to get relative path
            $relativePath = str_replace($inputBaseDir . '/', '', $rstFile);
        } else {
            // Fallback to old logic if no base directory provided
            $inputDir = dirname($rstFile);
            $relativePath = str_replace(dirname($inputDir) . '/', '', $rstFile);
        }
        $mdFile = $outputDir . '/' . preg_replace('/\.rst$/', '.md', $relativePath);

        // Ensure output directory exists
        $mdDir = dirname($mdFile);
        if (!is_dir($mdDir)) {
            mkdir($mdDir, 0755, true);
        }

        // Write converted content
        file_put_contents($mdFile, $content);

        echo "Converted: $rstFile -> $mdFile\n";
    }

    public function convertDirectory(string $inputDir, string $outputDir): void
    {
        echo "Converting RST files from $inputDir to $outputDir\n";
        
        // Set base path from input directory (e.g., "./en" becomes "/en")
        $basePath = basename(realpath($inputDir));
        $this->setBasePath($basePath);
        echo "Using base path: /{$basePath}\n";

        // Create output directory
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Copy static files and other assets
        $staticDir = $inputDir . '/_static';
        if (is_dir($staticDir)) {
            $outputStatic = $outputDir . '/_static';
            if (is_dir($outputStatic)) {
                $this->removeDirectory($outputStatic);
            }
            $this->copyDirectory($staticDir, $outputStatic);
            echo "Copied static files to $outputStatic\n";
        }

        // Find and convert all RST files
        $rstFiles = $this->findRstFiles($inputDir);

        foreach ($rstFiles as $rstFile) {
            try {
                $this->convertFile($rstFile, $outputDir, $inputDir);
            } catch (Exception $e) {
                echo "Error converting $rstFile: " . $e->getMessage() . "\n";
            }
        }

        echo "Conversion complete. Processed " . count($rstFiles) . " files.\n";
    }

    public function findRstFiles(string $dir): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'rst') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    private function copyDirectory(string $src, string $dst): void
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if ($file !== '.' && $file !== '..') {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}

function showHelp(): void
{
    echo <<<HELP
CakePHP Documentation RST to Markdown Converter

USAGE:
    php convert_rst_to_md.php [input_dir] [output_dir]

ARGUMENTS:
    input_dir    Input directory containing RST files (default: ./en)
    output_dir   Output directory for Markdown files (default: ./docs)

OPTIONS:
    -h, --help   Show this help message

EXAMPLES:
    php convert_rst_to_md.php
    php convert_rst_to_md.php ./en ./docs
    php convert_rst_to_md.php ./en ./output/markdown

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

function main(): int
{
    global $argv;

    // Check for help flag
    if (in_array('-h', $argv) || in_array('--help', $argv)) {
        showHelp();
        return 0;
    }

    // Parse arguments
    $inputDir = './en';
    $outputDir = './docs';

    // Process command line arguments
    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];

        if (!str_starts_with($arg, '--')) {
            // Positional arguments
            if ($i === 1) {
                $inputDir = $arg;
            } elseif ($i === 2) {
                $outputDir = $arg;
            }
        }
    }

    if (!is_dir($inputDir)) {
        echo "Error: Input directory '$inputDir' does not exist\n";
        echo "Use -h or --help for usage information\n";
        return 1;
    }

    $converter = new RSTToMarkdownConverter();
    $converter->convertDirectory($inputDir, $outputDir);

    return 0;
}

// Run the script if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($argv[0])) {
    exit(main());
}
