<?php

namespace OddOnes\GooLabApi;

use OddOnes\GooLabApi\Exceptions\GooLabApiException;

class GooLabApi
{

    /** @var string */
    protected $goolab_api_key; // from the config file

    /** @var string */
    const base_url = 'https://labs.goo.ne.jp';

    /** @var array */
    const endpoints = [
        'textpair' => '/api/textpair', // https://labs.goo.ne.jp/api/textpair_doc/
        'slot' => '/api/slot', // https://labs.goo.ne.jp/api/jp/slot-value-extraction/
        'chrono' => '/api/chrono', // https://labs.goo.ne.jp/api/jp/time-normalization/
        'keyword' => '/api/keyword', // https://labs.goo.ne.jp/api/jp/keyword-extraction/
        'entity' => '/api/entity', // https://labs.goo.ne.jp/api/jp/named-entity-extraction/
        'morph' => '/api/morph', // https://labs.goo.ne.jp/api/jp/morphological-analysis/
        'hiragana' => '/api/hiragana', // https://labs.goo.ne.jp/api/jp/hiragana-translation/
    ];

    /**
     * Constructor
     * $GooLabApi = new GooLabApi(['key' => 'KEY HERE'])
     *
     * @param string $key
     * @throws GooLabApiException
     */
    public function __construct($key)
    {
        if (is_string($key) && !empty($key)) {
            $this->goolab_api_key = $key;
        } else {
            throw new GooLabApiException('goo API key is Required, please visit https://labs.goo.ne.jp/apiusage/', -1);
        }
    }

    /**
     * @param $key
     * @return GooLabApi
     */
    public function setApiKey($key)
    {
        $this->goolab_api_key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->goolab_api_key;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getEndpoint($name)
    {
        return self::endpoints[$name];
    }

    /**
      * テキストペア類似度API
      * @param string $text1
      * @param string $text2
      * @return array
      * @throws GooLabApiException
      */
    public function callTextPair($text1, $text2): array
    {
        $endpoint = $this->getEndpoint('textpair');
        $params = [
            'text1' => $text1,
            'text2' => $text2,
        ];

        $apiData = $this->callApi($endpoint, $params);

        return $apiData;
    }

    /**
      * スロット値抽出API
      * @param string $sentence
      * @param array $filters name(氏名)、birthday(生年月日)、sex(性別)、address(住所)、tel(電話番号)、age(年齢)のうち、スロット値抽出の対象とするスロットを配列で指定します。省略時はすべてのスロットを対象とします。
      * @return array
      * @throws GooLabApiException
      */
    public function callSlot($sentence, $filters = null): array
    {
        $endpoint = $this->getEndpoint('slot');
        $params = [
            'sentence' => $sentence,
        ];
        if ($filters) {
            $params['slot_filter'] = implode('|', $filters);
        }

        $apiData = $this->callApi($endpoint, $params);

        return $apiData;
    }

    /**
      * 時刻情報正規化API
      * @param string $sentence
      * @param string $docTime "%Y-%m-%dT%H:%M:%S"のフォーマットで基準となる日時を指定する。省略時には現在時刻が用いられます。
      * @return array
      * @throws GooLabApiException
      */
    public function callChrono($sentence, $docTime = null): array
    {
        $endpoint = $this->getEndpoint('chrono');
        $params = [
            'sentence' => $sentence,
        ];
        if ($docTime) {
            $params['doc_time'] = $docTime;
        }

        $apiData = $this->callApi($endpoint, $params);

        return $apiData;
    }

    /**
      * 時刻情報正規化API
      * @param string $title
      * @param string $body
      * @param int $maxNum 最大出力キーワード数。省略時は10となります。
      * @param string $focus 注目固有表現種別。ORG(組織名)、PSN(人名)、LOC(地名)のうち、スコアを強く算出したい固有表現種別を1種類のみ文字列で指定します。省略時または上記以外の種別指定時は、全ての種別を同等に扱います。
      * @return array
      * @throws GooLabApiException
      */
    public function callKeyword($title, $body, $maxNum = null, $focus = null): array
    {
        $endpoint = $this->getEndpoint('keyword');
        $params = [
            'title' => $title,
            'body' => $body,
        ];
        if ($maxNum) {
            $params['max_num'] = $maxNum;
        }
        if ($focus) {
            $params['focus'] = $focus;
        }

        $apiData = $this->callApi($endpoint, $params);

        return $apiData;
    }

    /**
      * 固有表現抽出API
      * @param string $sentence
      * @param array $filters 固有表現種別フィルタ : ART(人工物名)、ORG(組織名)、PSN(人名)、LOC(地名)、DAT(日付表現)、TIM(時刻表現)、MNY(金額表現)、PCT(割合表現)のうち、出力する情報を文字列で指定します。複数指定する場合は、配列で複数記載してください。省略時は全ての種別を出力します。
      * @return array
      * @throws GooLabApiException
      */
    public function callEntity($sentence, $filters = null): array
    {
        $endpoint = $this->getEndpoint('entity');
        $params = [
              'sentence' => $sentence,
          ];
        if ($filters) {
            $params['class_filter'] = implode('|', $filters);
        }
  
        $apiData = $this->callApi($endpoint, $params);
  
        return $apiData;
    }

    /**
      * 形態素解析API
      * @param string $sentence
      * @param array $infoFilters 形態素情報フィルタ : form(表記)、pos(形態素)、read(読み)のうち、出力する情報を文字列で指定します。複数指定する場合は、配列で複数記載してください。省略時は"form|pos|read"を指定したものとみなします。
      * @param array $posFilters 形態素品詞フィルタ : 出力対象とする品詞を配列で指定します。省略時は全形態素を出力します。設定可能な項目は「形態素解析APIの品詞一覧（https://labs.goo.ne.jp/api/jp/morphological-analysis-pos_filter）」をご参照ください。
      * @param bool $joinStem true:動詞語幹/動詞活用語尾/動詞接尾辞 および 形容詞語幹/形容詞接尾辞を結合します。
      * @return array
      * @throws GooLabApiException
      */
    public function callMorph($sentence, $infoFilters = null, $posFilters = null, $joinStem = false): array
    {
        $endpoint = $this->getEndpoint('morph');
        $params = [
              'sentence' => $sentence,
          ];
        if ($infoFilters) {
            $params['info_filter'] = implode('|', $infoFilters);
        }
        if ($posFilters) {
            $params['pos_filter'] = implode('|', $posFilters);
        }
  
        $apiData = $this->callApi($endpoint, $params);
  
        if ($joinStem) {
            $apiData = $this->joinStem($apiData);
        }

        return $apiData;
    }

    /**
      * ひらがな化API
      * @param string $sentence
      * @param string $outputType hiragana(ひらがな化)、katakana(カタカナ化)のどちらかを指定してください。
      * @return array
      * @throws GooLabApiException
      */
    public function callHiragana($sentence, $outputType = 'hiragana'): array
    {
        $endpoint = $this->getEndpoint('hiragana');
        $params = [
                'sentence' => $sentence,
                'output_type' => $outputType,
            ];
    
        $apiData = $this->callApi($endpoint, $params);
    
        return $apiData;
    }
      
    /**
      * 形態素解析API のレスポンスのうち、動詞語幹/動詞活用語尾/動詞接尾辞 および 形容詞語幹/形容詞接尾辞を結合します。
      * @param array $apiData
      * @return array
      * @throws GooLabApiException
      */
    public function joinStem($apiData): array
    {
        if (!isset($apiData['word_list'])) {
            return $apiData;
        }

        foreach ($apiData['word_list'] as $idxSentence => $sentences) {
            // 文脈数分配列に格納されている

            $joinedMorpheme = [];
            foreach ($sentences as $key => $morpheme) {
                // 形態素解析結果 0:表記/1:形態素/2:読み（カナ）
                if (count($morpheme) < 3) {
                    continue;
                }

                // 語幹ではなくなるので動詞/形容詞に変更
                if ($morpheme[1] === '動詞語幹') {
                    $morpheme[1] = '動詞';
                }
                if ($morpheme[1] === '形容詞語幹') {
                    $morpheme[1] = '形容詞';
                }

                // 動詞活用語尾/動詞接尾辞 であれば最新の格納データを確認し動詞であれば結合する
                if ($morpheme[1] === '動詞活用語尾' || $morpheme[1] === '動詞接尾辞') {
                    $lastElm = &$joinedMorpheme[array_key_last($joinedMorpheme)];
                    if ($lastElm[1] === '動詞') {
                        $lastElm[0] .= $morpheme[0];
                        $lastElm[2] .= $morpheme[2];
                        continue;
                    }
                }

                // 形容詞接尾辞 であれば最新の格納データを確認し形容詞であれば結合する
                if ($morpheme[1] === '形容詞接尾辞') {
                    $lastElm = &$joinedMorpheme[array_key_last($joinedMorpheme)];
                    if ($lastElm[1] === '形容詞') {
                        $lastElm[0] .= $morpheme[0];
                        $lastElm[2] .= $morpheme[2];
                        continue;
                    }
                }

                $joinedMorpheme[] = $morpheme;
            }

            $apiData['word_list'][$idxSentence] = $joinedMorpheme;
        }
        
        return $apiData;
    }

    /**
     * Guzzleを使ってAPIを叩く.
     *
     * @param $path
     * @param $params
     * @return array
     * @throws GooLabApiException
     */
    public function callApi($path, $params) :array
    {
        $head = [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json; charset=UTF-8',
            ],
        ];

        $params['app_id'] = $this->goolab_api_key;
        $head['body'] = json_encode($params);
        
        $client = new \GuzzleHttp\Client([
            'base_uri' => self::base_url,
        ]);

        $response = $client->request('POST', $path, $head);

        $decodeBody = $this->decodeResponse($response);

        return $decodeBody;
    }

    /**
     * Guzzleレスポンスをデコード.
     *
     * @param $response
     * @return array
     * @throws GooLabApiException
     */
    public function decodeResponse($response) : array
    {
        $decodeBody = json_decode($response->getBody(), true);
        $statusCode = $response->getStatusCode();
        switch ($statusCode) {
            case 400:
                // テキストペア類似度APIのみエラーレスポンスが異なる
                if (isset($decodeBody['error_code'])) {
                    switch ($decodeBody['error_code']) {
                        case -1:
                            throw new GooLabApiException('Invalid app_id.', $statusCode);
                            break;
                        case -3:
                            throw new GooLabApiException('Invalid request parameter.', $statusCode);
                            break;
                        default:
                            throw new GooLabApiException('Invalid request.', $statusCode);
                            break;
                    }
                } elseif (isset($decodeBody['error'])) {
                    throw new GooLabApiException($decodeBody['error']['message'], $statusCode);
                }
                break;
            case 404:
                throw new GooLabApiException('Not found: ' . $path, $statusCode);
                break;
            case 405:
                throw new GooLabApiException('Method not allowed.', $statusCode);
                break;
            case 413:
                throw new GooLabApiException('Request to large.', $statusCode);
                break;
            case 500:
                throw new GooLabApiException('Internal Server Error.', $statusCode);
                break;
            
            default:
                if ($decodeBody === null || count($decodeBody) === 0) {
                    throw new GooLabApiException('Bad Response.', -2);
                    break;
                }
        }
        return $decodeBody;
    }
}
