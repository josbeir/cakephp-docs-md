# Inflector

`class` **Inflector**

Inflector は文字列の複数形や大文字への変換を取り扱うクラスです。
Inflector のメソッドは通常では静的にアクセスします。
例: `Inflector::pluralize('example')` は "examples" を返します。

[inflector.cakephp.org](https://inflector.cakephp.org/)
にてオンライン上で変換を試すことができます。

> - **入力:** Apple, Orange, Person, Man
> - **出力:** Apples, Oranges, People, Men

> [!NOTE]
> `pluralize()` は、すでに複数形の名詞をいつも正しく変換できるわけではありません。
>
> - **入力:** Apples, Oranges, People, Men
> - **出力:** Apple, Orange, Person, Man

> [!NOTE]
> `singularize()` は、すでに単数形の名詞をいつも正しく変換できるわけではありません。
>
> - **入力:** Apple_pie, some_thing, people_person
> - **出力:** ApplePie, SomeThing, PeoplePerson
>
> underscore はキャメルケースの文字列をアンダースコア (\_) に変換します。
> スペースを含む文字列は小文字になりますがアンダースコアは含まれません。
>
> - **入力:** applePie, someThing
> - **出力:** apple_pie, some_thing
> - **入力:** apple_pie, some_thing, people_person
> - **出力:** Apple Pie, Some Thing, People Person
> - **入力:** Apple, UserProfileSetting, Person
> - **出力:** apples, user_profile_settings, people
> - **入力:** apples, user_profile_settings, people
> - **出力:** Apple, UserProfileSetting, Person
> - **入力:** apples, user_result, people_people
> - **出力:** apples, userResult, peoplePeople
>
> slug は特殊文字をラテン文字に変換したり、スペースをアンダースコアに変換します。
> slug は UTF-8 を前提とします。
>
> - **入力:** apple purée
> - **出力:** apple_puree
>
> reset は文字列を変更前の状態に戻します。テストでの利用を想定しています。
>
> rules は Inflector に対して新しい変換ルールを定義します。
> [Inflection Configuration](../development/configuration#inflection-configuration) により詳細な情報があります。
