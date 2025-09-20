# Folder & File

Folder と File ユーティリティは、ファイルの読み書きやフォルダ内のファイル名一覧の取得、
その他ディレクトリに関連するタスクにおいて便利なクラスです。

## 基本的な使い方

`App::uses()` を使ってクラスをロードします。 :

``` css
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
```

すると、新しいフォルダインスタンスをセットアップすることができるようになります。 :

``` php
$dir = new Folder('/path/to/folder');
```

インスタンスを作成したフォルダ内から *.ctp* の拡張子が付いたファイルを
正規表現検索する場合はこのようにします。 :

``` php
$files = $dir->find('.*\.ctp');
```

これでファイルの読み込みや、コンテンツの書き込み、ファイルの削除などが行えるようになります。 :

``` php
foreach ($files as $file) {
    $file = new File($dir->pwd() . DS . $file);
    $contents = $file->read();
    // $file->write('このファイルの内容を上書きします');
    // $file->append('このファイルの最後に追記します。');
    // $file->delete(); // このファイルを削除します
    $file->close(); // 終了時にファイルをクローズしましょう
}
```

## Folder API

`class` **Folder**(string $path = false, boolean $create = false, mixed $mode = false)

``` php
// 0755 のパーミッションで新しいフォルダを作成します
$dir = new Folder('/path/to/folder', true, 0755);


フォルダの現在のパス。 :php:meth:`Folder::pwd()` は同じ情報を返します。



ファイルリストを取得する際に、名前によるソートを実行するか否かの値。


フォルダ作成時のモード。デフォルトでは ``0755`` 。
Windows マシンでは何も影響しません。


:rtype: string

$path と $element の間に適切なスラッシュを加えて返します。 ::

    $path = Folder::addPathElement('/a/path/for', 'testing');
    // $path は /a/path/for/testing となります

.. versionadded:: 2.5
    2.5 から $element パラメータは配列も使用できます。
```

`method` Folder::**cd**(string $path)

`method` Folder::**chmod**(string $path, integer $mode = false, boolean $recursive = true, array $exceptions = array())

`method` Folder::**copy**(array|string $options = array())

`method` Folder::**create**(string $pathname, integer $mode = false)

`method` Folder::**delete**(string $path = null)

`method` Folder::**dirsize**()

`method` Folder::**errors**()

`method` Folder::**find**(string $regexpPattern = '.*', boolean $sort = false)

> [!NOTE]
> フォルダの find メソッドと findRecursive メソッドは、ファイルのみを検索します。
> フォルダとファイルを取得したい場合は、 `Folder::read()` もしくは
> `Folder::tree()` 参照してください。

`method` Folder::**findRecursive**(string $pattern = '.*', boolean $sort = false)

`method` Folder::**inCakePath**(string $path = '')

`method` Folder::**inPath**(string $path = '', boolean $reverse = false)

`method` Folder::**messages**()

`method` Folder::**move**(array $options)

`method` Folder::**pwd**()

`method` Folder::**read**(boolean $sort = true, array|boolean $exceptions = false, boolean $fullPath = false)

`method` Folder::**realpath**(string $path)

`method` Folder::**tree**(null|string $path = null, array|boolean $exceptions = true, null|string $type = null)

## File API

`class` **File**(string $path, boolean $create = false, integer $mode = 755)

``` php
// 0644 のパーミッションで新しいファイルを作成します
$file = new File('/path/to/file.php', true, 0644);


ファイルが属するフォルダ・オブジェクト


拡張子付きのファイル名。
拡張子なしのファイル名を返す :php:meth:`File::name()` とは異なります。


ファイル情報の配列。代わりに :php:meth:`File::info()` を使用してください。


ファイルをオープンしている場合のファイルハンドラを保持します。


ファイルの読み書き時のロックを有効にします。


現在のファイルの絶対パス。
```

`method` File::**append**(string $data, boolean $force = false)

`method` File::**close**()

`method` File::**copy**(string $dest, boolean $overwrite = true)

`method` File::**create**()

`method` File::**delete**()

`method` File::**executable**()

`method` File::**exists**()

`method` File::**ext**()

`method` File::**Folder**()

`method` File::**group**()

`method` File::**info**()

`method` File::**lastAccess**()

`method` File::**lastChange**()

`method` File::**md5**(integer|boolean $maxsize = 5)

`method` File::**name**()

`method` File::**offset**(integer|boolean $offset = false, integer $seek = 0)

`method` File::**open**(string $mode = 'r', boolean $force = false)

`method` File::**owner**()

`method` File::**perms**()

`method` File::**pwd**()

`method` File::**read**(string $bytes = false, string $mode = 'rb', boolean $force = false)

`method` File::**readable**()

`method` File::**safe**(string $name = null, string $ext = null)

`method` File::**size**()

`method` File::**writable**()

`method` File::**write**(string $data, string $mode = 'w', boolean$force = false)

::: info Added in version 2.1
`File::mime()`
:::

`method` File::**mime**()

`method` File::**replaceText**( $search, $replace )

<div class="todo">

双方のクラスの各メソッドの使い方について、より良い解説が必要です。

</div>
