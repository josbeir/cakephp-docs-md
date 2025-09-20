# Flash

`class` Cake\\View\\Helper\\**FlashHelper**(View $view, array $config = [])

FlashHelper provides a way to render flash messages that were set in
`$_SESSION` by [FlashComponent](../../controllers/components/flash).
[FlashComponent](../../controllers/components/flash) and FlashHelper
primarily use elements to render flash messages. Flash elements are found under
the **src/Template/Element/Flash** directory. You'll notice that CakePHP's App
template comes with three flash elements: **success.ctp**, **default.ctp**, and
**error.ctp**.

## Rendering Flash Messages

To render a flash message, you can simply use FlashHelper's `render()`
method in your template file:

``` php
<?= $this->Flash->render() ?>
```

By default, CakePHP uses a "flash" key for flash messages in a session. But, if
you've specified a key when setting the flash message in
[FlashComponent](../../controllers/components/flash), you can specify which
flash key to render:

``` php
<?= $this->Flash->render('other') ?>
```

You can also override any of the options that were set in FlashComponent:

``` php
// In your Controller
$this->Flash->set('The user has been saved.', [
    'element' => 'success'
]);

// In your template file: Will use great_success.ctp instead of succcess.ctp
<?= $this->Flash->render('flash', [
    'element' => 'great_success'
]);
```

> [!NOTE]
> When building custom flash message templates, be sure to properly HTML
> encode any user data. CakePHP won't escape flash message parameters for you.

::: info Added in version 3.1
The  nowstacks messages. If you set multiple flash messages, when you call`render()`, each message will be rendered in its own elements, in theorder they were set.
:::

For more information about the available array options, please refer to the
[FlashComponent](../../controllers/components/flash) section.

## Routing Prefix and Flash Messages

::: info Added in version 3.0.1
:::

If you have a Routing prefix configured, you can now have your Flash elements
stored in **src/Template/{Prefix}/Element/Flash**. This way, you can have
specific messages layouts for each part of your application. For instance, using
different layouts for your front-end and admin section.

## Flash Messages and Themes

The FlashHelper uses normal elements to render the messages and will therefore
obey any theme you might have specified. So when your theme has a
**src/Template/Element/Flash/error.ctp** file it will be used, just as with any
Elements and Views.
