<?php

/**
 * 気象庁から天気予報を取得するAPI
 */

declare(strict_types=1);

class jma
{
    private const AREA_JSON_URL = 'https://www.jma.go.jp/bosai/common/const/area.json';
    private const FORECAST_JSON_URL = 'https://www.jma.go.jp/bosai/forecast/data/forecast/%s.json';
    private const AREA_CLASS = ['centers', 'offices'];
    private const FORECAST_CLASS = [
        0 => 'twoDays',   // 明後日までの詳細
        1 => 'oneWeek'  // 週間
    ];
    // 曜日
    private const WEEK = ['日', '月', '火', '水', '木', '金', '土'];
    // 天気コード
    private const WEATHER_CODES = [
        100 => '晴れ',
        101 => '晴れ時々くもり',
        102 => '晴れ一時雨',
        103 => '晴れ時々雨',
        104 => '晴れ一時雪',
        105 => '晴れ時々雪',
        106 => '晴れ一時雨か雪',
        107 => '晴れ時々雨か雪',
        108 => '晴れ一時雨か雷雨',
        110 => '晴れのち時々くもり',
        111 => '晴れのちくもり',
        112 => '晴れのち一時雨',
        113 => '晴れのち時々雨',
        114 => '晴れのち雨',
        115 => '晴れのち一時雪',
        116 => '晴れのち時々雪',
        117 => '晴れのち雪',
        118 => '晴れのち雨か雪',
        119 => '晴れのち雨か雷雨',
        120 => '晴れ朝夕一時雨',
        121 => '晴れ朝の内一時雨',
        122 => '晴れ夕方一時雨',
        123 => '晴れ山沿い雷雨',
        124 => '晴れ山沿い雪',
        125 => '晴れ午後は雷雨',
        126 => '晴れ昼頃から雨',
        127 => '晴れ夕方から雨',
        128 => '晴れ夜は雨',
        129 => '晴れ夜半から雨',
        130 => '朝の内霧後晴れ',
        131 => '晴れ明け方霧',
        132 => '晴れ朝夕くもり',
        140 => '晴れ時々雨で雷を伴う',
        160 => '晴れ一時雪か雨',
        170 => '晴れ時々雪か雨',
        181 => '晴れのち雪か雨',
        200 => 'くもり',
        201 => 'くもり時々晴',
        202 => 'くもり一時雨',
        203 => 'くもり時々雨',
        204 => 'くもり一時雪',
        205 => 'くもり時々雪',
        206 => 'くもり一時雨か雪',
        207 => 'くもり時々雨か雪',
        208 => 'くもり一時雨か雷雨',
        209 => '霧',
        210 => 'くもりのち時々晴れ',
        211 => 'くもりのち晴れ',
        212 => 'くもりのち一時雨',
        213 => 'くもりのち時々雨',
        214 => 'くもりのち雨',
        215 => 'くもりのち一時雪',
        216 => 'くもりのち時々雪',
        217 => 'くもりのち雪',
        218 => 'くもりのち雨か雪',
        219 => 'くもりのち雨か雷雨',
        220 => 'くもり朝夕一時雨',
        221 => 'くもり朝の内一時雨',
        222 => 'くもり夕方一時雨',
        223 => 'くもり日中時々晴れ',
        224 => 'くもり昼頃から雨',
        225 => 'くもり夕方から雨',
        226 => 'くもり夜は雨',
        227 => 'くもり夜半から雨',
        228 => 'くもり昼頃から雪',
        229 => 'くもり夕方から雪',
        230 => 'くもり夜は雪',
        231 => 'くもり海上海岸は霧か霧雨',
        240 => 'くもり時々雨で雷を伴う',
        250 => 'くもり時々雪で雷を伴う',
        260 => 'くもり一時雪か雨',
        270 => 'くもり時々雪か雨',
        281 => 'くもりのち雪か雨',
        300 => '雨',
        301 => '雨時々晴れ',
        302 => '雨時々止む',
        303 => '雨時々雪',
        304 => '雨か雪',
        306 => '大雨',
        307 => '風雨共に強い',
        308 => '雨で暴風を伴う',
        309 => '雨一時雪',
        311 => '雨のち晴れ',
        313 => '雨のちくもり',
        314 => '雨のち時々雪',
        315 => '雨のち雪',
        316 => '雨か雪のち晴れ',
        317 => '雨か雪のちくもり',
        320 => '朝の内雨のち晴れ',
        321 => '朝の内雨のちくもり',
        322 => '雨朝晩一時雪',
        323 => '雨昼頃から晴れ',
        324 => '雨夕方から晴れ',
        325 => '雨夜は晴',
        326 => '雨夕方から雪',
        327 => '雨夜は雪',
        328 => '雨一時強く降る',
        329 => '雨一時みぞれ',
        340 => '雪か雨',
        350 => '雨で雷を伴う',
        361 => '雪か雨のち晴れ',
        371 => '雪か雨のちくもり',
        400 => '雪',
        401 => '雪時々晴れ',
        402 => '雪時々止む',
        403 => '雪時々雨',
        405 => '大雪',
        406 => '風雪強い',
        407 => '暴風雪',
        409 => '雪一時雨',
        411 => '雪のち晴れ',
        413 => '雪のちくもり',
        414 => '雪のち雨',
        420 => '朝の内雪のち晴れ',
        421 => '朝の内雪のちくもり',
        422 => '雪昼頃から雨',
        423 => '雪夕方から雨',
        424 => '雪夜半から雨',
        425 => '雪一時強く降る',
        426 => '雪のちみぞれ',
        427 => '雪一時みぞれ',
        450 => '雪で雷を伴う',
    ];

    private mixed $stdIn;
    private array $info = [];
    private array $areas = [];

    /**
     * メイン処理
     *
     * @return void
     */
    public function main()
    {
        $this->echoNowLoading();

        /**
         * 地方データの取得
         * ・地方：広域
         * ・地方：中域（関東・甲信越だと県）
         * ・地方：小域（東京都の場合、東京地方とか諸島とか）
         * ・地区（東京都の場合、小域内の東西南北のどこよ？）
         * ・市区町村（東京都の場合、新宿区とか）
         */
        list(
            $regionsWide,
            $regionsMiddle,
        ) = $this->fetchJmaDatas(self::AREA_JSON_URL, self::AREA_CLASS);

        // 広域選択
        $this->selectWideRegion($regionsWide);

        // 中域選択
        $this->echoNowLoading();
        $this->selectOtherRegion($regionsMiddle);

        // 気象情報を取得する
        $this->echoNowLoading();
        $forecastDatas = $this->fetchJmaDatas(sprintf(self::FORECAST_JSON_URL, $this->info['areaDatas'][$this->stdIn]['code']));
        $formatForecastDatas = [];
        $k = 0;
        date_default_timezone_set('Asia/Tokyo');
        foreach ($forecastDatas as $forecastKey => $forecastData) {
            if ($forecastKey === 0) $k = self::FORECAST_CLASS[0];
            if ($forecastKey === 1) $k = self::FORECAST_CLASS[1];
            // 観測気象台
            $formatForecastDatas[$k]['publishingOffice'] = $forecastData['publishingOffice'];
            // データ報告日時
            $formatForecastDatas[$k]['reportDatetime'] = date('Y年n月j日 H時i分', strtotime($forecastData['reportDatetime']));
            foreach ($forecastData['timeSeries'] as $timeSeriesValue) {
                foreach ($timeSeriesValue['areas'] as $areasValue) {
                    if (array_key_exists('weatherCodes', $areasValue)) {
                        $i = 0;
                        foreach ($areasValue['weatherCodes'] as $weatherCodesValue) {
                            // 天気概要
                            $formatForecastDatas[$k]['forecast'][$areasValue['area']['name']][$i]['weatherCodes'] = self::WEATHER_CODES[$weatherCodesValue];
                            // 日付
                            $day = match ($i) {
                                0 => '本日 ',
                                1 => '明日 ',
                                2 => '明後日 ',
                                default => '',
                            };
                            $now = strtotime("+{$i} day");
                            $timeDefinesValue = $day . date('n月j日', $now) . '(' . self::WEEK[date('w', $now)] . ')';
                            $formatForecastDatas[$k]['forecast'][$areasValue['area']['name']][$i]['timeDefines'] = $timeDefinesValue;
                            $i++;
                        }
                    }
                    // 天気詳細
                    if (array_key_exists('weathers', $areasValue)) {
                        $j = 0;
                        foreach ($areasValue['weathers'] as $weathersValue) {
                            $formatForecastDatas[$k]['forecast'][$areasValue['area']['name']][$j]['weathers'] = $weathersValue;
                            $j++;
                        }
                    }
                    // 風
                    if (array_key_exists('winds', $areasValue)) {
                        $l = 0;
                        foreach ($areasValue['winds'] as $windsValue) {
                            $formatForecastDatas[$k]['forecast'][$areasValue['area']['name']][$l]['winds'] = $windsValue;
                            $l++;
                        }
                    }
                    // 波
                    if (array_key_exists('waves', $areasValue)) {
                        $m = 0;
                        foreach ($areasValue['waves'] as $wavesValue) {
                            $formatForecastDatas[$k]['forecast'][$areasValue['area']['name']][$m]['waves'] = $wavesValue;
                            $m++;
                        }
                    }
                }
                // 日付
                $h = 0;
                foreach ($timeSeriesValue['timeDefines'] as $timeDefinesKey => $timeDefinesValue) {
                    $day = match ($timeDefinesKey) {
                        0 => '本日 ',
                        1 => '明日 ',
                        2 => '明後日 ',
                        default => '',
                    };
                    $timeDefinesValue = $day . date('n月j日', strtotime($timeDefinesValue)) . '(' . self::WEEK[date('w', strtotime($timeDefinesValue))] . ')';
                    $formatForecastDatas[$k]['forecast'][$areasValue['area']['name']][$h]['timeDefines'] = $timeDefinesValue;
                    $h++;
                }
            }
        }
        var_dump($formatForecastDatas);
    }

    /**
     * 気象庁からデータを取得する
     *
     * @param string $url
     * @param array $dataClass
     * @return array
     */
    private function fetchJmaDatas(string $url, array $dataClasses = null): array
    {
        $jsonDatas = json_decode($this->commonCurl($url), true);
        if ($dataClasses === null) {
            return $jsonDatas;
        }
        $fetchedDatas = [];
        foreach ($dataClasses as $dataClass) {
            $fetchedDatas[] = $jsonDatas[$dataClass];
        }
        return $fetchedDatas;
    }

    /**
     * Now Loadingの出力
     *
     * @return void
     */
    private function echoNowLoading(): void
    {
        echo "Now Loading...\n";
    }

    /**
     * cURL関数
     * @param string $url
     * @return string
     */
    private function commonCurl(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * 標準入力
     *
     * @return void
     */
    private function stdIn(): void
    {
        $this->stdIn = trim(fgets(STDIN));
    }

    /**
     * 入力が空である・入力値が数値ではない・入力値が11以上・入力値が負の数
     *
     * @param int $max
     * @return boolean
     */
    private function isInputCond(int $max): bool
    {
        return $this->stdIn === ''
            || $this->stdIn === null
            || $this->stdIn < 0
            || $this->stdIn > $max;
    }

    /**
     * 広域選択
     *
     * @param array $regionsWide
     * @return void
     */
    private function selectWideRegion(array $regionsWide): void
    {
        /**
         * 広域選択に向けてのデータ準備
         */
        $i = 0;
        foreach ($regionsWide as $areaDatas) {
            $this->info['areaDatas'][$i] = [
                'name' => $areaDatas['name'],
                'children' => $areaDatas['children']
            ];
            $this->info['input'][] = "{$i}\t{$areaDatas['name']}";
            $i++;
        }
        $regionCnt = count($this->info['input']) - 1;
        $this->info['input'] = implode("\n", $this->info['input']);

        /**
         * 広域を選択する
         */
        $this->selectRegion($regionCnt);
    }

    /**
     * 広域以外
     *
     * @param array $regionDatas
     * @return void
     */
    private function selectOtherRegion(array $regionDatas): void
    {
        // 地域コードを取得
        $children = $this->info['areaDatas'][$this->stdIn]['children'];
        $this->info = [];
        $i = 0;
        foreach ($children as $childrenCodes) {
            $this->info['areaDatas'][$i] = [
                'code' => $childrenCodes,
                'name' => $regionDatas[$childrenCodes]['name'],
            ];
            $this->info['input'][] = "{$i}\t{$regionDatas[$childrenCodes]['name']}";
            $i++;
        }
        $regionCnt = count($this->info['input']) - 1;
        $this->info['input'] = implode("\n", $this->info['input']);

        /**
         * 入力：地域を選択する
         */
        $this->selectRegion($regionCnt);
    }

    /**
     * 地域選択
     *
     * @param integer $regionCnt
     * @return void
     */
    private function selectRegion(int $regionCnt): void
    {
        echo "0～{$regionCnt}のいずれかを選択し、Enterキーを押してください。\n{$this->info['input']}\n";
        $this->stdIn();
        // 不正な値の場合、処理終了
        if ($this->isInputCond($regionCnt)) {
            echo "不正な値です。処理を終了します。\n最初からやり直してください。";
            exit;
        }
    }
}

$jma = new jma();
$jma->main();