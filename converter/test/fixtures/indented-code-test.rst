Indented Code Block Test
========================

This document tests various indented code blocks with language detection.

PHP Configuration
-----------------

Configure your database connection::

    <?php
    // config/app_local.php
    return [
        'Datasources' => [
            'default' => [
                'host' => 'localhost',
                'username' => 'cakephp',
                'password' => 'secret',
                'database' => 'my_app',
            ],
        ],
    ];

Class Definition
----------------

Here's a simple model class::

    class ArticlesTable extends Table
    {
        public function initialize(array $config): void
        {
            $this->addBehavior('Timestamp');
        }
    }

SQL Queries
-----------

Example database queries::

    SELECT id, title, body
    FROM articles
    WHERE published = 1
    ORDER BY created DESC;

JavaScript Code
---------------

Client-side validation::

    function validateForm() {
        var name = document.getElementById('name').value;
        if (name === '') {
            console.log('Name is required');
            return false;
        }
        return true;
    }

Shell Commands
--------------

Installation commands::

    $ composer create-project cakephp/app myapp
    $ cd myapp
    $ chmod +x bin/cake
    $ sudo chown -R www-data:www-data tmp

YAML Configuration
------------------

Docker compose file::

    version: '3'
    services:
      web:
        image: php:8.1
        ports:
          - "8080:80"