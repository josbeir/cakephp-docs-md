<?php
// phpcs:ignoreFile
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Cake\DocsMD\Converter;
use Cake\DocsMD\ConvertSteps\{
    ApplyQuirks,
    ConvertAdmonitions,
    ConvertCodeBlocks,
    ConvertContainers,
    ConvertCrossReferences,
    ConvertHeadings,
    ConvertImages,
    ConvertIncludes,
    ConvertIndentedPhpBlocks,
    ConvertLinks,
    ConvertLists,
    ConvertMiscDirectives,
    ConvertPhpDirectives,
    ConvertReferenceLabels,
    ConvertRstInlineCode,
    ConvertTables,
    ConvertVersionAdded,
    FixAbsolutePaths,
    FixBrokenMarkdownLinks,
    HandleMetaDirective,
    NormalizeIndentation
};

echo "CakePHP Docs MD Converter - Comprehensive Test Suite\n";
echo "====================================================\n\n";

// =============================================================================
// ORIGINAL BASIC TESTS
// =============================================================================

function runBasicTests(): void
{
    echo "Running basic integration tests...\n";
    echo str_repeat('-', 50) . "\n";

    function runPipeline(string $content): string
    {
        $basePath = '/en';
        $labelToDocumentMap = ['my-label' => 'test.md'];
        $pipeline = Converter::getPipeline($basePath, $labelToDocumentMap);
        foreach ($pipeline as $step) {
            $content = $step($content);
        }
        return $content;
    }

    $fixturesDir = __DIR__ . '/fixtures';
    $rst = file_get_contents($fixturesDir . '/test1.rst');

    // Run conversion on test1
    $output = runPipeline($rst);

    // Basic assertions
    assert(str_contains($output, '---'), 'Front matter should be added');
    assert(str_contains($output, '# Title'), 'Title should be converted to markdown heading');
    assert(str_contains($output, '> [!NOTE]'), 'Note admonition should be converted');
    assert(str_contains($output, '```php'), 'Code block should be converted to fenced code');
    assert(str_contains($output, '![](/_static/img/read-the-book.jpg)'), 'Image should be converted');
    assert(str_contains($output, '<a id="my-label"></a>') || str_contains($output, '(#my-label)'), 'Reference label should be converted');
    assert(str_contains($output, 'Included Title') || str_contains($output, 'Included content'), 'Included file content should be referenced');

    echo "✓ Basic integration tests passed\n\n";
}

// =============================================================================
// UNIT TESTS FOR CONVERT STEPS
// =============================================================================

function testConvertHeadings(): void
{
    echo "Testing ConvertHeadings...\n";
    $converter = new ConvertHeadings();

    // Test different heading levels
    $input = "Title\n=====\n\nSubtitle\n--------\n\nSub-subtitle\n~~~~~~~~~~~~";
    $expected = "## Title\n\n### Subtitle\n\n#### Sub-subtitle";
    $result = $converter($input);
    assert($result === $expected, "Heading conversion failed");

    // Test heading with hash symbols
    $input = "Main Title\n##########";
    $expected = "# Main Title";
    $result = $converter($input);
    assert($result === $expected, "Hash heading conversion failed");

    echo "✓ ConvertHeadings tests passed\n";
}

function testConvertAdmonitions(): void
{
    echo "Testing ConvertAdmonitions...\n";
    $converter = new ConvertAdmonitions();

    // Test note admonition
    $input = ".. note:: This is a note";
    $expected = "> [!NOTE]\n> This is a note";
    $result = $converter($input);
    assert($result === $expected, "Note admonition failed");

    // Test warning admonition with multiline content
    $input = ".. warning::\n    This is a warning\n    with multiple lines";
    $expected = "> [!WARNING]\n> This is a warning\n> with multiple lines";
    $result = $converter($input);
    assert($result === $expected, "Warning admonition failed");

    // Test all admonition types
    $types = ['tip' => 'TIP', 'important' => 'IMPORTANT', 'caution' => 'CAUTION', 'seealso' => 'NOTE'];
    foreach ($types as $type => $expected_type) {
        $input = ".. $type:: Test content";
        $expected = "> [!$expected_type]\n> Test content";
        $result = $converter($input);
        assert($result === $expected, "$type admonition failed");
    }

    echo "✓ ConvertAdmonitions tests passed\n";
}

function testConvertCodeBlocks(): void
{
    echo "Testing ConvertCodeBlocks...\n";
    $converter = new ConvertCodeBlocks();

    // Test basic code block
    $input = ".. code-block:: php\n\n    <?php\n    echo 'hello';";
    $expected = "\n```php\n\n<?php\necho 'hello';```";
    $result = $converter($input);
    assert($result === $expected, "Basic code block failed");

    // Test language mapping
    $input = ".. code-block:: console\n\n    $ ls -la";
    $expected = "\n```bash\n\n$ ls -la```";
    $result = $converter($input);
    assert($result === $expected, "Console to bash mapping failed");

    // Test double colon syntax
    $input = "Example::\n\n    some code\n    here";
    $expected = "Example:\n\n```\nsome code\nhere```";
    $result = $converter($input);
    assert($result === $expected, "Double colon syntax failed");

    echo "✓ ConvertCodeBlocks tests passed\n";
}

function testConvertLinks(): void
{
    echo "Testing ConvertLinks...\n";
    $converter = new ConvertLinks('/en');

    // Test external link with text
    $input = "`Link text <https://example.com>`__";
    $expected = "[Link text](https://example.com)";
    $result = $converter($input);
    assert($result === $expected, "External link failed");

    // Test internal link
    $input = "`Some page <internal-page>`__";
    $expected = "[Some page](/en/internal-page.md)";
    $result = $converter($input);
    assert($result === $expected, "Internal link failed");

    // Test single underscore link
    $input = "`Another link <page>`_";
    $expected = "[Another link](/en/page.md)";
    $result = $converter($input);
    assert($result === $expected, "Single underscore link failed");

    echo "✓ ConvertLinks tests passed\n";
}

function testConvertCrossReferences(): void
{
    echo "Testing ConvertCrossReferences...\n";
    $labelMap = ['my-label' => 'test.md'];
    $converter = new ConvertCrossReferences('/en', $labelMap);

    // Test PHP class reference
    $input = ":php:class:`Cake\\Controller\\Controller`";
    $expected = "`Cake\\Controller\\Controller`";
    $result = $converter($input);
    assert($result === $expected, "PHP class reference failed");

    // Test PHP method reference with tilde
    $input = ":php:meth:`~Cake\\Controller\\Controller::redirect()`";
    $expected = "`Cake\\Controller\\Controller::redirect()`";
    $result = $converter($input);
    assert($result === $expected, "PHP method reference failed");

    // Test abbreviation
    $input = ":abbr:`ORM (Object Relational Mapping)`";
    $expected = '<abbr title="Object Relational Mapping">ORM</abbr>';
    $result = $converter($input);
    assert($result === $expected, "Abbreviation failed");

    // Test doc reference with title (explicit title should be preserved)
    $input = ":doc:`Installation Guide </installation>`";
    $expected = "[Installation Guide](/en/installation.md)";
    $result = $converter($input);
    assert($result === $expected, "Doc reference with title failed");

    // Test doc reference with title extraction from actual file
    $input = ":doc:`installation`";
    $expected = "[Installation](/en/installation.md)";
    $result = $converter($input);
    assert($result === $expected, "Doc reference with title extraction failed");

    // Test ref with label mapping
    $input = ":ref:`My Label <my-label>`";
    $expected = "[My Label](/en/test.md#my-label)";
    $result = $converter($input);
    assert($result === $expected, "Ref with label mapping failed");

    // Test ref without label mapping (local reference)
    $input = ":ref:`unknown-label`";
    $expected = "[unknown-label](#unknown-label)";
    $result = $converter($input);
    assert($result === $expected, "Local ref failed");

    echo "✓ ConvertCrossReferences tests passed\n";
}

function testConvertImages(): void
{
    echo "Testing ConvertImages...\n";
    $converter = new ConvertImages();

    $input = ".. image:: /_static/img/cake-logo.png";
    $expected = "![](_static/img/cake-logo.png)";
    $result = $converter($input);
    assert($result === $expected, "Image conversion failed");

    echo "✓ ConvertImages tests passed\n";
}

function testConvertLists(): void
{
    echo "Testing ConvertLists...\n";
    $converter = new ConvertLists();

    // Test bullet lists
    $input = "* First item\n* Second item\n  * Nested item";
    $expected = "- First item\n- Second item\n  - Nested item";
    $result = $converter($input);
    assert($result === $expected, "Bullet list conversion failed");

    // Test numbered lists
    $input = "1. First\n2. Second\n   3. Nested";
    $expected = "1. First\n2. Second\n   3. Nested";
    $result = $converter($input);
    assert($result === $expected, "Numbered list should remain unchanged");

    echo "✓ ConvertLists tests passed\n";
}

function testConvertReferenceLabels(): void
{
    echo "Testing ConvertReferenceLabels...\n";
    $converter = new ConvertReferenceLabels();

    $input = ".. _my-label:\n\nSome content";
    $expected = "<a id=\"my-label\"></a>\n\nSome content";
    $result = $converter($input);
    assert($result === $expected, "Reference label conversion failed");

    echo "✓ ConvertReferenceLabels tests passed\n";
}

function testConvertRstInlineCode(): void
{
    echo "Testing ConvertRstInlineCode...\n";
    $converter = new ConvertRstInlineCode();

    // Test double backticks
    $input = "Use ``ClassName`` for this.";
    $expected = "Use `ClassName` for this.";
    $result = $converter($input);
    assert($result === $expected, "Double backticks conversion failed");

    // Test double backticks followed by double colon
    $input = "``ClassName``::";
    $expected = "`ClassName`";
    $result = $converter($input);
    assert($result === $expected, "Double backticks with double colon failed");

    echo "✓ ConvertRstInlineCode tests passed\n";
}

function testHandleMetaDirective(): void
{
    echo "Testing HandleMetaDirective...\n";
    $converter = new HandleMetaDirective();

    $input = ".. meta::\n    :title: Test Page\n    :description: A test page\n\nContent here";
    $expected = "---\ntitle: Test Page\ndescription: A test page\n---\n\nContent here";
    $result = $converter($input);
    assert($result === $expected, "Meta directive conversion failed");

    // Test with quoted values that contain commas
    $input = ".. meta::\n    :keywords: php, cakephp, framework\n\nContent";
    $expected = "---\nkeywords: \"php, cakephp, framework\"\n---\n\nContent";
    $result = $converter($input);
    assert($result === $expected, "Meta with commas failed");

    echo "✓ HandleMetaDirective tests passed\n";
}

function testConvertIncludes(): void
{
    echo "Testing ConvertIncludes...\n";
    $converter = new ConvertIncludes();

    $input = ".. include:: /path/to/file.rst";
    $expected = "<!--@include: ./path/to/file.md-->";
    $result = $converter($input);
    assert($result === $expected, "Include conversion failed");

    echo "✓ ConvertIncludes tests passed\n";
}

function testConvertVersionAdded(): void
{
    echo "Testing ConvertVersionAdded...\n";
    $converter = new ConvertVersionAdded();

    $input = ".. versionadded:: 4.0\n    This feature was added";
    $expected = "> [!IMPORTANT]\n> Added in version 4.0\n> This feature was added";
    $result = $converter($input);
    assert($result === $expected, "Version added conversion failed");

    echo "✓ ConvertVersionAdded tests passed\n";
}

function testConvertPhpDirectives(): void
{
    echo "Testing ConvertPhpDirectives...\n";
    $converter = new ConvertPhpDirectives();

    // Test namespace
    $input = ".. php:namespace:: Cake\\Controller\n\n.. php:class:: AppController\n\n.. php:method:: beforeFilter()";
    $expected = "**Namespace:** `Cake\\Controller`\n\n### Class `Cake\\Controller\\AppController`\n\n#### Method `Cake\\Controller\\AppController::beforeFilter()`\n";
    $result = $converter($input);
    assert($result === $expected, "PHP directives conversion failed");

    // Test static method
    $input = ".. php:namespace:: Cake\\Utility\n\n.. php:class:: Hash\n\n.. php:staticmethod:: get(array \$data, string \$path)";
    $expected = "**Namespace:** `Cake\\Utility`\n\n### Class `Cake\\Utility\\Hash`\n\n#### Static Method `Cake\\Utility\\Hash::get(array \$data, string \$path)`\n";
    $result = $converter($input);
    assert($result === $expected, "PHP static method failed");

    // Test global function
    $input = ".. php:function:: debug(mixed \$var)";
    $expected = "#### Function `debug(mixed \$var)`\n";
    $result = $converter($input);
    assert($result === $expected, "PHP function failed");

    echo "✓ ConvertPhpDirectives tests passed\n";
}

function testApplyQuirks(): void
{
    echo "Testing ApplyQuirks...\n";

    // Test with custom quirks
    $quirks = [
        'CakePHP' => 'CakePHP Framework',
        '/\bORM\b/' => 'Object-Relational Mapping'
    ];
    $converter = new ApplyQuirks($quirks);

    $input = "CakePHP has a powerful ORM system.";
    $expected = "CakePHP Framework has a powerful Object-Relational Mapping system.";
    $result = $converter($input);
    assert($result === $expected, "Custom quirks application failed");

    // Test with default quirks from config
    $defaultConverter = new ApplyQuirks();
    $input = "add<registry-object>() method";
    $result = $defaultConverter($input);
    assert(str_contains($result, '`add<registry-object>()`'), "Default quirks should be loaded from config");

    $input = "proper <p> around text";
    $result = $defaultConverter($input);
    assert(str_contains($result, 'proper `<p>` around'), "HTML tag quirks should be applied");

    echo "✓ ApplyQuirks tests passed\n";
}

function testRemoveIndexDirectives(): void
{
    echo "Testing RemoveIndexDirectives...\n";

    $rst = "Chapter Title\n=============\n\n.. index:: \$this->request\n\nSome content here.\n\n.. index:: nested commands, subcommands\n\nMore content.";
    $expected = "Chapter Title\n=============\n\nSome content here.\n\nMore content.";
    $remover = new \Cake\DocsMD\ConvertSteps\RemoveIndexDirectives();
    $result = $remover($rst);
    assert($result === $expected, 'RemoveIndexDirectives test failed');

    $removedDirectives = $remover->getRemovedDirectives();
    assert(count($removedDirectives) === 2, 'Should have removed 2 directives');
    assert(in_array('$this->request', $removedDirectives), 'Should have captured first directive');
    assert(in_array('nested commands, subcommands', $removedDirectives), 'Should have captured second directive');

    echo "✓ RemoveIndexDirectives tests passed\n";
}

function testNormalizeSpacing(): void
{
    echo "Testing NormalizeSpacing...\n";

    $normalizer = new \Cake\DocsMD\ConvertSteps\NormalizeSpacing();

    // Test alert spacing
    $input = "Some text before.\n> [!NOTE]\n> This is a note.\nMore text immediately after.";
    $expected = "Some text before.\n\n> [!NOTE]\n> This is a note.\n\nMore text immediately after.";
    $result = $normalizer($input);
    assert($result === $expected, 'Alert spacing test failed');

    // Test code block spacing
    $input = "Text before.\n```php\necho 'hello';\n```\nText after.";
    $expected = "Text before.\n\n```php\necho 'hello';\n```\n\nText after.";
    $result = $normalizer($input);
    assert($result === $expected, 'Code block spacing test failed');

    // Test that properly spaced content is left alone
    $input = "Text before.\n\n> [!WARNING]\n> This is properly spaced.\n\nMore text.";
    $result = $normalizer($input);
    assert($result === $input, 'Already proper spacing should be unchanged');

    echo "✓ NormalizeSpacing tests passed\n";
}

// =============================================================================
// ADDITIONAL CONVERT STEPS TESTS
// =============================================================================

function testConvertIndentedPhpBlocks(): void
{
    echo "Testing ConvertIndentedPhpBlocks...\n";
    $converter = new ConvertIndentedPhpBlocks();

    // Note: ConvertIndentedPhpBlocks is now primarily handled by ConvertCodeBlocks
    // These tests verify the functionality works correctly through the pipeline

    // Test simple input that should not be affected
    $input = "Regular text with no code blocks.";
    $expected = "Regular text with no code blocks.";
    $result = $converter($input);
    assert($result === $expected, "Regular text should pass through unchanged");

    // Test that it doesn't interfere with non-code content
    $input = "Some text::\n\nNot indented so not a code block.";
    $expected = "Some text::\n\nNot indented so not a code block.";
    $result = $converter($input);
    assert($result === $expected, "Non-indented content after :: should not be treated as code");

    echo "✓ ConvertIndentedPhpBlocks tests passed\n";
}

function testAdditionalConvertSteps(): void
{
    echo "Testing additional ConvertSteps...\n";

    // Test improved ConvertIndentedPhpBlocks
    testConvertIndentedPhpBlocks();

    // Test stub implementations (these just need to not crash)
    $converters = [
        'ConvertContainers' => new ConvertContainers(),
        'ConvertMiscDirectives' => new ConvertMiscDirectives(),
        'FixAbsolutePaths' => new FixAbsolutePaths(),
        'FixBrokenMarkdownLinks' => new FixBrokenMarkdownLinks(),
        'NormalizeIndentation' => new NormalizeIndentation()
    ];

    foreach ($converters as $name => $converter) {
        $input = "Test content for $name";
        $result = $converter($input);
        assert(is_string($result), "$name should return string");
    }

    echo "✓ Additional ConvertSteps tests passed\n";
}

// =============================================================================
// COMPLEX SCENARIO TESTS
// =============================================================================

function testComplexScenarios(): void
{
    echo "Testing complex scenarios...\n";

    // Test nested admonitions
    $admonitionConverter = new ConvertAdmonitions();
    $input = ".. note::\n    This is a note\n    \n    .. warning::\n        Nested warning\n        \n    Back to note content";
    $result = $admonitionConverter($input);
    assert(str_contains($result, '> [!NOTE]'), "Should handle nested admonitions");

    // Test mixed code blocks and lists
    $codeConverter = new ConvertCodeBlocks();
    $listConverter = new ConvertLists();

    $input = "* First item\n\n.. code-block:: php\n\n    <?php\n    echo 'test';\n\n* Second item";
    $result = $codeConverter($input);
    $result = $listConverter($result);

    assert(str_contains($result, '- First item'), "Should convert lists");
    assert(str_contains($result, '```php'), "Should convert code blocks");

    // Test consecutive headings
    $headingConverter = new ConvertHeadings();
    $input = "Main Title\n==========\n\nSubtitle\n--------\n\nAnother Section\n~~~~~~~~~~~~~~~";
    $result = $headingConverter($input);
    assert(str_contains($result, '## Main Title'), "Should convert first heading");
    assert(str_contains($result, '### Subtitle'), "Should convert second heading");
    assert(str_contains($result, '#### Another Section'), "Should convert third heading");

    echo "✓ Complex scenarios tests passed\n";
}

// =============================================================================
// INTEGRATION TESTS
// =============================================================================

function testComplexRstConversion(): void
{
    echo "Testing complex RST conversion with full pipeline...\n";

    $basePath = '/en';
    $labelToDocumentMap = [
        'my-reference-label' => 'complex.md',
        'installation' => 'install.md'
    ];

    $fixturesDir = __DIR__ . '/fixtures';
    $rstContent = file_get_contents($fixturesDir . '/complex.rst');

    // Run the full pipeline
    $pipeline = Converter::getPipeline($basePath, $labelToDocumentMap);
    $result = $rstContent;

    foreach ($pipeline as $step) {
        $result = $step($result);
    }

    // Comprehensive assertions
    assert(str_contains($result, '---'), 'Should contain front matter');
    assert(str_contains($result, 'title: Complex RST Example'), 'Should contain title metadata');
    assert(str_contains($result, '## Complex Document'), 'H1 should convert to H2');
    assert(str_contains($result, '> [!NOTE]'), 'Should convert note admonition');
    assert(str_contains($result, '```php'), 'Should convert PHP code blocks');
    assert(str_contains($result, '- First item'), 'Should convert bullet lists');
    assert(str_contains($result, '<a id="my-reference-label"></a>'), 'Should convert reference labels');
    assert(str_contains($result, '`Cake\\Controller\\Controller`'), 'Should clean PHP class references');
    assert(str_contains($result, '**Namespace:** `Cake\ORM`'), 'Should convert namespace directive');
    assert(str_contains($result, '> [!IMPORTANT]'), 'Should convert version added directive');
    assert(str_contains($result, '![](_static/logo.png)'), 'Should convert images');
    assert(str_contains($result, '<!--@include: ./shared/footer.md-->'), 'Should convert includes');
    assert(str_contains($result, '`inline code`'), 'Should convert inline code');
    assert(str_contains($result, '[CakePHP Website](https://cakephp.org)'), 'Should convert external links');
    assert(str_contains($result, '<abbr title="Object Relational Mapping">ORM</abbr>'), 'Should convert abbreviations');

    echo "✓ Complex RST conversion test passed\n";
}

function testQuirksIntegration(): void
{
    echo "Testing quirks integration in full pipeline...\n";

    $basePath = '/en';
    $labelToDocumentMap = [];

    $fixturesDir = __DIR__ . '/fixtures';
    $rstContent = file_get_contents($fixturesDir . '/quirks-test.rst');

    // Run the full pipeline
    $pipeline = Converter::getPipeline($basePath, $labelToDocumentMap);
    $result = $rstContent;

    foreach ($pipeline as $step) {
        $result = $step($result);
    }

    // Test that quirks were applied
    assert(str_contains($result, '`add<registry-object>()`'), 'Should apply add<registry-object>() quirk');
    assert(str_contains($result, 'proper `<p>` around'), 'Should apply <p> tag quirk');
    assert(str_contains($result, 'and `<br>` where'), 'Should apply <br> tag quirk');
    assert(str_contains($result, '`<td>`-attributes'), 'Should apply <td> tag quirk');
    assert(str_contains($result, '`<tr>` attributes'), 'Should apply <tr> tag quirk');
    assert(str_contains($result, 'inside of `<table>`'), 'Should apply <table> tag quirk');
    assert(str_contains($result, '\\<locale\\>'), 'Should escape <locale> placeholder');

    echo "✓ Quirks integration test passed\n";
}

function testIndentedCodeBlocksIntegration(): void
{
    echo "Testing indented code blocks integration...\n";

    $basePath = '/en';
    $labelToDocumentMap = [];

    $fixturesDir = __DIR__ . '/fixtures';
    $rstContent = file_get_contents($fixturesDir . '/indented-code-test.rst');

    // Run the full pipeline
    $pipeline = Converter::getPipeline($basePath, $labelToDocumentMap);
    $result = $rstContent;

    foreach ($pipeline as $step) {
        $result = $step($result);
    }

    // Test that indented code blocks were converted with proper language detection
    assert(str_contains($result, '```php'), 'Should detect PHP code blocks');
    assert(str_contains($result, '```sql'), 'Should detect SQL code blocks');
    assert(str_contains($result, '```javascript'), 'Should detect JavaScript code blocks');
    assert(str_contains($result, '```bash'), 'Should detect bash/shell code blocks');
    assert(str_contains($result, '```yaml'), 'Should detect YAML code blocks');

    // Test specific PHP patterns
    assert(str_contains($result, "```php\n<?php\n// config/app_local.php"), 'Should format PHP config correctly');
    assert(str_contains($result, "```php\nclass ArticlesTable extends Table"), 'Should format PHP class correctly');

    // Test SQL patterns
    assert(str_contains($result, "```sql\nSELECT id, title, body"), 'Should format SQL query correctly');

    // Test JavaScript patterns
    assert(str_contains($result, "```javascript\nfunction validateForm()"), 'Should format JavaScript function correctly');

    // Test shell commands
    assert(str_contains($result, "```bash\n$ composer create-project"), 'Should format shell commands correctly');

    // Test YAML
    assert(str_contains($result, "```yaml\nversion: '3'"), 'Should format YAML correctly');

    // Ensure no extra newlines after opening backticks
    assert(!str_contains($result, "```php\n\n<?php"), 'Should not have extra newlines after opening backticks');
    assert(!str_contains($result, "```sql\n\nSELECT"), 'Should not have extra newlines in SQL blocks');

    echo "✓ Indented code blocks integration test passed\n";
}

function testAdmonitionsAndCodeBlocksIntegration(): void
{
    echo "Testing admonitions and code blocks integration...\n";

    $basePath = '/en';
    $labelToDocumentMap = [];

    $fixturesDir = __DIR__ . '/fixtures';
    $rstContent = file_get_contents($fixturesDir . '/admonition-and-code-test.rst');

    // Run the full pipeline
    $pipeline = Converter::getPipeline($basePath, $labelToDocumentMap);
    $result = $rstContent;

    foreach ($pipeline as $step) {
        $result = $step($result);
    }

    // Test that admonitions use proper > [!TYPE] format (not code blocks)
    assert(str_contains($result, '> [!NOTE]'), 'Should convert note admonitions with > prefix');
    assert(str_contains($result, '> [!WARNING]'), 'Should convert warning admonitions with > prefix');
    assert(str_contains($result, '> This is a note that should use > prefixes'), 'Should use > prefix for admonition content');
    assert(str_contains($result, '> This warning should also use > prefixes'), 'Should use > prefix for warning content');

    // Test that code blocks work correctly
    assert(str_contains($result, '```php'), 'Should detect PHP code blocks with language identifier');
    assert(str_contains($result, "```php\n<?php\nreturn ["), 'Should not have extra newlines after opening backticks');
    assert(!str_contains($result, "```php\n\n<?php"), 'Should not have double newlines after opening backticks');

    // Test that explicit code blocks work
    assert(str_contains($result, "```php\n<?php\nclass Example"), 'Should handle explicit code blocks correctly');

    // Ensure admonitions are not converted to code blocks
    assert(!str_contains($result, '```note'), 'Should not convert admonitions to code blocks');
    assert(!str_contains($result, '```warning'), 'Should not convert admonitions to code blocks');

    echo "✓ Admonitions and code blocks integration test passed\n";
}

function testEdgeCases(): void
{
    echo "Testing edge cases...\n";

    $basePath = '/en';
    $labelToDocumentMap = [];

    // Test empty content
    $pipeline = Converter::getPipeline($basePath, $labelToDocumentMap);
    $result = '';
    foreach ($pipeline as $step) {
        $result = $step($result);
    }
    assert($result === '', 'Empty content should remain empty');

    // Test content with no RST directives
    $plainText = "This is just plain text\nwith no RST directives.";
    $result = $plainText;
    foreach ($pipeline as $step) {
        $result = $step($result);
    }
    assert($result === $plainText, 'Plain text should be unchanged');

    // Test error handling
    $converter = new ConvertHeadings();
    $result = $converter('');
    assert($result === '', "Empty input should return empty string");

    // Test special characters
    $specialChars = "Special chars: éñáøü 🎉 ©®™";
    $result = $converter($specialChars);
    assert(str_contains($result, 'éñáøü'), "Special characters should be preserved");

    echo "✓ Edge cases test passed\n";
}

// =============================================================================
// MAIN TEST RUNNER
// =============================================================================

function runAllTests(): void
{
    $testSections = [
        'Basic Tests' => 'runBasicTests',
        'Unit Tests' => function() {
            echo "Running ConvertSteps unit tests...\n";
            echo str_repeat('-', 50) . "\n";

            testConvertHeadings();
            testConvertAdmonitions();
            testConvertCodeBlocks();
            testConvertLinks();
            testConvertCrossReferences();
            testConvertImages();
            testConvertLists();
            testConvertReferenceLabels();
            testConvertRstInlineCode();
            testHandleMetaDirective();
            testConvertIncludes();
            testConvertVersionAdded();
            testConvertPhpDirectives();
            testApplyQuirks();
            testRemoveIndexDirectives();
            testNormalizeSpacing();
            testAdditionalConvertSteps();

            echo "✓ All unit tests passed\n\n";
        },
        'Complex Scenarios' => function() {
            echo "Running complex scenario tests...\n";
            echo str_repeat('-', 50) . "\n";

            testComplexScenarios();

            echo "✓ Complex scenario tests passed\n\n";
        },
        'Integration Tests' => function() {
            echo "Running integration tests...\n";
            echo str_repeat('-', 50) . "\n";

            testComplexRstConversion();
            testQuirksIntegration();
            testIndentedCodeBlocksIntegration();
            testAdmonitionsAndCodeBlocksIntegration();
            testEdgeCases();

            echo "✓ Integration tests passed\n\n";
        }
    ];

    $totalSections = count($testSections);
    $passedSections = 0;

    foreach ($testSections as $sectionName => $testFunction) {
        try {
            $testFunction();
            $passedSections++;
        } catch (Throwable $e) {
            echo "✗ $sectionName failed: " . $e->getMessage() . "\n\n";
        }
    }

    echo "Test Results Summary\n";
    echo "==================\n";
    echo "Total sections: $totalSections\n";
    echo "Passed: $passedSections\n";
    echo "Failed: " . ($totalSections - $passedSections) . "\n\n";

    if ($passedSections === $totalSections) {
        echo "🎉 All tests passed!\n";
    } else {
        exit(1);
    }
}

// Run all tests
runAllTests();