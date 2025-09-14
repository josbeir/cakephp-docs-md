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
];
