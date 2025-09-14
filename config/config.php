<?php
/**
 * Main Configuration for CakePHP RST to Markdown Converter
 */

return [
    /**
     * Default quirks to apply during conversion
     * These are loaded automatically by ApplyQuirks if no custom quirks are provided
     */
    'quirks' => require __DIR__ . '/quirks.php',

    /**
     * Default base path for internal links
     * Can be overridden when creating converters
     */
    'default_base_path' => '/en',

    /**
     * Language mappings for code blocks
     * Maps RST language names to markdown language names
     */
    'code_language_mappings' => [
        'console' => 'bash',
        'mysql' => 'sql',
        'apacheconf' => 'apache',
    ],

    /**
     * Admonition type mappings
     * Maps RST admonition types to GitHub-style callout types
     */
    'admonition_mappings' => [
        'note' => 'NOTE',
        'warning' => 'WARNING',
        'tip' => 'TIP',
        'seealso' => 'NOTE',
        'important' => 'IMPORTANT',
        'caution' => 'CAUTION',
    ],

    /**
     * Heading level mappings
     * Maps RST heading characters to markdown heading levels
     */
    'heading_mappings' => [
        '#' => 1, '*' => 1,  // Title level
        '=' => 2, // Section
        '-' => 3, // Subsection
        '~' => 4, // Sub-subsection
        '^' => 5, // Paragraph
        '"' => 6, // Subparagraph
    ],
];
