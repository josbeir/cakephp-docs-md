# エラーと例外の処理

CakePHP アプリケーションには、エラー処理と例外処理が用意されています。
PHP エラーはトラップされ、表示またはログに記録されます。
キャッチされなかった例外はエラーページに自動的にレンダリングされます。

## エラーと例外の設定

エラーの設定はアプリケーションの **config/app.php** ファイル中で行われます。デフォルトでは、
CakePHP は PHP エラーと例外の両方を処理するために `Cake\Error\ErrorHandler` を使います。
エラーの設定を使用すると、アプリケーションのエラー処理をカスタマイズできます。
次のオプションをサポートします。

- `errorLevel` - int - あなたが捕捉したいエラーレベル。組み込みの PHP エラー定数を使い、
  捕捉したいエラーレベルを選択するためにビットマスクします。非推奨の警告を無効にするために、
  `E_ALL ^ E_USER_DEPRECATED` をセットすることができます。
- `trace` - bool - ログファイル中にエラーのスタックトレースを含めます。
  スタックトレースはログ中の各エラーの後に含まれるでしょう。
  これはどこで／いつそのエラーが引き起こされたかを見つけるために役に立ちます。
- `exceptionRenderer` - string - キャッチされなかった例外を描画する役目を担うクラス。
  もしもカスタムクラスを選択する場合は **src/Error** 中にそのクラスのファイルを置くべきです。
  このクラスは `render()` メソッドを実装する必要があります。
- `log` - bool - `true` の時、 `Cake\Log\Log` によって例外と
  そのスタックトレースが `Cake\Log\Log` に記録されます。
- `skipLog` - array - ログに記録されるべきではない例外クラス名の配列。
  これは NotFoundException や他のありふれた、でもログにはメッセージを残したくない例外を
  除外するのに役立ちます。
- `extraFatalErrorMemory` - int - 致命的エラーが起きた時にメモリーの上限を増加させるための
  メガバイト数を設定します。これはログの記録やエラー処理を完了するために猶予を与えます。

エラーハンドラーは既定では、 `debug` が `true` の時にエラーを表示し、
`debug` が `false` の時にエラーをログに記録します。
いずれも捕捉されるエラータイプは `errorLevel` によって制御されます。
致命的エラーのハンドラーは `debug` レベルや `errorLevel` とは独立して呼び出されますが、
その結果は `debug` レベルによって変わるでしょう。
致命的エラーに対する既定のふるまいは内部サーバーエラーページ (`debug` 無効)
またはエラーメッセージ、ファイルおよび行を含むページ (`debug` 有効) を表示します。

> [!NOTE]
> もしカスタムエラーハンドラーを使うなら、サポートされるオプションはあなたのハンドラーに依存します。

`class` **ExceptionRenderer**(Exception $exception)

## 例外処理の変更

例外処理では、例外の処理方法を調整するいくつかの方法が用意されています。
それぞれのアプローチでは、例外処理プロセスの制御量が異なります。

1.  *エラーテンプレートのカスタマイズ* 描画されたビューテンプレートを
    アプリケーション内の他のテンプレートと同様に変更できます。
2.  *ErrorController のカスタマイズ* 例外ページの描画方法を制御できます。
3.  *ExceptionRenderer のカスタマイズ* 例外ページとロギングの実行方法を制御できます。
4.  *独自のエラーハンドラーの作成と登録* エラーと例外がどのように処理され、記録され、
    描画されるかを完全に制御することができます。

## エラーテンプレートのカスタマイズ

デフォルトのエラーハンドラは、 `Cake\Error\ExceptionRenderer` とアプリケーションの
`ErrorController` の助けを借りて、アプリケーションで発生した全ての捕捉されない例外を描画します。

エラーページのビューは **src/Template/Error/** に配置されます。デフォルトでは、
すべての 4xx エラーは **error400.ctp** テンプレートを使い、
すべての 5xx エラーは **error500.ctp** を使います。
エラーテンプレートの変数は次のとおりです。

- `message` 例外メッセージ。
- `code` 例外コード。
- `url` リクエスト URL。
- `error` 例外オブジェクト。

デバッグモードでエラーが `Cake\Core\Exception\Exception` を継承した場合、
`getAttributes()` によって返されたデータはビュー変数としても公開されます。

> [!NOTE]
> **error404** と **error500** テンプレートを表示するには `debug` を false に
> 設定する必要があります。デバッグモードだと、 CakePHP の開発用エラーページが表示されます。

### エラーページレイアウトのカスタマイズ

デフォルトでは、エラーテンプレートは、レイアウトに **src/Template/Layout/error.ctp** を使います。
`layout` プロパティーを使って別のレイアウトを選ぶことができます。 :

``` php
// src/Template/Error/error400.ctp の中で
$this->layout = 'my_error';
```

上記は、エラーページのレイアウトとして **src/Template/Layout/my_error.ctp** を使用します。
CakePHP によって引き起こされる多くの例外は、特定のビューテンプレートをデバッグモードで描画します。
デバッグをオフにすると、CakePHP によって生成されたすべての例外は、ステータスコードに基づいて
**error400.ctp** または **error500.ctp** のいずれかを使用します。

## ErrorController のカスタマイズ

`App\Controller\ErrorController` クラスは CakePHP の例外レンダリングでエラーページビューを
描画するために使われ、すべての標準リクエストライフサイクルイベントを受け取ります。
このクラスを変更することで、どのコンポーネントが使用され、どのテンプレートが描画されるかを制御できます。

アプリケーション内で [プレフィックスルーティング](#プレフィックスルーティング) を利用している場合は、
それぞれのルーティングプレフィックスに対してカスタムエラーコントローラーを作成できます。
例えば、 `Admin` プレフィックスの場合は以下のクラスを作成することができます。 :

``` php
namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

class ErrorController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->loadComponent('RequestHandler');
    }

    /**
     * beforeRender callback.
     *
     * @param \Cake\Event\EventInterface $event Event.
     * @return void
     */
    public function beforeRender(EventInterface $event)
    {
        $this->viewBuilder()->setTemplatePath('Error');
    }
}
```

このコントローラーは、プレフィックス付きのコントローラーでエラーが発生したときにのみ利用できます。
そして、必要に応じてプレフィックス固有のロジック/テンプレートを定義できます。

## ExceptionRenderer の変更

例外レンダリングとロギングプロセス全体を制御したい場合は **config/app.php** の
`Error.exceptionRenderer` オプションを使用して、例外ページをレンダリングするクラスを
選択することができます。ExceptionRenderer の変更は、アプリケーション固有の
例外クラスに対してカスタムエラーページを提供する場合に便利です。

カスタム例外レンダラークラスは **src/Error** に配置する必要があります。
アプリケーションで `App\Exception\MissingWidgetException` を使用して欠落している
ウィジェットを示すとしましょう。このエラーが処理されたときに特定のエラーページを
レンダリングする例外レンダラーを作成することができます。 :

``` php
// src/Error/AppExceptionRenderer.php の中で
namespace App\Error;

use Cake\Error\ExceptionRenderer;

class AppExceptionRenderer extends ExceptionRenderer
{
    public function missingWidget($error)
    {
        $response = $this->controller->response;
        return $response->withStringBody('おっとウィジェットが見つからない！');
    }
}

// config/app.php の中で
'Error' => [
    'exceptionRenderer' => 'App\Error\AppExceptionRenderer',
    // ...
],
// ...
```

上記は `MissingWidgetException` 型のあらゆる例外を処理し、
それらのアプリケーション例外を表示／処理するためのカスタム処理ができるようにします。

例外レンダリングメソッドは、引数として処理される例外を受け取り、
`Response` オブジェクトを返さなければなりません。
また、CakePHP のエラーを処理する際にロジックを追加するメソッドを実装することもできます。 :

``` php
// src/Error/AppExceptionRenderer.php の中で
namespace App\Error;

use Cake\Error\ExceptionRenderer;

class AppExceptionRenderer extends ExceptionRenderer
{
    public function notFound($error)
    {
        // NotFoundException オブジェクトで何かをします。
    }
}
```

### ErrorController クラスの変更

例外レンダラーは、例外の描画に使用されるコントローラーを指定します。
例外を描画するコントローラーを変更したい場合は、例外レンダラーの
`_getController()` メソッドをオーバーライドしてください。 :

``` php
// src/Error/AppExceptionRenderer の中で
namespace App\Error;

use App\Controller\SuperCustomErrorController;
use Cake\Error\ExceptionRenderer;

class AppExceptionRenderer extends ExceptionRenderer
{
    protected function _getController()
    {
        return new SuperCustomErrorController();
    }
}

// config/app.php の中で
'Error' => [
    'exceptionRenderer' => 'App\Error\AppExceptionRenderer',
    // ...
],
// ...
```

## 独自エラーハンドラーの作成

エラーハンドラーを置き換えることによって、エラーおよび例外処理プロセス全体をカスタマイズできます。
`Cake\Error\BaseErrorHandler` を継承することでエラーを処理するためのカスタムロジックを提供できます。
たとえば、エラーを処理するために `AppError` というクラスを使うことができます。 :

``` php
// config/bootstrap.php の中で
use App\Error\AppError;

$errorHandler = new AppError();
$errorHandler->register();

// src/Error/AppError.php の中で
namespace App\Error;

use Cake\Error\BaseErrorHandler;

class AppError extends BaseErrorHandler
{
    public function _displayError($error, $debug)
    {
        echo 'エラーがありました！';
    }

    public function _displayException($exception)
    {
        echo '例外がありました！';
    }
}
```

`BaseErrorHandler` は二つの抽象メソッドを定義しています。
`_displayError()` はエラーが引き起こされた時に使われます。
`_displayException()` メソッドはキャッチされなかった例外がある時に呼ばれます。

### 致命的エラーのふるまい変更

既定のエラーハンドラーは致命的エラーを例外に変換し
エラーページを描画するための例外処理方法を再利用します。
もし標準のエラーページを表示したくない場合は、あなたはそれをオーバーライドできます。 :

``` php
// src/Error/AppError.php の中で
namespace App\Error;

use Cake\Error\BaseErrorHandler;

class AppError extends BaseErrorHandler
{
    // 他のメソッド

    public function handleFatalError($code, $description, $file, $line)
    {
        echo '致命的エラーが発生しました';
    }
}
```

<div class="index">

application exceptions

</div>

## 独自アプリケーション例外の作成

組み込みの [SPL の例外](https://php.net/manual/en/spl.exceptions.php) 、
`Exception` そのもの、または `Cake\Core\Exception\Exception`
のいずれかを使って、独自のアプリケーション例外を作ることができます。
もしアプリケーションが以下の例外を含んでいたなら:

``` php
use Cake\Core\Exception\Exception;

class MissingWidgetException extends Exception
{
}
```

**src/Template/Error/missing_widget.ctp** を作ることで、素晴らしい開発用エラーを提供できるでしょう。
本番モードでは、上記のエラーは 500 エラーとして扱われ、 **error500** テンプレートを使用するでしょう。

例外コードが `400` と `506` の間にある場合、例外コードは HTTP レスポンスコードとして使用されます。

`Cake\Core\Exception\Exception` のコンストラクターが継承されており、
追加のデータを渡すことができます。それら追加のデータは `_messageTemplate` に差し込まれます。
これにより、エラー用の多くのコンテキスト提供して、データ豊富な例外を作ることができます。 :

``` php
use Cake\Core\Exception\Exception;

class MissingWidgetException extends Exception
{
    // コンテキストデータはこのフォーマット文字列に差し込まれます。
    protected $_messageTemplate = '%s が見当たらないようです。';

    // デフォルトの例外コードも設定できます。
    protected $_defaultCode = 404;
}

throw new MissingWidgetException(['widget' => 'Pointy']);
```

レンダリングされると、このビューテンプレートには `$widget` 変数が設定されます。
もしその例外を文字列にキャストするかその `getMessage()` メソッドを使うと
`Pointy が見当たらないようです。` を得られるでしょう。

### 例外のログ記録

組み込みの例外処理を使うと、 **config/app.php** 中で `log` オプションに `true` を設定することで
ErrorHandler によって対処されるすべての例外をログに記録することができます。
これを有効にすることで `Cake\Log\Log` と設定済みのロガーに各例外の記録が残るでしょう。

> [!NOTE]
> もしもカスタム例外ハンドラーを使用している場合、
> あなたの実装の中でそれを参照しない限り、この設定は効果がないでしょう。

## CakePHP 用の組み込みの例外

### HTTP の例外

CakePHP 内部のいくつかの組み込みの例外には、内部的なフレームワークの例外の他に、
HTTP メソッド用のいくつかの例外があります。

> 400 Bad Request エラーに使われます。
>
> 401 Unauthorized エラーに使われます。
>
> 403 Forbidden エラーに使われます。

::: info Added in version 3.1
InvalidCsrfTokenException が追加されました。無効な CSRF トークンによって引き起こされた 403 エラーに使われます。404 Not Found エラーに使われます。405 Method Not Allowed エラーに使われます。406 Not Acceptable エラーに使われます。409 Conflict エラーに使われます。410 Gone エラーに使われます。
:::

HTTP 4xx エラーステータスコードの詳細は `2616#section-10.4` をご覧ください。

> 500 Internal Server Error に使われます。
>
> 501 Not Implemented エラーに使われます。
>
> 503 Service Unavailable エラーに使われます。
>
> ::: info Added in version 3.1.7
> Service Unavailable が追加されました。
> :::

HTTP 5xx エラーステータスコードの詳細は `2616#section-10.5` をご覧ください。

失敗の状態や HTTP エラーを示すためにあなたのコントローラーからこれらの例外を投げることができます。
HTTP の例外の使用例はアイテムが見つからなかった場合に 404 ページを描画することでしょう。 :

``` php
// 3.6 より前は Cake\Network\Exception\NotFoundException を使用
use Cake\Http\Exception\NotFoundException;

public function view($id = null)
{
    $article = $this->Articles->findById($id)->first();
    if (empty($article)) {
        throw new NotFoundException(__('記事が見つかりません'));
    }
    $this->set('article', $article);
    $this->set('_serialize', ['article']);
}
```

HTTP エラー用の例外を使うことで、あなたのコードを綺麗にし、
かつ RESTful なレスポンスをアプリケーションのクライアントやユーザーに返すことができます。

### コントローラー中での HTTP の例外の使用

失敗の状態を示すためにコントローラーのアクションからあらゆる
HTTP 関連の例外を投げることができます。例:

``` php
use Cake\Network\Exception\NotFoundException;

public function view($id = null)
{
    $article = $this->Articles->findById($id)->first();
    if (empty($article)) {
        throw new NotFoundException(__('記事が見つかりません'));
    }
    $this->set('article', 'article');
    $this->set('_serialize', ['article']);
}
```

上記は `NotFoundException` をキャッチして処理するための例外ハンドラーを設定するでしょう。
デフォルトではエラーページを作り、例外をログに記録するでしょう。

### その他の組み込みの例外

さらに、CakePHP は次の例外を使用します。

> 選択されたビュークラスが見つかりません。
>
> 選択されたテンプレートファイルが見つかりません。
>
> 選択されたレイアウトが見つかりません。
>
> 選択されたヘルパーが見つかりません。
>
> 選択されたエレメントのファイルが見つかりません。
>
> 選択されたセルクラスが見つかりません。
>
> 選択されたセルのビューファイルが見つかりません。
>
> 設定されたコンポーネントが見つかりません。
>
> 要求されたコントローラーのアクションが見つかりません。
>
> private／protected／\_ が前置されたアクションへのアクセス。
>
> コンソールライブラリークラスがエラーに遭遇しました。
>
> 設定されたタスクが見つかりません。
>
> シェルクラスが見つかりません。
>
> 選択されたシェルクラスが該当の名前のメソッドを持っていません。
>
> モデルの接続がありません。
>
> データベースドライバーが見つかりません。
>
> データベースドライバーのための PHP 拡張がありません。
>
> モデルのテーブルが見つかりません。
>
> モデルのエンティティーが見つかりません。
>
> モデルのビヘイビアーが見つかりません。
>
> `Cake\ORM\Table::saveOrFail()` や
> `Cake\ORM\Table::deleteOrFail()` を使用しましたが、
> エンティティーは、保存/削除されませんでした。
>
> ::: info Added in version 3.4.1
> PersistenceFailedException は追加されました。
> :::
>
> 要求されたレコードが見つかりません。
> これにより HTTP 応答ヘッダーも 404 に設定されます。
>
> 要求されたコントローラーが見つかりません。
>
> 要求された URL はルーティングの逆引きができないか解析できません。
>
> ディスパッチャーフィルターが見つかりません。
>
> CakePHP での基底例外クラス。
> CakePHP によって投げられるすべてのフレームワーク層の例外はこのクラスを継承するでしょう。

これらの例外クラスはすべて `Exception` を継承します。
Exception を継承することにより、あなたは独自の‘フレームワーク’エラーを作ることができます。

`method` Cake\\Core\\Exception\\ExceptionRenderer::**responseHeader**($header = null, $value = null)

すべての Http と Cake の例外は Exception クラスを継承し、
レスポンスにヘッダーを追加するためのメソッドを持っています。
例えば、405 MethodNotAllowdException を投げる時、RFC2616 によると:

    "The response MUST include an Allow header containing a list of valid
    methods for the requested resource."

    「レスポンスは要求されたリソースに有効なメソッドの一覧を含むAllowヘッダーを含まなければ【ならない】」
