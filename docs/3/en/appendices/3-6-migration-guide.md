# 3.6 Migration Guide

CakePHP 3.6 is an API compatible upgrade from 3.5. This page outlines the
changes and improvements made in 3.6.

To upgrade to 3.6.x run the following composer command:

``` bash
php composer.phar require --update-with-dependencies "cakephp/cakephp:3.6.*"
```

## Deprecations

The following is a list of deprecated methods, properties and behaviors. These
features will continue to function until 4.0.0 after which they will be removed.

- `bin/cake orm_cache` is now `bin/cake schema_cache`.
- `Cake\Cache\Engine\ApcEngine` has been renamed to
  `Cake\Cache\Engine\ApcuEngine` to better reflect the extension name.
- `Cake\ORM\Table::association()` is deprecated. Use `getAssociation()`
  instead.
- The `Xcache` cache engine has been deprecated. The Xcache extension is no
  longer actively maintained. If you are using xcache, consider adopting APCu,
  Memcached, or Redis instead.
- Passing a list of arrays to `Router::setRequestInfo()` is now deprecated.
  Pass an instance of `ServerRequest` instead.
- `Cake\Controller\Controller:$name` is protected now. Use
  `Controller::getName()/setName()` to access a controller's name in other
  contexts.
- `Cake\Controller\Controller:$plugin` is protected now. Use
  `Controller::getPlugin()/setPlugin()` to access a controller's plugin in
  other contexts.
- `Cake\Controller\Controller:$autoRender` is protected now. Use
  `enableAutoRender()/disableAutoRender()/isAutoRenderEnabled()` instead.
- The setter mode of `ValidationSet::isPresenceRequired()` and
  `ValidationSet::isEmptyAllowed()` are deprecated. Use `requirePresence()`
  and `allowEmpty()` instead.
- `Cake\Form\Form::validator()` is deprecated. Use `getValidator()` and
  `setValidator()` instead.
- `Cake\ORM\TableRegistry` static API has been deprecated. Use a table locator
  directly instead. A global `Cake\ORM\Locator\TableLocator` instance can be
  accessed via `Cake\ORM\TableRegistry::getTableLocator()` or using a
  `Cake\ORM\Locator\LocatorAwareTrait`.
- `Cake\View\Helper\FormHelper::widgetRegistry()` is deprecated. Use
  `getWidgetLocator()` and `setWidgetLocator()` instead.
- `Cake\ORM\Behavior\TranslateBehavior::locale()` is deprecated. Use
  `getLocale()` and `setLocale()` instead.
- The getter part of `Cake\Datasource\QueryTrait::formatResults()` is deprecated. Use
  `getResultFormatters()` instead.
- The getter part of `Cake\Datasource\QueryTrait::mapReduce()` is deprecated. Use
  `getMapReducers()` instead.
- The getter part of `Cake\ORM\Query::contain()` is deprecated. Use
  `getContain()` instead.
- The getter part of `Cake\Datasource\QueryInterface::repository()`
  is deprecated. Use `getRepository()` instead.
- The getter part of `Cake\Database\Type::map()` is deprecated. Use
  `getMap()` instead.
- `Cake\Database\Type::map()` to set complete types map is deprecated. Use
  `setMap()` instead.
- Passing `$className` as object to `Cake\Database\Type::map()` is deprecated. Use
  `set()` instead.
- `Cake\Routing\Dispatcher` is deprecated. You should upgrade to [new
  HTTP stack](../development/application#adding-http-stack).

Several classes were *renamed*. Their old names will continue to work until 4.0,
but will emit deprecation warnings:

- `Cake\Network\Exception\BadRequestException` has been renamed to
  `Cake\Http\Exception\BadRequestException`.
- `Cake\Network\Exception\ConflictException` has been renamed to
  `Cake\Http\Exception\ConflictException`.
- `Cake\Network\Exception\ForbiddenException` has been renamed to
  `Cake\Http\Exception\ForbiddenException`.
- `Cake\Network\Exception\GoneException` has been renamed to
  `Cake\Http\Exception\GoneException`.
- `Cake\Network\Exception\HttpException` has been renamed to
  `Cake\Http\Exception\HttpException`.
- `Cake\Network\Exception\InternalErrorException` has been renamed to
  `Cake\Http\Exception\InternalErrorException`.
- `Cake\Network\Exception\InvalidCsrfTokenException` has been renamed to
  `Cake\Http\Exception\InvalidCsrfTokenException`.
- `Cake\Network\Exception\MethodNotAllowedException` has been renamed to
  `Cake\Http\Exception\MethodNotAllowedException`.
- `Cake\Network\Exception\NotAcceptableException` has been renamed to
  `Cake\Http\Exception\NotAcceptableException`.
- `Cake\Network\Exception\NotFoundException` has been renamed to
  `Cake\Http\Exception\NotFoundException`.
- `Cake\Network\Exception\NotImplementedException` has been renamed to
  `Cake\Http\Exception\NotImplementedException`.
- `Cake\Network\Exception\ServiceUnavailableException` has been renamed to
  `Cake\Http\Exception\ServiceUnavailableException`.
- `Cake\Network\Exception\UnauthorizedException` has been renamed to
  `Cake\Http\Exception\UnauthorizedException`.
- `Cake\Network\Exception\UnavailableForLegalReasonsException` has been
  renamed to `Cake\Http\Exception\UnavailableForLegalReasonsException`.
- `Cake\Network\Session` has been renamed to `Cake\Http\Session`.
- `Cake\Network\Session\DatabaseSession` has been renamed to
  `Cake\Http\Session\DatabaseSession`.
- `Cake\Network\Session\CacheSession` has been renamed to
  `Cake\Http\Session\CacheSession`.
- `Cake\Network\CorsBuilder` has been renamed to `Cake\Http\CorsBuilder`.
- `Cake\View\Widget\WidgetRegistry` has been renamed to
  `Cake\View\Widget\WidgetLocator`.

## Disabling Deprecation Warnings

Deprecation warnings are intended to help you prepare for future versions of
CakePHP, but updating your code will likely be an incremental process. You can
disable deprecation warnings while you work towards adopting the new methods. In
your **config/app.php** you can set `Error.errorLevel`:

    // in config/app.php
    'Error' => [
        'errorLevel' => E_ALL ^ E_USER_DEPRECATED
    ]

Once you have no remaining deprecation warnings you can set your error level to
`E_ALL` to help prevent future use of deprecated methods.

## Behavior Changes

The following changes are API compatible, but they represent minor variances in
behavior that may affect your application:

- `Cake\Utility\Security::randomBytes()` will now throw exceptions when
  a secure source of entropy cannot be found in PHP5.
- Tokens generated by `SecurityComponent` now include the user's session id,
  to prevent token reuse between users/sessions. This changes the value of
  security tokens and will cause forms created in earlier versions of CakePHP to
  fail validation in 3.6.
- `Cake\Database\Query::page()` now raises exceptions when page values are
  \< 1.
- Pagination now allows sorting on multiple fields across all pages. Previously
  only the first page could be sorted by more than one column. Furthermore, sort
  conditions defined in the query string are *prepended* to the default order
  parameters now instead of fully replacing the default ordering.
- Shell classes will now throw exceptions when task classes cannot be found.
  Previously invalid tasks would be silently ignored.
- CakePHP internals now chain exceptions where possible, allowing root causes of
  errors to be exposed.
- MySQL connections now default to `utf8mb4` (4 byte unicode) as their
  encoding. If you want to continue using the old default of `utf8`, set
  your application's datasource' encoding in `config/app.php` to 'utf8'.
- APCu and Wincache engines no longer set separate `_expires` keys for data
  with a TTL. Instead they rely on the native TTL features of the cache engine.
  By default all entries created with APCu for a single request/CLI script will
  have the same expiration time. You can use `apc.use_request_time` to change
  this behavior.
- `Router::parseNamedParams()` no longer mutates the request object in-place.
  Instead you will need to use the return value of this method to access the
  modified request.
- `Hash::extract()` no longer matches integer `0` when using attribute
  matchers for string values.
- The console environment no longer automatically loads plugins specified by the
  `--plugin` option. Instead you must ensure that your application
  bootstrapping loads all the required plugins.
- `Cake\Http\CorsBuilder::build()` no longer mutates the response in place.
  You must now use the updated response that `build()` returns.

## Cache

- Fallback engines can be disabled by setting `fallback = false` for any cache
  configuration.

## Collection

- The `appendItem()`, `prepend()`, `prependItems()` methods were added to
  `CollectionTrait`.

## Configure

- `consumeOrFail()` has been added to complete the API for reading with the expectation of data being present.

## Console

A new way to build CLI tools has been added. Shells & Tasks have several
shortcomings that are hard to correct without breaking compatibility.
`Cake\Console\Command` will replace `Shell` long term as the recommended way
to build console applications. See the [Console Commands](../console-and-shells/commands)
section for more information.

- `ConsoleOptionParse::enableSubcommandSort()` was added. This method allows
  you to disable the automatic sorting of subcommands in generated help text.

## Core

- Plugins can now define [Plugin Objects](../plugins#plugin-objects). These classes are optional and
  allow a plugin to add middleware and console commands to the host application.
  They are a class based way to configure and define a plugin.
- `getTypeName()` was added to assist in getting correct class/type name when
  building more descriptive error messages.

## Database

- `Query::identifier()` was added. This method makes it easier to create
  identifier expressions for use in complex queries.
- Database connections will automatically be re-established when connections
  timeout during long running scripts.
- `Cake\Database\Type\BatchCastingInterface` was added. This interface allows
  type classes to optimize casting operations by operating on all columns in
  bulk. This interface can be used in custom types now, and will be used in core
  types in 4.x.
- `Cake\Database\Type\DateTimeType::setTimezone()` was added. This method lets
  you set the timezone that datetime objects should be converted to before
  inserting into the database. It does not change datetime data being read from
  the database.
- `Cake\Database\Statement\StatementDecorator::fetchAssoc()` was added.
- `Cake\Database\Statement\BufferedStatement::fetchAssoc()` was added.
- `Cake\Database\Statement\StatementDecorator::FETCH_TYPE_NUM` was added.
- `Cake\Database\Statement\StatementDecorator::FETCH_TYPE_ASSOC` was added.
- `Cake\Database\Statement\StatementDecorator::FETCH_TYPE_OBJ` was added.

## Datasource

- A new abstract type was added for `binaryuuid`. In MySQL and SQLite,
  `BINARY(16)` will be used as the column type. While in SQLServer, and
  Postgres will use their native UUID types.

## Event

- `Cake\Event\EventInterface` was added to give allow better typehinting in
  the future.

## Form

- Form classes now support the `_validatorClass` property which works the same
  as it does on `ORM\Table`.

## Http

- `Response::withAddedLink()` was added to make creating `Link` headers
  simpler.
- `BodyParserMiddleware` was added.

## I18n

- The two leftover FormHelper i18n translation strings `'Edit %s'` and `'New %s'` are now `'Edit {0}'` and `'New {0}'`. If you are using translating
  messages from CakePHP, please make sure you adjust those translations in your
  PO file.

## Mailer

- The various email address setter methods on `Email` now accept `[]`
  allowing individual properties to be reset.

## ORM

- `EntityTrait::isEmpty()` and `EntityTrait::hasValue()` were added.
- `Table::getAssociation()` can now read deeply nested associations using
  `.` separated paths. e.g `Users.Comments`. This method will raise
  exceptions when getting an unknown association now.
- `Table::addBehaviors()` was added making it simpler to add multiple
  behaviors at once.
- `Table::getBehavior()` was added.
- `CounterCacheBehavior` callback functions can now return `false` to skip
  updating the counter value.
- `TimestampBehavior` now uses the correct mutable/immutable class type when
  creating time objects instead of always using a mutable time instance.
- `Query::selectAllExcept()` was added.
- `Query::whereInList()`/`Query::whereNotInList()` have been added as standalone wrappers around `IN`/`NOT IN` conditions.

## Routing

- `Cake\Routing\Route\EntityRoute` was added. This route class makes building
  routes that need data from entities easier. See the [Entity Routing](../development/routing#entity-routing)
  section for more information.
- Routing variables can now use `{var}` style placeholders. This style allows
  mid-word variables to be defined. You cannot combine `{var}` placeholders
  with `:var` style placeholders.
- `Router::routeExists()` was added. This method lets you check if a route
  array can be resolved into a valid route.
- Route connection can now use compact 'string targets' e.g
  `Bookmarks::index`. See [Routes Configuration](../development/routing#routes-configuration) for more information.
- `RoutingMiddleware` can now cache the route collection created by your
  routes. Caching routes greatly improves application startup times.
  It now also requires to be instantiated and the current object (`$this`) passed for plugin routing.

## Shell

- The `cake assets copy` command now features an `--overwrite` option for
  overwriting plugin assets if they already exist in the application webroot.

## Utility

- `Security::randomString()` was added.

## Validation

- `Validation::compareFields()` was added as a more flexible version of
  `Validation::compareWith()`.
- `Validator::notSameAs()` was added to make it
  easier to check if a field is not the same as another field.
- New field comparison methods were added. `equalToField()`,
  `notEqualToField()`, `greaterThanField()`,
  `greaterThanOrEqualToField()`, `lessThanField()`,
  `lessThanOrEqualToField()` were added.
- Validator rules will now use the rule alias as the rule method when the
  `rule` key is not defined.
- `Validator::addNested()` and `addNestedMany()` now support the `when`
  and `message` parameters like other validator methods.

## View

- `UrlHelper::script()`, `css()`, and `image()` methods now support
  a `timestamp` option which allows you to set the `Asset.timestamp`
  setting for a single method call.
- Cell classes now have an `initialize()` hook method.
- `PaginatorHelper` will now reset the page to 1 each time sort directions are
  changed.
