# php-goolab-api
gooラボAPIのLaravel PHP Facade/Wrapper（テキストペア類似度API・スロット値抽出API・時刻情報正規化API・キーワード抽出API・固有表現抽出API・形態素解析API・ひらがな化API）

## 動作環境

- PHP ^7.4 | ^8.0
- APIキー [gooラボ](https://labs.goo.ne.jp/apiusage/)


## インストール

コンソールで以下のコマンドを実行すると、プロジェクトにパッケージがダウンロードされます。:
```
composer require oddones/php-goolab-api
```

## 構成

`/config/app.php` にGooLabApiServiceProviderを追加します (Laravel < 5.5):
```
OddOnes\GooLabApi\GooLabApiServiceProvider::class,
```

また、そこにGooLabApi facadeを追加してください (Laravel < 5.5):
```
'GooLabApi' => OddOnes\GooLabApi\Facades\GooLabApi::class,
```

設定ファイルの公開:
```
$ php artisan vendor:publish --provider="OddOnes\GooLabApi\GooLabApiServiceProvider"
```

gooラボのAPIキーをファイルに設定します:
```
/config/GooLabApi.php
```

または、`.env`ファイルに設定します:
```
GOOLAB_API_KEY=KEY
```

または、実行時にプログラムでキーを設定することもできます:
```
GooLabApi::setApiKey('KEY');
```

## 使い方

```php
// use OddOnes\GooLabApi\Facades\GooLabApi;


// テキストペア類似度API
$pairResult = GooLabApi::callTextPair('高橋さんはアメリカに出張に行きました。', '山田さんはイギリスに留学している。');;

// スロット値抽出API
$pairResult1 = GooLabApi::callSlot('名前は田中太郎で、男性で、30歳です。港区芝浦3-4-1に住んでいます。');

// スロット値抽出API フィルタ指定
// name(氏名)、birthday(生年月日)、sex(性別)、address(住所)、tel(電話番号)、age(年齢)のうち、スロット値抽出の対象とするスロットを配列で指定します。省略時はすべてのスロットを対象とします。
$pairResult2 = GooLabApi::callSlot('名前は田中太郎で、男性で、30歳です。港区芝浦3-4-1に住んでいます。', ['name', 'birthday', 'sex', 'address', 'tel', 'age']);

// 時刻情報正規化API
$chronoResult1 = GooLabApi::callChrono('今日の10時半に出かけます。');

// 時刻情報正規化API 基準日指定
// "%Y-%m-%dT%H:%M:%S"のフォーマットで基準となる日時を指定する。省略時には現在時刻が用いられます。
$chronoResult1 = GooLabApi::callChrono('今日の10時半に出かけます。', '2022-01-01T09:00:00');

// キーワード抽出API
$keywordResult1 = GooLabApi::callKeyword(
            '「和」をコンセプトとする 匿名性コミュニケーションサービス「MURA」 gooラボでのβ版のトライアル実施 ～gooの検索技術を使った「ネタ枯れ防止機能」によりコミュニティの話題活性化が可能に～',
            'NTTレゾナント株式会社（本社：東京都港区、代表取締役社長：若井 昌宏、以下、NTTレゾナント）は、同じ興味関心を持つ人と匿名でコミュニティをつくることができるコミュニケーションサービス「MURA」を、2015年8月27日よりgooラボ上でβ版サイトのトライアル提供を開始します。',
        );

// キーワード抽出API 最大抽出件数/注目固有表現種別指定
// 最大抽出件数:省略時は10となります。
// 注目固有表現種別:ORG(組織名)、PSN(人名)、LOC(地名)のうち、スコアを強く算出したい固有表現種別を1種類のみ文字列で指定します。省略時または上記以外の種別指定時は、全ての種別を同等に扱います。
$keywordResult1 = GooLabApi::callKeyword(
            '「和」をコンセプトとする 匿名性コミュニケーションサービス「MURA」 gooラボでのβ版のトライアル実施 ～gooの検索技術を使った「ネタ枯れ防止機能」によりコミュニティの話題活性化が可能に～',
            'NTTレゾナント株式会社（本社：東京都港区、代表取締役社長：若井 昌宏、以下、NTTレゾナント）は、同じ興味関心を持つ人と匿名でコミュニティをつくることができるコミュニケーションサービス「MURA」を、2015年8月27日よりgooラボ上でβ版サイトのトライアル提供を開始します。',
            50,
            'PSN',
        );

// 固有表現抽出API
$entityResult1 = GooLabApi::callEntity('鈴木さんがきょうの9時30分に横浜に行きます。');

// 固有表現抽出API フィルタ指定
// 固有表現種別フィルタ : ART(人工物名)、ORG(組織名)、PSN(人名)、LOC(地名)、DAT(日付表現)、TIM(時刻表現)、MNY(金額表現)、PCT(割合表現)のうち、出力する情報を文字列で指定します。複数指定する場合は、配列で複数記載してください。省略時は全ての種別を出力します。
$entityResult1 = GooLabApi::callEntity('鈴木さんがきょうの9時30分に横浜に行きます。', ['ART','ORG','PSN','LOC','DAT','TIM','MNY','PCT']);

// 形態素解析API
$entityResult1 = GooLabApi::callMorph('美しく飛んでいる燕。料理を美味しく作る。');

// 形態素解析API 語幹結合
// 動詞語幹/動詞活用語尾/動詞接尾辞 および 形容詞語幹/形容詞接尾辞を結合します。
$entityResult2 = GooLabApi::callMorph('美しく飛んでいる燕。料理を美味しく作る。', null, null, true);

// 形態素解析API 形態素情報フィルタ/形態素品詞フィルタ
// 形態素情報フィルタ : form(表記)、pos(形態素)、read(読み)のうち、出力する情報を文字列で指定します。複数指定する場合は、配列で複数記載してください。省略時は"form|pos|read"を指定したものとみなします。
// 形態素品詞フィルタ : 出力対象とする品詞を配列で指定します。省略時は全形態素を出力します。設定可能な項目は「形態素解析APIの品詞一覧（https://labs.goo.ne.jp/api/jp/morphological-analysis-pos_filter）」をご参照ください。
$entityResult4 = GooLabApi::callMorph('美しく飛んでいる燕。料理を美味しく作る。', ['form','pos','read'], ['名詞']);

```

## 手動によるクラスのインスタンス化

```php
// GooLabApi のコンストラクタを直接呼び出します。
$GooLabApi = new GooLabApi(config('GOOLAB_API_KEY'));

$GooLabApi->callMorph('美しく飛んでいる燕。料理を美味しく作る。');
```

## ユニットテストの実行
PHPUnitがインストールされている環境では、次のように実行します:

```bash
$ phpunit
```

PHPUnitがインストールされていない場合は、以下を実行します:

```bash
$ composer update
$ ./vendor/bin/phpunit
```

## レスポンスデータのフォーマット
返されるJSONはPHPのArrayとしてデコードされます（stdClassではありません）  
詳しくは以下公式APIドキュメントのレスポンスパラメーター項目をお読みください。


## gooラボApi
- [テキストペア類似度API](https://labs.goo.ne.jp/api/textpair_doc)
- [スロット値抽出API](https://labs.goo.ne.jp/api/jp/slot-value-extraction/)
- [時刻情報正規化API](https://labs.goo.ne.jp/api/jp/time-normalization/)
- [キーワード抽出API](https://labs.goo.ne.jp/api/jp/keyword-extraction/)
- [固有表現抽出API](https://labs.goo.ne.jp/api/jp/named-entity-extraction/)
- [形態素解析API](https://labs.goo.ne.jp/api/jp/morphological-analysis/)
- [ひらがな化API](https://labs.goo.ne.jp/api/jp/hiragana-translation/)



## クレジット
### API

<a href="https://www.goo.ne.jp/">
<img src="https://u.xgoo.jp/img/sgoo.png" width="200px" alt="supported by goo" title="supported by goo">
</a>

### コード
OddOnes's [php-goolab-api](https://github.com/OddOnes/php-goolab-api).



[![Donate](https://img.shields.io/badge/Donate-PayPay-green.svg)](https://qr.paypay.ne.jp/rJ4cEfl2lSlSShMW)