---
title: Inflector
keywords: "apple orange,word variations,apple pie,person man,latin versions,profile settings,php class,initial state,puree,slug,apples,oranges,user profile,underscore"
---

# Inflector

**Namespace:** `Cake\Utility`


### Class `Cake\Utility\Inflector`


The Inflector class takes a string and can manipulate it to handle word
variations such as pluralization or camelizing and is normally accessed
statically. Example:
`Inflector::pluralize('example')` returns "examples".

You can try out the inflections online at [inflector.cakephp.org](https://inflector.cakephp.org/) or [sandbox.dereuromark.de](https://sandbox.dereuromark.de/sandbox/inflector).
<a id="inflector-methods-summary"></a>
## Summary of Inflector Methods and Their Output

Quick summary of the Inflector built-in methods and the results they output
when provided a multi-word argument:


| Method | Argument | Output |
| --- | --- | --- |
| `pluralize()`   | | igApple      | | igApples     | |
|  | -------------- | -------------- |
|  | big_apple | big_apples |
| `singularize()` | | igApples     | | igApple      | |
|  | -------------- | -------------- |
|  | big_apples | big_apple |
| `camelize()`    | | ig_apples    | | igApples     | |
|  | -------------- | -------------- |
|  | big apple | BigApple |
| `underscore()`  | | igApples     | | ig_apples    | |
|  | -------------- | -------------- |
|  | Big Apples | big apples |
| `humanize()`    | | ig_apples    | | ig Apples    | |
|  | -------------- | -------------- |
|  | bigApple | BigApple |
| `classify()`    | | ig_apples    | | igApple      | |
|  | -------------- | -------------- |
|  | big apple | BigApple |
| `dasherize()`   | | igApples     | | ig-apples    | |
|  | -------------- | -------------- |
|  | big apple | big apple |
| `tableize()`    | | igApple      | | ig_apples    | |
|  | -------------- | -------------- |
|  | Big Apple | big apples |
| `variable()`    | | ig_apple     | | igApple      | |
|  | -------------- | -------------- |
|  | big apples | bigApples |


## Creating Plural & Singular Forms

#### Static Method `Cake\Utility\Inflector::singularize($singular)`

#### Static Method `Cake\Utility\Inflector::pluralize($singular)`


Both `pluralize` and `singularize()` work on most English nouns. If you need
to support other languages, you can use [inflection-configuration](#inflection-configuration) to
customize the rules used

```php
// Apples
echo Inflector::pluralize('Apple');
```
> [!NOTE]
> `pluralize()` should not be used on a noun that is already in its plural form.
```php
// Person
echo Inflector::singularize('People');
```
> [!NOTE]
> `singularize()` should not be used on a noun that is already in its singular form.
>
## Creating CamelCase and under_scored Forms

#### Static Method `Cake\Utility\Inflector::camelize($underscored)`

#### Static Method `Cake\Utility\Inflector::underscore($camelCase)`


These methods are useful when creating class names, or property names
```php
// ApplePie
Inflector::camelize('Apple_pie')

// apple_pie
Inflector::underscore('ApplePie');
```
It should be noted that underscore will only convert camelCase formatted words.
Words that contains spaces will be lower-cased, but will not contain an
underscore.

## Creating Human Readable Forms

#### Static Method `Cake\Utility\Inflector::humanize($underscored)`


This method is useful when converting underscored forms into "Title Case" forms
for human readable values
```php
// Apple Pie
Inflector::humanize('apple_pie');
```
## Creating Table and Class Name Forms

#### Static Method `Cake\Utility\Inflector::classify($underscored)`

#### Static Method `Cake\Utility\Inflector::dasherize($dashed)`

#### Static Method `Cake\Utility\Inflector::tableize($camelCase)`


When generating code, or using CakePHP's conventions you may need to inflect
table names or class names
```php
// UserProfileSetting
Inflector::classify('user_profile_settings');

// user-profile-setting
Inflector::dasherize('UserProfileSetting');

// user_profile_settings
Inflector::tableize('UserProfileSetting');
```
## Creating Variable Names

#### Static Method `Cake\Utility\Inflector::variable($underscored)`


Variable names are often useful when doing meta-programming tasks that involve
generating code or doing work based on conventions
```php
// applePie
Inflector::variable('apple_pie');
```
<a id="inflection-configuration"></a>
## Inflection Configuration

CakePHP's naming conventions can be really nice - you can name your database
table `big_boxes`, your model `BigBoxes`, your controller
`BigBoxesController`, and everything just works together automatically. The
way CakePHP knows how to tie things together is by *inflecting* the words
between their singular and plural forms.

There are occasions (especially for our non-English speaking friends) where you
may run into situations where CakePHP's inflector (the class that pluralizes,
singularizes, camelCases, and under\_scores) might not work as you'd like. If
CakePHP won't recognize your Foci or Fish, you can tell CakePHP about your
special cases.

### Loading Custom Inflections

#### Static Method `Cake\Utility\Inflector::rules($type, $rules, $reset = false)`


Define new inflection and transliteration rules for Inflector to use.  Often,
this method is used in your **config/bootstrap.php**
```php
Inflector::rules('singular', ['/^(bil)er$/i' => '\1', '/^(inflec|contribu)tors$/i' => '\1ta']);
Inflector::rules('uninflected', ['singulars']);
Inflector::rules('irregular', ['phylum' => 'phyla']); // The key is singular form, value is plural form
```

The supplied rules will be merged into the respective inflection sets defined in
`Cake/Utility/Inflector`, with the added rules taking precedence over the core
rules. You can use `Inflector::reset()` to clear rules and restore the
original Inflector state.
