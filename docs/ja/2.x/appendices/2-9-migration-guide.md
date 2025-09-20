# 2.9 移行ガイド

CakePHP 2.9 は、2.8 の API の完全上位互換です。
このページでは、2.9 の変更と改善についてのアウトラインを紹介します。

## PHP7 の互換性

CakePHP 2.9 は、PHP7 互換で、テストされています。

## 非推奨

\* `Object` クラスは非推奨になり、 `CakeObject` に名前を変更してください。  
 `object` が PHP7 以降、予約語になったためです。  
(\[RFC\](<https://wiki.php.net/rfc/reserve_even_more_types_in_php_7>) をご覧ください。)

## 新機能

- `DboSource::flushQueryCache()` は、
  有効時にクエリ結果のキャッシュをよりきめ細かく制御するために追加されました。
- `ErrorHandler` によって作成されたログメッセージは、
  サブクラスでより簡単にカスタマイズすることができます。
- 追加の MIME タイプ 'jsonapi' と 'psd' が追加されました。
- 時間と日時の入力は、 'text' 入力タイプとして描画された時、 `maxlength` 属性を設定しません。
- `AuthComponent::user()` は、ステートレス認証アダプターを使用した時、
  ユーザーデータが利用できるようになります。
