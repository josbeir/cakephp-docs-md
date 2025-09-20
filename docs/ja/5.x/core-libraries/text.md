# Text

`class` Cake\\Utility\\**Text**

Text クラスは文字列を作ったり操作したりする便利なメソッドを持っており、通常は static にアクセスします。例: `Text::uuid()`

`View` の外側で `Cake\View\Helper\TextHelper` の機能が必要なときは `Text` クラスを使ってください。 :

``` php
namespace App\Controller;

use Cake\Utility\Text;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Auth')
    };

    public function afterLogin()
    {
        $message = $this->Users->find('new_message')->first();
        if (!empty($message)) {
            // ユーザーに新しいメッセージを通知
            $this->Flash->success(__(
                'You have a new message: {0}',
                Text::truncate($message['Message']['body'], 255, ['html' => true])
            ));
        }
    }
}
```

## ASCII 文字への変換

transliterate はデフォルトで、与えられた文字列の文字すべてを同じ意味の ASCII 文字に置き換えます。
このメソッドは UTF-8 エンコーディングであることが前提になっています。
文字変換はトランスリテレーション識別子で制御することができます。
この識別子は引数 `$transliteratorId` を使って渡すか、
`Text::setTransliteratorId()` を使ってデフォルトの識別子文字列を変更することができます。
ICU のトランスリテレーション識別子は基本的に `<元のスクリプト>:<変換後のスクリプト>` の形で、
`;` で区切って複数の変換の組み合わせを指定することができます。
トランスリテレーション識別子についての詳細は
[ここ](https://unicode-org.github.io/icu/userguide/transforms/general/#transliterator-identifiers)
を参照してください。 :

``` text
// apple puree
Text::transliterate('apple purée');

// Ubermensch (ラテン文字だけを変換する)
Text::transliterate('Übérmensch', 'Latin-ASCII;');
```

## URL に安全な文字列の作成

slug はすべての文字を ASCII バージョンにトランスリテレートし（別言語の文字に置き換え）、
マッチしない文字や空白はダッシュに変換します。
slug メソッドは UTF-8 エンコーディングであること前提にしています。

slug をコントロールするオプション配列を渡すことができます。
`$options` は文字列で指定することもでき、その場合は置き換え文字列として使われます。
利用可能なオプションは次の通りです:

- `replacement` 置き換え文字列。デフォルトは '-' 。

- `transliteratorId` 有効なトランスリテレータIDの文字列。  
  デフォルトの `null` なら、`Text::$_defaultTransliteratorId` が使われます。
  <span class="title-ref">false</span> なら、トランスリテレーションは実行されず、非単語のみが削除されます。

- `preserve` 保持したい特定の非単語文字。デフォルトは `null` 。  
  たとえば、このオプションに '.' をセットすることでクリーンなファイル名を生成することができます。 :

  ``` text
  // apple-puree
  Text::slug('apple purée');

  // apple_puree
  Text::slug('apple purée', '_');

  // foo-bar.tar.gz
  Text::slug('foo bar.tar.gz', ['preserve' => '.']);
  ```

## UUID の生成

UUID メソッドは `4122` 準拠のユニークな識別子を生成するのに使います。
UUID は `485fc381-e790-47a3-9794-1337c0a8fe68` というフォーマットの 128 ビットの文字列です。 :

``` css
Text::uuid(); // 485fc381-e790-47a3-9794-1337c0a8fe68
```

## 単純な文字列のパース

`$separator` を使って文字列をトークン化します。その際、 `$leftBound` と `$rightBound` の間にある `$separator` は無視されます。

このメソッドはタグリストのような標準フォーマットを持つデータを分割するのに役立ちます。 :

``` php
$data = "cakephp 'great framework' php";
$result = Text::tokenize($data, ' ', "'", "'");
// 結果
['cakephp', "'great framework'", 'php'];
```

`method` Cake\\Utility\\Text::**parseFileSize**(string $size, $default)

このメソッドは人が読みやすいバイトのサイズのフォーマットから、バイトの整数値へと変換します。 :

``` php
$int = Text::parseFileSize('2GB');
```

## 文字列のフォーマット

insert メソッドは文字列テンプレートを作り、key/value で置き換えるのに使います。 :

``` css
Text::insert(
    'My name is :name and I am :age years old.',
    ['name' => 'Bob', 'age' => '65']
);
// これを返す: "My name is Bob and I am 65 years old."
```

`$options` 内の 'clean' キーに従って、 `Text::insert` でフォーマットされた文字列を掃除します。
デフォルトで method に使われるのは text ですが html も使えます。
この機能の目的は、`Text::insert` で置き換えられなかった、プレースホルダ周辺のすべての空白と不要なマークアップを置き換えることにあります。

options 配列内で下記のオプションを使うことができます。 :

``` php
$options = [
    'clean' => [
        'method' => 'text', // もしくは html
    ],
    'before' => '',
    'after' => ''
];
```

## テキストの改行

テキストのブロックを幅やインデントを指定して改行させます。
単語が別の行に分離されないように賢く改行してくれます。 :

``` php
$text = 'This is the song that never ends.';
$result = Text::wrap($text, 22);

// 戻り値
This is the song that
never ends.
```

オプション配列でどのように改行されるのかを制御できます。
利用できるオプションは次の通りです。

- `width` 改行の幅。デフォルトは 72。
- `wordWrap` 単語単位で改行するか。デフォルトは `true` 。
- `indent` インデントに使う文字。デフォルトは '' 。
- `indentAt` 何行目からテキストのインデントを開始するか。デフォルトは 0 。

生成されたブロックの合計幅が内部的なインデントと同じ幅を確実に超えないようにする必要があるなら、
`wrap()` の代わりに `wrapBlock()` を使う必要があります。
これは例えばコンソール向けのテキストを生成するのにとても便利です。
`wrap()` と同じオプションが使えます。 :

``` php
$text = 'This is the song that never ends. This is the song that never ends.';
$result = Text::wrapBlock($text, [
    'width' => 22,
    'indent' => ' → ',
    'indentAt' => 1
]);

// 戻り値
This is the song that
 → never ends. This
 → is the song that
 → never ends.
```

## 文字列の一部をハイライトする

`method` Cake\\Utility\\Text::**highlight**(string $haystack, string $needle, array $options = [] )

`$options['format']` で指定された文字列か、デフォルトの文字列を使って `$haystack` 中の `$needle` をハイライトします。

オプション:

- `format` string - ハイライトするフレーズに適用する HTML パーツ
- `html` bool - `true` ならすべての HTML タグを無視して、正確にテキストのみをハイライトするよう保証します。

例:

``` php
// TextHelper として呼ぶ
echo $this->Text->highlight(
    $lastSentence,
    '使って',
    ['format' => '<span class="highlight">\1</span>']
);

// Text として呼ぶ
use Cake\Utility\Text;

echo Text::highlight(
    $lastSentence,
    '使って',
    ['format' => '<span class="highlight">\1</span>']
);
```

出力:

``` text
$options['format'] で指定された文字列か、デフォルトの文字列を<span class="highlight">使って</span>
$haystack 中の $needle をハイライトします。
```

## リンク除去

`method` Cake\\Utility\\Text::**stripLinks**($text)

渡された `$text` から HTML リンクを取り除きます。

## テキストの切り詰め

`method` Cake\\Utility\\Text::**truncate**(string $text, int $length = 100, array $options)

`$text` が `$length` より長い場合、このメソッドはそれを `$length` の長さに切り詰め、
`'ellipsis'` が定義されているなら末尾にその文字列を追加します。
もし `'exact'` に `false` が渡されたなら、 `$length` を超えた最初の空白で切り詰められます。
もし `'html'` に `true` が渡されたなら、HTML タグは尊重され、削除されなくなります。

`$options` はすべての追加パラメーターを渡すのに使われ、下記のようなキーがデフォルトになっており、すべてが省略可能です。 :

``` php
[
    'ellipsis' => '...',
    'exact' => true,
    'html' => false
]
```

例:

``` php
// TextHelper として呼ぶ
echo $this->Text->truncate(
    'The killer crept forward and tripped on the rug.',
    22,
    [
        'ellipsis' => '...',
        'exact' => false
    ]
);

// Text として呼ぶ
use Cake\Utility\Text;

echo Text::truncate(
    'The killer crept forward and tripped on the rug.',
    22,
    [
        'ellipsis' => '...',
        'exact' => false
    ]
);
```

出力:

    The killer crept...

## 文字列の末尾を切り詰める

`method` Cake\\Utility\\Text::**tail**(string $text, int $length = 100, array $options)

`$text` が `$length` より長い場合、このメソッドは先頭から差となる長さの文字列を取り除き、
`'ellipsis'` が定義されているなら先頭にその文字列を追加します。
もし `'exact'` に `false` が渡されたなら、切り詰めが本来発生したであろう場所の前にある最初の空白で切り詰められます。

`$options` はすべての追加パラメーターを渡すのに使われ、下記のようなキーがデフォルトになっており、すべてが省略可能です。 :

``` php
[
    'ellipsis' => '...',
    'exact' => true
]
```

例:

``` php
$sampleText = 'I packed my bag and in it I put a PSP, a PS3, a TV, ' .
    'a C# program that can divide by zero, death metal t-shirts'

// TextHelper として呼ぶ
echo $this->Text->tail(
    $sampleText,
    70,
    [
        'ellipsis' => '...',
        'exact' => false
    ]
);

// Text として呼ぶ
use Cake\Utility\Text;

echo Text::tail(
    $sampleText,
    70,
    [
        'ellipsis' => '...',
        'exact' => false
    ]
);
```

出力:

    ...a TV, a C# program that can divide by zero, death metal t-shirts

## 抜粋の抽出

`method` Cake\\Utility\\Text::**excerpt**(string $haystack, string $needle, integer $radius=100, string $ellipsis="...")

`$haystack` から、 `$needle` の前後 `$radius` で指定された文字数分を含む文字列を抜粋として抽出し、
その先頭と末尾に `$ellipsis` の文字列を追加します。
このメソッドは検索結果には特に便利でしょう。クエリー文字列やキーワードを結果の文章中とともに表示することができます。 :

``` php
// TextHelper として呼ぶ
echo $this->Text->excerpt($lastParagraph, 'method', 50, '...');

// Text として呼ぶ
use Cake\Utility\Text;

echo Text::excerpt($lastParagraph, 'method', 50, '...');
```

出力:

    ... by $radius, and prefix/suffix with $ellipsis. This method is especially
    handy for search results. The query...

## 配列を文章的なものに変換する

`method` Cake\\Utility\\Text::**toList**(array $list, $and='and', $separator=', ')

最後の２要素が 'and' で繋がっている、カンマ区切りのリストを生成します。 :

``` php
$colors = ['red', 'orange', 'yellow', 'green', 'blue', 'indigo', 'violet'];

// TextHelper として呼ぶ
echo $this->Text->toList($colors);

// Text として呼ぶ
use Cake\Utility\Text;

echo Text::toList($colors);
```

出力:

    red, orange, yellow, green, blue, indigo and violet
