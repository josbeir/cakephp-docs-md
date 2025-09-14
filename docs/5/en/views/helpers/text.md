---
title: TextHelper
description: The TextHelper contains methods to make text more usable and friendly in your views.
keywords: "text helper,autoLinkEmails,autoLinkUrls,autoLink,excerpt,highlight,stripLinks,truncate,string text"
---

# Text

**Namespace:** `Cake\View\Helper`

### Class `Cake\View\Helper\TextHelper(View $view, array $config = [])`

The TextHelper contains methods to make text more usable and
friendly in your views. It aids in enabling links, formatting URLs,
creating excerpts of text around chosen words or phrases,
highlighting key words in blocks of text, and gracefully
truncating long stretches of text.

## Linking Email addresses

#### Method `Cake\View\Helper\TextHelper(View $view, array $config = [])::autoLinkEmails(string $text, array $options = [])`

Adds links to the well-formed email addresses in $text, according
to any options defined in `$options` (see
`HtmlHelper::link()`).

```php
$myText = 'For more information regarding our world-famous ' .
    'pastries and desserts, contact info@example.com';
$linkedText = $this->Text->autoLinkEmails($myText);

```

Output::

```html
For more information regarding our world-famous pastries and desserts,
contact <a href="mailto:info@example.com">info@example.com</a>

```

This method automatically escapes its input. Use the `escape`
option to disable this if necessary.

## Linking URLs

#### Method `Cake\View\Helper\TextHelper(View $view, array $config = [])::autoLinkUrls(string $text, array $options = [])`

Same as `autoLinkEmails()`, only this method searches for
strings that start with https, http, ftp, or nntp and links them
appropriately.

This method automatically escapes its input. Use the `escape`
option to disable this if necessary.

## Linking Both URLs and Email Addresses

#### Method `Cake\View\Helper\TextHelper(View $view, array $config = [])::autoLink(string $text, array $options = [])`

Performs the functionality in both `autoLinkUrls()` and
`autoLinkEmails()` on the supplied `$text`. All URLs and emails
are linked appropriately given the supplied `$options`.

This method automatically escapes its input. Use the `escape`
option to disable this if necessary.

Further options:

- ``stripProtocol`: Strips `http://` and `https://` from the beginning of
  the link label. Default off.
- `maxLength`: The maximum length of the link label. Default off.
- `ellipsis``: The string to append to the end of the link label. Defaults to
  UTF8 ellipsis.

## Converting Text into Paragraphs

#### Method `Cake\View\Helper\TextHelper(View $view, array $config = [])::autoParagraph(string $text)`

Adds proper `\<p\>` around text where double-line returns are found, and `\<br\>` where
single-line returns are found.

```php
$myText = 'For more information
regarding our world-famous pastries and desserts.

contact info@example.com';
$formattedText = $this->Text->autoParagraph($myText);

```

Output::

```html
\<p\>For more information<br />
regarding our world-famous pastries and desserts.</p>
\<p\>contact info@example.com</p>
```

<!--@include: ../../core-libraries/text.md-->

