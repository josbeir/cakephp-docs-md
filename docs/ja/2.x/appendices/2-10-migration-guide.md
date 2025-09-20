# 2.10 移行ガイド

CakePHP 2.10 は、2.9 の API の完全上位互換です。
このページでは、2.10 の変更と改善についてのアウトラインを紹介します。

## コア

- `CONFIG` 定数が追加されました。この定数のデフォルトは `app/Config` で、
  3.x との前方互換性を向上させることを目的としています。

## モデル

- `smallinteger` と `tinyinteger` が新しい内部データ型に追加されました。
  既存の `SMALLINT` と `TINYINT` カラムは新しい内部データ型として反映されます。
  `TINYINT(1)` カラムは、引き続き MySQL でブール型カラムとして扱われます。
- `Model::find()` は、新たに `having` と `lock` オプションをサポートします。
  それは `HAVING` と `FOR UPDATE` のロック句を追加することができます。
- `TranslateBehavior` は、新たに LEFT JOIN での翻訳の読み込みをサポートします。
  この機能を使用するためには `joinType` オプションを使用してください。

## コンポーネント

- `SecurityComponent` は、デバッグモードでフォーム改ざんや CSRF 保護が失敗した場合、
  より詳細なエラーメッセージを出すようになりました。この機能は 3.x からのバックポートです。
- `SecurityComponent` は、リクエストデータのない post リクエストを破棄します。
  この変更は、データベースのデフォルト値のみのレコードを作成する動作を防ぐのに役立ちます。
- `FlashComponent` は、同じタイプのメッセージを積み重ねます。この機能は、 3.x からの
  バックポートです。この動作を無効にするためには、 FlashComponent の設定に
  `'clear' => true` を追加してください。
- `PaginatorComponent` は、 `queryScope` オプションを介して複数のページ制御を
  サポートします。データをページ制御するときに、このオプションを使用すると、
  PaginatorComponent はルートクエリー文字列データの代わりに
  スコープ付きクエリーパラメーターからの読み取りを強制します。

## ヘルパー

- `HtmlHelper::image()` は、 `base64` オプションをサポートします。
  このオプションは、ローカルの画像ファイルを読み込み、base64 データ URI を作成します。
- `HtmlHelper::addCrumb()` に `prepend` オプションが追加されました。
  これにより、パンくずリストを後に追加するのではなく、前に付けることができます。
- `FormHelper` は、 `smallinteger` や `tinyinteger` 型の 'numeric' 入力を
  作成します。

## ルーティング

- `Router::reverseToArray()` が追加されました。
