# Content Management Tutorial

This tutorial will walk you through the creation of a simple <abbr title="Content Management System">CMS</abbr> application. To start with, we'll be installing CakePHP,
creating our database, and building simple article management.

Here's what you'll need:

#. A database server. We're going to be using MySQL server in this tutorial.
You'll need to know enough about SQL in order to create a database, and run
SQL snippets from the tutorial. CakePHP will handle building all the queries
your application needs. Since we're using MySQL, also make sure that you have
`pdo_mysql` enabled in PHP.
#. Basic PHP knowledge.

Before starting you should make sure that you're using a supported PHP version:

```bash
php -v

```

You should at least have got installed PHP |minphpversion| (CLI) or higher.
Your webserver's PHP version must also be of |minphpversion| or higher, and
should be the same version your command line interface (CLI) PHP is.

## Getting CakePHP

The easiest way to install CakePHP is to use Composer. Composer is a simple way
of installing CakePHP from your terminal or command line prompt. First, you'll
need to download and install Composer if you haven't done so already. If you
have cURL installed, run the following:

```bash
curl -s https://getcomposer.org/installer | php

```

Or, you can download `composer.phar` from the
[Composer website](https://getcomposer.org/download/).

Then simply type the following line in your terminal from your
installation directory to install the CakePHP application skeleton
in the **cms** directory of the current working directory:

```bash
php composer.phar create-project --prefer-dist cakephp/app:5 cms

```

If you downloaded and ran the [Composer Windows Installer](https://getcomposer.org/Composer-Setup.exe), then type the following line in
your terminal from your installation directory (ie.
C:\\wamp\\www\\dev):

```bash
composer self-update && composer create-project --prefer-dist cakephp/app:5.* cms

```

The advantage to using Composer is that it will automatically complete some
important set up tasks, such as setting the correct file permissions and
creating your **config/app.php** file for you.

There are other ways to install CakePHP. If you cannot or don't want to use
Composer, check out the [/installation` section.

Regardless of how you downloaded and installed CakePHP, once your set up is
completed, your directory setup should look like the following, though other
files may also be present

```
cms/
  bin/
  config/
  plugins/
  resources/
  src/
  templates/
  tests/
  tmp/
  vendor/
  webroot/
  composer.json
  index.php
  README.md

```

Now might be a good time to learn a bit about how CakePHP's directory structure
works: check out the [cakephp-folder-structure](../../intro/cakephp-folder-structure.md) section.

If you get lost during this tutorial, you can see the finished result [on GitHub](https://github.com/cakephp/cms-tutorial).

> [!TIP]
> The `bin/cake` console utility can build most of the classes and data
> tables in this tutorial automatically. However, we recommend following along
> with the manual code examples to understand how the pieces fit together and
> how to add your application logic.
>

## Checking our Installation

We can quickly check that our installation is correct, by checking the default
home page. Before you can do that, you'll need to start the development server:

```bash
cd /path/to/our/app

bin/cake server

```

> [!NOTE]
> For Windows, the command needs to be `bin\cake server` (note the backslash).
>

This will start PHP's built-in webserver on port 8765. Open up
**http://localhost:8765** in your web browser to see the welcome page. All the
bullet points should be green chef hats other than CakePHP being able to connect to
your database. If not, you may need to install additional PHP extensions, or set
directory permissions.

Next, we will build our [Database](database.md).
