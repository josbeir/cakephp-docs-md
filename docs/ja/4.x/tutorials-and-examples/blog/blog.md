# ブログチュートリアル

このチュートリアルでは、シンプルなブログアプリケーションを作成します。CakePHP を取得して
インストールし、データベースの設定を行い、ブログの投稿記事の一覧表示 (*list*)、追加 (*add*)、
編集 (*edit*)、削除 (*delete*) などのアプリケーションロジックを作成します。

必要なもの:

1.  動作しているウェブサーバー。Apache を使っているという前提で書いてありますが、
    他の種類のサーバーを使用する場合でも、ほぼ同じにいけるはずです。サーバーの設定を
    少し変える必要があるかもしれませんが、たいていの人は、そのままの設定で CakePHP を
    動作させることが可能です。PHP |minphpversion| 以上が動作していること、
    そして PHP で `mbstring` と `intl` が有効になっていることを確認してください。
2.  データベースサーバー。このチュートリアルでは MySQL を使用します。
    データベースを作成できる程度の SQL の知識が必要です。その先は CakePHP が面倒を
    みてくれます。MySQL を使用するので、PHP で `pdo_mysql` が有効になっていることを
    確認してください。
3.  PHP の基本的な知識。

それでは、はじめましょう！

## CakePHP の取得

最も簡単な CakePHP のインストール方法は Composer を使う方法です。Composer は、
ターミナルやコマンドラインプロンプトから CakePHP をインストールするシンプルな方法です。
まだ準備ができていない場合、最初に Composer のダウンロードおよびインストールが必要です。
cURL がインストールされていたら、以下のように実行するのが簡単です。 :

    curl -s https://getcomposer.org/installer | php

もしくは [Composer のウェブサイト](https://getcomposer.org/download/)
から `composer.phar` をダウンロードすることができます。

そして、CakePHP アプリケーションのスケルトンを使用したいディレクトリーにインストールするために、
インストールディレクトリーからターミナルに以下の行をシンプルにタイプしてください。
この例では、"blog" を使用しますが、他のものに自由に変更できます。 :

    php composer.phar create-project --prefer-dist cakephp/app:4.* blog

Composer をグローバルにすでに設定している場合は、以下のようにタイプすることもできます。 :

    composer self-update && composer create-project --prefer-dist cakephp/app:4.* blog

Composer を使うメリットは、 正しいファイルパーミッションの設定や、 **config/app.php**
ファイルの作成などのような、重要なセットアップを自動的に完全にしてくれることです。

CakePHP をインストールする他の方法があります。 Composer を使いたくない場合、
[インストール](../../installation) セクションをご覧ください。

CakePHP のダウンロードやインストール方法にかかわらず、いったんセットアップが完了すると、
ディレクトリー構成は以下のようになります。 :

    /cake_install
        /bin
        /config
        /logs
        /plugins
        /src
        /tests
        /tmp
        /vendor
        /webroot
        .editorconfig
        .gitignore
        .htaccess
        .travis.yml
        composer.json
        index.php
        phpunit.xml.dist
        README.md

CakePHP のディレクトリー構造がどのように働くかを学ぶのにいい機会かもしれません。
[CakePHP のフォルダー構成](../../intro/cakephp-folder-structure) セクションをご覧ください。

## tmp と logs ディレクトリーのパーミッション

ウェブサーバーが書き込みを行うために、 `tmp` と `logs` ディレクトリーに適切なパーミッションの
設定をする必要があります。インストールに Composer を用いた場合はこれはすでに終わっており、
"Permissions set on \<folder\>" というメッセージで確認できます。もしエラーメッセージが出ている場合、
または手動で設定したい場合は、ウェブサーバーを動作させているユーザーを見つけるために
`` <?= `whoami`; ?> `` をウェブサーバーで実行するのが最も良いでしょう。
そしてそのユーザーにこれらの2つのディレクトリーの所有権を変更します。
実行するコマンドは (\*nixで) このようになります。 :

    chown -R www-data tmp
    chown -R www-data logs

何らかの理由で CakePHP がそのディレクトリーに書き込めない場合は、
プロダクションモードでない限り警告で通知されます。

推奨はしませんが、もしこの方法でウェブサーバーに権限の設定ができない場合、
以下のコマンドを実行することで書き込み権限をフォルダーに与えることができます。 :

    chmod -R 777 tmp
    chmod -R 777 logs

## データベースの作成

次に、ブログで使用する基礎的なMySQLデータベースをセットアップしましょう。データベースを
まだ作成していないのであれば、このチュートリアル用に好きな名前で、
例えば `cake_blog` のような名前で空のデータベースを作成しておいてください。このページでは、
投稿記事を保存するためのテーブルをひとつ作成します。そしてテスト用に、いくつかの記事も投入します。
次の SQL をデータベースで実行してください。

``` sql
# まず、articles テーブルを作成します
CREATE TABLE articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50),
    body TEXT,
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);

# それから、テスト用に記事をいくつか入れておきます
INSERT INTO articles (title,body,created)
    VALUES ('タイトル', 'これは、記事の本文です。', NOW());
INSERT INTO articles (title,body,created)
    VALUES ('またタイトル', 'そこに本文が続きます。', NOW());
INSERT INTO articles (title,body,created)
    VALUES ('タイトルの逆襲', 'こりゃ本当にわくわくする！うそ。', NOW());
```

テーブル名とフィールド名は適当に選んだわけではありません。CakePHP のデータベース命名規約と
クラスの命名規約に従っておくと、（どちらも、 [CakePHP の規約](../../intro/conventions) の中で説明されています）
たくさんの機能を自由に使うことができ、設定作業をする必要がなくなります。
CakePHP はレガシーなデータベーススキーマに対応できるくらい十分に柔軟ですが、規約に従うことで、
時間を節約できます。

詳細は、 [CakePHP の規約](../../intro/conventions) を参照してください。簡単に言うと、
'articles' というテーブル名にしておけば、自動的に Articles モデルが呼び出され、'modified' と
'created' というフィールドがあると、自動的にCakePHP が管理するようになります。

## データベース設定

次に、どこにデータベースあるか、そしてどうやってテータベースに接続するかを CakePHP
に伝えましょう。おそらく、これが何らかの設定が必要となる最初で最後です。

この設定はとても単純です。あなたのセットアップを適用するために **config/app.php**
ファイルの中の `Datasources.default` 配列の値を置き換えてください。
完全な設定配列の例は、以下のようになります。 :

    return [
        // More configuration above.
        'Datasources' => [
            'default' => [
                'className' => 'Cake\Database\Connection',
                'driver' => 'Cake\Database\Driver\Mysql',
                'persistent' => false,
                'host' => 'localhost',
                'username' => 'cake_blog',
                'password' => 'AngelF00dC4k3~',
                'database' => 'cake_blog',
                'encoding' => 'utf8',
                'timezone' => 'UTC'
            ],
        ],
        // More configuration below.
    ];

**config/app.php** を保存すると、ブラウザーでウェルカムページが表示されるはずです。
データベースへの接続ファイルがみつかり、CakePHPがデータベースにきちんと接続されていることをも示しています。

> [!NOTE]
> CakePHP のデフォルト設定ファイルは **config/app.default.php** にあります。

## 追加の設定

設定できる項目があといくつかあります。たいていの開発者はこれらの詳細なリストも仕上げますが、
このチュートリアルに必要不可欠、というわけではありません。ひとつは、セキュリティハッシュ用の
カスタム文字列(「salt」ともいう)です。

セキュリティ用の salt は、ハッシュの生成に用いられます。 `config/app.php` を
編集し、デフォルトの `Security.salt` の値を変更してください。
この値は、ランダムで長い文字列にします。そうすることで推測がより困難になります。 :

    'Security' => [
       'salt' => '長いもので、多くの異なる値を含むもの。',
    ],

## mod_rewrite について

新しいユーザーは mod_rewrite でつまずくことがよくあります。例えば CakePHP の
ウェルカムページが少しおかしくなったりします (画像が表示されない、CSS が効いていない)。
これはおそらく、システム上の mod_rewrite が機能していないということです。
[Url Rewriting](../../installation#url-rewriting) セクションを参照して、URL リライティングが有効になるように設定してください。

はじめての CakePHP アプリケーションを構築しはじめるには、続けて
[ブログチュートリアル - パート2](../../tutorials-and-examples/blog/part-two)
をご覧ください。
