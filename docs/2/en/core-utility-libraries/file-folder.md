# Folder & File

The Folder and File utilities are convenience classes to help you read from and
write/append to files; list files within a folder and other common directory
related tasks.

## Basic usage

Ensure the classes are loaded using `App::uses()`:

``` php
<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
```

Then we can setup a new folder instance:

``` php
<?php
$dir = new Folder('/path/to/folder');
```

and search for all *.ctp* files within that folder using regex:

``` php
<?php
$files = $dir->find('.*\.ctp');
```

Now we can loop through the files and read from or write/append to the contents or
simply delete the file:

``` php
<?php
foreach ($files as $file) {
    $file = new File($dir->pwd() . DS . $file);
    $contents = $file->read();
    // $file->write('I am overwriting the contents of this file');
    // $file->append('I am adding to the bottom of this file.');
    // $file->delete(); // I am deleting this file
    $file->close(); // Be sure to close the file when you're done
}
```

## Folder API

`class` **Folder(string**

``` php
<?php
// Create a new folder with 0755 permissions
$dir = new Folder('/path/to/folder', true, 0755);


Path of the current folder. :php:meth:`Folder::pwd()` will return the same
information.


Whether or not the list results should be sorted by name.


Mode to be used when creating folders. Defaults to ``0755``. Does nothing on
Windows machines.


:rtype: string

Returns $path with $element added, with correct slash in-between::

    $path = Folder::addPathElement('/a/path/for', 'testing');
    // $path equals /a/path/for/testing

$element can also be an array::

    $path = Folder::addPathElement('/a/path/for', array('testing', 'another'));
    // $path equals /a/path/for/testing/another

.. versionadded:: 2.5
    $element parameter accepts an array as of 2.5
```

`method` Folder(string::**cd**(string $path)

`method` Folder(string::**chmod**(string $path, integer $mode = false, boolean $recursive = true, array $exceptions = array())

`method` Folder(string::**copy**(array|string $options = array())

`method` Folder(string::**create**(string $pathname, integer $mode = false)

`method` Folder(string::**delete**(string $path = null)

`method` Folder(string::**dirsize**()

`method` Folder(string::**errors**()

`method` Folder(string::**find**(string $regexpPattern = '.*', boolean $sort = false)

> [!NOTE]
> The folder find and findRecursive methods will only find files. If you
> would like to get folders and files see `Folder::read()` or
> `Folder::tree()`

`method` Folder(string::**findRecursive**(string $pattern = '.*', boolean $sort = false)

`method` Folder(string::**inCakePath**(string $path = '')

`method` Folder(string::**inPath**(string $path = '', boolean $reverse = false)

`method` Folder(string::**messages**()

`method` Folder(string::**move**(array $options)

`method` Folder(string::**pwd**()

`method` Folder(string::**read**(boolean $sort = true, array|boolean $exceptions = false, boolean $fullPath = false)

`method` Folder(string::**realpath**(string $path)

`method` Folder(string::**tree**(null|string $path = null, array|boolean $exceptions = true, null|string $type = null)

## File API

`class` **File(string**

``` php
<?php
// Create a new file with 0644 permissions
$file = new File('/path/to/file.php', true, 0644);


The Folder object of the file.


The name of the file with the extension. Differs from
:php:meth:`File::name()` which returns the name without the extension.


An array of file info. Use :php:meth:`File::info()` instead.


Holds the file handler resource if the file is opened.


Enable locking for file reading and writing.


The current file's absolute path.
```

`method` File(string::**append**(string $data, boolean $force = false)

`method` File(string::**close**()

`method` File(string::**copy**(string $dest, boolean $overwrite = true)

`method` File(string::**create**()

`method` File(string::**delete**()

`method` File(string::**executable**()

`method` File(string::**exists**()

`method` File(string::**ext**()

`method` File(string::**Folder**()

`method` File(string::**group**()

`method` File(string::**info**()

`method` File(string::**lastAccess**()

`method` File(string::**lastChange**()

`method` File(string::**md5**(integer|boolean $maxsize = 5)

`method` File(string::**name**()

`method` File(string::**offset**(integer|boolean $offset = false, integer $seek = 0)

`method` File(string::**open**(string $mode = 'r', boolean $force = false)

`method` File(string::**owner**()

`method` File(string::**perms**()

`method` File(string::**pwd**()

`method` File(string::**read**(string $bytes = false, string $mode = 'rb', boolean $force = false)

`method` File(string::**readable**()

`method` File(string::**safe**(string $name = null, string $ext = null)

`method` File(string::**size**()

`method` File(string::**writable**()

`method` File(string::**write**(string $data, string $mode = 'w', boolean$force = false)

<div class="versionadded">

2.1 `File::mime()`

</div>

`method` File(string::**mime**()

`method` File(string::**replaceText**( $search, $replace )

<div class="todo">

Better explain how to use each method with both classes.

</div>
