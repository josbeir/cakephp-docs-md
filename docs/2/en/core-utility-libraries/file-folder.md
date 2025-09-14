---
title: Folder & File
description: "The Folder and File utilities are convenience classes to help you read, write, and append to files; list files within a folder and other common directory related tasks."
keywords: "file,folder,cakephp utility,read file,write file,append file,recursively copy,copy options,folder path,class folder,file php,php files,change directory,file utilities,new folder,directory structure,delete file"
---

# Folder & File

The Folder and File utilities are convenience classes to help you read from and
write/append to files; list files within a folder and other common directory
related tasks.

## Basic usage

Ensure the classes are loaded using `App::uses()`

```php
<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

```

Then we can setup a new folder instance::

```php
<?php
$dir = new Folder('/path/to/folder');

```

and search for all *.ctp* files within that folder using regex

```php
<?php
$files = $dir->find('.*\.ctp');

```

Now we can loop through the files and read from or write/append to the contents or
simply delete the file

```php
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

### Class `Folder(string $path = false, boolean $create = false, string|boolean $mode = false)`

```php
<?php
// Create a new folder with 0755 permissions
$dir = new Folder('/path/to/folder', true, 0755);

```

#### Property `path`

Path of the current folder. `Folder::pwd()` will return the same
information.

#### Property `sort`

Whether or not the list results should be sorted by name.

#### Property `mode`

Mode to be used when creating folders. Defaults to `0755`. Does nothing on
Windows machines.

#### Static Method `addPathElement(string $path, string $element)`

:rtype: string

Returns $path with $element added, with correct slash in-between

```php
$path = Folder::addPathElement('/a/path/for', 'testing');
// $path equals /a/path/for/testing

```

    $element can also be an array::

```php
$path = Folder::addPathElement('/a/path/for', array('testing', 'another'));
// $path equals /a/path/for/testing/another

```

> [!IMPORTANT]
> Added in version 2.5
> $element parameter accepts an array as of 2.5
>
>

#### Method `cd(string $path)`

:rtype: string

Change directory to $path. Returns false on failure

```php
<?php
$folder = new Folder('/foo');
echo $folder->path; // Prints /foo
$folder->cd('/bar');
echo $folder->path; // Prints /bar
$false = $folder->cd('/non-existent-folder');

```

#### Method `chmod(string $path, integer $mode = false, boolean $recursive = true, array $exceptions = array())`

:rtype: boolean

Change the mode on a directory structure recursively. This includes
changing the mode on files as well

```php
<?php
$dir = new Folder();
$dir->chmod('/path/to/folder', 0755, true, array('skip_me.php'));

```

#### Method `copy(array|string $options = array())`

:rtype: boolean

Copy a directory (recursively by default). The only parameter $options can either
be a path into copy to or an array of options

```php
<?php
$folder1 = new Folder('/path/to/folder1');
$folder1->copy('/path/to/folder2');
// Will put folder1 and all its contents into folder2

$folder = new Folder('/path/to/folder');
$folder->copy(array(
    'to' => '/path/to/new/folder',
    'from' => '/path/to/copy/from', // will cause a cd() to occur
    'mode' => 0755,
    'skip' => array('skip-me.php', '.git'),
    'scheme' => Folder::SKIP, // Skip directories/files that already exist.
    'recursive' => true //set false to disable recursive copy
));

```

There are 3 supported schemes:

- `Folder::SKIP` skip copying/moving files & directories that exist in the
destination directory.
- `Folder::MERGE` merge the source/destination directories. Files in the
source directory will replace files in the target directory. Directory
contents will be merged.
- `Folder::OVERWRITE` overwrite existing files & directories in the target
directory with those in the source directory. If both the target and
destination contain the same subdirectory, the target directory's contents
will be removed and replaced with the source's.
> **versionchanged:** 2.3
The merge, skip and overwrite schemes were added to `copy()`

#### Static Method `correctSlashFor(string $path)`

:rtype: string

Returns a correct set of slashes for given $path ('\\' for
Windows paths and '/' for other paths).

#### Method `create(string $pathname, integer $mode = false)`

:rtype: boolean

Create a directory structure recursively. Can be used to create
deep path structures like `/foo/bar/baz/shoe/horn`

```php
<?php
$folder = new Folder();
if ($folder->create('foo' . DS . 'bar' . DS . 'baz' . DS . 'shoe' . DS . 'horn')) {
    // Successfully created the nested folders
}

```

#### Method `delete(string $path = null)`

:rtype: boolean

Recursively remove directories if the system allows

```php
<?php
$folder = new Folder('foo');
if ($folder->delete()) {
    // Successfully deleted foo and its nested folders
}

```

#### Method `dirsize()`

:rtype: integer

Returns the size in bytes of this Folder and its contents.

#### Method `errors()`

:rtype: array

Get the error from latest method.

#### Method `find(string $regexpPattern = '.*', boolean $sort = false)`

:rtype: array

Returns an array of all matching files in the current directory

```php
<?php
// Find all .png in your app/webroot/img/ folder and sort the results
$dir = new Folder(WWW_ROOT . 'img');
$files = $dir->find('.*\.png', true);
/*
Array
(
    [0] => cake.icon.png
    [1] => test-error-icon.png
    [2] => test-fail-icon.png
    [3] => test-pass-icon.png
    [4] => test-skip-icon.png
)
*/

```

> [!NOTE]
> The folder find and findRecursive methods will only find files. If you
> would like to get folders and files see `Folder::read()` or
> `Folder::tree()`
>

#### Method `findRecursive(string $pattern = '.*', boolean $sort = false)`

:rtype: array

Returns an array of all matching files in and below the current directory

```php
<?php
// Recursively find files beginning with test or index
$dir = new Folder(WWW_ROOT);
$files = $dir->findRecursive('(test|index).*');
/*
Array
(
    [0] => /var/www/cake/app/webroot/index.php
    [1] => /var/www/cake/app/webroot/test.php
    [2] => /var/www/cake/app/webroot/img/test-skip-icon.png
    [3] => /var/www/cake/app/webroot/img/test-fail-icon.png
    [4] => /var/www/cake/app/webroot/img/test-error-icon.png
    [5] => /var/www/cake/app/webroot/img/test-pass-icon.png
)
*/

```

#### Method `inCakePath(string $path = '')`

:rtype: boolean

Returns true if the file is in a given CakePath.

#### Method `inPath(string $path = '', boolean $reverse = false)`

:rtype: boolean

Returns true if the file is in the given path

```php
<?php
$Folder = new Folder(WWW_ROOT);
$result = $Folder->inPath(APP);
// $result = true, /var/www/example/app/ is in /var/www/example/app/webroot/

$result = $Folder->inPath(WWW_ROOT . 'img' . DS, true);
// $result = true, /var/www/example/app/webroot/ is in /var/www/example/app/webroot/img/

```

#### Static Method `isAbsolute(string $path)`

:rtype: boolean

Returns true if the given $path is an absolute path.

#### Static Method `isSlashTerm(string $path)`

:rtype: boolean

Returns true if given $path ends in a slash (i.e. is slash-terminated)

```php
<?php
$result = Folder::isSlashTerm('/my/test/path');
// $result = false
$result = Folder::isSlashTerm('/my/test/path/');
// $result = true

```

#### Static Method `isWindowsPath(string $path)`

:rtype: boolean

Returns true if the given $path is a Windows path.

#### Method `messages()`

:rtype: array

Get the messages from the latest method.

#### Method `move(array $options)`

:rtype: boolean

Move a directory (recursively by default). The only parameter $options is the same as for `copy()`

#### Static Method `normalizePath(string $path)`

:rtype: string

Returns a correct set of slashes for given $path ('\\' for
Windows paths and '/' for other paths).

#### Method `pwd()`

:rtype: string

Return current path.

#### Method `read(boolean $sort = true, array|boolean $exceptions = false, boolean $fullPath = false)`

:rtype: mixed

:param boolean $sort: If true will sort results.
:param mixed $exceptions: An array of files and folder names to ignore. If
true or '.' this method will ignore hidden or dot files.
:param boolean $fullPath: If true will return results using absolute paths.

Returns an array of the contents of the current directory. The
returned array holds two sub arrays: One of directories and one of files

```php
<?php
$dir = new Folder(WWW_ROOT);
$files = $dir->read(true, array('files', 'index.php'));
/*
Array
(
    [0] => Array // folders
        (
            [0] => css
            [1] => img
            [2] => js
        )
    [1] => Array // files
        (
            [0] => .htaccess
            [1] => favicon.ico
            [2] => test.php
        )
)
*/

```

#### Method `realpath(string $path)`

:rtype: string

Get the real path (taking ".." and such into account).

#### Static Method `slashTerm(string $path)`

:rtype: string

Returns $path with added terminating slash (corrected for
Windows or other OS).

#### Method `tree(null|string $path = null, array|boolean $exceptions = true, null|string $type = null)`

:rtype: mixed

Returns an array of nested directories and files in each directory.

## File API

### Class `File(string $path, boolean $create = false, integer $mode = 755)`

```php
<?php
// Create a new file with 0644 permissions
$file = new File('/path/to/file.php', true, 0644);

```

#### Property `Folder`

The Folder object of the file.

#### Property `name`

The name of the file with the extension. Differs from
`File::name()` which returns the name without the extension.

#### Property `info`

An array of file info. Use `File::info()` instead.

#### Property `handle`

Holds the file handler resource if the file is opened.

#### Property `lock`

Enable locking for file reading and writing.

#### Property `path`

The current file's absolute path.

#### Method `append(string $data, boolean $force = false)`

:rtype: boolean

Append the given data string to the current file.

#### Method `close()`

:rtype: boolean

Closes the current file if it is opened.

#### Method `copy(string $dest, boolean $overwrite = true)`

:rtype: boolean

Copy the file to $dest.

#### Method `create()`

:rtype: boolean

Creates the file.

#### Method `delete()`

:rtype: boolean

Deletes the file.

#### Method `executable()`

:rtype: boolean

Returns true if the file is executable.

#### Method `exists()`

:rtype: boolean

Returns true if the file exists.

#### Method `ext()`

:rtype: string

Returns the file extension.

#### Method `Folder()`

:rtype: Folder

Returns the current folder.

#### Method `group()`

:rtype: integer|false

Returns the file's group, or false in case of an error.

#### Method `info()`

:rtype: array

Returns the file info.
> **versionchanged:** 2.1
`File::info()` now includes filesize & mimetype information.

#### Method `lastAccess()`

:rtype: integer|false

Returns last access time, or false in case of an error.

#### Method `lastChange()`

:rtype: integer|false

Returns last modified time, or false in case of an error.

#### Method `md5(integer|boolean $maxsize = 5)`

:rtype: string

Get the MD5 Checksum of file with previous check of filesize,
or false in case of an error.

#### Method `name()`

:rtype: string

Returns the file name without extension.

#### Method `offset(integer|boolean $offset = false, integer $seek = 0)`

:rtype: mixed

Sets or gets the offset for the currently opened file.

#### Method `open(string $mode = 'r', boolean $force = false)`

:rtype: boolean

Opens the current file with the given $mode.

#### Method `owner()`

:rtype: integer

Returns the file's owner.

#### Method `perms()`

:rtype: string

Returns the "chmod" (permissions) of the file.

#### Static Method `prepare(string $data, boolean $forceWindows = false)`

:rtype: string

Prepares a ascii string for writing. Converts line endings to the
correct terminator for the current platform. For Windows "\r\n"
will be used, "\n" for all other platforms.

#### Method `pwd()`

:rtype: string

Returns the full path of the file.

#### Method `read(string $bytes = false, string $mode = 'rb', boolean $force = false)`

:rtype: string|boolean

Return the contents of the current file as a string or return false on failure.

#### Method `readable()`

:rtype: boolean

Returns true if the file is readable.

#### Method `safe(string $name = null, string $ext = null)`

:rtype: string

Makes filename safe for saving.

#### Method `size()`

:rtype: integer

Returns the filesize.

#### Method `writable()`

:rtype: boolean

Returns true if the file is writable.

#### Method `write(string $data, string $mode = 'w', boolean$force = false)`

:rtype: boolean

Write given data to the current file.

> [!IMPORTANT]
> Added in version 2.1 `File::mime()`
>

#### Method `mime()`

:rtype: mixed

Get the file's mimetype, returns false on failure.

#### Method `replaceText( $search, $replace )`

:rtype: boolean

Replaces text in a file. Returns false on failure and true on success.

> [!IMPORTANT]
> Added in version 
> 2.5 `File::replaceText()`
>

.. todo

```
Better explain how to use each method with both classes.

```