<?php
/**
 * Created by PhpStorm.
 * User: sz
 * Date: 2017/8/25
 * Time: 10:17
 */

namespace App\Service\ott;

use App\Components\http\MyCurl;
use GuzzleHttp\Client;

abstract class ottbase
{
    public $expireTime;
    public $key;
    public $curl;
    public $header = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'zh-CN,zh;q=0.9,en;q=0.8,sm;q=0.7',
        'Accept-Encoding' => 'gzip'
    ];

    public function setCurl()
    {
        $this->curl = new MyCurl();
    }

    public function getHttpClient()
    {
        return new Client();
    }

    public function setHeader($data)
    {
        $header = $this->header;
        foreach ($data as $key => $value) {
            $header[$key] = $value;
        }

        return $header;
    }

    abstract function getKey();
    abstract function getExpireTime();
    abstract function getUrl($data);
}