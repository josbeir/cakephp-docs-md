# CakePHP Folder Structure

After you've downloaded the CakePHP application skeleton, there are a few top
level folders you should see:

- The *bin* folder holds the Cake console executables.

- The *config* folder holds the [/development/configuration](development/configuration.md) files
  CakePHP uses. Database connection details, bootstrapping, core configuration files
  and more should be stored here.

- The *plugins* folder is where the [/plugins](plugins.md) your application uses are stored.

- The *logs* folder normally contains your log files, depending on your log
  configuration.

- The *src* folder will be where your application’s source files will be placed.

- The *tests* folder will be where you put the test cases for your application.

- The *tmp* folder is where CakePHP stores temporary data. The actual data it
  stores depends on how you have CakePHP configured, but this folder
  is usually used to store translation messages, model descriptions and sometimes
  session information.

- The *vendor* folder is where CakePHP and other application dependencies will
  be installed by [Composer](https://getcomposer.org). Editing these files is not
  advised, as Composer will overwrite your changes next time you update.

- The *webroot* directory is the public document root of your application. It
  contains all the files you want to be publicly reachable.

  Make sure that the *tmp* and *logs* folders exist and are writable,
  otherwise the performance of your application will be severely
  impacted. In debug mode, CakePHP will warn you, if these directories are not
  writable.

## The src Folder

CakePHP's *src* folder is where you will do most of your application
development. Let's look a little closer at the folders inside
*src*.

Command  
Contains your application's console commands. See
[/console-and-shells/commands](console-and-shells/commands.md) to learn more.

Console  
Contains the installation script executed by Composer.

Controller  
Contains your application's [/controllers](controllers.md) and their components.

Locale  
Stores language files for internationalization.

Middleware  
Stores any [/controllers/middleware](controllers/middleware.md) for your application.

Model  
Contains your application's tables, entities and behaviors.

Shell  
Contains shell tasks for your application.
For more information see [/console-and-shells](console-and-shells.md).

Template  
Presentational files are placed here: elements, error pages,
layouts, and view template files.

View  
Presentational classes are placed here: views, cells, helpers.

> [!NOTE]
> The folders `Command` and `Locale` are not there by default.
> You can add them when you need them.
