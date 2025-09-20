# セキュリティ

`class` **SecurityComponent**(ComponentCollection $collection, array $settings = array())

Security コンポーネントを使うと、アプリケーションにさらに堅牢なセキュリティを導入できます。
このコンポーネントは、以下の様々なタスクのためのメソッドを提供します。

- アプリケーションが受け付ける HTTP メソッドの限定
- CSRF 防御
- フォーム改ざん防止
- SSL の利用を要求
- コントローラ間の通信制限

全てのコンポーネントと同様に、いくつかの設定できるパラメータがあり、
これら全てのプロパティは、直接設定したり、コントローラの beforeFilter の中で、
プロパティと同じ名前のセッターメソッドで設定できます。

Security コンポーネントを使用することで、自動的に [CSRF](https://en.wikipedia.org/wiki/Cross-site_request_forgery) とフォーム改ざんを
防止します。Security コンポーネントによって、hidden トークンフィールドがフォームに追加され、
チェックされます。他には、 `csrfExpires` で設定した、無効になる期間を過ぎると
フォームの投稿を拒否します。

Security コンポーネントのフォーム保護機能と、 `startup()` コールバックで
フォームデータを処理する他のコンポーネントを使用している時、 `$components` 配列中で
それらのコンポーネントは Security コンポーネントの前に配置してください。

> [!NOTE]
> Security コンポーネントを使用している時、フォームの作成に FormHelper を
> **使わなければなりません** 。また、フィールドの "name" 属性を
> **上書きしてはいけません** 。Security コンポーネントは、FormHelper で作成され、
> 管理されるインジケーターを確認します。(これらは `~FormHelper::create()`
> と `~FormHelper::end()` の中で作成されます。) 例えば JavaScript で無効化・
> 削除・新規作成するなどして POST リクエスト中の投稿されたフィールドを動的に差し替えることは、
> リクエストが破棄されるきっかけになります。 `$validatePost` や `$disabledFields`
> 設定パラメータをご覧ください。

## ブラックホールコールバックの処理

あるアクションが Security コンポーネントによって制限されている時、
デフォルトでは、不正なリクエストとして 400 エラーを返し破棄します。
コントローラ中のコールバック関数を `$this->Security->blackHoleCallback`
に設定することによってこの振る舞いを変更できます。

`method` SecurityComponent::**blackHole**(object $controller, string $error)

## HTTP メソッドの限定

`method` SecurityComponent::**requirePost**()

`method` SecurityComponent::**requireGet**()

`method` SecurityComponent::**requirePut**()

`method` SecurityComponent::**requireDelete**()

## アクションを SSL 通信に限定

`method` SecurityComponent::**requireSecure**()

`method` SecurityComponent::**requireAuth**()

## コントローラー間通信の限定

> このコントローラにリクエストを送ることができるコントローラのリスト。
> これは、コントローラ間リクエストの制御に利用できます。
>
> このコントローラのアクションにリクエストを送ることができるアクションのリスト。
> これは、コントローラ間リクエストの制御に利用できます。

## フォーム改ざん防止

デフォルトでは、 `SecurityComponent` は、ユーザーが特定の方法でフォームを改変することを
防ぎます。 `SecurityComponent` は、以下のことを防止します。

- フォームに新規フィールドを追加することはできません。
- フォームからフィールドを削除することはできません。
- hidden フィールドの値を更新することはできません。

これらの改変を防ぐには、FormHelper を使って、フォーム内のフィールドを追跡することで実現します。
その際、 hidden フィールドの値も追跡対象になります。このデータの全てが結合され、
ハッシュに変換されます。フォームが投稿された時、 `SecurityComponent` は、
POST データをもとに同じ構造を構築し、ハッシュと比較します。

> [!NOTE]
> SecurityComponent は、セレクトオプションの追加や変更を防ぐことは **できません** 。
> また、ラジオオプションも追加や変更を防ぐことはできません。
>
> POST バリデーションを解除したいフォームフィールドの一覧をセットします。
> このコンポーネントの他にも、 `FormHelper::unlockField()`
> でも解除できます。制限が解除されたフィールドは、POST 時に必須ではなくなり、
> hidden フィールドの値もチェックされません。
>
> `false` をセットすると、POST リクエストのバリデーションを完全にスキップし、
> 実質フォームバリデーションを無効化します。

## CSRF 設定

> フォームの CSRF 防御するかどうか。 `false` をセットすると無効になります。
>
> CSRF トークンが作成されてから有効期限が切れるまでの期間。
> 各フォームやページのリクエストは、有効期限が切れるまで一度だけ投稿できるトークンを
> 新規に生成します。 `strtotime()` と互換性のある値です。デフォルト値は、
> "+30 minutes" です。
>
> CSRF トークンを一度きりの使用かそうでないかの制御。 `false` を指定すると
> 各リクエストで新しいトークンを生成しません。一つのトークンを有効期限が切れるまで
> 再利用されます。トークンの消費によってユーザーの変更が不正なリクエストになることが
> 少なくなります。トークンを再利用することは、CSRF に対して脆弱になる副作用があります。

## 使い方

Security コンポーネントは、一般的にコントローラの `beforeFilter()` で使用します。
あなたが望むセキュリティ制限をここで指定すると SecurityComponent は起動時に
それらの制限を有効にします。 :

``` php
class WidgetController extends AppController {

    public $components = array('Security');

    public function beforeFilter() {
        $this->Security->requirePost('delete');
    }
}
```

上記の例では、delete アクションは POST リクエストを受け取った場合にのみ
正しく実行されます。 :

``` php
class WidgetController extends AppController {

    public $components = array('Security');

    public function beforeFilter() {
        if (isset($this->request->params['admin'])) {
            $this->Security->requireSecure();
        }
    }
}
```

上記の例では、 管理者用ルーティングの全てのアクションは、セキュアな SSL 通信のみを許可します。 :

``` php
class WidgetController extends AppController {

    public $components = array('Security');

    public function beforeFilter() {
        if (isset($this->params['admin'])) {
            $this->Security->blackHoleCallback = 'forceSSL';
            $this->Security->requireSecure();
        }
    }

    public function forceSSL() {
        return $this->redirect('https://' . env('SERVER_NAME') . $this->here);
    }
}
```

上記の例では、 管理者用ルーティングの全てのアクションは、セキュアな SSL 通信のみを許可します。
リクエストが破棄対象になった時、 `forceSSL()` コールバック関数が呼ばれ、非セキュアなリクエストを
自動的にセキュアなリクエストにリダイレクトします。

## CSRF 防御

CSRF つまり、クロスサイトリクエストフォージェリ (Cross Site Request Forgery) は、
アプリケーションの一般的な脆弱性です。攻撃者が、直前のリクエストを記録し再生することを許し、
他のドメイン上の画像タグやリソースを使用してデータを送信します。

`SecurityComponent` の CSRF 機能によって、二重投稿やリプレイ攻撃を捕捉します。
フォーム送信ごとに特別なトークンを追加することによって動作します。このトークンは、
一度使用されると、再度利用することはできません。有効期限切れのトークンを再利用しようとすると、
そのリクエストは破棄されます。

### CSRF 防御の利用

シンプルに components 配列に `SecurityComponent` を追加することで、
CSRF 防御の恩恵を受けられます。デフォルトでは CSRF トークンは、30分間有効です。
コンポーネントの csfrExpires を設定することで有効期限を指定することができます。 :

``` php
public $components = array(
    'Security' => array(
        'csrfExpires' => '+1 hour'
    )
);
```

コントローラの `beforeFilter` 中でこのプロパティをセットすることもできます。 :

``` php
public function beforeFilter() {
    $this->Security->csrfExpires = '+1 hour';
    // ...
}
```

csrfExpires プロパティは、
[strtotime()](https://www.php.net/manual/en/function.strtotime.php)
互換の値を設定できます。このコンポーネントが有効になると、全てのフォームの
`FormHelper` に CSRF トークンを含む `data[_Token][key]` が追加されます。

### 消失・期限切れトークンの処理

消失・期限切れトークンは、他のセキュリティ違反と同様に扱われます。
`SecurityComponent` の `blackHoleCollback` は、 'csrf' パラメータで呼び出されます。
これは、他の警告と CSRF トークンの失敗を区別するのに役立ちます。

### ワンタイムトークンの代わりにセッション単位のトークンを利用

デフォルトで、新しい CSRF トークンが各リクエストで生成され、各トークンは一度だけ使用されます。
トークンが二度使用された場合、リクエストは破棄されます。しばしば、シングルページアプリケーションで
問題が発生して、この振る舞いが好ましくないことがあります。
`csrfUseOnce` を `false` にすることで、長期間、複数回使用できるトークンに切り替える
ことができます。この設定は、コントローラの components 配列や `beforeFilter` で行います。 :

``` php
public $components = array(
    'Security' => array(
        'csrfUseOnce' => false
    )
);
```

上記の例は、有効期限まで CSRF トークンを再利用したいことをコンポーネントに伝えます。
有効期限は、 `csrfExpire` の値で制御できます。有効期限切れトークンが問題になっている場合、
セキュリティと利便性の間でバランスを取ります。

### CSRF 防御を無効化

何らかの理由でフォームの CSRF 防御を無効にしたい場合があります。
この機能を無効にしたい場合、 `beforeFilter` 内で `$this->Security->csrfCheck = false;`
をセットするか components 配列を使用します。デフォルトでは CSRF 防御は有効で、一度きりのトークン
が設定されます。

## 指定したアクションの CSRF とデータバリデーションの無効化

例えば AJAX リクエストなど、あるアクションで全てのセキュリティチェックを無効化したい場合があります。
`beforeFilter` 内で `$this->Security->unlockedActions` にリストアップすることで
これらのアクションを「アンロック」できます。 `unlockedActions` プロパティは、
`SecurityComponent` のその他の機能には **影響しません** 。

::: info Added in version 2.3
:::
