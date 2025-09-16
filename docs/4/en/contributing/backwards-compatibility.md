# Backwards Compatibility Guide

Ensuring that you can upgrade your applications easily and smoothly is important
to us. That's why we only break compatibility at major release milestones.
You might be familiar with [semantic versioning](https://semver.org/), which is
the general guideline we use on all CakePHP projects. In short, semantic
versioning means that only major releases (such as 2.0, 3.0, 4.0) can break
backwards compatibility. Minor releases (such as 2.1, 3.1, 3.2) may introduce new
features, but are not allowed to break compatibility. Bug fix releases (such as 2.1.2,
3.0.1) do not add new features, but fix bugs or enhance performance only.

> [!NOTE]
> Deprecations are removed with the next major version of the framework.
> It is advised that you adapt to deprecations as they are introduced to
> ensure future upgrades are easier.

To clarify what changes you can expect in each release tier we have more
detailed information for developers using CakePHP, and for developers working on
CakePHP that helps set expectations of what can be done in minor releases. Major
releases can have as many breaking changes as required.

## Migration Guides

For each major and minor release, the CakePHP team will provide a migration
guide. These guides explain the new features and any breaking changes that are
in each release. They can be found in the [Appendices](../appendices.md) section of the
cookbook.

## Using CakePHP

If you are building your application with CakePHP, the following guidelines
explain the stability you can expect.

### Interfaces

Outside of major releases, interfaces provided by CakePHP will **not** have any
existing methods changed. New methods may be added, but no existing methods will
be changed.

### Classes

Classes provided by CakePHP can be constructed and have their public methods and
properties used by application code and outside of major releases backwards
compatibility is ensured.

> [!NOTE]
> Some classes in CakePHP are marked with the `@internal` API doc tag. These
> classes are **not** stable and do not have any backwards compatibility
> promises.

In minor releases, new methods may be added to classes, and existing methods may
have new arguments added. Any new arguments will have default values, but if
you've overridden methods with a differing signature you may see fatal errors.
Methods that have new arguments added will be documented in the migration guide
for that release.

The following table outlines several use cases and what compatibility you can
expect from CakePHP:

| If you...                     | Backwards compatibility? |
|-------------------------------|--------------------------|
| Typehint against the class    | Yes                      |
| Create a new instance         | Yes                      |
| Extend the class              | Yes                      |
| Access a public property      | Yes                      |
| Call a public method          | Yes                      |
| **Extend a class and...**     |                          |
| Override a public property    | Yes                      |
| Access a protected property   | No[^1]                   |
| Override a protected property | No[^2]                   |
| Override a protected method   | No[^3]                   |
| Call a protected method       | No[^4]                   |
| Add a public property         | No                       |
| Add a public method           | No                       |
| Add an argument               
 to an overridden method        | No[^5]                   |
| Add a default argument value  
 to an existing method          
 argument                       | Yes                      |

## Working on CakePHP

If you are helping make CakePHP even better please keep the following guidelines
in mind when adding/changing functionality:

In a minor release you can:

| In a minor release can you... |         |
|-------------------------------|---------|
| **Classes**                   |         |
| Remove a class                | No      |
| Remove an interface           | No      |
| Remove a trait                | No      |
| Make final                    | No      |
| Make abstract                 | No      |
| Change name                   | Yes[^6] |
| **Properties**                |         |
| Add a public property         | Yes     |
| Remove a public property      | No      |
| Add a protected property      | Yes     |
| Remove a protected property   | Yes[^7] |
| **Methods**                   |         |
| Add a public method           | Yes     |
| Remove a public method        | No      |
| Add a protected method        | Yes     |
| Move to parent class          | Yes     |
| Remove a protected method     | Yes[^8] |
| Reduce visibility             | No      |
| Change method name            | Yes[^9] |
| Add a new argument with       
 default value                  | Yes     |
| Add a new required argument   
 to an existing method.         | No      |
| Remove a default value from   
 an existing argument           | No      |
| Change method type void       | Yes     |

## Deprecations

In each minor release, features may be deprecated. If features are deprecated,
API documentation and runtime warnings will be added. Runtime errors help you
locate code that needs to be updated before it breaks. If you wish to disable
runtime warnings you can do so using the `Error.errorLevel` configuration
value:

    // in config/app.php
    // ...
    'Error' => [
        'errorLevel' => E_ALL ^ E_USER_DEPRECATED,
    ]
    // ...

Will disable runtime deprecation warnings.

<a id="experimental-features"></a>

## Experimental Features

Experimental features are **not included** in the above backwards compatibility
promises. Experimental features can have breaking changes made in minor releases
as long as they remain experimental. Experimental features can be identified by
the warning in the book and the usage of `@experimental` in the API
documentation.

Experimental features are intended to help gather feedback on how a feature
works before it becomes stable. Once the interfaces and behavior has been vetted
with the community the experimental flags will be removed.

[^1]: Your code *may* be broken by minor releases. Check the migration guide
    for details.

[^2]: Your code *may* be broken by minor releases. Check the migration guide
    for details.

[^3]: Your code *may* be broken by minor releases. Check the migration guide
    for details.

[^4]: Your code *may* be broken by minor releases. Check the migration guide
    for details.

[^5]: Your code *may* be broken by minor releases. Check the migration guide
    for details.

[^6]: You can change a class/method name as long as the old name remains
    available. This is generally avoided unless renaming has significant
    benefit.

[^7]: Avoid whenever possible. Any removals need to be documented in
    the migration guide.

[^8]: Avoid whenever possible. Any removals need to be documented in
    the migration guide.

[^9]: You can change a class/method name as long as the old name remains
    available. This is generally avoided unless renaming has significant
    benefit.
