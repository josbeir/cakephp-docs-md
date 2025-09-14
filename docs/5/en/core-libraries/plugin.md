---
title: Plugin Class
keywords: "compatible implementation,model behaviors,path management,loading files,php class,class loading,model behavior,class location,component model,management class,autoloader,classname,directory location,override,conventions,lib,textile,cakephp,php classes,loaded"
---

# Plugin Class

**Namespace:** `Cake\Core`

### Class `Cake\Core\Plugin`

The Plugin class is responsible for resource location and path management of plugins.

## Locating Plugins

#### Static Method `Cake\Core\Plugin::path(string $plugin)`

Plugins can be located with Plugin. Using `Plugin::path('DebugKit');`
for example, will give you the full path to the DebugKit plugin

```php
$path = Plugin::path('DebugKit');

```

## Check if a Plugin is Loaded

You can check dynamically inside your code if a specific plugin has been loaded

```php
$isLoaded = Plugin::isLoaded('DebugKit');

```

Use `Plugin::loaded()` if you want to get a list of all currently loaded plugins.

## Finding Paths to Namespaces

#### Static Method `Cake\Core\Plugin::classPath(string $plugin)`

Used to get the location of the plugin's class files

```php
$path = App::classPath('DebugKit');

```

## Finding Paths to Resources

#### Static Method `Cake\Core\Plugin::templatePath(string $plugin)`

The method returns the path to the plugins' templates

```php
$path = Plugin::templatePath('DebugKit');

```

The same goes for the config path::

```php
$path = Plugin::configPath('DebugKit');

```