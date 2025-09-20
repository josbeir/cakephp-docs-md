# Routing

Routing is a feature that maps URLs to controller actions. It was
added to CakePHP to make pretty URLs more configurable and
flexible. Using Apache's mod_rewrite is not required for using
routes, but it will make your address bar look much more tidy.

Routing in CakePHP also encompasses the idea of reverse routing,
where an array of parameters can be reversed into a string URL.
By using reverse routing, you can easily re-factor your application's
URL structure without having to update all your code.

<div class="index">

routes.php

</div>

<a id="routes-configuration"></a>

## Routes Configuration

Routes in an application are configured in `app/Config/routes.php`.
This file is included by the `Dispatcher` when handling routes
and allows you to define application specific routes you want used. Routes
declared in this file are processed top to bottom when incoming requests
are matched. This means that the order you place routes can affect how
routes are parsed. It's generally a good idea to place most frequently
visited routes at the top of the routes file if possible. This will
save having to check a number of routes that won't match on each request.

Routes are parsed and matched, in the order they are connected in.
If you define two similar routes, the first defined route will
have higher priority over the one defined latter. After connecting routes you
can manipulate the order of routes using `Router::promote()`.

CakePHP also comes with a few default routes to get you started. These
can be disabled later on once you are sure you don't need them.
See [Disabling Default Routes](#disabling-default-routes) on how to disable the default routing.

## Default Routing

Before you learn about configuring your own routes, you should know
that CakePHP comes configured with a default set of routes.
CakePHP's default routing will get you pretty far in any
application. You can access an action directly via the URL by
putting its name in the request. You can also pass parameters to
your controller actions using the URL. :

``` text
// URL pattern default routes:
http://example.com/controller/action/param1/param2/param3
```

The URL /posts/view maps to the view() action of the
PostsController, and /products/view_clearance maps to the
view_clearance() action of the ProductsController. If no action is
specified in the URL, the index() method is assumed.

The default routing setup also allows you to pass parameters to
your actions using the URL. A request for /posts/view/25 would be
equivalent to calling view(25) on the PostsController, for
example. The default routing also provides routes for plugins,
and prefix routes should you choose to use those features.

The built-in routes live in `Cake/Config/routes.php`. You can
disable the default routing by removing them from your application's
`routes.php` file.

<div class="index">

:controller, :action, :plugin

</div>

<a id="connecting-routes"></a>

## Connecting Routes

Defining your own routes allows you to define how your application
will respond to a given URL. Define your own routes in the
`app/Config/routes.php` file using the `Router::connect()`
method.

The `connect()` method takes up to three parameters: the URL you
wish to match, the default values for your route elements, and
regular expression rules to help the router match elements in the
URL.

The basic format for a route definition is:

``` php
Router::connect(
    'URL',
    array('default' => 'defaultValue'),
    array('option' => 'matchingRegex')
);
```

The first parameter is used to tell the router what sort of URL
you're trying to control. The URL is a normal slash delimited
string, but can also contain a wildcard (\*) or [Route Elements](#route-elements).
Using a wildcard tells the router that you are willing to accept
any additional arguments supplied. Routes without a \* only match
the exact template pattern supplied.

Once you've specified a URL, you use the last two parameters of
`connect()` to tell CakePHP what to do with a request once it has
been matched. The second parameter is an associative array. The
keys of the array should be named after the route elements in the
URL, or the default elements: `:controller`, `:action`, and `:plugin`.
The values in the array are the default values for those keys.
Let's look at some basic examples before we start using the third
parameter of connect():

``` php
Router::connect(
    '/pages/*',
    array('controller' => 'pages', 'action' => 'display')
);
```

This route is found in the routes.php file distributed with CakePHP.
This route matches any URL starting with `/pages/` and
hands it to the `display()` action of the `PagesController();`
The request /pages/products would be mapped to
`PagesController->display('products')`.

In addition to the greedy star `/*` there is also the `/**` trailing star
syntax. Using a trailing double star, will capture the remainder of a URL as a
single passed argument. This is useful when you want to use an argument that
included a `/` in it:

``` php
Router::connect(
    '/pages/**',
    array('controller' => 'pages', 'action' => 'show')
);
```

The incoming URL of `/pages/the-example-/-and-proof` would result in a single
passed argument of `the-example-/-and-proof`.

::: info Added in version 2.1
The trailing double star was added in 2.1.
:::

You can use the second parameter of `Router::connect()`
to provide any routing parameters that are composed of the default values
of the route:

``` php
Router::connect(
    '/government',
    array('controller' => 'pages', 'action' => 'display', 5)
);
```

This example shows how you can use the second parameter of
`connect()` to define default parameters. If you built a site
that features products for different categories of customers, you
might consider creating a route. This allows you link to
`/government` rather than `/pages/display/5`.

> [!NOTE]
> Although you can connect alternate routes, the default routes
> will continue to work. In this setting, you can access a single piece of
> content from 2 different URLs. See [Disabling Default Routes](#disabling-default-routes) to
> disable default routes, and only provide the URLs you define.

Another common use for the Router is to define an "alias" for a
controller. Let's say that instead of accessing our regular URL at
`/users/some_action/5`, we'd like to be able to access it by
`/cooks/some_action/5`. The following route easily takes care of
that:

``` php
Router::connect(
    '/cooks/:action/*', array('controller' => 'users')
);
```

This is telling the Router that any url beginning with `/cooks/`
should be sent to the users controller. The action called will
depend on the value of the `:action` parameter. By using
[Route Elements](#route-elements), you can create variable routes, that accept
user input or variables. The above route also uses the greedy star.
The greedy star indicates to `Router` that this route
should accept any additional positional arguments given. These
arguments will be made available in the [Passed Arguments](#passed-arguments)
array.

When generating URLs, routes are used too. Using
`array('controller' => 'users', 'action' => 'some_action', 5)` as
a url will output /cooks/some_action/5 if the above route is the
first match found.

By default all named and passed arguments are extracted from URLs matching
greedy templates. However, you can configure how and which named arguments are
parsed using `Router::connectNamed()` if you need to.

<a id="route-elements"></a>

### Route Elements

You can specify your own route elements and doing so gives you the
power to define places in the URL where parameters for controller
actions should lie. When a request is made, the values for these
route elements are found in `$this->request->params` on the controller.
This is different than how named parameters are handled, so note the
difference: named parameters (/controller/action/name:value) are
found in `$this->request->params['named']`, whereas custom route
element data is found in `$this->request->params`. When you define
a custom route element, you can optionally specify a regular
expression - this tells CakePHP how to know if the URL is correctly formed or not.
If you choose to not provide a regular expression, any non `/` will be
treated as part of the parameter:

``` php
Router::connect(
    '/:controller/:id',
    array('action' => 'view'),
    array('id' => '[0-9]+')
);
```

This simple example illustrates how to create a quick way to view
models from any controller by crafting a URL that looks like
`/controllername/:id`. The URL provided to connect() specifies two
route elements: `:controller` and `:id`. The `:controller` element
is a CakePHP default route element, so the router knows how to match and
identify controller names in URLs. The `:id` element is a custom
route element, and must be further clarified by specifying a
matching regular expression in the third parameter of connect().

> [!NOTE]
> Patterns used for route elements must not contain any capturing
> groups. If they do, Router will not function correctly.

Once this route has been defined, requesting `/apples/5` is the same
as requesting `/apples/view/5`. Both would call the view() method of
the ApplesController. Inside the view() method, you would need to
access the passed ID at `$this->request->params['id']`.

If you have a single controller in your application and you do not want
the controller name to appear in the URL, you can map all URLs to actions
in your controller. For example, to map all URLs to actions of the
`home` controller, e.g have URLs like `/demo` instead of
`/home/demo`, you can do the following:

``` php
Router::connect('/:action', array('controller' => 'home'));
```

If you would like to provide a case insensitive URL, you can use regular
expression inline modifiers:

``` php
Router::connect(
    '/:userShortcut',
    array('controller' => 'teachers', 'action' => 'profile', 1),
    array('userShortcut' => '(?i:principal)')
);
```

One more example, and you'll be a routing pro:

``` php
Router::connect(
    '/:controller/:year/:month/:day',
    array('action' => 'index'),
    array(
        'year' => '[12][0-9]{3}',
        'month' => '0[1-9]|1[012]',
        'day' => '0[1-9]|[12][0-9]|3[01]'
    )
);
```

This is rather involved, but shows how powerful routes can really
become. The URL supplied has four route elements. The first is
familiar to us: it's a default route element that tells CakePHP to
expect a controller name.

Next, we specify some default values. Regardless of the controller,
we want the index() action to be called.

Finally, we specify some regular expressions that will match years,
months and days in numerical form. Note that parenthesis (grouping)
are not supported in the regular expressions. You can still specify
alternates, as above, but not grouped with parenthesis.

Once defined, this route will match `/articles/2007/02/01`,
`/posts/2004/11/16`, handing the requests to
the index() actions of their respective controllers, with the date
parameters in `$this->request->params`.

There are several route elements that have special meaning in
CakePHP, and should not be used unless you want the special meaning

- `controller` Used to name the controller for a route.
- `action` Used to name the controller action for a route.
- `plugin` Used to name the plugin a controller is located in.
- `prefix` Used for [Prefix Routing](#prefix-routing)
- `ext` Used for [File Extensions](#file-extensions) routing.

### Passing Parameters to Action

When connecting routes using [Route Elements](#route-elements) you may want
to have routed elements be passed arguments instead. By using the 3rd
argument of `Router::connect()` you can define which route
elements should also be made available as passed arguments:

``` php
// SomeController.php
public function view($articleId = null, $slug = null) {
    // some code here...
}

// routes.php
Router::connect(
    '/blog/:id-:slug', // E.g. /blog/3-CakePHP_Rocks
    array('controller' => 'blog', 'action' => 'view'),
    array(
        // order matters since this will simply map ":id" to
        // $articleId in your action
        'pass' => array('id', 'slug'),
        'id' => '[0-9]+'
    )
);
```

And now, thanks to the reverse routing capabilities, you can pass
in the url array like below and CakePHP will know how to form the URL
as defined in the routes:

``` php
// view.ctp
// this will return a link to /blog/3-CakePHP_Rocks
echo $this->Html->link('CakePHP Rocks', array(
    'controller' => 'blog',
    'action' => 'view',
    'id' => 3,
    'slug' => 'CakePHP_Rocks'
));
```

### Per-Route Named Parameters

While you can control named parameters on a global scale using
`Router::connectNamed()` you can also control named parameter
behavior at the route level using the 3rd argument of `Router::connect()`:

``` php
Router::connect(
    '/:controller/:action/*',
    array(),
    array(
        'named' => array(
            'wibble',
            'fish' => array('action' => 'index'),
            'fizz' => array('controller' => array('comments', 'other')),
            'buzz' => 'val-[\d]+'
        )
    )
);
```

The above route definition uses the `named` key to define how several named
parameters should be treated. Lets go through each of the various rules
one-by-one:

- 'wibble' has no additional information. This means it will always parse if
  found in a URL matching this route.
- 'fish' has an array of conditions, containing the 'action' key. This means
  that fish will only be parsed as a named parameter if the action is also index.
- 'fizz' also has an array of conditions. However, it contains two controllers,
  this means that 'fizz' will only be parsed if the controller matches one of the
  names in the array.
- 'buzz' has a string condition. String conditions are treated as
  regular expression fragments. Only values for buzz matching the pattern will
  be parsed.

If a named parameter is used and it does not match the provided criteria, it will
be treated as a passed argument instead of a named parameter.

<div class="index">

admin routing, prefix routing

</div>

<a id="prefix-routing"></a>

### Prefix Routing

Many applications require an administration section where
privileged users can make changes. This is often done through a
special URL such as `/admin/users/edit/5`. In CakePHP, prefix routing
can be enabled from within the core configuration file by setting
the prefixes with Routing.prefixes. Note that prefixes, although
related to the router, are to be configured in
`app/Config/core.php`:

``` php
Configure::write('Routing.prefixes', array('admin'));
```

In your controller, any action with an `admin_` prefix will be
called. Using our users example, accessing the URL
`/admin/users/edit/5` would call the method `admin_edit` of our
`UsersController` passing 5 as the first parameter. The view file
used would be `app/View/Users/admin_edit.ctp`

You can map the URL /admin to your `admin_index` action of pages
controller using following route:

``` php
Router::connect(
    '/admin',
    array('controller' => 'pages', 'action' => 'index', 'admin' => true)
);
```

You can configure the Router to use multiple prefixes too. By
adding additional values to `Routing.prefixes`. If you set:

``` php
Configure::write('Routing.prefixes', array('admin', 'manager'));
```

CakePHP will automatically generate routes for both the admin and
manager prefixes. Each configured prefix will have the following
routes generated for it:

``` php
Router::connect(
    "/{$prefix}/:plugin/:controller",
    array('action' => 'index', 'prefix' => $prefix, $prefix => true)
);
Router::connect(
    "/{$prefix}/:plugin/:controller/:action/*",
    array('prefix' => $prefix, $prefix => true)
);
Router::connect(
    "/{$prefix}/:controller",
    array('action' => 'index', 'prefix' => $prefix, $prefix => true)
);
Router::connect(
    "/{$prefix}/:controller/:action/*",
    array('prefix' => $prefix, $prefix => true)
);
```

Much like admin routing all prefix actions should be prefixed with
the prefix name. So `/manager/posts/add` would map to
`PostsController::manager_add()`.

Additionally, the current prefix will be available from the controller methods through `$this->request->prefix`

When using prefix routes it's important to remember, using the HTML
helper to build your links will help maintain the prefix calls.
Here's how to build this link using the HTML helper:

``` php
// Go into a prefixed route.
echo $this->Html->link(
    'Manage posts',
    array('manager' => true, 'controller' => 'posts', 'action' => 'add')
);

// leave a prefix
echo $this->Html->link(
    'View Post',
    array('manager' => false, 'controller' => 'posts', 'action' => 'view', 5)
);
```

<div class="index">

plugin routing

</div>

### Plugin Routing

Plugin routing uses the **plugin** key. You can create links that
point to a plugin, but adding the plugin key to your URL array:

``` php
echo $this->Html->link(
    'New todo',
    array('plugin' => 'todo', 'controller' => 'todo_items', 'action' => 'create')
);
```

Conversely if the active request is a plugin request and you want
to create a link that has no plugin you can do the following:

``` php
echo $this->Html->link(
    'New todo',
    array('plugin' => null, 'controller' => 'users', 'action' => 'profile')
);
```

By setting `plugin => null` you tell the Router that you want to
create a link that is not part of a plugin.

<div class="index">

file extensions

</div>

<a id="file-extensions"></a>

### File Extensions

To handle different file extensions with your routes, you need one
extra line in your routes config file:

``` php
Router::parseExtensions('html', 'rss');
```

This will tell the router to remove any matching file extensions,
and then parse what remains.

If you want to create a URL such as /page/title-of-page.html you
would create your route as illustrated below:

``` php
Router::connect(
    '/page/:title',
    array('controller' => 'pages', 'action' => 'view'),
    array(
        'pass' => array('title')
    )
);
```

Then to create links which map back to the routes simply use:

``` php
$this->Html->link(
    'Link title',
    array(
        'controller' => 'pages',
        'action' => 'view',
        'title' => 'super-article',
        'ext' => 'html'
    )
);
```

File extensions are used by `RequestHandlerComponent` to do automatic
view switching based on content types. See the RequestHandlerComponent for
more information.

<a id="route-conditions"></a>

### Using Additional Conditions When Matching Routes

When creating routes you might want to restrict certain URL's based on specific
request/environment settings. A good example of this is [Rest](../rest)
routing. You can specify additional conditions in the `$defaults` argument for
`Router::connect()`. By default CakePHP exposes 3 environment
conditions, but you can add more using [Custom Route Classes](#custom-route-classes). The built-in
options are:

- `[type]` Only match requests for specific content types.
- `[method]` Only match requests with specific HTTP verbs.
- `[server]` Only match when \$\_SERVER\['SERVER_NAME'\] matches the given value.

We'll provide a simple example here of how you can use the `[method]`
option to create a custom RESTful route:

``` php
Router::connect(
    "/:controller/:id",
    array("action" => "edit", "[method]" => "PUT"),
    array("id" => "[0-9]+")
);
```

The above route will only match for `PUT` requests. Using these conditions,
you can create custom REST routing, or other request data dependent information.

<div class="index">

passed arguments

</div>

<a id="passed-arguments"></a>

## Passed Arguments

Passed arguments are additional arguments or path segments that are
used when making a request. They are often used to pass parameters
to your controller methods. :

    http://localhost/calendars/view/recent/mark

In the above example, both `recent` and `mark` are passed
arguments to `CalendarsController::view()`. Passed arguments are
given to your controllers in three ways. First as arguments to the
action method called, and secondly they are available in
`$this->request->params['pass']` as a numerically indexed array. Lastly
there is `$this->passedArgs` available in the same way as the
second one. When using custom routes you can force particular
parameters to go into the passed arguments as well.

If you were to visit the previously mentioned URL, and you
had a controller action that looked like:

``` text
CalendarsController extends AppController {
    public function view($arg1, $arg2) {
        debug(func_get_args());
    }
}
```

You would get the following output:

``` text
Array
(
    [0] => recent
    [1] => mark
)
```

This same data is also available at `$this->request->params['pass']`
and `$this->passedArgs` in your controllers, views, and helpers.
The values in the pass array are numerically indexed based on the
order they appear in the called URL:

``` php
debug($this->request->params['pass']);
debug($this->passedArgs);
```

Either of the above would output:

``` text
Array
(
    [0] => recent
    [1] => mark
)
```

> [!NOTE]
> \$this-\>passedArgs may also contain named parameters as a named
> array mixed with Passed arguments.

When generating URLs, using a `routing array` you add passed
arguments as values without string keys in the array:

``` text
array('controller' => 'posts', 'action' => 'view', 5)
```

Since `5` has a numeric key, it is treated as a passed argument.

<div class="index">

named parameters

</div>

<a id="named-parameters"></a>

## Named Parameters

You can name parameters and send their values using the URL. A
request for `/posts/view/title:first/category:general` would result
in a call to the view() action of the PostsController. In that
action, you'd find the values of the title and category parameters
inside `$this->params['named']`. They are also available inside
`$this->passedArgs`. In both cases you can access named parameters using their
name as an index. If named parameters are omitted, they will not be set.

> [!NOTE]
> What is parsed as a named parameter is controlled by
> `Router::connectNamed()`. If your named parameters are not
> reverse routing, or parsing correctly, you will need to inform
> `Router` about them.

Some summarizing examples for default routes might prove helpful:

``` php
URL to controller action mapping using default routes:

URL: /monkeys/jump
Mapping: MonkeysController->jump();

URL: /products
Mapping: ProductsController->index();

URL: /tasks/view/45
Mapping: TasksController->view(45);

URL: /donations/view/recent/2001
Mapping: DonationsController->view('recent', '2001');

URL: /contents/view/chapter:models/section:associations
Mapping: ContentsController->view();
$this->passedArgs['chapter'] = 'models';
$this->passedArgs['section'] = 'associations';
$this->params['named']['chapter'] = 'models';
$this->params['named']['section'] = 'associations';
```

When making custom routes, a common pitfall is that using named
parameters will break your custom routes. In order to solve this
you should inform the Router about which parameters are intended to
be named parameters. Without this knowledge the Router is unable to
determine whether named parameters are intended to actually be
named parameters or routed parameters, and defaults to assuming you
intended them to be routed parameters. To connect named parameters
in the router use `Router::connectNamed()`:

``` php
Router::connectNamed(array('chapter', 'section'));
```

Will ensure that your chapter and section parameters reverse route
correctly.

When generating URLs, using a `routing array` you add named
parameters as values with string keys matching the name:

``` text
array('controller' => 'posts', 'action' => 'view', 'chapter' => 'association')
```

Since 'chapter' doesn't match any defined route elements, it's treated
as a named parameter.

> [!NOTE]
> Both named parameters and route elements share the same key-space.
> It's best to avoid re-using a key for both a route element and a named
> parameter.

Named parameters also support using arrays to generate and parse
URLs. The syntax works very similar to the array syntax used
for GET parameters. When generating URLs you can use the following
syntax:

``` php
$url = Router::url(array(
    'controller' => 'posts',
    'action' => 'index',
    'filter' => array(
        'published' => 1,
        'frontpage' => 1
    )
));
```

The above would generate the URL `/posts/index/filter[published]:1/filter[frontpage]:1`.
The parameters are then parsed and stored in your controller's passedArgs variable
as an array, just as you sent them to `Router::url`:

``` php
$this->passedArgs['filter'] = array(
    'published' => 1,
    'frontpage' => 1
);
```

Arrays can be deeply nested as well, allowing you even more flexibility in
passing arguments:

``` php
$url = Router::url(array(
    'controller' => 'posts',
    'action' => 'search',
    'models' => array(
        'post' => array(
            'order' => 'asc',
            'filter' => array(
                'published' => 1
            )
        ),
        'comment' => array(
            'order' => 'desc',
            'filter' => array(
                'spam' => 0
            )
        ),
    ),
    'users' => array(1, 2, 3)
));
```

You would end up with a pretty long url like this (wrapped for easy reading):

    posts/search
      /models[post][order]:asc/models[post][filter][published]:1
      /models[comment][order]:desc/models[comment][filter][spam]:0
      /users[]:1/users[]:2/users[]:3

And the resulting array that would be passed to the controller would match that
which you passed to the router:

``` php
$this->passedArgs['models'] = array(
    'post' => array(
        'order' => 'asc',
        'filter' => array(
            'published' => 1
        )
    ),
    'comment' => array(
        'order' => 'desc',
        'filter' => array(
            'spam' => 0
        )
    ),
);
```

<a id="controlling-named-parameters"></a>

### Controlling Named Parameters

You can control named parameter configuration at the per-route-level
or control them globally. Global control is done through `Router::connectNamed()`
The following gives some examples of how you can control named parameter parsing
with connectNamed().

Do not parse any named parameters:

``` php
Router::connectNamed(false);
```

Parse only default parameters used for CakePHP's pagination:

``` php
Router::connectNamed(false, array('default' => true));
```

Parse only the page parameter if its value is a number:

``` php
Router::connectNamed(
    array('page' => '[\d]+'),
    array('default' => false, 'greedy' => false)
);
```

Parse only the page parameter no matter what:

``` php
Router::connectNamed(
    array('page'),
    array('default' => false, 'greedy' => false)
);
```

Parse only the page parameter if the current action is 'index':

``` php
Router::connectNamed(
    array('page' => array('action' => 'index')),
    array('default' => false, 'greedy' => false)
);
```

Parse only the page parameter if the current action is 'index' and the controller is 'pages':

``` php
Router::connectNamed(
    array('page' => array('action' => 'index', 'controller' => 'pages')),
    array('default' => false, 'greedy' => false)
);
```

connectNamed() supports a number of options:

- `greedy` Setting this to true will make Router parse all named params.
  Setting it to false will parse only the connected named params.
- `default` Set this to true to merge in the default set of named parameters.
- `reset` Set to true to clear existing rules and start fresh.
- `separator` Change the string used to separate the key & value in a named
  parameter. Defaults to <span class="title-ref">:</span>

## Reverse Routing

Reverse routing is a feature in CakePHP that is used to allow you to
easily change your URL structure without having to modify all your code.
By using `routing arrays <routing array>` to define your URLs, you can
later configure routes and the generated URLs will automatically update.

If you create URLs using strings like:

``` php
$this->Html->link('View', '/posts/view/' . $id);
```

And then later decide that `/posts` should really be called
'articles' instead, you would have to go through your entire
application renaming URLs. However, if you defined your link like:

``` php
$this->Html->link(
    'View',
    array('controller' => 'posts', 'action' => 'view', $id)
);
```

Then when you decided to change your URLs, you could do so by defining a
route. This would change both the incoming URL mapping, as well as the
generated URLs.

When using array URLs, you can define both query string parameters and
document fragments using special keys:

``` php
Router::url(array(
    'controller' => 'posts',
    'action' => 'index',
    '?' => array('page' => 1),
    '#' => 'top'
));

// will generate a URL like.
/posts/index?page=1#top
```

<a id="redirect-routing"></a>

## Redirect Routing

Redirect routing allows you to issue HTTP status 30x redirects for
incoming routes, and point them at different URLs. This is useful
when you want to inform client applications that a resource has moved
and you don't want to expose two URLs for the same content

Redirection routes are different from normal routes as they perform an actual
header redirection if a match is found. The redirection can occur to
a destination within your application or an outside location:

``` php
Router::redirect(
    '/home/*',
    array('controller' => 'posts', 'action' => 'view'),
    // or array('persist'=>array('id')) for default routing where the
    // view action expects $id as an argument
    array('persist' => true)
);
```

Redirects `/home/*` to `/posts/view` and passes the parameters to
`/posts/view`. Using an array as the redirect destination allows
you to use other routes to define where a URL string should be
redirected to. You can redirect to external locations using
string URLs as the destination:

``` php
Router::redirect('/posts/*', 'https://google.com', array('status' => 302));
```

This would redirect `/posts/*` to `https://google.com` with a
HTTP status of 302.

<a id="disabling-default-routes"></a>

## Disabling the Default Routes

If you have fully customized all your routes, and want to avoid any
possible duplicate content penalties from search engines, you can
remove the default routes that CakePHP offers by deleting them from your
application's routes.php file.

This will cause CakePHP to serve errors, when users try to visit
URLs that would normally be provided by CakePHP but have not
been connected explicitly.

<a id="custom-route-classes"></a>

## Custom Route Classes

Custom route classes allow you to extend and change how individual
routes parse requests and handle reverse routing. A custom route class
should be created in `app/Routing/Route` and should extend
`CakeRoute` and implement one or both of `match()`
and/or `parse()`. `parse()` is used to parse requests and
`match()` is used to handle reverse routing.

You can use a custom route class when making a route by using the
`routeClass` option, and loading the file containing your route
before trying to use it:

``` php
App::uses('SlugRoute', 'Routing/Route');

Router::connect(
     '/:slug',
     array('controller' => 'posts', 'action' => 'view'),
     array('routeClass' => 'SlugRoute')
);
```

This route would create an instance of `SlugRoute` and allow you
to implement custom parameter handling.

## Router API

`class` **Router**

`class` **CakeRoute**

`method` CakeRoute::**parse**($url)

`method` CakeRoute::**match**($url)

`method` CakeRoute::**compile**()
