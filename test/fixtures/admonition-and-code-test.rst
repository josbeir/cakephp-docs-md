Testing Admonitions and Code Blocks
===================================

First, let's show a note:

.. note::
    This is a note that should use > prefixes
    and not be converted to a code block.

    It can have multiple paragraphs.

Configuration Example
---------------------

Now here's a code block::

    <?php
    return [
        'debug' => true,
    ];

Another Admonition
------------------

.. warning::
    This warning should also use > prefixes.

Explicit Code Block
-------------------

.. code-block:: php

    <?php
    class Example {
        public function test() {
            return 'explicit code block';
        }
    }