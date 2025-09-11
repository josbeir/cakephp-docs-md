---
title: Global Constants and Functions
keywords: "internationalization and localization,global constants,example config,array php,convenience functions,core libraries,component classes,optional number,global functions,string string,core classes,format strings,unread messages,placeholders,useful functions,arrays,parameters,existence,translations"
---

# Constants & Functions

While most of your day-to-day work in CakePHP will be utilizing core classes and
methods, CakePHP features a number of global convenience functions that may come
in handy. Many of these functions are for use with CakePHP classes (loading
model or component classes), but many others make working with arrays or
strings a little easier.

We'll also cover some of the constants available in CakePHP applications. Using
these constants will help make upgrades more smooth, but are also convenient
ways to point to certain files or directories in your CakePHP application.

## Global Functions

Here are CakePHP's globally available functions. Most of them are just
convenience wrappers for other CakePHP functionality, such as debugging and
translating content. By default only namespaced functions are autoloaded,
however you can optionally load global aliases by adding

```php
require CAKE . 'functions.php';
```
To your application's `config/bootstrap.php`. Doing this will load global
aliases for *all* functions listed below.

**Namespace:** `Cake\I18n`


#### Function `\_\_(string $string_id, [$formatArgs])`


This function handles localization in CakePHP applications. The
`$string_id` identifies the ID for a translation. You can supply
additional arguments to replace placeholders in your string
```php
__('You have {0} unread messages', $number);
```

You can also provide a name-indexed array of replacements::

        __('You have {unread} unread messages', ['unread' => $number]);

> [!NOTE]
> Check out the
> [/core-libraries/internationalization-and-localization` section for
> more information.
>
#### Function `__d(string $domain, string $msg, mixed $args = null)`


Allows you to override the current domain for a single message lookup.

Useful when internationalizing a plugin:
`echo __d('plugin_name', 'This is my plugin');`

> [!NOTE]
> Make sure to use the underscored version of the plugin name here as domain.
>
#### Function `__dn(string $domain, string $singular, string $plural, integer $count, mixed $args = null)`


Allows you to override the current domain for a single plural message
lookup. Returns correct plural form of message identified by `$singular`
and `$plural` for count `$count` from domain `$domain`.

#### Function `__dx(string $domain, string $context, string $msg, mixed $args = null)`


Allows you to override the current domain for a single message lookup. It
also allows you to specify a context.

The context is a unique identifier for the translations string that makes it
unique within the same domain.

#### Function `__dxn(string $domain, string $context, string $singular, string $plural, integer $count, mixed $args = null)`


Allows you to override the current domain for a single plural message
lookup. It also allows you to specify a context. Returns correct plural
form of message identified by `$singular` and `$plural` for count
`$count` from domain `$domain`. Some languages have more than one form
    for plural messages dependent on the count.

The context is a unique identifier for the translations string that makes it
unique within the same domain.

#### Function `__n(string $singular, string $plural, integer $count, mixed $args = null)`


Returns correct plural form of message identified by `$singular` and
`$plural` for count `$count`. Some languages have more than one form for
plural messages dependent on the count.

#### Function `__x(string $context, string $msg, mixed $args = null)`


The context is a unique identifier for the translations string that makes it
unique within the same domain.

#### Function `__xn(string $context, string $singular, string $plural, integer $count, mixed $args = null)`


Returns correct plural form of message identified by `$singular` and
`$plural` for count `$count` from domain `$domain`. It also allows you
to specify a context. Some languages have more than one form for plural
messages dependent on the count.

The context is a unique identifier for the translations string that makes it
unique within the same domain.

**Namespace:** `Cake\Collection`


#### Function `collection(mixed $items)`


Convenience wrapper for instantiating a new `Cake\Collection\Collection`
object, wrapping the passed argument. The `$items` parameter takes either
a `Traversable` object or an array.

**Namespace:** `Cake\Core`


#### Function `debug(mixed $var, boolean $showHtml = null, $showFrom = true)`


If the core `$debug` variable is `true`, `$var` is printed out.
If `$showHTML` is `true` or left as `null`, the data is rendered to be
browser-friendly. If `$showFrom` is not set to `false`, the debug output
will start with the line from which it was called. Also see
[development/debugging](/en/development/debugging.md)

#### Function `dd(mixed $var, boolean $showHtml = null)`


It behaves like `debug()`, but execution is also halted.
If the core `$debug` variable is `true`, `$var` is printed.
If `$showHTML` is `true` or left as `null`, the data is rendered to be
browser-friendly. Also see [development/debugging](/en/development/debugging.md)

#### Function `pr(mixed $var)`


Convenience wrapper for `print_r()`, with the addition of
wrapping ``](/en/pre.md)` tags around the output.

#### Function `pj(mixed $var)`


JSON pretty print convenience function, with the addition of
wrapping `<pre>` tags around the output.

It is meant for debugging the JSON representation of objects and arrays.

#### Function `env(string $key, string $default = null)`


Gets an environment variable from available sources. Used as a backup if
`$_SERVER` or `$_ENV` are disabled.

This function also emulates `PHP_SELF` and `DOCUMENT_ROOT` on
unsupporting servers. In fact, it's a good idea to always use `env()`
instead of `$_SERVER` or `getenv()` (especially if you plan to
distribute the code), since it's a full emulation wrapper.

#### Function `h(string $text, boolean $double = true, string $charset = null)`


Convenience wrapper for `htmlspecialchars()`.

#### Function `pluginSplit(string $name, boolean $dotAppend = false, string $plugin = null)`


Splits a dot syntax plugin name into its plugin and class name. If `$name`
does not have a dot, then index 0 will be `null`.

Commonly used like `list($plugin, $name) = pluginSplit('Users.User');`

#### Function `namespaceSplit(string $class)`


Split the namespace from the classname.

Commonly used like `list($namespace, $className) = namespaceSplit('Cake\Core\App');`

## Core Definition Constants

Most of the following constants refer to paths in your application.

.. php:const:: APP

Absolute path to your application directory, including a trailing slash.

.. php:const:: APP_DIR

Equals `app` or the name of your application directory.

.. php:const:: CACHE

Path to the cache files directory. It can be shared between hosts in a
multi-server setup.

.. php:const:: CAKE

Path to the cake directory.

.. php:const:: CAKE_CORE_INCLUDE_PATH

Path to the root lib directory.

.. php:const:: CONFIG

Path to the config directory.

.. php:const:: CORE_PATH

Path to the CakePHP directory with ending directory slash.

.. php:const:: DS

Short for PHP's `DIRECTORY_SEPARATOR`, which is `/` on Linux and `\`
on Windows.

.. php:const:: LOGS

Path to the logs directory.

.. php:const:: RESOURCES

Path to the resources directory.

.. php:const:: ROOT

Path to the root directory.

.. php:const:: TESTS

Path to the tests directory.

.. php:const:: TMP

Path to the temporary files directory.

.. php:const:: WWW_ROOT

Full path to the webroot.

## Timing Definition Constants

.. php:const:: TIME_START

Unix timestamp in microseconds as a float from when the application started.
