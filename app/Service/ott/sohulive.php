<?php

namespace App\Service\ott;

use Snoopy\Snoopy;

class sohulive extends ottbase {

    public $key = array('name');
    public $expireTime = 86400;

    private $lid = array(
        'bjws' => '307',
        'gdws' => '329',
        'szws' => '367',
        'hljws' => '83',
        'sdws' => '131',
        'ahws' => '140',
        'jxws' => '173',
        'dnws' => '224',
        'tjws' => '274',
        'scws' => '285',
        'nfws' => '333'
    );

    private $breif = array(
        'bjws' => 'btv1',
        'gdws' => 'gdHD',
        'szws' => 'sztvHD',
        'hljws' => 'hljHD',
        'sdws' => 'sdtv',
        'ahws' => 'ahtv',
        'jxws' => 'jxtv',
        'dnws' => 'setv',
        'tjws' => 'tjtv',
        'scws' => 'sctv',
        'nfws' => 'nftv'
    );

    public function getKey()
    {
       return $this->key;
    }

    public function getExpireTime()
    {
       return $this->expireTime;
    }

    public function getUrl($data){
        $name = $data['name'];
        echo "sohulive: {$data['name']}\n";
        $encoding = "utf-8";
        $lid = $this->lid[$name];
        $af = 1;
        $type = 1;
        $uid = (time()-24*3600) . '0181586981';
        $t = $this->randomFloat();
        $url = "http://live.tv.sohu.com/live/player_json.jhtml?encoding={$encoding}&lid={$lid}&af={$af}&type={$type}&uid={$uid}&out=0&g=8&referer=$referer&t={$t}";

        $client = $this->getHttpClient();
        $response = $client->get($url, $this->setHeader([
            'referer' => "http://live.tv.sohu.com/" . $this->breif[$name]
        ]));

        $data = json_decode($response,true);
        return $data['data']['hls'];
    }

    public function randomFloat($min = 0, $max = 1)
    {
        return ($min + mt_rand() / mt_getrandmax() * ($max - $min)).mt_rand(11,99);
    }


}
