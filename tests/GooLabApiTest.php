<?php

namespace OddOnes\GooLabApi\Tests;

use OddOnes\GooLabApi\GooLabApi;
use OddOnes\GooLabApi\Exceptions\GooLabApiException;
use PHPUnit\Framework\TestCase;

class GooLabApiTest extends TestCase
{
    /** @var GooLabApi */
    public $GooLabApi;

    public function setUp() :void
    {
        $this->GooLabApi = new GooLabApi(getenv("GOOLAB_API_KEY"));
    }

    public function tearDown() :void
    {
        $this->GooLabApi = null;
    }

    public function urlProvider(): array
    {
        return [
            ['https://'],
            ['https://labs.goo.ne.jp/api'],
        ];
    }

    /**
     * 異常系 コンストラクタエラーパターン1
     */
    public function testConstructorFail()
    {
        $verify_code = 0;
        try {
            $this->GooLabApi = new GooLabApi([]);
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, -1);
    }

    /**
     * 異常系 コンストラクタエラーパターン2
     */
    public function testConstructorFail2()
    {
        $verify_code = 0;
        try {
            $this->GooLabApi = new GooLabApi('');
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, -1);
    }

    /**
     * 正常系 apiキーセット処理
     */
    public function testSetApiKey()
    {
        $this->GooLabApi->setApiKey('test_api_key');

        $this->assertEquals($this->GooLabApi->getApiKey(), 'test_api_key');
    }

    /**
     * 異常系 apiキーが正しくない問い合わせ1（callTextPairのみエラーレスポンスが異なる）
     */
    public function testInvalidApiKey1()
    {
        $verify_code = 0;
        try {
            $this->GooLabApi = new GooLabApi('invalid_api_key');
            $this->GooLabApi->callTextPair('高橋さんはアメリカに出張に行きました。', '山田さんはイギリスに留学している。');
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }

    /**
     * 異常系 apiキーが正しくない問い合わせ2
     */
    public function testInvalidApiKey2()
    {
        $verify_code = 0;
        try {
            $this->GooLabApi = new GooLabApi('invalid_api_key');
            $this->GooLabApi->callSlot('名前は田中太郎で、男性で、30歳です。港区芝浦3-4-1に住んでいます。');
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }

    /**
     * 正常系 テキストペア類似度API
     */
    public function testCallTextPair()
    {
        $response = $this->GooLabApi->callTextPair('高橋さんはアメリカに出張に行きました。', '山田さんはイギリスに留学している。');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('score', $response);
    }

    /**
     * 正常系 スロット値抽出API
     */
    public function testCallSlot()
    {
        $response = $this->GooLabApi->callSlot('名前は田中太郎で、男性で、30歳です。港区芝浦3-4-1に住んでいます。');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('slots', $response);
    }
    
    /**
     * 正常系 スロット値抽出API フィルタ指定
     */
    public function testCallSlotFiltered()
    {
        $response = $this->GooLabApi->callSlot('名前は田中太郎で、男性で、30歳です。港区芝浦3-4-1に住んでいます。', ['name', 'birthday', 'sex', 'address', 'tel', 'age']);
        $this->assertNotNull('response');
        $this->assertArrayHasKey('slots', $response);
    }

    /**
     * 異常系 スロット値抽出API フィルタ指定
     */
    public function testInvalidCallSlotFiltered()
    {
        $verify_code = 0;
        try {
            $response = $this->GooLabApi->callSlot('名前は田中太郎で、男性で、30歳です。港区芝浦3-4-1に住んでいます。', ['name', 'aaaaaaaaa']);
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }
    
    /**
     * 正常系 時刻情報正規化API
     */
    public function testCallChrono()
    {
        $response = $this->GooLabApi->callChrono('今日の10時半に出かけます。');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('doc_time', $response);
        $this->assertArrayHasKey('datetime_list', $response);
    }

    /**
     * 正常系 時刻情報正規化API 日付指定
     */
    public function testCallChronoFixedDate()
    {
        $response = $this->GooLabApi->callChrono('今日の10時半に出かけます。', '2022-01-01T09:00:00');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('doc_time', $response);
        $this->assertArrayHasKey('datetime_list', $response);
    }

    /**
     * 異常系 時刻情報正規化API 日付指定
     */
    public function testInvalidCallChrono()
    {
        $verify_code = 0;
        try {
            $response = $this->GooLabApi->callChrono('今日の10時半に出かけます。', 'aaaaaaaaa');
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }

    /**
     * 正常系 キーワード抽出API
     */
    public function testCallKeyword()
    {
        $response = $this->GooLabApi->callKeyword(
            '「和」をコンセプトとする 匿名性コミュニケーションサービス「MURA」 gooラボでのβ版のトライアル実施 ～gooの検索技術を使った「ネタ枯れ防止機能」によりコミュニティの話題活性化が可能に～',
            'NTTレゾナント株式会社（本社：東京都港区、代表取締役社長：若井 昌宏、以下、NTTレゾナント）は、同じ興味関心を持つ人と匿名でコミュニティをつくることができるコミュニケーションサービス「MURA」を、2015年8月27日よりgooラボ上でβ版サイトのトライアル提供を開始します。',
        );
        $this->assertNotNull('response');
        $this->assertArrayHasKey('keywords', $response);
    }

    /**
     * 正常系 キーワード抽出API 最大出力数指定
     */
    public function testCallKeywordMaxNum()
    {
        $response = $this->GooLabApi->callKeyword(
            '「和」をコンセプトとする 匿名性コミュニケーションサービス「MURA」 gooラボでのβ版のトライアル実施 ～gooの検索技術を使った「ネタ枯れ防止機能」によりコミュニティの話題活性化が可能に～',
            'NTTレゾナント株式会社（本社：東京都港区、代表取締役社長：若井 昌宏、以下、NTTレゾナント）は、同じ興味関心を持つ人と匿名でコミュニティをつくることができるコミュニケーションサービス「MURA」を、2015年8月27日よりgooラボ上でβ版サイトのトライアル提供を開始します。',
            50,
        );
        $this->assertNotNull('response');
        $this->assertArrayHasKey('keywords', $response);
    }

    /**
     * 正常系 キーワード抽出API 注目固有表現種別指定
     */
    public function testCallKeywordFocused()
    {
        $response = $this->GooLabApi->callKeyword(
            '「和」をコンセプトとする 匿名性コミュニケーションサービス「MURA」 gooラボでのβ版のトライアル実施 ～gooの検索技術を使った「ネタ枯れ防止機能」によりコミュニティの話題活性化が可能に～',
            'NTTレゾナント株式会社（本社：東京都港区、代表取締役社長：若井 昌宏、以下、NTTレゾナント）は、同じ興味関心を持つ人と匿名でコミュニティをつくることができるコミュニケーションサービス「MURA」を、2015年8月27日よりgooラボ上でβ版サイトのトライアル提供を開始します。',
            50,
            'PSN'
        );
        $this->assertNotNull('response');
        $this->assertArrayHasKey('keywords', $response);
    }

    /**
     * 異常系 キーワード抽出API 引数エラー
     */
    public function testInvalidCallKeyword1()
    {
        $verify_code = 0;
        try {
            $response = $this->GooLabApi->callKeyword(
                '「和」をコンセプトとする 匿名性コミュニケーションサービス「MURA」 gooラボでのβ版のトライアル実施 ～gooの検索技術を使った「ネタ枯れ防止機能」によりコミュニティの話題活性化が可能に～',
                'NTTレゾナント株式会社（本社：東京都港区、代表取締役社長：若井 昌宏、以下、NTTレゾナント）は、同じ興味関心を持つ人と匿名でコミュニティをつくることができるコミュニケーションサービス「MURA」を、2015年8月27日よりgooラボ上でβ版サイトのトライアル提供を開始します。',
                'aaaaaaaaa',
                'PSN',
            );
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }

    /**
     * 異常系 キーワード抽出API 不正引数（focusの異常値はエラーにならない
     */
    public function testInvalidCallKeyword2()
    {
        $response = $this->GooLabApi->callKeyword(
            '「和」をコンセプトとする 匿名性コミュニケーションサービス「MURA」 gooラボでのβ版のトライアル実施 ～gooの検索技術を使った「ネタ枯れ防止機能」によりコミュニティの話題活性化が可能に～',
            'NTTレゾナント株式会社（本社：東京都港区、代表取締役社長：若井 昌宏、以下、NTTレゾナント）は、同じ興味関心を持つ人と匿名でコミュニティをつくることができるコミュニケーションサービス「MURA」を、2015年8月27日よりgooラボ上でβ版サイトのトライアル提供を開始します。',
            10,
            'aaaaaaaaa',
        );
        $this->assertNotNull('response');
        $this->assertArrayHasKey('keywords', $response);
    }

    /**
     * 正常系 固有表現抽出API
     */
    public function testCallEntity()
    {
        $response = $this->GooLabApi->callEntity('鈴木さんがきょうの9時30分に横浜に行きます。');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('ne_list', $response);
    }
    
    /**
     * 正常系 固有表現抽出API フィルタ指定
     */
    public function testCallEntityFiltered()
    {
        $response = $this->GooLabApi->callEntity('鈴木さんがきょうの9時30分に横浜に行きます。', ['ART','ORG','PSN','LOC','DAT','TIM','MNY','PCT']);
        $this->assertNotNull('response');
        $this->assertArrayHasKey('ne_list', $response);
    }

    /**
     * 異常系 固有表現抽出API フィルタ指定
     */
    public function testInvalidCallEntityFiltered()
    {
        $verify_code = 0;
        try {
            $response = $this->GooLabApi->callEntity('鈴木さんがきょうの9時30分に横浜に行きます。', ['ART','aaaaaaaaa']);
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }

    /**
     * 正常系 形態素解析API
     */
    public function testCallMorph()
    {
        $response = $this->GooLabApi->callMorph('美しく飛んでいる燕。料理を美味しく作る。');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('word_list', $response);
    }
    
    /**
     * 正常系 形態素解析API 語幹結合
     */
    public function testCallMorphJoinStem()
    {
        $response = $this->GooLabApi->callMorph('美しく飛んでいる燕。料理を美味しく作る。', null, null, true);
        $this->assertNotNull('response');
        $this->assertArrayHasKey('word_list', $response);
    }
    
    /**
     * 正常系 形態素解析API フィルタ指定1
     */
    public function testCallMorphFiltered1()
    {
        $response = $this->GooLabApi->callMorph('美しく飛んでいる燕。料理を美味しく作る。', ['form','pos','read']);
        $this->assertNotNull('response');
        $this->assertArrayHasKey('word_list', $response);
    }

    /**
     * 正常系 形態素解析API フィルタ指定2
     */
    public function testCallMorphFiltered2()
    {
        $response = $this->GooLabApi->callMorph('美しく飛んでいる燕。料理を美味しく作る。', ['form','pos','read'], ['名詞']);
        $this->assertNotNull('response');
        $this->assertArrayHasKey('word_list', $response);
    }

    /**
     * 異常系 形態素解析API フィルタ指定1
     */
    public function testInvalidCallMorphFiltered1()
    {
        $verify_code = 0;
        try {
            $response = $this->GooLabApi->callMorph('美しく飛んでいる燕。料理を美味しく作る。', ['form','aaaaaaaaa']);
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }

    /**
     * 異常系 形態素解析API フィルタ指定2
     */
    public function testInvalidCallMorphFiltered2()
    {
        $verify_code = 0;
        try {
            $response = $this->GooLabApi->callMorph('美しく飛んでいる燕。料理を美味しく作る。', ['form','pos','read'], ['めいし']);
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }

    /**
     * 正常系 ひらがな化API かな指定
     */
    public function testCallHiragana1()
    {
        $response = $this->GooLabApi->callHiragana('漢字が混ざっている文章');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('converted', $response);
    }
    
    /**
     * 正常系 ひらがな化API カナ指定
     */
    public function testCallHiragana2()
    {
        $response = $this->GooLabApi->callHiragana('漢字が混ざっている文章', 'katakana');
        $this->assertNotNull('response');
        $this->assertArrayHasKey('converted', $response);
    }

    /**
     * 異常系 ひらがな化API 異常指定
     */
    public function testInvalidCallHiraganaFiltered()
    {
        $verify_code = 0;
        try {
            $response = $this->GooLabApi->callHiragana('漢字が混ざっている文章', 'aaaaaaaaa');
        } catch (GooLabApiException $th) {
            $verify_code = $th->getCode();
        }
        $this->assertEquals($verify_code, 400);
    }
}
