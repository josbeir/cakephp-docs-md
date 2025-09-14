---
title: Test
---

# Test

```php
public function findAvailable($state, $query, $results = array()) {
    if ($state === 'before') {
        $query['conditions']['Recipe.type'] = 'available';
        return $query;
    }
    return $results;
}

}
```

This is after the code block.