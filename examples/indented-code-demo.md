# Indented Code Block Conversion Demo

This document demonstrates the improved indented code block conversion with language detection.

## Before (Old Implementation)

The old implementation would convert indented code blocks like this:

```
Configuration::

    <?php
    return [
        'debug' => true,
    ];
```

Would become:

````
Configuration::

```

<?php
return [
    'debug' => true,
];
```
````

**Issues:**
- Extra newline after opening backticks
- No language detection (plain text)
- Poor formatting

## After (New Implementation)

The new implementation converts the same code to:

````
Configuration::

```php
<?php
return [
    'debug' => true,
];
```
````

**Improvements:**
- ✅ No extra newlines
- ✅ Automatic language detection (`php`)
- ✅ Clean formatting
- ✅ Proper indentation handling

## Supported Languages

The new implementation can detect and label these languages:

### PHP
```php
<?php
class Example {
    public function test() {
        return $this->property;
    }
}
```

### JavaScript
```javascript
function validateForm() {
    var name = document.getElementById('name');
    console.log('Validating:', name.value);
    return true;
}
```

### SQL
```sql
SELECT id, title, body
FROM articles
WHERE published = 1
ORDER BY created DESC;
```

### Bash/Shell
```bash
$ composer install
$ php bin/cake.php bake controller Articles
sudo chmod +x bin/cake
```

### YAML
```yaml
version: '3'
services:
  web:
    image: php:8.1
    ports:
      - "8080:80"
```

### JSON
```json
{
  "name": "my-app",
  "require": {
    "cakephp/cakephp": "^5.0"
  }
}
```

## Language Detection Algorithm

The converter uses pattern matching to score potential languages:

1. **Pattern Matching**: Each language has specific regex patterns
2. **Scoring**: Patterns that match increase the language score
3. **Best Match**: Language with highest score wins
4. **Fallback**: If no patterns match, uses plain text (no language)

## Edge Cases Handled

- Mixed indentation levels
- Empty lines within code blocks
- Code blocks with comments
- Backticks with double colon syntax (``:method():``)
- Multiple code blocks in sequence