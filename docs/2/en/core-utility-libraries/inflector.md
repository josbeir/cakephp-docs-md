---
title: Inflector
keywords: "apple orange,word variations,apple pie,person man,latin versions,profile settings,php class,initial state,puree,slug,apples,oranges,user profile,underscore"
---

# Inflector

### Class `Inflector`

The Inflector class takes a string and can manipulate it to handle
word variations such as pluralizations or camelizing and is
normally accessed statically. Example:
`Inflector::pluralize('example')` returns "examples".

You can try out the inflections online at
[inflector.cakephp.org](https://inflector.cakephp.org/).

#### Static Method `pluralize($singular)`

- **Input:** Apple, Orange, Person, Man
- **Output:** Apples, Oranges, People, Men

> [!NOTE]
> `pluralize()` may not always correctly convert a noun that is already in
> it's plural form.
>

#### Static Method `singularize($plural)`

- **Input:** Apples, Oranges, People, Men
- **Output:** Apple, Orange, Person, Man

> [!NOTE]
> `singularize()` may not always correctly convert a noun that is already in
> it's singular form.
>

#### Static Method `camelize($underscored)`

- **Input:** Apple\_pie, some\_thing, people\_person
- **Output:** ApplePie, SomeThing, PeoplePerson

#### Static Method `underscore($camelCase)`

It should be noted that underscore will only convert camelCase
formatted words. Words that contains spaces will be lower-cased,
but will not contain an underscore.

- **Input:** applePie, someThing
- **Output:** apple\_pie, some\_thing

#### Static Method `humanize($underscored)`

- **Input:** apple\_pie, some\_thing, people\_person
- **Output:** Apple Pie, Some Thing, People Person

#### Static Method `tableize($camelCase)`

- **Input:** Apple, UserProfileSetting, Person
- **Output:** apples, user\_profile\_settings, people

#### Static Method `classify($underscored)`

- **Input:** apples, user\_profile\_settings, people
- **Output:** Apple, UserProfileSetting, Person

#### Static Method `variable($underscored)`

- **Input:** apples, user\_result, people\_people
- **Output:** apples, userResult, peoplePeople

#### Static Method `slug($word, $replacement = '_')`

Slug converts special characters into latin versions and converting
unmatched characters and spaces to underscores. The slug method
expects UTF-8 encoding.

- **Input:** apple purée
- **Output:** apple\_puree

#### Static Method `reset()`

Resets Inflector back to its initial state, useful in testing.

#### Static Method `rules($type, $rules, $reset = false)`

Define new inflection and transliteration rules for Inflector to use.
    See [inflection-configuration](../development/configuration.md#inflection-configuration) for more information.

