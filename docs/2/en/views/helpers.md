# Helpers

Helpers are the component-like classes for the presentation layer
of your application. They contain presentational logic that is
shared between many views, elements, or layouts. This chapter will
show you how to create your own helpers, and outline the basic
tasks CakePHP's core helpers can help you accomplish.

CakePHP features a number of helpers that aid in view creation.
They assist in creating well-formed markup (including forms), aid
in formatting text, times and numbers, and can even speed up AJAX
functionality. For more information on the helpers included in CakePHP,
check out the chapter for each helper:

## Using and Configuring Helpers

You enable helpers in CakePHP by making a controller aware of them. Each
controller has a `~Controller::$helpers` property that lists the
helpers to be made available in the view. To enable a helper in your view, add
the name of the helper to the controller's `$helpers` array:

``` php
class BakeriesController extends AppController {
    public $helpers = array('Form', 'Html', 'Js', 'Time');
}
```

Adding helpers from plugins uses the `plugin syntax` used elsewhere in
CakePHP:

``` php
class BakeriesController extends AppController {
    public $helpers = array('Blog.Comment');
}
```

You can also add helpers from within an action, so they will only
be available to that action and not to the other actions in the
controller. This saves processing power for the other actions that
do not use the helper and helps keep the controller better
organized:

``` php
class BakeriesController extends AppController {
    public function bake() {
        $this->helpers[] = 'Time';
    }
    public function mix() {
        // The Time helper is not loaded here and thus not available
    }
}
```

If you need to enable a helper for all controllers, add the name of
the helper to the `$helpers` array in [App / Controller / AppController.php](app/Controller/AppController.php.md) (or
create it if not present). Remember to include the default Html and
Form helpers:

``` php
class AppController extends Controller {
    public $helpers = array('Form', 'Html', 'Js', 'Time');
}
```

You can pass options to helpers. These options can be used to set
attribute values or modify behavior of a helper:

``` php
class AwesomeHelper extends AppHelper {
    public function __construct(View $view, $settings = array()) {
        parent::__construct($view, $settings);
        debug($settings);
    }
}

class AwesomeController extends AppController {
    public $helpers = array('Awesome' => array('option1' => 'value1'));
}
```

As of 2.3, the options are merged with the `Helper::$settings` property of
the helper.

One common setting to use is the `className` option, which allows you to
create aliased helpers in your views. This feature is useful when you want to
replace `$this->Html` or another common Helper reference with a custom
implementation:

``` php
// app/Controller/PostsController.php
class PostsController extends AppController {
    public $helpers = array(
        'Html' => array(
            'className' => 'MyHtml'
        )
    );
}

// app/View/Helper/MyHtmlHelper.php
App::uses('HtmlHelper', 'View/Helper');
class MyHtmlHelper extends HtmlHelper {
    // Add your code to override the core HtmlHelper
}
```

The above would *alias* `MyHtmlHelper` to `$this->Html` in your views.

> [!NOTE]
> Aliasing a helper replaces that instance anywhere that helper is used,
> including inside other Helpers.

Using helper settings allows you to declaratively configure your helpers and
keep configuration logic out of your controller actions. If you have
configuration options that cannot be included as part of a class declaration,
you can set those in your controller's beforeRender callback:

``` php
class PostsController extends AppController {
    public function beforeRender() {
        parent::beforeRender();
        $this->helpers['CustomStuff'] = $this->_getCustomStuffSettings();
    }
}
```

## Using Helpers

Once you've configured which helpers you want to use in your controller,
each helper is exposed as a public property in the view. For example, if you
were using the `HtmlHelper` you would be able to access it by
doing the following:

``` php
echo $this->Html->css('styles');
```

The above would call the `css` method on the HtmlHelper. You can
access any loaded helper using `$this->{$helperName}`. There may
come a time where you need to dynamically load a helper from inside
a view. You can use the view's `HelperCollection` to
do this:

``` php
$mediaHelper = $this->Helpers->load('Media', $mediaSettings);
```

The HelperCollection is a [collection](core-libraries/collections.md) and
supports the collection API used elsewhere in CakePHP.

## Callback methods

Helpers feature several callbacks that allow you to augment the
view rendering process. See the [helper-api](#helper-api) and the
[/core-libraries/collections](core-libraries/collections.md) documentation for more information.

## Creating Helpers

If a core helper (or one showcased on GitHub or in the Bakery)
doesn't fit your needs, helpers are easy to create.

Let's say we wanted to create a helper that could be used to output
a specifically crafted CSS-styled link you needed many different
places in your application. In order to fit your logic into
CakePHP's existing helper structure, you'll need to create a new
class in [App / View / Helper](app/View/Helper.md). Let's call our helper LinkHelper. The
actual PHP class file would look something like this:

    /* /app/View/Helper/LinkHelper.php */
    App::uses('AppHelper', 'View/Helper');

    class LinkHelper extends AppHelper {
        public function makeEdit($title, $url) {
            // Logic to create specially formatted link goes here...
        }
    }

> [!NOTE]
> Helpers must extend either `AppHelper` or `Helper` or implement all the callbacks
> in the [helper-api](#helper-api).

### Including other Helpers

You may wish to use some functionality already existing in another
helper. To do so, you can specify helpers you wish to use with a
`$helpers` array, formatted just as you would in a controller:

``` php
/* /app/View/Helper/LinkHelper.php (using other helpers) */
App::uses('AppHelper', 'View/Helper');

class LinkHelper extends AppHelper {
    public $helpers = array('Html');

    public function makeEdit($title, $url) {
        // Use the HTML helper to output
        // formatted data:

        $link = $this->Html->link($title, $url, array('class' => 'edit'));

        return '<div class="editOuter">' . $link . '</div>';
    }
}
```

### Using your Helper

Once you've created your helper and placed it in
[App / View / Helper / ](app/View/Helper/.md), you'll be able to include it in your
controllers using the special variable `~Controller::$helpers`:

``` php
class PostsController extends AppController {
    public $helpers = array('Link');
}
```

Once your controller has been made aware of this new class, you can
use it in your views by accessing an object named after the
helper:

``` php
<!-- make a link using the new helper -->
<?php echo $this->Link->makeEdit('Change this Recipe', '/recipes/edit/5'); ?>
```

## Creating Functionality for All Helpers

All helpers extend a special class, AppHelper (just like models
extend AppModel and controllers extend AppController). To create
functionality that would be available to all helpers, create
[App / View / Helper / AppHelper.php](app/View/Helper/AppHelper.php.md):

``` css
App::uses('Helper', 'View');

class AppHelper extends Helper {
    public function customMethod() {
    }
}
```

## Helper API

`class` **Helper**

`method` Helper::**webroot**($file)

`method` Helper::**url**($url, $full = false)

`method` Helper::**value**($options = array(), $field = null, $key = 'value')

`method` Helper::**domId**($options = null, $id = 'id')

### Callbacks

`method` Helper::**beforeRenderFile**($viewFile)

`method` Helper::**afterRenderFile**($viewFile, $content)

`method` Helper::**beforeRender**($viewFile)

`method` Helper::**afterRender**($viewFile)

`method` Helper::**beforeLayout**($layoutFile)

`method` Helper::**afterLayout**($layoutFile)
