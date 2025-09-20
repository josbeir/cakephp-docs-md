# データバリデーション

データバリデーションは、どのようなアプリケーションにおいても重要な部分です。
なぜなら、これはモデル内のデータがアプリケーションのビジネスルールに
則していることを保証する仕組みだからです。
たとえば、パスワードの長さが８文字以上あることや、
ユーザ名がユニークであることを保証したい場合などが考えられます。
バリデーションルールを定義することで、フォームの扱いが非常に楽になります。

データバリデーションのプロセスには、いろいろと異なった側面があります。
そのうちこのセクションで扱うのは、モデルに特化した部分です。基本的には、
あなたのモデルで save() メソッドをコールした時に起こる内容です。
[FormHelper](../core-libraries/helpers/form) には、バリデーションエラーの
表示がどのように処理されるのかについての記載があります。

データバリデーションへの最初のステップは、
まずモデルの中にバリデーションルールを作成することです。
そのためには、モデル定義の中で Model::validate 配列を使います。
たとえば以下のようになります。 :

``` php
class User extends AppModel {
    public $validate = array();
}
```

この例では User モデルに対して `$validate` 配列を追加していますが、
その配列には（まだ）バリデーションルールが書かれていません。
ここで、users テーブルには login, password, email, born
という項目があるとしましょう。これらの項目に対して適用される
いくつかのシンプルなバリデーションルールを以下の例で示します。 :

``` php
class User extends AppModel {
    public $validate = array(
        'login' => 'alphaNumeric',
        'email' => 'email',
        'born'  => 'date'
    );
}
```

この例では、モデルの項目に対してバリデーションルールを追加する方法を
示しています。login 項目については文字と数字のみが、email と born は
それぞれ書式に合致したメールアドレスと日付が入力できます。
バリデーションルールの定義があると、
入力されたデータが定義されたルールに合致しない場合、
CakePHP がフォームの中で自動的にエラーメッセージを表示してくれます。

CakePHP には多くのバリデーションルールが用意されており、
非常に簡単に利用できます。ビルトインルールのいくつかを使って
電子メールや URL、クレジットカード番号等の書式をチェックできますが、
まずこれらについて説明します。

これらビルトインされたバリデーションルールの利点を活用した、
より複雑な例を以下に示します。 :

``` php
class User extends AppModel {
    public $validate = array(
        'login' => array(
            'alphaNumeric' => array(
                'rule' => 'alphaNumeric',
                'required' => true,
                'message' => '文字と数字のみです'
            ),
            'between' => array(
                'rule' => array('lengthBetween', 5, 15),
                'message' => '5～15文字です'
            )
        ),
        'password' => array(
            'rule' => array('minLength', '8'),
            'message' => '最低8文字です'
        ),
        'email' => 'email',
        'born' => array(
            'rule' => 'date',
            'message' => '正しい値を入れてください',
            'allowEmpty' => true
        )
    );
}
```

ユーザ名(login)に対して２つのバリデーションルールが定義されています。
文字または数字のみで構成されること、
および長さが 5 から 8 までということの２つです。
パスワード(password)項目は 8 文字以上である必要があります。
メールアドレス(email)は正しいメールアドレスで、
また誕生日(born)は正しい日付でなければなりません。
さらに、これらのバリデーションルールに引っかかった時に
CakePHP が使うエラーメッセージを自由に定義できます。

この例でもわかるように、ひとつのフィールドに複数のバリデーションルールを
持つことができます。また、ビルトインルールがあなたの基準に合わなければ、
いつでも必要に応じて独自のバリデーションルールを追加できます。

ここまで、データバリデーションがどう動くのかの全体像を見てきました。
次はこれらのルールをモデルの中でどう定義すればよいのかを見てみましょう。
バリデーションルールを定義するには３つの方法があります：
単純配列、項目単位の単一ルール、項目単位の複数ルールです。

## 単純なルール

名前の通り、これがバリデーションルールを定義する最も簡単な方法です。
この方法で定義する場合の、一般的な書式は以下の通りです。 :

``` php
public $validate = array('fieldName' => 'ruleName');
```

ここで 'fieldName' はルールを定義する対象の項目名です。
'ruleName' は事前に定義されたルール名で、
'alphaNumeric', 'email', 'isUnique' といったものがあります。

たとえば、ユーザーが入力したメールアドレスが書式に合っているか
どうかをチェックするために、以下のようにルールを使えます。 :

``` php
public $validate = array('user_email' => 'email');
```

## 項目単位の単一ルール

この定義テクニックを使うと、バリデーションルールの動きをより良く
コントロールできます。しかしその前に、単一項目に対してルールを追加
する際の一般的に利用パターンを見てみましょう。 :

``` php
public $validate = array(
    'fieldName1' => array(
        // or: array('ruleName', 'param1', 'param2' ...)
        'rule'       => 'ruleName',
        'required'   => true,
        'allowEmpty' => false,
        // or: 'update'
        'on'         => 'create',
        'message'    => 'エラーメッセージ'
    )
);
```

'rule' キーは必須です。　'required' =\> true だけをセットしても、
そのフォームバリデーションは正しく機能しません。このため、
'required' は厳密にはルールではありません。

これでわかるように、各項目（直前の例では項目は一つだけですが）は
'rule', 'required', 'allowEmpty', 'on', 'message' という５つの
キーを持つ配列に関連付けられます。これらのキーについてもう少し
詳しく見てみましょう。

### rule

'rule' キーはバリデーションの方法を定義します。引数には単一の値または
配列を渡します。引数として指定できる値はあなたのモデルのメソッド名、
組み込みで用意されているバリデーションクラスのメソッド名、もしくは
正規表現のいずれかです。デフォルトで利用可能な rule の詳細は
[Core Validation Rules](#core-validation-rules) を参照してください。

rule がパラメータを必要としない場合、'rule' に次のような単一の
値を指定できます。 :

``` php
public $validate = array(
    'login' => array(
        'rule' => 'alphaNumeric'
    )
);
```

（max, min, range のように）rule がパラメータを取る場合、
'rule' を配列で指定します。 :

``` php
public $validate = array(
    'password' => array(
        'rule' => array('minLength', 8)
    )
);
```

配列ベースのルール定義には 'rule' キーが必要ですので覚えておいてください。

### required

このキーにはブール値か、または `create`, `update` を指定できます。
`true` にすると、その項目は必須項目になります。一方 `create` または
`update` にすると、その項目は更新または作成処理の場合にのみ必須となります。
'required' が真と評価されると、
その項目はデータ配列の中に存在しなければならなくなります。
たとえばバリデーションルールが以下のように定義されたとすると、 :

``` php
public $validate = array(
    'login' => array(
        'rule'     => 'alphaNumeric',
        'required' => true
    )
);
```

モデルの save() メソッドに送られるデータにはログイン項目が含まれて
いなければなりません。そうでなければバリデーションは失敗します。
このキーのデフォルト値はブールの「偽」です。

`required => true` はバリデーションルールの `notBlank()`
と同じではありません。 `required => true` は配列の「キー」が
含まれていなければならないという意味であり、
それが値を持たなければならないということではありません。
このため、データセット中にこの項目がない場合バリデーションは
失敗しますが、送信された値が空 ('') の場合は成功することもあります
（ルール定義によります）

::: info Changed in version 2.1
`create` と `update` のサポートが追加されました。
:::

### allowEmpty

`false` にすると、その項目の値は『nonempty』でなければなりません。
ここで "nonempty" は `!empty($value) || is_numeric($value)`
と定義されます。数値のチェックがあるため `$value` がゼロでも
CakePHP は正しく動作します。

`required` と `allowEmpty` の違いは少し紛らわしいです。
`'required' => true` の場合、 `$this->data` の中にその
項目の『キー』がないと、モデルの保存はできません（チェックは
`isset` で行われます）。一方 `'allowEmpty' => false`
にした場合、前述のように現在の項目の『値が』 nonempty で
あること、となります。

### on

'on' キーには 'update' または 'create' のいずれかをセットします。
これにより、新しいレコードの生成時、またはレコードの更新時に
特定のルールが適用されます。

'on' =\> 'create' でルールが定義されていると、そのルールは
新しいレコードを生成する場合に限って適用されます。同様に、
'on' =\> 'update' で定義されている場合はレコードの更新時に
のみ適用されます。

'on' のデフォルト値は null です。'on' が null の場合、
そのルールは生成と更新双方のケースで適用されます。

### message

message キーを使うと、そのルールについてのカスタムバリデーション
エラーメッセージを定義できます。 :

``` php
public $validate = array(
    'password' => array(
        'rule' => array('minLength', 8),
        'message' => 'パスワードは８文字以上必要です。'
    )
);
```

> [!NOTE]
> ルールにかかわらず、message が定義されない場合のバリデーション
> 失敗時のメッセージは "This field cannot be left blank." です。

## １項目に複数のルール

これまでの説明では、単純なルール設定に比べればより柔軟な書き方を
学んできましたが、さらにきめ細かいデータバリデーション制御の
方法があります。次のテクニックを使うと、１つのモデル項目に対して
複数のバリデーションルールを割り当てることができます。

１つの項目に対して複数のバリデーションルールを割り当てたい場合、
その基本的な書き方は以下のようになります。 :

``` php
public $validate = array(
    'fieldName' => array(
        'ruleName' => array(
            'rule' => 'ruleName',
            // ここに on や required などその他のキーを書く
        ),
        'ruleName2' => array(
            'rule' => 'ruleName2',
            // ここに on や required などその他のキーを書く
        )
    )
);
```

これでわかるように、この書き方はこれまでのセクションで出てきた
ものととてもよく似ています。それぞれの項目にはバリデーション
パラメータの配列が一つだけ書かれています。このケースでは、
'fieldName' はルールのインデックスの配列から構成されます。
それぞれの 'ruleName' にはバリデーションパラメータの別々の
配列を保持しています。

もう少し実践的な例を見てみましょう。 :

``` php
public $validate = array(
    'login' => array(
        'loginRule-1' => array(
            'rule' => 'alphaNumeric',
            'message' => 'アルファベットまたは数字のみです',
         ),
        'loginRule-2' => array(
            'rule' => array('minLength', 8),
            'message' => '最低８文字です'
        )
    )
);
```

この例では login 項目に対して loginRule-1 と loginRule-2
という２つのルールを定義しています。見ての通り、各ルールは
任意の名前で区別されています。

項目単位の複数ルールを使っている場合、'required' と 'allowEmpty'
キーは先頭のルールに一度だけ現れるようにしなければなりません。

### last

項目単位の複数ルールでは、デフォルトでは特定のルールに引っかかると
そのルールのエラーメッセージが返され、その項目についてそれ以降の
ルールは処理されません。もしあるルールが失敗しても
それ以降のバリデーションを継続したい場合は、そのルールで `last`
キーを `false` にします。

以下の例では "rule1" のチェックに引っかかっても "rule2" が
引き続き実行され、さらに "rule2" にも引っかかった場合は両方の
エラーメッセージが返されます。 :

``` php
public $validate = array(
    'login' => array(
        'rule1' => array(
            'rule' => 'alphaNumeric',
            'message' => 'アルファベットと数字だけを使えます',
            'last'    => false
         ),
        'rule2' => array(
            'rule' => array('minLength', 8),
            'message' => '８文字以上です'
        )
    )
);
```

この配列形式でバリデーションルールを指定する場合、
`message` キーは省略できます。以下の例を見てください。 :

``` php
public $validate = array(
    'login' => array(
        'アルファベットと数字だけです' => array(
            'rule' => 'alphaNumeric',
         ),
    )
);
```

`alphaNumeric` ルールに引っかかった場合、 `message` キーが
指定されていないので、'アルファベットと数字だけです' がエラー
メッセージとして返されます。

## カスタムバリデーションルール

ここまで読んでもなお希望するような仕組みを見つけきれない方については、
いつでもプログラマ独自のバリデーションルールを作成できます。
これには２つの方法があります。カスタム正規表現を使う方法と、
カスタムバリデーションメソッドを定義する方法です。

### カスタム正規表現によるバリデーション

自分が必要とするバリデーションテクニックを正規表現マッチングを
使って実現できるのであれば、ある項目のバリデーションルールとして
カスタム正規表現を定義できます。 :

``` php
public $validate = array(
    'login' => array(
        'rule' => '/^[a-z0-9]{3,}$/i',
        'message' => '文字と数字のみ、３文字以上'
    )
);
```

上記の例では login が文字と数字のみで構成され、最低３文字あるか
どうかをチェックしています。

`rule` で指定する正規表現は、スラッシュで囲まなければなりません。
末尾のスラッシュの後に 'i' を付けると、その正規表現は case
*i*nsensitive（大文字と小文字を区別しないこをと）を表します。

### 独自のバリデーションメソッドを追加する

データをチェックするのに、正規表現パターンだけでは機能が足りない
場合があります。たとえばプロモーションコードが使われた回数が
25 回を超えていないことをチェックしたい場合、以下の様な独自の
バリデーション関数を追加する必要があります。 :

``` php
class User extends AppModel {

    public $validate = array(
        'promotion_code' => array(
            'rule' => array('limitDuplicates', 25),
            'message' => '利用回数の制限を超えました。'
        )
    );

    public function limitDuplicates($check, $limit) {
        // $check には array('promotion_code' => '入力値') が入る
        // $limit には 25 が入っている
        $existingPromoCount = $this->find('count', array(
            'conditions' => $check,
            'recursive' => -1
        ));
        return $existingPromoCount < $limit;
    }
}
```

チェック対象の現在の項目が、項目名をキー、ポストされたデータを
値とする連想配列として構成され、この関数の第一パラメータとして
渡されます。

作成したバリデーション用の関数に追加のパラメータを渡したい場合は、
要素を 'rule' 配列に入れ、関数内でそれらを（主となる \$check
パラメータの後の）追加パラメータとして処理します。

作成したバリデーション関数は（上記の例のように）モデルの中に書くか、
またはモデルを実装するビヘイビアとして書きます。これにはマップされた
メソッドも含まれます。

モデルやビヘイビアメソッドは、 `Validation` クラスの中のメソッド
を検索する前の段階で、最初にチェックされます。つまり、
（ `alphaNumeric()` のような）既存のメソッドを、アプリケーション
のレベル（ `AppModel` にメソッドを追加する）やモデルのレベルで
オーバーライドできるということです。

複数項目で利用可能なバリデーションルールを作成する場合、\$check
配列から項目の値を取り出す際には注意が必要です。\$check 配列は
フォームの項目名をキー、項目値を値として渡されます。チェック対象の
レコードの全体は \$this-\>data メンバ変数に入っています。 :

``` php
class Post extends AppModel {

    public $validate = array(
        'slug' => array(
            'rule' => 'alphaNumericDashUnderscore',
            'message' => 'これには文字、数字、ハイフン、' .
                'アンダースコアが使えます'
        )
    );

    public function alphaNumericDashUnderscore($check) {
        // $data 配列はフォームの項目名をキーとして渡される。
        // この関数が汎用的に使えるように、値を展開する必要がある。
        $value = array_values($check);
        $value = $value[0];

        return preg_match('|^[0-9a-zA-Z_-]*$|', $value);
    }
}
```

> [!NOTE]
> 独自のバリデーションメソッドは、スコープが `public` でなければ
> なりません。 `protected` や `private` なバリデーション
> メソッドはサポートされていません。

入力値が有効の場合、メソッドは `true` を返さなければなりません。
無効の場合は `false` を返します。これら以外の返り値が文字列であれば、
それはエラーメッセージとして表示されます。文字列を返すということは、
チェックでエラーになったことを表します。その文字列は \$validate
配列に設定された message を上書きし、その項目がエラーになった
理由としてビューのフォームに表示されます。

## バリデーションルールを動的に変更する

`$validate` プロパティを使ってバリデーションルールを定義するのは、
それぞれのモデルについて静的にルールを定義するにはよい方法です。
しかしながら、事前に定義された設定に対して動的にバリデーションルール
を追加／変更／削除したいケースがあります。

バリデーションルールはすべて `ModelValidator` オブジェクトに
格納されますが、ここにはあなたのモデルにおけるそれぞれの項目の
ルールセットが保持されます。新しいバリデーションルールを定義するのは、
このオブジェクトに対して希望する項目の新しいバリデーションメソッドを
格納したいことを伝えるのと同じくらい簡単です。

### 新しいバリデーションルールの追加

::: info Added in version 2.2
:::

`ModelValidator` オブジェクトが内部設定に新しい項目を追加するためには
いくつかのやり方があります。最初は `add` メソッドを使う方法です。 :

``` php
// Inside a model class
$this->validator()->add('password', 'required', array(
    'rule' => 'notBlank',
    'required' => 'create'
));
```

これはモデルの `password` 項目に対して単一のルールを追加します。
add に対してさらに他の add への呼び出しを、好きなだけ繋げられます。 :

``` php
// Inside a model class
$this->validator()
    ->add('password', 'required', array(
        'rule' => 'notBlank',
        'required' => 'create'
    ))
    ->add('password', 'size', array(
        'rule' => array('lengthBetween', 8, 20),
        'message' => 'パスワードは最低8文字です。'
    ));
```

一つの項目に対して一度に複数のルールを追加することもできます。 :

``` php
$this->validator()->add('password', array(
    'required' => array(
        'rule' => 'notBlank',
        'required' => 'create'
    ),
    'size' => array(
        'rule' => array('lengthBetween', 8, 20),
        'message' => 'パスワードは最低8文字です。'
    )
));
```

また、validator オブジェクトに対して配列形式のインターフェース
を使って直接ルールを設定することもできます。 :

``` php
$validator = $this->validator();
$validator['username'] = array(
    'unique' => array(
        'rule' => 'isUnique',
        'required' => 'create'
    ),
    'alphanumeric' => array(
        'rule' => 'alphanumeric'
    )
);
```

### 現在のバリデーションルールを変更する

::: info Added in version 2.2
:::

validator オブジェクトを使って現在のバリデーションルールを変更する
こともできます。現在のルールを変更する場合、項目にメソッドを追加したり、
またその項目からルールセットを完全に取り去ってしまうなど、
いくつかの方法があります。 :

``` php
// モデルクラスの中で
$this->validator()->getField('password')->setRule('required', array(
    'rule' => 'required',
    'required' => true
));
```

これと似たようなやり方で、その項目におけるルールをすべて完全に
差し替えてしまうこともできます。 :

``` php
// モデルクラスの中で
$this->validator()->getField('password')->setRules(array(
    'required' => array(...),
    'otherRule' => array(...)
));
```

ルールの中の一つのプロパティだけを変更したい場合は、
`CakeValidationRule` オブジェクトの中に入って直接プロパティを
セットすることもできます。 :

``` php
// モデルクラスの中で
$this->validator()->getField('password')
    ->getRule('required')->message = 'この項目は必須入力です';
```

`CakeValidationRule` におけるどのプロパティも、たとえば 'message'
や 'allowEmpty' といった配列キーのように、バリデーションルールの
プロパティを定義する際に使える自分自身の名前を配列キーから取得します。

ルールセットに新しいルールを追加する場合、配列インターフェースを使って
既存のルールを変更することも可能です。 :

``` php
$validator = $this->validator();
$validator['username']['unique'] = array(
    'rule' => 'isUnique',
    'required' => 'create'
);

$validator['username']['unique']->last = true;
$validator['username']['unique']->message = 'その名前はすでに使われています';
```

### ルールセットからルールを削除する

::: info Added in version 2.2
:::

ある項目のルールセットにおいて、ルールすべてを削除することも
一つのルールを削除することも、どちらも可能です。 :

``` php
// ある項目からすべてのルールを完全に削除する
$this->validator()->remove('username');

// password から 'required' ルールを削除する
$this->validator()->remove('password', 'required');
```

配列インターフェースを使って削除することもできます。 :

``` php
$validator = $this->validator();
// ある項目のすべてのルールを完全に削除する
unset($validator['username']);

// password から 'required' ルールを削除する
unset($validator['password']['required']);
```

## コア・バリデーションルール

`class` **Validation**

CakePHP のバリデーションクラスには多くのバリデーションルールがあり、
モデルデータのバリデーションをより簡単にしています。
このクラスにはよく使われるバリデーションテクニックが組み込まれており、
プログラマが独自のルールを書かなくても済むようになっています。
以下にすべてのルールの一覧を、その使用例とともに示します。

> この項目は文字と数字だけで構成されなければなりません。 :
>
> ``` php
> public $validate = array(
>     'login' => array(
>         'rule' => 'alphaNumeric',
>         'message' => 'ユーザ名には文字と数字だけしか使えません。'
>     )
> );
> ```
>
> この項目のデータ長は指定された数値の範囲に収まっていなければ
> なりません。最小値と最大値の両方を指定する必要があります。 :
>
> ``` php
> public $validate = array(
>     'password' => array(
>         'rule' => array('lengthBetween', 5, 15),
>         'message' => 'パスワードは5～15文字でなければなりません。'
>     )
> );
> ```
>
> データは文字数でチェックされます。バイト数ではありません。
> もし、UTF-8 互換の代わりに、純粋な ASCII 入力に対して検証したい場合、
> カスタムバリデータを書く必要があります。
>
> このルールはその項目が未入力のまま、もしくはその値がホワイトスペース
> だけで構成されていることを確認するときに使います。ホワイトスペース
> 文字として有効なのは空白、タブ、復帰（キャリッジリターン）、
> 改行（ニューライン）です。 :
>
> ``` php
> public $validate = array(
>     'id' => array(
>         'rule' => 'blank',
>         'on'   => 'create'
>     )
> );
> ```
>
> その項目のデータはブール値でなければなりません。有効な値は
> true, false, 数字の 0 または 1、文字列の '0' または '1' です。 :
>
> ``` php
> public $validate = array(
>     'myCheckbox' => array(
>         'rule' => array('boolean'),
>         'message' => 'myCheckbox の値が誤っています'
>     )
> );
> ```
>
> このルールはそのデータが有効なクレジットカード番号かどうかを
> チェックします。これにはパラメータとして 'type', 'deep', 'regex'
> の３つが必要です。
>
> 'type' キーには 'fast', 'all' または以下のいずれかを指定します:
>
> - amex
> - bankcard
> - diners
> - disc
> - electron
> - enroute
> - jcb
> - maestro
> - mc
> - solo
> - switch
> - visa
> - voyager
>
> 'type' が 'fast' の場合、そのデータがメジャーなクレジットカードの
> 番号のフォーマットになっているかチェックします。'type' を 'all'
> にすると、すべてのクレジットカードタイプをチェックします。
> また、マッチさせたいタイプだけを配列で指定することもできます。
>
> 'deep' キーにはブール値を指定します。true が指定されると、その
> クレジットカードの Luhn アルゴリズムをチェックします
> (<https://en.wikipedia.org/wiki/Luhn_algorithm>).
> デフォルトは false です。
>
> 'regex' キーにはクレジットカード番号の妥当性チェックで使うための
> 独自の正規表現を指定できます。 :
>
> ``` php
> public $validate = array(
>     'ccnumber' => array(
>         'rule' => array('cc', array('visa', 'maestro'), false, null),
>         'message' => 'クレジットカード番号が正しくありません。'
>     )
> );
> ```
>
> comparison は数値を比較します。サポートしている機能は『より大きい』
> 『より小さい』『以上』『以下』『等しい』『等しくない』です。
> 以下にいくつか例を示します。 :
>
> ``` php
> public $validate = array(
>     'age' => array(
>         'rule' => array('comparison', '>=', 18),
>         'message' => '条件は１８歳以上です。'
>     )
> );
>
> public $validate = array(
>     'age' => array(
>         'rule' => array('comparison', 'greater or equal', 18),
>         'message' => '条件は１８歳以上です。'
>     )
> );
> ```
>
> カスタム正規表現が必要な場合に使われます。 :
>
> ``` php
> public $validate = array(
>     'infinite' => array(
>         'rule' => array('custom', '\u221E'),
>         'message' => '∞ を入力してください'
>     )
> );
> ```
>
> このルールは、データが有効な日付のフォーマットで入力されたか
> どうかを検証します。単一のパラメータ（配列でもよい）を渡すと、
> それを元に入力された日付のフォーマットをチェックします。
> パラメータのに値は以下のいずれかを指定します:
>
> - 'dmy' たとえば 27-12-2006 または 27-12-06 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'mdy' たとえば 12-27-2006 または 12-27-06 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'ymd' たとえば 2006-12-27 または 06-12-27 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'dMy' たとえば 27 December 2006 または 27 Dec 2006
> - 'Mdy' たとえば December 27, 2006 または Dec 27, 2006
>   （カンマはオプション）
> - 'My' たとえば December 2006 または Dec 2006
> - 'my' たとえば 12/2006 または 12/06 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'ym' たとえば 2006/12 または 06/12 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'y' たとえば 2006 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
>
> キーが指定されない場合、'ymd' がデフォルトのキーとして使われます。 :
>
> ``` php
> public $validate = array(
>     'born' => array(
>         'rule'       => array('date', 'ymd'),
>         'message'    => '有効な日付を YY-MM-DD フォーマットで入力してください。',
>         'allowEmpty' => true
>     )
> );
> ```
>
> 大量のデータが特定の日付フォーマットを要求している場合は、様々な
> 日付フォーマットを受け取ってから、それらを変換するという力仕事を考えて
> みてもよいでしょう。利用者のために一度がんばれば、後が楽になります。
>
> ::: info Changed in version 2.4
> `ym` および `y` フォーマットが追加されました。
> :::
>
> このルールはデータが有効な日時のフォーマットに合致していることを
> 保証します。日付のフォーマットを指定するためのパラメータ
> （配列でもよい）が渡されます。パラメータの値は以下のいずれかです:
>
> - 'dmy' たとえば 27-12-2006 または 27-12-06 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'mdy' たとえば 12-27-2006 またはr 12-27-06 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'ymd' たとえば 2006-12-27 または 06-12-27 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
> - 'dMy' たとえば 27 December 2006 または 27 Dec 2006
> - 'Mdy' たとえば December 27, 2006 または Dec 27, 2006
>   （カンマはオプションです）
> - 'My' たとえば December 2006 または Dec 2006
> - 'my' たとえば 12/2006 or 12/06 （区切り文字には空白、
>   ピリオド、ハイフン、スラッシュが使えます）
>
> キーが指定されない場合、'ymd' がデフォルトのキーとして使われます。 :
>
> ``` php
> public $validate = array(
>     'birthday' => array(
>         'rule' => array('datetime', 'dmy'),
>         'message'    => '有効な日時を入力してください。',
>     )
> );
> ```
>
> ２番めのパラメータには正規表現を指定します。このパラメータが指定
> されると、そのパターンのみが有効となります。
>
> date() と異なり、datetime() は日時を検証します。
>
> このルールは、データが有効な数値であることを保証します。パラメータ
> として小数部の桁数を指定します。パラメータが渡された場合、その
> データは浮動小数点形式として検証されるため、小数点の後ろに数字がない
> 場合は正当な数値としてはみなされません。 :
>
> ``` php
> public $validate = array(
>     'price' => array(
>         'rule' => array('decimal', 2)
>     )
> );
> ```
>
> データが正しいメールアドレスかどうかをチェックします。オプションの
> 第二パラメータとして true を渡すと、ホスト部が有効なアドレスか
> どうかも合わせてチェックします。 :
>
> ``` php
> public $validate = array('email' => array('rule' => 'email'));
>
> public $validate = array(
>     'email' => array(
>         'rule' => array('email', true),
>         'message' => '有効なメールアドレスを入力してください。'
>     )
> );
> ```
>
> 入力値がパラメータで与えられた値と同じ型で、
> かつ同じ値であるかどうかをチェックします。
> :
>
> ``` php
> public $validate = array(
>     'food' => array(
>         'rule' => array('equalTo', 'cake'),
>         'message' => 'この値は文字列 cake でなければなりません。'
>     )
> );
> ```
>
> .jpg や .png といった有効なファイル拡張子かどうかをチェックします。
> 複数の拡張子を配列として渡すこともできます。
>
> ``` php
> public $validate = array(
>     'image' => array(
>         'rule' => array(
>             'extension',
>             array('gif', 'jpeg', 'png', 'jpg')
>         ),
>         'message' => '有効な画像ファイルを指定してください。'
>     )
> );
> ```
>
> ファイルサイズのチェックを行います。使用したい比較のタイプを
> `$operator` （演算子）として指定できます。ここでは
> `~Validation::comparison()` がサポートしている演算子を
> すべてサポートしています。 `$check` が `tmp_name` キーを
> 含んでいる場合、このメソッドは `$_FILES` から渡される値を
> 自動的に解析し、`tmp_name` キーがあれば取り出します。 :
>
> ``` php
> public $validate = array(
>     'image' => array(
>         'rule' => array('fileSize', '<=', '1MB'),
>         'message' => '画像は 1MB 未満でなければなりません。'
>     )
> );
> ```
>
> ::: info Added in version 2.3
> このメソッドは 2.3 で追加されました。
> :::
>
> このルールは、値が与えられた候補セットに含まれることを保証します。
> パラメータとして値の配列を指定する必要があります。項目値が与えられた
> 配列の値のいずれかにマッチする場合、その項目は有効であるとみなされます。
>
> 使用例:
>
> ``` php
> public $validate = array(
>     'function' => array(
>          'allowedChoice' => array(
>              'rule' => array('inList', array('Foo', 'Bar')),
>              'message' => 'Foo または Bar を入力してください。'
>          )
>      )
>  );
> ```
>
> デフォルトでは比較の際に大文字と小文字を区別します。 `$caseInsensitive`
> を true で指定すると大文字と小文字を区別せずに比較します。
>
> このルールは有効な IPv4 もしくは IPv6 アドレスが入力されたことを
> 保証します。オプションとして 'both'（デフォルト）、'IPv4'、'IPv6'
> を指定可能です。
>
> ``` php
> public $validate = array(
>     'clientip' => array(
>         'rule' => array('ip', 'IPv4'), // or 'IPv6' or 'both' (default)
>         'message' => '有効なIPアドレスを入力してください。'
>     )
> );
> ```

`method` Validation::**Model::isUnique()**()

## ローカライズされたバリデーション

バリデーションルール phone() と postal() に渡された国別プリフィックスを、
適切な名前で別のクラスに渡す方法がわからない場合があります。たとえば
あなたがオランダに住んでいて、以下の様なクラスを作ったとします。 :

``` php
class NlValidation {
    public static function phone($check) {
        // ...
    }
    public static function postal($check) {
        // ...
    }
}
```

このファイルは `APP/Validation/` や `App/PluginName/Validation/`
に置かれる可能性がありますが、利用する前に App::uses() 経由で
インポートしておく必要があります。その後、あなたのモデルの
バリデーションで、以下のように NlValidation を利用できます。 :

``` php
public $validate = array(
    'phone_no' => array('rule' => array('phone', null, 'nl')),
    'postal_code' => array('rule' => array('postal', null, 'nl')),
);
```

モデルのデータが検査される際、Validation は `nl` ロケールを
処理できないので `NlValidation::postal()` に処理を委譲し、
そのメソッドの返り値がババリデーションの結果（成功／失敗）
として使われます。このアプローチに従って、ロケールのサブセット
もしくはグループを扱うクラスを作ることで、大きな処理を書かずに済みます。
個々のバリデーションメソッドの使い方は変えずに、別の
バリデータを追加できるようになっています。

> [!TIP]
> ローカライズされたプラグインでも、すでに多くのルールが利用
> できるようになっています: <https://github.com/cakephp/localized>
> さらに、あなたがローカライズしたバリデーションルールも、
> ぜひご提供ください。

- [Validating Data From The Controller](../data-validation/validating-data-from-the-controller)
