---
title: CakePHP Application
keywords: "http, middleware, psr-7, events, plugins, application, baseapplication"
---

# Application

The `Application` is the heart of your application. It controls
how your application is configured, and what plugins, middleware, console
commands and routes are included.

You can find your `Application` class at **src/Application.php**. By default
it will be pretty slim and only define a few default
[/controllers/middleware`. Applications can define the following hook
methods:

- `bootstrap` Used to load [plugins](configuration files](configuration.md), define constants and other global functions.
  By default this will include **config/bootstrap.php**. This is the ideal place
  to load [/plugins.md) and global [events.md).
*](event listeners](../core-libraries/events.md).
*.md)`routes` Used to load [routes](routing.md). By default this
  will include **config/routes.php**.
- `middleware` Used to add [middleware](../controllers/middleware.md) to your application.
- `console` Used to add [console commands](../console-commands.md) to your
  application. By default this will automatically discover shells & commands in
  your application and all plugins.

## Bootstrapping your Application

If you have any additional configuration needs, you should add them to your
application's **config/bootstrap.php** file. This file is included before each
request, and CLI command.

This file is ideal for a number of common bootstrapping tasks:

- Defining convenience functions.
- Declaring constants.
- Defining cache configuration.
- Defining logging configuration.
- Loading custom inflections.
- Loading configuration files.

It might be tempting to place formatting functions there in order to use them in
your controllers. As you'll see in the [controllers](../controllers.md) and [views](../views.md)
sections there are better ways you add custom logic to your application.
<!-- anchor: application-bootstrap -->
### Application::bootstrap()

In addition to the **config/bootstrap.php** file which should be used to
configure low-level concerns of your application, you can also use the
`Application::bootstrap()`` hook method to load/initialize plugins, and attach
global event listeners

```php
// in src/Application.php
namespace App;

use Cake\Http\BaseApplication;

class Application extends BaseApplication
{
    public function bootstrap()
    {
        // Call the parent to `require_once` config/bootstrap.php
        parent::bootstrap();

        // Load MyPlugin
        $this->addPlugin('MyPlugin');
    }
}

```

Loading plugins and events in `Application::bootstrap()` makes
[integration-testing](testing.md#integration-testing) easier as events and routes will be re-processed on
each test method.
