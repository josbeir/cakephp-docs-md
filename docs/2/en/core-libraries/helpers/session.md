---
title: SessionHelper
description: "As a natural counterpart to the Session Component, the Session Helper replicates most of the component's functionality and makes it available in your view."
keywords: "session helper,flash messages,session flash,session read,session check"
---

# SessionHelper

### Class `SessionHelper(View $view, array $settings = array())`

As a natural counterpart to the Session Component, the Session
Helper replicates most of the component's functionality and makes it
available in your view.

The major difference between the Session Helper and the Session
Component is that the helper does *not* have the ability to write
to the session.

As with the Session Component, data is read by using
:term:`dot notation` array structures

```
array('User' => array(
    'username' => 'super@example.com'
));

```

Given the previous array structure, the node would be accessed by
`User.username`, with the dot indicating the nested array. This
notation is used for all Session helper methods wherever a `$key` is
used.

#### Method `read(string $key)`

:rtype: mixed

Read from the Session. Returns a string or array depending on the
contents of the session.

#### Method `consume($name)`

:rtype: mixed

Read and delete a value from the Session. This is useful when you want to
combine reading and deleting values in a single operation.

#### Method `check(string $key)`

:rtype: boolean

Check to see whether a key is in the Session. Returns a boolean representing the
key's existence.

#### Method `error()`

:rtype: string

Returns last error encountered in a session.

#### Method `valid()`

:rtype: boolean

Used to check whether a session is valid in a view.

## Displaying notifications or flash messages

#### Method `flash(string $key = 'flash', array $params = array())`

> **deprecated:** 2.7.0
You should use [flash](flash.md) to
render flash messages.

As explained in [creating-notification-messages](../components/sessions.md#creating-notification-messages), you can
create one-time notifications for feedback. After creating messages
with `SessionComponent::setFlash()`, you will want to
display them. Once a message is displayed, it will be removed and
not displayed again

```php
echo $this->Session->flash();

```

The above will output a simple message with the following HTML:

```html
\<div id="flashMessage" class="message">
    Your stuff has been saved.
</div>

```

As with the component method, you can set additional properties
and customize which element is used. In the controller, you might
have code like

```php
// in a controller
$this->Session->setFlash('The user could not be deleted.');

```

When outputting this message, you can choose the element used to display
the message

```php
// in a layout.
echo $this->Session->flash('flash', array('element' => 'failure'));

```

This would use `View/Elements/failure.ctp` to render the message. The
message text would be available as `$message` in the element.

The failure element would contain something like this:

```php
<div class="flash flash-failure">
    <?php echo h($message); ?>
</div>

```

You can also pass additional parameters into the `flash()` method, which
allows you to generate customized messages

```php
// In the controller
$this->Session->setFlash('Thanks for your payment.');

// In the layout.
echo $this->Session->flash('flash', array(
    'params' => array('name' => $user['User']['name'])
    'element' => 'payment'
));

// View/Elements/payment.ctp
<div class="flash payment">
    <?php printf($message, h($name)); ?>
</div>

```

> [!NOTE]
> By default, CakePHP does not escape the HTML in flash messages. If you are using
> any request or user data in your flash messages, you should escape it
> with `h` when formatting your messages.
>
>