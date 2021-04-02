<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Request extends Model
{
    public static $days = [
        0 => 'вс',
        1 => 'пн',
        2 => 'вт',
        3 => 'ср',
        4 => 'чт',
        5 => 'пт',
        6 => 'сб',
    ];

    public static $currencies = [
        'RUB' => 'рублей',
        'USD' => 'долларов',
        'EUR' => 'евро',
        'UAH' => 'гривен',
        'CNY' => 'жэньминьби',
        'KZT' => 'тенге',
        'AZN' => 'азербайджанских манат',
        'BYN' => 'белорусских рублей',
        'THB' => 'тайских бат',
        'KGS' => 'киргизских сом',
        'UZS' => 'узбекских сум'
    ];

    public static $shortCurrencyTranslate = [
        'rub' => 'руб',
    ];


    public static $passengers = [
        'url' => '1 пассажир, экономкласс',
        'url2Passengers' => '2 пассажира, экономкласс',
        'url1Business' => '1 пассажир, бизнес-класс',
        'urlForma' => 'открытая редактируемая форма поиска'
    ];

    protected $table = 'requests';

    protected $fillable = [
        'user_id', 'content', 'interval', 'output', 'limit', 'currentLimit', 'updated_at', 'group_id', 'send_count'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public static function getFlightsFromApiFunc($currency)
    {
        return static::$shortCurrencyTranslate[$currency] ?? $currency;
    }

    public function getFlightsFromApi()
    {

        //if ((!$this->limitHasReached() || $this->haveNoLimit()) && $this->timeHasPassed()) {
        try {
            $content = $this->getFlightsFromApiRequest();
        } catch (Exception $e) {
            Log::error('getFlightsFromApi ' . $e->getMessage());
        }

        $this->updateRequest();

        if ($content) {
            $this->updateRequestCurrentLimit(count($content));
            return $content;
        }
        //   }

        return [];
    }

    public function limitHasReached()
    {
        return (int)$this->currentLimit >= (int)$this->limit;
    }

    public function haveNoLimit()
    {
        return $this->limit == 0;
    }

    public function timeHasPassed()
    {
        $now = Carbon::now();
        $timePast = $this->getIntervalInSeconds();

        return $now->diffInSeconds($this->updated_at) > $timePast;
    }

    public function getIntervalInSeconds()
    {
        return $this->interval * 60;
    }

    public function formRequestJson()
    {
        $requestContent = json_decode($this->content, true);
        $requestOutput = json_decode($this->output, true);

        // if (isset($requestOutput)) {
        //     $requestOutput = [
        //         "citySrc" => "Москва",
        //         "cityDst" => "любой"
        //     ];
        // }

        $json['filter'] = $requestContent;
        // $json['filter']['src.name'] = $requestOutput['citySrc'];
        // $json['filter']['dst.name'] = $requestOutput['cityDst'];
        // if (isset($json['filter']['src.name'])) {
        // 	unset($json['filter']['src.name']);
        // }
        //unset($json['filter']['dst.name']);
        unset($json['filter']['dst.name']);
        unset($json['filter']['src.name']);

        $json['filter']['src.nameTranslations.ru'] = isset($requestOutput['citySrc']) ? $requestOutput['citySrc'] : "Москва";

        if (isset($requestOutput['cityDst']) && $requestOutput['cityDst'] != 'любой') {
            $json['filter']['dst.nameTranslations.ru'] = $requestOutput['cityDst'];
        }
        $limit = $this->limit;
        if (!$limit) {
            $limit = ($this->interval / 60) * 90;
        }
        if ($limit) {
            $json['limit'] = $limit;
        }

        // if (isset($json['filter']['Dst.continentCode']) && $json['filter']['Dst.continentCode'] == 'ALL') {
        // 	unset($json['filter']['Dst.continentCode']);
        // }

        if (empty($json['filter']['Dst.continentCode']) or $json['filter']['Dst.continentCode'] == 'ALL') {
            unset($json['filter']['Dst.continentCode']);
        }

        if (empty($json['filter']['Dst.CountryCode'])) {
            unset($json['filter']['Dst.CountryCode']);
        }

        //print_r($json);
        //print_r(json_encode($json));
        Log::info($json);

        return $json;
    }

    public function updateRequest()
    {
        $now = Carbon::now();
        $this->updated_at = $this->interval === 0 ? $now->addYears(10) : $now;
        $this->save();
    }

    public function updateRequestCurrentLimit($count)
    {
        $this->currentLimit += $count;
        $this->save();
    }

    public function getFlightsFromApiRequest()
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post(
            'https://api.cheapflights.sale/api/Flights/query',
            [
                'json' => $this->formRequestJson()
            ]
        );

        \Log::info('Found ' . count(json_decode($response->getBody(), true)));

        return json_decode($response->getBody(), true);
    }

    public function inRequestAllowableUpdatedDatesRadius($flight)
    {
        $flightUpdated = date_format(date_create($flight['updated']), 'Y-m-d H:i:s');

        $requestUpdated = $this->updated;
        $allowableDatesRadius = date('Y-m-d H:i:s', strtotime('-' . $requestUpdated . ' minute', strtotime(date('Y-m-d H:i:s'))));

        return $flightUpdated > $allowableDatesRadius;
    }

    public static function getCountries()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'GET',
            'https://api.cheapflights.sale/api/Places/countries',
            ['timeout' => 9999]
        );
        $countries = json_decode($response->getBody()->getContents(), true);
        $newCountries = [];
        foreach ($countries as $country) {
            $newCountries[$country['code']] = $country;
        }

        return $newCountries;
    }

    private static function formatDate($date)
    {
        $year = substr($date, 2, 2);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);

        return $day . '.' . $month . '.' . $year;
    }

    public function makeRequestMessage($item)
    {
        $output = json_decode($this->output, true);
        $countries = Request::getCountries();
        $language = isset($output['language']) ? $output['language'] : "ru";
        $srcCountry = $countries[$item['src']['countryCode']]['nameTranslations'][$language];
        $srcCity = $item['src']['nameTranslations'][$language] ?? $item['src']['info']['name'];

        $dstCountry = $countries[$item['dst']['countryCode']]['nameTranslations'][$language];
        $dstCity = $item['dst']['nameTranslations'][$language] ?? $item['dst']['info']['name'];

        $srcDt = self::formatDate($item['srcDt']);
        $srcDayOfWeek = self::$days[$item['srcDayOfWeek']];

        $dstDt = self::formatDate($item['dstDt']);
        $dstDayOfWeek = $item['dstDayOfWeek'] ? '-' . self::$days[$item['dstDayOfWeek']] : '';
        $days = $item['days'];

        $currency = isset($output['currency']) ? $output['currency'] : "RUB";
        $price = $item['localizedInfos'][$currency]['price'];

        $discount = $item['discount'];
        $translateCurrency = static::getFlightsFromApiFunc(strtolower($currency));
        $oldPrice = (round($item['localizedInfos'][$currency]['price'] / (1 - ($discount / 100)))) . ' ' . $translateCurrency;
        $price = round($price) . ' ' . $translateCurrency;

        $footer = $item['footer'];

        $tempMin = $item['temp']['min'];
        $tempMax = $item['temp']['max'];
        $tempSummary = $item['temp']['summary'];

        $updated = $item['updated'];
        $currencyForUrl = isset($output['currencyForUrl']) ? $output['currencyForUrl'] : "RUB";
        $passengers = isset($output['passengers']) ? $output['passengers'] : "url";
        $fullUrl = $item['localizedInfos'][$currencyForUrl][$passengers];
        $vkAppId = getenv('VK_APP_ID');

        if (!empty($days)) {
            $dates = <<<EOT
$srcDt - $dstDt на $days дн., $srcDayOfWeek$dstDayOfWeek
EOT;
            $arrow = '⇄';
        } else {
            $dates = <<<EOT
$srcDt, $srcDayOfWeek
EOT;
            $arrow = '→';
        }

        $imgApiArr = array_pop($item['dstImages']);
        $imgSrc = getenv('API_URL') . $imgApiArr['url'];

        $apiMessage = Message::where(['name' => 'api_text'])->first();

        $healthy = [
            '[[srcCity]]', '[[srcCountry]]', '[[arrow]]',
            '[[dstCity]]', '[[dstCountry]]', '[[dates]]',
            '[[price]]', '[[dstCity]]',
            '[[oldPrice]]', '[[discount]]', '[[footer]]',
            '[[tempMin]]', '[[tempMax]]', '[[vkAppId]]',
            '[[updated:d]]', '[[updated:g]]', '[[tempSummary]]',
            '[[url]]', '[[passengers]]'
        ];

        $updated = date_create($updated);
        $updatedG = date_format($updated, "Y-m-d H:i:s");
        $updatedD = explode(' ', $updatedG)[0];
        $yummy = [
            $srcCity, $srcCountry, $arrow,
            $dstCity, $dstCountry, $dates,
            $price, $dstCity,
            $oldPrice, $discount, $footer,
            $tempMin, $tempMax, $vkAppId,
            $updatedD, $updatedG, $tempSummary,
            $fullUrl, self::$passengers[$passengers]
        ];
        $apiMessage = str_replace($healthy, $yummy, $apiMessage['content']);

        $message = <<<EOT
$apiMessage
EOT;

        $this->send_count = $this->send_count + 1;
        $this->save();

        return $message;
    }

    public static function getAllByUserId($id)
    {
        return self::where(['user_id' => $id])->get();
    }
}
