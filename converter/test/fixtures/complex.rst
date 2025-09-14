.. meta::
    :title: Complex RST Example
    :description: Testing various RST constructs
    :keywords: test, rst, conversion

Complex Document
================

Introduction
------------

This document tests various RST constructs.

.. note:: This is a note admonition
   with multiple lines of content.

.. warning::
   This is a warning with
   multiple paragraphs.

   Second paragraph here.

Code Examples
~~~~~~~~~~~~~

Here's a PHP example::

    <?php
    echo "Hello World";

.. code-block:: php

    <?php
    class Example {
        public function test() {
            return true;
        }
    }

Lists and References
^^^^^^^^^^^^^^^^^^^^

* First item
* Second item

  * Nested item
  * Another nested

1. Numbered first
2. Numbered second

.. _my-reference-label:

Cross References
""""""""""""""""

See :ref:`My Reference Label <my-reference-label>` above.

Also check :doc:`installation` guide.

Use :php:class:`Cake\\Controller\\Controller` in your app.

PHP API Documentation
~~~~~~~~~~~~~~~~~~~~~

.. php:namespace:: Cake\ORM

.. php:class:: Table

.. php:method:: find(string $type = 'all')

    Find records in the table.

.. php:staticmethod:: schema()

    Get the table schema.

.. versionadded:: 4.0
   This method was added in version 4.0.

Images and Includes
^^^^^^^^^^^^^^^^^^^

.. image:: /_static/logo.png

.. include:: /shared/footer.rst

Inline Elements
~~~~~~~~~~~~~~~

Use ``inline code`` in your text.

Visit `CakePHP Website <https://cakephp.org>`__ for more info.

See :abbr:`ORM (Object Relational Mapping)` documentation.