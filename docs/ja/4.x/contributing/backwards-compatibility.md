# 後方互換性ガイド

アプリケーションを簡単に、円滑にアップグレードができるようになっていることは
重要なことです。
メジャーリリースのマイルストーンでのみ互換性を破棄するのはそのためです。
全ての CakePHP プロジェクトで使用する全般的なガイドラインである、
[セマンティックバージョニング](https://semver.org/) について理解したほうが
良いでしょう。
セマンティックバージョニングとは簡単に言うと、(2.0、3.0、4.0 のような)
メジャーリリースのみ後方互換性を破棄することができ、(2.1、3.1、3.2 のような)
マイナーリリースが新しい機能の導入をするが互換性を破棄することは許されず、
(2.1.2、3.0.1 のような) バグフィックスリリースは新機能の追加はせず、
バグの修正かパフォーマンスの向上のみにすることを意味します。

> [!NOTE]
> 非推奨は、フレームワークの次のメジャーバージョンで削除されます。
> 非推奨のコメントや移行ガイドに記載されているように、コードに
> マイナーリリースの変更を早期に適用することをお勧めします。

それぞれのリリースに予定する変更を明確にするために、CakePHP を使う開発者と
CakePHP の開発者のために、マイナーリリースでされ得る変更予定の助けとなる更に
詳しい情報があります。
メジャーリリースは必要に応じて多くの破壊的変更を含むことができます。

## 移行ガイド

メジャー・マイナーの各リリースについて、CakePHP チームは移行ガイドを提供します。
このガイドはそれぞれのリリースの新機能と非互換な変更を説明します。
これは Cookbook の [付録](../appendices) セクションで見ることができます。

## CakePHP の使用

CakePHP を用いてアプリケーションを構築している場合、次のガイドラインで示す堅牢性
(**stability**) が期待できます。

### インターフェイス

メジャーリリース以外では、CakePHP が提供するどんな既存のメソッドの
インターフェイスも変更 **されません** 。
新しいメソッドは、既存のインターフェイスには追加 **されません** 。

### クラス

CakePHP が提供するクラスを構成する、アプリケーションから使われる public なメソッド
とプロパティーがメジャーリリース以外で後方互換性が保証されます。

> [!NOTE]
> CakePHP のいくつかのクラスは `@internal` API ドキュメントタグがつけられています。
> これらのクラスは堅牢 **ではなく** 、後方互換性は約束されていません。

マイナーリリースでは、クラスに新しいメソッドが追加されることがあり、
既存のメソッドは新しい引数が追加されることもあります。
新しい引数は必ずデフォルト値を持ちますが、異なる引数の形式でメソッドを
オーバーライドしていると致命的な (**fatal**) エラーになることがあります。
新しい引数が追加されたメソッドは、そのリリースの移行ガイドに掲載されます。

下記のテーブルはいくつかのユースケースと CakePHP に予定する互換性についての
概要となります。

<table style="width:85%;">
<colgroup>
<col style="width: 55%" />
<col style="width: 29%" />
</colgroup>
<thead>
<tr>
<th>すること</th>
<th>互換性</th>
</tr>
</thead>
<tbody>
<tr>
<td>クラスに対するタイプヒント</td>
<td>有り</td>
</tr>
<tr>
<td>新しいインスタンスの作成</td>
<td>有り</td>
</tr>
<tr>
<td>クラスの継承</td>
<td>有り</td>
</tr>
<tr>
<td>public プロパティーへのアクセス</td>
<td>有り</td>
</tr>
<tr>
<td>public メソッドの呼び出し</td>
<td>有り</td>
</tr>
<tr>
<td colspan="2"><strong>クラスを継承して...</strong></td>
</tr>
<tr>
<td>public プロパティーをオーバーライド</td>
<td>有り</td>
</tr>
<tr>
<td>protected プロパティーにアクセス</td>
<td>無し<a href="#fn1" class="footnote-ref" id="fnref1" role="doc-noteref"><sup>1</sup></a></td>
</tr>
<tr>
<td>protected プロパティーをオーバーライド</td>
<td>無し<a href="#fn2" class="footnote-ref" id="fnref2" role="doc-noteref"><sup>2</sup></a></td>
</tr>
<tr>
<td>protected メソッドをオーバーライド</td>
<td>無し<a href="#fn3" class="footnote-ref" id="fnref3" role="doc-noteref"><sup>3</sup></a></td>
</tr>
<tr>
<td>protected メソッドの呼び出し</td>
<td>無し<a href="#fn4" class="footnote-ref" id="fnref4" role="doc-noteref"><sup>4</sup></a></td>
</tr>
<tr>
<td>public プロパティーの追加</td>
<td>無し</td>
</tr>
<tr>
<td>public メソッドの追加</td>
<td>無し</td>
</tr>
<tr>
<td>オーバーライドされたメソッド
への引数の追加</td>
<td>無し<a href="#fn5" class="footnote-ref" id="fnref5" role="doc-noteref"><sup>5</sup></a></td>
</tr>
<tr>
<td>既存メソッドの引数へのデフォルト値
の追加</td>
<td>有り</td>
</tr>
</tbody>
</table>
<section id="footnotes" class="footnotes footnotes-end-of-document" role="doc-endnotes">
<hr />
<ol>
<li id="fn1"><p>マイナーリリースでコードが破壊される <em>恐れが</em> あります。
詳細は移行ガイドをチェックしてください。<a href="#fnref1" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
<li id="fn2"><p>マイナーリリースでコードが破壊される <em>恐れが</em> あります。
詳細は移行ガイドをチェックしてください。<a href="#fnref2" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
<li id="fn3"><p>マイナーリリースでコードが破壊される <em>恐れが</em> あります。
詳細は移行ガイドをチェックしてください。<a href="#fnref3" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
<li id="fn4"><p>マイナーリリースでコードが破壊される <em>恐れが</em> あります。
詳細は移行ガイドをチェックしてください。<a href="#fnref4" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
<li id="fn5"><p>マイナーリリースでコードが破壊される <em>恐れが</em> あります。
詳細は移行ガイドをチェックしてください。<a href="#fnref5" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
</ol>
</section>

## CakePHP での作業

CakePHP をより良くする手助けをしようという場合、機能の追加・変更時に以下の
ガイドラインに沿うように頭にとどめておいてください。

マイナーリリースでは次のことができます。

<table style="width:83%;">
<colgroup>
<col style="width: 45%" />
<col style="width: 37%" />
</colgroup>
<thead>
<tr>
<th colspan="2">マイナーリリースでできること</th>
</tr>
</thead>
<tbody>
<tr>
<td colspan="2"><strong>クラス</strong></td>
</tr>
<tr>
<td>クラスの削除</td>
<td>不可</td>
</tr>
<tr>
<td>インターフェイスの削除</td>
<td>不可</td>
</tr>
<tr>
<td>トレイトの削除</td>
<td>不可</td>
</tr>
<tr>
<td>final にする</td>
<td>不可</td>
</tr>
<tr>
<td>abstract にする</td>
<td>不可</td>
</tr>
<tr>
<td>名前の変更</td>
<td>可<a href="#fn1" class="footnote-ref" id="fnref1" role="doc-noteref"><sup>1</sup></a></td>
</tr>
<tr>
<td colspan="2"><strong>プロパティー</strong></td>
</tr>
<tr>
<td>public プロパティーの追加</td>
<td>可</td>
</tr>
<tr>
<td>public プロパティーの削除</td>
<td>不可</td>
</tr>
<tr>
<td>protected プロパティーの追加</td>
<td>可</td>
</tr>
<tr>
<td>protected プロパティーの削除</td>
<td>可<a href="#fn2" class="footnote-ref" id="fnref2" role="doc-noteref"><sup>2</sup></a></td>
</tr>
<tr>
<td colspan="2"><strong>メソッド</strong></td>
</tr>
<tr>
<td>public メソッドの追加</td>
<td>可</td>
</tr>
<tr>
<td>public メソッドの削除</td>
<td>不可</td>
</tr>
<tr>
<td>protected メソッドの追加</td>
<td>可</td>
</tr>
<tr>
<td>親クラスへの移動</td>
<td>可</td>
</tr>
<tr>
<td>protected メソッドの削除</td>
<td>可<a href="#fn3" class="footnote-ref" id="fnref3" role="doc-noteref"><sup>3</sup></a></td>
</tr>
<tr>
<td>可視性の減少</td>
<td>不可</td>
</tr>
<tr>
<td>メソッド名の変更</td>
<td>可<a href="#fn4" class="footnote-ref" id="fnref4" role="doc-noteref"><sup>4</sup></a></td>
</tr>
<tr>
<td>デフォルト値つき引数の新規追加</td>
<td>可</td>
</tr>
<tr>
<td>既存メソッドへの必須引数の
新規追加</td>
<td>不可</td>
</tr>
<tr>
<td>既存引数からのデフォルト値の
削除</td>
<td>不可</td>
</tr>
<tr>
<td>void 型メソッドの変更</td>
<td>可</td>
</tr>
</tbody>
</table>
<section id="footnotes" class="footnotes footnotes-end-of-document" role="doc-endnotes">
<hr />
<ol>
<li id="fn1"><p>古いクラス名・メソッド名を利用可能なように残しておくことで名前の変更ができます。
通常、名前の変更は重要な利点を持っていない限り避けられます。<a href="#fnref1" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
<li id="fn2"><p>出来る限り避けましょう。削除したことは移行ガイドに掲載する必要があります。<a href="#fnref2" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
<li id="fn3"><p>出来る限り避けましょう。削除したことは移行ガイドに掲載する必要があります。<a href="#fnref3" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
<li id="fn4"><p>古いクラス名・メソッド名を利用可能なように残しておくことで名前の変更ができます。
通常、名前の変更は重要な利点を持っていない限り避けられます。<a href="#fnref4" class="footnote-back" role="doc-backlink">↩︎</a></p></li>
</ol>
</section>

## 非推奨

各マイナーリリースでは、機能が非推奨になる可能性があります。
機能が非推奨になると、API ドキュメントや実行時の警告が追加されます。
実行時エラーは、コードが壊れる前に更新する必要があるコードを見つけるのに役立ちます。
実行時の警告を無効にするには、 `Error.errorLevel` 設定値を使用します。 :

``` text
// config/app.php の中で
// ...
'Error' => [
    'errorLevel' => E_ALL ^ E_USER_DEPRECATED,
]
// ...
```

これで、実行時の非推奨警告を無効にします。

<a id="experimental-features"></a>

## Experimental Features

Experimental features are **not included** in the above backwards compatibility
promises. Experimental features can have breaking changes made in minor releases
as long as they remain experimental. Experiemental features can be identified by
the warning in the book and the usage of `@experimental` in the API
documentation.

Experimental features are intended to help gather feedback on how a feature
works before it becomes stable. Once the interfaces and behavior has been vetted
with the community the experimental flags will be removed.
