# Configuration Files

This directory contains configuration files for the CakePHP RST to Markdown converter.

## Files

### `config.php`
Main configuration file containing:
- Default base path for internal links
- Language mappings for code blocks
- Admonition type mappings
- Heading level mappings
- Reference to quirks configuration

### `quirks.php`
Text replacement patterns and fixes applied during conversion. Contains:
- Simple string replacements (literal text)
- Regex pattern replacements (for complex patterns)
- HTML tag formatting fixes
- Special character escaping

## Usage

Most converter classes automatically load their configuration from these files. You can override settings by:

1. **Modifying the config files directly** (affects all conversions)
2. **Passing custom settings to constructors** (affects specific instances)

### Example: Custom Quirks

```php
// Use default quirks from config/quirks.php
$converter = new ApplyQuirks();

// Use custom quirks (ignores config file)
$customQuirks = ['old' => 'new'];
$converter = new ApplyQuirks($customQuirks);
```

### Example: Custom Code Language Mappings

```php
// Modify config/config.php to change default mappings
'code_language_mappings' => [
    'console' => 'bash',
    'mysql' => 'sql',
    'your-custom' => 'custom-lang',
],
```

## Adding New Quirks

To add new text replacements, edit `config/quirks.php`:

```php
return [
    // Simple string replacement
    'old text' => 'new text',

    // Regex replacement (must start with /)
    '/pattern/' => 'replacement',
];
```

The quirks are applied in the order they appear in the file.