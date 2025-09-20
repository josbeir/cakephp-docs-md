# データベースの基本

CakePHP データベースアクセス層は、リレーショナルデータベースを扱うほとんどの面を
抽象化して、サーバーコネクションの保持、クエリーの生成、SQL インジェクションの防止、
スキーマチェックと変更、デバッグとデータベースに送信したクエリーのプロファイリング
などの支援を提供します。

## クイックツアー

この章で説明する機能は、下位レベルのデータベースアクセス API でできることを説明します。
もし ORM についてもっと詳細に知りたい場合は、 [クエリービルダー](../orm/query-builder)
や [テーブルオブジェクト](../orm/table-objects) のセクションを参照してください。

データベース接続を作る一番簡単な方法は、 `DSN` 文字列を使います。 :

``` php
use Cake\Datasource\ConnectionManager;

$dsn = 'mysql://root:password@localhost/my_database';
ConnectionManager::config('default', ['url' => $dsn]);
```

作成したら、コネクションオブジェクトにアクセスして使えるようになります。 :

``` php
$connection = ConnectionManager::get('default');
```

### サポートしているデータベース

CakePHP は下記のリレーショナルデータベースをサポートしています。

- MySQL 5.1+
- SQLite 3
- PostgreSQL 8.3+
- SQLServer 2008+
- Oracle (コミュニティープラグイン経由)

上記のデータベースそれぞれについて、適切な PDO 拡張がインストールされている必要があります。
プロシージャー型 API はサポートされていません。

Oracle データベースは、
[Driver for Oracle Database](https://github.com/CakeDC/cakephp-oracle-driver)
コミュニティープラグインを経由してサポートされます。

### Select 文の実行

生の SQL クエリーを実行するのは非常に簡単です。 :

``` php
use Cake\Datasource\ConnectionManager;

$connection = ConnectionManager::get('default');
$results = $connection->execute('SELECT * FROM articles')->fetchAll('assoc');
```

パラメーターを追加するには、プリペアードステートメントを使います。 :

``` php
$results = $connection
    ->execute('SELECT * FROM articles WHERE id = :id', ['id' => 1])
    ->fetchAll('assoc');
```

これは引数として複合データ型を使用することも可能です。 :

``` php
use Cake\Datasource\ConnectionManager;
use DateTime;

$connection = ConnectionManager::get('default');
$results = $connection
    ->execute(
        'SELECT * FROM articles WHERE created >= :created',
        ['created' => new DateTime('1 day ago')],
        ['created' => 'datetime']
    )
    ->fetchAll('assoc');
```

SQL 文を手で書く代わりに、クエリービルダーを使うこともできます。 :

``` php
$results = $connection
    ->newQuery()
    ->select('*')
    ->from('articles')
    ->where(['created >' => new DateTime('1 day ago')], ['created' => 'datetime'])
    ->order(['title' => 'DESC'])
    ->execute()
    ->fetchAll('assoc');
```

### Insert 文の実行

データベースに行を追加するのは、通常は数行の話しです。 :

``` php
use Cake\Datasource\ConnectionManager;
use DateTime;

$connection = ConnectionManager::get('default');
$connection->insert('articles', [
    'title' => 'A New Article',
    'created' => new DateTime('now')
], ['created' => 'datetime']);
```

### Update 文の実行

データベースの行の更新も同様に直感的に可能で、下記の例では article の **id** が 10 の
データを更新しています。 :

``` php
use Cake\Datasource\ConnectionManager;
$connection = ConnectionManager::get('default');
$connection->update('articles', ['title' => 'New title'], ['id' => 10]);
```

### Delete 文の実行

同様に `delete()` メソッドはデータベースから行を削除するために使われ、
下記の例では article の **id** が 10 の行を削除しています。 :

``` php
use Cake\Datasource\ConnectionManager;
$connection = ConnectionManager::get('default');
$connection->delete('articles', ['id' => 10]);
```

## 設定

慣例として、データベース接続は **config/app.php** に設定します。
このファイルに定義された接続情報は、アプリケーションが使用する接続構成を生成する
`Cake\Datasource\ConnectionManager` に引き渡します。
サンプルとなる接続情報が **config/app.default.php** にあります。
サンプルの接続設定は、次のようになります。 :

    'Datasources' => [
        'default' => [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'persistent' => false,
            'host' => 'localhost',
            'username' => 'my_app',
            'password' => 'secret',
            'database' => 'my_app',
            'encoding' => 'utf8',
            'timezone' => 'UTC',
            'cacheMetadata' => true,
        ]
    ],

上記は指定されたパラメーターを持つ 'default' 接続を生成します。
あなたは設定ファイルに必要な数だけ接続を定義することができます。
また、 `Cake\Datasource\ConnectionManager::config()` を使って
実行時に追加の設定をおこなうこともできます。その一例は次のようになります。 :

``` php
use Cake\Datasource\ConnectionManager;

ConnectionManager::config('default', [
    'className' => 'Cake\Database\Connection',
    'driver' => 'Cake\Database\Driver\Mysql',
    'persistent' => false,
    'host' => 'localhost',
    'username' => 'my_app',
    'password' => 'secret',
    'database' => 'my_app',
    'encoding' => 'utf8',
    'timezone' => 'UTC',
    'cacheMetadata' => true,
]);
```

設定オプションは `DSN` 文字列形式で設定することもできます。
これは、環境変数や `PaaS` 環境で作業する時に便利です。:

``` css
ConnectionManager::config('default', [
    'url' => 'mysql://my_app:secret@localhost/my_app?encoding=utf8&timezone=UTC&cacheMetadata=true',
]);
```

DSN 文字列を使用するときには、クエリー文字列引数として追加のパラメーターやオプションを
定義することができます。

デフォルトでは、すべてのテーブルオブジェクトは `default` の接続を使用します。
デフォルト以外の接続を使用するには、 [Configuring Table Connections](../orm/table-objects#configuring-table-connections) を参照してください。

データベース設定ではいくつかのキーがサポートされています。使用可能なキーは下記の通りです。:

className  
データベースサーバーへの接続を行うクラスの名前空間名付きの完全クラス名。
このクラスは、データベースドライバーをロードし、SQL トランザクションメカニズムを提供し、
SQL を生成したりといったことを担当しています。

driver  
ドライバークラス名は、データベースエンジンのすべての特異性を実装するために使われます。
これは `プラグイン記法` を用いた短いクラス名でも、
完全名前空間名でも、どちらでもドライバーインスタンスを生成することが可能です。
短いクラス名の例は、 Mysql, Sqlite, Postgres, Sqlserver などです。

persistent  
データベースへの持続的接続を使うかどうか。このオプションは、 SqlServer ではサポートされません。
CakePHP バージョン 3.4.13 以降、SqlServer で `persistent` を `true` にセットすると
例外が投げられます。

host  
データベースサーバーのホスト名 （または IP アドレス）。

username  
アカウントのユーザー名。

password  
アカウントのパスワード。

database  
接続するデータベース名。データベース名に `.` の使用は避けてください。
識別子を引用符で囲むことが難しくするため、CakePHP は、データベース名の
`.` をサポートしません。相対パスに起因する不正なパスを避けるため、 SQLite
データベースへのパスは、絶対パス (`ROOT . DS . 'my_app.db'` など) にしてください。

port (*optional*)  
サーバーに接続する際に使用する TCP ポート または Unix ソケット。

encoding  
サーバーに SQL 文を送信する際に使用する文字セットを示します。
DB2 以外のデータベースでは、データベースのデフォルトエンコーディングが
デフォルト設定されます。
もし MySQL で UTF-8 で接続したいのなら、ハイフンなしで 'utf8' と指定してください。

timezone  
サーバーのタイムゾーンがセットされます。

schema  
PostgreSQL データベースで特定のスキーマを使う時に設定します。

unix_socket  
Unix ソケットファイルを経由して接続することをサポートしているドライバーによって
使用されます。PostgreSQL を使用していて、Unix ソケットを使用する場合は、
host を空白のままにします。

ssl_key  
SSL キー・ファイルへのファイルパス。 （MySQL のみでサポートされています）。

ssl_cert  
SSL 証明書ファイルへのファイルパス。 （MySQL のみでサポートされています）。

ssl_ca  
SSL 証明書の認証局へのファイルパス。 （MySQL のみでサポートされています）。

init  
接続が作成されたときに、データベースサーバーに送信されるクエリーのリスト。

log  
クエリーログを有効にするには `true` をセットします。
有効なクエリーで `debug` レベルの時に、 `queriesLog` スコープでログ出力されます。

quoteIdentifiers  
あなたがテーブルやカラム名に予約語や特殊文字を使用している場合は `true` に設定します。
この設定を有効にすると、SQL を生成する際に [クエリービルダー](../orm/query-builder)
によって引用符で囲まれたクエリーが生成されます。
これはクエリーを実行する前に横断的に処理を行う必要があるため、パフォーマンスを
低下させることに注意してください。

flags  
ベースになる PDO のインスタンスに引き継がれる、 PDO 定数の連想配列。
flags がサポートしている内容については、お使いの PDO ドライバーのマニュアルを
ご覧ください。

cacheMetadata  
boolean 型の `true` か、メタデータを格納するキャッシュ設定の文字列のどちらか。
メタデータのキャッシュをオフにする事はお勧めしませんし、パフォーマンスがとても
悪化します。詳細は [Database Metadata Cache](#database-metadata-cache) のセクションを
参照してください。

mask  
生成されたデータベースファイルのパーミッションをセットします。 (SQLite のみでサポートされています)

この時点で、あなたは [CakePHP の規約](../intro/conventions) を見たいと思うかもしれません。
正しいテーブル名（といくつかのカラムの追加）によって、いくつかの機能を獲得して、
設定を回避することができます。
例えば、もしデータベースのテーブル名が big_boxes でしたら、 テーブルクラス
BigBoxesTable と、コントローラー BigBoxesController は、全て自動的に一緒に
動作します。
慣例としてデータベースのテーブル名は、例えば bakers, pastry_stores, savory_cakes
といった具合に、アンダースコアー区切り・小文字・複数形とします。

## コネクションの管理

`class` Cake\\Datasource\\**ConnectionManager**

`ConnectionManager` クラスは、あなたのアプリケーションがデータベース接続に
アクセスするためのレジストリーとして機能します。
これは他のオブジェクトが既存のコネクションへの参照を取得するための場所を提供します。

### コネクションへのアクセス

一度設定した接続は、 `Cake\Datasource\ConnectionManager::get()` を
使って取り出すことができます。
このメソッドはすでに確立しているコネクションを返すか、もしまだ接続していないのであれば
ロードして接続してから返します。 :

``` php
use Cake\Datasource\ConnectionManager;

$conn = ConnectionManager::get('default');
```

存在しない接続をロードしようとしたら、例外を throw します。

### 実行時にコネクションを生成する

`config()` や `get()` を使用して、実行時に設定ファイルに定義されていない
コネクションを生成することができます。 :

``` php
ConnectionManager::config('my_connection', $config);
$conn = ConnectionManager::get('my_connection');
```

コネクション生成時の設定についての詳細は [Database Configuration](#database-configuration) を参照してください。

## データの型

`class` Cake\\Database\\**Type**

各ベンダーのデータベースは全て同じデータ型を持つわけではなく、似たようなデータ型が
同じ名前になっているわけでもありませんので、 CakePHP ではデータベース層で使用するために
基本的なデータ型のセットを提供しています。CakePHP がサポートしている型は、

string  
一般的に CHAR または VARCHAR のカラムが指定されます。
`fixed` オプションを使うと、強制的に CHAR カラムとなります。
SQL Server では、NCHAR と NVARCHAR 型になります。

text  
TEXT 型に変換します。

uuid  
データベースがサポートするなら UUID 型に、さもなければ CHAR(36) に変換します。

integer  
データベースがサポートする INTEGER 型に変換します。現時点では、
BIT はサポートしていません。

smallinteger  
データベースによって提供される SMALLINT 型に変換します。

tinyinteger  
データベースによって提供される TINYINT 型か SMALLINT 型に変換します。
MySQL の `TINYINT(1)` は、 boolean として扱われます。

biginteger  
データベースによって提供される BIGINT 型に変換します。

float  
データベースに応じて DOUBLE 型か FLOAT 型に変換されます。
精度（小数点以下桁数）を指定するために `precision` オプションを使うことができます。

decimal  
DECIMAL 型に変換されます。 `length` と `precision` オプションをサポート
します。

boolean  
BOOLEAN に変換します。MySQL の場合は TINYINT(1) になります。現時点では、
BIT(1) はサポートしていません。

binary  
データベースによって提供される BLOB 型または BYTEA 型に変換します。

date  
タイムゾーン情報を持たない DATE 型に変換されます。この型の戻り値は、ネイティブな
`DateTime` クラスを拡張した `Cake\I18n\Date` です。

datetime  
タイムゾーン情報を持たない DATETIME 型に変換されます。
PostgreSQL と SQL Server では、TIMESTAMP 型に変換されます。
この型のデフォルトの戻り値は、組込みの `DateTime` クラスと
[Chronos](https://github.com/cakephp/chronos) を拡張した
`Cake\I18n\Time` クラスになります。

timestamp  
TIMESTAMP 型に変換します。

time  
全てのデータベースで TIME 型に変換します。

json  
可能であれば、JSON 型に変換し、そうでなければ TEXT 型に変換します。
'json' 型は 3.3.0 で追加されました。

これらの型は、テストフィクスデャーを使用している時に、CakePHP が提供する
スキーマリフレクション機能とスキーマ生成機能の両方で使用されます。

また、各型は PHP と SQL の表現の変換を行う機能も提供します。
これらのメソッドはクエリー実行時に型のヒントに基づいて呼び出されます。
例えば、 'datetime' という名前の項目なら、入力パラメーターを自動的に `DateTime` から
timestamp か 整形した日付文字列に変換します。
同様に 'binary' という名前の項目ならファイルハンドラを受け入れ、データを読み込むときには
ファイルハンドラを生成します。

::: info Changed in version 3.3.0
`json` 型が追加されました。
:::

::: info Changed in version 3.5.0
`smallinteger` 型と `tinyinteger` 型が追加されました。
:::

### 独自の型を作成する

もしあなたが CakePHP に実装されていない、データベース独自の型が必要な場合、
CakePHP の型システムに新たな型を追加することができます。
Type クラスは次のメソッドを実装することが期待されます。

- `toPHP`: 与えられた値をデータベース型から PHP で等価な値にキャストします。
- `toDatabase`: 与えられた値を PHP 型からデータベースで受け入れ可能な値にキャストします。
- `toStatement`: 与えられた値をステートメントの型にキャストします。
- `marshal`: フラットデータを PHP オブジェクトに変換します。

基本的なインターフェイスを満たす簡単な方法は、 `Cake\Database\Type` を
拡張することです。例えば、もしあなたが JSON 型を追加したいなら、下記のような型クラスを
作成します。 :

``` php
// src/Database/Type/JsonType.php の中で

namespace App\Database\Type;

use Cake\Database\Driver;
use Cake\Database\Type;
use PDO;

class JsonType extends Type
{

    public function toPHP($value, Driver $driver)
    {
        if ($value === null) {
            return null;
        }
        return json_decode($value, true);
    }

    public function marshal($value)
    {
        if (is_array($value) || $value === null) {
            return $value;
        }
        return json_decode($value, true);
    }

    public function toDatabase($value, Driver $driver)
    {
        return json_encode($value);
    }

    public function toStatement($value, Driver $driver)
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }
        return PDO::PARAM_STR;
    }

}
```

デフォルトでは `toStatement()` メソッドは新しい型の値を文字列として扱います。
私たちは新しい型を作成したら、型マッピングに追加しなければなりません。
アプリケーションの bootstrap 時に、次の事を行います。 :

``` php
use Cake\Database\Type;

Type::map('json', 'App\Database\Type\JsonType');
```

::: info Added in version 3.3.0
この例で記述された JsonType は、コアに追加されました。
:::

こうすればスキーマ情報は新しい型で上書きされ、CakePHP のデータベース層は自動的に
JSON データを変換してクエリーを作成します。
あなたは Table の [\_initializeSchema() メソッド](../orm/saving-data#saving-complex-types) で、
新たに作った型のマッピングをすることができます。 :

``` php
use Cake\Database\Schema\TableSchema;

class WidgetsTable extends Table
{

    protected function _initializeSchema(TableSchema $schema)
    {
        $schema->columnType('widget_prefs', 'json');
        return $schema;
    }

}
```

### 独自データ型から SQL 表現への変換

::: info Added in version 3.3.0
独自データ型から SQL 表現への変換のサポートは 3.3.0 で追加されました。
:::

前の例は、SQL 文の文字列として表現しやすい 'json' カラム型のための独自データ型に変換します。
複雑な SQL データ型は、SQL クエリーの文字列や整数として表現することはできません。
これらのデータ型を動作させる際、あなたの Type クラスは、
`Cake\Database\Type\ExpressionTypeInterface` インスタンスを実装する必要があります。
例として、MySQL の `POINT` 型データのためのシンプルな Type クラスを作成します。
最初に、PHP の `POINT` データを表現するために使用する「値」オブジェクトを定義します。 :

``` php
// src/Database/Point.php の中で
namespace App\Database;

// 値オブジェクトはイミュータブルです。
class Point
{
    protected $_lat;
    protected $_long;

    // ファクトリーメソッド
    public static function parse($value)
    {
        // MySQL からのデータをパース
        return new static($value[0], $value[1]);
    }

    public function __construct($lat, $long)
    {
        $this->_lat = $lat;
        $this->_long = $long;
    }

    public function lat()
    {
        return $this->_lat;
    }

    public function long()
    {
        return $this->_long;
    }
}
```

値オブジェクトを作成することで、この値オブジェクトや SQL 表現にデータを変換する
Type クラスが必要になります。 :

``` php
namespace App\Database\Type;

use App\Database\Point;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\Type as BaseType;
use Cake\Database\Type\ExpressionTypeInterface;

class PointType extends BaseType implements ExpressionTypeInterface
{
    public function toPHP($value, Driver $d)
    {
        return Point::parse($value);
    }

    public function marshal($value)
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (is_array($value)) {
            return new Point($value[0], $value[1]);
        }
        return null;
    }

    public function toExpression($value)
    {
        if ($value instanceof Point) {
            return new FunctionExpression(
                'POINT',
                [
                    $value->lat(),
                    $value->long()
                ]
            );
        }
        if (is_array($value)) {
            return new FunctionExpression('POINT', [$value[0], $value[1]]);
        }
        // その他のケースを処理
    }
}
```

上記のクラスは、いくつかの興味深い特徴があります。

- `toPHP` メソッドは、SQL クエリーの結果を値オブジェクトにパースします。
- `marchal` メソッドは、例えばリクエストデータで与えられたデータから値オブジェクトへ
  変換します。 `'10.24,12.34` のような文字列や配列を受け取れるようにしています。
- `toExpression` メソッドは、値オブジェクトから同等の SQL 表現へ変換します。
  例えば、結果の SQL は、 `POINT(10.24, 12.34)` のようになります。

一度独自の型を作成したら、 [独自の型をテーブルクラスと関連づける](../orm/saving-data#saving-complex-types)
必要があります。

### イミュータブル DateTime オブジェクトの有効化

::: info Added in version 3.2
イミュータブル date/time オブジェクトは、 3.2 で追加されました。
:::

Date/Time オブジェクトは容易に変更されてしまうため、CakePHP はイミュータブルな
オブジェクトを利用できるようなっています。以下の設定は、 あなたのアプリケーションの
**config/bootstrap.php** ファイル内で行うのが最適です。 :

``` php
Type::build('datetime')->useImmutable();
Type::build('date')->useImmutable();
Type::build('time')->useImmutable();
Type::build('timestamp')->useImmutable();
```

> [!NOTE]
> 新しいアプリケーションは、デフォルトでイミュータブルオブジェクトが有効になります。

## Connection クラス

`class` Cake\\Database\\**Connection**

Connection クラスは、一貫性のある方法でデータベースコネクションと対話するための
シンプルなインターフェイスを提供します。
これはドライバー層への基底インターフェイスであり、クエリーの実行、クエリーのロギング、
トランザクション処理といった機能を提供するためのものです。

### クエリーの実行

`method` Cake\\Database\\Connection::**query**($sql)

あなたがコネクションオブジェクトを取得したら、恐らく何らかのクエリーを発行したくなるでしょう。
CakePHP のデータベース抽象化レイヤは、PDO とネイティブドライバー上にラッパー機能を提供します。
これらのラッパーは PDO と似たようなインターフェイスを提供します。
クエリーを実行する方法は、あなたが実行したいクエリーと取得したい結果の種類に応じて
いくつかあります。
もっとも基本的な方法は、完全な SQL クエリーの実行を可能にする `query()` です。 :

``` php
$stmt = $conn->query('UPDATE articles SET published = 1 WHERE id = 2');
```

`method` Cake\\Database\\Connection::**execute**($sql, $params, $types)

`query()` メソッドは追加パラメーターを受け付けません。もし追加パラメーターが必要なら、
プレースホルダーを使用可能な `execute()` メソッドを使用します。 :

``` php
$stmt = $conn->execute(
    'UPDATE articles SET published = ? WHERE id = ?',
    [1, 2]
);
```

型に関する情報がない場合は、 `execute` は全てのプレースホルダーを文字列とみなします。
もし特定の型にバインドする必要があるなら、クエリーを生成する時に型名を指定することが
できます。 :

``` php
$stmt = $conn->execute(
    'UPDATE articles SET published_date = ? WHERE id = ?',
    [new DateTime('now'), 2],
    ['date', 'integer']
);
```

`method` Cake\\Database\\Connection::**newQuery**()

これはあなたのアプリケーションで豊富なデータ型を使用し、適切に SQL 文に変換することができます。
クエリーを作成する最後の、そして最も柔軟な方法は、 [クエリービルダー](../orm/query-builder) を
使用することです。
この方法では、プラットフォーム固有の SQL を使用することなく、複雑で表現力豊かなクエリーを
構築することができます。 :

``` php
$query = $conn->newQuery();
$query->update('articles')
    ->set(['published' => true])
    ->where(['id' => 2]);
$stmt = $query->execute();
```

クエリービルダーを使用する場合は、 `execute()` メソッドを呼ぶまではサーバーに SQL は
送信されず、メソッド呼び出し後に順次処理されます。
最初に送信してから、順次結果セットを作成します。 :

``` php
$query = $conn->newQuery();
$query->select('*')
    ->from('articles')
    ->where(['published' => true]);

foreach ($query as $row) {
    // 行に何かする
}
```

> [!NOTE]
> もし `Cake\ORM\Query` のインスタンスを生成しているのなら、
> SELECT クエリーの結果セットを取得するのに `all()` を使用できます。

### トランザクションを使う

コネクションオブジェクトは、データベーストランザクションを行うためのいくつかの簡単な
方法を提供します。
トランザクション操作の最も基本的な方法は、SQL構文と同じような `begin()` ,
`commit()` , `rollback()` を使用するものです。 :

``` php
$conn->begin();
$conn->execute('UPDATE articles SET published = ? WHERE id = ?', [true, 2]);
$conn->execute('UPDATE articles SET published = ? WHERE id = ?', [false, 4]);
$conn->commit();
```

`method` Cake\\Database\\Connection::**transactional**(callable $callback)

このコネクションインスタンスへのインターフェースに加えて、さらに begin/commit/rollback を
簡単にハンドリングする `transactional()` メソッドが提供されています。 :

``` php
$conn->transactional(function ($conn) {
    $conn->execute('UPDATE articles SET published = ? WHERE id = ?', [true, 2]);
    $conn->execute('UPDATE articles SET published = ? WHERE id = ?', [false, 4]);
});
```

基本的なクエリーに加えて、 [クエリービルダー](../orm/query-builder) または [テーブルオブジェクト](../orm/table-objects) の
いずれかを使用してより複雑なクエリーを実行することができます。
トランザクションメソッドは下記のことを実行します。

- `begin` を呼び出します。
- 引数で渡されたクロージャーを実行します。
- もしクロージャー内で例外が発生したら、ロールバックを発行して例外を再度 throw します。
- クロージャーが `false` を返したら、ロールバックを発行して false を返します。
- クロージャーが正常終了したら、トランザクションをコミットします。

## ステートメントとの対話

基底レベルのデータベース API を使用していると、ステートメントオブジェクトが
よく出てきます。
これらのオブジェクトで、ドライバーから基になるプリペアードステートメントを操作できるように
なります。
クエリーオブジェクトを生成し実行するか `execute()` を実行した後、あなたは
`StatementDecorator` インスタンスを持つ事になります。
これはベースとなる基本的なステートメントオブジェクトをラップして、追加の機能を
提供します。

### ステートメントを準備する

あなたは `execute()` か `prepare()` でステートメントオブジェクトを生成できます。
`execute()` メソッドは引き継いだ値をバインドしたステートメントを返します。
それに対して `prepare()` は不完全なステートメントを返します。 :

``` php
// execute は指定された値でバインドして SQL ステートメントを実行します。
$stmt = $conn->execute(
    'SELECT * FROM articles WHERE published = ?',
    [true]
);

// prepare はプレースホルダーのための準備をします。
// 実行する前にパラメーターをバインドする必要があります。
$stmt = $conn->prepare('SELECT * FROM articles WHERE published = ?');
```

SQL 文を準備したら、あなたは追加のデータをバインドし、それを実行することができます。

### 値をバインドする

プリペアードステートメントを作成したら、追加のデータをバインドする必要があります。
あなたは `bind()` メソッドを使って一度に複数の値をバインドする事も、
`bindValue` を使って１項目ずつバインドする事もできます。 :

``` php
$stmt = $conn->prepare(
    'SELECT * FROM articles WHERE published = ? AND created > ?'
);

// 複数項目のバインド
$stmt->bind(
    [true, new DateTime('2013-01-01')],
    ['boolean', 'date']
);

// １項目ずつのバインド
$stmt->bindValue(1, true, 'boolean');
$stmt->bindValue(2, new DateTime('2013-01-01'), 'date');
```

ステートメントを作成する時には、項目の通し番号ではなく、項目名の配列をキーに
使用することもできます。 :

``` php
$stmt = $conn->prepare(
    'SELECT * FROM articles WHERE published = :published AND created > :created'
);

// 複数項目のバインド
$stmt->bind(
    ['published' => true, 'created' => new DateTime('2013-01-01')],
    ['published' => 'boolean', 'created' => 'date']
);

// １項目ずつのバインド
$stmt->bindValue('published', true, 'boolean');
$stmt->bindValue('created', new DateTime('2013-01-01'), 'date');
```

> [!WARNING]
> 同じステートメント内で、項目の通し番号と項目名のキーを混在させることはできません。

### 実行と結果行の取得

プリペアードステートメントを作成してデータをバインドしたら、実行して行フェッチすることが
できます。
ステートメントは `execute()` メソッドで実行します。
一度実行したら、結果は `fetch()` か `fetchAll()` を使ってフェッチします。 :

``` php
$stmt->execute();

// １行読み込む
$row = $stmt->fetch('assoc');

// 全行を読み込む
$rows = $stmt->fetchAll('assoc');

// 全行読み込んだ結果を順次処理する
foreach ($stmt as $row) {
    // Do work
}
```

> [!NOTE]
> 読み込んだフェッチする時には、２つのモードを使用することができます。
> 結果配列のキーを項目の通番にする場合 (num) と、項目名をキーにする場合 (assoc) です。

### 行数を取得する

ステートメントを実行したら、下記のように対象行数を取得することができます。 :

``` php
$rowCount = count($stmt);
$rowCount = $stmt->rowCount();
```

### エラーコードをチェックする

あなたのクエリーが成功しなかった場合は、エラー関連情報を `errorCode()` と `errorInfo()`
メソッドによって取得することができます。
このメソッドは PDO で提供されているものと同じように動作します。 :

``` php
$code = $stmt->errorCode();
$info = $stmt->errorInfo();
```

<div class="todo">

Possibly document CallbackStatement and BufferedStatement

</div>

## クエリーロギング

あなたのコネクションを設定する時に、 `log` オプションに `true` をセットすると
クエリーのログを有効にすることができます。
また、 `logQueries` を使って実行中にクエリーログを切り替えることができます。 :

``` php
// クエリーログを有効
$conn->logQueries(true);

// クエリーログを停止
$conn->logQueries(false);
```

クエリーログを有効にしていると、 'debug' レベルで 'queriesLog' スコープで
`Cake\Log\Log` にクエリーをログ出力します。
あなたはこのレベル・スコープを出力するようにログ設定をする必要があります。
`stderr` にログ出力するのはユニットテストの時に便利で、files/syslog に出力するのは
Web リクエストの時に便利です。 :

``` php
use Cake\Log\Log;

// Console logging
Log::config('queries', [
    'className' => 'Console',
    'stream' => 'php://stderr',
    'scopes' => ['queriesLog']
]);

// File logging
Log::config('queries', [
    'className' => 'File',
    'path' => LOGS,
    'file' => 'queries.log',
    'scopes' => ['queriesLog']
]);
```

> [!NOTE]
> クエリーログは、デバッグまたは開発用途での利用を想定しています。
> アプリケーションのパフォーマンスに悪影響を及ぼしますので、公開サイトでは
> 利用すべきではありません。

## 引用識別子

デフォルトの CakePHP では、生成される SQL 文は引用符で囲まれて **いません** 。
その理由は、引用識別子はいくつかの問題があるためです。

- パフォーマンスへの負荷 - 引用符を使うと、使わない時よりずっと遅く、複雑になります。
- ほとんどの場合に不要 - CakePHP の規約に従う新しいデータベースでは、引用符で囲む必要はありません。

もしあなたが引用符が必要な古いスキーマを使用しているなら、
[データベースの設定](#database-configuration) で `quoteIdentifiers` を設定すると
引用符を使うことができます。
また、実行時にこの機能を有効にすることもできます。 :

``` php
$conn->getDriver()->enableAutoQuoting();
```

有効にすると、引用識別子は 全ての識別子を `IdentifierExpression` オブジェクトに
変換するトラバーサルが発生する原因になります。

> [!NOTE]
> QueryExpression オブジェクトに含まれる SQL スニペットは変換されません。

## メタデータ・キャッシング

CakePHP の ORM は、あなたのアプリケーションのスキーマ、インデックス、外部キーを
決定するために、データベースリフレクションを使用します。
このメタデータは頻繁に変更され、アクセスにコストがかかるため、一般的にキャッシュされます。
デフォルトでは、メタデータは `_cake_model_` キャッシュ設定に保存されます。
あなたはデータベース設定の `cacheMetatdata` オプションを使って
カスタムキャッシュ設定を定義することができます。 :

    'Datasources' => [
        'default' => [
            // その他のキーはここに書く

            // メタデータのキャッシュ設定に'orm_metadata'を使用
            'cacheMetadata' => 'orm_metadata',
        ]
    ],

実行時に `cacheMetadata()` メソッドを使ってメタデータのキャッシュを
設定することもできます。 :

``` php
// キャッシュを無効化
$connection->cacheMetadata(false);

// キャッシュを有効化
$connection->cacheMetadata(true);

// カスタムキャッシュ設定を利用
$connection->cacheMetadata('orm_metadata');
```

CakePHP にはメタデータキャッシュを管理するための CLI ツールも同梱しています。
詳細については [スキーマキャッシュシェル](../console-and-shells/schema-cache) を参照してください。

## データベースの作成

もし、データベースを選択せずに接続したい場合、データベース名を省略してください。 :

``` php
$dsn = 'mysql://root:password@localhost/';
```

これでデータベースの作成や変更のクエリーを実行するためにコネクションオブジェクトが使えます。 :

``` php
$connection->query("CREATE DATABASE IF NOT EXISTS my_database");
```

> [!NOTE]
> データベースを作成する場合、文字コードや照合順序をセットすることをお勧めします。
> もしこれらの値がなかった場合、データベースはシステムのデフォルト値をセットします。
