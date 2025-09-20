# Http Client

`class` Cake\\Http\\**Client**(mixed $config = [])

CakePHP には、リクエストの実行に使用できる基本的ながら強力な HTTP クライアントが含まれています。
これは、ウェブサービスや、リモート API と通信するための素晴らしい方法です。

::: info Changed in version 3.3.0
3.3.0 より前なら、 `Cake\Network\Http\Client` を使用してください。
:::

## リクエストの実行

リクエストの実行は、シンプルで簡単です。
GET リクエストは、次のようになります。 :

``` php
use Cake\Http\Client;

$http = new Client();

// 単純な GET
$response = $http->get('http://example.com/test.html');

// クエリー文字列を使用した単純な GET
$response = $http->get('http://example.com/search', ['q' => 'widget']);

// クエリー文字列と追加ヘッダーを使用した単純な GET
$response = $http->get('http://example.com/search', ['q' => 'widget'], [
  'headers' => ['X-Requested-With' => 'XMLHttpRequest']
]);
```

POST や PUT のリクエストを実行することは、同様に簡単です。 :

``` php
// application/x-www-form-urlencoded エンコードデータを POST リクエストで送信
$http = new Client();
$response = $http->post('http://example.com/posts/add', [
  'title' => 'testing',
  'body' => 'content in the post'
]);

// application/x-www-form-urlencoded エンコードデータを PUT リクエストで送信
$response = $http->put('http://example.com/posts/add', [
  'title' => 'testing',
  'body' => 'content in the post'
]);

// 他のメソッドも同様に、
$http->delete(...);
$http->head(...);
$http->patch(...);
```

## ファイルを使用したマルチパートリクエストの作成

配列の中にファイルハンドルを含めることで、リクエストのボディー内にファイルを含めることができます。 :

``` php
$http = new Client();
$response = $http->post('http://example.com/api', [
  'image' => fopen('/path/to/a/file', 'r'),
]);
```

ファイルハンドルは、最後まで読まれます。それが読まれる前に巻き戻されることはありません。

> [!WARNING]
> 互換性の理由から、 `@` で始まる文字列は、ローカルまたはリモートのファイルパスとして評価されます。

この機能は、CakePHP 3.0.5 からは非推奨で、将来のバージョンで削除されます。
それまでは、HTTP クライアントに渡されるユーザーデータは、次のようにサニタイズする必要があります。 :

``` php
$response = $http->post('http://example.com/api', [
    'search' => ltrim($this->request->getData('search'), '@'),
]);
```

クエリー文字列の先頭の `@` 文字を維持する必要がある場合は、
`http_build_query()` で予めエンコードされたクエリー文字列を渡すことができます。 :

``` php
$response = $http->post('http://example.com/api', http_build_query([
    'search' => $this->request->getData('search'),
]));
```

### 手動でマルチパートリクエストのボディーを構築

非常に特殊な方法でリクエストボディーを構築しなければならない場合もあるかもしれません。
このような状況では、多くの場合、あなたが望んだ特殊なマルチパートの HTTP リクエストを作るために
`Cake\Http\Client\FormData` を使用することができます。 :

``` php
use Cake\Http\Client\FormData;

$data = new FormData();

// XML 部分を作成
$xml = $data->newPart('xml', $xmlString);
// コンテンツタイプを設定
$xml->type('application/xml');
$data->add($xml);

// addFile() でファイルアップロードの作成
// 同様にフォームデータにファイルを追加します。
$file = $data->addFile('upload', fopen('/some/file.txt', 'r'));
$file->contentId('abc123');
$file->disposition('attachment');

// リクエストの送信
$response = $http->post(
    'http://example.com/api',
    (string)$data,
    ['headers' => ['Content-Type' => $data->contentType()]]
);
```

## リクエストボディーを送信

REST API を扱うとき、多くの場合、フォームエンコードされていないリクエストボディーを送信する必要があります。
Http\Client は、type オプションを介してこれを公開します。 :

``` php
// JSON リクエストボディーの送信
$http = new Client();
$response = $http->post(
  'http://example.com/tasks',
  json_encode($data),
  ['type' => 'json']
);
```

`type` キーは「json」、「xml」または完全な MIME タイプのいずれかになります。
`type` オプションを使用するときは、文字列としてデータを提供してください。
クエリー文字列パラメーターとリクエストボディーの両方を必要とする GET リクエストを行う場合は、
次の操作で行うことができます。 :

``` php
// クエリー文字列パラメーター付きの GET リクエストで JSON ボディーを送信
$http = new Client();
$response = $http->get(
  'http://example.com/tasks',
  ['q' => 'test', '_content' => json_encode($data)],
  ['type' => 'json']
);
```

## リクエストメソッドのオプション

各 HTTP メソッドは、追加のリクエスト情報を提供するための `$options` パラメーターを受け取ります。
以下のキーが `$options` で使用することができます。

- `headers` - 追加ヘッダーの配列。
- `cookie` - 使用するクッキーの配列。
- `proxy` - プロキシー情報の配列。
- `auth` - 認証データの配列、 `type` キーが認証ストラテジーに委譲するために使用されます。
  デフォルトでは、Basic 認証が使用されます。
- `ssl_verify_peer` - デフォルトは `true` 。SSL 証明書の検証を無効にするには
  `false` を設定します（推奨されません）。
- `ssl_verify_peer_name` - デフォルトは `true` 。SSL 証明書を検証する場合、
  ホスト名検証を無効にするには `false` を設定します（推奨されません）。
- `ssl_verify_depth` - デフォルトは 5 。CA チェーンを通過する深さ。
- `ssl_verify_host` - デフォルトは `true` 。ホスト名に対して SSL 証明書を検証します。
- `ssl_cafile` - デフォルトは組み込みの cafile 。カスタム CA バンドルを使用するためには
  上書きしてください。
- `timeout` - 秒単位でタイムアウトするまで待つ時間。
- `type` - 独自のコンテンツタイプでリクエストボディーを送信します。 `$data` を文字列にするか、
  GET リクエストで `_content` オプションを指定する必要があります。
- `redirect` - フォローするリダイレクトの数。デフォルトは `false` です。

オプションのパラメーターは、いつも HTTP メソッドの 3 番目のパラメーターです。
[スコープ指定クライアント](#http_client_scoped_client) を作成するために
`Client` を構築する場合にも使用できます。

## 認証

`Cake\Http\Client` は、いくつかの異なる認証システムをサポートしています。
異なる認証ストラテジーを、開発者によって追加することができます。
認証ストラテジーは、リクエストが送信される前に呼び出され、
リクエストの文脈にヘッダーを追加することができます。

### Basic 認証の使用

Basic 認証の例:

``` php
$http = new Client();
$response = $http->get('http://example.com/profile/1', [], [
  'auth' => ['username' => 'mark', 'password' => 'secret']
]);
```

デフォルトでは、 auth オプションに `'type'` キーが存在しない場合、
`Cake\Http\Client` は Basic 認証を使用します。

### ダイジェスト認証の使用

Basic 認証の例:

``` php
$http = new Client();
$response = $http->get('http://example.com/profile/1', [], [
  'auth' => [
    'type' => 'digest',
    'username' => 'mark',
    'password' => 'secret',
    'realm' => 'myrealm',
    'nonce' => 'onetimevalue',
    'qop' => 1,
    'opaque' => 'someval'
  ]
]);
```

'type' キーに 'digest' を設定することによって、
認証サブシステムにダイジェスト認証を使用することを伝えます。

### OAuth 1 認証

多くのモダンなウェブサービスは、API にアクセスするために OAuth 認証を必要とします。
含まれる OAuth 認証は、すでにコンシューマキーとコンシューマシークレットがあることを前提としています。 :

``` php
$http = new Client();
$response = $http->get('http://example.com/profile/1', [], [
  'auth' => [
    'type' => 'oauth',
    'consumerKey' => 'bigkey',
    'consumerSecret' => 'secret',
    'token' => '...',
    'tokenSecret' => '...',
    'realm' => 'tickets',
  ]
]);
```

### OAuth 2 認証

OAuth2 は、多くの場合、単一のヘッダーであるため、特殊な認証アダプターがありません。
代わりに、アクセストークンを使用してクライアントを作成することができます。 :

``` php
$http = new Client([
    'headers' => ['Authorization' => 'Bearer ' . $accessToken]
]);
$response = $http->get('https://example.com/api/profile/1');
```

### プロキシー認証

いくつかのプロキシーは使用するために認証を必要とします。
一般に、この認証は Basic ですが、任意の認証アダプターによって実装することができます。
デフォルトでは、 type キーが設定されていない限り、 Http\Client は Basic 認証を前提としています。 :

``` php
$http = new Client();
$response = $http->get('http://example.com/test.php', [], [
  'proxy' => [
    'username' => 'mark',
    'password' => 'testing',
    'proxy' => '127.0.0.1:8080',
  ]
]);
```

２番目のプロキシーパラメーターは、プロトコルのない IP またはドメインの文字列でなければなりません。
ユーザー名とパスワードは、ヘッダー通じて渡されますが、プロキシー文字列は [stream_context_create()](https://php.net/manual/ja/function.stream-context-create.php) を通じて渡されます。

## スコープ指定クライアントの作成

ドメイン名を再入力すると、認証とプロキシーの設定が面倒になり、エラーが発生しやすくなります。
間違いの可能性を減らし、いくつかの退屈さを緩和するために、
スコープ指定クライアントを作成することができます。 :

``` php
// スコープ指定クライアントの作成
$http = new Client([
  'host' => 'api.example.com',
  'scheme' => 'https',
  'auth' => ['username' => 'mark', 'password' => 'testing']
]);

// api.example.com にリクエストします
$response = $http->get('/test.php');
```

スコープ指定クライアントを作成する場合、以下の情報を使用することができます。

- host
- scheme
- proxy
- auth
- port
- cookies
- timeout
- ssl_verify_peer
- ssl_verify_depth
- ssl_verify_host

リクエスト実行時に、これらのオプションのいずれかを指定することで上書きすることができます。
リクエスト URL 中のホスト、スキーム、プロキシー、ポートが上書きされます。 :

``` php
// 先ほど作成したスコープ指定クライアントの使用
$response = $http->get('http://foo.com/test.php');
```

上記は、ドメインやスキーム、ポートが置き換えられます。ただし、このリクエストは、
スコープ指定クライアントの作成時に定義された他のすべてのオプションを使用して引き続き行われます。
対応するオプションの詳細については [Http Client Request Options](#http_client_request_options) をご覧ください。

## クッキーの設定と管理

Http\Client はまた、リクエストを行うときクッキーを受け入れることができます。
クッキーを受け入れることに加えて、レスポンス中に自動的に設定された有効なクッキーを格納します。
クッキーを持つ任意のレスポンスが、元の Http\Client のインスタンスに格納されています。
Client インスタンスに格納されているクッキーは、それ以後のドメイン+パスの組み合わせが一致する
リクエストに自動的に含まれます。 :

``` php
$http = new Client([
    'host' => 'cakephp.org'
]);

// いくつかのクッキーをセットしたリクエストを実行
$response = $http->get('/');

// デフォルトで、初めのリクエストのクッキーが
// 含まれます。
$response2 = $http->get('/changelogs');
```

リクエストの `$options` パラメーターの中に設定することにより、
自動で含まれるクッキーをいつでも上書きすることができます。 :

``` php
// 格納されたクッキーを独自の値に置き換えます。
$response = $http->get('/changelogs', [], [
    'cookies' => ['sessionid' => '123abc']
]);
```

`addCookie()` メソッドを使って、作成された後のクライアントにクッキーオブジェクトを
追加することができます。 :

``` php
use Cake\Http\Cookie\Cookie;

$http = new Client([
    'host' => 'cakephp.org'
]);
$http->addCookie(new Cookie('session', 'abc123'));
```

::: info Added in version 3.5.0
`addCookie()` は 3.5.0 で追加されました。
:::

## レスポンスオブジェクト

`class` Cake\\Http\\Client\\**Response**

Response オブジェクトは、レスポンスデータを検査するための多くのメソッドを持ちます。

::: info Changed in version 3.3.0
3.3.0 では、 `Cake\Http\Client\Response` は を実装します。
:::

### レスポンスボディーの読み込み

文字列としてレスポンスボディー全体を読み込みます。 :

``` php
// 文字列としてレスポンス全体を読み込み（3.7.0 の例）
$response->getStringBody();

// 3.7.0 より前の例
$response->body();
// もしくは
$response->body;
```

また、レスポンスのストリームオブジェクトにアクセスし、そのメソッドを使用することができます。 :

``` php
// レスポンスボディーを含む Psr\Http\Message\StreamInterface を取得
$stream = $response->getBody();

// ストリームを一度に 100 バイト読み込み
while (!$stream->eof()) {
    echo $stream->read(100);
}
```

### JSON や XML レスポンスボディーの読み込み

JSON や XML のレスポンスが一般的に使用されているので、レスポンスオブジェクトは、
デコードされたデータを読み取るためにアクセサーを簡単に使用することができます。
JSON は配列にデコードされ、XML データは、 `SimpleXMLElement` ツリーにデコードされます。 :

``` php
// 何らかの XML を取得
$http = new Client();
$response = $http->get('http://example.com/test.xml');
// 3.7.0 の例
$xml = $response->getXml();

// 3.7.0 より前の例
$xml = $response->xml;

// 何らかの JSON を取得
$http = new Client();
$response = $http->get('http://example.com/test.json');
// 3.7.0 の例
$json = $response->getJson();

// 3.7.0 より前の例
$json = $response->json;
```

デコードされたレスポンスデータはそれを複数回アクセスし、レスポンスオブジェクトに格納されても、
何も追加コストはかかりません。

### レスポンスヘッダーへのアクセス

いくつかの異なるメソッドを介してヘッダーにアクセスすることができます。
メソッドを介してアクセスする際に、ヘッダー名は常に大文字と小文字を区別しない値として扱われます。 :

``` php
// 連想配列として全てのヘッダーを取得
$response->getHeaders();

// 配列として単一のヘッダーを取得
$response->getHeader('content-type');

// 文字列としてヘッダーを取得
$response->getHeaderLine('content-type');

// レスポンスのエンコーディングを取得
$response->getEncoding();

// 全てのヘッダーの key=>value の配列を取得
$response->headers;
```

### クッキーデータへのアクセス

クッキーについて必要なデータ量に応じて、いくつかの異なる方法でクッキーを読むことができます。 :

``` php
// 全てのクッキー (全データ) を取得
$response->getCookies();

// 単一のクッキーの値を取得
$response->getCookie('session_id');

// 単一のクッキーの完全なデータを取得
// value, expires, path, httponly, secure キーを含みます
$response->getCookieData('session_id');

// 全てのクッキーの完全なデータにアクセス
$response->cookies;
```

### ステータスコードの確認

レスポンスオブジェクトは、ステータスコードを確認するためのいくつかのメソッドを提供します。 :

``` php
// レスポンスが 20x だった
$response->isOk();

// レスポンスが 30x だった
$response->isRedirect();

// ステータスコードの取得
$response->getStatusCode();

// __get() ヘルパー
$response->code;
```
