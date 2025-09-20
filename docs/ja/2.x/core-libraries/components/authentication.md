# 認証

`class` **AuthComponent**(ComponentCollection $collection, array $settings = array())

ユーザーを識別し、認証し、権限を付与することは、ほとんどすべてのウェブアプリケーションに
共通の機能です。CakePHP の AuthComponent ではそういったタスクを実行するための
プラガブルな方法を提供します。AuthComponent により、認証オブジェクトと、ユーザーの権限を
識別・判定する柔軟な仕組みを作るための認可オブジェクトを組み合わせることができるように
なります。

## 以降を読む前に

認証の設定には、ユーザーテーブルの定義、モデルやコントローラーやビューの作成など、
いくつかのステップが必要です。

[ブログチュートリアル](../../tutorials-and-examples/blog-auth-example/auth)
の中で順を追って説明しています。

## 認証

認証とは、与えられた認証情報によりユーザーを識別し、そのユーザーが言うとおりの人物であることを
確実なものにする処理のことです。たいていの場合、これはユーザー名とパスワードにより行われ、
それと既知のユーザーリストを照らし合わせます。CakePHP には、あなたのアプリケーション内に
保管されているユーザーを認証するための組み込み済みの方法がいくつか存在します。

- `FormAuthenticate` では、POST されたデータをもとに認証を行うことが可能です。
  通常これは、ユーザーが情報を入力するログインフォームです。
- `BasicAuthenticate` では、Basic HTTP 認証を使った認証を行うことが可能です。
- `DigestAuthenticate` では、ダイジェスト HTTP 認証を使った認証を行うことが可能です。

デフォルトで `AuthComponent` は `FormAuthenticate` を使用します。

### 認証タイプの選択

たいていの場合は、フォームベースの認証を提供したいと思うでしょう。これはウェブブラウザを
使うユーザーにとってはもっとも簡単な方法です。もし、API やウェブサービスを構築しているなら、
Basic 認証やダイジェスト認証も考慮したくなるかもしれません。ダイジェスト認証と Basic
認証の重要な違いは、どのようにパスワードを扱うかということにあります。Basic 認証では、
ユーザー名とパスワードは平文のテキストとしてサーバに送信されます。そのため Basic 認証は
SSL を使わないアプリケーションには向いていません。これは、慎重に扱うべきパスワードが
露出してしまうことになるためです。ダイジェスト認証はユーザー名やパスワード、
そのほかのいくつかの詳細情報のダイジェストハッシュを使います。そのため ダイジェスト認証は
SSL 暗号化しないアプリケーションにもふさわしいものです。

また、OpenID のような認証システムを使うことも可能です。ただし、OpenID は CakePHP の
コアには含まれません。

### 認証ハンドラの設定

認証ハンドラは `$this->Auth->authenticate` を使って設定します。
認証に使うハンドラを１つもしくは複数設定することができます。複数のハンドラを
設定することで、複数のログインの仕組みをサポートすることが可能です。
ユーザーがログインする際、認証ハンドラは宣言されている順に判定されます。
あるハンドラでユーザーを識別ができたら、それ以降のハンドラでは判定されません。
逆に、例外を投げることですべての認証を失敗にすることもできます。
投げられた任意の例外をキャッチし、必要に応じて処理する必要があります。

コントローラの `beforeFilter` の中、もしくは `$components` 配列の中に、
認証ハンドラを設定することができます。配列を使用して各認証オブジェクトに設定情報を
渡すことができます。 :

``` php
// 基本的な設定法
$this->Auth->authenticate = array('Form');

// 設定を中に記述
$this->Auth->authenticate = array(
    'Basic' => array('userModel' => 'Member'),
    'Form' => array('userModel' => 'Member')
);
```

上記の２つ目のブロックでは、 `userModel` キーを２回宣言しなければならないということに
気づいたでしょう。コードを DRY に保ちたいなら、 `all` キーを使うことができます。
この特別なキーを使うことで、列挙したオブジェクトすべてに設定が渡されることになります。
all キーは `AuthComponent::ALL` と記述することもできます。 :

``` php
// 'all' を使って設定を記述
$this->Auth->authenticate = array(
    AuthComponent::ALL => array('userModel' => 'Member'),
    'Basic',
    'Form'
);
```

上記の例では、 `Form` と `Basic` の両方ともが 'all' キーで宣言された設定を
取得することになります。特定の認証オブジェクトに個別に書いた設定は 'all' キーの同名の
キーの情報をオーバーライドします。コアの認証オブジェクトでは次の設定キーをサポートしています。

- `fields` ユーザーを識別するのに使う列名の配列。

- `userModel` User のモデル名。デフォルトは User。

- `scope` 認証するユーザーを検索する際に使う、追加の条件。
  例： `array('User.is_active' => 1)`

- `recursive` `find()` に渡された recursive キーの値。 デフォルトは `0` 。

- `contain` ユーザーのレコードがロードされた際に含めることのできるオプション。
  もしこのオプションを使用したい場合、モデルに Containable ビヘイビアを追加する必要があります。

  ::: info Added in version 2.2
  :::

- `passwordHasher` パスワードをハッシュするクラス。デフォルトは `Simple` 。

  ::: info Added in version 2.4
  :::

- `userFields` `userModel` から取得するフィールドの一覧。このオプションは、
  カラムの多いユーザーテーブルで全てのカラムがセッションに必要ないときに便利です。
  デフォルトでは全てのフィールドを取得します。

  ::: info Added in version 2.6
  :::

配列 `$components` の中でユーザーの特定の列名を設定するには:

``` php
// $components 配列の中で設定を記述
public $components = array(
    'Auth' => array(
        'authenticate' => array(
            'Form' => array(
                'fields' => array('username' => 'email')
            )
        )
    )
);
```

Auth の他の設定キー (authError や loginAction など) を authenticate や Form の
要素として書いてはいけません。それらは authenticate キーと同じレベルであるべきです。
上記の例を他の Auth 設定を使って書いた場合は次のようになります。 :

``` php
// $components 配列の中で設定を記述
public $components = array(
    'Auth' => array(
        'loginAction' => array(
            'controller' => 'users',
            'action' => 'login',
            'plugin' => 'users'
        ),
        'authError' => 'Did you really think you are allowed to see that?',
        'authenticate' => array(
            'Form' => array(
                'fields' => array(
                  'username' => 'my_user_model_username_field', // デフォルトは userModel の 'username'
                  'password' => 'my_user_model_password_field'  // デフォルトは userModel の 'password'
                )
            )
        )
    )
);
```

共通の設定に加えて、Basic 認証では次のキーも利用できます:

- `realm` 認証される realm。デフォルトは `env('SERVER_NAME')` 。

共通の設定に加えて、ダイジェスト認証では次のキーも利用できます:

- `realm` realm 認証の認証先。デフォルトはサーバ名。
- `nonce` 認証で使われる nonce。デフォルトは `uniqid()` 。
- `qop` デフォルトは auth。現時点では他の値はサポートされていません。
- `opaque` クライアントから変更されることなく戻されるべき文字列。デフォルトは
  `md5($settings['realm'])` 。

### ユーザーの識別とログイン

以前の `AuthComponent` は自動的にログインを行っていました。
これに混乱する人が多く、時には AuthComponent の利用をやや難しくしていました。
2.0 でログインしたい場合には、手動で `$this->Auth->login()` を呼び出す必要があります。

ユーザーを認証する際には、設定されている認証オブジェクトを設定された順にチェックしていきます。
あるオブジェクトでユーザーが識別できたら、以降のオブジェクトはチェックされません。
ログインフォームと連携する単純な login 関数なら次のようになります。 :

``` php
public function login() {
    if ($this->request->is('post')) {
        // Important: Use login() without arguments! See warning below.
        if ($this->Auth->login()) {
            return $this->redirect($this->Auth->redirectUrl());
            // 2.3より前なら
            // `return $this->redirect($this->Auth->redirect());`
        }
        $this->Flash->error(
            __('Username or password is incorrect')
        );
        // 2.7 より前なら
        // $this->Session->setFlash(__('Username or password is incorrect'));
    }
}
```

上記のコードは (`login` メソッドに渡される情報以外は) 、POST データを使ってユーザーを
ログインさせようとします。ログインが成功ならユーザーが最後に訪れていたページか
`AuthComponent::$loginRedirect` へと redirect します。
ログインが失敗なら、フラッシュメッセージがセットされます。

> [!WARNING]
> 1.3 の `$this->Auth->login($this->data)` では、ユーザーの識別を試みて成功したときのみ
> ログインが行われましたが、2.x では `$this->Auth->login($this->request->data)`
> で、どんなデータが POST されたとしてもログインを行います。

#### ログインでのダイジェスト認証・Basic 認証の利用

Basic 認証およびダイジェスト認証は初回の POST やフォームを必要としないので、もし
Basic／ダイジェストオーセンティケータだけを使っているならコントローラに
ログインアクションは必要ありません。また、 AuthComponent がユーザー情報を session
から読み込まないようにするために `AuthComponent::$sessionKey` を false に
設定することができます。こうすると、ステートレス認証がリクエストごとにユーザーの
資格情報を再確認します。これは若干のオーバーヘッドになりますが、クッキーを使用することなく
ログイン処理を行えます。

> [!NOTE]
> 2.4 より前のバージョンでは、Basic またはダイジェスト認証だけを使用する場合でも、
> 認証されていないユーザーが保護されたページにアクセスしようとするとログインに
> リダイレクトされるように、ログインアクションが必要となります。また、2.4より前では
> `AuthComponent::$sessionKey` に false を設定するとエラーが発生します。

### カスタム認証オブジェクトの作成

認証オブジェクトはプラガブルなので、カスタム認証オブジェクトを自分のアプリケーション内にでも、
プラグインとしてでも作成が可能です。もし例えば、OpenID 認証オブジェクトを作成したいのだとしたら、
`app/Controller/Component/Auth/OpenidAuthenticate.php` の中で次のように記述することが
できます。 :

``` css
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class OpenidAuthenticate extends BaseAuthenticate {
    public function authenticate(CakeRequest $request, CakeResponse $response) {
        // OpenID 用の処理をここに記述します。
        // ユーザー認証が通った場合は、user の配列を返します。
        // 通らなかった場合は false を返します。
    }
}
```

認証オブジェクトは、ユーザーを識別できなかった場合に `false` を返さなければなりません。
そして、可能ならユーザー情報の配列も返すべきでしょう。 `BaseAuthenticate` を継承しなくても
かまいません。独自の認証オブジェクトには `authenticate()` メソッドが実装されていれば
よいのです。 `BaseAuthenticate` クラスではよく使われる強力なメソッドが多数提供されます。
また、独自の認証オブジェクトがステートレス認証やクッキーレス認証をサポートする必要があるなら、
`getUser()` メソッドを実装することもできます。詳細は下記の Basic／ダイジェスト認証の
セクションを参照してください。

### カスタム認証オブジェクトの利用

カスタム認証オブジェクトを作成したら、AuthComponent の authenticate 配列内にそれを
含めることで利用することができます。 :

``` php
$this->Auth->authenticate = array(
    'Openid', // app 内の認証オブジェクト
    'AuthBag.Combo', // プラグインの認証オブジェクト
);
```

### ステートレス認証システムの作成

認証オブジェクトはクッキーに依存しないユーザーログインのシステムをサポートするために使われる
`getUser()` メソッドを実装することができます。典型的な getUser メソッドはリクエストや
環境を見て、ユーザーを識別するためにその情報を使います。HTTP Basic 認証の例を挙げると、
ユーザー名とパスワードの値として `$_SERVER['PHP_AUTH_USER']` と
`$_SERVER['PHP_AUTH_PW']` を使います。リクエストごとに、それらの値を再度ユーザーを
識別するために使い、正規のユーザーであることを確認します。認証オブジェクトの `authenticate()`
メソッドと同様に、`getUser()` メソッドも成功ならユーザー情報の配列を、失敗なら `false`
を返すようにしてください。 :

``` php
public function getUser($request) {
    $username = env('PHP_AUTH_USER');
    $pass = env('PHP_AUTH_PW');

    if (empty($username) || empty($pass)) {
        return false;
    }
    return $this->_findUser($username, $pass);
}
```

上記は HTTP Basic 認証用の getUser メソッドをどのように実行できるのかを示しています。
`_findUser()` メソッドは `BaseAuthenticate` の一部でユーザー名、パスワードをもとに
ユーザーを識別します。

### 認証されていないリクエストの扱い

認証されていないユーザーが最初に保護されたページにアクセスしようとすると、
チェーンの最後のオーセンティケータの <span class="title-ref">unauthenticated()</span> メソッドが呼び出されます。
認証オブジェクトが適切に応答またはリダイレクトを送信処理し、
それ以上のアクションは必要ないということを示すために <span class="title-ref">true</span> を返すことができます。
<span class="title-ref">AuthComponent::\$authenticate</span> プロパティで認証オブジェクトを指定する順序を設定できます。

オーセンティケータが null を返した場合、 <span class="title-ref">AuthComponent</span> は、ユーザーをログインアクションに
リダイレクトします。Ajax リクエストでかつ <span class="title-ref">AuthComponent::\$ajaxLogin</span> が指定されていた場合、
その要素は描画され、そうでなければ HTTP ステータスコード 403 が返されます。

> [!NOTE]
> 2.4 より前では、認証オブジェクトは <span class="title-ref">unauthenticated()</span> メソッドを提供しません。

### 認証についてのフラッシュメッセージの表示

Auth が生成するセッションエラーメッセージを表示するためには、あなたのレイアウトに次のコードを
加えなければなりません。 `app/View/Layouts/default.ctp` ファイルに次の２行を
加えてください。content_for_layout 行の前にある body 部の中がよいでしょう。 :

``` php
// CakePHP 2.7 以上
echo $this->Flash->render();
echo $this->Flash->render('auth');

// 2.7 より前なら
echo $this->Session->flash();
echo $this->Session->flash('auth');
```

AuthComponent の flash 設定を使うことでエラーメッセージをカスタマイズすることができます。
`$this->Auth->flash` を使うことで、AuthComponent がフラッシュメッセージのために使う
パラメータを設定することができます。利用可能なキーは次のとおりです。

- `element` - 使用されるエレメント。デフォルトは 'default'
- `key` - 使用されるキー。デフォルトは 'auth'
- `params` - 使用される追加の params 配列。デフォルトは array()

フラッシュメッセージの設定だけでなく、AuthComponent が使用する他のエラーメッセージを
カスタマイズすることもできます。コントローラの beforeFilter の中や component の設定で、
認証が失敗した際に使われるエラーをカスタマイズするのに `authError` を
使うことができます。 :

``` php
$this->Auth->authError = "このエラーは保護されたウェブサイトの一部に" .
                           "ユーザーがアクセスしようとした際に表示されます。";
```

::: info Changed in version 2.4
ユーザーがすでにログインしていた後にのみ、認可エラーを表示したいということもあると思います。その場合は  を設定することにより、このメッセージを表示しないようにすることができます。
:::

コントローラの beforeFilter()、またはコンポーネントの設定で:

``` php
if (!$this->Auth->loggedIn()) {
    $this->Auth->authError = false;
}
```

### パスワードのハッシュ化

AuthComponent がもはや自動ではパスワードをハッシュ化しなくなったことに、気づいたかもしれません。
これは妥当性チェックのような多くの共通タスクを難しいものにしていたため、取り除かれました。
パスワードを平文テキストのまま保管しては **いけません** 。ユーザーのレコードを保存する前に、
パスワードは必ずハッシュ化するべきです。

2.4 の時点で、パスワードハッシュの生成とチェックはパスワードハッシュ化クラスに委譲されています。
認証オブジェクトは `passwordHasher` という新しい設定項目で使用するパスワードハッシュ化
クラスを指定します。この設定項目にはクラス名を文字列で指定するか、 `className` というキーに
クラス名を指定した配列を設定します。このとき、配列の余分なキーが設定としてパスワードハッシュ化
クラスのコンストラクタに渡されます。デフォルトのハッシュ化クラス `Simple` は sha1、sha256、
md5 ハッシュに使用することができます。次のようにしてハッシュ化クラスを指定します。 :

``` php
public $components = array(
    'Auth' => array(
        'authenticate' => array(
            'Form' => array(
                'passwordHasher' => array(
                    'className' => 'Simple',
                    'hashType' => 'sha256'
                )
            )
        )
    )
);
```

新しいユーザーレコードを作成するとき、モデルの `beforeSave` コールバック内で適切な
パスワードハッシュ化クラスを使用してパスワードをハッシュ化できます。 :

``` php
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

class User extends AppModel {
    public function beforeSave($options = array()) {
        if (!empty($this->data[$this->alias]['password'])) {
            $passwordHasher = new SimplePasswordHasher(array('hashType' => 'sha256'));
            $this->data[$this->alias]['password'] = $passwordHasher->hash(
                $this->data[$this->alias]['password']
            );
        }
        return true;
    }
}
```

`$this->Auth->login()` を呼び出す前にパスワードをハッシュ化する必要はありません。
さまざまな認証オブジェクトが個々にパスワードをハッシュ化します。

### パスワードに bcrypt を使う

CakePHP 2.3 で `BlowfishAuthenticate` クラスが導入され、
[bcrypt](https://en.wikipedia.org/wiki/Bcrypt) (別名: Blowfish) をパスワードの
ハッシュ化に使用できるようになりました。bcrypt ハッシュは SHA1 で保存されたパスワードよりも
ブルートフォースアタックに対してとても強固です。なお、 `BlowfishAuthenticate` は 2.4 で
非推奨になり、代わりに `BlowfishPasswordHasher` が追加されました。

Blowfish password hasher は、任意の認証クラスで使用することができます。使用するには、
認証オブジェクトの `passwordHasher` の設定で Blowfish password hasher を
指定しないといけません。 :

``` php
public $components = array(
    'Auth' => array(
        'authenticate' => array(
            'Form' => array(
                'passwordHasher' => 'Blowfish'
            )
        )
    )
);
```

#### ダイジェスト認証のパスワードのハッシュ化

ダイジェスト認証は RFC で定義されたフォーマットでハッシュ化されたパスワードが必要です。
パスワードをダイジェスト認証で使用できるよう正しくハッシュ化するために、特別な
パスワードハッシュ化の関数 `DigestAuthenticate` を使ってください。ダイジェスト認証と
その他の認証戦略を合わせて利用する場合には、通常のハッシュ化パスワードとは別のカラムで
ダイジェストパスワードを保管するのをお勧めします。 :

``` php
App::uses('DigestAuthenticate', 'Controller/Component/Auth');

class User extends AppModel {
    public function beforeSave($options = array()) {
        // ダイジェスト認証のパスワードを作成
        $this->data[$this->alias]['digest_hash'] = DigestAuthenticate::password(
            $this->data[$this->alias]['username'],
            $this->data[$this->alias]['password'],
            env('SERVER_NAME')
        );
        return true;
    }
}
```

ダイジェスト認証用のパスワードは、ダイジェスト認証の RFC に基づき、他のハッシュ化パスワード
よりもやや多くの情報を要求します。

> [!NOTE]
> AuthComponent::\$authenticate 内で DigestAuthentication が設定された場合、
> DigestAuthenticate::password() の第３パラメータは定義した 'realm' の設定値と
> 一致する必要があります。このデフォルトは `env('SERVER_NAME')` です。
> 複数の環境で一貫したハッシュが欲しい場合に static な文字列を使用することができます。

### カスタムパスワードハッシュ化クラスの作成

カスタムパスワードハッシュ化クラスは `AbstractPasswordHasher` クラスを継承し、
抽象メソッドの `hash()` と `check()` を実装する必要があります。
`app/Controller/Component/Auth/CustomPasswordHasher.php` に次のように記述します。 :

``` css
App::uses('AbstractPasswordHasher', 'Controller/Component/Auth');

class CustomPasswordHasher extends AbstractPasswordHasher {
    public function hash($password) {
        // ここにコードを書く
    }

    public function check($password, $hashedPassword) {
        // ここにコードを書く
    }
}
```

### 手動でのユーザーログイン

独自のアプリケーションを登録した直後など、時には手動によるログインが必要になる事態が
発生することもあるでしょう。ログインさせたいユーザーデータを引数に `$this->Auth->login()`
を呼び出すことで、これを実現することができます。 :

``` php
public function register() {
    if ($this->User->save($this->request->data)) {
        $id = $this->User->id;
        $this->request->data['User'] = array_merge(
            $this->request->data['User'],
            array('id' => $id)
        );
        unset($this->request->data['User']['password']);
        $this->Auth->login($this->request->data['User']);
        return $this->redirect('/users/home');
    }
}
```

> [!WARNING]
> login メソッドに渡される配列に新たなユーザー ID が追加されていることを必ず確認してください。
> そうでなければ、そのユーザー ID が利用できなくなってしまいます。

> [!WARNING]
> `$this->Auth->login()` にデータを渡す前にパスワードは消去してください。
> そうしなければ、ハッシュ化されていないセッションに保存されてしまいます。

### ログインしたユーザーのアクセス

ユーザーがログインしたあと、現状のそのユーザーについての特定の情報が必要になることもあるでしょう。
`AuthComponent::user()` を使うことで、現在ログインしているそのユーザーにアクセスすることが
できます。このメソッドは static で、AuthComponent がロードされたあと、global に使うことも
できます。インスタンスメソッドとしても、static メソッドとしてもアクセス可能です。 :

``` php
// どこからでも利用できます。
AuthComponent::user('id')

// Controllerの中でのみ利用できます。
$this->Auth->user('id');
```

### ログアウト

最終的には認証を解除し、適切な場所へとリダイレクトするためのてっとり早い方法がほしくなるでしょう。
このメソッドはあなたのアプリケーション内のメンバーページに 'ログアウト' リンクを入れたい場合にも
便利です。 :

``` php
public function logout() {
    $this->redirect($this->Auth->logout());
}
```

すべてのクライアントで、ダイジェスト認証や Basic 認証でログインしたユーザーのログアウトを
達成することは難しいものです。多くのブラウザは開いている間だけ継続する認証情報を保有しています。
一部のクライアントは 401 のステータスコードを送信して強制的にログアウトすることができます。
認証 realm の変更は、一部のクライアントで機能させるためのもう１つの解決法です。

## 認可

認可は識別され認証されたユーザーが、要求するリソースへのアクセスを許可されていることを
保証するプロセスです。有効な `AuthComponent` が自動的に認証ハンドラをチェックし、
ログインしたユーザーが要求どおりにリソースへのアクセスを許可するかどうかを確認します。
組み込み済みの認証ハンドラがいくつか存在しますので、あなたのアプリケーション用に
カスタム版を作成したり、プラグインの一部として作成することができます。

- `ActionsAuthorize` アクションレベルでパーミッションをチェックするために AclComponent
  を使います。
- `CrudAuthorize` リソースへのパーミッションをチェックするために、AclComponent と、
  アクション -\> CRUD のマッピングを使います。
- `ControllerAuthorize` アクティブなコントローラの `isAuthorized()` を呼び、
  ユーザーの認可のために、その戻り値を使います。これはユーザーの認可をもっともシンプルに
  行う方法です。

### 認可ハンドラの設定

認可ハンドラの設定は `$this->Auth->authorize` で行います。
認可に使うハンドラを１つもしくは複数設定することができます。
複数のハンドラを使うことで、さまざまな認可の方法をサポートできます。
認可ハンドラがチェックされる際には、宣言された順に呼び出されます。
もし、認可を確認することができない場合やチェックが失敗した場合、ハンドラは false を返す必要があります。
もし、正常に認可を確認することができた場合、ハンドラは true を返す必要があります。
いずれかのハンドラを通過できるまで、順番に呼び出されます。
すべてのチェック結果が失敗なら、ユーザーは元いたページへとリダイレクトされます。
また、例外を投げることですべての認可を失敗にすることができます。
投げられた任意の例外をキャッチし、それらを処理する必要があります。

あなたのコントローラの `beforeFilter` の中や `$components` 配列の中で
認可ハンドラの設定を行うことができます。配列を使って、各認可オブジェクトに
設定情報を渡すことができます。 :

``` php
// 基本的な設定
$this->Auth->authorize = array('Controller');

// 設定を記述
$this->Auth->authorize = array(
    'Actions' => array('actionPath' => 'controllers/'),
    'Controller'
);
```

`Auth->authorize` も `Auth->authenticate` とほぼ同様で、 `all` キーを使うことで
コードを DRY に保ちやすくなります。この特別なキーを使うことで、列挙したオブジェクトすべてに
設定が渡されることになります。all キーは `AuthComponent::ALL` と記述することもできます。 :

``` php
// 'all' を使って設定を記述
$this->Auth->authorize = array(
    AuthComponent::ALL => array('actionPath' => 'controllers/'),
    'Actions',
    'Controller'
);
```

上記の例では、 `Actions` と `Controller` の両方ともが 'all' キーで宣言された設定を
取得することになります。特定の認可オブジェクトに個別に書いた設定は 'all' キーの同名の
キーの情報をオーバーライドします。コアの認可オブジェクトでは次の設定キーをサポートしています。

- `actionPath` ACO ツリーの中にコントローラアクションの ACO を配置するために
  `ActionsAuthorize` によって使われます。
- `actionMap` アクション -\> CRUD のマッピング。CRUD ロールにアクションをマッピングしたい
  `CrudAuthorize` もしくは認可オブジェクトによって使われます。
- `userModel` ARO/モデル のノード名。これ以下からユーザー情報を探します。ActionsAuthorize
  で使われます。

### カスタム認可オブジェクトの作成

認可オブジェクトはプラガブルなので、カスタム認可オブジェクトを自分の
アプリケーション内にでも、プラグインとしてでも作成が可能です。もし例えば、LDAP 認可
オブジェクトを作成したいのだとしたら、 `app/Controller/Component/Auth/LdapAuthorize.php`
の中で次のように記述することができます。 :

``` css
App::uses('BaseAuthorize', 'Controller/Component/Auth');

class LdapAuthorize extends BaseAuthorize {
    public function authorize($user, CakeRequest $request) {
        // LDAP 用の処理をここに記述します。
    }
}
```

認可オブジェクトは該当ユーザーがアクセスを拒否されたり、該当オブジェクトでのチェックが
できなかった場合には `false` を返してください。認可オブジェクトがユーザーのアクセスが
妥当だと判定したなら `true` を返してください。 `BaseAuthorize` を継承する必要は
ありませんが、独自の認可オブジェクトは必ず `authorize()` メソッドを実装してください。
`BaseAuthorize` クラスではよく使われる強力なメソッドが多数提供されます。

#### カスタム認可オブジェクトの利用

カスタム認可オブジェクトを作成したら、AuthComponent の authorize 配列にそれらを
含めることで使うことができます。 :

``` php
$this->Auth->authorize = array(
    'Ldap', // app 内の認可オブジェクト
    'AuthBag.Combo', // プラグインの認可オブジェクト
);
```

### 認可を使用しない

どの組み込み認可オブジェクトも使いたくなくて、AuthComponent の外側で完全に
権限を扱いたい場合は、 `$this->Auth->authorize = false;` を設定することが可能です。
デフォルトで AuthComponent は `authorize = false` となっています。認可のスキーマを
使いたくない場合は、コントローラの beforeFilter か、別のコンポーネントで権限を確実に
チェックしてください。

### 公開するアクションの作成

コントローラのアクションが完全に公開すべきものであったり、ユーザーのログインが
不要であったりという場合があります。AuthComponent は悲観的であり、デフォルトでは
アクセスを拒否します。 `AuthComponent::allow()` を使うことで、公開すべきアクションに
印をつけることができます。アクションに公開の印をつけることで、AuthComponent は該当のユーザーが
ログインしているかのチェックも、認可オブジェクトによるチェックも行わなくなります。 :

``` php
// すべてのアクションを許可。 CakePHP 2.0 (非推奨)。
$this->Auth->allow('*');

// すべてのアクションを許可。 CakePHP 2.1 以降。
$this->Auth->allow();

// view と index アクションのみ許可。
$this->Auth->allow('view', 'index');

// view と index アクションのみ許可。
$this->Auth->allow(array('view', 'index'));
```

> [!WARNING]
> もし scaffolding を使っている場合、すべてを許可する設定では scaffold のメソッドを
> 識別できず、許可されません。それらのアクション名を明示するようにしてください。

`allow()` には必要な数だけいくつでもアクション名を記述することができます。
すべてのアクション名を含む配列を渡してもかまいません。

### 認可が必要なアクションの作成

デフォルトでは、全てのアクションには認可を必要とします。一方、アクションに公開の印を付けた後、
その公開アクションを取り消したくなるかもしれません。そのために `AuthComponent::deny()`
を使うことができます。 :

``` php
// アクション１つを取り除く
$this->Auth->deny('add');

// すべてのアクションを取り除く
$this->Auth->deny();

// アクションのグループを取り除く
$this->Auth->deny('add', 'edit');
$this->Auth->deny(array('add', 'edit'));
```

`deny()` には必要な数だけいくつでもアクション名を記述することができます。
すべてのアクション名を含む配列を渡してもかまいません。

### ControllerAuthorize の利用

ControllerAuthorize では、コントローラのコールバックで認可チェックを処理することができます。
非常にシンプルな認可を行う場合や、認可を行うのにモデルとコンポーネントを合わせて利用する必要がある場合、
しかしカスタム認可オブジェクトを作成したくない場合に、これは理想的です。

コールバックでは必ず `isAuthorized()` を呼んでください。これは該当ユーザーがリクエスト内で
リソースにアクセスすることが許可されるかを boolean で返します。
コールバックにはアクティブなユーザーが渡されますので、チェックが可能です。 :

``` php
class AppController extends Controller {
    public $components = array(
        'Auth' => array('authorize' => 'Controller'),
    );
    public function isAuthorized($user = null) {
        // 登録済みユーザーなら誰でも公開機能にアクセス可能です。
        if (empty($this->request->params['admin'])) {
            return true;
        }

        // admin ユーザーだけが管理機能にアクセス可能です。
        if (isset($this->request->params['admin'])) {
            return (bool)($user['role'] === 'admin');
        }

        // デフォルトは拒否
        return false;
    }
}
```

上記のコールバックは非常にシンプルな認可システムとなっており、role = admin のユーザーだけが
admin に設定されたアクションにアクセスすることができます。

### ActionsAuthorize の利用

ActionsAuthorize は AclComponent を取りまとめ、各リクエストでアクション ACL チェックを
きめ細かに行うことができるようになります。ActionsAuthorize は DbAcl とペアで使うことが多く、
アプリケーションを通して管理ユーザーにより編集されうる、動的かつ柔軟なパーミッションシステムを
提供します。それは、ただし、たとえば IniAcl とカスタムアプリケーション ACL バックエンドと
いうように、他の ACL の実装と組み合わせることが可能です。

### CrudAuthorize の利用

`CrudAuthorize` は AclComponent と一体となり、CRUD 操作へのリクエストをマッピングする
機能を提供します。CRUD マッピングを使った認可の機能を提供します。これらのマッピングされた
リクエストは AclComponent 内で特別なパーミッションとしてチェックされます。

たとえば、 `/posts/index` を現在のリクエストであるとします。デフォルトでは `index` に
マッピングされますが、 `read` のパーミッションチェックを行います。ACL チェックは `posts`
コントローラの `read` パーミッションを使って行われることになります。これにより、
アクセスされたアクションにとどまらず、リソースへと行われる行為により焦点を合わせた
パーミッションシステムを作ることができるようになります。

### CrudAuthorize を使う場合のアクションのマッピング

CrudAuthorize やアクションマッピングを使う他の認可オブジェクトを使う場合、
追加でモデルのマッピングが必要になるかもしれません。その場合、mapAction() を使うことで、
アクション -\> CRUD パーミッションのマッピングを行うことができます。AuthComponent の
このメソッドを呼び出すことで、設定済みのすべての認可オブジェクトに設定が渡されます。 :

``` php
$this->Auth->mapActions(array(
    'create' => array('register'),
    'view' => array('show', 'display')
));
```

mapActions のキーには設定したい CRUD パーミッションを指定してください。
一方、値には CRUD パーミッションにマッピングされたすべてのアクションの配列を設定してください。

## AuthComponent API

AuthComponent は CakePHP に組み込み済みの認可・認証メカニズムへの
主要なインターフェイスです。

> 不正な／期限切れのセッションを伴った Ajax リクエストの場合に render すべき任意の
> ビューエレメントの名前。
>
> ユーザーの妥当性チェックが必要ないコントローラのアクションの配列。
>
> ユーザーのログインに使いたい認証オブジェクトの配列を設定してください。
> コアの認証オブジェクトがいくつか存在します。 [Authentication Objects](#authentication-objects)
> を参照してください。
>
> ユーザーがアクセス権の無いオブジェクトやアクションにアクセスした場合に表示されるエラー。
>
> ::: info Changed in version 2.4
> を設定することにより、authError メッセージを表示しないようにできます。
> :::
>
> 各リクエストでユーザーの認可に使いたい認可オブジェクトの配列を設定してください。
> [Authorization Objects](#authorization-objects) を参照してください。
>
> AuthComponent により利用される他のコンポーネント。
>
> Auth が `FlashComponent::set()` でフラッシュメッセージを行う
> 必要がある場合に使用する設定。次のキーが利用可能:
>
> - `element` - 使用するエレメント。デフォルトで 'default'。
> - `key` - 使用するキー。デフォルトで 'auth'。
> - `params` - 追加で使用するパラメータの配列。デフォルトで array()。
>
> ログインを扱うコントローラとアクションを表す (文字列や配列で定義した) URL。
> デフォルトは <span class="title-ref">/users/login</span> 。
>
> ログイン後のリダイレクト先のコントローラとアクションを表す (文字列や配列で定義した) URL。
> この値はユーザーが `Auth.redirect` をセッション内に持っている場合には無視されます。
>
> ユーザーがログアウトした後のリダイレクト先となるデフォルトのアクション。
> AuthComponent は post-logout のリダイレクトを扱いませんが、リダイレクト先の URL は
> `AuthComponent::logout()` から返されるものとなります。
> デフォルトは `AuthComponent::$loginAction`。
>
> 許可されていないアクセスに対する処理を制御します。
> デフォルトでは、許可されていないユーザーはリファラの URL か
> `AuthComponent::$loginRedirect` か、もしくは '/' にリダイレクトされます。
> false をセットした場合は、リダイレクトする代わりに ForbiddenException が送出されます。
>
> リクエストオブジェクト。
>
> レスポンスオブジェクト。
>
> 現在のユーザーレコードが保存されているセッションのキー名。指定がない場合は
> "Auth.User" となります。

`method` AuthComponent::**allow**($action, [$action, ...])

`method` AuthComponent::**constructAuthenticate**()

`method` AuthComponent::**constructAuthorize**()

`method` AuthComponent::**deny**($action, [$action, ...])

`method` AuthComponent::**identify**($request, $response)

`method` AuthComponent::**initialize**($Controller)

`method` AuthComponent::**isAuthorized**($user = null, $request = null)

`method` AuthComponent::**loggedIn**()

`method` AuthComponent::**login**($user)

`method` AuthComponent::**logout**()

`method` AuthComponent::**mapActions**($map = array())

<div class="deprecated">

2.4

</div>

`method` AuthComponent::**redirect**($url = null)

<div class="deprecated">

2.3

</div>

`method` AuthComponent::**redirectUrl**($url = null)

::: info Added in version 2.3
:::

`method` AuthComponent::**shutdown**($Controller)

`method` AuthComponent::**startup**($Controller)
