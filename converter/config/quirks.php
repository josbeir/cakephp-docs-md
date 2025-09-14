<?php
/**
 * Quirks Configuration for CakePHP RST to Markdown Converter
 *
 * This file contains text replacements and fixes that need to be applied
 * during the conversion process to handle edge cases and formatting issues.
 */

return [
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
    '  <?= $variable ?>' => '```php' . "\n" . '<?= $variable ?>' . "\n" . '```', // Embed PHP short tags in code blocks
    '_static/img/' => '/',
    ':ref:`CakePHP request cycle]' => '[CakePHP request cycle]', // Fix broken link,
    ':doc:`caching]' => '[caching]', // Fix broken link
    '   <?= $this->Html->media(' => '```php' . "\n" . '   <?= $this->Html->media(',
    'codecs=\'theora, vorbis\'"/>' . "\n" . '    </video>' => 'codecs=\'theora, vorbis\'"/>' . "\n" . '    </video>' . "\n" . '```',
    'on <folder>' => 'on `<folder>`',
    // General pattern to fix malformed HTML tags from RST conversion
    '/``\]\(([^)]*?)\.md\)`/' => '`<$1>`',
    // Fix SQL operators that look like HTML tags
    "('created <' =>" => "('created &lt;' =>",
    
    // Fix malformed HTML tags from conversion errors
    '``](span.md)` tags' => '`<span>` tags',
    '``](div.md)` tags' => '`<div>` tags',
    '``](pre.md)` tags' => '`<pre>` tags', 
    '``](text-right.md)` formatting tag' => '`text-right` formatting tag',
    '``](li.md)` elements' => '`<li>` elements',
    '``](!--nocache--.md)` tags' => '`<!--nocache-->` tags',
    '``](table.md)`' => '`<table>`',
    '``](cake:nocache.md)`' => '`cake:nocache`',
    
    // HTML validation fixes for VitePress compatibility
    // Convert HTML anchor tags to HTML comments
    '/(<a id="[^"]*"><\/a>)/' => '<!-- anchor: $1 -->',
    '/^<a id="([^"]+)"><\/a>$/m' => '<!-- anchor: $1 -->',
    
    // Escape angle brackets in function names and placeholders
    '/findAllBy<([^>]+)>/' => 'findAllBy\\<$1\\>',
    '/findBy<([^>]+)>/' => 'findBy\\<$1\\>',
    '/<name>/' => '\\<name\\>',
    
    // Escape common HTML tags that Vue parser interprets incorrectly
    '/<head>/' => '\\<head\\>',
    '/<body>/' => '\\<body\\>',
    '/<html>/' => '\\<html\\>',
    '/<script>/' => '\\<script\\>',
    '/<div>/' => '\\<div\\>',
    '/<span>/' => '\\<span\\>',
    '/<table>/' => '\\<table\\>',
    '/<tr>/' => '\\<tr\\>',
    '/<td>/' => '\\<td\\>',
    '/<th>/' => '\\<th\\>',
    '/<ul>/' => '\\<ul\\>',
    '/<ol>/' => '\\<ol\\>',
    '/<li>/' => '\\<li\\>',
    '/<strong>/' => '\\<strong\\>',
    '/<em>/' => '\\<em\\>',
    '/<br>/' => '\\<br\\>',
    '/<hr>/' => '\\<hr\\>',
    '/<p>/' => '\\<p\\>',
    '/(<div[^>]*>)/' => '\\$1',
    
    // Fix CDATA sections
    '/<!\[CDATA\[/' => '\\<![CDATA[',
    '/\]\]>/' => ']]\\>',
    
    // Convert problematic HTML links to markdown-friendly format
    '/<a href="([^"]*)"[^>]*>([^<]*)<\/a>/' => '`$2` (href: $1)',
    
    // Fix folder placeholders and PHP code snippets in text
    'on <folder>' => 'on \\<folder\\>',
    '/\<\?= `([^`]+)`; \?\>/' => '\\<?= \\`$1\\`; ?\\>',
];
