<?php

/**
 * 気象庁から天気予報を取得するAPI
 */

declare(strict_types=1);

class jma
{
    private const AREA_JSON_URL = 'https://www.jma.go.jp/bosai/common/const/area.json';
    private const AREA_CLASS = [
        'centers', 'offices', 'class10s', 'class15s', 'class20s'
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
            $regionsSmall, 
            $districts, 
            $cities
            ) = $this->fetchJmaDatas(self::AREA_JSON_URL, self::AREA_CLASS);

        // 広域選択
        $this->selectWideRegion($regionsWide);

        // 中域選択
        $this->echoNowLoading();
        $this->selectOtherRegion($regionsMiddle);

        // 小域選択
        $this->echoNowLoading();
        $this->selectOtherRegion($regionsSmall);

        // 地区選択
        $this->echoNowLoading();
        $this->selectOtherRegion($districts);

        // 市区町村選択
        $this->echoNowLoading();
        $this->selectOtherRegion($cities);

        // 気象情報を取得する

    }

    /**
     * 気象庁からデータを取得する
     *
     * @param string $url
     * @param array $dataClass
     * @return array
     */
    private function fetchJmaDatas(string $url, array $dataClasses): array
    {
        $jsonDatas = json_decode($this->commonCurl($url), true);
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
        // 市区町村の場合はchidlrenがない、かつ最小単位のためエリアコードとnameだけ取得するようにする
        $children = $this->info['areaDatas'][$this->stdIn]['children'];
        $this->info = [];
        $i = 0;
        foreach ($children as $childrenCodes) {
            if (empty($regionDatas[$childrenCodes]['children'])) {
                $this->info['areaDatas'][$i] = [
                    'code' => $childrenCodes,
                    'name' => $regionDatas[$childrenCodes]['name'],
                ];
            } else {
                $this->info['areaDatas'][$i] = [
                    'name' => $regionDatas[$childrenCodes]['name'],
                    'children' => $regionDatas[$childrenCodes]['children']
                ];
            }            
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